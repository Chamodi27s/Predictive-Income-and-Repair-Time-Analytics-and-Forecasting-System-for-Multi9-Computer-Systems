<?php
include 'db_config.php';
include 'navbar.php';

/* ===============================
   GET PHONE
================================ */
$phone = isset($_GET['phone']) ? mysqli_real_escape_string($conn, $_GET['phone']) : '';
if (!$phone) {
    header("Location: add_customer.php");
    exit();
}

/* ===============================
   EDIT MODE
================================ */
$is_edit = isset($_GET['edit']);

/* ===============================
   SAVE DATA
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CUSTOMER
    $name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    mysqli_query($conn,"
        UPDATE customer SET
        customer_name='$name',
        email='$email',
        address='$address'
        WHERE phone_number='$phone'
    ");

    // JOB DESCRIPTION + IMAGE
    foreach ($_POST['job_desc'] as $job_no => $desc) {

        $desc = mysqli_real_escape_string($conn, $desc);
        $image_sql = "";

        if (!empty($_FILES['job_image']['name'][$job_no])) {
            $img_name = time() . "_" . $_FILES['job_image']['name'][$job_no];
            move_uploaded_file(
                $_FILES['job_image']['tmp_name'][$job_no],
                "uploads/jobs/" . $img_name
            );
            $image_sql = ", job_image='$img_name'";
        }

        mysqli_query($conn,"
            UPDATE job SET description='$desc' $image_sql
            WHERE job_no='$job_no'
        ");
    }

    // DEVICE STATUS
    if (isset($_POST['device_status'])) {
        foreach ($_POST['device_status'] as $id => $status) {
            $status = mysqli_real_escape_string($conn, $status);
            mysqli_query($conn,"
                UPDATE job_device SET device_status='$status'
                WHERE job_device_id='$id'
            ");
        }
    }

    header("Location: customer_details.php?phone=$phone");
    exit();
}

/* ===============================
   FETCH CUSTOMER
================================ */
$customer = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM customer WHERE phone_number='$phone'")
);

/* ===============================
   FETCH JOBS
================================ */
$jobs = mysqli_query($conn,"
    SELECT job.*, technicians.name AS tech
    FROM job
    LEFT JOIN technicians ON job.technician_id=technicians.technician_id
    WHERE job.phone_number='$phone'
    ORDER BY job.job_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Customer Details</title>
<style>
body{background:#f0fdf4;font-family:Arial}
.card{background:#fff;padding:25px;border-radius:15px;margin-bottom:20px}
.device{background:#f9fafb;padding:12px;border-radius:10px;margin-top:10px}
input,textarea,select{width:100%;padding:8px;border-radius:8px}
.btn{background:#059669;color:#fff;padding:12px 25px;border:none;border-radius:10px;font-weight:bold}
</style>
</head>

<body>
<div class="container">

<form method="POST" enctype="multipart/form-data">

<!-- CUSTOMER -->
<div class="card">
<h2>Customer</h2>
<input name="customer_name" value="<?= $customer['customer_name'] ?>" <?= !$is_edit?'readonly':'' ?>>
<input name="email" value="<?= $customer['email'] ?>" <?= !$is_edit?'readonly':'' ?>>
<textarea name="address" <?= !$is_edit?'readonly':'' ?>><?= $customer['address'] ?></textarea>
</div>

<!-- JOBS -->
<?php while($job=mysqli_fetch_assoc($jobs)): ?>
<div class="card">
<h3>Job <?= $job['job_no'] ?></h3>
<p>Date: <?= $job['job_date'] ?></p>
<p>Status: <?= $job['job_status'] ?></p>
<p>Technician: <?= $job['tech'] ?: 'Not Assigned' ?></p>

<label>Description</label>
<textarea name="job_desc[<?= $job['job_no'] ?>]" <?= !$is_edit?'readonly':'' ?>>
<?= $job['description'] ?>
</textarea>

<?php if($job['job_image']): ?>
<img src="uploads/jobs/<?= $job['job_image'] ?>" width="200">
<?php endif; ?>

<?php if($is_edit): ?>
<input type="file" name="job_image[<?= $job['job_no'] ?>]">
<?php endif; ?>

<?php
$devices = mysqli_query($conn,"SELECT * FROM job_device WHERE job_no='{$job['job_no']}'");
while($d=mysqli_fetch_assoc($devices)):
?>
<div class="device">
<p>Device: <?= $d['device_name'] ?></p>
<p>Issue: <?= $d['issue_name'] ?></p>

<?php if($is_edit): ?>
<select name="device_status[<?= $d['job_device_id'] ?>]">
<option <?= $d['device_status']=='Pending'?'selected':'' ?>>Pending</option>
<option <?= $d['device_status']=='Repairing'?'selected':'' ?>>Repairing</option>
<option <?= $d['device_status']=='Completed'?'selected':'' ?>>Completed</option>
</select>
<?php else: ?>
<p>Status: <?= $d['device_status'] ?></p>
<?php endif; ?>

<p>Warranty: <?= $d['warranty_status'] ?: '-' ?></p>
</div>
<?php endwhile; ?>

</div>
<?php endwhile; ?>

<!-- BUTTONS -->
<?php if(!$is_edit): ?>
<a href="?phone=<?= $phone ?>&edit=1" class="btn">Edit</a>
<?php else: ?>
<button class="btn">Save</button>
<a href="?phone=<?= $phone ?>" class="btn" style="background:#9ca3af">Cancel</a>
<?php endif; ?>

</form>
</div>
</body>
</html>
