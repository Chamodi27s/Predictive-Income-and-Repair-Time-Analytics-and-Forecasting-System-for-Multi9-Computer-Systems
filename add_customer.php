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
    GROUP_CONCAT(job_device.device_name SEPARATOR ', ') as all_devices
FROM customer
INNER JOIN job ON customer.phone_number = job.phone_number
LEFT JOIN job_device ON job.job_no = job_device.job_no
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
            --primary-green: #14b8a6;
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
        .add-btn { background: linear-gradient(135deg, var(--primary-green), var(--accent-green)); color: white; padding: 14px 35px; border-radius: 30px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 6px 15px rgba(20, 184, 166, 0.3); transition: var(--transition); }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(20, 184, 166, 0.4); color: white; }
        
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
        body.dark-mode .search-box input:focus { box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.2); }
        .search-box i.ph { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--text-muted); }
        
        .customer-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .customer-table th { text-align: left; padding: 16px 15px; background: var(--light-bg); color: var(--text-muted); font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.8px; border-bottom: 2px solid var(--border-light); transition: var(--transition); }
        .customer-table tbody tr { cursor: pointer; transition: var(--transition); background: var(--light-surface); }
        .customer-table tbody tr:hover { background: var(--light-bg); transform: translateX(4px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
        .customer-table td { padding: 16px 15px; font-size: 13px; color: var(--text-dark); border-bottom: 1px solid var(--border-light); transition: var(--transition); }
        
        body.dark-mode .customer-table th { background: rgba(0,0,0,0.2); border-bottom-color: #334155; }
        body.dark-mode .customer-table tbody tr { background: var(--dark-surface); }
        body.dark-mode .customer-table tbody tr:hover { background: #1e293b; }
        body.dark-mode .customer-table td { border-bottom-color: #1e293b; color: #e2e8f0; }
        
        .job-badge { background: var(--primary-green-light); color: var(--primary-green-dark); padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 12px; display: inline-block; border: 1px solid rgba(20, 184, 166, 0.2); }
        body.dark-mode .job-badge { background: rgba(20, 184, 166, 0.1); color: var(--accent-green); }
        
        .device-badge { background: #f3e8ff; color: #6b21a8; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 12px; display: inline-block; border: 1px solid rgba(147, 51, 234, 0.2); }
        body.dark-mode .device-badge { background: rgba(147, 51, 234, 0.1); color: #d8b4fe; }
        
        .predict-btn { background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark)); color: white !important; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; transition: var(--transition); box-shadow: 0 3px 10px rgba(15, 118, 110, 0.3); }
        .predict-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(15, 118, 110, 0.4); }

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
        
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Job No</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Contact</th>
                    <th>Devices</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($customers_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($customers_result)): ?>
                        <tr onclick="window.location.href='customer_details.php?phone=<?= urlencode($row['phone_number']) ?>'">
                            <td><span class="job-badge"><?= htmlspecialchars($row['job_no']) ?></span></td>
                            <td><?= $row['job_date'] ? date('d/m/Y', strtotime($row['job_date'])) : '-' ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><span class="device-badge"><?= htmlspecialchars($row['all_devices']) ?></span></td>
                            <td onclick="event.stopPropagation();">
                                <?php if($row['job_no']): ?>
                                    <a href="time_prediction_project/index.php?job_no=<?= urlencode($row['job_no']) ?>" class="predict-btn"><i class="ph ph-clock"></i> Predict</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

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

</body>

</html>