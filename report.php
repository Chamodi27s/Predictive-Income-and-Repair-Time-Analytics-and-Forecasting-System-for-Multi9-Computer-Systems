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

// 3. Device Types Breakdown
$deviceQuery = "SELECT device_name as item_category, COUNT(*) as count FROM job_device GROUP BY device_name ORDER BY count DESC LIMIT 5";
$deviceResult = $conn->query($deviceQuery);
$deviceData = [];
$totalDevices = 0;
while($row = $deviceResult->fetch_assoc()) {
    $deviceData[] = $row;
    $totalDevices += $row['count'];
}

// 4. මාසික ආදායම් ප්‍රස්ථාරය සඳහා දත්ත
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
    <title>Business Report - Green Edition</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-green: #1b5e20; /* Dark Green */
            --accent-green: #2e7d32; /* Medium Green */
            --light-green: #e8f5e9; /* Very Light Green for BG */
            --white: #ffffff;
            --border: #c8e6c9;
        }

        body { 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            background-color: #f1f8e9; 
            margin: 0; padding: 0; 
            color: #263238;
        }

        .container { 
            max-width: 1100px; 
            margin: 80px auto 40px auto; 
            background: var(--white); 
            padding: 40px; 
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.1);
            border: 1px solid var(--border);
        }

        /* Floating Button - Green Gradient */
        .float-download-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white; padding: 16px 32px; border-radius: 50px;
            border: none; cursor: pointer; font-weight: 600;
            box-shadow: 0 8px 20px rgba(27, 94, 32, 0.3); z-index: 9999;
            display: flex; align-items: center; gap: 12px; font-size: 16px;
            transition: all 0.3s ease;
        }
        .float-download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(27, 94, 32, 0.4);
        }

        /* Report Header */
        .report-header { 
            display: none; /* Hidden on Web */
            border-bottom: 4px solid var(--primary-green);
            padding-bottom: 20px; margin-bottom: 30px;
        }

        .header-title {
            text-align: center; color: var(--primary-green);
            margin-bottom: 40px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px;
        }

        /* Stats Cards - Green Style */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .card { 
            background: var(--white); padding: 30px; border-radius: 12px; 
            text-align: center; border: 1px solid var(--border);
            transition: 0.3s; position: relative; overflow: hidden;
        }
        .card::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 5px;
            background: var(--accent-green);
        }
        .card h3 { margin: 0; font-size: 13px; color: #546e7a; text-transform: uppercase; letter-spacing: 1.5px; }
        .card .value { font-size: 32px; font-weight: 800; color: var(--primary-green); margin-top: 15px; }

        /* Charts Layout */
        .charts-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 30px; }
        .chart-wrapper { 
            background: var(--white); border: 1px solid var(--border); 
            padding: 25px; border-radius: 12px; 
        }
        .section-title { 
            font-size: 18px; font-weight: 700; color: var(--primary-green); 
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        .section-title::before {
            content: ""; display: inline-block; width: 4px; height: 20px; background: var(--accent-green); border-radius: 2px;
        }

        /* Modern Table */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 12px; background: var(--light-green); font-size: 13px; color: var(--primary-green); }
        .data-table td { padding: 15px 12px; border-bottom: 1px solid #f1f8e9; font-size: 14px; }

        /* Green Progress Bar */
        .progress-bar { height: 10px; background: #f1f8e9; border-radius: 10px; overflow: hidden; margin-top: 6px; }
        .progress-fill { height: 100%; border-radius: 10px; transition: width 1s ease-in-out; }

        @media print {
            nav, .navbar, .float-download-btn { display: none !important; }
            body { background: white; padding: 0; }
            .container { 
                margin: 0 !important; top: 0 !important; position: absolute !important;
                box-shadow: none; width: 100%; max-width: 100%; padding: 20px; border: none;
            }
            .report-header { display: flex; justify-content: space-between; align-items: center; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .charts-grid { grid-template-columns: 1fr; }
            .card { border: 1px solid #c8e6c9 !important; background: #f9fdf9 !important; }
            @page { size: A4; margin: 1cm; }
        }

        @media (max-width: 850px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<button onclick="window.print()" class="float-download-btn">
    <span>🖨️</span> SAVE AS PDF
</button>

<div class="container">
    <div class="report-header">
        <div>
            <h1 style="margin:0; color: #1b5e20; font-size: 28px;">BUSINESS PERFORMANCE</h1>
            <p style="margin:5px 0; color: #2e7d32; font-weight: 600;">Official Monthly Report</p>
        </div>
        <div style="text-align: right; font-size: 14px; color: #37474f;">
            <strong>REPORT DATE:</strong> <?php echo date('d F, Y'); ?><br>
            <strong>STATUS:</strong> <span style="color: #2e7d32;">CONFIRMED</span>
        </div>
    </div>

    <h2 class="header-title">Monthly Performance Dashboard</h2>
    
    <div class="stats-grid">
        <div class="card">
            <h3>Total Items Received</h3>
            <div class="value"><?php echo number_format($totalRepairs); ?></div>
        </div>
        <div class="card">
            <h3>Monthly Revenue (<?php echo date('F'); ?>)</h3>
            <div class="value">Rs. <?php echo number_format($monthlyRevenue, 2); ?></div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-wrapper">
            <div class="section-title">Revenue Growth Trend</div>
            <div style="height: 350px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="chart-wrapper">
            <div class="section-title">Device Breakdown</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>CATEGORY</th>
                        <th>VOLUME</th>
                        <th>SHARE (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Green Shades for Progress Bars
                    $greenShades = ['#1b5e20', '#0d3cc6ff', '#cc9213ff', '#20dbc8ff', '#f50e1dff']; 
                    foreach($deviceData as $index => $device): 
                        $percentage = ($totalDevices > 0) ? ($device['count'] / $totalDevices) * 100 : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($device['item_category']); ?></strong></td>
                        <td><?php echo $device['count']; ?> Units</td>
                        <td>
                            <div style="font-size: 12px; font-weight: bold; color: var(--primary-green);"><?php echo round($percentage); ?>%</div>
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
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line', 
    data: {
        labels: <?php echo json_encode($months); ?>, 
        datasets: [{
            label: 'Revenue (Rs.)',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#2e7d32', // Accent Green
            backgroundColor: 'rgba(46, 125, 50, 0.1)', // Light Green Fill
            fill: true,
            tension: 0.4,
            borderWidth: 4,
            pointRadius: 6,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#1b5e20',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { 
                display: true,
                labels: { color: '#1b5e20', font: { weight: 'bold' } }
            }
        },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: '#e8f5e9' },
                ticks: { color: '#2e7d32' }
            },
            x: { 
                grid: { display: false },
                ticks: { color: '#2e7d32' }
            }
        }
    }
});
</script>

</body>
</html>