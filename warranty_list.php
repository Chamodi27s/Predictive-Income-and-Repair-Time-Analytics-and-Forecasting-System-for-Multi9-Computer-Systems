<?php 
include 'db_config.php';
include 'navbar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warranty Management | Smart Repair</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f1f5f9; padding-top: 120px;   /* 🔥 navbar height */
    padding-left: 40px;
    padding-right: 40px;}
        .container { max-width: 1350px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        
        .search-box { display: flex; gap: 10px; }
        .search-input { padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 8px; width: 300px; outline: none; }
        .btn-clear { background: #64748b; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; color: #475569; padding: 15px; text-align: left; font-size: 13px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
        
        /* Input & Button Styles */
        .status-select { padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer; font-weight: bold; width: 100%; }
        
        .supplier-input { padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; width: 150px; background: #f8fafc; outline: none; }
        .supplier-input.editing { background: #fff; border-color: #3b82f6; box-shadow: 0 0 5px rgba(59,130,246,0.3); }
        
        .btn-edit { background: #3b82f6; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-edit.save { background: #10b981; }
        
        .save-toast { position: fixed; bottom: 20px; right: 20px; background: #1e293b; color: white; padding: 12px 25px; border-radius: 8px; display: none; z-index: 1000; box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2>🛡️ Warranty Management</h2>
        <div class="search-box">
            <input type="text" id="warrantySearch" class="search-input" placeholder="Search Job, Name, Phone..." onkeyup="filterWarranty()">
            <button class="btn-clear" onclick="clearSearch()">Clear</button>
        </div>
    </div>

    <table id="warrantyTable">
        <thead>
            <tr>
                <th>Job No</th>
                <th>Device Details</th>
                <th>Customer / Phone</th>
                <th>Status (Auto-Save)</th>
                <th style="text-align: right;">Supplier Control</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT jd.*, j.job_date, c.customer_name, c.phone_number 
                      FROM job_device jd
                      JOIN job j ON jd.job_no = j.job_no
                      JOIN customer c ON j.phone_number = c.phone_number
                      WHERE jd.warranty_status = 'Warranty' 
                      ORDER BY j.job_date DESC";
            $result = mysqli_query($conn, $query);

            while($row = mysqli_fetch_assoc($result)): 
                $id = $row['job_device_id'];
            ?>
            <tr>
                <td><strong>#<?= $row['job_no'] ?></strong></td>
                <td>
                    <strong><?= htmlspecialchars($row['device_name']) ?></strong><br>
                    <small style="color: #64748b;"><?= htmlspecialchars($row['issue_name']) ?></small>
                </td>
                <td>
                    <?= htmlspecialchars($row['customer_name']) ?><br>
                    <span style="font-size: 12px; color: #64748b;"><?= htmlspecialchars($row['phone_number']) ?></span>
                </td>
                <td>
                    <select class="status-select" id="stat-<?= $id ?>" onchange="saveStatus(<?= $id ?>)">
                        <option value="Pending" <?= $row['device_status']=='Pending'?'selected':'' ?>>Pending</option>
                        <option value="Sent to Warranty" <?= $row['device_status']=='Sent to Warranty'?'selected':'' ?>>Sent to Warranty</option>
                        <option value="Completed" <?= $row['device_status']=='Completed'?'selected':'' ?>>Completed</option>
                        <option value="Rejected" <?= $row['device_status']=='Rejected'?'selected':'' ?>>Rejected</option>
                    </select>
                </td>
                <td style="text-align: right;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <input type="text" id="sup-<?= $id ?>" class="supplier-input" 
                               value="<?= htmlspecialchars($row['supplier_name'] ?? '') ?>" readonly placeholder="Supplier...">
                        <button id="btn-<?= $id ?>" class="btn-edit" onclick="toggleSupplier(<?= $id ?>)">Edit</button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="saveMsg" class="save-toast">✅ Saved!</div>

<script>
// Search Function
function filterWarranty() {
    let input = document.getElementById("warrantySearch").value.toUpperCase();
    let tr = document.getElementById("warrantyTable").getElementsByTagName("tr");
    for (let i = 1; i < tr.length; i++) {
        tr[i].style.display = tr[i].textContent.toUpperCase().includes(input) ? "" : "none";
    }
}

function clearSearch() {
    document.getElementById("warrantySearch").value = "";
    filterWarranty();
}

// 1. Status Auto-Save
function saveStatus(id) {
    let status = document.getElementById('stat-' + id).value;
    let supplier = document.getElementById('sup-' + id).value;
    ajaxUpdate(id, supplier, status, "Status Updated!");
}

// 2. Supplier Edit/Save Button
function toggleSupplier(id) {
    let input = document.getElementById('sup-' + id);
    let btn = document.getElementById('btn-' + id);
    let status = document.getElementById('stat-' + id).value;

    if (input.readOnly) {
        input.readOnly = false;
        input.classList.add('editing');
        btn.innerText = "Save";
        btn.classList.add('save');
        input.focus();
    } else {
        ajaxUpdate(id, input.value, status, "Supplier Saved!");
        input.readOnly = true;
        input.classList.remove('editing');
        btn.innerText = "Edit";
        btn.classList.remove('save');
    }
}

function ajaxUpdate(id, supplier, status, msg) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "update_warranty_list.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.trim() === "success") {
                showToast(msg);
            } else {
                alert("Error: Data not saved. Make sure update_warranty_ajax.php is correct.");
            }
        }
    };
    xhr.send("id=" + id + "&supplier=" + encodeURIComponent(supplier) + "&status=" + encodeURIComponent(status));
}

function showToast(text) {
    let toast = document.getElementById('saveMsg');
    toast.innerText = "✅ " + text;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2000);
}
</script>
</body>
</html>