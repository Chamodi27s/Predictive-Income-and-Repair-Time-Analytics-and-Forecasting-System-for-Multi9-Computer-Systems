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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destroyed Items - Multi9</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2ecc71;
            --primary-hover: #27ae60;
            --primary-dark: #229954;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --secondary: #64748b;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1a202c;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
            padding: 140px 20px 40px 20px;
            color: var(--text-main);
        }

        .page-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header Card */
        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #2ecc71 100%);
            padding: 36px 40px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: white;
            text-align: center;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .page-header p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 500;
        }

        /* Container */
        .container {
            background: var(--card-bg);
            padding: 36px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            animation: fadeIn 0.5s ease-out;
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

        .header-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        /* Table Container */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        /* Table Styling */
        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
        }

        .report-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .report-table th {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 16px 18px;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .report-table th:first-child {
            border-top-left-radius: 12px;
        }

        .report-table th:last-child {
            border-top-right-radius: 12px;
        }

        .report-table tbody tr {
            transition: all 0.3s ease;
            background: white;
        }

        .report-table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .report-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #f0f2f5;
            text-align: center;
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
        }

        .report-table td b {
            color: var(--text-dark);
            font-weight: 800;
        }

        /* Job Badge */
        .job-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(25, 118, 210, 0.15);
        }

        /* Date Badge */
        .badge-date {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #e65100;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 13px;
            display: inline-block;
            border: 2px solid #ffcc80;
            letter-spacing: 0.3px;
        }

        /* Device Info */
        .device-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
        }

        .device-name {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 14px;
        }

        .device-issue {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
        }

        /* Delete Button */
        .btn-delete-perm {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-delete-perm:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        /* Empty State */
        .empty-msg {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
            font-style: normal;
            font-weight: 600;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 120px 15px 30px 15px;
            }

            .page-header {
                padding: 24px 28px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .container {
                padding: 24px;
            }

            .report-table {
                font-size: 12px;
            }

            .report-table th,
            .report-table td {
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="page-header">
        <h1>🗑️ Destroyed Items</h1>
        <p>Records of permanently destroyed devices</p>
    </div>
</div>

<div class="container">
    <h2 class="header-title">📋 Destroyed Items Records</h2>
    
    <div class="table-container">
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
                        <td>
                            <span class="job-badge">#<?= $row['job_no'] ?></span>
                        </td>
                        <td>
                            <b><?= htmlspecialchars($row['customer_name']) ?></b>
                        </td>
                        <td>
                            <div class="device-info">
                                <span class="device-name">📱 <?= htmlspecialchars($row['device_name']) ?></span>
                                <span class="device-issue"><?= htmlspecialchars($row['issue_name']) ?></span>
                            </div>
                        </td>
                        <td>
                            <b><?= date('M d, Y', strtotime($row['completed_date'])) ?></b>
                        </td>
                        <td>
                            <span class="badge-date"><?= date('M d, Y', strtotime($row['destroy_notice_sent_date'])) ?></span>
                        </td>
                        <td>
                            <a href="?delete_id=<?= $row['job_device_id'] ?>" 
                               class="btn-delete-perm" 
                               onclick="return confirm('මෙම වාර්තාව සදහටම ඉවත් කිරීමට අවශ්‍යද?')">
                                🗑️ Permanent Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-msg">
                            <span class="empty-state-icon">📋</span>
                            <strong>No destroyed items found in the records.</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>