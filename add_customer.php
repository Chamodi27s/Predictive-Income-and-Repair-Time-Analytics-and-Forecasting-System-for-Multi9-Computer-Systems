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
    job.job_status,
    technicians.name as tech_name,
    GROUP_CONCAT(job_device.device_name SEPARATOR ', ') as all_devices,
    GROUP_CONCAT(job_device.issue_name SEPARATOR ', ') as all_issues,
    GROUP_CONCAT(job_device.item_model SEPARATOR ', ') as all_models,
    GROUP_CONCAT(job_device.warranty_status SEPARATOR ', ') as all_warranties,
    GROUP_CONCAT(job_device.repair_path SEPARATOR ', ') as all_paths
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

        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--text-dark); transition: var(--transition); padding-top: 100px; }
        body.dark-mode { background: #0f172a; color: #f1f5f9; }

        .container { max-width: 1400px; margin: 0 auto; margin-top: 25px; padding: 0 20px; }
        .stats-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px; flex-wrap: wrap; }
        .stats-container { display: flex; gap: 20px; flex: 1; }
        .stat-card { background: var(--light-surface); padding: 25px 30px; border-radius: 16px; box-shadow: var(--card-shadow); min-width: 220px; border: 1px solid var(--border-light); position: relative; overflow: hidden; transition: var(--transition); border-left: 4px solid var(--primary-green); }
        .stat-card.pink { border-left-color: var(--accent-green); }
        body.dark-mode .stat-card { background: var(--dark-surface); border-color: #334155; }
        .stat-info .number { font-size: 36px; font-weight: 800; color: var(--text-dark); }
        body.dark-mode .stat-info .number { color: #f1f5f9; }
        .add-btn { background: linear-gradient(135deg, var(--primary-green), var(--accent-green)); color: white; padding: 14px 35px; border-radius: 30px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 6px 15px rgba(4, 217, 146, 0.3); transition: var(--transition); }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(4, 217, 146, 0.4); color: white; }
        
        .table-section { background: var(--light-surface); border-radius: 12px; padding: 25px; box-shadow: var(--card-shadow); transition: var(--transition); border: 1px solid var(--border-light); }
        body.dark-mode .table-section { background: var(--dark-surface); border-color: #334155; }
        
        .table-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .table-controls h2 { font-size: 22px; font-weight: 700; color: var(--text-dark); }
        body.dark-mode .table-controls h2 { color: #f1f5f9; }
        
        .right-controls { display: flex; gap: 12px; align-items: center; }
        .search-box { position: relative; }
        .search-box input { padding: 12px 20px 12px 46px; border: 2px solid var(--border-light); border-radius: 12px; font-size: 14px; width: 320px; transition: var(--transition); background: var(--light-bg); color: var(--text-dark); font-weight: 500; }
        .search-box input:focus { outline: none; border-color: var(--primary-green); background: var(--light-surface); box-shadow: 0 0 0 4px var(--primary-green-light); }
        body.dark-mode .search-box input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        body.dark-mode .search-box input:focus { box-shadow: 0 0 0 4px rgba(4, 217, 146, 0.2); }
        .search-box i.ph { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--text-muted); }
        
        .customer-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .customer-table th { text-align: left; padding: 12px 10px; background: var(--light-bg); color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; border-bottom: 2px solid var(--border-light); transition: var(--transition); }
        .customer-table tbody tr { cursor: pointer; transition: var(--transition); background: var(--light-surface); }
        .customer-table tbody tr:hover { background: var(--light-bg); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
        .customer-table td { padding: 12px 10px; font-size: 12px; color: var(--text-dark); border-bottom: 1px solid var(--border-light); transition: var(--transition); line-height: 1.4; }
        
        body.dark-mode .customer-table th { background: rgba(0,0,0,0.2); border-bottom-color: #334155; }
        body.dark-mode .customer-table tbody tr { background: var(--dark-surface); }
        body.dark-mode .customer-table tbody tr:hover { background: #1e293b; }
        body.dark-mode .customer-table td { border-bottom-color: #1e293b; color: #e2e8f0; }
        
        .job-badge { background: var(--primary-green-light); color: var(--primary-green-dark); padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 13px; }
        .device-badge { background: #f3e8ff; color: #7e22ce; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px; }
        body.dark-mode .device-badge { background: rgba(168, 85, 247, 0.2); color: #d8b4fe; }
        .tech-badge { background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px; }
        body.dark-mode .tech-badge { background: rgba(56, 189, 248, 0.2); color: #7dd3fc; }
        
        .status-badge { padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 12px; text-transform: uppercase; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-approved { background: #dbeafe; color: #1e40af; }
        .status-in-progress { background: #ffedd5; color: #9a3412; }
        .status-completed { background: #dcfce7; color: #166534; }
        body.dark-mode .status-pending { background: rgba(234, 179, 8, 0.2); color: #fef08a; }
        body.dark-mode .status-approved { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }
        body.dark-mode .status-in-progress { background: rgba(249, 115, 22, 0.2); color: #fdba74; }
        body.dark-mode .status-completed { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        
        .predict-btn { background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark)); color: white !important; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; transition: var(--transition); box-shadow: 0 3px 10px rgba(15, 118, 110, 0.3); }
        .predict-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(15, 118, 110, 0.4); }

        .cost-btn { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white !important; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; transition: var(--transition); box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3); }
        .cost-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4); }

        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding-top: 20px; border-top: 2px solid var(--border-light); }
        .pagination a { padding: 10px 16px; border-radius: 8px; text-decoration: none; color: var(--text-muted); font-weight: 600; border: 1px solid var(--border-light); background: var(--light-surface); transition: var(--transition); }
        .pagination a.active { background: var(--primary-green); color: white; border-color: transparent; }
    </style>
</head>
<body id="pageBody">

<div class="container">
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
                            <td><span class="device-badge"><?= htmlspecialchars($row['all_devices'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($row['all_models'] ?? '-') ?></td>
                            <td><span style="color:#ef4444; font-weight:600;"><?= htmlspecialchars($row['all_issues'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($row['all_warranties'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['all_paths'] ?? '-') ?></td>
                            <td><span class="tech-badge"><i class="ph-fill ph-wrench"></i> <?= htmlspecialchars($row['tech_name'] ?? 'Unassigned') ?></span></td>
                            <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($status) ?></span></td>
                            <td onclick="event.stopPropagation();">
                                <?php if($row['job_no']): ?>
                                    <div style="display: flex; gap: 8px; align-items: center; justify-content:center;">
                                        <button type="button" onclick="openPredictionModal('<?= htmlspecialchars($row['job_no']) ?>')" class="predict-btn" style="border:none; cursor:pointer;"><i class="ph ph-clock"></i> Predict</button>
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
<div id="predictModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter:blur(8px); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal-content" style="background:linear-gradient(145deg, #1e293b, #0f172a); border:1px solid rgba(255,255,255,0.05); border-radius:24px; width:750px; max-width:95vw; padding:40px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); position:relative;">
        <button onclick="closePredictionModal()" style="position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.05); border:none; color:var(--text-muted); font-size:20px; width:40px; height:40px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.3s;"><i class="ph ph-x"></i></button>
        
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:30px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:20px;">
            <div style="background:rgba(4, 217, 146, 0.1); width:60px; height:60px; border-radius:16px; display:flex; align-items:center; justify-content:center; box-shadow: 0 0 20px rgba(4,217,146,0.2);">
                <i class="ph-fill ph-brain" style="font-size:36px; color:var(--primary-green);"></i>
            </div>
            <div>
                <h2 style="margin:0 0 5px 0; font-size:24px; color:#f8fafc; font-weight:700;">AI Instant Prediction</h2>
                <p id="predictJobNo" style="color:#94a3b8; font-size:14px; margin:0; font-family:monospace;"></p>
            </div>
        </div>
        
        <div id="predictLoading" style="text-align:center; padding:50px 0;">
            <i class="ph ph-spinner ph-spin" style="font-size:40px; color:var(--primary-green);"></i>
            <p style="margin-top:20px; color:#94a3b8; font-size:15px;">Analyzing historical repair data & calculating metrics...</p>
        </div>
        
        <div id="predictResults" style="display:none; gap:25px; align-items:stretch;">
            <!-- Left Column: Inputs -->
            <div style="flex:1; background:rgba(255, 255, 255, 0.02); border:1px solid rgba(255,255,255,0.05); border-radius:16px; padding:25px;">
                <h3 style="font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#64748b; margin:0 0 20px 0; font-weight:600;">Prediction Parameters</h3>
                
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Reported Fault</div>
                    <div id="resFault" style="color:#f1f5f9; font-size:15px; font-weight:500;"></div>
                </div>
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Assigned Technician</div>
                    <div id="resTech" style="color:#f1f5f9; font-size:15px; font-weight:500;"></div>
                </div>
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Repair Pathway</div>
                    <div id="resPath" style="color:#f1f5f9; font-size:15px; font-weight:500;"></div>
                </div>
                <div>
                    <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Expected Solution</div>
                    <div id="resSolution" style="color:#f1f5f9; font-size:15px; font-weight:500;"></div>
                </div>
            </div>

            <!-- Right Column: Outputs -->
            <div style="flex:1; display:flex; flex-direction:column; gap:15px;">
                <!-- Dates Card -->
                <div style="background:rgba(4, 217, 146, 0.05); border:1px solid rgba(4,217,146,0.2); border-radius:16px; padding:25px; flex:1; display:flex; flex-direction:column; justify-content:center;">
                    <h3 style="font-size:13px; text-transform:uppercase; letter-spacing:1px; color:var(--primary-green); margin:0 0 15px 0; font-weight:700;">Completion Estimate</h3>
                    
                    <div style="display:flex; align-items:flex-end; gap:10px; margin-bottom:5px;">
                        <span id="resDate" style="color:#f8fafc; font-size:32px; font-weight:800; line-height:1;"></span>
                    </div>
                    <div style="color:#94a3b8; font-size:14px;">Takes approx <span id="resRaw" style="color:var(--primary-green); font-weight:700;"></span> days to repair</div>
                </div>

                <!-- Cost Card -->
                <div style="background:rgba(168, 85, 247, 0.05); border:1px solid rgba(168,85,247,0.2); border-radius:16px; padding:20px; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#c084fc; margin-bottom:5px; font-weight:700;">Estimated Cost</div>
                        <div id="resCost" style="color:#f8fafc; font-size:24px; font-weight:700;"></div>
                    </div>
                    <div style="width:40px; height:40px; border-radius:10px; background:rgba(168,85,247,0.1); display:flex; align-items:center; justify-content:center;">
                        <i class="ph ph-currency-dollar" style="color:#c084fc; font-size:20px;"></i>
                    </div>
                </div>

                <!-- Parts Card -->
                <div style="background:rgba(59, 130, 246, 0.05); border:1px solid rgba(59,130,246,0.2); border-radius:16px; padding:20px;">
                    <div style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#60a5fa; margin-bottom:5px; font-weight:700;">Required Parts</div>
                    <div id="resParts" style="color:#f8fafc; font-size:16px; font-weight:600;"></div>
                </div>
            </div>
        </div>
        
        <div id="predictError" style="display:none; background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.3); color:#fca5a5; padding:15px; border-radius:12px; font-size:14px; text-align:center; margin-top:20px;">
        </div>
    </div>
</div>

<script>
    function openPredictionModal(jobNo) {
        document.getElementById('predictModal').style.display = 'flex';
        document.getElementById('predictJobNo').innerText = 'Job ID: ' + jobNo;
        
        // Reset states
        document.getElementById('predictLoading').style.display = 'block';
        document.getElementById('predictResults').style.display = 'none';
        document.getElementById('predictError').style.display = 'none';
        
        // Call API
        fetch('api_predict.php?job_no=' + encodeURIComponent(jobNo))
            .then(response => response.json())
            .then(data => {
                document.getElementById('predictLoading').style.display = 'none';
                
                if(data.status === 'success') {
                    document.getElementById('predictResults').style.display = 'flex';
                    
                    document.getElementById('resFault').innerText = data.issue;
                    document.getElementById('resPath').innerText = data.repair_path;
                    document.getElementById('resTech').innerText = data.technician;
                    document.getElementById('resSolution').innerText = data.solution;
                    document.getElementById('resRaw').innerText = data.days;
                    document.getElementById('resDate').innerText = data.completion_date;

                    document.getElementById('resCost').innerText = 'Rs. ' + data.cost;
                    document.getElementById('resParts').innerText = data.parts;
                } else {
                    document.getElementById('predictError').style.display = 'block';
                    document.getElementById('predictError').innerText = data.message;
                }
            })
            .catch(err => {
                document.getElementById('predictLoading').style.display = 'none';
                document.getElementById('predictError').style.display = 'block';
                document.getElementById('predictError').innerText = 'Network Error occurred.';
            });
    }
    
    function closePredictionModal() {
        document.getElementById('predictModal').style.display = 'none';
    }
</script>

</body>

</html>