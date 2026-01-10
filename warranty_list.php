<?php 
include 'db_config.php';
include 'navbar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warranty Management | Smart Repair</title>
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
            padding-top: 120px;
            padding-left: 40px;
            padding-right: 40px;
            color: var(--text-main);
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 32px 40px;
            border-radius: 20px;
            margin-bottom: 28px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: white;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: auto;
            background: var(--card-bg);
            padding: 36px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }

        /* Header Section */
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border);
        }

        .header-flex h2 {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Search Box */
        .search-box {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-input {
            padding: 12px 20px;
            border: 2px solid var(--border);
            border-radius: 12px;
            width: 320px;
            outline: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .search-input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        .btn-clear {
            background: #e2e8f0;
            color: var(--text-dark);
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-clear:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-dark);
            padding: 16px 18px;
            text-align: left;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #dee2e6;
        }

        th:first-child {
            border-top-left-radius: 12px;
        }

        th:last-child {
            border-top-right-radius: 12px;
        }

        tbody tr {
            transition: all 0.3s ease;
            background: white;
        }

        tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        td {
            padding: 16px 18px;
            border-bottom: 1px solid #f0f2f5;
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
        }

        td strong {
            color: var(--text-dark);
            font-weight: 800;
        }

        /* Status Select */
        .status-select {
            padding: 10px 14px;
            border-radius: 10px;
            border: 2px solid var(--border);
            cursor: pointer;
            font-weight: 700;
            width: 100%;
            font-size: 13px;
            transition: all 0.3s ease;
            background: white;
            color: var(--text-dark);
        }

        .status-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        .status-select option {
            padding: 10px;
            font-weight: 600;
        }

        /* Supplier Input */
        .supplier-input {
            padding: 10px 14px;
            border-radius: 10px;
            border: 2px solid var(--border);
            width: 180px;
            background: #f8fafc;
            outline: none;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .supplier-input.editing {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        /* Buttons */
        .btn-edit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-dark) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 204, 113, 0.4);
        }

        .btn-edit.save {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-edit.save:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        /* Supplier Control Flex */
        .supplier-control {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            align-items: center;
        }

        /* Toast Notification */
        .save-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 16px 28px;
            border-radius: 12px;
            display: none;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            font-weight: 700;
            font-size: 15px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Device Details */
        .device-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .device-name {
            font-weight: 800;
            color: var(--text-dark);
            font-size: 15px;
        }

        .device-issue {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
        }

        /* Customer Info */
        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .customer-name {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 14px;
        }

        .customer-phone {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        /* Job Badge */
        .job-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(25, 118, 210, 0.15);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 100px 15px 30px 15px;
            }

            .container {
                padding: 24px;
            }

            .header-flex {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }

            .search-box {
                flex-direction: column;
            }

            .search-input {
                width: 100%;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 12px 10px;
            }

            .supplier-input {
                width: 120px;
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

        .container {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>

<div class="page-header">
    <h1> Warranty Management System</h1>
</div>

<div class="container">
    <div class="header-flex">
        <h2>📋 All Warranty Devices</h2>
        <div class="search-box">
            <input type="text" id="warrantySearch" class="search-input" placeholder="🔍 Search Job, Name, Phone..." onkeyup="filterWarranty()">
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
                <td>
                    <span class="job-badge">#<?= $row['job_no'] ?></span>
                </td>
                <td>
                    <div class="device-details">
                        <div class="device-name">📱 <?= htmlspecialchars($row['device_name']) ?></div>
                        <div class="device-issue"><?= htmlspecialchars($row['issue_name']) ?></div>
                    </div>
                </td>
                <td>
                    <div class="customer-info">
                        <div class="customer-name"><?= htmlspecialchars($row['customer_name']) ?></div>
                        <div class="customer-phone"><?= htmlspecialchars($row['phone_number']) ?></div>
                    </div>
                </td>
                <td>
                    <select class="status-select" id="stat-<?= $id ?>" onchange="saveStatus(<?= $id ?>)">
                        <option value="Pending" <?= $row['device_status']=='Pending'?'selected':'' ?>>⏳ Pending</option>
                        <option value="Sent to Warranty" <?= $row['device_status']=='Sent to Warranty'?'selected':'' ?>>📦 Sent to Warranty</option>
                        <option value="Completed" <?= $row['device_status']=='Completed'?'selected':'' ?>>✅ Completed</option>
                        <option value="Rejected" <?= $row['device_status']=='Rejected'?'selected':'' ?>>❌ Rejected</option>
                    </select>
                </td>
                <td style="text-align: right;">
                    <div class="supplier-control">
                        <input type="text" id="sup-<?= $id ?>" class="supplier-input" 
                               value="<?= htmlspecialchars($row['supplier_name'] ?? '') ?>" readonly placeholder="Supplier name...">
                        <button id="btn-<?= $id ?>" class="btn-edit" onclick="toggleSupplier(<?= $id ?>)">✏️ Edit</button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="saveMsg" class="save-toast">✅ Saved Successfully!</div>

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
        btn.innerHTML = "💾 Save";
        btn.classList.add('save');
        input.focus();
    } else {
        ajaxUpdate(id, input.value, status, "Supplier Saved!");
        input.readOnly = true;
        input.classList.remove('editing');
        btn.innerHTML = "✏️ Edit";
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