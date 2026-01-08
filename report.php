<?php
include 'db_config.php';
include 'navbar.php';

// වත්මන් දිනය සහ වේලාව අනුව පරාමිතීන් සැකසීම
$currentMonth = date('n'); 
$currentYear = date('Y');

// 1. Total Repairs ලබා ගැනීම
$totalRepairsQuery = "SELECT COUNT(*) as total FROM job_device";
$totalRepairsResult = $conn->query($totalRepairsQuery);
$totalRepairs = $totalRepairsResult->fetch_assoc()['total'] ?? 0;

// 2. Monthly Revenue ලබා ගැනීම (වත්මන් වසරේ සහ මාසයේ දත්ත නැතිනම් අවසාන දත්ත පෙන්වයි)
$revenueQuery = "SELECT COALESCE(SUM(grand_total), 0) as total_rev 
                FROM invoice 
                WHERE (MONTH(invoice_date) = $currentMonth AND YEAR(invoice_date) = $currentYear)
                OR invoice_date = (SELECT MAX(invoice_date) FROM invoice)";
$revenueResult = $conn->query($revenueQuery);
$monthlyRevenue = $revenueResult->fetch_assoc()['total_rev'] ?? 0;

// 3. Stock Analytics
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

// 5. මාසික ආදායම් ප්‍රස්ථාරය (YEAR සීමාව ඉවත් කර ඇත)
$monthlyRevQuery = "SELECT MONTHNAME(invoice_date) as month, SUM(grand_total) as total 
                    FROM invoice 
                    GROUP BY YEAR(invoice_date), MONTH(invoice_date) 
                    ORDER BY YEAR(invoice_date) ASC, MONTH(invoice_date) ASC";
$monthlyRevResult = $conn->query($monthlyRevQuery);

$months = [];
$revenues = [];

while($row = $monthlyRevResult->fetch_assoc()) {
    $months[] = $row['month'];
    $revenues[] = (float)$row['total'];
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
            background-color: #f4f7f4; 
            margin: 0; padding: 0; 
            color: #263238;
            padding-top: 100px;
        }

        .container { 
            max-width: 1200px; 
            margin: 20px auto 40px auto; 
            background: var(--white); 
            padding: 30px; 
            border-radius: 12px;
            box-shadow: 0 10px 30px var(--shadow);
        }

        .header-title {
            text-align: center; color: var(--primary-green);
            margin-bottom: 30px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px;
            border-bottom: 2px solid var(--light-green);
            padding-bottom: 15px;
        }

        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        
        .card { 
            background: var(--white); 
            padding: 20px; 
            border-radius: 12px; 
            text-align: center; 
            border-left: 5px solid var(--accent-green);
            box-shadow: 0 4px 12px var(--shadow);
            transition: 0.3s;
        }

        .card:hover { transform: translateY(-5px); }
        .card h3 { margin: 0; font-size: 12px; color: #78909c; text-transform: uppercase; }
        .card .value { font-size: 26px; font-weight: 800; color: var(--primary-green); margin-top: 8px; }

        .charts-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px; margin-bottom: 25px; }
        .chart-wrapper { 
            background: var(--white); 
            border: 1px solid var(--border); 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 2px 10px var(--shadow);
        }
        
        .section-title { font-size: 16px; font-weight: 700; color: var(--primary-green); margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 10px; background: var(--light-green); color: var(--primary-green); font-size: 12px; }
        .data-table td { padding: 10px; border-bottom: 1px solid #edf2ed; font-size: 13px; }

        .progress-bar { height: 6px; background: #eee; border-radius: 10px; overflow: hidden; margin-top: 5px; }
        .progress-fill { height: 100%; border-radius: 10px; }

        .float-download-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: #1b5e20; color: white; padding: 15px 25px; 
            border-radius: 30px; border: none; cursor: pointer; font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 1000;
        }

        @media print {
            .float-download-btn, navbar { display: none !important; }
            body { padding-top: 0; background: white; }
            .container { box-shadow: none; border: none; width: 100%; }
        }
    </style>
</head>
<body>

<button onclick="window.print()" class="float-download-btn">
    🖨️ SAVE AS PDF
</button>

<div class="container">
    <h2 class="header-title">Business Intelligence Report</h2>
    
    <div class="stats-grid">
        <div class="card">
            <h3>Total Repairs</h3>
            <div class="value"><?php echo number_format($totalRepairs); ?></div>
        </div>
        <div class="card">
            <h3>Revenue (Last Active Month)</h3>
            <div class="value">Rs. <?php echo number_format($monthlyRevenue, 2); ?></div>
        </div>
        <div class="card">
            <h3>Stock Quantity</h3>
            <div class="value"><?php echo number_format($totalStockQty); ?> Items</div>
        </div>
        <div class="card">
            <h3>Total Stock Value</h3>
            <div class="value">Rs. <?php echo number_format($totalStockValue, 0); ?></div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-wrapper">
            <div class="section-title">Revenue Growth Trend (Monthly)</div>
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
                    $colors = ['#2e7d32', '#1565c0', '#f9a825', '#6a1b9a', '#c62828']; 
                    foreach($deviceData as $index => $device): 
                        $percentage = ($totalDevices > 0) ? ($device['count'] / $totalDevices) * 100 : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($device['item_category']); ?></strong></td>
                        <td><?php echo $device['count']; ?></td>
                        <td>
                            <div style="font-size: 10px; font-weight: bold;"><?php echo round($percentage); ?>%</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $colors[$index % 5]; ?>;"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="chart-wrapper">
        <div class="section-title">Critical Inventory Status</div>
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
                $stockTableQuery = "SELECT item_name, quantity, unit_price FROM stock ORDER BY quantity ASC LIMIT 10";
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
                        <?php echo $lowStock ? '<span style="color:red">⚠️ LOW</span>' : '<span style="color:green">✓ OK</span>'; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar', // ප්‍රස්ථාරය Bar Chart එකක් ලෙස වෙනස් කරන ලදි
        data: {
            labels: <?php echo json_encode($months); ?>, 
            datasets: [{
                label: 'Revenue (Rs.)',
                data: <?php echo json_encode($revenues); ?>,
                backgroundColor: '#2e7d32', // බාර් වල වර්ණය
                borderColor: '#1b5e20',
                borderWidth: 1,
                borderRadius: 5, // බාර් වල කොන් රවුම් කිරීමට
                barPercentage: 0.5 // බාර් වල මහත පාලනය කිරීමට
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
});
</script>
</body>
</html>