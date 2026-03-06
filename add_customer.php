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
    <style>
        /* --- Existing White Mode CSS (Unchanged) --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f4f8; min-height: 100vh; padding-top: 120px; padding-left: 40px; padding-right: 40px; color: #2c3e50; transition: background 0.3s ease, color 0.3s ease; }
        .container { max-width: 1400px; margin: 0 auto; margin-top: 25px; }
        .stats-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px; flex-wrap: wrap; }
        .stats-container { display: flex; gap: 20px; flex: 1; }
        .stat-card { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 25px 30px; border-radius: 16px; box-shadow: 0 8px 24px rgba(46, 204, 113, 0.2); min-width: 220px; border: 2px solid rgba(46, 204, 113, 0.3); position: relative; overflow: hidden; transition: all 0.3s ease; }
        .stat-card.pink { background: linear-gradient(135deg, #ffe8f0 0%, #ffc9dd 100%); border-color: rgba(233, 30, 99, 0.3); box-shadow: 0 8px 24px rgba(233, 30, 99, 0.2); }
        .stat-info .number { font-size: 36px; font-weight: 800; color: #2c3e50; }
        .add-btn { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 14px 35px; border-radius: 30px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; }
        .table-section { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); transition: background 0.3s ease; }
        .table-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .table-controls h2 { font-size: 22px; font-weight: 700; color: #2c3e50; }
        .right-controls { display: flex; gap: 12px; align-items: center; }
        .search-box { position: relative; }
        .search-box input { padding: 12px 20px 12px 46px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; width: 320px; transition: all 0.3s ease; background: #f8fafc; color: #2c3e50; font-weight: 500; }
        .search-box input:focus { outline: none; border-color: #2ecc71; background: white; box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1); }
        .search-box::before { content: '🔍'; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none; }
        .customer-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .customer-table th { text-align: left; padding: 16px 15px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #5a6c7d; font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.8px; border-bottom: 2px solid #dee2e6; transition: background 0.3s ease; }
        .customer-table tbody tr { cursor: pointer; transition: all 0.3s ease; background: white; }
        .customer-table tbody tr:hover { background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%); transform: translateX(4px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        .customer-table td { padding: 16px 15px; font-size: 13px; color: #2c3e50; border-bottom: 1px solid #f0f2f5; transition: color 0.3s ease; }
        .job-badge { background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); color: #1976d2; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 12px; display: inline-block; box-shadow: 0 2px 6px rgba(25, 118, 210, 0.15); }
        .device-badge { background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); color: #7b1fa2; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 12px; display: inline-block; box-shadow: 0 2px 6px rgba(123, 31, 162, 0.15); }
        .predict-btn { background: linear-gradient(135deg, #059669 0%, #059669 100%); color: white !important; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-block; transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(5, 150, 105, 0.3); }

        /* --- New Dark Mode CSS (Only applied when .dark-mode is active) --- */
        body.dark-mode { background: #0f172a; color: #f8fafc; }
        body.dark-mode .table-section { background: #1e293b; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        body.dark-mode .table-controls h2 { color: #f8fafc; }
        body.dark-mode .stat-card { background: #1e293b; border-color: #334155; color: #fff; }
        body.dark-mode .stat-info .number { color: #fff; }
        body.dark-mode .customer-table th { background: #334155; color: #cbd5e1; border-bottom-color: #475569; }
        body.dark-mode .customer-table tbody tr { background: #1e293b; }
        body.dark-mode .customer-table tbody tr:hover { background: #2d3748; }
        body.dark-mode .customer-table td { color: #cbd5e1; border-bottom-color: #334155; }
        body.dark-mode .search-box input { background: #0f172a; border-color: #334155; color: #fff; }
        body.dark-mode .pagination a { background: #1e293b; border-color: #334155; color: #cbd5e1; }
        body.dark-mode .showing-text { color: #94a3b8; }
        
        /* Pagination Styles (Unchanged but adapted for dark) */
        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding-top: 20px; border-top: 2px solid #e9ecef; }
        .pagination a { padding: 10px 16px; border-radius: 8px; text-decoration: none; color: #6c757d; font-weight: 600; border: 2px solid #e9ecef; background: white; transition: 0.3s; }
        .pagination a.active { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; border-color: transparent; }
    </style>
</head>
<body id="pageBody">

<div class="container">
    <div class="stats-header">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>👥 Total Customers</h3>
                    <div class="number"><?= $total_customers ?></div>
                </div>
            </div>
            <div class="stat-card pink">
                <div class="stat-info">
                    <h3>📈 New This Month</h3>
                    <div class="number"><?= $monthly_customers ?></div>
                </div>
            </div>
        </div>
        <a href="register.php" class="add-btn"><span>+</span><span>Add Customer</span></a>
    </div>

    <div class="table-section">
        <div class="table-controls">
            <h2>All Customers</h2>
            <div class="right-controls">
                <form method="GET">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search here..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </form>
            </div>
        </div>
        
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Job No</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone NO</th>
                    <th>Device</th>
                    <th>Address</th>
                    <th>Time Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($customers_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($customers_result)): ?>
                        <tr onclick="window.location.href='customer_details.php?phone=<?= urlencode($row['phone_number']) ?>'">
                            <td><?= $row['job_date'] ? date('d/m/Y', strtotime($row['job_date'])) : '-' ?></td>
                            <td><span class="job-badge"><?= htmlspecialchars($row['job_no']) ?></span></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><span class="device-badge"><?= htmlspecialchars($row['all_devices']) ?></span></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td onclick="event.stopPropagation();">
                                <a href="duration.php?job_no=<?= urlencode($row['job_no']) ?>" class="predict-btn">Time Duration</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination-container">
            <div class="showing-text">Showing records...</div>
            <div class="pagination">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * පද්ධතිය Refresh කරන්නේ නැතිව Auto-sync වීමට පහත JavaScript එක ක්‍රියාත්මක වේ.
     */
    function applyTheme() {
        const body = document.getElementById('pageBody');
        const isDarkMode = localStorage.getItem("darkMode") === "enabled";
        
        if (isDarkMode) {
            body.classList.add("dark-mode");
        } else {
            body.classList.remove("dark-mode");
        }
    }

    // 1. පිටුව මුලින්ම load වන විට තේමාව පරීක්ෂා කිරීම
    applyTheme();

    // 2. Navbar එකේ mode එක වෙනස් කළ සැනින් හඳුනා ගැනීම (Real-time storage listener)
    window.addEventListener('storage', (event) => {
        if (event.key === 'darkMode') {
            applyTheme();
        }
    });

    // 3. Navbar එකේ switch එක click කරන අවස්ථාවේදීම වර්ණ වෙනස් වීම සහතික කිරීමට (Interval sync)
    setInterval(applyTheme, 500);
</script>

</body>
<?php include 'chatbot.php'; ?>
</html>