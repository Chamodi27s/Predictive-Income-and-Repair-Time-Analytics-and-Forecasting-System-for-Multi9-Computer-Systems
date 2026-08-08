<?php 
include 'db_config.php';
include 'navbar.php'; 

$filter_query = " WHERE (jd.warranty_status = 'No' OR jd.warranty_status = 'No Warranty' OR jd.warranty_status IS NULL OR jd.warranty_status = '') ";

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
    padding: 140px 20px 40px;
    color: var(--text-main);
}

.page-container {
    max-width: 1200px;
    margin: auto;
}

.page-header {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    padding: 36px 40px;
    border-radius: 22px;
    margin-bottom: 32px;
    box-shadow: 0 12px 30px rgba(46,204,113,0.35);
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
    padding: 34px;
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
    transition: 0.3s;
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
    overflow-x: auto;
    border-radius: 16px;
    border: 1px solid var(--border);
    -webkit-overflow-scrolling: touch;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1100px;
}

th {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    padding: 16px 18px;
    color: white;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 13px;
    text-align: left;
}

td {
    padding: 16px 18px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 14px;
}

tbody tr {
    transition: 0.25s;
}

tbody tr:hover {
    background: #f8fafc;
    transform: translateX(4px);
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
}

.est-input {
    width: 110px;
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

/* advance badge - purple */
.advance-badge {
    background: linear-gradient(135deg, #ede9fe, #c4b5fd);
    color: #5b21b6;
    padding: 7px 14px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 13px;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(124,58,237,0.15);
    white-space: nowrap;
}

/* selects */
.status-select {
    padding: 10px 14px;
    border-radius: 12px;
    border: 2px solid var(--border);
    font-weight: 700;
    cursor: pointer;
    background: #ffffff;
}

.status-pending {
    background: #e0e7ff;
    color: #3730a3;
}

.status-approved {
    background: #dcfce7;
    color: #166534;
}

/* buttons */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.btn-edit,
.btn-sms {
    border: none;
    padding: 11px 16px;
    border-radius: 13px;
    cursor: pointer;
    font-weight: 800;
    transition: 0.3s;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 13px;
}

.btn-sms {
    background: linear-gradient(135deg, #fbbf24, #f97316);
    color: #1f2937;
    box-shadow: 0 5px 12px rgba(249,115,22,0.28);
}

.btn-edit {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    box-shadow: 0 5px 12px rgba(59,130,246,0.28);
}

.btn-sms:hover,
.btn-edit:hover {
    transform: translateY(-3px);
}

.btn-sms:hover {
    box-shadow: 0 8px 18px rgba(249,115,22,0.38);
}

.btn-edit:hover {
    box-shadow: 0 8px 18px rgba(59,130,246,0.38);
}

.status-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
}

.save-msg {
    font-size: 13px;
    color: var(--success);
    display: none;
    font-weight: 800;
    margin-left: 6px;
    white-space: nowrap;
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

/* ==================== RESPONSIVE MEDIA QUERIES ==================== */

/* Tablet & Medium Screens (Max-width: 1024px) */
@media (max-width: 1024px) {
    body {
        padding: 120px 16px 40px;
    }

    .page-header {
        margin-top: 15px;
        padding: 28px 24px;
        margin-bottom: 24px;
    }

    .page-header h1 {
        font-size: 26px;
    }

    .container {
        padding: 24px;
    }

    .filter-btn {
        padding: 9px 14px;
        font-size: 13px;
    }
}

/* Mobile & Small Tablets (Max-width: 768px) */
@media (max-width: 768px) {
    body {
        padding: 115px 12px 100px; /* Top spacing above header and bottom protection for chatbot */
    }

    .page-header {
        margin-top: 15px;
        padding: 22px 16px;
        border-radius: 18px;
        margin-bottom: 18px;
    }

    .page-header h1 {
        font-size: 22px;
    }

    .page-header p {
        font-size: 13px;
    }

    .container {
        padding: 14px 10px;
        border-radius: 18px;
    }

    /* Filter Buttons in 2x2 Compact Grid */
    .filter-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 16px;
    }

    .filter-btn {
        width: 100%;
        text-align: center;
        padding: 11px 8px;
        font-size: 13px;
        border-radius: 12px;
    }

    /* Transform Table into Responsive Cards on Mobile */
    .table-container {
        border: none;
        background: transparent;
        border-radius: 0;
        overflow: visible;
    }

    table#jobsTable,
    table#jobsTable tbody,
    table#jobsTable tr,
    table#jobsTable td {
        display: block;
        width: 100%;
    }

    table#jobsTable {
        min-width: 0 !important;
    }

    table#jobsTable thead {
        display: none;
    }

    table#jobsTable tbody tr {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 10px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 20px;
        margin-bottom: 16px;
        padding: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    table#jobsTable tbody tr:hover {
        transform: none;
    }

    body.dark-mode table#jobsTable tbody tr {
        background: rgba(30, 41, 59, 0.9) !important;
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
    }

    /* Card Field Cells */
    table#jobsTable td {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
        padding: 0;
        border: none;
        font-size: 14px;
    }

    /* Labels for mobile cards */
    table#jobsTable td::before {
        content: attr(data-label);
        font-weight: 800;
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    body.dark-mode table#jobsTable td::before {
        color: #94a3b8;
    }

    /* Row 1: Header - Job No (Left) & Status (Right) */
    table#jobsTable td:nth-child(1) {
        grid-column: 1 / 2;
        grid-row: 1;
        font-size: 18px;
        font-weight: 800;
        color: var(--primary-hover);
        padding-bottom: 8px;
        border-bottom: 1.5px dashed var(--border);
        justify-content: center;
    }

    table#jobsTable td:nth-child(1)::before {
        display: none;
    }

    table#jobsTable td:nth-child(8) {
        grid-column: 2 / 3;
        grid-row: 1;
        align-items: flex-end;
        padding-bottom: 8px;
        border-bottom: 1.5px dashed var(--border);
    }

    table#jobsTable td:nth-child(8)::before {
        display: none;
    }

    table#jobsTable td:nth-child(8) .status-wrapper {
        justify-content: flex-end;
        width: 100%;
    }

    /* Row 2: Customer Name */
    table#jobsTable td:nth-child(2) {
        grid-column: 1 / -1;
        grid-row: 2;
    }

    /* Row 3: Issue Name */
    table#jobsTable td:nth-child(3) {
        grid-column: 1 / -1;
        grid-row: 3;
    }

    /* Row 4: Phone Number */
    table#jobsTable td:nth-child(7) {
        grid-column: 1 / -1;
        grid-row: 4;
    }

    /* Row 5: Financial Stats - Estimate (Left) & Advance (Right) */
    table#jobsTable td:nth-child(4) {
        grid-column: 1 / 2;
        grid-row: 5;
        background: #f8fafc;
        padding: 10px;
        border-radius: 12px;
        border: 1px solid var(--border);
    }

    table#jobsTable td:nth-child(5) {
        grid-column: 2 / 3;
        grid-row: 5;
        background: #f8fafc;
        padding: 10px;
        border-radius: 12px;
        border: 1px solid var(--border);
    }

    body.dark-mode table#jobsTable td:nth-child(4),
    body.dark-mode table#jobsTable td:nth-child(5) {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    /* Row 6: Category */
    table#jobsTable td:nth-child(6) {
        grid-column: 1 / -1;
        grid-row: 6;
    }

    /* Row 7: Action Buttons */
    table#jobsTable td:nth-child(9) {
        grid-column: 1 / -1;
        grid-row: 7;
        padding-top: 4px;
    }

    table#jobsTable td:nth-child(9)::before {
        display: none;
    }

    /* Controls inside mobile cards */
    .table-input,
    .est-input,
    .status-select {
        width: 100% !important;
        max-width: 100% !important;
        font-size: 14px;
        padding: 9px 12px;
        border-radius: 10px;
        text-align: left;
    }

    .advance-badge {
        width: 100%;
        text-align: center;
        padding: 8px 10px;
        font-size: 13px;
    }

    .action-buttons {
        display: flex;
        flex-direction: row;
        gap: 8px;
        width: 100%;
    }

    .btn-sms,
    .btn-edit {
        flex: 1;
        padding: 11px 10px;
        font-size: 13px;
        border-radius: 12px;
    }
}

/* Extra Small Phones (Max-width: 480px) */
@media (max-width: 480px) {
    body {
        padding: 80px 8px 100px;
    }

    .container {
        padding: 10px 8px;
    }

    table#jobsTable tbody tr {
        padding: 14px 12px;
        gap: 10px 8px;
    }

    .action-buttons {
        flex-direction: row;
    }

    .btn-sms,
    .btn-edit {
        padding: 10px;
        font-size: 12.5px;
    }
}
</style>
</head>

<body id="jobsBody">

<div class="page-container">
    <div class="page-header">
        <h1> Jobs Management</h1>
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
                $sql = "SELECT j.job_no, j.job_status, j.estimated_cost, j.advance_paid,
                        c.customer_name, c.email, c.phone_number,
                        jd.issue_name, jd.issue_category 
                        FROM job j
                        LEFT JOIN customer c ON j.phone_number = c.phone_number
                        LEFT JOIN job_device jd ON j.job_no = jd.job_no
                        $filter_query 
                        ORDER BY j.job_no DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $counter = 0;

                    while($row = $result->fetch_assoc()) {
                        $counter++;
                        $id = $row['job_no'];
                        $row_uid = $id . "_" . $counter;

                        $status_val = $row['job_status'] ?? 'Pending';
                        $cat_val = $row['issue_category'] ?? 'Hardware';
                        $est_cost = $row['estimated_cost'] ?? '0.00';
                        $adv_paid = $row['advance_paid'] ?? '0.00';

                        $status_class = ($status_val == 'Approved') ? 'status-approved' : 'status-pending';
                ?>
                    <tr id="row-<?= $row_uid ?>">
                        <td data-label="Job No"><strong>#<?= $id ?></strong></td>

                        <td data-label="Customer">
                            <input type="text" id="name-<?= $row_uid ?>" class="table-input"
                            value="<?= htmlspecialchars($row['customer_name']); ?>" readonly>
                        </td>

                        <td data-label="Issue">
                            <input type="text" id="issue-<?= $row_uid ?>" class="table-input"
                            value="<?= htmlspecialchars($row['issue_name']); ?>" readonly>
                        </td>

                        <td data-label="Estimate">
                            <input type="number" id="est-<?= $row_uid ?>" class="est-input"
                            value="<?= $est_cost ?>"
                            onchange="saveToDB('<?= $row_uid ?>', '<?= $id ?>')">
                        </td>

                        <td data-label="Advance">
                            <span class="advance-badge">Rs. <?= number_format($adv_paid, 2); ?></span>
                        </td>

                        <td data-label="Category">
                            <select id="cat-<?= $row_uid ?>" class="status-select"
                            onchange="saveToDB('<?= $row_uid ?>', '<?= $id ?>')">
                                <option value="Hardware" <?= $cat_val == 'Hardware' ? 'selected' : ''; ?>> Hardware</option>
                                <option value="Software" <?= $cat_val == 'Software' ? 'selected' : ''; ?>> Software</option>
                            </select>
                        </td>

                        <td data-label="Phone">
                            <input type="text" id="phone-<?= $row_uid ?>" class="table-input"
                            value="<?= htmlspecialchars($row['phone_number']); ?>" readonly>
                        </td>

                        <td data-label="Status">
                            <div class="status-wrapper">
                                <select id="stat-<?= $row_uid ?>" class="status-select <?= $status_class ?>"
                                onchange="updateStatusOnly('<?= $row_uid ?>', '<?= $id ?>')">
                                    <option value="Pending" <?= $status_val == 'Pending' ? 'selected' : ''; ?>> Pending</option>
                                    <option value="Approved" <?= $status_val == 'Approved' ? 'selected' : ''; ?>> Approved</option>
                                </select>
                                <span id="msg-<?= $row_uid ?>" class="save-msg"> Saved</span>
                            </div>
                        </td>

                        <td data-label="Action">
                            <div class="action-buttons">
                                <button class="btn-sms" onclick="sendEstimateSMS('<?= $row_uid ?>', '<?= $id ?>')"> Send Estimate</button>
                                <button id="btn-edit-<?= $row_uid ?>" class="btn-edit" onclick="toggleEdit('<?= $row_uid ?>', '<?= $id ?>')">Edit</button>
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

function sendEstimateSMS(row_uid, job_no) {
    let parts = prompt("Inclusion of items and prices required for repair:", "Service Charge Only");
    if (parts === null) return;

    const data = {
        action: 'send_estimate_sms',
        job_no: job_no,
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
                alert("Estimate SMS එක සාර්ථකව යවන ලදී!");
            } else {
                alert("Error sending SMS: " + this.responseText);
            }
        }
    };

    xhr.send("id=" + encodeURIComponent(job_no) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}

function updateStatusOnly(row_uid, job_no) {
    const statSelect = document.getElementById('stat-' + row_uid);
    let advanceAmount = 0;

    if (statSelect.value === 'Approved') {
        if (confirm("Did the customer confirm this? Once approved, it will be removed from this list..")) {
            let userInput = prompt("Enter the advance amount paid by the customer:", "0");

            if (userInput === null) {
                statSelect.value = 'Pending';
                return;
            }

            advanceAmount = parseFloat(userInput) || 0;
            saveToDB(row_uid, job_no, advanceAmount);
        } else {
            statSelect.value = 'Pending';
        }
    } else {
        saveToDB(row_uid, job_no, 0);
    }
}

function saveToDB(row_uid, job_no, advance = 0, callback = null) {
    const data = {
        job_no: job_no,
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

    xhr.send("id=" + encodeURIComponent(job_no) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}

function toggleEdit(row_uid, job_no) {
    const fields = ['name', 'issue'];
    const btn = document.getElementById('btn-edit-' + row_uid);
    const isReadOnly = document.getElementById('name-' + row_uid).readOnly;

    if (isReadOnly) {
        fields.forEach(f => {
            let el = document.getElementById(f + '-' + row_uid);
            el.readOnly = false;
            el.classList.add('editing-active');
        });

        btn.innerHTML = '<i class="ph-bold ph-floppy-disk"></i> Save';
    } else {
        saveToDB(row_uid, job_no, 0, () => {
            fields.forEach(f => {
                let el = document.getElementById(f + '-' + row_uid);
                el.readOnly = true;
                el.classList.remove('editing-active');
            });

            btn.innerHTML = '<i class="ph-bold ph-pencil-simple"></i> Edit';
        });
    }
}
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>

</body>
</html>