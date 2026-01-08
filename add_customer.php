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
        /* ඔබගේ පවතින CSS මම කිසිදු වෙනසක් කර නැත */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f4f8; min-height: 100vh; padding-top: 120px; padding-left: 40px; padding-right: 40px; color: #2c3e50; }
        .container { max-width: 1400px; margin: 0 auto; margin-top: 25px; }
        .stats-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px; flex-wrap: wrap; }
        .stats-container { display: flex; gap: 20px; flex: 1; }
        .stat-card { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 25px 30px; border-radius: 16px; box-shadow: 0 8px 24px rgba(46, 204, 113, 0.2); min-width: 220px; border: 2px solid rgba(46, 204, 113, 0.3); position: relative; overflow: hidden; transition: all 0.3s ease; }
        .stat-card.pink { background: linear-gradient(135deg, #ffe8f0 0%, #ffc9dd 100%); border-color: rgba(233, 30, 99, 0.3); box-shadow: 0 8px 24px rgba(233, 30, 99, 0.2); }
        .stat-info .number { font-size: 36px; font-weight: 800; color: #2c3e50; }
        .add-btn { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 14px 35px; border-radius: 30px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; }
        .table-section { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        .customer-table { width: 100%; border-collapse: collapse; }
        .customer-table th { text-align: left; padding: 12px 15px; background: #f8f9fa; color: #5a6c7d; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #e8ecef; }
        .customer-table td { padding: 14px 15px; font-size: 13px; color: #2c3e50; border-bottom: 1px solid #f0f2f5; }
        .job-badge { background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-weight: 600; }
        .device-badge { background: #f3e5f5; color: #7b1fa2; padding: 4px 10px; border-radius: 4px; font-weight: 500; }
        
        /* Prediction Button එක සඳහා අවශ්‍ය Style එකක් පමණක් එක් කරන ලදී */
        .predict-btn {
            background: #4361ee;
            color: white !important;
            padding: 5px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .predict-btn:hover { background: #3a56d4; }
    </style>
</head>
<body>

<div class="container">
    <div class="stats-header">
        <div class="stats-container">
            <div class="stat-card green">
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
                    <th>Job No</th> <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone NO</th>
                    <th>Device</th>
                    <th>Address</th>
                    <th>Prediction</th> </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($customers_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($customers_result)): ?>
                        <tr onclick="window.location.href='customer_details.php?phone=<?= urlencode($row['phone_number']) ?>'">
                            <td class="date-text"><?= $row['job_date'] ? date('d/m/Y', strtotime($row['job_date'])) : '-' ?></td>
                            <td><span class="job-badge"><?= htmlspecialchars($row['job_no']) ?></span></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td style="color: #5a6c7d;"><?= htmlspecialchars($row['email']) ?></td>
                            <td style="font-family: monospace; font-weight: 500;"><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><span class="device-badge"><?= htmlspecialchars($row['all_devices']) ?></span></td>
                            <td style="color: #7f8c8d; font-size: 12px;"><?= htmlspecialchars($row['address']) ?></td>
                            <td onclick="event.stopPropagation();"> <a href="duration.php?job_no=<?= urlencode($row['job_no']) ?>" class="predict-btn">Predict Time</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination-container">
            <div class="showing-text">Showing data...</div>
            <div class="pagination">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>