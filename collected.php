<?php 
include 'db_config.php';
include 'navbar.php'; 

$filter_query = " WHERE (jd.warranty_status = 'No' OR jd.warranty_status = 'No Warranty' OR jd.warranty_status IS NULL OR jd.warranty_status = '') 
                AND (jd.job_status != 'Approved' OR jd.job_status IS NULL) ";

if(isset($_GET['range'])) {
    if($_GET['range'] == 'today') {
        $filter_query .= " AND DATE(j.job_date) = CURDATE() ";
    } elseif($_GET['range'] == 'month') {
        $filter_query .= " AND MONTH(j.job_date) = MONTH(CURDATE()) AND YEAR(j.job_date) = YEAR(CURDATE()) ";
    } elseif($_GET['range'] == '2weeks') {
        $filter_query .= " AND j.job_date >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK) ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobs Management | Smart Repair</title>
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
    --success: #22c55e;
    --warning: #f59e0b;

    --purple: #7c3aed;
    --purple-dark: #5b21b6;
    --purple-soft: #ede9fe;

    --bg-main: #f8fafc;
    --card-bg: #ffffff;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.10);
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
    padding: 140px 10px 40px;
    color: var(--text-main);
}

.page-container {
    max-width: 1500px;
    margin: auto;
}

.page-header {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    padding: 36px 40px;
    border-radius: 20px;
    margin-top: 15px;
    margin-bottom: 32px;
    box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
    color: white;
    text-align: center;
}

.page-header h1 {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 8px;
}

.page-header p {
    font-size: 15px;
    opacity: 0.95;
}

.container {
    background: var(--card-bg);
    padding: 24px 20px;
    border-radius: 22px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
}

/* filters */
.filter-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: #ffffff;
    cursor: pointer;
    font-weight: 700;
    color: #334155;
}

.filter-btn:hover {
    background: #ecfdf5;
    border-color: var(--primary);
    color: #15803d;
}

.filter-btn.active {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    border-color: transparent;
}

/* table */
.table-container {
    overflow: visible;
    border-radius: 16px;
    border: 1px solid var(--border);
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th {
    background: #f8fafc;
    color: #64748b;
    padding: 10px 10px;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 0.5px;
    text-align: left;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
    font-size: 13px;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

tbody tr {
}

tbody tr:hover {
    background: #f8fafc;
}

/* inputs */
.table-input,
.est-input {
    border: 2px solid transparent;
    background: #f8fafc;
    padding: 9px 10px;
    border-radius: 10px;
    font-weight: 600;
    color: #334155;
}

.table-input {
    width: 100%;
    box-sizing: border-box;
    padding: 5px 6px;
    font-size: 12px;
}

.est-input {
    width: 100%;
    box-sizing: border-box;
    padding: 5px 6px;
    font-size: 12px;
}

.table-input:focus,
.est-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(46,204,113,0.15);
}

.editing-active {
    background: #ffffff !important;
    border: 2px solid var(--primary) !important;
}

/* advance badge - green theme */
.advance-badge {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #15803d;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 11px;
    display: inline-block;
    border: 1px solid #86efac;
}

/* selects */
.status-select {
    padding: 5px 6px;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    background: #ffffff;
    width: 100%;
    box-sizing: border-box;
}

.status-pending {
    background: #e0e7ff;
    color: #3730a3;
}

.status-approved {
    background: #dcfce7;
    color: #166534;
}

/* buttons - side by side */
.btn-action-group {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 5px;
    flex-wrap: nowrap;
}

.btn-edit,
.btn-sms {
    border: none;
    padding: 6px 10px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: 700;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Send Estimate - white with green border */
.btn-sms {
    background: #ffffff;
    color: #16a34a;
    border: 1.5px solid #22c55e;
    box-shadow: 0 1px 4px rgba(46,204,113,0.15);
}

/* Edit - solid green */
.btn-edit {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    box-shadow: 0 1px 6px rgba(34,197,94,0.25);
}

.btn-sms:hover {
    background: #f0fdf4;
    border-color: #16a34a;
}

.btn-edit:hover {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.save-msg {
    font-size: 11px;
    color: var(--success);
    display: none;
    font-weight: 700;
}

/* dark mode */
body.dark-mode {
    background: linear-gradient(135deg, #020617, #0f172a) !important;
    color: #e2e8f0 !important;
}

body.dark-mode .container {
    background: rgba(30,41,59,0.75) !important;
    border-color: rgba(255,255,255,0.08);
}

body.dark-mode td {
    color: #cbd5e1;
    border-bottom-color: rgba(255,255,255,0.06);
}

body.dark-mode tbody tr:hover {
    background: rgba(255,255,255,0.05);
}

body.dark-mode .table-input,
body.dark-mode .est-input,
body.dark-mode .status-select {
    background: rgba(15,23,42,0.8);
    color: white;
    border-color: rgba(255,255,255,0.10);
}

body.dark-mode .advance-badge {
    background: rgba(124,58,237,0.2);
    color: #c4b5fd;
}

@media(max-width: 768px) {
    body {
        padding: 110px 12px 20px;
    }

    .page-header {
        padding: 24px 16px;
        border-radius: 16px;
        margin-bottom: 20px;
    }

    .page-header h1 {
        font-size: 24px;
    }

    .page-header p {
        font-size: 13px;
    }

    .container {
        padding: 15px;
        border-radius: 16px;
        overflow-x: hidden;
    }

    .filter-buttons {
        gap: 6px;
        margin-bottom: 15px;
    }

    .filter-btn {
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 10px;
        flex: 1;
        text-align: center;
        min-width: 75px;
    }

    .table-container {
        border-radius: 12px;
        box-shadow: none;
    }

    table {
        min-width: 1000px;
    }

    th, td {
        padding: 12px 10px;
        font-size: 13px;
    }

    .est-input {
        width: 90px;
    }

    .btn-sms, .btn-edit {
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 10px;
    }
}
</style>
</head>

<body id="jobsBody">

<div class="page-container">
    <div class="page-header">
        <h1>Jobs Management</h1>
        <p>Manage non-warranty jobs, estimates and approvals</p>
    </div>

    <div class="container">
        <div class="filter-buttons">
            <button class="filter-btn <?= !isset($_GET['range']) ? 'active' : ''; ?>" onclick="window.location.href='?'">All</button>
            <button class="filter-btn <?= ($_GET['range'] ?? '') == 'today' ? 'active' : ''; ?>" onclick="window.location.href='?range=today'">Today</button>
            <button class="filter-btn <?= ($_GET['range'] ?? '') == '2weeks' ? 'active' : ''; ?>" onclick="window.location.href='?range=2weeks'">Last 2 Weeks</button>
            <button class="filter-btn <?= ($_GET['range'] ?? '') == 'month' ? 'active' : ''; ?>" onclick="window.location.href='?range=month'">This Month</button>
        </div>

        <div class="table-container">
            <table id="jobsTable">
                <colgroup>
                    <col style="width:10%">  <!-- Job No -->
                    <col style="width:13%">  <!-- Customer -->
                    <col style="width:10%">  <!-- Issue -->
                    <col style="width:8%">   <!-- Estimate -->
                    <col style="width:8%">   <!-- Advance -->
                    <col style="width:10%">  <!-- Category -->
                    <col style="width:10%">  <!-- Phone -->
                    <col style="width:10%">  <!-- Status -->
                    <col style="width:21%">  <!-- Action -->
                </colgroup>
                <thead>
                    <tr>
                        <th>Job No</th>
                        <th>Customer</th>
                        <th>Issue</th>
                        <th>Estimate</th>
                        <th>Advance</th>
                        <th>Category</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $sql = "SELECT j.job_no, jd.job_status, jd.estimated_cost, jd.advance_paid, 
                        c.customer_name, c.email, c.phone_number,
                        jd.job_device_id as device_id,
                        jd.issue_name, jd.issue_category 
                        FROM job_device jd
                        LEFT JOIN job j ON jd.job_no = j.job_no
                        LEFT JOIN customer c ON j.phone_number = c.phone_number
                        $filter_query 
                        ORDER BY jd.job_device_id DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $id = $row['job_no'];
                        $device_id = $row['device_id']; 
                        $row_uid = $id . "_" . $device_id;

                        $status_val = $row['job_status'] ?? 'Pending';
                        $cat_val = $row['issue_category'] ?? 'Hardware';
                        $est_cost = $row['estimated_cost'] ?? '0.00';
                        $adv_paid = $row['advance_paid'] ?? '0.00';

                        $status_class = ($status_val == 'Approved') ? 'status-approved' : 'status-pending';
                ?>
                    <tr id="row-<?= $row_uid ?>">
                        <td><strong>#<?= $id ?></strong></td>

                        <td>
                            <input type="text" id="name-<?= $row_uid ?>" class="table-input"
                            value="<?= htmlspecialchars($row['customer_name']); ?>" readonly>
                        </td>

                        <td>
                            <input type="text" id="issue-<?= $row_uid ?>" class="table-input"
                            value="<?= htmlspecialchars($row['issue_name']); ?>" readonly>
                        </td>

                        <td>
                            <input type="number" id="est-<?= $row_uid ?>" class="est-input"
                            value="<?= $est_cost ?>"
                            onchange="saveToDB('<?= $row_uid ?>', '<?= $id ?>', '<?= $device_id ?>')">
                        </td>

                        <td>
                            <span class="advance-badge">Rs. <?= number_format($adv_paid, 2); ?></span>
                        </td>

                        <td>
                            <select id="cat-<?= $row_uid ?>" class="status-select"
                            onchange="saveToDB('<?= $row_uid ?>', '<?= $id ?>', '<?= $device_id ?>')">
                                <option value="Hardware" <?= $cat_val == 'Hardware' ? 'selected' : ''; ?>>Hardware</option>
                                <option value="Software" <?= $cat_val == 'Software' ? 'selected' : ''; ?>>Software</option>
                            </select>
                        </td>

                        <td>
                            <input type="text" id="phone-<?= $row_uid ?>" class="table-input"
                            value="<?= htmlspecialchars($row['phone_number']); ?>" readonly>
                        </td>

                        <td>
                            <select id="stat-<?= $row_uid ?>" class="status-select <?= $status_class ?>"
                            onchange="updateStatusOnly('<?= $row_uid ?>', '<?= $id ?>', '<?= $device_id ?>')">
                                <option value="Pending" <?= $status_val == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?= $status_val == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            </select>
                            <span id="msg-<?= $row_uid ?>" class="save-msg">Saved</span>
                        </td>

                        <td>
                            <div class="btn-action-group">
                                <button class="btn-sms" onclick="sendEstimateSMS('<?= $row_uid ?>', '<?= $id ?>', '<?= $device_id ?>')">Send Estimate</button>
                                <button id="btn-edit-<?= $row_uid ?>" class="btn-edit" onclick="toggleEdit('<?= $row_uid ?>', '<?= $id ?>', '<?= $device_id ?>')">Edit</button>
                            </div>
                            <input type="hidden" id="email-<?= $row_uid ?>" value="<?= $row['email']; ?>">
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align:center; padding:50px;'>No records found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function syncTheme() {
    const body = document.getElementById('jobsBody');
    const isDark = localStorage.getItem("darkMode") === "enabled";

    if (isDark) {
        body.classList.add("dark-mode");
    } else {
        body.classList.remove("dark-mode");
    }
}

syncTheme();
setInterval(syncTheme, 1000);

function sendEstimateSMS(row_uid, job_no, device_id) {
    let parts = prompt("Enter required parts and prices for the repair:", "Service Charge Only");
    if (parts === null) return;

    const data = {
        action: 'send_estimate_sms',
        job_no: job_no,
        device_id: device_id,
        customer_name: document.getElementById('name-' + row_uid).value,
        issue_name: document.getElementById('issue-' + row_uid).value,
        estimated_cost: document.getElementById('est-' + row_uid).value,
        parts_details: parts
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_engine.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.trim().includes("success")) {
                alert("Estimate SMS sent successfully!");
            } else {
                alert("Error occurred while sending SMS: " + this.responseText);
            }
        }
    };

    xhr.send("id=" + encodeURIComponent(job_no) + "&device_id=" + encodeURIComponent(device_id) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}

function updateStatusOnly(row_uid, job_no, device_id) {
    const statSelect = document.getElementById('stat-' + row_uid);
    let advanceAmount = 0;

    if (statSelect.value === 'Approved') {
        if (confirm("Did the customer confirm this? Once approved, it will be removed from this list.")) {
            let userInput = prompt("Enter the received advance payment amount:", "0");

            // මෙහිදී Cancel කළත් හෝ හිස්ව තිබුණත් advance එක 0 ලෙස සලකා Approved වේ
            if (userInput !== null && userInput.trim() !== "") {
                advanceAmount = parseFloat(userInput) || 0;
            } else {
                advanceAmount = 0; 
            }

            saveToDB(row_uid, job_no, device_id, advanceAmount);
        } else {
            statSelect.value = 'Pending';
            statSelect.className = 'status-select status-pending';
        }
    } else {
        saveToDB(row_uid, job_no, device_id, 0);
    }
}

function saveToDB(row_uid, job_no, device_id, advance = 0, callback = null) {
    const data = {
        job_no: job_no,
        device_id: device_id,
        customer_name: document.getElementById('name-' + row_uid).value,
        email: document.getElementById('email-' + row_uid).value,
        issue_name: document.getElementById('issue-' + row_uid).value,
        phone_number: document.getElementById('phone-' + row_uid).value,
        job_status: document.getElementById('stat-' + row_uid).value,
        issue_category: document.getElementById('cat-' + row_uid).value,
        estimated_cost: document.getElementById('est-' + row_uid).value,
        advance_paid: advance
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_engine.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.trim().includes("success")) {
                document.getElementById('msg-' + row_uid).style.display = 'inline';

                setTimeout(() => {
                    document.getElementById('msg-' + row_uid).style.display = 'none';

                    if (data.job_status === 'Approved') {
                        location.reload();
                    }
                }, 1000);

                if (callback) callback();
            }
        }
    };

    xhr.send("id=" + encodeURIComponent(job_no) + "&device_id=" + encodeURIComponent(device_id) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}

function toggleEdit(row_uid, job_no, device_id) {
    const fields = ['name', 'issue'];
    const btn = document.getElementById('btn-edit-' + row_uid);
    const isReadOnly = document.getElementById('name-' + row_uid).readOnly;

    if (isReadOnly) {
        fields.forEach(f => {
            let el = document.getElementById(f + '-' + row_uid);
            el.readOnly = false;
            el.classList.add('editing-active');
        });

        btn.innerHTML = " Save";
    } else {
        saveToDB(row_uid, job_no, device_id, 0, () => {
            fields.forEach(f => {
                let el = document.getElementById(f + '-' + row_uid);
                el.readOnly = true;
                el.classList.remove('editing-active');
            });

            btn.innerHTML = "Edit";
        });
    }
}
</script>

<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>