<?php 
include 'db_config.php';
include 'navbar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warranty Jobs Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; padding: 20px; }
        .container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        h2 { margin-bottom: 20px; color: #2e7d32; border-left: 5px solid #2e7d32; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; margin-top: 20px; }
        th { background: #2e7d32; text-align: left; padding: 12px; font-size: 13px; color: #fff; }
        td { padding: 12px; font-size: 13px; border-bottom: 1px solid #f1f1f1; }
        
        /* Input Styling for Editing */
        .table-input { width: 100%; border: 1px solid transparent; background: transparent; padding: 6px; outline: none; }
        .editing-active { background: #fff !important; border: 1px solid #3b82f6 !important; border-radius: 6px; }

        .warranty-badge { 
            background: #fff3e0; color: #e65100; padding: 4px 10px; border-radius: 20px; 
            font-weight: bold; font-size: 11px; border: 1px solid #ffcc80; display: inline-block;
        }
        
        .status-select { padding: 6px; border-radius: 6px; border: 1px solid #ddd; outline: none; cursor: pointer; }
        .save-msg { font-size: 11px; color: #059669; display: none; font-weight: bold; margin-left: 10px; }
        
        .status-pending { background-color: #e0e7ff; color: #3730a3; }
        .status-approved { background-color: #dcfce7; color: #166534; }

        .btn-edit { background: #065f46; color: white; border:none; padding: 7px 14px; border-radius: 6px; cursor:pointer; }
        .btn-save-active { background: #2563eb !important; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛡️ Warranty Collected Jobs</h2>
    
    <table>
        <thead>
            <tr>
                <th>Job No</th>
                <th>Customer Name</th>
                <th>Issue</th>
                <th>Phone</th>
                <th>Warranty Status</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT j.job_no, j.job_status, c.customer_name, c.email, c.phone_number, jd.issue_name, jd.warranty_status 
                    FROM job j
                    LEFT JOIN customer c ON j.phone_number = c.phone_number
                    LEFT JOIN job_device jd ON j.job_no = jd.job_no
                    WHERE jd.warranty_status = 'Warranty' 
                    ORDER BY j.job_no DESC";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $id = $row['job_no'];
                    $status_val = $row['job_status'] ?? 'Pending';
                    $status_class = ($status_val == 'Approved') ? 'status-approved' : 'status-pending';
            ?>
            <tr id="row-<?php echo $id; ?>">
                <td><strong>#<?php echo $id; ?></strong></td>
                
                <td><input type="text" id="name-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['customer_name']); ?>" readonly></td>
                <td><input type="text" id="issue-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['issue_name']); ?>" readonly></td>
                <td><input type="text" id="phone-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['phone_number']); ?>" readonly></td>
                
                <input type="hidden" id="email-<?php echo $id; ?>" value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>">

                <td><span class="warranty-badge"><?php echo htmlspecialchars($row['warranty_status']); ?></span></td>
                <td>
                    <select id="stat-<?php echo $id; ?>" class="status-select <?php echo $status_class; ?>" onchange="updateStatusOnly('<?php echo $id; ?>')">
                        <option value="Pending" <?php if($status_val=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Pending" <?php if($status_val=='not-Approved') echo 'selected'; ?>>not-Approved</option>
                        <option value="Approved" <?php if($status_val=='Approved') echo 'selected'; ?>>Approved</option>
                    </select>
                    <span id="msg-<?php echo $id; ?>" class="save-msg">✓ Saved</span>
                </td>
                <td>
                    <button id="btn-edit-<?php echo $id; ?>" class="btn-edit" onclick="toggleEdit('<?php echo $id; ?>')">Edit</button>
                </td>
            </tr>
            <?php 
                }
            } else { 
                echo "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #666;'>No warranty jobs found.</td></tr>"; 
            }
            ?>
        </tbody>
    </table>
</div>

<script>
// Edit button එක එබූ විට ක්‍රියාත්මක වන function එක
function toggleEdit(id) {
    const fields = ['name', 'issue', 'phone'];
    const btn = document.getElementById('btn-edit-' + id);
    const isReadOnly = document.getElementById('name-' + id).readOnly;

    if (isReadOnly) {
        // Edit Mode එකට මාරු වීම
        fields.forEach(f => {
            let el = document.getElementById(f + '-' + id);
            el.readOnly = false;
            el.classList.add('editing-active');
        });
        btn.innerText = "Save";
        btn.classList.add('btn-save-active');
    } else {
        // Save කිරීම
        saveToDB(id, () => {
            fields.forEach(f => {
                let el = document.getElementById(f + '-' + id);
                el.readOnly = true;
                el.classList.remove('editing-active');
            });
            btn.innerText = "Edit";
            btn.classList.remove('btn-save-active');
        });
    }
}

function updateStatusOnly(id) {
    const statSelect = document.getElementById('stat-' + id);
    if(statSelect.value === 'Approved') {
        statSelect.className = 'status-select status-approved';
    } else {
        statSelect.className = 'status-select status-pending';
    }
    saveToDB(id);
}

function saveToDB(id, callback = null) {
    const data = {
        job_no: id,
        customer_name: document.getElementById('name-' + id).value,
        email: document.getElementById('email-' + id).value,
        issue_name: document.getElementById('issue-' + id).value,
        phone_number: document.getElementById('phone-' + id).value,
        job_status: document.getElementById('stat-' + id).value
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_engine.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.trim() === "success") {
                let msg = document.getElementById('msg-' + id);
                msg.style.display = 'inline';
                setTimeout(() => { msg.style.display = 'none'; }, 2000);
                if (callback) callback();
            } else {
                alert("Error: " + this.responseText);
            }
        }
    };
    xhr.send("id=" + encodeURIComponent(id) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}
</script>
</body>
</html>