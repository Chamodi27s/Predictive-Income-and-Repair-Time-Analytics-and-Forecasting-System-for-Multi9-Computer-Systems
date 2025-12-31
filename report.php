<?php
include 'db_config.php';
include 'navbar.php';

// 1. Total Repairs ලබා ගැනීම
$totalRepairsQuery = "SELECT COUNT(*) as total FROM job_device";
$totalRepairsResult = $conn->query($totalRepairsQuery);
$totalRepairs = $totalRepairsResult->fetch_assoc()['total'] ?? 0;

// 2. Monthly Revenue ලබා ගැනීම
$currentMonth = date('m');
$currentYear = date('Y');
$revenueQuery = "SELECT SUM(grand_total) as total_rev FROM invoice WHERE MONTH(invoice_date) = '$currentMonth' AND YEAR(invoice_date) = '$currentYear'";
$revenueResult = $conn->query($revenueQuery);
$monthlyRevenue = $revenueResult->fetch_assoc()['total_rev'] ?? 0;

// 3. Stock Analytics ලබා ගැනීම (අලුත් කොටස)
$stockSummaryQuery = "SELECT SUM(quantity) as total_qty, SUM(quantity * unit_price) as total_value FROM stock";
$stockSummaryResult = $conn->query($stockSummaryQuery);
$stockDataSummary = $stockSummaryResult->fetch_assoc();
$totalStockQty = $stockDataSummary['total_qty'] ?? 0;
$totalStockValue = $stockDataSummary['total_value'] ?? 0;

// 4. Device Types Breakdown
$deviceQuery = "SELECT device_name as item_category, COUNT(*) as count FROM job_device GROUP BY device_name ORDER BY count DESC LIMIT 5";
$deviceResult = $conn->query($deviceQuery);
$deviceData = [];
$totalDevices = 0;
while($row = $deviceResult->fetch_assoc()) {
    $deviceData[] = $row;
    $totalDevices += $row['count'];
}

// 5. මාසික ආදායම් ප්‍රස්ථාරය සඳහා දත්ත
$monthlyRevQuery = "SELECT MONTHNAME(invoice_date) as month, SUM(grand_total) as total 
                    FROM invoice 
                    WHERE YEAR(invoice_date) = '$currentYear'
                    GROUP BY MONTH(invoice_date) 
                    ORDER BY MONTH(invoice_date) ASC";
$monthlyRevResult = $conn->query($monthlyRevQuery);

$months = [];
$revenues = [];

while($row = $monthlyRevResult->fetch_assoc()) {
    $months[] = $row['month'];
    $revenues[] = $row['total'];
}

if(empty($months)) {
    $months = [date('F')];
    $revenues = [0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Report - Executive Edition</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-green: #1b5e20;
            --accent-green: #2e7d32;
            --light-green: #f1f8e9;
            --white: #ffffff;
            --border: #c8e6c9;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        body { 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            background-color: #f9fdf9; 
            margin: 0; padding: 0; 
            color: #263238;
            padding-top: 120px;
            padding-left: 40px;
            padding-right: 40px;
        }

        .container { 
            max-width: 1100px; 
            margin: 20px auto 40px auto; 
            background: var(--white); 
            padding: 40px; 
            border-radius: 16px;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid var(--border);
        }

        .float-download-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white; padding: 16px 32px; border-radius: 50px;
            border: none; cursor: pointer; font-weight: 600;
            box-shadow: 0 8px 20px rgba(27, 94, 32, 0.3); z-index: 9999;
            display: flex; align-items: center; gap: 12px;
            transition: all 0.3s ease;
        }
        .float-download-btn:hover { transform: translateY(-3px); scale: 1.05; }

        .header-title {
            text-align: center; color: var(--primary-green);
            margin-bottom: 40px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px;
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        
        .card { 
            background: var(--white); 
            padding: 25px; 
            border-radius: 15px; 
            text-align: center; 
            border: 1px solid var(--border);
            position: relative; 
            overflow: hidden;
            transition: all 0.4s ease;
             box-shadow: 0 15px 30px rgba(27, 94, 32, 0.15);
        }

        .card:hover { transform: translateY(-5px); border-color: var(--accent-green); }

        .card h3 { margin: 0; font-size: 11px; color: #546e7a; text-transform: uppercase; letter-spacing: 1px; }
        .card .value { font-size: 28px; font-weight: 800; color: var(--primary-green); margin-top: 10px; }

        .charts-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 30px; margin-bottom: 30px; }
        .chart-wrapper { background: var(--white); border: 1px solid var(--border); padding: 25px; border-radius: 12px; }
        
        .section-title { font-size: 18px; font-weight: 700; color: var(--primary-green); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ""; display: inline-block; width: 4px; height: 20px; background: var(--accent-green); border-radius: 2px; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { text-align: left; padding: 12px; background: var(--light-green); font-size: 12px; color: var(--primary-green); }
        .data-table td { padding: 12px; border-bottom: 1px solid #f1f8e9; font-size: 14px; }

        .progress-bar { height: 8px; background: #eee; border-radius: 10px; overflow: hidden; margin-top: 6px; }
        .progress-fill { height: 100%; border-radius: 10px; }

@media print {

    /* Force Hide Header Branding Bar */
    header,
    header *,
    .header,
    .header *,
    .brand,
    .brand *,
    .topbar,
    .topbar *,
    .site-header,
    .site-header *,
    .button,
    .button *,
    .logo,
    .logo * {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Remove green background block */
    [style*="background"],
    [class*="bg"],
    .bg-dark,
    .bg-success,
    .bg-header {
        background: transparent !important;
        box-shadow: none !important;
    }

    /* Move report to very top */
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* ===== Device Breakdown PRINT FIX ===== */

    .dashboard {
        display: flex !important;
        align-items: flex-start !important;
    }
    .float-download-btn {
        display: none !important;
    }

    .main-content {
        width: 70% !important;
    }

    .device-breakdown {
        width: 30% !important;
        margin-left: 20px !important;
        page-break-inside: avoid !important;
    }

}



    </style>
</head>
<body>

<button onclick="window.print()" class="float-download-btn">
    <span>🖨️</span> SAVE AS PDF
</button>

<div class="container">
    <h2 class="header-title">Business & Inventory Analytics Report</h2>
    
    <div class="stats-grid">
        <div class="card">
            <h3>Total Repairs</h3>
            <div class="value"><?php echo number_format($totalRepairs); ?></div>
        </div>
        <div class="card">
            <h3>Monthly Revenue</h3>
            <div class="value">Rs. <?php echo number_format($monthlyRevenue, 2); ?></div>
        </div>
        <div class="card">
            <h3>Warehouse Stock</h3>
            <div class="value"><?php echo number_format($totalStockQty); ?> <small style="font-size:12px">Items</small></div>
        </div>
        <div class="card">
            <h3>Stock Value</h3>
            <div class="value">Rs. <?php echo number_format($totalStockValue, 0); ?></div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-wrapper">
            <div class="section-title">Revenue Growth Trend</div>
            <div style="height: 300px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="chart-wrapper">
            <div class="section-title">Device Breakdown</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>CATEGORY</th>
                        <th>VOL</th>
                        <th>SHARE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $greenShades = ['#1b5e20', '#1d46cfff', '#e8ce0fff', '#980df5ff', '#db350cff']; 
                    foreach($deviceData as $index => $device): 
                        $percentage = ($totalDevices > 0) ? ($device['count'] / $totalDevices) * 100 : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($device['item_category']); ?></strong></td>
                        <td><?php echo $device['count']; ?></td>
                        <td>
                            <div style="font-size: 11px; font-weight: bold;"><?php echo round($percentage); ?>%</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $greenShades[$index % 5]; ?>;"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="chart-wrapper">
        <div class="section-title">Current Inventory Status (Stock Level)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ITEM NAME</th>
                    <th>UNIT PRICE</th>
                    <th>QTY</th>
                    <th>STOCK VALUE</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stockTableQuery = "SELECT item_name, quantity, unit_price FROM stock ORDER BY quantity ASC LIMIT 15";
                $stockTableResult = $conn->query($stockTableQuery);
                while($item = $stockTableResult->fetch_assoc()): 
                    $subtotal = $item['quantity'] * $item['unit_price'];
                    $lowStock = ($item['quantity'] <= 5);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td>Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                    <td style="<?php echo $lowStock ? 'color:red; font-weight:bold;' : ''; ?>">
                        <?php echo $item['quantity']; ?>
                    </td>
                    <td>Rs. <?php echo number_format($subtotal, 2); ?></td>
                    <td>
                        <?php if($lowStock): ?>
                            <span style="color: #d32f2f; font-size: 11px; font-weight: bold;">⚠️ LOW STOCK</span>
                        <?php else: ?>
                            <span style="color: #2e7d32; font-size: 11px; font-weight: bold;">✓ OK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar', 
    data: {
        labels: <?php echo json_encode($months); ?>, 
        datasets: [{
            label: 'Revenue (Rs.)',
            data: <?php echo json_encode($revenues); ?>,
            backgroundColor: '#81c784', 
            borderColor: '#1b5e20',
            borderWidth: 1,
            borderRadius: 5,
            barPercentage: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>