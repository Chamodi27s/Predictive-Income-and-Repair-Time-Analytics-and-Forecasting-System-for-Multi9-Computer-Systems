<?php
include 'db_config.php';
include 'navbar.php';

// Search සහ Status Filter අගයන් ලබා ගැනීම
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// මූලික SQL Query එක - 'billed' නොවන දත්ත පමණක් ලබා ගැනීමට
$sql = "SELECT j.job_no, j.job_date, t.name as technician_name, c.customer_name, j.phone_number, 
               jd.job_device_id, jd.device_name, jd.issue_name, jd.device_status 
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        WHERE j.job_status = 'Approved' 
        AND jd.device_status != 'billed'"; 

// Status අනුව Filter කිරීම
if ($filter_status != '') {
    $sql .= " AND jd.device_status = '$filter_status'";
}

// Search අනුව Filter කිරීම
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
    <title>Job Management - Multi9</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding-top: 120px;   /* 🔥 navbar height */
    padding-left: 40px;
    padding-right: 40px; }
        
        /* Filter Section */
        .filter-container { display: flex; justify-content: center; gap: 10px; margin-top: 25px; flex-wrap: wrap; }
        .filter-tag { padding: 8px 20px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 13px; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); color: white; }
        .tag-all { background: #2e7d32; }
        .tag-pending { background: #f39c12; }
        .tag-progress { background: #3498db; }
        .tag-completed { background: #28a745; }
        .active-tag { border: 3px solid #333; transform: scale(1.05); }

        /* Search Bar */
        .search-container { text-align: center; margin: 25px; display: flex; justify-content: center; gap: 10px; align-items: center; }
        .search-box { padding: 12px 25px; width: 350px; border-radius: 30px; border: 1px solid #ddd; outline: none; font-size: 14px; }
        .btn-search { padding: 11px 25px; border-radius: 30px; border: none; background: #2e7d32; color: white; cursor: pointer; font-weight: bold; }
        
        .btn-clear { 
            padding: 10px 20px; 
            border-radius: 30px; 
            border: 1px solid #e53935; 
            background: white; 
            color: #e53935; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 13px;
            transition: 0.3s;
        }
        .btn-clear:hover { background: #e53935; color: white; }

        /* Table Styling */
        .status-table { width: 96%; margin: 20px auto; border-collapse: collapse; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-radius: 12px; overflow: hidden; }
        .status-table th { background: #2e7d32; color: white; padding: 15px; font-size: 14px; text-transform: uppercase; }
        .status-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; font-size: 14px; }

        .action-gap { display: flex; gap: 8px; justify-content: center; align-items: center; }
        
        .btn-bill, .btn-sms, .btn-edit, .btn-delete, .btn-save { 
            padding: 7px 14px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 11px; color: white; border: none; cursor: pointer; transition: 0.2s;
        }
        .btn-bill { background: #e67e22; }
        .btn-sms { background: #9b59b6; }
        .btn-edit { background: #1976d2; }
        .btn-delete { background: #e53935; }
        .btn-save { background: #2ecc71; }

        .inline-input { width: 90%; border: 1px solid transparent; background: transparent; text-align: center; padding: 6px; font-size: 14px; }
        .editing { border: 1px solid #3498db !important; background: #fff !important; border-radius: 4px; }
    </style>
</head>
<body>

<div class="filter-container">
    <a href="?" class="filter-tag tag-all <?= $filter_status == '' ? 'active-tag' : '' ?>">All Active Jobs</a>
    <a href="?status=Pending" class="filter-tag tag-pending <?= $filter_status == 'Pending' ? 'active-tag' : '' ?>">🕒 Pending</a>
    <a href="?status=In Progress" class="filter-tag tag-progress <?= $filter_status == 'In Progress' ? 'active-tag' : '' ?>">⚙️ In Progress</a>
    <a href="?status=Completed" class="filter-tag tag-completed <?= $filter_status == 'Completed' ? 'active-tag' : '' ?>">✅ Completed</a>
</div>

<div class="search-container">
    <form action="" method="GET" style="display: flex; gap: 10px; align-items: center;">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
        <input type="text" name="search" class="search-box" placeholder="Search by Job No, Name or Phone..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Search</button>
        
        <?php if($search != '' || $filter_status != ''): ?>
            <a href="?" class="btn-clear">✕ Clear All</a>
        <?php endif; ?>
    </form>
</div>

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
            <?php while($row = mysqli_fetch_assoc($result)): $id = $row['job_device_id']; ?>
            <tr id="row-<?= $id ?>">
                <td><b><?= $row['job_no'] ?></b></td>
                <td><?= $row['customer_name'] ?></td>
                <td><input type="text" id="dev-<?= $id ?>" class="inline-input" value="<?= $row['device_name'] ?>" readonly></td>
                <td><input type="text" id="iss-<?= $id ?>" class="inline-input" value="<?= $row['issue_name'] ?>" readonly></td>
                <td>
                    <select id="stat-<?= $id ?>" onchange="updateStatusOnly(<?= $id ?>)" style="padding:6px; border-radius:5px; border:1px solid #ccc;">
                        <option value="Pending" <?= $row['device_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $row['device_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $row['device_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancel" <?= $row['device_status'] == 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                    </select>
                </td>
                <td>
                    <div class="action-gap">
                        <button onclick="manualSMS(<?= $id ?>)" class="btn-sms">SMS</button>
                        
                        <span id="bill-container-<?= $id ?>">
                            <?php if($row['device_status'] == 'Completed'): ?>
                                <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>" class="btn-bill">Make Bill</a>
                            <?php endif; ?>
                        </span>

                        <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" class="btn-edit">Edit</button>
                        <button onclick="deleteItem(<?= $id ?>)" class="btn-delete">Delete</button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="padding: 50px; color: #999;">No active orders matching your criteria.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function updateStatusOnly(id) {
    let statVal = document.getElementById('stat-' + id).value;
    let devVal = document.getElementById('dev-' + id).value;
    let issVal = document.getElementById('iss-' + id).value;
    
    sendUpdate(id, devVal, issVal, statVal);

    let billContainer = document.getElementById('bill-container-' + id);
    if(statVal === 'Completed') {
        let jobNo = document.querySelector(`#row-${id} td b`).innerText;
        billContainer.innerHTML = `<a href="generate_bill.php?job_no=${jobNo}" class="btn-bill">Make Bill</a>`;
    } else {
        billContainer.innerHTML = '';
    }
}

function toggleEdit(id) {
    let dev = document.getElementById('dev-' + id);
    let iss = document.getElementById('iss-' + id);
    let btn = document.getElementById('btn-edit-' + id);

    if (dev.readOnly) {
        dev.readOnly = false; iss.readOnly = false;
        dev.classList.add('editing'); iss.classList.add('editing');
        btn.innerText = "Save"; btn.className = "btn-save";
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
            btn.innerText = "Edit"; btn.className = "btn-edit";
        } else {
            alert("Error updating: " + data);
        }
    });
}

function manualSMS(id) {
    let statVal = document.getElementById('stat-' + id).value;
    if(confirm("Send SMS notification to customer?")) {
        fetch('send_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${statVal}`
        }).then(res => res.text()).then(data => alert("Notification: " + data));
    }
}

function deleteItem(id) {
    if(confirm("Are you sure you want to delete this record?")) {
        fetch('delete_device.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "device_id=" + id
        }).then(res => res.text()).then(data => {
            if(data.trim() === "Success") {
                document.getElementById('row-' + id).style.background = "#ffebee";
                setTimeout(() => document.getElementById('row-' + id).remove(), 500);
            }
        });
    }
}
</script>
</body>
</html>