<?php 
include 'db_config.php';
include 'navbar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Center Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; padding: 20px; }
        .container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        h2 { margin-bottom: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; min-width: 1100px; margin-top: 20px; }
        th { background: #2e7d32; text-align: left; padding: 12px; font-size: 13px; color: #fff; border-bottom: 2px solid #eee; }
        td { padding: 12px; font-size: 13px; border-bottom: 1px solid #f1f1f1; }

        /* Inputs & Selects */
        .table-input { width: 100%; border: 1px solid transparent; background: transparent; padding: 6px; outline: none; transition: 0.2s; }
        .editing-active { background: #fff !important; border: 1px solid #3b82f6 !important; border-radius: 6px; padding: 6px; }

        .status-select { padding: 8px; border-radius: 8px; font-weight: bold; border: 1px solid #ddd; cursor: pointer; min-width: 120px; }
        .status-select:disabled { opacity: 1; color: inherit; cursor: default; border: 1px solid transparent; background: transparent; appearance: none; }

        /* Status Colors */
        .approved { background: #dcfce7; color: #166534; }
        .warrenty { background: #fee2e2; color: #991b1b; }
        .non-approved { background: #fef3c7; color: #92400e; }
        .pending { background: #e0e7ff; color: #3730a3; }

        /* Buttons */
        .btn-edit { background: #065f46; color: white; border:none; padding: 8px 16px; border-radius: 6px; cursor:pointer; font-weight: 500; }
        .btn-save-active { background: #2563eb !important; }
        .btn-delete { background: #7f1d1d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 12px; display: inline-block; }
        
        .save-msg { font-size: 11px; color: #059669; display: none; font-weight: bold; margin-top: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Service Job Management</h2>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 10px; margin-bottom: 20px; border-radius: 6px;">
            Job record deleted successfully!
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Job no,</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Issue</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // SQL JOIN: Tables 3ma connect karala data gannawa
            // Customer join wenne phone number eken kiyala oya kiwwa nisa eka damma
            $sql = "SELECT j.job_no, j.job_status, c.customer_name, c.email, c.phone_number, jd.issue_name 
                    FROM job j
                    LEFT JOIN customer c ON j.phone_number = c.phone_number
                    LEFT JOIN job_device jd ON j.job_no = jd.job_no
                    ORDER BY j.job_no DESC";
            
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $id = $row['job_no'];
                    $status_class = strtolower(str_replace(' ', '-', $row['job_status'] ?? 'pending'));
            ?>
            <tr id="row-<?php echo $id; ?>">
                <td><strong>#<?php echo $id; ?></strong></td>
                <td><input type="text" id="name-<?php echo $id; ?>" class="table-input" value="<?php echo $row['customer_name']; ?>" readonly></td>
                <td><input type="text" id="email-<?php echo $id; ?>" class="table-input" value="<?php echo $row['email']; ?>" readonly></td>
                <td><input type="text" id="issue-<?php echo $id; ?>" class="table-input" value="<?php echo $row['issue_name']; ?>" readonly></td>
                <td><input type="text" id="phone-<?php echo $id; ?>" class="table-input" value="<?php echo $row['phone_number']; ?>" readonly></td>
                <td>
                    <select id="stat-<?php echo $id; ?>" class="status-select <?php echo $status_class; ?>" disabled>
                        <option value="Pending" <?php if($row['job_status']=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Approved" <?php if($row['job_status']=='Approved') echo 'selected'; ?>>Approved</option>
                        
                        <option value="Non Approved" <?php if($row['job_status']=='Non Approved') echo 'selected'; ?>>Non Approved</option>
                    </select>
                </td>
                <td>
                    <div style="display: flex; gap: 5px; align-items: center;">
                        <button id="btn-edit-<?php echo $id; ?>" class="btn-edit" onclick="toggleEdit('<?php echo $id; ?>')">Edit</button>
                        <a href="delete.php?job_no=<?php echo $id; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this job?')">Delete</a>
                    </div>
                    <span id="msg-<?php echo $id; ?>" class="save-msg">✓ Saved</span>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>No jobs found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function toggleEdit(id) {
    const fields = ['name', 'email', 'issue', 'phone'];
    const btn = document.getElementById('btn-edit-' + id);
    const stat = document.getElementById('stat-' + id);
    const isReadOnly = document.getElementById('name-' + id).readOnly;

    if (isReadOnly) {
        // Switch to Edit Mode
        fields.forEach(f => {
            let el = document.getElementById(f + '-' + id);
            el.readOnly = false;
            el.classList.add('editing-active');
        });
        stat.disabled = false;
        btn.innerText = "Save";
        btn.classList.add('btn-save-active');
    } else {
        // Switch to Save Mode
        saveData(id, fields, stat, btn);
    }
}

function saveData(id, fields, stat, btn) {
    const data = {
        job_no: id,
        customer_name: document.getElementById('name-' + id).value,
        email: document.getElementById('email-' + id).value,
        issue_name: document.getElementById('issue-' + id).value,
        phone_number: document.getElementById('phone-' + id).value,
        job_status: stat.value
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_engine.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const response = this.responseText.trim();
            console.log("Server Response: " + response);

            if (response === "success") {
                // UI ekath normal thathvayata harawanna
                fields.forEach(f => {
                    let el = document.getElementById(f + '-' + id);
                    el.readOnly = true;
                    el.classList.remove('editing-active');
                });
                stat.disabled = true;
                btn.innerText = "Edit";
                btn.classList.remove('btn-save-active');

                // Update Select Color
                stat.className = 'status-select ' + stat.value.toLowerCase().replace(' ', '-');
                
                // Show Success Message
                let msg = document.getElementById('msg-' + id);
                msg.style.display = 'block';
                setTimeout(() => { msg.style.display = 'none'; }, 2000);
            } else {
                alert("Update failed: " + response);
            }
        }
    };
    
    // JSON data yawaddi eka encode karala yawanna ona
    xhr.send("id=" + encodeURIComponent(id) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}
</script>
</body>
</html>