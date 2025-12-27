<?php
include 'db_config.php';
include 'navbar.php';

// Search aur Filter values lena
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Query base
$sql = "SELECT j.job_no, j.job_date, j.technician, c.customer_name, j.phone_number, jd.job_device_id, jd.device_name, jd.issue_name, jd.device_status 
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        LEFT JOIN job_device jd ON j.job_no = jd.job_no 
        WHERE 1=1";

// Agar Search box mein kuch likha hai
if ($search != '') {
    $sql .= " AND (j.job_no LIKE '%$search%' 
               OR j.phone_number LIKE '%$search%' 
               OR jd.issue_name LIKE '%$search%'
               OR c.customer_name LIKE '%$search%')";
}

// Agar Status filter click kiya hai
if ($status_filter != '') {
    $sql .= " AND jd.device_status = '$status_filter'";
}

$sql .= " ORDER BY jd.job_device_id DESC";
$result = mysqli_query($conn, $sql);
?>

<style>
    body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }
    
    /* Search Bar Styling */
    .search-container { text-align: center; margin: 20px 0; }
    .search-box { 
        padding: 10px 15px; width: 350px; border-radius: 25px; 
        border: 1px solid #ddd; outline: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn-search { 
        padding: 10px 20px; border-radius: 25px; border: none; 
        background: #3498db; color: white; cursor: pointer; margin-left: -50px;
    }

    /* Filter Links */
    .filter-bar { text-align: center; margin-bottom: 20px; }
    .filter-bar a { 
        text-decoration: none; padding: 8px 15px; margin: 0 5px; 
        background: #fff; color: #333; border-radius: 20px; 
        border: 1px solid #ddd; font-size: 14px; transition: 0.3s;
    }
    .filter-bar a.active { background: #2c3e50; color: #fff; border-color: #2c3e50; }

    .status-table { width: 98%; margin: auto; border-collapse: collapse; background: #fff; }
    .status-table th { background: #2c3e50; color: white; padding: 12px; }
    .status-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: center; }
    
    .inline-input { width: 95%; border: 1px solid transparent; background: transparent; text-align: center; padding: 5px; }
    .inline-input:focus { border: 1px solid #3498db; background: #fff; outline: none; }
</style>

<div class="search-container">
    <form action="job_list.php" method="GET">
        <input type="text" name="search" class="search-box" placeholder="Search Job No, Phone, or Issue..." value="<?php echo $search; ?>">
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
            <th>Phone</th>
            <th>Device</th>
            <th>Issue</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr id="row-<?php echo $row['job_device_id']; ?>">
                <td><?php echo $row['job_date']; ?></td>
                <td><b><?php echo $row['job_no']; ?></b></td>
                <td><?php echo $row['customer_name']; ?></td>
                <td><?php echo $row['phone_number']; ?></td>
                <td><input type="text" class="inline-input" value="<?php echo $row['device_name']; ?>" onchange="updateRow(<?php echo $row['job_device_id']; ?>, 'device_name', this.value, 'device')"></td>
                <td><input type="text" class="inline-input" value="<?php echo $row['issue_name']; ?>" onchange="updateRow(<?php echo $row['job_device_id']; ?>, 'issue_name', this.value, 'device')"></td>
                <td>
                    <select onchange="updateRow(<?php echo $row['job_device_id']; ?>, 'device_status', this.value, 'device')">
                        <option value="Pending" <?= $row['device_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $row['device_status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $row['device_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </td>
                <td>
                    <button onclick="deleteItem(<?php echo $row['job_device_id']; ?>)" style="color:red; cursor:pointer; border:none; background:none;">🗑️</button>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No results found for "<?php echo $search; ?>"</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function updateRow(id, field, value, type) {
    let params = "id=" + id + "&field=" + field + "&value=" + encodeURIComponent(value) + "&type=" + type;
    fetch('inline_update_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(res => res.text()).then(data => {
        if(data.trim() !== "Success") alert("Error: " + data);
    });
}

function deleteItem(id) {
    if(confirm("Are you sure?")) {
        fetch('delete_device.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "device_id=" + id
        }).then(res => res.text()).then(data => {
            if(data.trim() === "Success") document.getElementById('row-'+id).remove();
        });
    }
}
</script>