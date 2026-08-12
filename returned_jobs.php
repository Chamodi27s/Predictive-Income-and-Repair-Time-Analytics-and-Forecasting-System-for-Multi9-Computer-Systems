<?php
include 'db_config.php';
include 'navbar.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// grand total
$sql = "SELECT i.invoice_no, i.job_no, i.invoice_date, c.customer_name, j.phone_number, 
               jd.device_name, jd.issue_name, i.grand_total
        FROM invoice i
        INNER JOIN job j ON i.job_no = j.job_no
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON i.job_no = jd.job_no
        WHERE i.payment_status = 'Paid'";

if ($search != '') { 
    $sql .= " AND (i.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR c.customer_name LIKE '%$search%')"; 
}

$sql .= " ORDER BY i.invoice_no DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returned Jobs - Multi9</title>
    <link rel="stylesheet" href="CSS/global.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body.dark-mode {
            --bg-main: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
        }


        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            padding: 140px 20px 40px;
            transition: all 0.3s ease;
        }

        .container { max-width: 1300px; margin: 0 auto; }

        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 36px 40px;
            border-radius: 20px;
            margin-top: 15px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: white;
            text-align: center;
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        .search-box {
            display: flex;
            background: var(--card-bg);
            padding: 8px;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .search-box input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 12px 20px;
            color: var(--text-main);
            font-size: 15px;
            outline: none;
        }
        .search-box button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .search-box button:hover { background: var(--primary-hover); }

        .table-card {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .status-table { width: 100%; border-collapse: collapse; }
        .status-table th {
            background: #f8fafc;
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .status-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            color: #1e293b;
            font-size: 14px;
        }

        .job-id-badge {
            background: var(--bg-main);
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            color: var(--text-main);
            border: 1px solid var(--border);
        }
        .solution-text {
            font-size: 13px;
            color: var(--text-muted);
            max-width: 250px;
            margin: 0 auto;
            line-height: 1.5;
        }
        .status-pill {
            background: #dcfce7;
            color: #166534;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 800;
        }
    </style>
</head>
<body id="mainBody">

<div class="container">
    <div class="page-header">
        <h1 style="font-size: 32px; font-weight: 800;">Returned Jobs History</h1>
        <p style="opacity: 0.9; margin-top: 8px;">List of all devices successfully returned to customers</p>
    </div>

    <div class="search-container">
        <form action="" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search by Job No, Phone or Customer..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search Records</button>
        </form>
    </div>

    <div class="table-card">
        <table class="status-table">
            <thead>
                <tr>
                    <th>Job No</th>
                    <th>Customer Details</th>
                    <th>Device & Issue</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <span class="job-id-badge">#<?= $row['job_no'] ?></span>
                        </td>
                        <td style="text-align: left;">
                            <b style="font-size: 15px;"><?= htmlspecialchars($row['customer_name']) ?></b><br>
                            <small style="color: var(--text-muted)"><?= $row['phone_number'] ?></small>
                        </td>
                        <td style="text-align: left;">
                            <b><?= htmlspecialchars($row['device_name']) ?></b><br>
                            <small style="color: #ef4444;"><?= htmlspecialchars($row['issue_name']) ?></small>
                        </td>
                        <td><span class="status-pill">RETURNED</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="padding: 100px; color: var(--text-muted);">
                            No returned jobs found in the database.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function applyTheme() {
        const isDark = localStorage.getItem("darkMode") === "enabled";
        if (isDark) {
            document.getElementById('mainBody').classList.add('dark-mode');
        }
    }
    applyTheme();
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>