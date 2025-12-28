<?php
include 'db_config.php';
include 'navbar.php';

// 1. Phone Number එක URL එකෙන් ලබා ගැනීම
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

    // Customer Update
    $name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    mysqli_query($conn,"UPDATE customer SET customer_name='$name', email='$email', address='$address' WHERE phone_number='$phone'");

    // Devices & Description Update
    if (isset($_POST['device_status'])) {
        foreach ($_POST['device_status'] as $id => $status) {
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

            mysqli_query($conn,"UPDATE job_device SET 
                device_status='$status', 
                description='$desc' 
                $image_sql 
                WHERE job_device_id='$id'");
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

$jobs = mysqli_query($conn,"SELECT job.*, technicians.name AS tech 
                            FROM job 
                            LEFT JOIN technicians ON job.technician_id = technicians.technician_id 
                            WHERE job.phone_number='$phone' 
                            ORDER BY job.job_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - <?= htmlspecialchars($phone) ?></title>
    <style>
        body { background:#f4f7fe; font-family: 'Inter', sans-serif; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: auto; }
        .card { background:#fff; padding: 25px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); position: relative; }
        .device-box { background:#f9fafb; padding: 20px; border-radius: 12px; margin-top: 15px; border: 1px solid #eee; }
        h2, h3 { color: #2c3e50; margin-top: 0; }
        label { font-weight: 600; display: block; margin-bottom: 8px; font-size: 0.85rem; color: #555; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; margin-bottom: 15px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
        
        .btn { background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; }
        
        .device-img { width: 100%; max-width: 300px; height: auto; border-radius: 10px; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 10px; }
        
        /* Status Colors */
        .status-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.75rem; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-repairing { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<div class="container">
    <form method="POST" enctype="multipart/form-data">
        
        <div class="card">
            <h2>👤 Customer Profile</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['customer_name']) ?>" <?= !$is_edit?'readonly':'' ?>>
                </div>
                <div>
                    <label>Phone Number</label>
                    <input type="text" value="<?= $customer['phone_number'] ?>" readonly style="background:#f8f9fa;">
                </div>
                <div>
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" <?= !$is_edit?'readonly':'' ?>>
                </div>
                <div>
                    <label>Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($customer['address']) ?>" <?= !$is_edit?'readonly':'' ?>>
                </div>
            </div>
        </div>

        <?php while($job = mysqli_fetch_assoc($jobs)): ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                <h3>📑 Job: <?= $job['job_no'] ?></h3>
                <span style="color: #888;">📅 <?= $job['job_date'] ?></span>
            </div>
            <p><strong>Technician Assigned:</strong> <?= $job['tech'] ?: '<span style="color:red">Not Assigned</span>' ?></p>

            <?php
            $job_no = $job['job_no'];
            $devices_res = mysqli_query($conn,"SELECT * FROM job_device WHERE job_no='$job_no'");
            while($d = mysqli_fetch_assoc($devices_res)):
                $status_class = 'status-' . strtolower($d['device_status']);
            ?>
            <div class="device-box">
                <div style="display: flex; justify-content: space-between;">
                    <strong>📱 <?= $d['device_name'] ?></strong>
                    <span class="status-badge <?= $status_class ?>"><?= $d['device_status'] ?></span>
                </div>
                <p style="margin: 10px 0; font-size: 0.9rem;"><strong>Issue:</strong> <?= htmlspecialchars($d['issue_name']) ?></p>

                <label>Notes / Description</label>
                <textarea name="device_desc[<?= $d['job_device_id'] ?>]" rows="2" <?= !$is_edit?'readonly':'' ?> placeholder="Add notes about device condition..."><?= htmlspecialchars($d['description']) ?></textarea>

                <?php if($is_edit): ?>
                <label>Change Status</label>
                <select name="device_status[<?= $d['job_device_id'] ?>]">
                    <option value="Pending" <?= $d['device_status']=='Pending'?'selected':'' ?>>Pending</option>
                    <option value="Repairing" <?= $d['device_status']=='Repairing'?'selected':'' ?>>Repairing</option>
                    <option value="Completed" <?= $d['device_status']=='Completed'?'selected':'' ?>>Completed</option>
                </select>

                <label>Update Device Photo</label>
                <input type="file" name="device_image[<?= $d['job_device_id'] ?>]">
                <?php endif; ?>

                <div style="margin-top: 10px;">
                    <label>Current Photo:</label>
                    <?php if(!empty($d['device_image'])): ?>
                        <a href="uploads/devices/<?= $d['device_image'] ?>" target="_blank">
                            <img src="uploads/devices/<?= $d['device_image'] ?>" class="device-img" alt="Device">
                        </a>
                    <?php else: ?>
                        <div style="padding: 20px; background: #eee; border-radius: 10px; text-align: center; color: #999;">No image uploaded</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endwhile; ?>

        <div style="position: sticky; bottom: 20px; text-align: right; z-index: 100;">
            <?php if(!$is_edit): ?>
                <a href="?phone=<?= $phone ?>&edit=1" class="btn">✏️ Edit Profile & Jobs</a>
            <?php else: ?>
                <button type="submit" class="btn" style="background: #28a745;">💾 Save All Changes</button>
                <a href="?phone=<?= $phone ?>" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>

    </form>
</div>

</body>
</html>