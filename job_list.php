<?php
include 'db_config.php';
include 'navbar.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Query එකෙන් jd.warranty_status ඉවත් කළා
$sql = "SELECT j.job_no, j.job_date, t.name as technician_name, c.customer_name, j.phone_number, 
                jd.job_device_id, jd.device_name, jd.issue_name, jd.device_status 
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        WHERE 1=1";

if ($search != '') {
    $sql .= " AND (j.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR jd.issue_name LIKE '%$search%' OR c.customer_name LIKE '%$search%' OR t.name LIKE '%$search%')";
}

if ($status_filter != '') {
    $sql .= " AND jd.device_status = '$status_filter'";
}

$sql .= " ORDER BY jd.job_device_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Jobs</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
        .search-container { text-align: center; margin: 20px 0; }
        .search-box { padding: 10px 20px; width: 350px; border-radius: 25px; border: 1px solid #ddd; outline: none; }
        .btn-search { padding: 10px 25px; border-radius: 25px; border: none; background: #2e7d32; color: white; cursor: pointer; margin-left: -55px; font-weight: bold; }
        .filter-bar { text-align: center; margin-bottom: 25px; }
        .filter-bar a { text-decoration: none; padding: 10px 20px; margin: 0 5px; background: #fff; color: #2e7d32; border-radius: 20px; border: 1px solid #ddd; font-size: 14px; font-weight: 600; }
        .filter-bar a.active { background: #2e7d32; color: #fff; }
        .status-table { width: 98%; margin: auto; border-collapse: collapse; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
        .status-table th { background: #2e7d32; color: white; padding: 15px; font-size: 13px; text-transform: uppercase; }
        .status-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        .inline-input { width: 90%; border: 1px solid transparent; background: transparent; text-align: center; padding: 6px; border-radius: 4px; font-size: 14px; }
        .editing { border: 1px solid #3498db !important; background: #fff !important; }
        .btn-edit { background: #1976d2; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .btn-delete { background: #7f8c8d; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .btn-save-active { background: #27ae60 !important; }
        select:disabled { color: #333; opacity: 1; border: none; -webkit-appearance: none; }
    </style>
</head>
<body>

<div class="search-container">
    <form action="job_list.php" method="GET">
        <input type="text" name="search" class="search-box" placeholder="Search Job, Customer, or Technician..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Search</button>
    </form>
</div>

<div class="filter-bar">
    <a href="job_list.php" class="<?= $status_filter == '' ? 'active' : '' ?>">All</a>
    <a href="job_list.php?status=Pending" class="<?= $status_filter == 'Pending' ? 'active' : '' ?>">Pending</a>
    <a href="job_list.php?status=In Progress" class="<?= $status_filter == 'In Progress' ? 'active' : '' ?>">In Progress</a>
    <a href="job_list.php?status=Completed" class="<?= $status_filter == 'Completed' ? 'active' : '' ?>">Completed</a>
</div>

<table class="status-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Job No</th>
            <th>Customer</th>
            <th>Technician</th>
            <th>Device</th>
            <th>Issue</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): $id = $row['job_device_id']; ?>
        <tr id="row-<?= $id ?>">
            <td><?= $row['job_date'] ?></td>
            <td><b><?= $row['job_no'] ?></b></td>
            <td><?= $row['customer_name'] ?></td>
            <td style="color:#1976d2; font-weight:600;"><?= $row['technician_name'] ?: 'N/A' ?></td>
            <td><input type="text" id="dev-<?= $id ?>" class="inline-input" value="<?= $row['device_name'] ?>" readonly></td>
            <td><input type="text" id="iss-<?= $id ?>" class="inline-input" value="<?= $row['issue_name'] ?>" readonly></td>
            <td>
                <select id="stat-<?= $id ?>" disabled>
                    <option value="Pending" <?= $row['device_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="In Progress" <?= $row['device_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Completed" <?= $row['device_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </td>
            <td>
                <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" class="btn-edit">Edit</button>
                <button onclick="deleteItem(<?= $id ?>)" class="btn-delete">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
// JS functions එලෙසම පවතී (saveChanges වලදී warranty status එක යවන්නේ නැති නිසා ගැටලුවක් නැත)
function toggleEdit(id) {
    let dev = document.getElementById('dev-' + id);
    let iss = document.getElementById('iss-' + id);
    let stat = document.getElementById('stat-' + id);
    let btn = document.getElementById('btn-edit-' + id);

    if (dev.readOnly) {
        dev.readOnly = false; iss.readOnly = false; stat.disabled = false;
        dev.classList.add('editing'); iss.classList.add('editing');
        btn.innerText = "Save"; btn.classList.add('btn-save-active');
    } else {
        saveChanges(id);
    }
}

function saveChanges(id) {
    let devVal = document.getElementById('dev-' + id).value;
    let issVal = document.getElementById('iss-' + id).value;
    let statVal = document.getElementById('stat-' + id).value;

    // Params වලින් warranty එක ඉවත් කළා
    let params = `id=${id}&device_name=${encodeURIComponent(devVal)}&issue_name=${encodeURIComponent(issVal)}&device_status=${encodeURIComponent(statVal)}`;
    
    fetch('inline_update_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(res => res.text()).then(data => {
        if(data.trim() === "Success") {
            alert("Updated!");
            location.reload();
        } else { alert("Error: " + data); }
    });
}

function deleteItem(id) {
    if(confirm("Delete this device?")) {
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