<?php 
include 'db_config.php'; 
include 'navbar.php';




// Search query 
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repair Management System - Job List</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-light: #f8fafc;
            --card-light: #ffffff;
            --text-light: #1e293b;
            --border-light: rgba(0,0,0,0.08);
            --accent: #10b981;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
            padding: var(--nav-height) 20px 20px; /* Offset for fixed navbar */
            margin: 0;
            transition: background 0.3s, color 0.3s;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #020617, #0f172a);
            color: #f1f5f9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-light);
            padding: 35px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }
        
        body.dark-mode .container {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .header-flex { 
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; 
            flex-wrap: wrap; gap: 20px;
        }
        
        .header-flex h2 { 
            font-size: 28px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 12px; 
        }
        body.dark-mode .header-flex h2 { color: white; }

        .search-wrapper { position: relative; width: 350px; }
        .search-wrapper i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px; }
        .search-box {
            width: 100%; padding: 14px 20px 14px 45px;
            border-radius: 50px; border: 1px solid var(--border-light);
            background: #f1f5f9; font-size: 14px; transition: 0.3s;
            outline: none; color: var(--text-light);
            box-sizing: border-box;
        }
        .search-box:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(16,185,129,0.2); background: white; }
        
        body.dark-mode .search-box {
            background: rgba(15, 23, 42, 0.6); color: white; border: 1px solid rgba(255,255,255,0.1);
        }
        body.dark-mode .search-box:focus { background: rgba(15, 23, 42, 0.9); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { 
            text-align: left; padding: 14px 20px; background: #f8fafc;
            color: #64748b; font-size: 11px;
            text-transform: uppercase; font-weight: 700;
            letter-spacing: 0.6px; border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        
        td { 
            padding: 14px 20px; background: #ffffff; 
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            color: #1e293b;
            transition: 0.3s; 
            line-height: 1.4;
        }
        td:first-child { border-left: 4px solid transparent; }
        td:last-child {  }

        body.dark-mode td { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); }

        tr:hover td { background: #f8fafc; }
        body.dark-mode tr:hover td { background: rgba(255,255,255,0.04); }
        tr:hover td:first-child { border-left-color: var(--accent); }

        .job-no { font-weight: 800; color: var(--accent); font-size: 15px; }

        .status-badge {
            padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; 
            display: inline-flex; align-items: center; gap: 6px;
        }
        .completed { background: #dcfce7; color: #166534; }
        body.dark-mode .completed { background: rgba(34, 197, 94, 0.15); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.3); }
        
        .pending { background: #fef9c3; color: #854d0e; }
        body.dark-mode .pending { background: rgba(234, 179, 8, 0.15); color: #fde047; border: 1px solid rgba(234, 179, 8, 0.3); }

        .btn-bill {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white; padding: 10px 18px; text-decoration: none; border-radius: 12px; 
            font-size: 13px; font-weight: 700;
            display: inline-flex; align-items: center; gap: 6px; transition: 0.3s; 
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); border: none; cursor: pointer;
        }
        .btn-bill:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4); }
        
        @media(max-width: 768px) {
            .header-flex { flex-direction: column; align-items: flex-start; }
            .search-wrapper { width: 100%; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2><i class="ph-fill ph-check-circle" style="color: var(--accent);"></i> Active Repair Records</h2>
        
        <form method="GET">
            <div class="search-wrapper">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" 
                       class="search-box" placeholder="Search Job No, Name, Device..." onchange="this.form.submit()">
            </div>
            <?php if($q): ?>
                <a href="customer_list.php" style="font-size: 13px; color: #ef4444; text-decoration: none; font-weight: 600; margin-top: 10px; display: inline-block;"><i class="ph-bold ph-x"></i> Clear Search</a>
            <?php endif; ?>
        </form>
    </div>

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
                            <td><span class='job-no'>#{$row['job_no']}</span></td>
                            <td style='font-weight: 600;'>{$row['customer_name']}</td>
                            <td>{$row['device_name']}</td>
                            <td>" . ($row['issue_name'] ? $row['issue_name'] : '<span style="color:#94a3b8; font-style: italic;">No issue recorded</span>') . "</td>
                            <td style='color: #64748b;'>{$row['job_date']}</td>
                            <td><span class='status-badge {$statusClass}'><i class='ph-bold ph-check'></i> {$row['job_status']}</span></td>
                            <td>
                                <a href='generate_invoice.php?job_no={$row['job_no']}' class='btn-bill'><i class='ph-bold ph-receipt'></i> Make Bill</a>
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

<script>
    // Theme sync
    function syncTheme() {
        const theme = localStorage.getItem("darkMode");
        if (theme === "enabled") {
            document.body.classList.add("dark-mode");
        } else {
            document.body.classList.remove("dark-mode");
        }
    }
    syncTheme();
    window.addEventListener('storage', syncTheme);
</script>

</body>

</html>