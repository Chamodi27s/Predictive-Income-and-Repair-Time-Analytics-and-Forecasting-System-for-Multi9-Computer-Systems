<?php
include 'db_config.php';
include 'navbar.php';

date_default_timezone_set("Asia/Colombo");
$currentMonth = date('n'); 
$currentYear = date('Y');

// --- 1. Basic Statistical Calculations ---
$totalRepairs = $conn->query("SELECT COUNT(*) as total FROM job_device")->fetch_assoc()['total'] ?? 0;

$monthlyRevenue = $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total_rev FROM invoice WHERE MONTH(invoice_date) = $currentMonth AND YEAR(invoice_date) = $currentYear AND invoice_date != '0000-00-00'")->fetch_assoc()['total_rev'] ?? 0;

$stockDataSummary = $conn->query("SELECT SUM(quantity) as total_qty, SUM(quantity * unit_price) as total_value FROM stock")->fetch_assoc();
$totalStockQty = $stockDataSummary['total_qty'] ?? 0;
$totalStockValue = $stockDataSummary['total_value'] ?? 0;

// --- 2. Top Performing Devices ---
$deviceResult = $conn->query("SELECT device_name as item_category, COUNT(*) as count FROM job_device GROUP BY device_name ORDER BY count DESC LIMIT 5");
$deviceData = []; $totalDevices = 0;
while($row = $deviceResult->fetch_assoc()) { $deviceData[] = $row; $totalDevices += $row['count']; }

// --- 3. Revenue Trend Analysis ---
$sql_rev = "SELECT DATE_FORMAT(invoice_date, '%M') as month_name, SUM(grand_total) as total 
            FROM invoice 
            WHERE invoice_date != '0000-00-00' AND invoice_date IS NOT NULL 
            GROUP BY YEAR(invoice_date), MONTH(invoice_date) 
            ORDER BY YEAR(invoice_date) ASC, MONTH(invoice_date) ASC";
$monthlyRevResult = $conn->query($sql_rev);
$months = []; $revenues = [];
if($monthlyRevResult && $monthlyRevResult->num_rows > 0) {
    while($row = $monthlyRevResult->fetch_assoc()) { $months[] = $row['month_name']; $revenues[] = (float)$row['total']; }
} else { $months = [date('F')]; $revenues = [0]; }

// --- 4. Failure Pattern Analytics ---
$failureQuery = "SELECT issue_name, device_name, COUNT(*) as issue_count 
                 FROM job_device 
                 GROUP BY issue_name, device_name 
                 ORDER BY issue_count DESC LIMIT 5";
$failureResult = $conn->query($failureQuery);
$failurePatterns = [];
while($row = $failureResult->fetch_assoc()) { $failurePatterns[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Report | Smart Repair</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --primary: #2ecc71; --primary-dark: #27ae60; --bg-main: #f8fafc; --card-bg: #ffffff; --text-dark: #0f172a; --text-muted: #64748b; --border-color: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-main); padding: 140px 20px 40px 20px; color: var(--text-dark); }
        .page-container { max-width: 1200px; margin: 0 auto; }
        .page-header { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); padding: 40px; border-radius: 24px; margin-bottom: 40px; color: white; text-align: center; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); padding: 25px; border-radius: 15px; border: 1px solid var(--border-color); text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .stat-value { font-size: 24px; font-weight: 800; }
        .main-card { background: var(--card-bg); padding: 25px; border-radius: 20px; border: 1px solid var(--border-color); margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ''; width: 5px; height: 18px; background: var(--primary); border-radius: 10px; }
        
        /* Failure Alert Styling */
        .analytics-box { background: #fffcf0; border: 1px solid #fde68a; border-radius: 15px; padding: 20px; margin-bottom: 30px; }
        .pattern-chip { display: inline-block; background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 700; margin: 5px; border: 1px solid #fde68a; }

        .table-container { border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 12px; font-size: 12px; text-align: left; text-transform: uppercase; color: var(--text-muted); }
        td { padding: 12px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .btn-export { position: fixed; bottom: 30px; right: 30px; background: var(--primary-dark); color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        @media print { .btn-export, nav { display: none !important; } body { padding: 0; } }
    </style>
</head>
<body>

<button onclick="window.print()" class="btn-export">🖨️ Export to PDF / Print</button>

<div class="page-container">
    <div class="page-header">
        <h1>📊 Business Intelligence Report</h1>
        <p>Smart Repair Management Insight - <?php echo date('Y-m-d'); ?></p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card"><h3>Total Repairs</h3><div class="stat-value"><?php echo number_format($totalRepairs); ?></div></div>
        <div class="stat-card"><h3>Monthly Revenue</h3><div class="stat-value">Rs. <?php echo number_format($monthlyRevenue, 0); ?></div></div>
        <div class="stat-card"><h3>Stock Items</h3><div class="stat-value"><?php echo number_format($totalStockQty); ?></div></div>
        <div class="stat-card"><h3>Inventory Value</h3><div class="stat-value">Rs. <?php echo number_format($totalStockValue, 0); ?></div></div>
    </div>

    <!-- Failure Insights -->
    <div class="analytics-box">
        <h2 class="section-title" style="color: #92400e;">🔍 Device Failure Patterns Detected</h2>
        <p style="font-size: 14px; margin-bottom: 15px;">Automated data analysis has identified the following recurring technical issues:</p>
        <?php foreach($failurePatterns as $fp): ?>
            <span class="pattern-chip">
                📍 <strong><?php echo htmlspecialchars($fp['device_name']); ?></strong>: 
                <strong><?php echo htmlspecialchars($fp['issue_name']); ?></strong> (<?php echo $fp['issue_count']; ?> Cases)
            </span>
        <?php endforeach; ?>
    </div>

    <div class="charts-grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px;">
        <div class="main-card">
            <h2 class="section-title">Revenue Trend</h2>
            <div style="height: 280px;"><canvas id="revenueChart"></canvas></div>
        </div>

        <div class="main-card">
            <h2 class="section-title">Top Devices</h2>
            <div class="table-container">
                <table>
                    <thead><tr><th>Device Model</th><th>Job Count</th></tr></thead>
                    <tbody>
                        <?php foreach($deviceData as $device): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($device['item_category']); ?></strong></td>
                            <td><?php echo $device['count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Inventory -->
    <div class="main-card">
        <h2 class="section-title">Critical Inventory Alert</h2>
        <div class="table-container">
            <table>
                <thead><tr><th>Item Description</th><th>Stock Quantity</th><th>Inventory Status</th></tr></thead>
                <tbody>
                    <?php 
                    $lowStock = $conn->query("SELECT item_name, quantity FROM stock ORDER BY quantity ASC LIMIT 5");
                    while($s = $lowStock->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['item_name']); ?></td>
                        <td><?php echo $s['quantity']; ?></td>
                        <td style="color:#ef4444; font-weight:700;">LOW STOCK</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Monthly Revenue (Rs)',
                data: <?php echo json_encode($revenues); ?>,
                backgroundColor: '#2ecc71',
                borderRadius: 5
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Revenue (LKR)' }
                }
            }
        }
    });
</script>
</body>
</html>