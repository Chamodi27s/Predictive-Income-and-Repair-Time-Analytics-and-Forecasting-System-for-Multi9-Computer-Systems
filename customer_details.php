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
    header("Location: customer_details.php?phone=$phone");
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --primary-hover: #3a56d4; --success: #2ec4b6; --danger: #e63946; --secondary: #64748b; --bg: #f8fafc; --card-bg: #ffffff; --text-main: #1e293b; --text-muted: #64748b; --border: #e2e8f0; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; padding: 40px 20px; color: var(--text-main); line-height: 1.6; margin: 0; }
        .container { max-width: 1000px; margin: auto; padding-bottom: 100px; }
        .card { background: var(--card-bg); padding: 32px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); }
        h2, h3 { font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        label { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; display: block; }
        input, textarea, select { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        input[readonly] { background-color: #f1f5f9; cursor: not-allowed; }
        .device-box { background: #fdfdfd; padding: 24px; border-radius: 12px; margin-top: 20px; border: 1px solid var(--border); }
        .btn { background: var(--primary); color: white; padding: 12px 24px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
        .btn-success { background: var(--success); }
        .btn-outline { background: transparent; color: var(--primary); border: 1.5px solid var(--primary); }
        .status-badge { padding: 6px 14px; border-radius: 30px; font-weight: 700; font-size: 11px; text-transform: uppercase; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-danger { background: #fee2e2; color: #991b1b; }
        .action-bar { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); padding: 16px 32px; border-radius: 100px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); display: flex; gap: 12px; z-index: 1000; border: 1px solid var(--border); }
        .grid-layout { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        
        /* Prediction Link Style */
        .predict-link {
            background: #f1f5f9;
            color: #4361ee;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        .predict-link:hover {
            background: #4361ee;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <form method="POST" enctype="multipart/form-data">
        <div class="card">
            <h2>👤 Customer Profile</h2>
            <div class="grid-layout">
                <div><label>Name</label><input type="text" name="customer_name" value="<?= htmlspecialchars($customer['customer_name'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?>></div>
                <div><label>Phone</label><input type="text" value="<?= htmlspecialchars($phone) ?>" readonly></div>
                <div><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?>></div>
                <div><label>Address</label><input type="text" name="address" value="<?= htmlspecialchars($customer['address'] ?? '') ?>" <?= !$is_edit?'readonly':'' ?>></div>
            </div>
        </div>

        <?php while($job = mysqli_fetch_assoc($jobs)): ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin:0;">📑 Job No: <?= $job['job_no'] ?></h3>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <?php if(!$is_edit): ?>
                    <a href="duration.php?job_no=<?= urlencode($job['job_no']) ?>" class="predict-link">
                        ⏱️ Predict Repair Duration
                    </a>
                    <?php endif; ?>
                    <span style="font-size: 12px; color: var(--text-muted);">📅 <?= date("M d, Y", strtotime($job['job_date'])) ?></span>
                </div>
            </div>
            <p><strong>Technician:</strong> <?= htmlspecialchars($job['tech'] ?? 'Not Assigned') ?></p>

            <?php
            $jno = $job['job_no'];
            $devices_res = mysqli_query($conn,"SELECT * FROM job_device WHERE job_no='$jno'");
            while($d = mysqli_fetch_assoc($devices_res)):
                $w_class = (strtolower($d['warranty_status']) == 'warranty') ? 'status-completed' : 'status-danger';
            ?>
            <div class="device-box">
                <div style="display: flex; justify-content: space-between;">
                    <div><strong>📱 <?= htmlspecialchars($d['device_name']) ?></strong><br><small>Issue: <?= htmlspecialchars($d['issue_name']) ?></small></div>
                    <span class="status-badge <?= $w_class ?>">🛡️ <?= htmlspecialchars($d['warranty_status']) ?></span>
                </div>
                <label style="margin-top:10px;">Notes</label>
                <textarea name="device_desc[<?= $d['job_device_id'] ?>]" rows="2" <?= !$is_edit?'readonly':'' ?>><?= htmlspecialchars($d['description']) ?></textarea>
                
                <?php if($is_edit): ?>
                    <div class="grid-layout">
                        <select name="warranty_status[<?= $d['job_device_id'] ?>]">
                            <option value="Warranty" <?= $d['warranty_status']=='Warranty'?'selected':'' ?>>Warranty</option>
                            <option value="No Warranty" <?= $d['warranty_status']=='No Warranty'?'selected':'' ?>>No Warranty</option>
                        </select>
                        <input type="file" name="device_image[<?= $d['job_device_id'] ?>]">
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endwhile; ?>

        <div class="action-bar">
            <?php if(!empty($current_job_no)): ?>
                <a href="jobsheet.php?job_no=<?= $current_job_no ?>" class="btn btn-outline" target="_blank">
                    📄 Print Current Job (#<?= $current_job_no ?>)
                </a>
            <?php endif; ?>

            <?php if(!$is_edit): ?>
                <a href="?phone=<?= $phone ?>&edit=1" class="btn">✏️ Edit</a>
            <?php else: ?>
                <button type="submit" class="btn btn-success">💾 Save All</button>
                <a href="?phone=<?= $phone ?>" class="btn">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>
</body>
</html>