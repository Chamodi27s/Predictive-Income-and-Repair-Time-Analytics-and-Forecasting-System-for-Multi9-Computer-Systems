<?php
include 'db_config.php';
include 'navbar.php';

// 1. Pagination Settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// 2. Search Handling
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 3. Stats Calculation
$total_query = "SELECT COUNT(*) as total FROM customer";
$total_result = mysqli_query($conn, $total_query);
$total_customers = mysqli_fetch_assoc($total_result)['total'];

$month_query = "SELECT COUNT(*) as monthly FROM job 
                WHERE MONTH(job_date) = MONTH(CURDATE()) 
                AND YEAR(job_date) = YEAR(CURDATE())";
$month_result = mysqli_query($conn, $month_query);
$monthly_customers = mysqli_fetch_assoc($month_result)['monthly'];

// 4. Build Where Clause
$where_clause = '';
if($search) {
    $where_clause = "WHERE (customer.customer_name LIKE '%$search%' 
                      OR customer.phone_number LIKE '%$search%' 
                      OR job.job_no LIKE '%$search%')";
}

// 5. Main Dashboard Query
$customers_query = "
SELECT 
    customer.phone_number, 
    customer.customer_name, 
    customer.email, 
    customer.address,
    job.job_no, 
    job.job_date, 
    job_device.job_status,
    technicians.name as tech_name,
    GROUP_CONCAT(job_device.device_name SEPARATOR ', ') as all_devices,
    GROUP_CONCAT(job_device.issue_name SEPARATOR ', ') as all_issues,
    GROUP_CONCAT(job_device.model SEPARATOR ', ') as all_models,
    GROUP_CONCAT(job_device.warranty_status SEPARATOR ', ') as all_warranties,
    GROUP_CONCAT(
        CASE
            WHEN LOWER(TRIM(job_device.warranty_status)) = 'warranty'
                THEN 'Agent'
            ELSE 'In-House'
        END
        SEPARATOR ', '
    ) as all_paths
FROM customer
INNER JOIN job ON customer.phone_number = job.phone_number
LEFT JOIN job_device ON job.job_no = job_device.job_no
LEFT JOIN technicians ON job.technician_id = technicians.technician_id
$where_clause
GROUP BY job.job_no
ORDER BY job.job_no DESC 
LIMIT $records_per_page OFFSET $offset
";
$customers_result = mysqli_query($conn, $customers_query);

if (!$customers_result) {
    die('Unable to load customer jobs: ' . htmlspecialchars(mysqli_error($conn)));
}

// 6. Total Pages Calculation
$total_pages_query = "
    SELECT COUNT(DISTINCT job.job_no) as total 
    FROM job 
    INNER JOIN customer ON job.phone_number = customer.phone_number 
    $where_clause
";
$total_pages_result = mysqli_query($conn, $total_pages_query);
$total_records = mysqli_fetch_assoc($total_pages_result)['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer & Job Dashboard</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --primary-green: #04d992;
            --primary-green-dark: #0f766e;
            --primary-green-light: #ccfbf1;
            --accent-green: #2ecc71;
            --light-bg: #f8fafc;
            --light-surface: #ffffff;
            --dark-surface: #1e293b;
            --border-light: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-dark);
            transition: var(--transition);
            padding: 140px 20px 40px;
        }
        body.dark-mode { background: #0f172a; color: #f1f5f9; }

        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }

        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 36px 40px;
            border-radius: 20px;
            margin-top: 15px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: black;
            text-align: center;
        }
        .page-header h1 { font-size: 32px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom:5px; color: #000000; }
        .page-header p { color: #ffffff; opacity: 0.95; font-size:15px; margin: 0; }

        /* ---- Stats Header ---- */
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
            flex-wrap: wrap;
        }
        .stats-container { display: flex; gap: 20px; flex: 1; flex-wrap: wrap; }
        .stat-card {
            background: var(--light-surface);
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            min-width: 180px;
            flex: 1;
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            border-left: 4px solid var(--primary-green);
        }
        .stat-card.pink { border-left-color: var(--accent-green); }
        body.dark-mode .stat-card { background: var(--dark-surface); border-color: #334155; }
        .stat-info .number { font-size: 36px; font-weight: 800; color: var(--text-dark); }
        body.dark-mode .stat-info .number { color: #f1f5f9; }

        .add-btn {
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
            color: white;
            padding: 14px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 6px 15px rgba(4, 217, 146, 0.3);
            transition: var(--transition);
            white-space: nowrap;
        }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(4, 217, 146, 0.4); color: white; }

        /* ---- Table Section ---- */
        .table-section {
            background: var(--light-surface);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid var(--border-light);
        }
        body.dark-mode .table-section { background: var(--dark-surface); border-color: #334155; }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .table-controls h2 { font-size: 22px; font-weight: 700; color: var(--text-dark); }
        body.dark-mode .table-controls h2 { color: #f1f5f9; }

        .right-controls { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .search-box { position: relative; }
        .search-box input {
            padding: 12px 20px 12px 46px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            font-size: 14px;
            width: 320px;
            max-width: 100%;
            transition: var(--transition);
            background: var(--light-bg);
            color: var(--text-dark);
            font-weight: 500;
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--primary-green);
            background: var(--light-surface);
            box-shadow: 0 0 0 4px var(--primary-green-light);
        }
        body.dark-mode .search-box input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        body.dark-mode .search-box input:focus { box-shadow: 0 0 0 4px rgba(4, 217, 146, 0.2); }
        .search-box i.ph { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--text-muted); }

        /* ---- Table ---- */
        .customer-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .customer-table th {
            text-align: left;
            padding: 12px 10px;
            background: var(--light-bg);
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-light);
            transition: var(--transition);
            white-space: nowrap;
        }
        .customer-table tbody tr { cursor: pointer; transition: var(--transition); background: var(--light-surface); }
        .customer-table tbody tr:hover { background: var(--light-bg); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
        .customer-table td { padding: 12px 10px; font-size: 12px; color: var(--text-dark); border-bottom: 1px solid var(--border-light); transition: var(--transition); line-height: 1.4; }

        body.dark-mode .customer-table th { background: rgba(0,0,0,0.2); border-bottom-color: #334155; }
        body.dark-mode .customer-table tbody tr { background: var(--dark-surface); }
        /* ---- Text Highlights ---- */
        .job-badge { color: #111; font-weight: 700; font-size: 13px; white-space: nowrap; }
        .status-badge { color: #111; font-weight: 700; font-size: 12px; text-transform: uppercase; white-space: nowrap; }
        body.dark-mode .job-badge, body.dark-mode .status-badge { color: #f1f5f9; }

        .predict-btn, .cost-btn { background: #ffffff; color: var(--primary-green-dark) !important; border: 1px solid var(--primary-green-dark); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; transition: var(--transition); box-shadow: none; white-space: nowrap; }
        .predict-btn:hover, .cost-btn:hover { background: var(--primary-green-light); transform: translateY(-1px); }
        
        body.dark-mode .predict-btn, body.dark-mode .cost-btn { background: transparent; border-color: var(--primary-green); color: var(--primary-green) !important; }
        body.dark-mode .predict-btn:hover, body.dark-mode .cost-btn:hover { background: rgba(4, 217, 146, 0.1); }

        /* ---- Pagination ---- */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid var(--border-light);
            flex-wrap: wrap;
            gap: 12px;
        }
        .pagination { display: flex; gap: 6px; flex-wrap: wrap; }
        .pagination a { padding: 10px 16px; border-radius: 8px; text-decoration: none; color: var(--text-muted); font-weight: 600; border: 1px solid var(--border-light); background: var(--light-surface); transition: var(--transition); }
        .pagination a.active { background: var(--primary-green); color: white; border-color: transparent; }

        /* ==================== RESPONSIVE QUERIES ==================== */

        /* Tablet (≤ 1024px) */
        @media (max-width: 1024px) {
            .container { padding: 0 15px; }
            .stat-card { padding: 18px 20px; }
            .stat-info .number { font-size: 28px; }
            .search-box input { width: 260px; }
            .table-section { padding: 18px; }
        }

        /* Mobile Landscape (≤ 768px) */
        @media (max-width: 768px) {
            .container { padding: 0 12px; margin-top: 15px; }

            .stats-header {
                flex-direction: column;
                align-items: stretch;
            }
            .stats-container { flex-direction: row; }
            .stat-card { min-width: 0; padding: 16px 18px; }
            .stat-info .number { font-size: 26px; }

            .add-btn {
                width: 100%;
                justify-content: center;
                padding: 14px 20px;
            }

            .table-section { padding: 14px 12px; }
            .table-controls { flex-direction: column; align-items: stretch; }
            .table-controls h2 { font-size: 18px; }
            .right-controls { width: 100%; }
            .search-box { width: 100%; }
            .search-box input { width: 100%; font-size: 15px; }

            /* Horizontal scrollable table */
            [style*="overflow-x: auto"] {
                -webkit-overflow-scrolling: touch;
            }
            .customer-table th,
            .customer-table td {
                padding: 10px 8px;
                font-size: 11px;
            }
            .predict-btn, .cost-btn {
                padding: 6px 10px;
                font-size: 11px;
            }
            .job-badge, .status-badge {
                font-size: 11px;
                padding: 4px 8px;
            }

            .pagination-container {
                justify-content: center;
            }
            .pagination a {
                padding: 8px 12px;
                font-size: 13px;
            }
        }

        /* Mobile Portrait (≤ 480px) */
        @media (max-width: 480px) {
            .container { padding: 0 8px; margin-top: 10px; }
            .stats-container { flex-direction: column; gap: 10px; }
            .stat-card { padding: 14px 16px; }
            .stat-info .number { font-size: 30px; }
            .stat-info h3 { font-size: 13px; }

            .table-section { padding: 10px 8px; border-radius: 10px; }
            .table-controls h2 { font-size: 16px; }

            .customer-table th,
            .customer-table td {
                padding: 8px 6px;
                font-size: 11px;
            }

            .predict-btn, .cost-btn {
                padding: 5px 8px;
                font-size: 10px;
                gap: 3px;
            }

            .pagination a {
                padding: 7px 10px;
                font-size: 12px;
                min-width: 36px;
                text-align: center;
            }
        }
    </style>
</head>
<body id="pageBody">

<div class="container">
    <div class="page-header">
        <h1>Customer & Job Dashboard</h1>
        <p>Register new customers, view existing profiles, and manage active repairs.</p>
    </div>
    <div class="stats-header">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Customers</h3>
                    <div class="number"><?= $total_customers ?></div>
                </div>
            </div>
            <div class="stat-card pink">
                <div class="stat-info">
                    <h3>New This Month</h3>
                    <div class="number"><?= $monthly_customers ?></div>
                </div>
            </div>
        </div>
        <a href="register.php" class="add-btn"><i class="ph ph-plus-circle"></i> New Registration</a>
    </div>

    <div class="table-section">
        <div class="table-controls">
            <h2>All Customers & Repairs</h2>
            <div class="right-controls">
                <form method="GET" action="" class="search-box">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Search by name, phone or job no..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
        </div>
        
        <div style="overflow-x: auto; width: 100%;">
        <table class="customer-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Job No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Devices</th>
                    <th>Model</th>
                    <th>Issue</th>
                    <th>Warranty</th>
                    <th>Path</th>
                    <th>Tech</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($customers_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($customers_result)): ?>
                        <?php 
                            $status = $row['job_status'] ?? 'Pending';
                            $status_class = 'status-pending';
                            if (strtolower($status) == 'approved') $status_class = 'status-approved';
                            else if (strtolower($status) == 'in progress') $status_class = 'status-in-progress';
                            else if (strtolower($status) == 'completed') $status_class = 'status-completed';
                        ?>
                        <tr onclick="window.location.href='customer_details.php?phone=<?= urlencode($row['phone_number']) ?>'" style="cursor:pointer;">
                            <td><span class="job-badge"><?= htmlspecialchars($row['job_no']) ?></span></td>
                            <td><?= $row['job_date'] ? date('d/m/Y', strtotime($row['job_date'])) : '-' ?></td>
                            <td style="font-weight: 600;">
                                <?= htmlspecialchars($row['customer_name']) ?><br>
                                <span style="font-size:12px; color:var(--text-muted); font-weight:normal;"><?= htmlspecialchars($row['phone_number']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['all_devices'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['all_models'] ?? '-') ?></td>
                            <td><span style="color:#ef4444; font-weight:600;"><?= htmlspecialchars($row['all_issues'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($row['all_warranties'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['all_paths'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['tech_name'] ?? 'Unassigned') ?></td>
                            <td><span class="status-badge"><?= htmlspecialchars($status) ?></span></td>
                            <td onclick="event.stopPropagation();">
                                <?php if($row['job_no']): ?>
                                    <div style="display: flex; gap: 6px; align-items: center; justify-content:center;">
                                        <a href="duration.php?job_no=<?= rawurlencode((string) $row['job_no']) ?>" class="predict-btn" title="Predict Completion Date"><i class="ph ph-clock"></i> Date</a>
                                        <button type="button" onclick="openPredictionModal('<?= htmlspecialchars($row['job_no']) ?>', 'cost')" class="cost-btn" style="cursor:pointer;" title="Predict Cost & Parts"><i class="ph ph-currency-dollar"></i> Cost</button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11" style="text-align:center;">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <div class="pagination-container">
            <div class="showing-text">Showing page <?= $page ?> of <?= $total_pages ?></div>
            <div class="pagination">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function applyTheme() {
        const body = document.getElementById('pageBody');
        const isDarkMode = localStorage.getItem("darkMode") === "enabled";
        if (isDarkMode) {
            body.classList.add("dark-mode");
        } else {
            body.classList.remove("dark-mode");
        }
    }
    applyTheme();
    window.addEventListener('storage', (event) => {
        if (event.key === 'darkMode') { applyTheme(); }
    });
    setInterval(applyTheme, 500);
</script>

<!-- Prediction Modal -->
<div id="predictModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.35); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal-content" style="background:linear-gradient(145deg, #ffffff, #f1f5f9); border:1px solid rgba(0,0,0,0.08); border-radius:24px; width:750px; max-width:95vw; padding:40px; box-shadow:0 25px 60px -12px rgba(0,0,0,0.2); position:relative;">
        <button onclick="closePredictionModal()" style="position:absolute; top:20px; right:20px; background:rgba(0,0,0,0.05); border:none; color:#64748b; font-size:20px; width:40px; height:40px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.3s;"><i class="ph ph-x"></i></button>
        
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:25px; border-bottom:1px solid rgba(0,0,0,0.06); padding-bottom:18px;">
            <div id="predictHeaderIcon" style="background:rgba(4, 217, 146, 0.1); width:56px; height:56px; border-radius:16px; display:flex; align-items:center; justify-content:center; box-shadow: 0 0 20px rgba(4,217,146,0.15);">
                <i id="predictIcon" class="ph-fill ph-clock" style="font-size:32px; color:#10b981;"></i>
            </div>
            <div>
                <h2 id="predictTitleText" style="margin:0 0 4px 0; font-size:22px; color:#1e293b; font-weight:700;">AI Date Prediction</h2>
                <p id="predictJobNo" style="color:#64748b; font-size:14px; margin:0; font-family:monospace;"></p>
            </div>
        </div>
        
        <!-- Styled AI Loading Animation -->
        <style>
            @keyframes pulseGlow {
                0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
                70% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(16, 185, 129, 0); }
                100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
            }
            @keyframes spinRing {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            @keyframes progressSweep {
                0% { width: 0%; left: 0; }
                50% { width: 70%; left: 15%; }
                100% { width: 100%; left: 0; }
            }
        </style>
        
        <div id="predictLoading" style="text-align:center; padding:45px 20px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
            <div style="position:relative; width:95px; height:95px; margin-bottom:22px; display:flex; align-items:center; justify-content:center;">
                <!-- Outer Glowing Ring -->
                <div style="position:absolute; width:95px; height:95px; border-radius:50%; border:3px solid transparent; border-top-color:#10b981; border-right-color:#3b82f6; animation: spinRing 1.2s linear infinite;"></div>
                <!-- Inner Reverse Ring -->
                <div style="position:absolute; width:70px; height:70px; border-radius:50%; border:3px solid transparent; border-bottom-color:#8b5cf6; border-left-color:#10b981; animation: spinRing 0.8s linear infinite reverse;"></div>
                <!-- Glowing Center Orb -->
                <div style="width:50px; height:50px; border-radius:50%; background:linear-gradient(135deg, #10b981, #059669); display:flex; align-items:center; justify-content:center; animation: pulseGlow 2s infinite;">
                    <i class="ph-fill ph-brain" style="font-size:26px; color:#ffffff;"></i>
                </div>
            </div>

            <h4 style="margin:0 0 8px 0; color:#1e293b; font-size:18px; font-weight:700;">AI Engine Processing</h4>
            <p id="predictLoadingText" style="color:#64748b; font-size:14px; margin:0 0 20px 0; min-height:22px; font-weight:500;">Analyzing historical repair data & calculating metrics...</p>

            <!-- Animated Progress Line -->
            <div style="width:240px; height:4px; background:rgba(0,0,0,0.06); border-radius:10px; overflow:hidden; position:relative;">
                <div style="height:100%; background:linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6); border-radius:10px; position:absolute; animation: progressSweep 1.5s ease-in-out infinite;"></div>
            </div>
        </div>
        
        <style>
            @keyframes timelineFadeIn {
                from { opacity: 0; transform: translateX(-15px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes resultCardPop {
                from { opacity: 0; transform: scale(0.92); }
                to { opacity: 1; transform: scale(1); }
            }
            @keyframes glowPulse {
                0%, 100% { box-shadow: 0 0 15px rgba(16,185,129,0.3); }
                50% { box-shadow: 0 0 30px rgba(16,185,129,0.5); }
            }
            @keyframes dotPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.3); }
            }
            .timeline-step { 
                display: flex; gap: 18px; align-items: flex-start; 
                animation: timelineFadeIn 0.4s ease-out both;
                position: relative;
            }
            .timeline-step:nth-child(1) { animation-delay: 0.1s; }
            .timeline-step:nth-child(2) { animation-delay: 0.2s; }
            .timeline-step:nth-child(3) { animation-delay: 0.3s; }
            .timeline-step:nth-child(4) { animation-delay: 0.4s; }
            .timeline-dot-wrap {
                display: flex; flex-direction: column; align-items: center; min-width: 20px; padding-top: 2px;
            }
            .timeline-dot {
                width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0;
                animation: dotPulse 2s ease-in-out infinite;
            }
            .timeline-line {
                width: 2px; flex: 1; min-height: 28px; background: rgba(0,0,0,0.08); margin-top: 4px;
            }
            .timeline-card {
                background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.06);
                border-radius: 12px; padding: 14px 18px; flex: 1; transition: 0.3s;
            }
            .timeline-card:hover {
                background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.10);
            }
            .timeline-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; margin-bottom: 4px; }
            .timeline-value { color: #1e293b; font-size: 15px; font-weight: 600; }
            .ai-divider {
                display: flex; align-items: center; gap: 12px; margin: 22px 0;
                animation: timelineFadeIn 0.5s ease-out 0.5s both;
            }
            .ai-divider-line { flex: 1; height: 1px; background: linear-gradient(90deg, transparent, rgba(16,185,129,0.35), transparent); }
            .ai-divider-badge {
                display: flex; align-items: center; gap: 6px; background: rgba(16,185,129,0.1);
                border: 1px solid rgba(16,185,129,0.25); border-radius: 20px; padding: 6px 16px;
                color: #10b981; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
            }
            .result-card {
                border-radius: 16px; padding: 22px 24px; transition: 0.3s;
                animation: resultCardPop 0.5s ease-out both;
            }
            .result-card:hover { transform: translateY(-2px); }
        </style>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
            .minimal-summary-card {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            }
        </style>
        <div id="predictResults" style="display:none; width:100%; margin-top:20px;">
            <div id="cardPredictDate" class="minimal-summary-card" style="background: #ffffff; border: 1px solid #eaeaea; border-top: 5px solid #10b981; border-radius: 24px; padding: 40px; box-shadow: 0 12px 48px rgba(0,0,0,0.05); max-width: 550px; margin: 0 auto; width: 100%;">
                
                <div style="text-align: center; margin-bottom: 28px;">
                    <div style="font-size: 13px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 1.5px;">Completion Estimate</div>
                    <div id="resDate" style="font-size: 48px; font-weight: 800; color: #064e3b; margin-top: 8px; letter-spacing: -1px;"></div>
                    <div style="font-size: 15px; color: #047857; margin-top: 6px;">Takes approx <span id="resRaw" style="font-weight: 700; color: #064e3b;"></span> days to repair</div>
                </div>

                <div style="border-top: 1px dashed #e0e0e0; margin: 28px 0;"></div>
                
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 16px; color: #666;">Reported Fault</div>
                        <div id="resFault" style="font-size: 16px; font-weight: 600; color: #222; text-align: right; max-width: 60%;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 16px; color: #666;">Assigned Technician</div>
                        <div id="resTech" style="font-size: 16px; font-weight: 600; color: #222;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 16px; color: #666;">Repair Pathway</div>
                        <div id="resPath" style="font-size: 16px; font-weight: 600; color: #222;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 16px; color: #666;">Expected Solution</div>
                        <div id="resSolution" style="font-size: 16px; font-weight: 600; color: #222; text-align: right; max-width: 60%;"></div>
                    </div>
                </div>
            </div>

            <div id="cardPredictCost" class="minimal-summary-card" style="background: #ffffff; border: 1px solid #eaeaea; border-top: 5px solid #10b981; border-radius: 24px; padding: 40px; box-shadow: 0 12px 48px rgba(0,0,0,0.05); max-width: 550px; margin: 0 auto; width: 100%;">
                
                <div style="text-align: center; margin-bottom: 28px;">
                    <div style="font-size: 13px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 1.5px;">Estimated Cost</div>
                    <div id="resCost" style="font-size: 54px; font-weight: 850; color: #064e3b; margin-top: 8px; letter-spacing: -1.5px;"></div>
                </div>

                <div style="border-top: 1px dashed #e0e0e0; margin: 28px 0;"></div>
                
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 16px; color: #666;">Device Type</div>
                        <div id="resDeviceType" style="font-size: 16px; font-weight: 600; color: #222;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 16px; color: #666;">Item Model</div>
                        <div id="resItemModel" style="font-size: 16px; font-weight: 600; color: #222;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 16px; color: #666;">Reported Fault</div>
                        <div id="resFaultCost" style="font-size: 16px; font-weight: 600; color: #222; text-align: right; max-width: 60%;"></div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 20px; border-top: 1px solid #f0f0f0;">
                        <div style="font-size: 16px; font-weight: 600; color: #111;">Required Parts</div>
                        <div id="resParts" style="font-size: 14px; font-weight: 700; color: #047857; background: #d1fae5; padding: 8px 16px; border-radius: 8px;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="predictError" style="display:none; background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.3); color:#fca5a5; padding:15px; border-radius:12px; font-size:14px; text-align:center; margin-top:20px;">
        </div>
    </div>
</div>

<script>
    let loadingTextInterval;
    const loadingSteps = [
        "Connecting to AI Prediction Backend...",
        "Querying Machine Learning Model...",
        "Cross-referencing historical repair dataset...",
        "Calculating prediction result..."
    ];

    function applyPredictionView(mode) {
        const titleEl = document.getElementById('predictTitleText');
        const iconEl = document.getElementById('predictIcon');
        const iconBox = document.getElementById('predictHeaderIcon');

        const cardDate = document.getElementById('cardPredictDate');
        const cardCost = document.getElementById('cardPredictCost');

        if (mode === 'cost') {
            if (titleEl) titleEl.innerText = "AI Cost & Parts Prediction";
            if (iconEl) iconEl.className = "ph-fill ph-currency-dollar";
            if (iconEl) iconEl.style.color = "#111";
            if (iconBox) {
                iconBox.style.background = "#f1f5f9";
                iconBox.style.boxShadow = "none";
            }
            if (cardDate) cardDate.style.display = 'none';
            if (cardCost) cardCost.style.display = 'block';
        } else {
            if (titleEl) titleEl.innerText = "AI Date Prediction";
            if (iconEl) iconEl.className = "ph-fill ph-clock";
            if (iconEl) iconEl.style.color = "#111";
            if (iconBox) {
                iconBox.style.background = "#f1f5f9";
                iconBox.style.boxShadow = "none";
            }
            if (cardDate) cardDate.style.display = 'block';
            if (cardCost) cardCost.style.display = 'none';
        }
    }

    function startLoadingAnimation() {
        let step = 0;
        const txtEl = document.getElementById('predictLoadingText');
        if (txtEl) txtEl.innerText = loadingSteps[0];
        clearInterval(loadingTextInterval);
        loadingTextInterval = setInterval(() => {
            step = (step + 1) % loadingSteps.length;
            if (txtEl) txtEl.innerText = loadingSteps[step];
        }, 500);
    }

    function stopLoadingAnimation() {
        clearInterval(loadingTextInterval);
    }

    function openPredictionModal(jobNo, initialMode = 'date') {
        document.getElementById('predictModal').style.display = 'flex';
        document.getElementById('predictJobNo').innerText = 'Job ID: ' + jobNo;
        
        // Reset states
        document.getElementById('predictLoading').style.display = 'flex';
        document.getElementById('predictResults').style.display = 'none';
        document.getElementById('predictError').style.display = 'none';

        applyPredictionView(initialMode);
        startLoadingAnimation();
        
        // Call API
        fetch('api_predict.php?job_no=' + encodeURIComponent(jobNo))
            .then(response => response.json())
            .then(data => {
                stopLoadingAnimation();
                document.getElementById('predictLoading').style.display = 'none';
                
                if(data.status === 'success') {
                    document.getElementById('predictResults').style.display = 'flex';
                    
                    // Date prediction fields
                    document.getElementById('resFault').innerText = data.issue;
                    document.getElementById('resPath').innerText = data.repair_path;
                    document.getElementById('resTech').innerText = data.technician;
                    document.getElementById('resSolution').innerText = data.solution;
                    document.getElementById('resRaw').innerText = data.days;
                    document.getElementById('resDate').innerText = data.completion_date;

                    // Cost prediction fields
                    document.getElementById('resDeviceType').innerText = data.device;
                    document.getElementById('resItemModel').innerText = data.model;
                    document.getElementById('resFaultCost').innerText = data.issue;

                    document.getElementById('resCost').innerText = 'Rs. ' + data.cost;
                    document.getElementById('resParts').innerText = data.parts;
                    
                    applyPredictionView(initialMode);
                } else {
                    document.getElementById('predictError').style.display = 'block';
                    document.getElementById('predictError').innerText = data.message;
                }
            })
            .catch(err => {
                stopLoadingAnimation();
                document.getElementById('predictLoading').style.display = 'none';
                document.getElementById('predictError').style.display = 'block';
                document.getElementById('predictError').innerText = 'Network Error occurred.';
            });
    }
    
    function closePredictionModal() {
        stopLoadingAnimation();
        document.getElementById('predictModal').style.display = 'none';
    }
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>

</html>