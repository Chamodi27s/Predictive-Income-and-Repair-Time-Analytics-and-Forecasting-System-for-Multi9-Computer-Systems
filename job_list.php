<?php 
include 'db_config.php';
include 'navbar.php';

// --- 1. ස්වයංක්‍රීයව Status Update කිරීම ---
mysqli_query($conn, "UPDATE job_device SET device_status = 'Destroyed' 
                     WHERE destroy_notice_sent_date IS NOT NULL 
                     AND DATEDIFF(NOW(), destroy_notice_sent_date) >= 7 
                     AND device_status != 'Destroyed'");

// පරාමිතීන් ලබා ගැනීම
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

// Query Logic
$sql = "SELECT j.job_no, j.job_date, t.name as technician_name, c.customer_name, j.phone_number, 
                jd.job_device_id, jd.device_name, jd.issue_name, jd.solution,
                CASE WHEN inv.payment_status = 'Paid' THEN 'Returned' ELSE jd.device_status END AS device_status,
                jd.completed_date, jd.destroy_notice_sent_date, jd.rent_warning_sent,
                inv.payment_status
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        LEFT JOIN invoice inv ON jd.job_no = inv.job_no 
        WHERE j.job_status = 'Approved' 
        AND jd.device_status != 'Destroyed'";

// Status Filter Logic
if ($filter_status != '') { 
    if($filter_status == 'Returned') {
        $sql .= " AND inv.payment_status = 'Paid'";
    } else {
        $sql .= " AND jd.device_status = '$filter_status' AND (inv.payment_status != 'Paid' OR inv.payment_status IS NULL)";
    }
}

// Date Filter Logic
if ($date_filter == 'today') { $sql .= " AND DATE(j.job_date) = CURDATE()"; } 
elseif ($date_filter == '2weeks') { $sql .= " AND j.job_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)"; } 
elseif ($date_filter == 'monthly') { $sql .= " AND MONTH(j.job_date) = MONTH(NOW()) AND YEAR(j.job_date) = YEAR(NOW())"; } 
elseif ($date_filter == 'yearly') { $sql .= " AND YEAR(j.job_date) = YEAR(NOW())"; }

// Search Logic
if ($search != '') { 
    $sql .= " AND (j.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR jd.device_name LIKE '%$search%' OR jd.issue_name LIKE '%$search%' OR c.customer_name LIKE '%$search%')"; 
}

$sql .= " ORDER BY jd.job_device_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - Multi9</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2ecc71; --primary-hover: #27ae60; --primary-dark: #229954;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --purple: #9b59b6; --orange: #e67e22; --blue: #3b82f6;
            --secondary: #64748b; --bg-main: #f8fafc; --card-bg: #ffffff;
            --text-main: #1a202c; --text-dark: #0f172a; --text-muted: #64748b;
            --border: #e2e8f0; --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%); padding: 120px 20px 40px 20px; color: var(--text-main); }
        .page-container { max-width: 1400px; margin: 0 auto; }
        .page-header { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 30px; border-radius: 20px; margin-bottom: 30px; color: white; text-align: center; }
        .badge { padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .filter-container { display: flex; justify-content: center; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .filter-tag { padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 13px; color: white; transition: all 0.2s; }
        .active-tag { transform: scale(1.05); outline: 3px solid rgba(0,0,0,0.1); }
        
        .search-container { display: flex; justify-content: center; margin-bottom: 25px; align-items: center; gap: 10px; }
        .search-box { display: flex; background: white; padding: 5px; border-radius: 12px; box-shadow: var(--shadow-md); width: 100%; max-width: 500px; border: 1px solid var(--border); }
        .search-box input { flex: 1; border: none; padding: 10px 15px; outline: none; border-radius: 8px; }
        .search-box button { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }

        .table-container { background: white; border-radius: 15px; box-shadow: var(--shadow-lg); overflow-x: auto; }
        .status-table { width: 100%; border-collapse: collapse; min-width: 1100px; }
        .status-table th { background: #f1f5f9; color: var(--text-muted); padding: 15px; font-size: 12px; text-align: center; }
        .status-table td { padding: 15px; border-bottom: 1px solid var(--border); text-align: center; }
        .inline-input { width: 100%; border: 1px solid transparent; background: #f8fafc; padding: 8px; border-radius: 6px; text-align: center; font-size: 13px; }
        .inline-input.editing { border-color: var(--primary); background: white; }
        .solution-text { min-height: 40px; resize: vertical; font-family: inherit; }

        body.dark-mode { background: #020617 !important; color: #e2e8f0 !important; }
        body.dark-mode .table-container { background: rgba(30, 41, 59, 0.6) !important; }
    </style>
</head>
<body id="jobBody">

<div class="page-container">
    <div class="page-header">
        <h1>⚙️ Job Management</h1>
        <p>Manage and track your service orders efficiently</p>
    </div>

    <div class="search-container">
        <form action="" method="GET" class="search-box">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <input type="hidden" name="date_filter" value="<?= htmlspecialchars($date_filter) ?>">
            <input type="text" name="search" placeholder="Search by Job No, Phone, Name..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
        <a href="returned_jobs.php" class="filter-tag" style="background: #0f172a; display: flex; align-items: center; gap: 8px; height: 45px;">
            📦 View History
        </a>
    </div>

    <div class="filter-container">
        <a href="?search=<?= $search ?>&date_filter=<?= $date_filter ?>" class="filter-tag <?= $filter_status == '' ? 'active-tag' : '' ?>" style="background: var(--secondary)">📋 All Jobs</a>
        <a href="?status=Pending&search=<?= $search ?>&date_filter=<?= $date_filter ?>" class="filter-tag <?= $filter_status == 'Pending' ? 'active-tag' : '' ?>" style="background: var(--warning)">⏳ Pending</a>
        <a href="?status=In Progress&search=<?= $search ?>&date_filter=<?= $date_filter ?>" class="filter-tag <?= $filter_status == 'In Progress' ? 'active-tag' : '' ?>" style="background: var(--blue)">🔧 In Progress</a>
        <a href="?status=Completed&search=<?= $search ?>&date_filter=<?= $date_filter ?>" class="filter-tag <?= $filter_status == 'Completed' ? 'active-tag' : '' ?>" style="background: var(--success)">✅ Completed</a>
        <a href="?status=Returned&search=<?= $search ?>&date_filter=<?= $date_filter ?>" class="filter-tag <?= $filter_status == 'Returned' ? 'active-tag' : '' ?>" style="background: #10b981">📦 Returned</a>
    </div>

    <div class="table-container">
        <table class="status-table">
            <thead>
                <tr>
                    <th>JOB NO</th>
                    <th>CUSTOMER DETAILS</th>
                    <th>DEVICE NAME</th>
                    <th>ISSUE DESCRIPTION</th>
                    <th>SOLUTION / REPAIR DETAILS</th> 
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $id = $row['job_device_id'];
                        $current_status = $row['device_status'];
                        $is_paid = ($row['payment_status'] == 'Paid');
                        $is_locked = ($current_status == 'Completed' || $current_status == 'Returned');
                        $disable_pending = ($current_status != 'Pending');

                        $days_passed = 0; $delay_fee = 0;
                        $is_destroy_ready = false; $needs_sms_warning = false;
                        $needs_rent_warning = false;

                        if($current_status == 'Completed' && $row['completed_date'] != null) {
                            $days_passed = floor((time() - strtotime($row['completed_date'])) / 86400);
                            if($days_passed > 90) { $delay_fee = ceil(($days_passed - 90) / 30) * 100; }
                            if($days_passed >= 90 && $days_passed < 365 && $row['rent_warning_sent'] == 0) $needs_rent_warning = true;
                            if($days_passed >= 365 && empty($row['destroy_notice_sent_date'])) $needs_sms_warning = true;
                            if($days_passed >= 372 && !empty($row['destroy_notice_sent_date'])) $is_destroy_ready = true;
                        }
                    ?>
                    <tr id="row-<?= $id ?>">
                        <td>
                            <span class="badge">#<?= $row['job_no'] ?></span><br>
                            <small style="font-size: 9px; color: #94a3b8;"><?= date('Y-m-d', strtotime($row['job_date'])) ?></small>
                        </td>
                        <td style="text-align: left;">
                            <b><?= htmlspecialchars($row['customer_name']) ?></b><br>
                            <small style="color: var(--text-muted)"><?= $row['phone_number'] ?></small>
                        </td>
                        <td><input type="text" id="dev-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['device_name']) ?>" readonly></td>
                        <td><input type="text" id="iss-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['issue_name']) ?>" readonly></td>
                        <td>
                            <textarea id="sol-<?= $id ?>" class="inline-input solution-text" placeholder="No solution added..." readonly><?= htmlspecialchars($row['solution'] ?? '') ?></textarea>
                        </td>
                        <td>
                            <select id="stat-<?= $id ?>" onchange="updateStatusAndSMS(<?= $id ?>)" style="width: 120px;" <?= $is_locked ? 'disabled' : '' ?>>
                                <option value="Pending" <?= $current_status == 'Pending' ? 'selected' : '' ?> <?= $disable_pending ? 'disabled' : '' ?>>Pending</option>
                                <option value="In Progress" <?= $current_status == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= $current_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Returned" <?= $current_status == 'Returned' ? 'selected' : '' ?>>Returned</option>
                                <option value="Cancel" <?= $current_status == 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                            </select>
                            <?php if($delay_fee > 0): ?>
                                <div class="rent-fee" style="font-size:11px; color:var(--danger); font-weight:bold;">💰 Rs. <?= $delay_fee ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                                <?php if(!$is_locked): ?>
                                    <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" style="background: var(--blue); color:white; border:none; padding:8px; border-radius:5px; cursor:pointer;">✏️</button>
                                    <button onclick="deleteItem(<?= $id ?>)" style="background: #f8d7da; color: #721c24; border:none; padding:8px; border-radius:5px; cursor:pointer;">🗑️</button>
                                <?php else: ?>
                                    <?php if($is_paid): ?>
                                        <span class="badge" style="background: var(--success); color: white;">PAID & CLOSED</span>
                                    <?php else: ?>
                                        <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&fee=<?= $delay_fee ?>" style="background: var(--orange); color:white; padding:8px; border-radius:5px; text-decoration:none; font-size:12px;">📄 Bill</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="padding: 50px; text-align: center;">No jobs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Logic Functions (වෙනසක් නැත)
function applySavedTheme() {
    const isDark = localStorage.getItem("darkMode") === "enabled";
    if (isDark) document.getElementById('jobBody').classList.add("dark-mode");
}
applySavedTheme();

function updateStatusAndSMS(id) {
    const statusSelect = document.getElementById('stat-' + id);
    const newStatus = statusSelect.value;
    const devName = document.getElementById('dev-' + id).value;
    const issName = document.getElementById('iss-' + id).value;
    const solution = document.getElementById('sol-' + id).value;

    let params = `id=${id}&device_name=${encodeURIComponent(devName)}&issue_name=${encodeURIComponent(issName)}&solution=${encodeURIComponent(solution)}&device_status=${encodeURIComponent(newStatus)}`;
    
    fetch('./inline_update_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(res => res.text()).then(data => {
        if(data.trim() === "Success") {
            location.reload();
        } else {
            alert("Error: " + data);
        }
    });
}

function toggleEdit(id) {
    let dev = document.getElementById('dev-' + id);
    let iss = document.getElementById('iss-' + id);
    let sol = document.getElementById('sol-' + id);
    let btn = document.getElementById('btn-edit-' + id);

    if (dev.readOnly) {
        dev.readOnly = false; iss.readOnly = false; sol.readOnly = false;
        dev.classList.add('editing'); iss.classList.add('editing'); sol.classList.add('editing');
        btn.innerHTML = "💾"; btn.style.background = "var(--success)";
    } else {
        updateStatusAndSMS(id);
    }
}
</script>
</body>
</html>