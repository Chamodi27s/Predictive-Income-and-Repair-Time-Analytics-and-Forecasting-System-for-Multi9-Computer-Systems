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
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            background: #f0f4f8;
            min-height: 100vh;
           padding-top: 120px;   /* 🔥 navbar height */
    padding-left: 40px;
    padding-right: 40px;
            color: #2c3e50;
        }
        
        .container { 
            max-width: 1400px; 
            margin: 0 auto;
            margin-top: 25px;
        }
        
        /* Stats Cards */
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .stats-container { 
            display: flex;
            gap: 20px;
            flex: 1;
        }
        
        .stat-card { 
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(46, 204, 113, 0.2);
            min-width: 220px;
            border: 2px solid rgba(46, 204, 113, 0.3);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(46, 204, 113, 0.3);
        }
        
        .stat-card.pink {
            background: linear-gradient(135deg, #ffe8f0 0%, #ffc9dd 100%);
            border-color: rgba(233, 30, 99, 0.3);
            box-shadow: 0 8px 24px rgba(233, 30, 99, 0.2);
        }
        
        .stat-card.pink:hover {
            box-shadow: 0 12px 32px rgba(233, 30, 99, 0.3);
        }
        
        .stat-card.green {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-color: rgba(46, 204, 113, 0.3);
        }
        
        .stat-info {
            position: relative;
            z-index: 2;
        }
        
        .stat-info h3 { 
            font-size: 11px; 
            color: #5a6c7d; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .stat-info .number { 
            font-size: 36px; 
            font-weight: 800; 
            color: #2c3e50;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            font-size: 20px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        /* Add Button */
        .add-btn { 
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white; 
            padding: 14px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(46, 204, 113, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .add-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .add-btn:hover::before {
            left: 100%;
        }
        
        .add-btn:hover { 
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(46, 204, 113, 0.5);
        }
        
        .add-btn:active {
            transform: translateY(-2px);
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-controls h2 {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .right-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .search-box { 
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 280px;
            transition: all 0.3s ease;
        }
        
        .search-box:focus-within {
            background: white;
            border-color: #2ecc71;
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
        }
        
        .search-box input { 
            border: none;
            outline: none;
            width: 100%;
            font-size: 13px;
            background: transparent;
            color: #2c3e50;
        }
        
        .search-box input::placeholder {
            color: #95a5a6;
        }
        
        .view-all {
            color: #2ecc71;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Table Style */
        .customer-table { 
            width: 100%;
            border-collapse: collapse;
        }
        
        .customer-table th { 
            text-align: left;
            padding: 12px 15px;
            background: #f8f9fa;
            color: #5a6c7d;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            border-bottom: 2px solid #e8ecef;
        }
        
        .customer-table tbody tr { 
            border-bottom: 1px solid #f0f2f5;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .customer-table tbody tr:hover { 
            background: #f8fffe;
        }
        
        .customer-table td { 
            padding: 14px 15px;
            font-size: 13px;
            color: #2c3e50;
        }
        
        .job-badge { 
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }
        
        .device-badge { 
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 12px;
            display: inline-block;
        }
        
        .date-text {
            color: #5a6c7d;
            font-weight: 500;
        }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e8ecef;
        }
        
        .showing-text {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .pagination { 
            display: flex;
            gap: 5px;
        }
        
        .pagination a, .pagination span { 
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            background: white;
            transition: all 0.2s ease;
            font-size: 12px;
        }
        
        .pagination a:hover {
            background: #f8f9fa;
            border-color: #2ecc71;
        }
        
        .pagination .active { 
            background: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 30px;
            color: #95a5a6;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        .empty-state-text {
            font-size: 16px;
            font-weight: 500;
            color: #7f8c8d;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .container {
                margin-top: 20px;
            }
            
            .stats-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-container {
                flex-direction: column;
            }
            
            .add-btn {
                width: 100%;
                justify-content: center;
            }
            
            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .right-controls {
                flex-direction: column;
                width: 100%;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .customer-table {
                font-size: 11px;
            }
            
            .customer-table th,
            .customer-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Stats and Add Button -->
    <div class="stats-header">
        <div class="stats-container">
            <div class="stat-card green">
                <div class="stat-info">
                    <h3><span class="stat-icon">👥</span> Total Customers</h3>
                    <div class="number"><?= $total_customers ?></div>
                </div>
            </div>
            <div class="stat-card pink">
                <div class="stat-info">
                    <h3><span class="stat-icon">📈</span> New This Month</h3>
                    <div class="number"><?= $monthly_customers ?></div>
                </div>
            </div>
        </div>
        <a href="register.php" class="add-btn">
            <span>+</span>
            <span>Add Customer</span>
        </a>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <div class="table-controls">
            <h2>All Customers</h2>
            <div class="right-controls">
                <form method="GET" action="">
                    <div class="search-box">
                        <span style="font-size: 14px;">🔍</span>
                        <input type="text" name="search" placeholder="Search here..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </form>
                <a href="#" class="view-all">View All →</a>
            </div>
        </div>
        
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>NIC</th>
                    <th>Job ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone NO</th>
                    <th>Device</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($customers_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($customers_result)): ?>
                        <tr onclick="window.location.href='customer_details.php?phone=<?= urlencode($row['phone_number']) ?>'">
                            <td class="date-text">
                                <?= $row['job_date'] ? date('d/m/Y', strtotime($row['job_date'])) : '-' ?>
                            </td>
                            <td><?= substr($row['phone_number'], 0, 12) ?></td>
                            <td><span class="job-badge"><?= htmlspecialchars($row['job_no']) ?></span></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td style="color: #5a6c7d;"><?= htmlspecialchars($row['email']) ?></td>
                            <td style="font-family: monospace; font-weight: 500;"><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><span class="device-badge"><?= htmlspecialchars($row['all_devices']) ?></span></td>
                            <td style="color: #7f8c8d; font-size: 12px;"><?= htmlspecialchars($row['address']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-state-icon">🔍</div>
                                <div class="empty-state-text">
                                    No records found<?= $search ? ' for "'.htmlspecialchars($search).'"' : '' ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination-container">
            <div class="showing-text">
                Showing data 1 to <?= min($records_per_page, $total_records) ?> of <?= $total_records ?> entries
            </div>
            
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <a href="?page=1&search=<?= urlencode($search) ?>">«</a>
                <?php for($i=max(1, $page-2); $i<=min($total_pages, $page+2); $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($i==$page)?'active':'' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>">»</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>