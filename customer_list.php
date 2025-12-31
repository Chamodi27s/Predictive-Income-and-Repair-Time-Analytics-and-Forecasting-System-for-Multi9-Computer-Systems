<?php 
include 'db_config.php'; 
include 'navbar.php';




// Search query එක ලබා ගැනීම
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
        .btn-bill { background: #1976d2; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        .search-box { padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-search { padding: 10px 20px; background: #2e7d32; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2>Active Repair Records</h2>
    
    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" 
               class="search-box" placeholder="Search Job No, Name, Device or Issue...">
        <button type="submit" class="btn-search">Search</button>
        <?php if($q): ?>
            <a href="customer_list.php" style="margin-left:10px; color: #666;">Clear Search</a>
        <?php endif; ?>
    </form>

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
            // SQL Query: Job, Customer සහ Job_Device වගු JOIN කර ඇත
           // නිවැරදි කළ SQL Query එක
$sql = "SELECT j.job_no, c.customer_name, jd.device_name, jd.issue_name, j.job_date, j.job_status 
        FROM job j 
        LEFT JOIN customer c ON j.phone_number = c.phone_number 
        LEFT JOIN job_device jd ON j.job_no = jd.job_no 
        WHERE j.job_status = 'Approved' 
        AND (j.job_no LIKE '%$q%' 
             OR c.customer_name LIKE '%$q%' 
             OR jd.device_name LIKE '%$q%' 
             OR jd.issue_name LIKE '%$q%') 
        ORDER BY j.job_date DESC";

            $res = $conn->query($sql);

            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $statusClass = (strtolower($row['job_status']) == 'pending') ? 'pending' : 'completed';
                    echo "<tr>
                            <td><strong>#{$row['job_no']}</strong></td>
                            <td>{$row['customer_name']}</td>
                            <td>{$row['device_name']}</td>
                            <td>" . ($row['issue_name'] ? $row['issue_name'] : '<i style="color:gray;">No issue recorded</i>') . "</td>
                            <td>{$row['job_date']}</td>
                            <td><span class='status-badge {$statusClass}'>{$row['job_status']}</span></td>
                            <td>
                                <a href='generate_invoice.php?job_no={$row['job_no']}' class='btn-bill'>Make Bill</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center; padding:30px; color:red;'>No records found for '<b>$q</b>'</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>