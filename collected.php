<?php 
include 'db_config.php';
include 'navbar.php'; 

// Approved ඒව මේ list එකෙන් අයින් කරන්න අවශ්‍ය නම් මෙතනට AND j.job_status != 'Approved' දාන්න පුළුවන්
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs Management | Smart Repair</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ඔයාගේ පරණ CSS ඔක්කොම මෙතන තියෙනවා */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2ecc71; --primary-hover: #27ae60; --primary-dark: #229954;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --secondary: #64748b; --bg-main: #f8fafc; --card-bg: #ffffff;
            --text-main: #1a202c; --text-dark: #0f172a; --text-muted: #64748b;
            --border: #e2e8f0; --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%); padding: 140px 20px 40px 20px; color: var(--text-main); transition: background 0.3s ease, color 0.3s ease; }
        .page-container { max-width: 1200px; margin: 0 auto; }
        .page-header { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 36px 40px; border-radius: 20px; margin-bottom: 32px; box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4); color: white; text-align: center; }
        .page-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 12px; }
        .container { background: var(--card-bg); padding: 36px; border-radius: 20px; box-shadow: var(--shadow-lg); border: 1px solid var(--border); animation: fadeIn 0.5s ease-out; transition: all 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid var(--border); flex-wrap: wrap; gap: 20px; }
        .search-box { display: flex; gap: 12px; align-items: center; }
        .search-input { padding: 12px 20px; border: 2px solid var(--border); border-radius: 12px; width: 320px; outline: none; background: #f8fafc; transition: all 0.3s ease; }
        .table-container { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1100px; }
        th { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); text-align: left; padding: 16px 18px; font-size: 13px; color: white; font-weight: 800; text-transform: uppercase; }
        td { padding: 16px 18px; font-size: 14px; border-bottom: 1px solid #f0f2f5; transition: all 0.3s ease; }
        .table-input { width: 100%; border: 2px solid transparent; background: transparent; padding: 8px; border-radius: 8px; font-weight: 600; color: inherit; }
        .editing-active { background: white !important; border: 2px solid var(--primary) !important; color: #1a202c !important; }
        .status-select { padding: 10px 14px; border-radius: 10px; border: 2px solid var(--border); font-weight: 700; cursor: pointer; background: white; transition: all 0.3s ease; }
        .status-pending { background: #e0e7ff; color: #3730a3; }
        .status-approved { background: #dcfce7; color: #166534; }
        .btn-edit { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; white-space: nowrap; }
        .btn-sms { background: var(--warning); color: #000; border: none; padding: 10px 15px; border-radius: 10px; cursor: pointer; font-weight: 700; margin-bottom: 5px; width: 100%; }
        .save-msg { font-size: 12px; color: var(--success); display: none; font-weight: 800; margin-left: 10px; }
        .filter-buttons { display: flex; gap: 8px; margin-bottom: 15px; }
        .filter-btn { padding: 8px 15px; border-radius: 8px; border: 1px solid var(--border); background: white; cursor: pointer; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .filter-btn:hover { background: #f0f0f0; }
        .filter-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .est-input { width: 100px; padding: 5px; border: 1px solid var(--border); border-radius: 5px; font-weight: bold; color: var(--primary-dark); background: white; }

        body.dark-mode {
            background: linear-gradient(135deg, #020617 0%, #0f172a 100%) !important;
            color: #e2e8f0 !important;
        }
        body.dark-mode .container {
            background: rgba(30, 41, 59, 0.6) !important;
            backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
        }
        body.dark-mode .search-input, body.dark-mode .status-select, body.dark-mode .est-input, body.dark-mode .filter-btn {
            background: rgba(15, 23, 42, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }
    </style>
</head>
<body id="jobsBody">

<div class="page-container">
    <div class="page-header">
        <h1>🛠️ Jobs Management</h1>
        <p>Manage non-warranty jobs and approvals</p>
    </div>

    <div class="container">
        <div class="filter-buttons">
            <button class="filter-btn <?php echo !isset($_GET['range']) ? 'active' : ''; ?>" onclick="window.location.href='?'">All</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == 'today' ? 'active' : ''; ?>" onclick="window.location.href='?range=today'">Today</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == '2weeks' ? 'active' : ''; ?>" onclick="window.location.href='?range=2weeks'">Last 2 Weeks</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == 'month' ? 'active' : ''; ?>" onclick="window.location.href='?range=month'">This Month</button>
        </div>

        <div class="header-section">
            <h2>📋 All Non-Warranty Jobs</h2>
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search Job, Issue, Category..." onkeyup="filterTable()">
                <button class="btn-edit" style="background: var(--danger);" onclick="window.location.href='?'">✕ Clear</button>
            </div>
        </div>
        
        <div class="table-container">
            <table id="jobsTable">
                <thead>
                    <tr>
                        <th>Job No</th>
                        <th>Customer</th>
                        <th>Issue</th>
                        <th>Estimate (Rs.)</th> 
                        <th>Diagnosis Category</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT j.job_no, j.job_status, j.estimated_cost, c.customer_name, c.email, c.phone_number, jd.issue_name, jd.warranty_status, jd.issue_category 
                            FROM job j
                            LEFT JOIN customer c ON j.phone_number = c.phone_number
                            LEFT JOIN job_device jd ON j.job_no = jd.job_no
                            $filter_query 
                            ORDER BY j.job_no DESC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $id = $row['job_no'];
                            $status_val = $row['job_status'] ?? 'Pending';
                            $cat_val = $row['issue_category'] ?? 'Hardware';
                            $est_cost = $row['estimated_cost'] ?? '0.00';
                            $status_class = ($status_val == 'Approved') ? 'status-approved' : 'status-pending';
                    ?>
                    <tr id="row-<?php echo $id; ?>">
                        <td><strong>#<?php echo $id; ?></strong></td>
                        <td><input type="text" id="name-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['customer_name']); ?>" readonly></td>
                        <td><input type="text" id="issue-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['issue_name']); ?>" readonly></td>
                        <td><input type="number" id="est-<?php echo $id; ?>" class="est-input" value="<?php echo $est_cost; ?>" onchange="saveToDB('<?php echo $id; ?>')"></td>
                        <td>
                            <select id="cat-<?php echo $id; ?>" class="status-select" onchange="saveToDB('<?php echo $id; ?>')">
                                <option value="Hardware" <?php if($cat_val == 'Hardware') echo 'selected'; ?>>⚙️ Hardware</option>
                                <option value="Software" <?php if($cat_val == 'Software') echo 'selected'; ?>>💻 Software</option>
                            </select>
                        </td>
                        <td><input type="text" id="phone-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['phone_number']); ?>" readonly></td>
                        <td>
                            <select id="stat-<?php echo $id; ?>" class="status-select <?php echo $status_class; ?>" onchange="updateStatusOnly('<?php echo $id; ?>')">
                                <option value="Pending" <?php if($status_val=='Pending') echo 'selected'; ?>>⏳ Pending</option>
                                <option value="Approved" <?php if($status_val=='Approved') echo 'selected'; ?>>✅ Approved</option>
                            </select>
                            <span id="msg-<?php echo $id; ?>" class="save-msg">✓</span>
                        </td>
                        <td>
                            <button class="btn-sms" onclick="sendEstimateSMS('<?php echo $id; ?>')">📩 Send Estimate</button><br>
                            <button id="btn-edit-<?php echo $id; ?>" class="btn-edit" onclick="toggleEdit('<?php echo $id; ?>')">✏️ Edit</button>
                            <input type="hidden" id="email-<?php echo $id; ?>" value="<?php echo $row['email']; ?>">
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center; padding:50px;'>No records found</td></tr>";
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
setInterval(syncTheme, 500);

// SMS එක යැවීමේ Function එක
function sendEstimateSMS(id) {
    let est = document.getElementById('est-' + id).value;
    let parts = prompt("දාන්න බලාපොරොත්තු වන අමතර කොටස් (Parts) ඇතුළත් කරන්න:");
    
    if (parts === null || parts === "") {
        alert("කරුණාකර Parts විස්තර ඇතුළත් කරන්න.");
        return;
    }

    const data = {
        job_no: id,
        action: 'send_estimate_sms',
        parts_details: parts,
        estimated_cost: est,
        phone_number: document.getElementById('phone-' + id).value,
        customer_name: document.getElementById('name-' + id).value,
        issue_name: document.getElementById('issue-' + id).value
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_engine.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.trim().includes("success")) {
                alert("Estimate SMS එක සාර්ථකව යවන ලදී!");
            } else {
                alert("SMS එක යැවීමේදී ගැටළුවක් ඇති විය: " + this.responseText);
            }
        }
    };
    xhr.send("id=" + id + "&data=" + encodeURIComponent(JSON.stringify(data)));
}

function updateStatusOnly(id) {
    const statSelect = document.getElementById('stat-' + id);
    const currentStatus = statSelect.value;
    
    if (currentStatus === 'Approved') {
        let confirmAction = confirm("පාරිභෝගිකයා මෙම ඇස්තමේන්තුවට කැමති බව ස්ථිරද? එසේනම් මෙය Approved කර Order පිටුවට යවනු ඇත.");
        if (!confirmAction) {
            statSelect.value = 'Pending';
            return;
        }
    }
    statSelect.className = 'status-select ' + (statSelect.value === 'Approved' ? 'status-approved' : 'status-pending');
    saveToDB(id);
}

function saveToDB(id, callback = null) {
    const data = {
        job_no: id,
        customer_name: document.getElementById('name-' + id).value,
        email: document.getElementById('email-' + id).value,
        issue_name: document.getElementById('issue-' + id).value,
        phone_number: document.getElementById('phone-' + id).value,
        job_status: document.getElementById('stat-' + id).value,
        issue_category: document.getElementById('cat-' + id).value,
        estimated_cost: document.getElementById('est-' + id).value 
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_engine.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.trim().includes("success")) {
                let msg = document.getElementById('msg-' + id);
                msg.style.display = 'inline';
                setTimeout(() => { 
                    msg.style.display = 'none'; 
                    // Approved නම් refresh කරලා list එකෙන් අයින් කරන්න
                    if (data.job_status === 'Approved') location.reload();
                }, 1000);
                if (callback) callback();
            } else { alert("Error: " + this.responseText); }
        }
    };
    xhr.send("id=" + encodeURIComponent(id) + "&data=" + encodeURIComponent(JSON.stringify(data)));
}

function toggleEdit(id) {
    const fields = ['name', 'issue']; 
    const btn = document.getElementById('btn-edit-' + id);
    const isReadOnly = document.getElementById('name-' + id).readOnly;

    if (isReadOnly) {
        fields.forEach(f => {
            let el = document.getElementById(f + '-' + id);
            el.readOnly = false;
            el.classList.add('editing-active');
        });
        btn.innerHTML = "💾 Save";
    } else {
        saveToDB(id, () => {
            fields.forEach(f => {
                let el = document.getElementById(f + '-' + id);
                el.readOnly = true;
                el.classList.remove('editing-active');
            });
            btn.innerHTML = "✏️ Edit";
        });
    }
}

function filterTable() {
    const filter = document.getElementById("searchInput").value.toUpperCase();
    const tr = document.getElementById("jobsTable").getElementsByTagName("tr");
    for (let i = 1; i < tr.length; i++) {
        let combinedText = tr[i].innerText.toUpperCase();
        const inputs = tr[i].getElementsByTagName('input');
        for (let input of inputs) { combinedText += " " + input.value.toUpperCase(); }
        const selects = tr[i].getElementsByTagName('select');
        for (let select of selects) {
            let selectedText = select.options[select.selectedIndex].text.toUpperCase();
            combinedText += " " + selectedText;
        }
        tr[i].style.display = combinedText.includes(filter) ? "" : "none";
    }
}
</script>
</body>
<?php include 'chatbot.php'; ?>
</html>