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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3a56d4;
            --success: #2ec4b6;
            --danger: #e63946;
            --secondary: #64748b;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Inter', sans-serif; 
            padding: 40px 20px; 
            color: var(--text-main);
            line-height: 1.6;
            margin: 0;
        }

        .container { max-width: 1000px; margin: auto; padding-bottom: 100px; }

        /* Professional Card Styling */
        .card { 
            background: var(--card-bg); 
            padding: 32px; 
            border-radius: 16px; 
            margin-bottom: 24px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
        }

        h2, h3 { 
            font-weight: 700; 
            letter-spacing: -0.02em; 
            color: var(--text-main); 
            margin-top: 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Form Elements */
        label { 
            font-weight: 600; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            color: var(--text-muted); 
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.05em;
        }

        input, textarea, select { 
            width: 100%; 
            padding: 12px 16px; 
            border: 1px solid var(--border); 
            border-radius: 10px; 
            margin-bottom: 20px; 
            background: #fff;
            transition: all 0.2s ease;
            font-size: 14px;
            color: var(--text-main);
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        input[readonly] { background-color: #f1f5f9; cursor: not-allowed; border-color: transparent; }

        /* Device Box */
        .device-box { 
            background: #fdfdfd; 
            padding: 24px; 
            border-radius: 12px; 
            margin-top: 20px; 
            border: 1px solid var(--border);
        }

        /* Buttons */
        .btn { 
            background: var(--primary); 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 10px; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px;
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-secondary { background: var(--secondary); }
        .btn-success { background: var(--success); }
        .btn-outline { 
            background: transparent; 
            color: var(--primary); 
            border: 1.5px solid var(--primary); 
        }
        .btn-outline:hover { background: rgba(67, 97, 238, 0.05); color: var(--primary-hover); }

        /* Status Badges */
        .status-badge { 
            padding: 6px 14px; 
            border-radius: 30px; 
            font-weight: 700; 
            font-size: 11px; 
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-repairing { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #dcfce7; color: #166534; }

        .device-img { 
            width: 100%; 
            max-width: 280px; 
            border-radius: 12px; 
            margin-top: 12px;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Floating Action Bar */
        .action-bar {
            position: fixed; 
            bottom: 30px; 
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            padding: 16px 32px;
            border-radius: 100px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255,255,255,0.3);
            z-index: 1000;
        }

        .grid-layout {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px;
        }

        .empty-img {
            padding: 30px; 
            background: #f1f5f9; 
            border-radius: 12px; 
            text-align: center; 
            color: var(--text-muted);
            font-size: 0.9rem;
            border: 2px dashed var(--border);
        }
    </style>
</head>
<body>

<div class="container">
    <form method="POST" enctype="multipart/form-data">
        
        <div class="card">
            <h2>👤 Customer Profile</h2>
            <div class="grid-layout">
                <div>
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['customer_name']) ?>" <?= !$is_edit?'readonly':'' ?>>
                </div>
                <div>
                    <label>Phone Number</label>
                    <input type="text" value="<?= $customer['phone_number'] ?>" readonly>
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
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin:0;">📑 Job ID: <?= $job['job_no'] ?></h3>
                <span style="font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">📅 <?= date("M d, Y", strtotime($job['job_date'])) ?></span>
            </div>
            
            <p style="margin-bottom: 20px;">
                <strong>Technician:</strong> 
                <?= $job['tech'] ? '<span style="color:var(--primary)">'.$job['tech'].'</span>' : '<span style="color:var(--danger)">Not Assigned</span>' ?>
            </p>

            <?php
            $job_no = $job['job_no'];
            $devices_res = mysqli_query($conn,"SELECT * FROM job_device WHERE job_no='$job_no'");
            while($d = mysqli_fetch_assoc($devices_res)):
                $status_class = 'status-' . strtolower($d['device_status']);
            ?>
            <div class="device-box">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main);">📱 <?= $d['device_name'] ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Issue: <?= htmlspecialchars($d['issue_name']) ?></div>
                    </div>
                    <span class="status-badge <?= $status_class ?>"><?= $d['device_status'] ?></span>
                </div>

                <label>Notes / Condition Report</label>
                <textarea name="device_desc[<?= $d['job_device_id'] ?>]" rows="2" <?= !$is_edit?'readonly':'' ?> placeholder="Add notes about device condition..."><?= htmlspecialchars($d['description']) ?></textarea>

                <?php if($is_edit): ?>
                    <div class="grid-layout">
                        <div>
                            <label>Update Status</label>
                            <select name="device_status[<?= $d['job_device_id'] ?>]">
                                <option value="Pending" <?= $d['device_status']=='Pending'?'selected':'' ?>>Pending</option>
                                <option value="Repairing" <?= $d['device_status']=='Repairing'?'selected':'' ?>>Repairing</option>
                                <option value="Completed" <?= $d['device_status']=='Completed'?'selected':'' ?>>Completed</option>
                            </select>
                        </div>
                        <div>
                            <label>New Device Photo</label>
                            <input type="file" name="device_image[<?= $d['job_device_id'] ?>]">
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 10px;">
                    <label>Inspection Photo</label>
                    <?php if(!empty($d['device_image'])): ?>
                        <a href="uploads/devices/<?= $d['device_image'] ?>" target="_blank">
                            <img src="uploads/devices/<?= $d['device_image'] ?>" class="device-img" alt="Device">
                        </a>
                    <?php else: ?>
                        <div class="empty-img">No visual documentation provided</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endwhile; ?>

        <div class="action-bar">
            <a href="jobsheet.php?phone=<?= $phone ?>" class="btn btn-outline" target="_blank">
                📄 Generate Job Sheet
            </a>

            <?php if(!$is_edit): ?>
                <a href="?phone=<?= $phone ?>&edit=1" class="btn">
                    ✏️ Edit Profile & Jobs
                </a>
            <?php else: ?>
                <button type="submit" class="btn btn-success">
                    💾 Save All Changes
                </button>
                <a href="?phone=<?= $phone ?>" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>

    </form>
</div>

</body>
</html>