<?php
include 'db_config.php';
include 'navbar.php';

// 1. Pagination Settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// 2. Search Handling (Phone, Job No, or Name)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 3. Stats Calculation
// Total unique customers
$total_query = "SELECT COUNT(*) as total FROM customer";
$total_result = mysqli_query($conn, $total_query);
$total_customers = mysqli_fetch_assoc($total_result)['total'];

// New jobs this month
$month_query = "SELECT COUNT(*) as monthly FROM job 
                WHERE MONTH(job_date) = MONTH(CURDATE()) 
                AND YEAR(job_date) = YEAR(CURDATE())";
$month_result = mysqli_query($conn, $month_query);
$monthly_customers = mysqli_fetch_assoc($month_result)['monthly'];

// 4. Build Where Clause for Search
// මෙහිදී Job Number, Phone Number සහ Name යන තුනම පරීක්ෂා කරයි
$where_clause = '';
if($search) {
    $where_clause = "WHERE (customer.customer_name LIKE '%$search%' 
                     OR customer.phone_number LIKE '%$search%' 
                     OR job.job_no LIKE '%$search%')";
}

// 5. Main Dashboard Query
// එකම Job එකේ devices කිහිපයක් තිබේ නම් ඒවා එක පේළියකට ගනී (GROUP BY)
// Job අංක 1 සිට ඉහළට පෙන්වයි (ASC ORDER)
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
ORDER BY CAST(job.job_no AS UNSIGNED) ASC 
LIMIT $records_per_page OFFSET $offset
";
$customers_result = mysqli_query($conn, $customers_query);

// 6. Total Pages Calculation (Fixes the Fatal Error by adding INNER JOIN)
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: #f0f9ff; padding: 20px; color: #1f2937; }
        .container { max-width: 1400px; margin: 0 auto; }
        
        /* Stats Cards */
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #10b981; }
        .stat-card.pink { border-left-color: #ec4899; }
        .stat-info h3 { font-size: 14px; color: #6b7280; text-transform: uppercase; }
        .stat-info .number { font-size: 32px; font-weight: 800; color: #111827; }

        /* Controls */
        .controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .add-btn { background: #10b981; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .add-btn:hover { background: #059669; transform: translateY(-2px); }
        
        .search-box { background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 10px 15px; display: flex; align-items: center; gap: 10px; width: 350px; }
        .search-box input { border: none; outline: none; width: 100%; font-size: 14px; }

        /* Table Style */
        .table-container { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow-x: auto; }
        .customer-table { width: 100%; border-collapse: collapse; }
        .customer-table th { text-align: left; padding: 15px; background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .customer-table tr { border-bottom: 1px solid #f1f5f9; transition: 0.2s; cursor: pointer; }
        .customer-table tr:hover { background: #f1f5f9; }
        .customer-table td { padding: 15px; font-size: 14px; }
        
        .job-badge { background: #dcfce7; color: #166534; padding: 5px 10px; border-radius: 6px; font-weight: 800; font-size: 12px; }
        .device-text { color: #059669; font-weight: 600; }
        
        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 25px; }
        .pagination a, .pagination span { padding: 10px 16px; border-radius: 10px; border: 1px solid #ddd; text-decoration: none; color: #374151; font-weight: 600; background: white; }
        .pagination .active { background: #10b981; color: white; border-color: #10b981; }
    </style>
</head>
<body>

<div class="container">
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-info"><h3>Total Customers</h3><div class="number"><?= $total_customers ?></div></div>
            <div style="font-size: 40px;">👥</div>
        </div>
        <div class="stat-card pink">
            <div class="stat-info"><h3>Jobs This Month</h3><div class="number"><?= $monthly_customers ?></div></div>
            <div style="font-size: 40px;">📈</div>
        </div>
    </div>

    <div class="controls">
        <a href="register.php" class="add-btn">➕ Add New Customer / Job</a>
        <form method="GET" action="">
            <div class="search-box">
                🔍 <input type="text" name="search" placeholder="Enter Job No, Phone or Name..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Job No</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Device Details</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($customers_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($customers_result)): ?>
                        <tr onclick="window.location.href='customer_details.php?phone=<?= urlencode($row['phone_number']) ?>'">
                            <td><span class="job-badge">#<?= htmlspecialchars($row['job_no']) ?></span></td>
                            <td><?= $row['job_date'] ? date('d-M-Y', strtotime($row['job_date'])) : '-' ?></td>
                            <td><strong><?= htmlspecialchars($row['customer_name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td class="device-text"><?= htmlspecialchars($row['all_devices']) ?></td>
                            <td style="color: #6b7280; font-size: 12px;"><?= htmlspecialchars($row['address']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 40px; color: #9ca3af;">No records found for "<?= htmlspecialchars($search) ?>"</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>