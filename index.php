<?php
include 'db_config.php';
include 'navbar.php';

// Ada dinaya
$today = date('Y-m-d');

// 1. Pending Repairs (job_device table eken status eka check kirima)
$pending_sql = "SELECT COUNT(*) as count FROM job_device WHERE device_status = 'Pending'";
$pending_res = $conn->query($pending_sql);
$pending_count = $pending_res->fetch_assoc()['count'];

// 2. In-progress Repairs
$inprogress_sql = "SELECT COUNT(*) as count FROM job_device WHERE device_status = 'In Progress'";
$inprogress_res = $conn->query($inprogress_sql);
$inprogress_count = $inprogress_res->fetch_assoc()['count'];

// 3. Completed Today (Ada completed karapu ewa)
// Meeta job table eka join karanna ona date eka job table eke thiyana nisa
$completed_sql = "SELECT COUNT(*) as count FROM job_device jd 
                  JOIN job j ON jd.job_no = j.job_no 
                  WHERE jd.device_status = 'Completed' AND j.job_date = '$today'";
$completed_res = $conn->query($completed_sql);
$completed_count = $completed_res->fetch_assoc()['count'];

// 4. Total Customers
$customer_sql = "SELECT COUNT(*) as count FROM customer";
$customer_res = $conn->query($customer_sql);
$total_customers = $customer_res->fetch_assoc()['count'];

// 5. Revenue Today (Invoice table eken ganna eka thamai wadath niwaradi)
$revenue_sql = "SELECT SUM(grand_total) as total FROM invoice WHERE DATE(invoice_date) = '$today'";
$revenue_res = $conn->query($revenue_sql);
$revenue_row = $revenue_res->fetch_assoc();
$revenue_today = $revenue_row['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f0fdf4; padding: 40px; }
        h1 { color: #444; margin-bottom: 40px; font-weight: 400; }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            max-width: 1200px;
        }
        .card {
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        /* Dashboard Colors */
        .bg-pending { background-color: #dcfce7; } /* Light Green */
        .bg-progress { background-color: #fee2e2; } /* Light Red */
        .bg-completed { background-color: #fef9c3; } /* Light Yellow */
        .bg-customers { background-color: #e0f2fe; } /* Light Blue */
        .bg-revenue { background-color: #ffedd5; } /* Light Orange */

        .card-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .card-title { font-weight: 600; font-size: 1.1rem; color: #374151; }
        .icon-box { background: rgba(255,255,255,0.5); padding: 8px; border-radius: 8px; font-size: 24px; }
        .card-value { font-size: 2.2rem; font-weight: 700; margin: 15px 0; color: #000; }
        .card-footer { font-size: 0.85rem; color: #4b5563; font-weight: 500; }
        .trend-up::before { content: "↑ "; }
        @media (max-width: 768px) { body { padding: 20px; } }
    </style>
</head>
<body>

    <h1>Welcome back, Vibuddha</h1>

    <div class="dashboard-grid">
        <div class="card bg-pending">
            <div class="card-header">
                <span class="card-title">Pending Repairs</span>
                <span class="icon-box">⏳</span>
            </div>
            <div class="card-value"><?php echo $pending_count; ?></div>
            <div class="card-footer trend-up">Current status</div>
        </div>

        <div class="card bg-progress">
            <div class="card-header">
                <span class="card-title">In-progress Repairs</span>
                <span class="icon-box">⌛</span>
            </div>
            <div class="card-value"><?php echo $inprogress_count; ?></div>
            <div class="card-footer trend-up">Actively working</div>
        </div>

        <div class="card bg-completed">
            <div class="card-header">
                <span class="card-title">Completed Today</span>
                <span class="icon-box">✅</span>
            </div>
            <div class="card-value"><?php echo $completed_count; ?></div>
            <div class="card-footer trend-up">Jobs finished today</div>
        </div>

        <div class="card bg-customers">
            <div class="card-header">
                <span class="card-title">Total Customers</span>
                <span class="icon-box">👥</span>
            </div>
            <div class="card-value"><?php echo $total_customers; ?></div>
            <div class="card-footer trend-up">Registered in system</div>
        </div>

        <div class="card bg-revenue">
            <div class="card-header">
                <span class="card-title">Revenue Today</span>
                <span class="icon-box">💰</span>
            </div>
            <div class="card-value">Rs.<?php echo number_format($revenue_today, 2); ?></div>
            <div class="card-footer trend-up">Today's total income</div>
        </div>
    </div>

</body>
</html>