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
            transition: background 0.3s ease;
        }

        /* ===== DARK MODE CSS ===== */
        body.dark-mode {
            background: #0f172a !important;
            color: #f1f5f9 !important;
        }

        body.dark-mode .container {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }

        body.dark-mode .header-title {
            color: #ffffff !important;
            border-bottom-color: #334155 !important;
        }

        body.dark-mode .report-table td {
            border-bottom-color: #334155 !important;
            color: #cbd5e1 !important;
            background: #1e293b !important;
        }

        body.dark-mode .report-table tbody tr:hover {
            background: #334155 !important;
        }

        body.dark-mode .report-table td b, 
        body.dark-mode .device-name {
            color: #ffffff !important;
        }

        body.dark-mode .table-container {
            border-color: #334155 !important;
        }

        body.dark-mode .empty-msg {
            color: #94a3b8 !important;
        }

        /* ===== LIGHT MODE STYLES (ORIGINAL) ===== */
        .page-container {
            max-width: 900px;
            margin: 0 auto;
        }

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

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
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

        .report-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #f0f2f5;
            text-align: center;
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
        }

        .job-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            display: inline-block;
        }

        .badge-date {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #e65100;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 13px;
            display: inline-block;
            border: 2px solid #ffcc80;
        }

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
        }

        .btn-delete-perm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        .empty-msg {
            padding: 60px 20px;
            text-align: center;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            body { padding-top: 120px; }
            .report-table { font-size: 12px; }
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
            <tbody id="tableBody">
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
                                <span class="device-name">📱 <?= htmlspecialchars($row['device_name']) ?></span><br>
                                <span class="device-issue" style="font-size: 12px; opacity: 0.8;"><?= htmlspecialchars($row['issue_name']) ?></span>
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
                            <span style="font-size: 40px; display: block; margin-bottom: 10px;">📋</span>
                            <strong>No destroyed items found in the records.</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// --- AUTO REFRESH ON MODE CHANGE ---
// Navbar එකේ ඇති Dark Mode toggle එක අනුව පිටුව refresh වීමට මෙම කොටස භාවිතා වේ.
let lastMode = document.body.classList.contains('dark-mode');
const observer = new MutationObserver(() => {
    let currentMode = document.body.classList.contains('dark-mode');
    if (currentMode !== lastMode) {
        lastMode = currentMode;
        location.reload(); 
    }
});
observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
</script>

</body>
<?php include 'chatbot.php'; ?>
</html>