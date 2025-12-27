<?php
include 'db_config.php';
include 'navbar.php';

// Search and Filter logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$sql = "SELECT j.job_no, j.job_date, j.technician, c.customer_name, j.phone_number, jd.job_device_id, jd.device_name, jd.issue_name, jd.device_status 
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        LEFT JOIN job_device jd ON j.job_no = jd.job_no 
        WHERE 1=1";

if ($search != '') {
    $sql .= " AND (j.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR jd.issue_name LIKE '%$search%' OR c.customer_name LIKE '%$search%')";
}

if ($status_filter != '') {
    $sql .= " AND jd.device_status = '$status_filter'";
}

$sql .= " ORDER BY jd.job_device_id DESC";
$result = mysqli_query($conn, $sql);
?>

<style>
    body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
    
    /* Search Bar */
    .search-container { text-align: center; margin: 20px 0; }
    .search-box { padding: 10px 20px; width: 350px; border-radius: 25px; border: 1px solid #ddd; outline: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .btn-search { padding: 10px 25px; border-radius: 25px; border: none; background: #2e7d32; color: white; cursor: pointer; margin-left: -55px; font-weight: bold; }

    /* Filters */
    .filter-bar { text-align: center; margin-bottom: 25px; }
    .filter-bar a { text-decoration: none; padding: 10px 20px; margin: 0 5px; background: #fff; color: #2e7d32; border-radius: 20px; border: 1px solid #ddd; font-size: 14px; font-weight: 600; transition: 0.3s; }
    .filter-bar a.active { background: #2e7d32; color: #fff; }

    /* Table Styles */
    .status-table { width: 98%; margin: auto; border-collapse: collapse; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
    .status-table th { background: #2e7d32; color: white; padding: 15px; font-size: 13px; text-transform: uppercase; }
    .status-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
    
    /* Input Styling */
    .inline-input { width: 95%; border: 1px solid transparent; background: transparent; text-align: center; padding: 6px; border-radius: 4px; font-size: 14px; }
    .editing { border: 1px solid #3498db !important; background: #fff !important; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); }

    /* Button Styling */
    .btn-edit { background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; margin-right: 5px; }
    .btn-edit:hover { background: #c0392b; }
    
    .btn-save-active { background: #27ae60 !important; } /* Save අවස්ථාවේදී කොළ පාට */
    .btn-save-active:hover { background: #219150 !important; }

    .btn-delete { background: #7f8c8d; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .btn-delete:hover { background: #636e72; }

    select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; background: #f9f9f9; }
    select:disabled { color: #333; opacity: 1; border: 1px solid transparent; -webkit-appearance: none; }
</style>

<div class="search-container">
    <form action="job_list.php" method="GET">
        <input type="text" name="search" class="search-box" placeholder="Search Job, Phone, or Customer..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn-search">Search</button>
    </form>
</div>

<div class="filter-bar">
    <a href="job_list.php" class="<?= $status_filter == '' ? 'active' : '' ?>">All Jobs</a>
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
            <th>Device</th>
            <th>Issue</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): $id = $row['job_device_id']; ?>
            <tr id="row-<?php echo $id; ?>">
                <td><?php echo $row['job_date']; ?></td>
                <td><b><?php echo $row['job_no']; ?></b></td>
                <td><?php echo $row['customer_name']; ?></td>
                
                <td><input type="text" id="dev-<?php echo $id; ?>" class="inline-input" value="<?php echo $row['device_name']; ?>" readonly></td>
                <td><input type="text" id="iss-<?php echo $id; ?>" class="inline-input" value="<?php echo $row['issue_name']; ?>" readonly></td>
                
                <td>
                    <select id="stat-<?php echo $id; ?>" disabled onchange="updateRow(<?php echo $id; ?>, 'device_status', this.value)">
                        <option value="Pending" <?= $row['device_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $row['device_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $row['device_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </td>
                <td>
                    <button id="btn-edit-<?php echo $id; ?>" onclick="toggleEdit(<?php echo $id; ?>)" class="btn-edit">Edit</button>
                    <button onclick="deleteItem(<?php echo $id; ?>)" class="btn-delete">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function toggleEdit(id) {
    let devInput = document.getElementById('dev-' + id);
    let issInput = document.getElementById('iss-' + id);
    let statSelect = document.getElementById('stat-' + id);
    let editBtn = document.getElementById('btn-edit-' + id);

    if (devInput.readOnly) {
        // Edit mode එකට ඇතුල් වීම
        devInput.readOnly = false;
        issInput.readOnly = false;
        statSelect.disabled = false;
        
        devInput.classList.add('editing');
        issInput.classList.add('editing');
        
        editBtn.innerText = "Save";
        editBtn.classList.add('btn-save-active');
    } else {
        // Save කර නැවත Read-only කිරීම
        saveChanges(id);
    }
}

function saveChanges(id) {
    let devVal = document.getElementById('dev-' + id).value;
    let issVal = document.getElementById('iss-' + id).value;
    let statVal = document.getElementById('stat-' + id).value;

    // Database එකට දත්ත යැවීම
    updateField(id, 'device_name', devVal);
    updateField(id, 'issue_name', issVal);
    updateField(id, 'device_status', statVal);

    // පෙනුම සාමාන්‍ය තත්වයට පත් කිරීම
    let devInput = document.getElementById('dev-' + id);
    let issInput = document.getElementById('iss-' + id);
    let statSelect = document.getElementById('stat-' + id);
    let editBtn = document.getElementById('btn-edit-' + id);

    devInput.readOnly = true;
    issInput.readOnly = true;
    statSelect.disabled = true;
    
    devInput.classList.remove('editing');
    issInput.classList.remove('editing');
    
    editBtn.innerText = "Edit";
    editBtn.classList.remove('btn-save-active');
}

function updateField(id, field, value) {
    let params = "id=" + id + "&field=" + field + "&value=" + encodeURIComponent(value) + "&type=device";
    fetch('inline_update_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(res => res.text()).then(data => {
        if(data.trim() !== "Success") console.error("Error updating " + field);
    });
}

function deleteItem(id) {
    if(confirm("Are you sure you want to delete this data permanently?")) {
        fetch('delete_device.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "device_id=" + id
        }).then(res => res.text()).then(data => {
            if(data.trim() === "Success") {
                document.getElementById('row-' + id).remove();
            } else {
                alert("Error: " + data);
            }
        });
    }
}
</script>