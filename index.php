<?php
// Session එක දැනටමත් active ද කියා පරීක්ෂා කර පසුව start කරයි
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

/* --- FIX: SCROLL ENABLED --- */
html, body {
    height: auto; 
    min-height: 100%;
    overflow-y: auto; 
}

body {
    background: linear-gradient(135deg, #fafffd 0%, #e2fce9 100%);
    padding-top: 135px;
    display: flex;
    flex-direction: column;
    transition: background 0.3s ease;
}

/* ---------------- DASHBOARD DARK MODE FIX ---------------- */
body.dark-mode {
    background: linear-gradient(135deg, #020617, #0f172a) !important;
    color: #e2e8f0 !important;
}

/* Dark Mode එකේදී Card එකට Glass Effect ලබා දීම */
body.dark-mode .card {
    background: rgba(30, 41, 59, 0.4) !important;
    backdrop-filter: blur(14px) !important;
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4) !important;
}

/* Dark Mode එකේදී Card එක ඇතුළත අකුරු සුදු පැහැ ගැන්වීම */
body.dark-mode .card-title,
body.dark-mode .card-value,
body.dark-mode .card-footer,
body.dark-mode .welcome-section h1 {
    color: #ffffff !important;
}

body.dark-mode .sub-text, body.dark-mode .sub-text strong {
    color: #94a3b8 !important;
}

/* Icon Box සදහා Dark Mode වර්ණ */
body.dark-mode .icon-box {
    background: rgba(255, 255, 255, 0.05) !important;
}

/* ---------------- DEFAULT DASHBOARD STYLES ---------------- */
.main-container {
    max-width: 1400px;
    width: 96%;
    margin: 0 auto;
    height: auto; 
    display: flex;
    flex-direction: column;
    padding-bottom: 40px; 
}

.welcome-section {
    flex: 0 0 auto;
    margin-bottom: 25px;
}

.welcome-section h1 {
    font-size: 28px;
    font-weight: 700;
    color: #064e3b; 
    display: flex;
    align-items: center;
    gap: 10px;
}

.sub-text {
    font-size: 14px;
    color: #065f46;
    font-weight: 600;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); 
    grid-template-rows: auto; 
    gap: 25px;
    flex: 1; 
}

.card {
    background: #fff;
    border-radius: 24px;
    padding: 35px; 
    min-height: 220px; 
    width: 100%;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    border: 1px solid rgba(255,255,255,0.6); 
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.icon-box {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;     
}

.card-value {
    font-size: 40px; 
    font-weight: 800;
    margin-top: 10px;
}

.card-footer {
    font-size: 12px;
    opacity: 0.8;
    font-weight: 600;
    padding-top: 10px;
}

/* LIGHT MODE COLORS */
.bg-pending { border-left: 8px solid #f97316; }
.bg-pending .card-title, .bg-pending .card-value { color: #c2410c; }
.bg-pending .icon-box { color: #ea580c; background: #fff7ed; }

.bg-progress { border-left: 8px solid #3b82f6; }
.bg-progress .card-title, .bg-progress .card-value { color: #1d4ed8; }
.bg-progress .icon-box { color: #2563eb; background: #eff6ff; }

.bg-completed { border-left: 8px solid #22c55e; }
.bg-completed .card-title, .bg-completed .card-value { color: #15803d; }
.bg-completed .icon-box { color: #16a34a; background: #f0fdf4; }

.bg-customers { border-left: 8px solid #8b5cf6; }
.bg-customers .card-title, .bg-customers .card-value { color: #6d28d9; }
.bg-customers .icon-box { color: #7c3aed; background: #f5f3ff; }

.bg-revenue { border-left: 8px solid #06b6d4; }
.bg-revenue .card-title, .bg-revenue .card-value { color: #0e7490; }
.bg-revenue .icon-box { color: #0891b2; background: #ecfeff; }

.bg-lowstock { border-left: 8px solid #f43f5e; }
.bg-lowstock .card-title, .bg-lowstock .card-value { color: #be123c; }
.bg-lowstock .icon-box { color: #e11d48; background: #fff1f2; }

/* MOBILE RESPONSIVE */
@media screen and (max-width: 1024px) {
    body { padding-top: 110px; }
    .dashboard-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
}

@media screen and (max-width: 768px) {
    .dashboard-grid { grid-template-columns: 1fr; }
    .welcome-section h1 { font-size: 24px; }
    .card-value { font-size: 36px; }
}
</style>
</head>
<body>

<div class="main-container">
    <div class="welcome-section">
        <h1>
            <?php echo $greeting; ?>, Multi9
            <span style="font-size:32px;"><?php echo $icon; ?></span>
        </h1>
        <div class="sub-text">
            Business Overview for 
            <strong style="color:#064e3b;">
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
            <div class="card-footer">Waiting for action</div>
        </div>

        <div class="card bg-progress">
            <div class="card-header">
                <span class="card-title">In Progress</span>
                <span class="icon-box">⌛</span>
            </div>
            <div class="card-value"><?php echo $inprogress_count; ?></div>
            <div class="card-footer">Currently working</div>
        </div>

        <div class="card bg-completed">
            <div class="card-header">
                <span class="card-title">Completed Today</span>
                <span class="icon-box">✅</span>
            </div>
            <div class="card-value"><?php echo $completed_count; ?></div>
            <div class="card-footer">Successfully done</div>
        </div>

        <div class="card bg-customers">
            <div class="card-header">
                <span class="card-title">Total Customers</span>
                <span class="icon-box">👥</span>
            </div>
            <div class="card-value"><?php echo $total_customers; ?></div>
            <div class="card-footer">Total Registered</div>
        </div>

        <div class="card bg-revenue">
            <div class="card-header">
                <span class="card-title">Revenue Today</span>
                <span class="icon-box">💰</span>
            </div>
            <div class="card-value">Rs.<?php echo number_format($revenue_today, 2); ?></div>
            <div class="card-footer">Daily Income</div>
        </div>

        <div class="card bg-lowstock">
            <div class="card-header">
                <span class="card-title">Low Stock</span>
                <span class="icon-box">⚠️</span>
            </div>
            <div class="card-value"><?php echo $low_stock_count; ?></div>
            <div class="card-footer">Items needing attention</div>
        </div>
    </div>
</div>

<script>
    // Theme එක Real-time check කිරීමට
    function syncDashboardTheme() {
        const theme = localStorage.getItem("darkMode");
        if (theme === "enabled") {
            document.body.classList.add("dark-mode");
        } else {
            document.body.classList.remove("dark-mode");
        }
    }
    
    // පිටුව load වන විට සහ theme වෙනස් වන විට ක්‍රියාත්මක කිරීම
    syncDashboardTheme();
    window.addEventListener('storage', syncDashboardTheme);
</script>

</body>
<?php include 'chatbot.php'; ?>
</html>