<?php
include 'db_config.php';
include 'navbar.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Total customers
$total_query = "SELECT COUNT(*) as total FROM customer";
$total_result = mysqli_query($conn, $total_query);
$total_customers = mysqli_fetch_assoc($total_result)['total'];

// Total this month
$month_query = "SELECT COUNT(*) as monthly FROM customer 
                WHERE MONTH(CURDATE()) = MONTH(CURDATE())"; // No date column in customer
$monthly_customers = 0; // Since table doesn't have date, leave 0 or use job table if needed

// Get customers with jobs and devices
$where_clause = '';
if($search) {
    $where_clause = "WHERE customer.customer_name LIKE '%$search%' 
                     OR customer.phone_number LIKE '%$search%' 
                     OR customer.email LIKE '%$search%'";
}

$customers_query = "
SELECT customer.phone_number, customer.customer_name, customer.email, customer.address,
       job.job_no, job.job_date, job_device.device_name
FROM customer
LEFT JOIN job ON customer.phone_number = job.phone_number
LEFT JOIN job_device ON job.job_no = job_device.job_no
$where_clause
ORDER BY job.job_date DESC
LIMIT $records_per_page OFFSET $offset
";
$customers_result = mysqli_query($conn, $customers_query);

// Total pages
$total_pages_query = "
SELECT COUNT(*) as total 
FROM customer
LEFT JOIN job ON customer.phone_number = job.phone_number
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
<title>Customer Dashboard</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stats Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 35px;
    }

    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #10b981, #059669);
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
    }

    .stat-card.green {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 2px solid #a7f3d0;
    }

    .stat-card.pink {
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
        border: 2px solid #f9a8d4;
    }

    .stat-info h3 {
        font-size: 14px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-info .number {
        font-size: 42px;
        font-weight: 800;
        background: linear-gradient(135deg, #10b981, #059669);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-card.pink .number {
        background: linear-gradient(135deg, #ec4899, #db2777);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-icon {
        font-size: 50px;
        opacity: 0.9;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    /* Add Customer Button */
    .add-customer-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 16px 32px;
        border: none;
        border-radius: 15px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .add-customer-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .add-customer-btn:hover::before {
        left: 100%;
    }

    .add-customer-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.6);
    }

    .add-customer-btn:active {
        transform: translateY(-1px) scale(1.02);
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 25px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 20px;
        padding-bottom: 20px;
        border-bottom: 3px solid #f0fdf4;
    }

    .table-title {
        font-size: 26px;
        font-weight: 800;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .table-title::before {
        content: '';
        width: 6px;
        height: 35px;
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 3px;
    }

    /* Search Box */
    .search-box {
        display: flex;
        align-items: center;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 15px;
        padding: 12px 20px;
        gap: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .search-box:focus-within {
        border-color: #10b981;
        background: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        transform: translateY(-2px);
    }

    .search-box span {
        font-size: 20px;
    }

    .search-box input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 15px;
        width: 280px;
        font-weight: 500;
        color: #1f2937;
    }

    .search-box input::placeholder {
        color: #9ca3af;
    }

    /* Table Styles */
    .customer-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .customer-table thead tr {
        background: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%);
    }

    .customer-table th {
        padding: 18px 20px;
        text-align: left;
        font-weight: 700;
        color: #059669;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .customer-table th:first-child {
        border-radius: 12px 0 0 12px;
    }

    .customer-table th:last-child {
        border-radius: 0 12px 12px 0;
    }

    .customer-table tbody tr {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-radius: 12px;
    }

    .customer-table tbody tr:hover {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        transform: translateX(8px) scale(1.01);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
    }

    .customer-table td {
        padding: 18px 20px;
        color: #4b5563;
        font-size: 14px;
        font-weight: 500;
        border: none;
    }

    .customer-table td:first-child {
        border-radius: 12px 0 0 12px;
        font-weight: 600;
        color: #059669;
    }

    .customer-table td:last-child {
        border-radius: 0 12px 12px 0;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination span {
        padding: 12px 18px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        text-decoration: none;
        color: #4b5563;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        background: white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .pagination a:hover {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-color: #10b981;
        transform: translateY(-3px) scale(1.1);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .pagination .active {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-color: #10b981;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        transform: scale(1.1);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .customer-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
        
        .customer-table thead,
        .customer-table tbody,
        .customer-table tr,
        .customer-table th,
        .customer-table td {
            display: block;
        }
        
        .customer-table thead {
            display: none;
        }
        
        .customer-table tbody tr {
            margin-bottom: 20px;
            border-radius: 15px;
            padding: 15px;
        }
        
        .customer-table tbody tr:hover {
            transform: scale(1.02);
        }
        
        .customer-table td {
            padding: 12px;
            text-align: left;
            position: relative;
            padding-left: 140px;
            border-radius: 8px !important;
        }
        
        .customer-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
    }

    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr;
        }

        .search-box input {
            width: 180px;
        }

        .table-title {
            font-size: 22px;
        }

        .stat-info .number {
            font-size: 36px;
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 80px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 24px;
        margin-bottom: 10px;
        color: #1f2937;
    }

    .empty-state p {
        font-size: 16px;
    }
</style>
</head>
<body>
<div class="container">
    <div class="stats-container">
        <div class="stat-card green">
            <div class="stat-info">
                <h3>Total Customers</h3>
                <div class="number"><?= $total_customers ?></div>
            </div>
            <div class="stat-icon">👥</div>
        </div>

        <div class="stat-card pink">
            <div class="stat-info">
                <h3>New This Month</h3>
                <div class="number"><?= $monthly_customers ?></div>
            </div>
            <div class="stat-icon">📈</div>
        </div>
    </div>

    <a href="register.php" class="add-customer-btn">➕ Add Customer</a>

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">All Customers</h2>
            <form method="GET" style="margin:0;">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" name="search" placeholder="Search customers..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </form>
        </div>

        <?php if(mysqli_num_rows($customers_result) > 0): ?>
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Phone</th>
                    <th>Job No</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Device</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($customers_result)): ?>
                <tr onclick="window.location.href='customer_details.php?phone=<?= urlencode($row['phone_number']) ?>'">
                    <td data-label="Date"><?= $row['job_date'] ? date('d/m/Y', strtotime($row['job_date'])) : '-' ?></td>
                    <td data-label="Phone"><?= htmlspecialchars($row['phone_number']) ?></td>
                    <td data-label="Job No"><?= htmlspecialchars($row['job_no']) ?></td>
                    <td data-label="Customer Name"><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                    <td data-label="Address"><?= htmlspecialchars($row['address']) ?></td>
                    <td data-label="Device"><?= htmlspecialchars($row['device_name']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>No Customers Found</h3>
            <p>Start by adding your first customer</p>
        </div>
        <?php endif; ?>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">◀</a>
            <?php endif; ?>

            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <?php if($i==$page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">▶</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>