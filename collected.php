<?php 
include 'db_config.php';
include 'navbar.php'; 

$filter_query = " WHERE jd.warranty_status = 'No Warranty' AND j.job_status != 'Approved' ";
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
    <title>Jobs Management | Smart Repair</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2ecc71; --primary-hover: #27ae60; --primary-dark: #229954;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --secondary: #64748b; --bg-main: #f8fafc; --card-bg: #ffffff;
            --text-main: #1a202c; --text-dark: #0f172a; --text-muted: #64748b;
            --border: #e2e8f0; --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%); padding: 140px 20px 40px 20px; color: var(--text-main); }
        .page-container { max-width: 1200px; margin: 0 auto; }
        .page-header { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 36px 40px; border-radius: 20px; margin-bottom: 32px; box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4); color: white; text-align: center; }
        .container { background: var(--card-bg); padding: 36px; border-radius: 20px; box-shadow: var(--shadow-lg); border: 1px solid var(--border); }
        .table-container { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1100px; }
        th { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 16px 18px; color: white; font-weight: 800; text-transform: uppercase; }
        td { padding: 16px 18px; border-bottom: 1px solid #f0f2f5; }
        .table-input { width: 100%; border: 2px solid transparent; background: transparent; padding: 8px; border-radius: 8px; font-weight: 600; }
        .editing-active { background: white !important; border: 2px solid var(--primary) !important; }
        .status-select { padding: 10px 14px; border-radius: 10px; border: 2px solid var(--border); font-weight: 700; cursor: pointer; }
        .status-pending { background: #e0e7ff; color: #3730a3; }
        .status-approved { background: #dcfce7; color: #166534; }
        .btn-edit { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; }
        .btn-sms { background: var(--warning); color: #000; border: none; padding: 10px 15px; border-radius: 10px; cursor: pointer; font-weight: 700; width: 100%; margin-bottom: 5px; }
        .save-msg { font-size: 12px; color: var(--success); display: none; font-weight: 800; }
        .filter-btn { padding: 8px 15px; border-radius: 8px; border: 1px solid var(--border); background: white; cursor: pointer; font-weight: 600; }
        .filter-btn.active { background: var(--primary); color: white; }
        .est-input { width: 100px; padding: 5px; border: 1px solid var(--border); border-radius: 5px; font-weight: bold; }
        body.dark-mode { background: #020617 !important; color: #e2e8f0 !important; }
        body.dark-mode .container { background: rgba(30, 41, 59, 0.6) !important; }
    </style>
</head>
<body id="jobsBody">

<div class="page-container">
    <div class="page-header">
        <h1>🛠️ Jobs Management</h1>
        <p>Manage non-warranty jobs and approvals</p>
    </div>

    <div class="container">
        <div class="filter-buttons" style="display: flex; gap: 8px; margin-bottom: 15px;">
            <button class="filter-btn <?php echo !isset($_GET['range']) ? 'active' : ''; ?>" onclick="window.location.href='?'">All</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == 'today' ? 'active' : ''; ?>" onclick="window.location.href='?range=today'">Today</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == '2weeks' ? 'active' : ''; ?>" onclick="window.location.href='?range=2weeks'">Last 2 Weeks</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == 'month' ? 'active' : ''; ?>" onclick="window.location.href='?range=month'">This Month</button>
        </div>

        <div class="table-container">
            <table id="jobsTable">
                <thead>
                    <tr>
                        <th>Job No</th>
                        <th>Customer</th>
                        <th>Issue</th>
                        <th>Estimate (Rs.)</th> 
                        <th style="color: #ef4444;">Advance (Rs.)</th>
                        <th>Category</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // SQL එකට advance_paid එකතු කළා
                    $sql = "SELECT j.job_no, j.job_status, j.estimated_cost, j.advance_paid, c.customer_name, c.email, c.phone_number, jd.issue_name, jd.issue_category 
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
                    <tr id="row-<?php echo $row_uid; ?>">
                        <td><strong>#<?php echo $id; ?></strong></td>
                        <td><input type="text" id="name-<?php echo $row_uid; ?>" class="table-input" value="<?php echo htmlspecialchars($row['customer_name']); ?>" readonly></td>
                        <td><input type="text" id="issue-<?php echo $row_uid; ?>" class="table-input" value="<?php echo htmlspecialchars($row['issue_name']); ?>" readonly></td>
                        <td><input type="number" id="est-<?php echo $row_uid; ?>" class="est-input" value="<?php echo $est_cost; ?>" onchange="saveToDB('<?php echo $row_uid; ?>', '<?php echo $id; ?>')"></td>
                        <td><strong style="color: #ef4444;">Rs. <?php echo number_format($adv_paid, 2); ?></strong></td>
                        <td>
                            <select id="cat-<?php echo $row_uid; ?>" class="status-select" onchange="saveToDB('<?php echo $row_uid; ?>', '<?php echo $id; ?>')">
                                <option value="Hardware" <?php if($cat_val == 'Hardware') echo 'selected'; ?>>⚙️ Hardware</option>
                                <option value="Software" <?php if($cat_val == 'Software') echo 'selected'; ?>>💻 Software</option>
                            </select>
                        </td>
                        <td><input type="text" id="phone-<?php echo $row_uid; ?>" class="table-input" value="<?php echo htmlspecialchars($row['phone_number']); ?>" readonly></td>
                        <td>
                            <select id="stat-<?php echo $row_uid; ?>" class="status-select <?php echo $status_class; ?>" onchange="updateStatusOnly('<?php echo $row_uid; ?>', '<?php echo $id; ?>')">
                                <option value="Pending" <?php if($status_val=='Pending') echo 'selected'; ?>>⏳ Pending</option>
                                <option value="Approved" <?php if($status_val=='Approved') echo 'selected'; ?>>✅ Approved</option>
                            </select>
                            <span id="msg-<?php echo $row_uid; ?>" class="save-msg">✓</span>
                        </td>
                        <td>
                            <button class="btn-sms" onclick="sendEstimateSMS('<?php echo $row_uid; ?>', '<?php echo $id; ?>')">📩 Send Estimate</button>
                            <button id="btn-edit-<?php echo $row_uid; ?>" class="btn-edit" onclick="toggleEdit('<?php echo $row_uid; ?>', '<?php echo $id; ?>')">✏️ Edit</button>
                            <input type="hidden" id="email-<?php echo $row_uid; ?>" value="<?php echo $row['email']; ?>">
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
    if (isDark) { body.classList.add("dark-mode"); } else { body.classList.remove("dark-mode"); }
}
syncTheme();
setInterval(syncTheme, 1000);

// SMS යැවීම සඳහා වන Function එක
function sendEstimateSMS(row_uid, job_no) {
    let parts = prompt("අලුත්වැඩියාව සඳහා අවශ්‍ය බඩු සහ මිල (Parts & Prices) ඇතුළත් කරන්න:", "Service Charge Only");
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
                alert("SMS යැවීමේදී දෝෂයක් ඇති විය: " + this.responseText);
            }
        }
    };
    xhr.send("id=" + encodeURIComponent(job_no) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}

function updateStatusOnly(row_uid, job_no) {
    const statSelect = document.getElementById('stat-' + row_uid);
    let advanceAmount = 0;

    if (statSelect.value === 'Approved') {
        if (confirm("පාරිභෝගිකයා මෙය ස්ථිර කළාද? Approved කළ පසු මෙය මෙම ලැයිස්තුවෙන් ඉවත් වේ.")) {
            let userInput = prompt("ලැබුණු අත්තිකාරම් මුදල (Advance) ඇතුළත් කරන්න:", "0");
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
                    if (data.job_status === 'Approved') location.reload();
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
        btn.innerHTML = "💾 Save";
    } else {
        saveToDB(row_uid, job_no, 0, () => {
            fields.forEach(f => {
                let el = document.getElementById(f + '-' + row_uid);
                el.readOnly = true;
                el.classList.remove('editing-active');
            });
            btn.innerHTML = "✏️ Edit";
        });
    }
}
</script>
</body>
</html>