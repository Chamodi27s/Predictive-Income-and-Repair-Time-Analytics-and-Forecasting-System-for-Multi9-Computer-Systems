 <?php
include("../config/db.php");

/* -------------------------
   Get Search Query
-------------------------- */
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repair Management System - Job List</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2e7d32; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .pending { background: #fff3e0; color: #e65100; }
        .completed { background: #e8f5e9; color: #2e7d32; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; }
        .btn-view { background: #1976d2; }
        .btn-bill { background: #2e7d32; }
        .search-box { padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-search { padding: 10px 20px; background: #2e7d32; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-clear { padding: 10px 20px; background: #999; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Repair Job Records</h2>

    <!-- 🔍 Search Form -->
    <form method="GET" style="margin-bottom: 20px;">
        <input type="text"
               name="q"
               value="<?php echo htmlspecialchars($q); ?>"
               class="search-box"
               placeholder="Search Job No, Customer, Device or Issue">

        <button type="submit" class="btn-search">Search</button>

        <?php if (!empty($q)): ?>
            <button type="button"
                    onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'"
                    class="btn-clear">
                Clear
            </button>
        <?php endif; ?>
    </form>

    <!-- 📋 Job Table -->
    <table>
        <thead>
            <tr>
                <th>Job No</th>
                <th>Customer Name</th>
                <th>Device</th>
                <th>Issue / Fault</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

        <?php
        $sql = "SELECT 
                    j.job_no,
                    j.job_date,
                    j.job_status,
                    c.customer_name,
                    jd.device_name,
                    jd.issue_name
                FROM job j
                LEFT JOIN customer c ON j.phone_number = c.phone_number
                LEFT JOIN job_device jd ON j.job_no = jd.job_no
                WHERE (
                    j.job_no LIKE '%$q%' 
                    OR c.customer_name LIKE '%$q%' 
                    OR jd.device_name LIKE '%$q%' 
                    OR jd.issue_name LIKE '%$q%'
                )
                ORDER BY j.job_date DESC";

        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {

                $status = strtolower($row['job_status']);
                $statusClass = ($status === 'pending') ? 'pending' : 'completed';

                echo "<tr>
                        <td><strong>#{$row['job_no']}</strong></td>
                        <td>" . htmlspecialchars($row['customer_name'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['device_name'] ?? 'N/A') . "</td>
                        <td>" .
                            ($row['issue_name']
                                ? htmlspecialchars($row['issue_name'])
                                : "<i style='color:gray;'>No issue recorded</i>")
                        . "</td>
                        <td>{$row['job_date']}</td>
                        <td>
                            <span class='status-badge {$statusClass}'>
                                {$row['job_status']}
                            </span>
                        </td>
                        <td>";

                // ✅ Action buttons
                if ($status === 'pending') {
                    // Pending jobs → View customer details
                    echo "<a href='../customers/view_customers.php?job_no={$row['job_no']}' class='btn btn-view'>View</a>";
                } else {
                    // Completed jobs → Generate invoice
                    echo "<a href='http://localhost/Predictive-Income-and-Repair-Time-Analytics-and-Forecasting-System-for-Multi9-Computer-Systems/invoices/generate_invoice.php?job_no={$row['job_no']}' target='_blank' class='btn btn-bill'>Make Bill</a>";
                }

                echo "</td></tr>";
            }
        } else {
            echo "<tr>
                    <td colspan='7' style='text-align:center; padding:30px; color:red;'>
                        No records found
                    </td>
                  </tr>";
        }
        ?>

        </tbody>
    </table>
</div>

</body>
</html>
