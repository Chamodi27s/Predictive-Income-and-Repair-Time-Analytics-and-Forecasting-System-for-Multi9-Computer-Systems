<?php
include("../config/db.php");

if(!isset($_GET['job_no'])){
    die("Job No missing!");
}

$job_no = $conn->real_escape_string($_GET['job_no']);

// Fetch all details
$sql = "SELECT j.job_no, j.job_date, j.technician, j.job_status,
               c.customer_name, c.phone_number, c.address, c.email,
               jd.device_name, jd.model, jd.issue_name
        FROM job j
        LEFT JOIN customer c ON j.phone_number = c.phone_number
        LEFT JOIN job_device jd ON j.job_no = jd.job_no
        WHERE j.job_no='$job_no'";

$result = $conn->query($sql);

if($result->num_rows == 0){
    die("Job not found!");
}

$job = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Details - <?php echo $job['job_no']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2, h3 { margin-top: 0; }
        p { line-height: 1.6; }
        .back-btn { margin-top: 20px; display: inline-block; padding: 10px 20px; background: #2e7d32; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Job Details: <?php echo $job['job_no']; ?></h2>

    <h3>Job Info</h3>
    <p><strong>Date:</strong> <?php echo $job['job_date']; ?></p>
    <p><strong>Technician:</strong> <?php echo $job['technician']; ?></p>
    <p><strong>Status:</strong> <?php echo $job['job_status']; ?></p>

    <h3>Customer Info</h3>
    <p><strong>Name:</strong> <?php echo $job['customer_name']; ?></p>
    <p><strong>Phone:</strong> <?php echo $job['phone_number']; ?></p>
    <p><strong>Address:</strong> <?php echo $job['address']; ?></p>
    <p><strong>Email:</strong> <?php echo $job['email']; ?></p>

    <h3>Device Info</h3>
    <p><strong>Device:</strong> <?php echo $job['device_name']; ?></p>
    <p><strong>Model:</strong> <?php echo $job['model']; ?></p>
    <p><strong>Issue / Fault:</strong> <?php echo $job['issue_name'] ?? 'No issue recorded'; ?></p>

    <a href="../jobs/job_list.php" class="back-btn">Back to Job List</a>
</div>
</body>
</html>
