<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db_config.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is not available. Check db_config.php.');
}

$conn->set_charset('utf8mb4');

/* Keep navbar output inside <body>, while still loading its PHP before output. */
ob_start();
require __DIR__ . '/navbar.php';
$navbar_html = (string) ob_get_clean();

function customerPageRedirect(string $url, ?string $message = null): void
{
    if ($message === null) {
        header('Location: ' . $url);
        exit;
    }

    $messageJson = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $urlJson = json_encode($url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "<script>alert($messageJson); window.location.href=$urlJson;</script>";
    exit;
}

$phone = isset($_GET['phone']) ? trim((string) $_GET['phone']) : '';
if ($phone === '') {
    customerPageRedirect('job_list.php');
}

$is_edit = isset($_GET['edit']) && $_GET['edit'] === 'true';

if (empty($_SESSION['customer_details_csrf'])) {
    $_SESSION['customer_details_csrf'] = bin2hex(random_bytes(32));
}
$csrf_token = (string) $_SESSION['customer_details_csrf'];

/* ===============================
   DATA UPDATE & DELETE SECTION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if (!hash_equals($csrf_token, $postedToken)) {
        http_response_code(403);
        die('Invalid request token. Refresh the page and try again.');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deviceImages = [];

        try {
            $conn->begin_transaction();

            $imageStmt = $conn->prepare(
                'SELECT jd.device_image
                 FROM job_device jd
                 INNER JOIN job j ON j.job_no = jd.job_no
                 WHERE j.phone_number = ? AND jd.device_image IS NOT NULL AND jd.device_image <> ""'
            );
            $imageStmt->bind_param('s', $phone);
            $imageStmt->execute();
            $imageResult = $imageStmt->get_result();
            while ($imageRow = $imageResult->fetch_assoc()) {
                $deviceImages[] = basename((string) $imageRow['device_image']);
            }
            $imageStmt->close();

            /* sms_history has no ON DELETE CASCADE, so remove it first. */
            $smsStmt = $conn->prepare(
                'DELETE sh FROM sms_history sh
                 INNER JOIN job_device jd ON jd.job_device_id = sh.job_device_id
                 INNER JOIN job j ON j.job_no = jd.job_no
                 WHERE j.phone_number = ?'
            );
            $smsStmt->bind_param('s', $phone);
            $smsStmt->execute();
            $smsStmt->close();

            /* Related jobs, devices, invoices and payments are removed by foreign-key cascades. */
            $deleteStmt = $conn->prepare('DELETE FROM customer WHERE phone_number = ?');
            $deleteStmt->bind_param('s', $phone);
            $deleteStmt->execute();

            if ($deleteStmt->affected_rows !== 1) {
                throw new RuntimeException('Customer was not found.');
            }

            $deleteStmt->close();
            $conn->commit();

            $imageDirectory = __DIR__ . '/uploads/devices/';
            foreach ($deviceImages as $imageName) {
                $imagePath = $imageDirectory . $imageName;
                if (is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }

            customerPageRedirect('add_customer.php', 'Customer and all related jobs deleted successfully!');
        } catch (Throwable $error) {
            $conn->rollback();
            error_log('Customer delete failed: ' . $error->getMessage());
            customerPageRedirect(
                'customer_details.php?phone=' . rawurlencode($phone),
                'Unable to delete this customer. Please try again.'
            );
        }
    }

    $name = trim((string) ($_POST['customer_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));

    if ($name === '') {
        customerPageRedirect(
            'customer_details.php?phone=' . rawurlencode($phone) . '&edit=true',
            'Customer name is required.'
        );
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        customerPageRedirect(
            'customer_details.php?phone=' . rawurlencode($phone) . '&edit=true',
            'Please enter a valid email address.'
        );
    }

    $uploadedFiles = [];

    try {
        $conn->begin_transaction();

        $customerStmt = $conn->prepare(
            'UPDATE customer SET customer_name = ?, email = ?, address = ? WHERE phone_number = ?'
        );
        $customerStmt->bind_param('ssss', $name, $email, $address, $phone);
        $customerStmt->execute();
        $customerStmt->close();

        $warrantyStatuses = $_POST['warranty_status'] ?? [];
        $deviceDescriptions = $_POST['device_desc'] ?? [];

        if (is_array($warrantyStatuses)) {
            foreach ($warrantyStatuses as $rawId => $rawStatus) {
                $deviceId = filter_var($rawId, FILTER_VALIDATE_INT);
                if ($deviceId === false || $deviceId < 1) {
                    continue;
                }

                $status = (string) $rawStatus;
                if (!in_array($status, ['Warranty', 'No Warranty'], true)) {
                    $status = 'No Warranty';
                }

                $description = isset($deviceDescriptions[$rawId])
                    ? trim((string) $deviceDescriptions[$rawId])
                    : '';
                $imageName = null;

                $uploadError = $_FILES['device_image']['error'][$rawId] ?? UPLOAD_ERR_NO_FILE;
                if ($uploadError !== UPLOAD_ERR_NO_FILE) {
                    if ($uploadError !== UPLOAD_ERR_OK) {
                        throw new RuntimeException('Device image upload failed.');
                    }

                    $tmpName = (string) ($_FILES['device_image']['tmp_name'][$rawId] ?? '');
                    $fileSize = (int) ($_FILES['device_image']['size'][$rawId] ?? 0);
                    if ($fileSize < 1 || $fileSize > 5 * 1024 * 1024) {
                        throw new RuntimeException('Device image must be smaller than 5 MB.');
                    }

                    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
                    $allowedTypes = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (!isset($allowedTypes[$mimeType])) {
                        throw new RuntimeException('Only JPG, PNG and WEBP images are allowed.');
                    }

                    $targetDirectory = __DIR__ . '/uploads/devices/';
                    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true)) {
                        throw new RuntimeException('Unable to create the image upload directory.');
                    }

                    $imageName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
                    $targetPath = $targetDirectory . $imageName;
                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        throw new RuntimeException('Unable to save the uploaded device image.');
                    }
                    $uploadedFiles[] = $targetPath;
                }

                if ($imageName !== null) {
                    $deviceStmt = $conn->prepare(
                        'UPDATE job_device jd
                         INNER JOIN job j ON j.job_no = jd.job_no
                         SET jd.warranty_status = ?, jd.description = ?, jd.device_image = ?
                         WHERE jd.job_device_id = ? AND j.phone_number = ?'
                    );
                    $deviceStmt->bind_param('sssis', $status, $description, $imageName, $deviceId, $phone);
                } else {
                    $deviceStmt = $conn->prepare(
                        'UPDATE job_device jd
                         INNER JOIN job j ON j.job_no = jd.job_no
                         SET jd.warranty_status = ?, jd.description = ?
                         WHERE jd.job_device_id = ? AND j.phone_number = ?'
                    );
                    $deviceStmt->bind_param('ssis', $status, $description, $deviceId, $phone);
                }

                $deviceStmt->execute();
                $deviceStmt->close();
            }
        }

        $conn->commit();
        customerPageRedirect(
            'customer_details.php?phone=' . rawurlencode($phone),
            'Changes saved successfully!'
        );
    } catch (Throwable $error) {
        $conn->rollback();
        foreach ($uploadedFiles as $uploadedFile) {
            if (is_file($uploadedFile)) {
                @unlink($uploadedFile);
            }
        }
        error_log('Customer update failed: ' . $error->getMessage());
        customerPageRedirect(
            'customer_details.php?phone=' . rawurlencode($phone) . '&edit=true',
            'Unable to save the changes. Please check the details and try again.'
        );
    }
}

/* ===============================
   FETCH PAGE DATA
================================ */
$customerStmt = $conn->prepare('SELECT * FROM customer WHERE phone_number = ? LIMIT 1');
$customerStmt->bind_param('s', $phone);
$customerStmt->execute();
$customer = $customerStmt->get_result()->fetch_assoc();
$customerStmt->close();

if (!$customer) {
    customerPageRedirect('add_customer.php', 'Customer not found.');
}

$latestJobStmt = $conn->prepare(
    'SELECT job_no FROM job WHERE phone_number = ? ORDER BY job_date DESC, job_no DESC LIMIT 1'
);
$latestJobStmt->bind_param('s', $phone);
$latestJobStmt->execute();
$latestJobData = $latestJobStmt->get_result()->fetch_assoc();
$latestJobStmt->close();
$current_job_no = $latestJobData['job_no'] ?? '';

$jobsStmt = $conn->prepare(
    'SELECT job.*, technicians.name AS tech
     FROM job
     LEFT JOIN technicians ON job.technician_id = technicians.technician_id
     WHERE job.phone_number = ?
     ORDER BY job.job_date DESC, job.job_no DESC'
);
$jobsStmt->bind_param('s', $phone);
$jobsStmt->execute();
$jobs = $jobsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - <?= htmlspecialchars($phone) ?></title>
    <link rel="stylesheet" href="CSS/global.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2ecc71; --primary-hover: #27ae60; --primary-dark: #229954;
            --success: #10b981; --success-hover: #059669; --danger: #ef4444;
            --warning: #f59e0b; --secondary: #64748b; --bg-main: #f8fafc;
            --card-bg: #ffffff; --text-main: #1a202c; --text-dark: #0f172a;
            --text-muted: #475569; --border: #e2e8f0; --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08); --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body { background: var(--bg-main); font-family: 'Inter', sans-serif; padding: 140px 20px 40px; color: var(--text-main); line-height: 1.6; transition: background 0.3s ease; }

        body.dark-mode {
            --bg-main: #0b1329;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-dark: #ffffff;
            --text-muted: #cbd5e1;
            --secondary: #94a3b8;
            --border: #334155;
            background: #0b1329 !important;
            color: #f8fafc !important;
        }

        body.dark-mode .card {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
        }

        body.dark-mode .card-header { border-bottom-color: #334155 !important; }
        body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode .device-name { color: #ffffff !important; }
        body.dark-mode label { color: #94a3b8 !important; }
        body.dark-mode p { color: #cbd5e1 !important; }
        body.dark-mode input, body.dark-mode textarea, body.dark-mode select {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }
        body.dark-mode input[readonly], body.dark-mode textarea[readonly] {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
            opacity: 1 !important;
        }
        body.dark-mode .device-box {
            background: #0f172a !important;
            border-color: #334155 !important;
        }
        body.dark-mode .action-bar { background: #1e293b !important; border-color: #334155 !important; }
        body.dark-mode .page-header h1 { color: #ffffff !important; }
        body.dark-mode .page-header p { color: #94a3b8 !important; }
        body.dark-mode .btn-outline {
            border-color: #475569 !important;
            color: #f8fafc !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }
        body.dark-mode .btn-outline:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px; padding-bottom: 50px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; margin-bottom:30px; color:var(--text-muted); font-weight:700; text-decoration:none; font-size:15px; transition:0.3s; padding:10px 20px; background:transparent; border: 1px solid var(--border); border-radius:100px; margin-top: 15px; }
        .back-link:hover { background:var(--card-bg); color:var(--text-main); border-color:var(--secondary); }
        
        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 36px 40px;
            border-radius: 20px;
            margin-top: 15px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: white;
            text-align: center;
        }
        .page-header h1 { font-size: 32px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom:5px; color: white; }
        .page-header p { color: white; opacity: 0.95; font-size:15px; margin: 0; }
        
        .top-actions { display:flex; gap:12px; flex-wrap:wrap; }
        
        .profile-layout { display: grid; grid-template-columns: 380px 1fr; gap: 40px; align-items: start; }
        
        .card { background: var(--card-bg); padding: 36px; border-radius: 24px; margin-bottom: 28px; box-shadow: var(--shadow-lg); border: 1px solid var(--border); transition: all 0.3s ease; animation: fadeIn 0.5s ease-out; }
        .profile-sidebar { position: sticky; top: 120px; }
        
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 15px; }
        h2, h3 { font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 24px; }
        label { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing:0.5px; color: var(--secondary); margin-bottom: 8px; display: block; }
        
        input, textarea, select { width: 100%; padding: 14px 18px; border: 2px solid var(--border); border-radius: 14px; font-size: 15px; font-family: 'Inter', sans-serif; background: transparent; color:var(--text-main); transition:0.3s; box-sizing: border-box; }
        input:focus, textarea:focus, select:focus { border-color:var(--primary); outline:none; box-shadow:0 0 0 4px rgba(46,204,113,0.1); }
        input[readonly], textarea[readonly] { border-color:transparent; background: rgba(0,0,0,0.02); }
        body.dark-mode input[readonly], body.dark-mode textarea[readonly] { background: rgba(255,255,255,0.02); }
        
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .device-box { background: rgba(0,0,0,0.01); padding: 28px; border-radius: 16px; margin-top: 20px; border: 1px solid var(--border); }
        .device-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; flex-wrap:wrap; gap:15px; }
        .device-name { font-size: 19px; font-weight: 800; }
        
        .status-badge { padding: 8px 16px; border-radius: 100px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:6px; white-space: nowrap; }
        .status-warranty { background: transparent; color: #10b981; border: 1px solid #10b981; }
        .status-no-warranty { background: transparent; color: #ef4444; border: 1px solid #ef4444; }
        
        .btn { padding: 14px 28px; border-radius: 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 10px; border: none; font-size:14px; transition:0.3s; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 10px 20px -10px rgba(0,0,0,0.3); }
        .btn-success { background: var(--success); color: white; }
        .btn-outline { border: 2px solid var(--border); color: var(--text-main); background: transparent; }
        .btn-secondary { background: var(--border); color: var(--text-dark); }
        
        .device-img { width: 120px; height: 120px; object-fit: cover; border-radius: 16px; border: 2px solid var(--border); cursor: pointer; transition: 0.3s; }
        .device-img:hover { transform: scale(1.05); }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* ==================== RESPONSIVE QUERIES ==================== */

        /* Tablet (≤ 1024px) */
        @media (max-width: 1024px) {
            .profile-layout { grid-template-columns: 1fr; gap: 20px; }
            .profile-sidebar { position: static; }
            body { padding-left: 20px; padding-right: 20px; }
            .container { padding: 0 10px; }
            .card { padding: 24px; margin-bottom: 20px; }
        }

        /* Mobile Landscape (≤ 768px) */
        @media (max-width: 768px) {
            body { padding: var(--nav-height, 100px) 15px 40px 15px; }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                margin-bottom: 25px;
            }
            .page-header h1 { font-size: 26px; margin-bottom: 0;}
            .page-header p { margin-left: 0; margin-top: 5px; font-size: 14px; }
            
            .top-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .top-actions .btn {
                width: 100%;
                justify-content: center;
                padding: 12px;
            }
            
            .card { padding: 20px; border-radius: 18px; }
            
            .grid-2 { grid-template-columns: 1fr; gap: 15px; }
            
            .device-box { padding: 20px; border-radius: 14px; }
            
            .device-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .card-header h3 { font-size: 16px; }
            .card-header > div > div { flex-direction: column; gap: 5px !important; margin-top: 10px; }
            
            input, textarea, select { font-size: 15px; /* Prevent iOS zoom */ padding: 12px 14px; }
        }

        /* Mobile Portrait (≤ 480px) */
        @media (max-width: 480px) {
            body { padding: var(--nav-height, 100px) 10px 40px 10px; }
            .container { padding: 0 5px; }
            .page-header h1 { font-size: 22px; }
            .card { padding: 16px; }
            .device-box { padding: 16px; }
            .device-name { font-size: 16px; }
            .btn { padding: 12px; font-size: 13px; }
            .back-link { margin-bottom: 20px; font-size: 14px; }
        }
    </style>
</head>
<body>

<?= $navbar_html ?>

<div class="container">
    <a href="add_customer.php" class="back-link">
        <i class="ph-bold ph-arrow-left"></i> Back to Dashboard
    </a>
    
    <div class="page-header">
        <h1> Customer Profile</h1>
        <p>Manage customer details and repair history</p>
    </div>
    
    <div class="top-actions" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom: 30px; justify-content: flex-end;">
        <?php if(!empty($current_job_no)): ?>
            <a href="jobsheet.php?job_no=<?= rawurlencode((string) $current_job_no) ?>" class="btn btn-outline" target="_blank"><i class="ph-bold ph-printer"></i> Print Job Sheet</a>
        <?php endif; ?>

        <?php if($is_edit): ?>
            <button type="submit" form="customerForm" class="btn btn-success"><i class="ph-bold ph-floppy-disk"></i> Save Changes</button>
            <a href="?phone=<?= rawurlencode($phone) ?>" class="btn btn-secondary"><i class="ph-bold ph-x"></i> Cancel</a>
        <?php else: ?>
            <a href="?phone=<?= rawurlencode($phone) ?>&edit=true" class="btn btn-outline" style="background: var(--card-bg); border-color:var(--primary); color:var(--primary);"><i class="ph-bold ph-pencil-simple"></i> Edit Details</a>
            <button type="button" onclick="confirmDelete()" class="btn" style="background:transparent; border: 1px solid var(--danger); color:var(--danger);"><i class="ph-bold ph-trash"></i> Delete</button>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" id="customerForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
        <div class="profile-layout">
            
            <!-- Left Column: Sticky Profile Card -->
            <div class="profile-sidebar">
                <div class="card" style="padding:40px 30px; text-align:center;">
                    <div style="width:120px; height:120px; background:transparent; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px auto; border:2px solid var(--border);">
                        <i class="ph-fill ph-user" style="font-size:56px; color:var(--primary);"></i>
                    </div>
                    
                    <div class="form-group" style="text-align:left;">
                        <label>Full Name</label>
                        <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= !$is_edit?'readonly':'' ?> style="font-weight:700; font-size:18px;" required>
                    </div>
                    <div class="form-group" style="text-align:left;">
                        <label>Phone Number (Primary ID)</label>
                        <input type="text" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" readonly style="font-family:monospace; font-size:16px;">
                    </div>
                    <div class="form-group" style="text-align:left;">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= !$is_edit?'readonly':'' ?>>
                    </div>
                    <div class="form-group" style="text-align:left; margin-bottom:0;">
                        <label>Residential Address</label>
                        <textarea name="address" <?= !$is_edit?'readonly':'' ?> style="resize:none; height:100px; line-height:1.6;"><?= htmlspecialchars($customer['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Job History -->
            <div class="job-history">
                <h3 style="margin-bottom:24px; font-size:22px;"><i class="ph-fill ph-clock-counter-clockwise"></i> Repair History</h3>
                
                <?php if(mysqli_num_rows($jobs) == 0): ?>
                    <div class="card" style="text-align:center; padding:60px 30px;">
                        <i class="ph-fill ph-folder-open" style="font-size:64px; color:var(--border); margin-bottom:15px;"></i>
                        <p style="color:var(--text-muted); font-size:16px;">No jobs found for this customer.</p>
                    </div>
                <?php endif; ?>

                <?php while($job = mysqli_fetch_assoc($jobs)): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 style="font-size:18px; margin-bottom:8px;"><i class="ph-fill ph-file-text" style="color:var(--primary);"></i> Job #<?= htmlspecialchars((string) $job['job_no'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <div style="display: flex; gap: 20px; font-size:14px;">
                                <span style="display:flex; align-items:center; gap:6px; color:var(--text-muted);"><i class="ph-fill ph-calendar-blank"></i> <strong><?= date("M d, Y", strtotime($job['job_date'])) ?></strong></span>
                                <span style="display:flex; align-items:center; gap:6px; color:var(--text-muted);"><i class="ph-fill ph-wrench"></i> Tech: <strong style="color:var(--text-main);"><?= htmlspecialchars($job['tech'] ?? 'Not Assigned') ?></strong></span>
                            </div>
                        </div>
                    </div>

                    <?php
                    $jno = (string) $job['job_no'];
                    $devicesStmt = $conn->prepare('SELECT * FROM job_device WHERE job_no = ? ORDER BY job_device_id ASC');
                    $devicesStmt->bind_param('s', $jno);
                    $devicesStmt->execute();
                    $devices_res = $devicesStmt->get_result();
                    while($d = mysqli_fetch_assoc($devices_res)):
                        $is_warranty = (strtolower($d['warranty_status'] ?? '') == 'warranty');
                        $deviceId = (int) $d['job_device_id'];
                    ?>
                    <div class="device-box">
                        <div class="device-header">
                            <div class="device-info">
                                <div class="device-name" style="display:flex; align-items:center; gap:10px;"><i class="ph-fill ph-device-mobile" style="color:var(--primary); font-size:28px;"></i> <?= htmlspecialchars((string) $d['device_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div style="margin-top:12px; color:var(--secondary); font-size:14px;"><strong style="color:var(--text-main);">Reported Issue:</strong> <?= htmlspecialchars((string) ($d['issue_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <span class="status-badge <?= $is_warranty ? 'status-warranty' : 'status-no-warranty' ?>">
                                <?php if($is_warranty): ?>
                                    <i class="ph-bold ph-shield-check"></i>
                                <?php else: ?>
                                    <i class="ph-bold ph-shield-warning"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars((string) ($d['warranty_status'] ?? 'No Warranty'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <?php if(!empty($d['device_image'])): ?>
                        <?php $deviceImageUrl = 'uploads/devices/' . rawurlencode(basename((string) $d['device_image'])); ?>
                        <div class="img-preview-container" style="margin-bottom: 24px;">
                            <label>Device Photo</label>
                            <a href="<?= htmlspecialchars($deviceImageUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                <img src="<?= htmlspecialchars($deviceImageUrl, ENT_QUOTES, 'UTF-8') ?>" class="device-img" alt="Device Image">
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Service Notes & Description</label>
                            <textarea name="device_desc[<?= $deviceId ?>]" <?= !$is_edit?'readonly':'' ?> style="min-height:80px;"><?= htmlspecialchars((string) ($d['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                        
                        <?php if($is_edit): ?>
                            <div class="grid-2">
                                <div class="form-group">
                                    <label>Warranty Status</label>
                                    <select name="warranty_status[<?= $deviceId ?>]">
                                        <option value="Warranty" <?= ($d['warranty_status']=='Warranty')?'selected':'' ?>>Warranty</option>
                                        <option value="No Warranty" <?= ($d['warranty_status']=='No Warranty')?'selected':'' ?>>No Warranty</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><?= !empty($d['device_image']) ? 'Update' : 'Upload' ?> Device Image</label>
                                    <input type="file" name="device_image[<?= $deviceId ?>]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="padding:10px;">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                    <?php $devicesStmt->close(); ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </form>
    
    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    </form>
</div>

<script>
function confirmDelete() {
    if(confirm("⚠️ WARNING: Are you sure you want to permanently delete this customer and all their repair jobs? This action cannot be undone.")) {
        document.getElementById('deleteForm').submit();
    }
}
let lastMode = document.body.classList.contains('dark-mode');
const observer = new MutationObserver(() => {
    let currentMode = document.body.classList.contains('dark-mode');
    if (currentMode !== lastMode) {
        lastMode = currentMode;
        location.reload(); 
    }
});
observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
</script>

<?php
$jobsStmt->close();
include_once __DIR__ . '/chatbot.php';
?>

</body>

</html>