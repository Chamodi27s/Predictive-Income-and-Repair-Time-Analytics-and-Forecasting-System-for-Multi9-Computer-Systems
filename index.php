<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db_config.php';
include 'navbar.php';

date_default_timezone_set('Asia/Colombo');
$today = date('Y-m-d');
$hour  = date('H');

$greeting = "Welcome";
$icon = "";

if ($hour < 12) {
    $greeting = "Good Morning";
    $icon = "☀️";
} elseif ($hour < 17) {
    $greeting = "Good Afternoon";
    $icon = "🌤️";
} else {
    $greeting = "Good Evening";
    $icon = "🌙";
}

/* Queries */
$pending_count = $conn->query("SELECT COUNT(*) c FROM job_device WHERE device_status='Pending'")->fetch_assoc()['c'];
$inprogress_count = $conn->query("SELECT COUNT(*) c FROM job_device WHERE device_status='In Progress'")->fetch_assoc()['c'];
$completed_count = $conn->query("
    SELECT COUNT(*) c
    FROM job_device jd
    JOIN job j ON jd.job_no=j.job_no
    WHERE jd.device_status='Completed'
    AND j.job_date='$today'
")->fetch_assoc()['c'];
$total_customers = $conn->query("SELECT COUNT(*) c FROM customer")->fetch_assoc()['c'];
$revenue_today = $conn->query("SELECT SUM(income) total FROM cashbook WHERE DATE(date)='$today'")->fetch_assoc()['total'] ?? 0;
$low_stock_count = $conn->query("SELECT COUNT(*) c FROM stock WHERE quantity BETWEEN 1 AND 5")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | Multi9</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

/* BODY – kill flex, fix navbar overlap */
body{
    background:#f0fdf4;
    padding-top:100px;
    display:block !important;
}

/* CONTAINER */
.main-container{
    width:94%;
    max-width:1500px;
    margin:auto;
    padding-bottom:50px;
}

/* WELCOME */
.welcome-section{
    margin-bottom:30px;
}

.welcome-section h1{
    font-size:34px;
    font-weight:700;
    color:#1e293b;
    display:flex;
    align-items:center;
    gap:10px;
}

.sub-text{
    font-size:15px;
    color:#64748b;
}

/* GRID */
.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:30px;
    width:100%;
}

/* CARD */
.card{
    background:#fff;
    border-radius:16px;
    padding:25px;
    min-height:180px;
    box-shadow:0 4px 12px rgba(0,0,0,.06);
    border:1px solid rgba(0,0,0,.05);
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}

.card-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.card-title{
    font-size:15px;
    font-weight:600;
}

.icon-box{
    width:42px;
    height:42px;
    border-radius:10px;
    background:rgba(255,255,255,.7);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}

.card-value{
    font-size:38px;
    font-weight:700;
}

.card-footer{
    font-size:13px;
    color:#555;
}

/* COLORS */
.bg-pending{background:#dcfce7;border:1px solid #bbf7d0;}
.bg-progress{background:#fee2e2;border:1px solid #fecaca;}
.bg-completed{background:#fef9c3;border:1px solid #fde047;}
.bg-customers{background:#e0f2fe;border:1px solid #bae6fd;}
.bg-revenue{background:#ffedd5;border:1px solid #fed7aa;}
.bg-lowstock{background:#fef2f2;border:1px solid #fecaca;}

/* TABLET */
@media screen and (max-width:1100px){
    .dashboard-grid{
        grid-template-columns:repeat(2,1fr) !important;
    }
}

/* 🔥 MOBILE – FORCE STACK */
@media screen and (max-width:768px){

    body{
        padding-top:120px;
    }

    .dashboard-grid{
        grid-template-columns:1fr !important;
        gap:20px !important;
    }

    .card{
        width:100% !important;
        min-height:auto !important;
    }

    .welcome-section h1{
        font-size:26px !important;
    }
}
</style>
</head>

<body>

<div class="main-container">

    <div class="welcome-section">
        <h1>
            <?php echo $greeting; ?>, Multi9
            <span style="font-size:28px;"><?php echo $icon; ?></span>
        </h1>
        <div class="sub-text">
            Overview for
            <strong style="color:#059669;">
                <?php echo date('l, F j, Y'); ?>
            </strong>
        </div>
    </div>

    <div class="dashboard-grid">

        <div class="card bg-pending">
            <div class="card-header">
                <span class="card-title">Pending Repairs</span>
                <span class="icon-box">⏳</span>
            </div>
            <div class="card-value"><?php echo $pending_count; ?></div>
            <div class="card-footer">Current status</div>
        </div>

        <div class="card bg-progress">
            <div class="card-header">
                <span class="card-title">In Progress</span>
                <span class="icon-box">⌛</span>
            </div>
            <div class="card-value"><?php echo $inprogress_count; ?></div>
            <div class="card-footer">Working</div>
        </div>

        <div class="card bg-completed">
            <div class="card-header">
                <span class="card-title">Completed Today</span>
                <span class="icon-box">✅</span>
            </div>
            <div class="card-value"><?php echo $completed_count; ?></div>
            <div class="card-footer">Finished</div>
        </div>

        <div class="card bg-customers">
            <div class="card-header">
                <span class="card-title">Customers</span>
                <span class="icon-box">👥</span>
            </div>
            <div class="card-value"><?php echo $total_customers; ?></div>
            <div class="card-footer">Registered</div>
        </div>

        <div class="card bg-revenue">
            <div class="card-header">
                <span class="card-title">Revenue Today</span>
                <span class="icon-box">💰</span>
            </div>
            <div class="card-value">Rs.<?php echo number_format($revenue_today,2); ?></div>
            <div class="card-footer">Income</div>
        </div>

        <div class="card bg-lowstock">
            <div class="card-header">
                <span class="card-title" style="color:#b91c1c;">Low Stock</span>
                <span class="icon-box">⚠️</span>
            </div>
            <div class="card-value" style="color:#b91c1c;"><?php echo $low_stock_count; ?></div>
            <div class="card-footer">Qty 1–5</div>
        </div>

    </div>
</div>

</body>
</html>
