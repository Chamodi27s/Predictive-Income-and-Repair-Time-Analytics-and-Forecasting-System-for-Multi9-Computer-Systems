<?php
include 'db_config.php';
include 'navbar.php';

$active_jobs = $conn
    ->query("SELECT COUNT(*) AS total FROM job WHERE job_status != 'Completed'")
    ->fetch_assoc()['total'];

$today_income = $conn
    ->query("SELECT SUM(income) AS total FROM cashbook WHERE date = CURDATE()")
    ->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multi9 Dashboard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            margin: 0;
        }

        .container {
            padding: 30px;
        }

        .cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            width: 220px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #374151;
        }

        .card p {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Management Dashboard</h1>

    <div class="cards">
        <div class="card">
            <h3>Active Jobs</h3>
            <p><?php echo $active_jobs; ?></p>
        </div>

        <div class="card">
            <h3>Today's Income</h3>
            <p>Rs. <?php echo number_format($today_income, 2); ?></p>
        </div>
    </div>
</div>

</body>
</html>
