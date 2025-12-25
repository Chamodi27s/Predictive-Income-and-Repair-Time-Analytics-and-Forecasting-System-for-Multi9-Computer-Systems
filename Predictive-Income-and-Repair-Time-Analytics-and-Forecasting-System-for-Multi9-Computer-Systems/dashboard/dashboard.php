<?php include("../config/db.php"); 

$active_jobs = $conn->query("SELECT COUNT(*) AS total FROM job WHERE job_status != 'Completed'")->fetch_assoc()['total'];
$today_income = $conn->query("SELECT SUM(income) AS total FROM cashbook WHERE date = CURDATE()")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Multi 9 Dashboard</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #2e7d32; height: 100vh; color: white; padding: 20px; }
        .main { flex: 1; padding: 30px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: inline-block; margin-right: 20px; }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>MULTI 9</h2>
    <a href="register.php" style="color:white; display:block; margin:10px 0;">New Registration</a>
    <a href="customer_list.php" style="color:white; display:block; margin:10px 0;">Job Records</a>
</div>
<div class="main">
    <h1>Management Dashboard</h1>
    <div class="card"><h3>Active Jobs</h3><p><?php echo $active_jobs; ?></p></div>
    <div class="card"><h3>Today's Income</h3><p>Rs. <?php echo number_format($today_income, 2); ?></p></div>
</div>
</body>
</html>