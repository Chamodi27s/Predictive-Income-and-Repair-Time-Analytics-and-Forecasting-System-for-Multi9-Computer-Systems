<?php
include 'db_config.php';
include 'navbar.php';

// 1. Get Phone Number from URL
$phone = isset($_GET['phone']) ? mysqli_real_escape_string($conn, $_GET['phone']) : '';
if (!$phone) {
    echo "<script>window.location='job_list.php';</script>";
    exit();
}

$is_edit = isset($_GET['edit']); 

/* ===============================
    DATA UPDATE (SAVE) SECTION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    mysqli_query($conn,"UPDATE customer SET customer_name='$name', email='$email', address='$address' WHERE phone_number='$phone'");

    if (isset($_POST['warranty_status'])) {
        foreach ($_POST['warranty_status'] as $id => $status) {
            $id = mysqli_real_escape_string($id);
            $status = mysqli_real_escape_string($conn, $status);
            $desc = mysqli_real_escape_string($conn, $_POST['device_desc'][$id]);
            
            $image_sql = "";
            if (!empty($_FILES['device_image']['name'][$id])) {
                $target_dir = "uploads/devices/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $img_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['device_image']['name'][$id]);
                move_uploaded_file($_FILES['device_image']['tmp_name'][$id], $target_dir . $img_name);
                $image_sql = ", device_image='$img_name'";
            }

            mysqli_query($conn,"UPDATE job_device SET warranty_status='$status', description='$desc' $image_sql WHERE job_device_id='$id'");
        }
    }
    
    echo "<script>
        alert('Changes saved successfully!');
        window.location.href='customer_details.php?phone=" . urlencode($phone) . "';
    </script>";
    exit();
}

/* ===============================
    FETCH DATA
================================ */
$customer_res = mysqli_query($conn,"SELECT * FROM customer WHERE phone_number='$phone'");
$customer = mysqli_fetch_assoc($customer_res);

$latest_job_res = mysqli_query($conn, "SELECT job_no FROM job WHERE phone_number='$phone' ORDER BY job_no DESC LIMIT 1");
$latest_job_data = mysqli_fetch_assoc($latest_job_res);
$current_job_no = isset($latest_job_data['job_no']) ? $latest_job_data['job_no'] : '';

$jobs = mysqli_query($conn,"SELECT job.*, technicians.name AS tech 
                            FROM job 
                            LEFT JOIN technicians ON job.technician_id = technicians.technician_id 
                            WHERE job.phone_number='$phone' 
                            ORDER BY job.job_no DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - <?= htmlspecialchars($phone) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2ecc71; --primary-hover: #27ae60; --primary-dark: #229954;
            --success: #10b981; --success-hover: #059669; --danger: #ef4444;
            --warning: #f59e0b; --secondary: #64748b; --bg-main: #f8fafc;
            --card-bg: #ffffff; --text-main: #1a202c; --text-dark: #0f172a;
            --text-muted: #475569; --border: #e2e8f0; --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08); --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body { background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%); font-family: 'Inter', sans-serif; padding: 120px 40px 40px 40px; color: var(--text-main); line-height: 1.6; transition: background 0.3s ease; }

        /* ===== DARK MODE CSS ===== */
        body.dark-mode {
            background: #0f172a !important;
            color: #f1f5f9 !important;
        }

        body.dark-mode .card {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }

        body.dark-mode .card-header {
            border-bottom-color: #334155 !important;
        }

        body.dark-mode h2, body.dark-mode h3, body.dark-mode .device-name, body.dark-mode label {
            color: #ffffff !important;
        }

        body.dark-mode input, body.dark-mode textarea, body.dark-mode select {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        body.dark-mode input[readonly], body.dark-mode textarea[readonly] {
            background: #1e293b !important;
            opacity: 0.8;
        }

        body.dark-mode .device-box {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .action-bar {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        body.dark-mode .btn-secondary {
            background: #334155 !important;
            color: #ffffff !important;
        }

        /* ===== LIGHT MODE STYLES (ORIGINAL) ===== */
        .container { max-width: 1200px; margin: 0 auto; padding-bottom: 120px; }
        .page-header { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 36px 40px; border-radius: 20px; margin-bottom: 32px; box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4); color: white; }
        .page-header h1 { font-size: 34px; font-weight: 800; margin-bottom: 10px; display: flex; align-items: center; gap: 12px; }
        .card { background: var(--card-bg); padding: 36px; border-radius: 20px; margin-bottom: 28px; box-shadow: var(--shadow-md); border: 1px solid var(--border); transition: all 0.3s ease; animation: fadeIn 0.5s ease-out; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid var(--border); }
        h2, h3 { font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 12px; }
        .form-group { margin-bottom: 24px; }
        label { font-weight: 700; font-size: 13px; text-transform: uppercase; color: var(--text-dark); margin-bottom: 10px; display: block; }
        input, textarea, select { width: 100%; padding: 14px 18px; border: 2px solid var(--border); border-radius: 12px; font-size: 15px; font-family: 'Inter', sans-serif; background: white; }
        input[readonly], textarea[readonly] { background: #f1f5f9; cursor: not-allowed; }
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .device-box { background: #fafafa; padding: 28px; border-radius: 16px; margin-top: 20px; border: 2px solid var(--border); }
        .device-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; }
        .device-name { font-size: 19px; font-weight: 800; }
        .status-badge { padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 13px; text-transform: uppercase; }
        .status-warranty { background: #dcfce7; color: #14532d; border: 2px solid #86efac; }
        .status-no-warranty { background: #fee2e2; color: #7f1d1d; border: 2px solid #fca5a5; }
        .btn { padding: 14px 28px; border-radius: 12px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; border: none; }
        .btn-success { background: var(--success); color: white; }
        .btn-outline { border: 2px solid var(--primary); color: var(--primary); background: transparent; }
        .btn-secondary { background: #e2e8f0; color: var(--text-dark); }
        .action-bar { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: white; padding: 20px 36px; border-radius: 100px; box-shadow: var(--shadow-lg); display: flex; gap: 16px; z-index: 1000; border: 2px solid var(--border); }
        .img-preview-container { margin-bottom: 20px; }
        .device-img { width: 150px; height: 150px; object-fit: cover; border-radius: 12px; border: 2px solid var(--border); cursor: pointer; transition: 0.3s; }
        .device-img:hover { transform: scale(1.05); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>👤 Customer Profile Details</h1>
        <p>Complete customer information and job history</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="card">
            <div class="card-header">
                <h2>📋 Personal Information</h2>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['customer_name'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?>>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" value="<?= htmlspecialchars($phone) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?>>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($customer['address'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?>>
                </div>
            </div>
        </div>

        <?php while($job = mysqli_fetch_assoc($jobs)): ?>
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>📑 Job #<?= $job['job_no'] ?></h3>
                    <div style="display: flex; gap: 20px; margin-top: 10px;">
                        <span>📅 <strong><?= date("M d, Y", strtotime($job['job_date'])) ?></strong></span>
                        <span>👨‍🔧 Technician: <strong><?= htmlspecialchars($job['tech'] ?? 'Not Assigned') ?></strong></span>
                    </div>
                </div>
            </div>

            <?php
            $jno = $job['job_no'];
            $devices_res = mysqli_query($conn,"SELECT * FROM job_device WHERE job_no='$jno'");
            while($d = mysqli_fetch_assoc($devices_res)):
                $is_warranty = (strtolower($d['warranty_status'] ?? '') == 'warranty');
            ?>
            <div class="device-box">
                <div class="device-header">
                    <div class="device-info">
                        <div class="device-name">📱 <?= htmlspecialchars($d['device_name']) ?></div>
                        <div style="font-weight: 600;"><strong>Issue:</strong> <?= htmlspecialchars($d['issue_name']) ?></div>
                    </div>
                    <span class="status-badge <?= $is_warranty ? 'status-warranty' : 'status-no-warranty' ?>">
                        <?= $is_warranty ? '✓' : '✗' ?> <?= htmlspecialchars($d['warranty_status']) ?>
                    </span>
                </div>

                <?php if(!empty($d['device_image'])): ?>
                <div class="img-preview-container">
                    <label>Device Photo</label>
                    <a href="uploads/devices/<?= $d['device_image'] ?>" target="_blank">
                        <img src="uploads/devices/<?= $d['device_image'] ?>" class="device-img" alt="Device Image">
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Service Notes</label>
                    <textarea name="device_desc[<?= $d['job_device_id'] ?>]" <?= !$is_edit?'readonly':'' ?>><?= htmlspecialchars($d['description']) ?></textarea>
                </div>
                
                <?php if($is_edit): ?>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Warranty Status</label>
                            <select name="warranty_status[<?= $d['job_device_id'] ?>]">
                                <option value="Warranty" <?= ($d['warranty_status']=='Warranty')?'selected':'' ?>>✓ Warranty</option>
                                <option value="No Warranty" <?= ($d['warranty_status']=='No Warranty')?'selected':'' ?>>✗ No Warranty</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= !empty($d['device_image']) ? 'Update' : 'Upload' ?> Device Image</label>
                            <input type="file" name="device_image[<?= $d['job_device_id'] ?>]" accept="image/*">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endwhile; ?>

        <div class="action-bar">
            <?php if(!empty($current_job_no)): ?>
                <a href="jobsheet.php?job_no=<?= $current_job_no ?>" class="btn btn-outline" target="_blank">📄 Print Job Sheet</a>
                <a href="duration.php?job_no=<?= urlencode($current_job_no) ?>" class="btn btn-secondary">⏱️ Time Duration</a>
            <?php endif; ?>

            <?php if(!$is_edit): ?>
                <a href="?phone=<?= $phone ?>&edit=1" class="btn" style="background: var(--primary); color: white;">✏️ Edit Details</a>
            <?php else: ?>
                <button type="submit" class="btn btn-success">💾 Save Changes</button>
                <a href="?phone=<?= $phone ?>" class="btn btn-secondary">✕ Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
// --- AUTO REFRESH ON MODE CHANGE ---
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

</body>
<?php include 'chatbot.php'; ?>
</html>