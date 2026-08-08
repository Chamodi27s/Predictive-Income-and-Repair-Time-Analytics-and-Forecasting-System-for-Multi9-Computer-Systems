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
    DATA UPDATE & DELETE SECTION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Find all jobs for this customer to delete their devices first
        $job_res = mysqli_query($conn, "SELECT job_no FROM job WHERE phone_number='$phone'");
        while($row = mysqli_fetch_assoc($job_res)) {
            $jno = $row['job_no'];
            // Optionally delete images from server here if needed
            mysqli_query($conn, "DELETE FROM job_device WHERE job_no='$jno'");
        }
        mysqli_query($conn, "DELETE FROM job WHERE phone_number='$phone'");
        mysqli_query($conn, "DELETE FROM customer WHERE phone_number='$phone'");
        
        echo "<script>
            alert('Customer and all related jobs deleted successfully!');
            window.location.href='add_customer.php';
        </script>";
        exit();
    }

    $name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    mysqli_query($conn,"UPDATE customer SET customer_name='$name', email='$email', address='$address' WHERE phone_number='$phone'");

    if (isset($_POST['warranty_status'])) {
        foreach ($_POST['warranty_status'] as $id => $status) {
            $id = mysqli_real_escape_string($conn, $id);
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

        .container { max-width: 1400px; margin: 0 auto; padding-bottom: 50px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; margin-bottom:30px; color:var(--primary); font-weight:700; text-decoration:none; font-size:15px; transition:0.3s; padding:10px 20px; background:rgba(46,204,113,0.1); border-radius:100px; }
        .back-link:hover { background:rgba(46,204,113,0.2); }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap:wrap; gap:20px; }
        .page-header h1 { font-size: 32px; font-weight: 800; display: flex; align-items: center; gap: 12px; margin-bottom:5px; color:var(--text-dark); }
        .page-header p { color:var(--text-muted); font-size:15px; margin-left:45px; }
        
        .profile-layout { display: grid; grid-template-columns: 380px 1fr; gap: 40px; align-items: start; }
        @media(max-width:992px) { .profile-layout { grid-template-columns: 1fr; } }
        
        .card { background: var(--card-bg); padding: 36px; border-radius: 24px; margin-bottom: 28px; box-shadow: var(--shadow-lg); border: 1px solid var(--border); transition: all 0.3s ease; animation: fadeIn 0.5s ease-out; }
        .profile-sidebar { position: sticky; top: 40px; }
        
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border); }
        h2, h3 { font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 24px; }
        label { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing:0.5px; color: var(--secondary); margin-bottom: 8px; display: block; }
        input, textarea, select { width: 100%; padding: 14px 18px; border: 2px solid var(--border); border-radius: 14px; font-size: 15px; font-family: 'Inter', sans-serif; background: transparent; color:var(--text-main); transition:0.3s; }
        input:focus, textarea:focus, select:focus { border-color:var(--primary); outline:none; box-shadow:0 0 0 4px rgba(46,204,113,0.1); }
        input[readonly], textarea[readonly] { border-color:transparent; background: rgba(0,0,0,0.02); }
        body.dark-mode input[readonly], body.dark-mode textarea[readonly] { background: rgba(255,255,255,0.02); }
        
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .device-box { background: rgba(0,0,0,0.01); padding: 28px; border-radius: 16px; margin-top: 20px; border: 1px solid var(--border); }
        .device-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; flex-wrap:wrap; gap:15px; }
        .device-name { font-size: 19px; font-weight: 800; }
        
        .status-badge { padding: 8px 16px; border-radius: 100px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:6px; }
        .status-warranty { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-no-warranty { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        
        .btn { padding: 14px 28px; border-radius: 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; border: none; font-size:14px; transition:0.3s; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 10px 20px -10px rgba(0,0,0,0.3); }
        .btn-success { background: var(--success); color: white; }
        .btn-outline { border: 2px solid var(--border); color: var(--text-main); background: transparent; }
        .btn-secondary { background: var(--border); color: var(--text-dark); }
        
        .device-img { width: 120px; height: 120px; object-fit: cover; border-radius: 16px; border: 2px solid var(--border); cursor: pointer; transition: 0.3s; }
        .device-img:hover { transform: scale(1.05); }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="container">
    <a href="add_customer.php" class="back-link">
        <i class="ph-bold ph-arrow-left"></i> Back to Dashboard
    </a>
    
    <div class="page-header">
        <div>
            <h1><i class="ph-fill ph-user-circle" style="color:var(--primary);"></i> Customer Profile</h1>
            <p>Manage customer details and repair history</p>
        </div>
        
        <div class="top-actions" style="display:flex; gap:12px; flex-wrap:wrap;">
            <?php if(!empty($current_job_no)): ?>
                <a href="jobsheet.php?job_no=<?= $current_job_no ?>" class="btn btn-outline" target="_blank"><i class="ph-bold ph-printer"></i> Print Job Sheet</a>
            <?php endif; ?>

            <?php if($is_edit): ?>
                <button type="submit" form="customerForm" class="btn btn-success"><i class="ph-bold ph-floppy-disk"></i> Save Changes</button>
                <a href="?phone=<?= $phone ?>" class="btn btn-secondary"><i class="ph-bold ph-x"></i> Cancel</a>
            <?php else: ?>
                <a href="?phone=<?= $phone ?>&edit=true" class="btn btn-outline" style="background: var(--card-bg); border-color:var(--primary); color:var(--primary);"><i class="ph-bold ph-pencil-simple"></i> Edit Details</a>
                <button type="button" onclick="confirmDelete()" class="btn" style="background:var(--danger); color:white;"><i class="ph-bold ph-trash"></i> Delete</button>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" id="customerForm">
        <div class="profile-layout">
            
            <!-- Left Column: Sticky Profile Card -->
            <div class="profile-sidebar">
                <div class="card" style="padding:40px 30px; text-align:center;">
                    <div style="width:120px; height:120px; background:linear-gradient(135deg, rgba(46,204,113,0.1), rgba(46,204,113,0.2)); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px auto; border:4px solid var(--card-bg); box-shadow:0 0 20px rgba(46,204,113,0.2);">
                        <i class="ph-fill ph-user" style="font-size:56px; color:var(--primary);"></i>
                    </div>
                    
                    <div class="form-group" style="text-align:left;">
                        <label>Full Name</label>
                        <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['customer_name'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?> style="font-weight:700; font-size:18px;">
                    </div>
                    <div class="form-group" style="text-align:left;">
                        <label>Phone Number (Primary ID)</label>
                        <input type="text" value="<?= htmlspecialchars($phone) ?>" readonly style="font-family:monospace; font-size:16px;">
                    </div>
                    <div class="form-group" style="text-align:left;">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?>>
                    </div>
                    <div class="form-group" style="text-align:left; margin-bottom:0;">
                        <label>Residential Address</label>
                        <textarea name="address" <?= !$is_edit?'readonly':'' ?> style="resize:none; height:100px; line-height:1.6;"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
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
                            <h3 style="font-size:18px; margin-bottom:8px;"><i class="ph-fill ph-file-text" style="color:var(--primary);"></i> Job #<?= $job['job_no'] ?></h3>
                            <div style="display: flex; gap: 20px; font-size:14px;">
                                <span style="display:flex; align-items:center; gap:6px; color:var(--text-muted);"><i class="ph-fill ph-calendar-blank"></i> <strong><?= date("M d, Y", strtotime($job['job_date'])) ?></strong></span>
                                <span style="display:flex; align-items:center; gap:6px; color:var(--text-muted);"><i class="ph-fill ph-wrench"></i> Tech: <strong style="color:var(--text-main);"><?= htmlspecialchars($job['tech'] ?? 'Not Assigned') ?></strong></span>
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
                                <div class="device-name" style="display:flex; align-items:center; gap:10px;"><i class="ph-fill ph-device-mobile" style="color:var(--primary); font-size:28px; background:rgba(46,204,113,0.1); padding:8px; border-radius:12px;"></i> <?= htmlspecialchars($d['device_name']) ?></div>
                                <div style="margin-top:12px; color:var(--secondary); font-size:14px;"><strong style="color:var(--text-main);">Reported Issue:</strong> <?= htmlspecialchars($d['issue_name']) ?></div>
                            </div>
                            <span class="status-badge <?= $is_warranty ? 'status-warranty' : 'status-no-warranty' ?>">
                                <?php if($is_warranty): ?>
                                    <i class="ph-bold ph-shield-check"></i>
                                <?php else: ?>
                                    <i class="ph-bold ph-shield-warning"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($d['warranty_status']) ?>
                            </span>
                        </div>

                        <?php if(!empty($d['device_image'])): ?>
                        <div class="img-preview-container" style="margin-bottom: 24px;">
                            <label>Device Photo</label>
                            <a href="uploads/devices/<?= $d['device_image'] ?>" target="_blank">
                                <img src="uploads/devices/<?= $d['device_image'] ?>" class="device-img" alt="Device Image">
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Service Notes & Description</label>
                            <textarea name="device_desc[<?= $d['job_device_id'] ?>]" <?= !$is_edit?'readonly':'' ?> style="min-height:80px;"><?= htmlspecialchars($d['description']) ?></textarea>
                        </div>
                        
                        <?php if($is_edit): ?>
                            <div class="grid-2">
                                <div class="form-group">
                                    <label>Warranty Status</label>
                                    <select name="warranty_status[<?= $d['job_device_id'] ?>]">
                                        <option value="Warranty" <?= ($d['warranty_status']=='Warranty')?'selected':'' ?>>Warranty</option>
                                        <option value="No Warranty" <?= ($d['warranty_status']=='No Warranty')?'selected':'' ?>>No Warranty</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><?= !empty($d['device_image']) ? 'Update' : 'Upload' ?> Device Image</label>
                                    <input type="file" name="device_image[<?= $d['job_device_id'] ?>]" accept="image/*" style="padding:10px;">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </form>
    
    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
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

</body>

</html>