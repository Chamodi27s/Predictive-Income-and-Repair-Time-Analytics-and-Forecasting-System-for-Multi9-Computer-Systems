<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db_config.php';
include 'navbar.php';

$today = date('Y-m-d');

// 🔥 වේලාව අනුව සුබ පැතුම සකස් කිරීම
date_default_timezone_set('Asia/Colombo'); // ශ්‍රී ලංකා වේලාව
$hour = date('H');
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

// SQL Queries
$pending_count = $conn->query("SELECT COUNT(*) as count FROM job_device WHERE device_status = 'Pending'")->fetch_assoc()['count'];
$inprogress_count = $conn->query("SELECT COUNT(*) as count FROM job_device WHERE device_status = 'In Progress'")->fetch_assoc()['count'];
$completed_count = $conn->query("SELECT COUNT(*) as count FROM job_device jd JOIN job j ON jd.job_no = j.job_no WHERE jd.device_status = 'Completed' AND j.job_date = '$today'")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'];
$revenue_today = $conn->query("SELECT SUM(income) as total FROM cashbook WHERE DATE(date) = '$today'")->fetch_assoc()['total'] ?? 0;

// 🔥 Quantity එක 1 ත් 5 ත් අතර පවතින අයිතම පමණක් ලබා ගැනීම
$low_stock_count = $conn->query("SELECT COUNT(*) as count FROM stock WHERE quantity >= 1 AND quantity <= 5")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Dashboard | Multi9</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body { 
            background-color: #f0fdf4;
            height: 100vh;
            overflow: hidden; 
            padding-top: 115px; /* Navbar එකට ඉඩ */
            display: flex;
            flex-direction: column;
        }

        .main-container {
            width: 94%;
            max-width: 1500px;
            margin: 0 auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            padding-bottom: 30px;
        }

        .welcome-section { 
            margin-bottom: 30px;
            padding-left: 5px;
            flex-shrink: 0;
        }

        h1 { 
            color: #1e293b; 
            font-weight: 700; 
            font-size: 36px;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sub-text { 
            color: #64748b; 
            font-size: 16px; 
            font-weight: 500;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 40px;
            height: 100%; 
            max-height: 500px; 
        }

        .card {
            border-radius: 16px;
            padding: 20px 25px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.05);
            background: white;
            transition: transform 0.2s;
            height: 100%; 
        }

        .card:hover { transform: translateY(-5px); }

        .bg-pending { background-color: #dcfce7; border: 1px solid #bbf7d0; }
        .bg-progress { background-color: #fee2e2; border: 1px solid #fecaca; }
        .bg-completed { background-color: #fef9c3; border: 1px solid #fde047; }
        .bg-customers { background-color: #e0f2fe; border: 1px solid #bae6fd; }
        .bg-revenue { background-color: #ffedd5; border: 1px solid #fed7aa; }
        .bg-lowstock { background-color: #fef2f2; border: 1px solid #fecaca; }

        .card-header { display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 15px; font-weight: 600; color: #374151; }
        .icon-box { background: rgba(255,255,255,0.6); width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .card-value { font-size: 36px; font-weight: 700; margin: 5px 0; color: #111; }
        .card-footer { font-size: 13px; color: #555; opacity: 0.9; }

        @media (max-height: 700px) {
            body { padding-top: 90px; }
            .dashboard-grid { gap: 20px; max-height: 420px; } 
            h1 { font-size: 28px; }
        }
    </style>
</head>
<body>

    <div class="main-container">
        
        <div class="welcome-section">
            <h1>
                <?php echo $greeting; ?>, Multi9 <span style="font-size: 32px;"><?php echo $icon; ?></span>
            </h1>
            <div class="sub-text">
                Overview for <span style="color: #059669; font-weight: 600;">
                    <?php echo date('l, F j, Y'); ?>
                </span>
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
                    <span class="card-title">In-progress Repairs</span>
                    <span class="icon-box">⌛</span>
                </div>
                <div class="card-value"><?php echo $inprogress_count; ?></div>
                <div class="card-footer">Actively working</div>
            </div>

            <div class="card bg-completed">
                <div class="card-header">
                    <span class="card-title">Completed Today</span>
                    <span class="icon-box">✅</span>
                </div>
                <div class="card-value"><?php echo $completed_count; ?></div>
                <div class="card-footer">Jobs finished today</div>
            </div>

            <div class="card bg-customers">
                <div class="card-header">
                    <span class="card-title">Total Customers</span>
                    <span class="icon-box">👥</span>
                </div>
                <div class="card-value"><?php echo $total_customers; ?></div>
                <div class="card-footer">Registered in system</div>
            </div>

            <div class="card bg-revenue">
                <div class="card-header">
                    <span class="card-title">Revenue Today</span>
                    <span class="icon-box">💰</span>
                </div>
                <div class="card-value">Rs.<?php echo number_format($revenue_today, 2); ?></div>
                <div class="card-footer">Today's total income</div>
            </div>
            
            <div class="card bg-lowstock">
                <div class="card-header">
                    <span class="card-title" style="color: #b91c1c;">Low Stock Alert</span>
                    <span class="icon-box">⚠️</span>
                </div>
                <div class="card-value" style="color: #b91c1c;"><?php echo $low_stock_count; ?></div>
                <div class="card-footer">Items between Qty 1-5</div>
            </div>

        </div>

    </div>

</body>
</html>