<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

/* ---------------- SAFE INCLUDES ---------------- */

if (file_exists(__DIR__ . "/db_config.php")) {
    include __DIR__ . "/db_config.php";
} else {
    die("db_config.php not found");
}

/*
 * Capture the navbar HTML instead of printing it before <!DOCTYPE html>.
 * It will be displayed inside a .no-print wrapper in the <body>.
 */
$navbarHtml = '';

if (file_exists(__DIR__ . "/navbar.php")) {
    ob_start();
    include __DIR__ . "/navbar.php";
    $navbarHtml = ob_get_clean();
}

/* ---------------- DB CHECK ---------------- */

if (!isset($conn)) {
    die("Database connection failed");
}

date_default_timezone_set("Asia/Colombo");

$currentMonth = date('n');
$currentYear  = date('Y');

/* ---------------- SAFE QUERY FUNCTION ---------------- */

function safeFetch($conn, $sql)
{
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return [];
}

/* ---------------- BASIC STATS ---------------- */

$totalRepairData = safeFetch(
    $conn,
    "SELECT COUNT(*) as total FROM job_device"
);

$totalRepairs = $totalRepairData['total'] ?? 0;

$monthlyRevenueData = safeFetch(
    $conn,
    "SELECT COALESCE(SUM(grand_total), 0) as total_rev
     FROM invoice
     WHERE MONTH(invoice_date) = $currentMonth
     AND YEAR(invoice_date) = $currentYear
     AND invoice_date != '0000-00-00'"
);

$monthlyRevenue = $monthlyRevenueData['total_rev'] ?? 0;

$stockDataSummary = safeFetch(
    $conn,
    "SELECT
        SUM(quantity) as total_qty,
        SUM(quantity * unit_price) as total_value
     FROM stock"
);

$totalStockQty   = $stockDataSummary['total_qty'] ?? 0;
$totalStockValue = $stockDataSummary['total_value'] ?? 0;

/* ---------------- TOP DEVICES ---------------- */

$deviceData   = [];
$totalDevices = 0;

$deviceResult = $conn->query("
    SELECT
        device_name as item_category,
        COUNT(*) as count
    FROM job_device
    GROUP BY device_name
    ORDER BY count DESC
    LIMIT 5
");

if ($deviceResult) {

    while ($row = $deviceResult->fetch_assoc()) {

        $deviceData[] = $row;
        $totalDevices += $row['count'];
    }
}

/* ---------------- REVENUE ANALYSIS ---------------- */

$months   = [];
$revenues = [];

$sql_rev = "
SELECT
    DATE_FORMAT(invoice_date, '%M') as month_name,
    SUM(grand_total) as total
FROM invoice
WHERE invoice_date != '0000-00-00'
AND invoice_date IS NOT NULL
GROUP BY YEAR(invoice_date), MONTH(invoice_date)
ORDER BY YEAR(invoice_date) ASC, MONTH(invoice_date) ASC
";

$monthlyRevResult = $conn->query($sql_rev);

if ($monthlyRevResult && $monthlyRevResult->num_rows > 0) {

    while ($row = $monthlyRevResult->fetch_assoc()) {

        $months[]   = $row['month_name'];
        $revenues[] = (float)$row['total'];
    }

} else {

    $months   = [date('F')];
    $revenues = [0];
}

/* ---------------- FAILURE PATTERN ANALYTICS ---------------- */

$failurePatterns = [];

$failureQuery = "
SELECT
    issue_name,
    device_name,
    COUNT(*) as issue_count
FROM job_device
GROUP BY issue_name, device_name
ORDER BY issue_count DESC
LIMIT 5
";

$failureResult = $conn->query($failureQuery);

if ($failureResult) {

    while ($row = $failureResult->fetch_assoc()) {

        $failurePatterns[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Business Report | Smart Repair</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --primary:#2ecc71;
    --primary-dark:#27ae60;
    --bg-main:#f8fafc;
    --card-bg:#ffffff;
    --text-dark:#0f172a;
    --text-muted:#64748b;
    --border-color:#e2e8f0;
}

body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    padding:140px 20px 40px 20px;
    color:var(--text-dark);
}

.page-container{
    max-width:1200px;
    margin:0 auto;
}

.page-header{
    background:linear-gradient(135deg,var(--primary) 0%,var(--primary-dark) 100%);
    padding:40px;
    border-radius:24px;
    margin-bottom:40px;
    color:white;
    text-align:center;
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.stat-card{
    background:var(--card-bg);
    padding:25px;
    border-radius:15px;
    border:1px solid var(--border-color);
    text-align:center;
    box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);
}

.stat-card h3{
    margin-bottom:10px;
    font-size:15px;
}

.stat-value{
    font-size:24px;
    font-weight:800;
}

.main-card{
    background:var(--card-bg);
    padding:25px;
    border-radius:20px;
    border:1px solid var(--border-color);
    margin-bottom:30px;
}

.section-title{
    font-size:18px;
    font-weight:800;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
}

.section-title::before{
    content:'';
    width:5px;
    height:18px;
    background:var(--primary);
    border-radius:10px;
}

/* ---------------- ANALYTICS ---------------- */

.analytics-box{
    background:#fffcf0;
    border:1px solid #fde68a;
    border-radius:15px;
    padding:20px;
    margin-bottom:30px;
}

.pattern-chip{
    display:inline-block;
    background:#fef3c7;
    color:#92400e;
    padding:6px 12px;
    border-radius:8px;
    font-size:13px;
    font-weight:700;
    margin:5px;
    border:1px solid #fde68a;
}

/* ---------------- TABLE ---------------- */

.table-container{
    border-radius:12px;
    border:1px solid var(--border-color);
    overflow:hidden;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f8fafc;
    padding:12px;
    font-size:12px;
    text-align:left;
    text-transform:uppercase;
    color:var(--text-muted);
}

td{
    padding:12px;
    font-size:14px;
    border-bottom:1px solid #f1f5f9;
}

/* ---------------- INVENTORY TABLE ---------------- */

.inventory-card{
    background:linear-gradient(135deg,#ffffff 0%,#f8fafc 100%);
    border:1px solid #e2e8f0;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
}

.inventory-subtitle{
    color:#64748b;
    font-size:14px;
    margin-bottom:20px;
}

.inventory-table-wrapper{
    overflow-x:auto;
    border-radius:16px;
    border:1px solid #e2e8f0;
}

.inventory-table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

.inventory-table thead{
    background:linear-gradient(135deg,#2ecc71 0%,#27ae60 100%);
}

.inventory-table th{
    color:white;
    padding:16px;
    font-size:13px;
    letter-spacing:0.5px;
    text-transform:uppercase;
    border:none;
}

.inventory-table td{
    padding:18px 16px;
    border-bottom:1px solid #f1f5f9;
    font-size:14px;
    font-weight:600;
}

.inventory-table tbody tr{
    transition:0.25s ease;
}

.inventory-table tbody tr:hover{
    background:#f0fff4;
    transform:scale(1.01);
}

.item-name{
    color:#0f172a;
    font-weight:700;
}

.qty-badge{
    display:inline-block;
    min-width:45px;
    text-align:center;
    padding:7px 12px;
    border-radius:999px;
    font-weight:800;
    font-size:13px;
}

.qty-warning{
    background:#fef3c7;
    color:#92400e;
}

.qty-danger{
    background:#fee2e2;
    color:#b91c1c;
}

.status-badge{
    display:inline-block;
    padding:7px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    letter-spacing:0.5px;
}

.status-badge.warning{
    background:#fef3c7;
    color:#92400e;
}

.status-badge.danger{
    background:#fee2e2;
    color:#b91c1c;
}

/* ---------------- BUTTON ---------------- */

.btn-export{
    position:fixed;
    bottom:30px;
    right:30px;
    background:var(--primary-dark);
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:10px;
    font-weight:700;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:8px;
}

/* ---------------- RESPONSIVE ---------------- */

@media(max-width:900px){

    .charts-grid{
        grid-template-columns:1fr !important;
    }
}

@media(max-width:768px){

    .inventory-table th,
    .inventory-table td{
        padding:12px;
        font-size:12px;
    }

    .status-badge{
        font-size:11px;
    }
}

/* ---------------- PRINT ---------------- */

@media print{

    .no-print,
    .btn-export,
    #report-navbar,
    nav,
    .navbar,
    .topbar,
    .sidebar,
    [class*="navbar"],
    [id*="navbar"]{
        display:none !important;
    }

    body{
        background:#ffffff !important;
        padding:0 !important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    .page-container{
        width:100%;
        max-width:none;
        margin:0;
    }

    .page-header,
    .stat-card,
    .main-card,
    .analytics-box,
    table,
    tr{
        break-inside:avoid;
        page-break-inside:avoid;
    }
}

@page{
    size:A4;
    margin:12mm;
}

</style>

</head>

<body>

<?php if ($navbarHtml !== ''): ?>
<div id="report-navbar" class="no-print">
<?php echo $navbarHtml; ?>
</div>
<?php endif; ?>

<button onclick="window.print()" class="btn-export">
🖨️ Export to PDF / Print
</button>

<div class="page-container">

<!-- HEADER -->

<div class="page-header">

<h1>
📊 Business Intelligence Report
</h1>

<p>
Smart Repair Management Insight -
<?php echo date('Y-m-d'); ?>
</p>

</div>

<!-- QUICK STATS -->

<div class="stats-grid">

<div class="stat-card">
<h3>Total Repairs</h3>
<div class="stat-value">
<?php echo number_format($totalRepairs); ?>
</div>
</div>

<div class="stat-card">
<h3>Monthly Revenue</h3>
<div class="stat-value">
Rs. <?php echo number_format($monthlyRevenue,0); ?>
</div>
</div>

<div class="stat-card">
<h3>Stock Items</h3>
<div class="stat-value">
<?php echo number_format($totalStockQty); ?>
</div>
</div>

<div class="stat-card">
<h3>Inventory Value</h3>
<div class="stat-value">
Rs. <?php echo number_format($totalStockValue,0); ?>
</div>
</div>

</div>

<!-- FAILURE ANALYTICS -->

<div class="analytics-box">

<h2 class="section-title" style="color:#92400e;">
🔍 Device Failure Patterns Detected
</h2>

<p style="font-size:14px;margin-bottom:15px;">
Automated data analysis has identified the following recurring technical issues:
</p>

<?php foreach($failurePatterns as $fp): ?>

<span class="pattern-chip">

📍

<strong>
<?php echo htmlspecialchars($fp['device_name']); ?>
</strong>

:

<strong>
<?php echo htmlspecialchars($fp['issue_name']); ?>
</strong>

(<?php echo $fp['issue_count']; ?> Cases)

</span>

<?php endforeach; ?>

</div>

<!-- CHART + TABLE -->

<div class="charts-grid" style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;">

<!-- REVENUE CHART -->

<div class="main-card">

<h2 class="section-title">
Revenue Trend
</h2>

<div style="height:280px;">
<canvas id="revenueChart"></canvas>
</div>

</div>

<!-- TOP DEVICES -->

<div class="main-card">

<h2 class="section-title">
Top Devices
</h2>

<div class="table-container">

<table>

<thead>

<tr>
<th>Device Model</th>
<th>Job Count</th>
</tr>

</thead>

<tbody>

<?php foreach($deviceData as $device): ?>

<tr>

<td>
<strong>
<?php echo htmlspecialchars($device['item_category']); ?>
</strong>
</td>

<td>
<?php echo $device['count']; ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- INVENTORY -->

<div class="main-card inventory-card">

<h2 class="section-title">
🚨 Critical Inventory Alert
</h2>

<p class="inventory-subtitle">
Items that require immediate restocking attention.
</p>

<div class="inventory-table-wrapper">

<table class="inventory-table">

<thead>

<tr>
<th>Item Description</th>
<th>Available Qty</th>
<th>Status</th>
</tr>

</thead>

<tbody>

<?php

$lowStock = $conn->query("
SELECT item_name, quantity
FROM stock
ORDER BY quantity ASC
LIMIT 5
");

if ($lowStock) :

while($s = $lowStock->fetch_assoc()):

$isVeryLow = $s['quantity'] <= 2;
?>

<tr>

<td class="item-name">
📦 <?php echo htmlspecialchars($s['item_name']); ?>
</td>

<td>

<span class="qty-badge <?php echo $isVeryLow ? 'qty-danger' : 'qty-warning'; ?>">

<?php echo $s['quantity']; ?>

</span>

</td>

<td>

<?php if($isVeryLow): ?>

<span class="status-badge danger">
⚠ CRITICAL
</span>

<?php else: ?>

<span class="status-badge warning">
LOW STOCK
</span>

<?php endif; ?>

</td>

</tr>

<?php
endwhile;
endif;
?>

</tbody>

</table>

</div>

</div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function(){

const canvas = document.getElementById('revenueChart');

if(!canvas){
    return;
}

const ctx = canvas.getContext('2d');

new Chart(ctx, {

    type:'bar',

    data:{

        labels: <?php echo json_encode($months); ?>,

        datasets:[{

            label:'Monthly Revenue (Rs)',

            data: <?php echo json_encode($revenues); ?>,

            backgroundColor:'#2ecc71',

            borderRadius:5
        }]
    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        scales:{

            y:{
                beginAtZero:true,

                title:{
                    display:true,
                    text:'Revenue (LKR)'
                }
            }
        }
    }
});

});

</script>

<?php if (file_exists(__DIR__ . "/chatbot.php")): ?>
<div class="no-print">
<?php include __DIR__ . "/chatbot.php"; ?>
</div>
<?php endif; ?>

</body>

</html>