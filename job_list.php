<?php
include 'db_config.php';
include 'navbar.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// SQL Query: 'Approved' jobs පමණක් පෙන්වීමට
$sql = "SELECT j.job_no, j.job_date, t.name as technician_name, c.customer_name, j.phone_number, 
               jd.job_device_id, jd.device_name, jd.issue_name, jd.device_status 
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        WHERE j.job_status = 'Approved'"; 

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
        .search-container { text-align: center; margin: 20px; display: flex; justify-content: center; gap: 10px; }
        .search-box { padding: 12px 20px; width: 300px; border-radius: 25px; border: 1px solid #ddd; outline: none; }
        .btn-search { padding: 10px 25px; border-radius: 25px; border: none; background: #2e7d32; color: white; cursor: pointer; font-weight: bold; }
        .btn-clear { padding: 10px 25px; border-radius: 25px; border: 1px solid #ccc; background: #fff; color: #333; text-decoration: none; font-weight: bold; }
        
        .status-table { width: 98%; margin: auto; border-collapse: collapse; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
        .status-table th { background: #2e7d32; color: white; padding: 15px; }
        .status-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }

        .btn-bill { background: #f39c12; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 12px; display: inline-block; }
        .btn-sms { background: #9b59b6; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 12px; }
        .btn-edit { background: #1976d2; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-save { background: #27ae60; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-delete { background: #e53935; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        
        .inline-input { width: 90%; border: 1px solid transparent; background: transparent; text-align: center; padding: 6px; }
        .editing { border: 1px solid #3498db !important; background: #fff !important; }
        .status-select { padding: 6px; border-radius: 5px; border: 1px solid #ccc; }
        .action-gap { display: flex; gap: 5px; justify-content: center; flex-wrap: wrap; }
    </style>
</head>
<body>

<div class="search-container">
    <form action="" method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" class="search-box" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Search</button>
        <?php if($search != ''): ?> <a href="?" class="btn-clear">✕ Clear</a> <?php endif; ?>
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
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
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
                </select>
            </td>
            <td>
                <div class="action-gap">
                    <button onclick="manualSMS(<?= $id ?>)" class="btn-sms">Send SMS</button>

                    <span id="bill-container-<?= $id ?>">
    <?php if($row['device_status'] == 'Completed'): ?>
        <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>" class="btn-bill">Make a Bill</a>
    <?php endif; ?>
</span>

                    <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" class="btn-edit">Edit</button>
                    <button onclick="deleteItem(<?= $id ?>)" class="btn-delete">Delete</button>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
// Status වෙනස් කළ විට ස්වයංක්‍රීයව සිදුවන දේ
function updateStatusOnly(id) {
    let statVal = document.getElementById('stat-' + id).value;
    let devVal = document.getElementById('dev-' + id).value;
    let issVal = document.getElementById('iss-' + id).value;
    
    sendUpdate(id, devVal, issVal, statVal);

    // Dynamic Bill Button Show/Hide
    function updateStatusOnly(id) {
    let statVal = document.getElementById('stat-' + id).value;
    let devVal = document.getElementById('dev-' + id).value;
    let issVal = document.getElementById('iss-' + id).value;
    
    sendUpdate(id, devVal, issVal, statVal);

    // Dynamic Bill Button update
    let billContainer = document.getElementById('bill-container-' + id);
    if(statVal === 'Completed') {
        // මෙතන job_no එක ගන්නේ table row එකේ bold කරලා තියෙන තැනින්
        let jobNo = document.querySelector(`#row-${id} td b`).innerText;
        billContainer.innerHTML = `<a href="generate_bill.php?job_no=${jobNo}" class="btn-bill">Make a Bill</a>`;
    } else {
        billContainer.innerHTML = '';
    }
}
}

// Manual SMS යැවීමේ Function එක
function manualSMS(id) {
    let statVal = document.getElementById('stat-' + id).value;
    if(confirm("Send status update SMS to customer?")) {
        fetch('send_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${statVal}`
        }).then(res => res.text()).then(data => {
            alert("SMS Response: " + data);
        });
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
    if(confirm("Delete this?")) {
        fetch('delete_device.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "device_id=" + id
        }).then(res => res.text()).then(data => {
            if(data.trim() === "Success") document.getElementById('row-' + id).style.display = 'none';
        });
    }
}
</script>
</body>
</html>