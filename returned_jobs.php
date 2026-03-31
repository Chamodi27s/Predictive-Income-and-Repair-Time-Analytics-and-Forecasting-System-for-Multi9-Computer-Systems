<?php
include 'db_config.php';
include 'navbar.php';

// --- පරාමිතීන් ලබා ගැනීම ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// --- Query Logic (Status එක 'Returned' වූ දත්ත පමණක් ලබා ගැනීම) ---
$sql = "SELECT j.job_no, j.job_date, c.customer_name, j.phone_number, 
               jd.device_name, jd.issue_name, jd.solution, jd.completed_date
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        WHERE jd.device_status = 'Returned'";

// --- Search Logic (Job No, Phone, Name සඳහා) ---
if ($search != '') { 
    $sql .= " AND (j.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR c.customer_name LIKE '%$search%')"; 
}

$sql .= " ORDER BY jd.completed_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returned History - Multi9</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ඔබේ අනෙක් පිටු වලට සමාන CSS */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #10b981; 
            --secondary: #64748b;
            --bg-main: #f8fafc;
            --border: #e2e8f0;
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%); 
            padding: 120px 20px 40px 20px; 
        }

        .page-container { max-width: 1300px; margin: 0 auto; }

        .page-header { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            padding: 30px; border-radius: 20px; margin-bottom: 30px; 
            color: white; text-align: center; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }

        .search-container { display: flex; justify-content: center; margin-bottom: 35px; }
        .search-box { 
            display: flex; background: white; padding: 5px; border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%; max-width: 600px; border: 1px solid var(--border); 
        }
        .search-box input { flex: 1; border: none; padding: 12px; outline: none; }
        .search-box button { background: var(--primary); color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; }

        .table-container { background: white; border-radius: 15px; box-shadow: var(--shadow-lg); overflow: hidden; }
        .status-table { width: 100%; border-collapse: collapse; }
        .status-table th { background: #f1f5f9; color: var(--secondary); padding: 18px; font-size: 12px; text-transform: uppercase; }
        .status-table td { padding: 18px; border-bottom: 1px solid var(--border); text-align: center; font-size: 14px; }

        .badge-id { background: #f1f5f9; color: #475569; padding: 5px 10px; border-radius: 50px; font-weight: 700; border: 1px solid #e2e8f0; }
        
        /* Dark Mode */
        body.dark-mode { background: #020617 !important; color: #e2e8f0 !important; }
        body.dark-mode .table-container, body.dark-mode .search-box { background: #1e293b; border-color: #334155; }
        body.dark-mode .status-table th { background: #0f172a; }
        body.dark-mode .status-table td { border-color: #334155; }
    </style>
</head>
<body id="pageBody">

<div class="page-container">
    <div class="page-header">
        <h1>📦 Returned Jobs History</h1>
        <p>List of all devices successfully returned to customers</p>
    </div>

    <div class="search-container">
        <form action="" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search by Job No, Phone, or Name..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="table-container">
        <table class="status-table">
            <thead>
                <tr>
                    <th>Job No</th>
                    <th>Customer</th>
                    <th>Device & Issue</th>
                    <th>Repair Solution</th>
                    <th>Date Returned</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><span class="badge-id">#<?= $row['job_no'] ?></span></td>
                        <td style="text-align: left;">
                            <b><?= htmlspecialchars($row['customer_name']) ?></b><br>
                            <small style="color: var(--secondary)"><?= $row['phone_number'] ?></small>
                        </td>
                        <td style="text-align: left;">
                            <b><?= htmlspecialchars($row['device_name']) ?></b><br>
                            <small><?= htmlspecialchars($row['issue_name']) ?></small>
                        </td>
                        <td><small><?= $row['solution'] ?: 'No details recorded' ?></small></td>
                        <td><?= $row['completed_date'] ? date('M d, Y', strtotime($row['completed_date'])) : 'N/A' ?></td>
                        <td><span style="color: #10b981; font-weight: 800;">✅ RETURNED</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="padding: 50px;">No returned jobs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function applySavedTheme() {
        const isDark = localStorage.getItem("darkMode") === "enabled";
        if (isDark) document.getElementById('pageBody').classList.add("dark-mode");
    }
    applySavedTheme();
</script>
</body>
</html>