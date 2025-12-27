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
    <title>Repair Shop Dashboard Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-bg: #f4f7f6;
            --card-green: #d4edda;
            --card-blue: #d1ecf1;
            --white: #ffffff;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--primary-bg); 
            margin: 0; 
            padding: 10px; 
        }

        .container { max-width: 1200px; margin: auto; padding: 10px; }

        /* Stat Cards */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }

        .card { 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            text-align: center; 
            transition: transform 0.3s ease;
        }

        .card:hover { transform: translateY(-5px); }
        .card-repairs { background: var(--card-blue); border-left: 5px solid #0c5460; }
        .card-revenue { background: var(--card-green); border-left: 5px solid #155724; }
        .card h3 { margin: 0; font-size: 16px; color: #555; text-transform: uppercase; letter-spacing: 1px; }
        .card .value { font-size: 32px; font-weight: bold; margin: 10px 0; color: #222; }

        /* Charts Layout */
        .charts-grid { 
            display: grid; 
            grid-template-columns: 1.5fr 1fr; 
            gap: 25px; 
        }

        .chart-wrapper {
            background: var(--white); 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        /* Chart එකේ උස පාලනය කිරීම */
        .canvas-container {
            position: relative;
            height: 350px; 
            width: 100%;
        }

        .chart-wrapper h3 { margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; font-size: 18px; }

        /* Progress Bars */
        .progress-item { margin-bottom: 18px; }
        .progress-label { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; font-weight: 600; color: #444; }
        .progress-bar { height: 10px; background: #e9ecef; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 10px; transition: width 0.8s ease-in-out; }

        /* Responsive Mobile Settings */
        @media (max-width: 850px) {
            .charts-grid { grid-template-columns: 1fr; }
            .canvas-container { height: 300px; }
            body { padding: 5px; }
        }
    </style>
</head>
<body>

<div class="container">
    <h2 style="color: #2c3e50; margin-bottom: 25px; text-align: center;">Business Performance Report</h2>
    
    <div class="stats-grid">
        <div class="card card-repairs">
            <h3>Total Items Received</h3>
            <div class="value"><?php echo number_format($totalRepairs); ?></div>
            <small>All time recorded devices</small>
        </div>
        <div class="card card-revenue">
            <h3>Revenue (<?php echo date('F'); ?>)</h3>
            <div class="value">Rs. <?php echo number_format($monthlyRevenue, 2); ?></div>
            <small>Total billed amount</small>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-wrapper">
            <h3>Monthly Revenue Overview</h3>
            <div class="canvas-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="chart-wrapper">
            <h3>Device Popularity</h3>
            <?php 
            $colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6610f2']; 
            if(empty($deviceData)): 
                echo "<p style='color:#777; text-align:center;'>No device data found.</p>"; 
            else:
                foreach($deviceData as $index => $device): 
                    $percentage = ($totalDevices > 0) ? ($device['count'] / $totalDevices) * 100 : 0;
            ?>
                <div class="progress-item">
                    <div class="progress-label">
                        <span><?php echo htmlspecialchars($device['item_category']); ?></span>
                        <span><?php echo round($percentage); ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $colors[$index % 5]; ?>;"></div>
                    </div>
                </div>
            <?php 
                endforeach; 
            endif; 
            ?>
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
            label: 'Income (Rs.)',
            data: <?php echo json_encode($revenues); ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: '#155724',
            borderWidth: 1.5,
            borderRadius: 8,
            // බාර් එකේ පළල මෙතැනින් පාලනය වේ (0.1 - 0.4 අතර අගයක් සුදුසුයි)
            barPercentage: 0.3,
            categoryPercentage: 0.6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // CSS height එකට අනුව හැඩගැසීමට
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f0f0f0' },
                ticks: {
                    font: { size: 11 },
                    callback: function(value) { return 'Rs.' + value.toLocaleString(); }
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 12, weight: 'bold' } }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#333',
                padding: 10,
                callbacks: {
                    label: function(context) {
                        return ' Revenue: Rs. ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>