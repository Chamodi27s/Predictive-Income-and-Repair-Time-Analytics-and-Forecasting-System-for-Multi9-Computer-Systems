<?php
include 'db_config.php';
include 'navbar.php';

// Search සහ Status Filter අගයන් ලබා ගැනීම
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// මූලික SQL Query එක
$sql = "SELECT j.job_no, j.job_date, t.name as technician_name, c.customer_name, j.phone_number, 
                jd.job_device_id, jd.device_name, jd.issue_name, jd.device_status 
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        WHERE j.job_status = 'Approved'"; 

// Status අනුව Filter කිරීම
if ($filter_status != '') {
    $sql .= " AND jd.device_status = '$filter_status'";
}

// Search අනුව Filter කිරීම
if ($search != '') {
    $sql .= " AND (j.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR jd.issue_name LIKE '%$search%' OR c.customer_name LIKE '%$search%' OR t.name LIKE '%$search%')";
}

$sql .= " ORDER BY jd.job_device_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing & SMS Management</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
        
        /* Filter Tags Styling */
        .filter-container { display: flex; justify-content: center; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .filter-tag { padding: 8px 18px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 13px; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn-save { background: #27ae60; }
        .tag-all { background:#27ae60 ; color: white; }
        .tag-pending { background:#27ae60 ; color: #333; }
        .tag-progress { background: #27ae60; color: white; }
        .tag-completed { background: #28a745; color: white; }
        .tag-cancel { background: #27ae60; color: white; }
        .active-tag { border: 3px solid #333; transform: scale(1.05); }

        .search-container { text-align: center; margin: 20px; display: flex; justify-content: center; gap: 10px; }
        .search-box { padding: 12px 20px; width: 300px; border-radius: 25px; border: 1px solid #ddd; outline: none; }
        .btn-search { padding: 10px 25px; border-radius: 25px; border: none; background: #2e7d32; color: white; cursor: pointer; font-weight: bold; }
        
        .status-table { width: 98%; margin: 20px auto; border-collapse: collapse; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 10px; }
        .status-table th { background: #2e7d32; color: white; padding: 15px; font-size: 14px; }
        .status-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; vertical-align: middle; }

        /* Actions Column Styling - මේ කොටසින් Buttons එක පේළියට ගනී */
        .action-gap { 
            display: flex; 
            gap: 6px; 
            justify-content: center; 
            align-items: center; 
            white-space: nowrap; 
        }

        .btn-bill, .btn-sms, .btn-edit, .btn-delete, .btn-save { 
            height: 34px;
            min-width: 70px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 11px;
            border: none;
            cursor: pointer;
            color: white;
            padding: 0 10px;
            transition: 0.2s;
        }

        .btn-bill { background: #f39c12; }
        .btn-sms { background: #9b59b6; }
        .btn-edit { background: #1976d2; }
        .btn-save { background: #27ae60; }
        .btn-delete { background: #e53935; }

        .inline-input { width: 95%; border: 1px solid transparent; background: transparent; text-align: center; padding: 6px; font-size: 14px; }
        .editing { border: 1px solid #3498db !important; background: #fff !important; border-radius: 4px; }
        .status-select { padding: 6px; border-radius: 5px; border: 1px solid #ccc; font-size: 12px; background: #f9f9f9; }
    </style>
</head>
<body>

<div class="filter-container">
    <a href="?" class="filter-tag tag-all <?= $filter_status == '' ? 'active-tag' : '' ?>">All Jobs</a>
    <a href="?status=Pending" class="filter-tag tag-pending <?= $filter_status == 'Pending' ? 'active-tag' : '' ?>">🕒 Pending</a>
    <a href="?status=In Progress" class="filter-tag tag-progress <?= $filter_status == 'In Progress' ? 'active-tag' : '' ?>">⚙️ In Progress</a>
    <a href="?status=Completed" class="filter-tag tag-completed <?= $filter_status == 'Completed' ? 'active-tag' : '' ?>">✅ Completed</a>
    <a href="?status=Cancel" class="filter-tag tag-cancel <?= $filter_status == 'Cancel' ? 'active-tag' : '' ?>">❌ Cancel</a>
</div>

<div class="search-container">
    <form action="" method="GET" style="display: flex; gap: 10px;">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
        <input type="text" name="search" class="search-box" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Search</button>
    </form>
</div>

<table class="status-table">
    <thead>
        <tr>
            <th style="width: 80px;">Job No</th>
            <th>Customer</th>
            <th>Device</th>
            <th>Issue</th>
            <th style="width: 120px;">Status</th>
            <th style="width: 320px;">Action</th>
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
                    <select id="stat-<?= $id ?>" class="status-select" onchange="updateStatusOnly(<?= $id ?>)">
                        <option value="Pending" <?= $row['device_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $row['device_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $row['device_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancel" <?= $row['device_status'] == 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                    </select>
                </td>
                <td>
                    <div class="action-gap">
                        <button onclick="manualSMS(<?= $id ?>)" class="btn-sms">Send SMS</button>
                        
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
            <tr><td colspan="6" style="padding: 40px; color: #999;">No records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function updateStatusOnly(id) {
    let statVal = document.getElementById('stat-' + id).value;
    let devVal = document.getElementById('dev-' + id).value;
    let issVal = document.getElementById('iss-' + id).value;
    
    sendUpdate(id, devVal, issVal, statVal);

    // Bill Button update
    let billContainer = document.getElementById('bill-container-' + id);
    if(statVal === 'Completed') {
        let jobNo = document.querySelector(`#row-${id} td b`).innerText;
        billContainer.innerHTML = `<a href="generate_bill.php?job_no=${jobNo}" class="btn-bill">Make Bill</a>`;
    } else {
        billContainer.innerHTML = '';
    }
}

function manualSMS(id) {
    let statVal = document.getElementById('stat-' + id).value;
    if(confirm("Send SMS notification?")) {
        fetch('send_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${statVal}`
        }).then(res => res.text()).then(data => alert("Response: " + data));
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
        }
    });
}

function deleteItem(id) {
    if(confirm("Delete this record?")) {
        fetch('delete_device.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "device_id=" + id
        }).then(res => res.text()).then(data => {
            if(data.trim() === "Success") document.getElementById('row-' + id).remove();
        });
    }
}
</script>
</body>
</html>