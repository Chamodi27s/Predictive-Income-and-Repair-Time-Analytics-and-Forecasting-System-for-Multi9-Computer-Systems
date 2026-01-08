<?php
include 'db_config.php';
include 'navbar.php';

// සදහටම ඉවත් කිරීමේ (Permanent Delete) Logic එක
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM job_device WHERE job_device_id = '$del_id'");
    header("Location: destroyed_items_view.php");
    exit();
}

// 'Destroyed' තත්ත්වයේ ඇති දත්ත පමණක් ලබා ගැනීම
$sql = "SELECT jd.*, j.job_no, c.customer_name 
        FROM job_device jd
        INNER JOIN job j ON jd.job_no = j.job_no
        INNER JOIN customer c ON j.phone_number = c.phone_number
        WHERE jd.device_status = 'Destroyed'
        ORDER BY jd.destroy_notice_sent_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Destroyed Items - Multi9</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding-top: 100px; }
        .container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .header-title { color: #d32f2f; text-align: center; margin-bottom: 25px; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background: #212121; color: white; padding: 15px; font-size: 14px; }
        .report-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; font-size: 14px; }
        .report-table tr:hover { background-color: #f9f9f9; }
        .badge-date { background: #ffecb3; color: #5f4b00; padding: 5px 10px; border-radius: 5px; font-weight: bold; font-size: 12px; }
        .btn-delete-perm { background: #c62828; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .btn-delete-perm:hover { background: #b71c1c; }
        .empty-msg { padding: 40px; text-align: center; color: #999; font-style: italic; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="header-title">🗑️ Destroyed Items Records</h2>
    
    <table class="report-table">
        <thead>
            <tr>
                <th>Job No</th>
                <th>Customer</th>
                <th>Device Details</th>
                <th>Completion Date</th>
                <th>Destroyed Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><b>#<?= $row['job_no'] ?></b></td>
                    <td><?= $row['customer_name'] ?></td>
                    <td><?= $row['device_name'] ?> (<?= $row['issue_name'] ?>)</td>
                    <td><?= date('Y-m-d', strtotime($row['completed_date'])) ?></td>
                    <td><span class="badge-date"><?= $row['destroy_notice_sent_date'] ?></span></td>
                    <td>
                        <a href="?delete_id=<?= $row['job_device_id'] ?>" 
                           class="btn-delete-perm" 
                           onclick="return confirm('මෙම වාර්තාව සදහටම ඉවත් කිරීමට අවශ්‍යද?')">Permanent Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="empty-msg">No destroyed items found in the records.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>