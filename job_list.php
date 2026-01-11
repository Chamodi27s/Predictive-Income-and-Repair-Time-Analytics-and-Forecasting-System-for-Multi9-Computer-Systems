<?php
include 'db_config.php';
include 'navbar.php';

// --- 1. ස්වයංක්‍රීයව Status Update කිරීම ---
// පණිවිඩය යවා දින 7ක් ගියපුවා ස්වයංක්‍රීයව 'Destroyed' තත්ත්වයට පත් කිරීම
mysqli_query($conn, "UPDATE job_device SET device_status = 'Destroyed' 
                     WHERE destroy_notice_sent_date IS NOT NULL 
                     AND DATEDIFF(NOW(), destroy_notice_sent_date) >= 7 
                     AND device_status != 'Destroyed'");

// Search සහ Status Filter අගයන් ලබා ගැනීම
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// 2. SQL Query
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

if ($filter_status != '') {
    $sql .= " AND jd.device_status = '$filter_status'";
}

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
            --purple: #9b59b6;
            --orange: #e67e22;
            --blue: #3b82f6;
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
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
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

        /* Filter Tags */
        .filter-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .filter-tag {
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            color: white;
            border: 2px solid transparent;
        }

        /* Different colors for each filter tag */
        .filter-tag.tag-all {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
        }

        .filter-tag.tag-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(100, 116, 139, 0.4);
        }

        .filter-tag.tag-all.active-tag {
            border: 2px solid var(--text-dark);
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(100, 116, 139, 0.5);
        }

        .filter-tag.tag-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .filter-tag.tag-pending:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        .filter-tag.tag-pending.active-tag {
            border: 2px solid var(--text-dark);
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        }

        .filter-tag.tag-progress {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .filter-tag.tag-progress:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        .filter-tag.tag-progress.active-tag {
            border: 2px solid var(--text-dark);
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }

        .filter-tag.tag-completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .filter-tag.tag-completed:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .filter-tag.tag-completed.active-tag {
            border: 2px solid var(--text-dark);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        /* Search Container */
        .search-container {
            text-align: center;
            margin-bottom: 28px;
            display: flex;
            justify-content: center;
            gap: 12px;
            align-items: center;
        }

        .search-box {
            padding: 14px 24px;
            width: 400px;
            border-radius: 12px;
            border: 2px solid var(--border);
            outline: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .search-box:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        .btn-search {
            padding: 14px 32px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
        }

        .btn-search:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-dark) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 204, 113, 0.4);
        }

        .btn-clear {
            padding: 12px 24px;
            border-radius: 12px;
            border: 2px solid var(--danger);
            background: white;
            color: var(--danger);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-clear:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        /* Table Container */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: white;
            box-shadow: var(--shadow-lg);
        }

        /* Table Styling */
        .status-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1200px;
        }

        .status-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .status-table th {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 16px 18px;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .status-table th:first-child {
            border-top-left-radius: 12px;
        }

        .status-table th:last-child {
            border-top-right-radius: 12px;
        }

        .status-table tbody tr {
            transition: all 0.3s ease;
            background: white;
        }

        .status-table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .status-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #f0f2f5;
            text-align: center;
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
        }

        .status-table td b {
            color: var(--text-dark);
            font-weight: 800;
        }

        /* Inline Inputs */
        .inline-input {
            width: 90%;
            border: 2px solid transparent;
            background: transparent;
            text-align: center;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .editing {
            border: 2px solid var(--primary) !important;
            background: white !important;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        /* Status Select */
        select {
            padding: 10px 14px;
            border-radius: 10px;
            border: 2px solid var(--border);
            outline: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.3s ease;
            background: white;
        }

        select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        /* Action Buttons */
        .action-gap {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-bill, .btn-sms, .btn-edit, .btn-delete, .btn-save, .btn-destroy {
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-bill {
            background: linear-gradient(135deg, var(--orange) 0%, #d35400 100%);
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
        }

        .btn-bill:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(230, 126, 34, 0.4);
        }

        .btn-sms {
            background: linear-gradient(135deg, var(--purple) 0%, #8e44ad 100%);
            box-shadow: 0 4px 12px rgba(155, 89, 182, 0.3);
        }

        .btn-sms:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(155, 89, 182, 0.4);
        }

        .btn-edit {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        .btn-save {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .btn-destroy {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 0 4px 12px rgba(30, 41, 59, 0.3);
        }

        .btn-destroy:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 41, 59, 0.4);
        }

        /* Rent Fee Warning */
        .rent-fee {
            color: var(--danger);
            font-weight: 800;
            font-size: 12px;
            margin-top: 6px;
            display: inline-block;
        }

        /* Empty State */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
            font-weight: 600;
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

            .search-container {
                flex-direction: column;
            }

            .search-box {
                width: 100%;
            }

            .filter-container {
                flex-direction: column;
            }

            .filter-tag {
                width: 100%;
                text-align: center;
            }

            .status-table {
                font-size: 12px;
            }

            .status-table th,
            .status-table td {
                padding: 12px 10px;
            }
        }

        /* Animation */
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
    </style>
</head>
<body>

<div class="page-container">
    <div class="page-header">
        <h1>⚙️ Job Management</h1>
        <p>Track and manage all active jobs</p>
    </div>
</div>

<div class="filter-container">
    <a href="?" class="filter-tag tag-all <?= $filter_status == '' ? 'active-tag' : '' ?>">📋 All Active Jobs</a>
    <a href="?status=Pending" class="filter-tag tag-pending <?= $filter_status == 'Pending' ? 'active-tag' : '' ?>">⏳ Pending</a>
    <a href="?status=In Progress" class="filter-tag tag-progress <?= $filter_status == 'In Progress' ? 'active-tag' : '' ?>">🔧 In Progress</a>
    <a href="?status=Completed" class="filter-tag tag-completed <?= $filter_status == 'Completed' ? 'active-tag' : '' ?>">✅ Completed</a>
</div>

<div class="search-container">
    <form action="" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; justify-content: center;">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
        <input type="text" name="search" class="search-box" placeholder="🔍 Search by Job No, Name or Phone..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Search</button>
        <?php if($search != '' || $filter_status != ''): ?>
            <a href="?" class="btn-clear">✕ Clear All</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-container">
    <table class="status-table">
        <thead>
            <tr>
                <th>Job No</th>
                <th>Customer</th>
                <th>Device</th>
                <th>Issue</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $id = $row['job_device_id'];
                    
                    $days_passed = 0;
                    $delay_fee = 0;
                    $is_destroy_ready = false;
                    $needs_sms_warning = false;

                    if($row['device_status'] == 'Completed' && $row['completed_date'] != null) {
                        $days_passed = floor((time() - strtotime($row['completed_date'])) / 86400);
                        
                        if($days_passed > 90) {
                            $extra_days = $days_passed - 90;
                            $months_passed = ceil($extra_days / 30);
                            $delay_fee = $months_passed * 100; 
                        }

                        if($days_passed >= 365 && empty($row['destroy_notice_sent_date'])) {
                            $needs_sms_warning = true;
                        }
                        
                        if($days_passed >= 372 && !empty($row['destroy_notice_sent_date'])) {
                            $is_destroy_ready = true;
                        }
                    }
                ?>
                <tr id="row-<?= $id ?>">
                    <td><b>#<?= $row['job_no'] ?></b></td>
                    <td><b><?= htmlspecialchars($row['customer_name']) ?></b></td>
                    <td><input type="text" id="dev-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['device_name']) ?>" readonly></td>
                    <td><input type="text" id="iss-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['issue_name']) ?>" readonly></td>
                    <td>
                        <select id="stat-<?= $id ?>" onchange="updateStatusOnly(<?= $id ?>)">
                            <option value="Pending" <?= $row['device_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= $row['device_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= $row['device_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancel" <?= $row['device_status'] == 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                        </select>
                        <?php if($delay_fee > 0): ?>
                            <br><small class="rent-fee">💰 Rent: RS <?= $delay_fee ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-gap">
                            <?php if($needs_sms_warning): ?>
                                <button onclick="sendDestroyWarning(<?= $id ?>)" class="btn-sms" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">📨 SEND DISPOSAL SMS</button>
                            <?php else: ?>
                                <button onclick="manualSMS(<?= $id ?>)" class="btn-sms">📱 SMS</button>
                            <?php endif; ?>
                            
                            <span id="bill-container-<?= $id ?>">
                                <?php if($row['device_status'] == 'Completed'): ?>
                                    <?php if($is_destroy_ready): ?>
                                        <a href="destroy_page.php?id=<?= $id ?>" class="btn-destroy">🗑️ Destroy Item</a>
                                    <?php else: ?>
                                        <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&fee=<?= $delay_fee ?>" class="btn-bill">📄 Make Bill</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>

                            <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" class="btn-edit">✏️ Edit</button>
                            <button onclick="deleteItem(<?= $id ?>)" class="btn-delete">🗑️ Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="empty-state">📋 No active orders matching your criteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function sendDestroyWarning(id) {
    if(confirm("මෙම උපකරණය වසරකට වඩා පැරණියි. විනාශ කිරීමට පෙර අවසන් දැනුම්දීම යවන්නද?")) {
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

function updateStatusOnly(id) {
    let statVal = document.getElementById('stat-' + id).value;
    let devVal = document.getElementById('dev-' + id).value;
    let issVal = document.getElementById('iss-' + id).value;
    sendUpdate(id, devVal, issVal, statVal);
    if(statVal === 'Completed') { location.reload(); }
}

function toggleEdit(id) {
    let dev = document.getElementById('dev-' + id);
    let iss = document.getElementById('iss-' + id);
    let btn = document.getElementById('btn-edit-' + id);
    if (dev.readOnly) {
        dev.readOnly = false; iss.readOnly = false;
        dev.classList.add('editing'); iss.classList.add('editing');
        btn.innerHTML = "💾 Save"; btn.className = "btn-save";
    } else { 
        sendUpdate(id, dev.value, iss.value, document.getElementById('stat-' + id).value); 
    }
}

function sendUpdate(id, dev, iss, stat) {
    let params = `id=${id}&device_name=${encodeURIComponent(dev)}&issue_name=${encodeURIComponent(iss)}&device_status=${encodeURIComponent(stat)}`;
    fetch('inline_update_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(res => res.text()).then(data => {
        if(data.trim() === "Success") {
            let devInput = document.getElementById('dev-' + id);
            let issInput = document.getElementById('iss-' + id);
            let btn = document.getElementById('btn-edit-' + id);
            devInput.readOnly = true; issInput.readOnly = true;
            devInput.classList.remove('editing'); issInput.classList.remove('editing');
            btn.innerHTML = "✏️ Edit"; btn.className = "btn-edit";
        } else { alert("Error: " + data); }
    });
}

function manualSMS(id) {
    let statVal = document.getElementById('stat-' + id).value;
    if(confirm("Send SMS notification?")) {
        fetch('send_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${statVal}`
        }).then(res => res.text()).then(data => alert("Notification: " + data));
    }
}

function deleteItem(id) {
    if(confirm("Are you sure?")) {
        fetch('delete_device.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "device_id=" + id
        }).then(res => res.text()).then(data => {
            if(data.trim() === "Success") { document.getElementById('row-' + id).remove(); }
        });
    }
}
</script>
</body>
</html>