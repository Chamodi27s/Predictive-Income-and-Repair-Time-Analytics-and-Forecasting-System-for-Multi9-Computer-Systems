<?php
include 'db_config.php';
include 'navbar.php';

date_default_timezone_set("Asia/Colombo");
$currentMonth = date('n'); 
$currentYear = date('Y');

// Queries & Logics
$totalRepairs = $conn->query("SELECT COUNT(*) as total FROM job_device")->fetch_assoc()['total'] ?? 0;
$monthlyRevenue = $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total_rev FROM invoice WHERE MONTH(invoice_date) = $currentMonth AND YEAR(invoice_date) = $currentYear AND invoice_date != '0000-00-00'")->fetch_assoc()['total_rev'] ?? 0;
$stockDataSummary = $conn->query("SELECT SUM(quantity) as total_qty, SUM(quantity * unit_price) as total_value FROM stock")->fetch_assoc();
$totalStockQty = $stockDataSummary['total_qty'] ?? 0;
$totalStockValue = $stockDataSummary['total_value'] ?? 0;

$deviceResult = $conn->query("SELECT device_name as item_category, COUNT(*) as count FROM job_device GROUP BY device_name ORDER BY count DESC LIMIT 5");
$deviceData = []; $totalDevices = 0;
while($row = $deviceResult->fetch_assoc()) { $deviceData[] = $row; $totalDevices += $row['count']; }

$monthlyRevResult = $conn->query("SELECT DATE_FORMAT(invoice_date, '%M') as month_name, SUM(grand_total) as total FROM invoice WHERE invoice_date != '0000-00-00' AND invoice_date IS NOT NULL GROUP BY YEAR(invoice_date), MONTH(invoice_date) ORDER BY YEAR(invoice_date) ASC, MONTH(invoice_date) ASC");
$months = []; $revenues = [];
while($row = $monthlyRevResult->fetch_assoc()) { $months[] = $row['month_name']; $revenues[] = (float)$row['total']; }
if(empty($months)) { $months = [date('F')]; $revenues = [0]; }
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
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --danger: #ef4444;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
            padding: 140px 20px 40px 20px;
            color: var(--text-dark);
        }

        .page-container { max-width: 1200px; margin: 0 auto; }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 40px; border-radius: 24px; margin-bottom: 40px;
            box-shadow: 0 15px 35px rgba(46, 204, 113, 0.3); color: white; text-align: center;
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        
        .stat-card {
            background: var(--card-bg); 
            padding: 30px; 
            border-radius: 20px;
            border: 2px solid var(--border-color); 
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .stat-card h3 { font-size: 13px; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; }
        .stat-value { font-size: 28px; font-weight: 800; color: var(--text-dark); }

        .main-card {
            background: var(--card-bg); 
            padding: 30px; 
            border-radius: 24px;
            border: 2px solid var(--border-color); 
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            transition: all 0.4s ease;
        }

        .main-card:hover {
            border-color: var(--primary);
            box-shadow: 0 15px 30px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 22px; font-weight: 800; color: var(--text-dark); margin-bottom: 25px;
            display: flex; align-items: center; gap: 10px;
        }
        .section-title::before {
            content: ''; display: block; width: 6px; height: 24px; background: var(--primary); border-radius: 10px;
        }

        .table-container { border-radius: 16px; border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        
        th {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white; 
            padding: 18px; 
            font-size: 13px; 
            font-weight: 800; 
            text-transform: uppercase; 
            text-align: left;
            letter-spacing: 0.5px;
        }
        
        td { padding: 18px; font-size: 14px; border-bottom: 1px solid #f1f5f9; font-weight: 600; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f0fff4; transition: 0.2s; }

        .share-badge { background: #e8f5e9; color: #2e7d32; padding: 6px 12px; border-radius: 8px; font-weight: 800; font-size: 12px; border: 1px solid #c8e6c9; }
        .badge-low { background: #fef2f2; color: #dc2626; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid #fee2e2; }
        .badge-ok { background: #f0fdf4; color: #166534; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid #dcfce7; }

        .btn-export {
            position: fixed; bottom: 40px; right: 40px; 
            background: #1e293b; color: white; 
            border: none; padding: 18px 30px; border-radius: 15px; 
            font-weight: 800; cursor: pointer;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000;
            transition: 0.3s; display: flex; align-items: center; gap: 10px;
        }
        .btn-export:hover { transform: scale(1.05); background: #000; }

        @media (max-width: 992px) {
            .charts-grid { grid-template-columns: 1fr !important; }
        }

        /* --- PROFESSIONAL PDF/PRINT STYLES --- */
        @media print {
            body { background: white !important; padding: 0 !important; color: black !important; }
            .btn-export, nav, .navbar { display: none !important; } /* Hide UI elements */
            .page-container { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 10mm !important; }
            
            .page-header { 
                background: #f8fafc !important; color: black !important; 
                border: 1px solid #000 !important; box-shadow: none !important; 
                border-radius: 10px !important; margin-bottom: 20px !important;
            }
            
            .stat-card, .main-card { 
                box-shadow: none !important; border: 1px solid #ddd !important; 
                border-radius: 10px !important; transform: none !important;
                page-break-inside: avoid;
            }

            th { 
                background: #e2e8f0 !important; color: black !important; 
                border-bottom: 2px solid #000 !important; 
            }

            .section-title::before { background: #000 !important; }
            canvas { max-width: 100% !important; height: auto !important; }
            
            /* Professional Header for Print */
            .page-header h1 { color: black !important; }
            .page-header p { color: #555 !important; }
        }
    </style>
</head>
<body>

<button onclick="window.print()" class="btn-export" style="background: var(--primary-dark);">
    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
    SAVE AS PDF
</button>

<div class="page-container">
    <div class="page-header">
        <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 10px;">📊 Business Intelligence Report</h1>
        <p style="opacity: 0.9; font-weight: 500;">Comprehensive Store Performance & Analytics - Generated on <?php echo date('Y-m-d'); ?></p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Repairs</h3>
            <div class="stat-value"><?php echo number_format($totalRepairs); ?></div>
        </div>
        <div class="stat-card">
            <h3>Monthly Revenue</h3>
            <div class="stat-value">Rs. <?php echo number_format($monthlyRevenue, 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Stock Items</h3>
            <div class="stat-value"><?php echo number_format($totalStockQty); ?></div>
        </div>
        <div class="stat-card">
            <h3>Inventory Value</h3>
            <div class="stat-value">Rs. <?php echo number_format($totalStockValue, 0); ?></div>
        </div>
    </div>

    <div class="charts-grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div class="main-card" style="margin-bottom: 0;">
            <h2 class="section-title">Revenue Trend</h2>
            <div style="height: 320px; margin-top: 10px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="main-card" style="margin-bottom: 0;">
            <h2 class="section-title">Top Devices</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Jobs</th>
                            <th>Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($deviceData as $device): 
                            $percentage = ($totalDevices > 0) ? ($device['count'] / $totalDevices) * 100 : 0; ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($device['item_category']); ?></strong></td>
                            <td><?php echo $device['count']; ?></td>
                            <td><span class="share-badge"><?php echo round($percentage); ?>%</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="main-card">
        <h2 class="section-title">Critical Inventory Status</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stockTableQuery = "SELECT item_name, quantity, unit_price FROM stock ORDER BY quantity ASC LIMIT 10";
                    $stockTableResult = $conn->query($stockTableQuery);
                    if($stockTableResult && $stockTableResult->num_rows > 0) {
                        while($item = $stockTableResult->fetch_assoc()): 
                            $subtotal = $item['quantity'] * $item['unit_price'];
                            $isLow = ($item['quantity'] <= 5);
                    ?>
                    <tr>
                        <td style="color: var(--text-dark);"><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                        <td>Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                        <td style="<?php echo $isLow ? 'color: var(--danger); font-weight: 800;' : ''; ?>">
                            <?php echo $item['quantity']; ?>
                        </td>
                        <td>Rs. <?php echo number_format($subtotal, 2); ?></td>
                        <td>
                            <?php if($isLow): ?>
                                <span class="badge-low">⚠️ LOW STOCK</span>
                            <?php else: ?>
                                <span class="badge-ok">✓ STABLE</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, '#2ecc71');
    gradient.addColorStop(1, '#27ae60');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Revenue (Rs.)',
                data: <?php echo json_encode($revenues); ?>,
                backgroundColor: gradient,
                borderRadius: 8,
                hoverBackgroundColor: '#1abc9c'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false, // Disabling animation for clean PDF output
            plugins: { legend: { display: false } },
            scales: { 
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, 
                x: { grid: { display: false } } 
            }
        }
    });
</script>
</body>
</html>