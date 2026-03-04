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

// 2. SQL Query - Optimized for speed
$sql = "SELECT j.job_no, j.job_date, t.name as technician_name, c.customer_name, j.phone_number, 
               jd.job_device_id, jd.device_name, jd.issue_name, jd.device_status,
               jd.completed_date, jd.destroy_notice_sent_date 
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        WHERE j.job_status = 'Approved' 
        AND jd.device_status != 'billed'
        AND jd.device_status != 'Destroyed'"; 

// Status Filter
if ($filter_status != '') {
    $sql .= " AND jd.device_status = '$filter_status'";
}

// --- කාලය අනුව Filter කිරීමේ Logic එක ---
if ($date_filter == 'today') {
    $sql .= " AND DATE(j.job_date) = CURDATE()";
} elseif ($date_filter == '2weeks') {
    $sql .= " AND j.job_date >= DATE_SUB(NOW(), INTERVAL 14 DAY)";
} elseif ($date_filter == 'monthly') {
    $sql .= " AND MONTH(j.job_date) = MONTH(NOW()) AND YEAR(j.job_date) = YEAR(NOW())";
} elseif ($date_filter == 'yearly') {
    $sql .= " AND YEAR(j.job_date) = YEAR(NOW())";
}

// Search Logic
if ($search != '') {
    $sql .= " AND (j.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR jd.issue_name LIKE '%$search%' OR c.customer_name LIKE '%$search%')";
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
        /* Existing base styles kept for consistency */
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

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
            padding: 120px 20px 40px 20px;
            color: var(--text-main);
        }

        .page-container { max-width: 1200px; margin: 0 auto; }

        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 30px; border-radius: 20px; margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
            color: white; text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        .badge { padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .filter-container { display: flex; justify-content: center; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .filter-tag { padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.2s; color: white; }
        .active-tag { transform: scale(1.05); outline: 3px solid rgba(0,0,0,0.1); }

        .table-container { background: white; border-radius: 15px; box-shadow: var(--shadow-lg); overflow: hidden; }
        .status-table { width: 100%; border-collapse: collapse; }
        .status-table th { background: #f1f5f9; color: var(--text-muted); padding: 15px; font-size: 12px; text-align: center; }
        .status-table td { padding: 15px; border-bottom: 1px solid var(--border); text-align: center; }
        
        .inline-input {
            width: 100%; border: 1px solid transparent; background: #f8fafc;
            padding: 8px; border-radius: 6px; text-align: center; font-size: 14px;
        }
        .inline-input.editing { border-color: var(--primary); background: white; box-shadow: 0 0 5px rgba(46,204,113,0.3); }
        .btn-loading { opacity: 0.5; pointer-events: none; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="page-container">
    <div class="page-header">
        <h1>⚙️ Job Management</h1>
        <p>Manage and track your service orders efficiently</p>
    </div>

    <div class="filter-container">
        <a href="?date_filter=<?= $date_filter ?>" class="filter-tag tag-all <?= $filter_status == '' ? 'active-tag' : '' ?>" style="background: var(--secondary)">📋 All Jobs</a>
        <a href="?status=Pending&date_filter=<?= $date_filter ?>" class="filter-tag tag-pending <?= $filter_status == 'Pending' ? 'active-tag' : '' ?>" style="background: var(--warning)">⏳ Pending</a>
        <a href="?status=In Progress&date_filter=<?= $date_filter ?>" class="filter-tag tag-progress <?= $filter_status == 'In Progress' ? 'active-tag' : '' ?>" style="background: var(--blue)">🔧 In Progress</a>
        <a href="?status=Completed&date_filter=<?= $date_filter ?>" class="filter-tag tag-completed <?= $filter_status == 'Completed' ? 'active-tag' : '' ?>" style="background: var(--success)">✅ Completed</a>
    </div>

    <div style="text-align: center; margin-bottom: 15px; font-size: 13px;">
        <span style="color: var(--text-muted);">View: </span>
        <a href="?date_filter=today&status=<?= $filter_status ?>" style="text-decoration: none; color: <?= $date_filter=='today'?'var(--primary)':'var(--secondary)' ?>; font-weight: bold;">Today</a> | 
        <a href="?date_filter=2weeks&status=<?= $filter_status ?>" style="text-decoration: none; color: <?= $date_filter=='2weeks'?'var(--primary)':'var(--secondary)' ?>; font-weight: bold;">2 Weeks</a> | 
        <a href="?date_filter=monthly&status=<?= $filter_status ?>" style="text-decoration: none; color: <?= $date_filter=='monthly'?'var(--primary)':'var(--secondary)' ?>; font-weight: bold;">This Month</a> | 
        <a href="?date_filter=yearly&status=<?= $filter_status ?>" style="text-decoration: none; color: <?= $date_filter=='yearly'?'var(--primary)':'var(--secondary)' ?>; font-weight: bold;">This Year</a> | 
        <a href="?status=<?= $filter_status ?>" style="text-decoration: none; color: <?= $date_filter==''?'var(--primary)':'var(--secondary)' ?>; font-weight: bold;">All Time</a>
    </div>

    <div class="search-container">
        <form action="" method="GET" style="display: flex; gap: 10px; justify-content: center; margin-bottom: 25px;">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <input type="hidden" name="date_filter" value="<?= htmlspecialchars($date_filter) ?>">
            <input type="text" name="search" class="search-box" style="padding: 12px; width: 350px; border-radius: 10px; border: 1px solid #ddd;" placeholder="Search Job, Name or Phone..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-search" style="padding: 10px 25px; border-radius: 10px; border: none; background: var(--primary); color: white; cursor: pointer; font-weight: bold;">Search</button>
            <?php if($search != '' || $date_filter != ''): ?>
                <a href="?" style="padding: 10px; color: var(--danger); text-decoration: none; font-size: 14px; align-self: center;">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <table class="status-table">
            <thead>
                <tr>
                    <th>JOB NO</th>
                    <th>CUSTOMER DETAILS</th>
                    <th>DEVICE NAME</th>
                    <th>ISSUE DESCRIPTION</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $id = $row['job_device_id'];
                        $days_passed = 0; $delay_fee = 0;
                        $is_destroy_ready = false; $needs_sms_warning = false;

                        if($row['device_status'] == 'Completed' && $row['completed_date'] != null) {
                            $days_passed = floor((time() - strtotime($row['completed_date'])) / 86400);
                            if($days_passed > 90) { $delay_fee = ceil(($days_passed - 90) / 30) * 100; }
                            if($days_passed >= 365 && empty($row['destroy_notice_sent_date'])) $needs_sms_warning = true;
                            if($days_passed >= 372 && !empty($row['destroy_notice_sent_date'])) $is_destroy_ready = true;
                        }
                    ?>
                    <tr id="row-<?= $id ?>">
                        <td>
                            <span class="badge" style="background: #edf2f7; color: #2d3748;">#<?= $row['job_no'] ?></span><br>
                            <small style="font-size: 9px; color: #94a3b8;"><?= date('Y-m-d', strtotime($row['job_date'])) ?></small>
                        </td>
                        <td style="text-align: left;">
                            <b><?= htmlspecialchars($row['customer_name']) ?></b><br>
                            <small style="color: var(--text-muted)"><?= $row['phone_number'] ?></small>
                        </td>
                        <td><input type="text" id="dev-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['device_name']) ?>" readonly></td>
                        <td><input type="text" id="iss-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['issue_name']) ?>" readonly></td>
                        <td>
                            <select id="stat-<?= $id ?>" onchange="updateStatusOnly(<?= $id ?>)" style="width: 120px;">
                                <option value="Pending" <?= $row['device_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress" <?= $row['device_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= $row['device_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancel" <?= $row['device_status'] == 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                            </select>
                            <?php if($delay_fee > 0): ?>
                                <div class="rent-fee">💰 Rs. <?= $delay_fee ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-gap" style="display: flex; gap: 5px; justify-content: center;">
                                <?php if($needs_sms_warning): ?>
                                    <button onclick="sendDestroyWarning(<?= $id ?>)" class="btn-sms" style="background: var(--danger); color:white; border:none; padding:8px; border-radius:5px; cursor:pointer;">⚠️ WARNING</button>
                                <?php else: ?>
                                    <button onclick="manualSMS(<?= $id ?>)" class="btn-sms" style="background: var(--purple); color:white; border:none; padding:8px; border-radius:5px; cursor:pointer;">📱 SMS</button>
                                <?php endif; ?>

                                <?php if($row['device_status'] == 'Completed'): ?>
                                    <?php if($is_destroy_ready): ?>
                                        <a href="destroy_page.php?id=<?= $id ?>" class="btn-destroy" style="background: #000; color:white; padding:8px; border-radius:5px; text-decoration:none;">🗑️ Destroy</a>
                                    <?php else: ?>
                                        <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&fee=<?= $delay_fee ?>" class="btn-bill" style="background: var(--orange); color:white; padding:8px; border-radius:5px; text-decoration:none;">📄 Bill</a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" class="btn-edit" style="background: var(--blue); color:white; border:none; padding:8px; border-radius:5px; cursor:pointer;">✏️</button>
                                <button onclick="deleteItem(<?= $id ?>)" class="btn-delete" style="background: #f8d7da; color: #721c24; border:none; padding:8px; border-radius:5px; cursor:pointer;">🗑️</button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty-state" style="padding: 50px; text-align: center;">No jobs found for the selected period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Logic Functions (Same as before)
function updateStatusOnly(id) {
    const btn = document.getElementById('stat-' + id);
    btn.classList.add('btn-loading');
    sendUpdate(id, document.getElementById('dev-' + id).value, document.getElementById('iss-' + id).value, btn.value, true);
}

function toggleEdit(id) {
    let dev = document.getElementById('dev-' + id);
    let iss = document.getElementById('iss-' + id);
    let btn = document.getElementById('btn-edit-' + id);
    if (dev.readOnly) {
        dev.readOnly = false; iss.readOnly = false;
        dev.classList.add('editing'); iss.classList.add('editing');
        btn.innerHTML = "💾"; btn.style.background = "var(--success)";
    } else {
        sendUpdate(id, dev.value, iss.value, document.getElementById('stat-' + id).value, false);
    }
}

function sendUpdate(id, dev, iss, stat, isStatusChange) {
    let params = `id=${id}&device_name=${encodeURIComponent(dev)}&issue_name=${encodeURIComponent(iss)}&device_status=${encodeURIComponent(stat)}`;
    fetch('inline_update_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(res => res.text()).then(data => {
        if(data.trim() === "Success") {
            if(isStatusChange && stat === 'Completed') { location.reload(); } 
            else {
                let devInput = document.getElementById('dev-' + id);
                let issInput = document.getElementById('iss-' + id);
                let btn = document.getElementById('btn-edit-' + id);
                devInput.readOnly = true; issInput.readOnly = true;
                devInput.classList.remove('editing'); issInput.classList.remove('editing');
                btn.innerHTML = "✏️"; btn.style.background = "var(--blue)";
                document.getElementById('stat-' + id).classList.remove('btn-loading');
            }
        } else { alert("Error updating record."); }
    });
}

function manualSMS(id) {
    let statVal = document.getElementById('stat-' + id).value;
    if(confirm("Send SMS notification to customer?")) {
        fetch('send_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${statVal}`
        }).then(res => res.text()).then(data => alert("Result: " + data));
    }
}

function deleteItem(id) {
    if(confirm("Are you sure you want to delete this device record?")) {
        fetch('delete_device.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "device_id=" + id
        }).then(res => res.text()).then(data => {
            if(data.trim() === "Success") {
                document.getElementById('row-' + id).style.opacity = '0';
                setTimeout(() => document.getElementById('row-' + id).remove(), 300);
            }
        });
    }
}

function sendDestroyWarning(id) {
    if(confirm("Device is over 1 year old. Send final disposal warning?")) {
        fetch('send_destroy_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        }).then(res => res.text()).then(data => {
            alert(data);
            location.reload();
        });
    }
}
</script>
</body>
</html>