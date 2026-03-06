<?php 
include 'db_config.php';
include 'navbar.php'; 

// Date Range Filters
$filter_query = " WHERE jd.warranty_status = 'Warranty' ";
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
    <title>Warranty Management | Smart Repair</title>
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
        .page-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 12px; }
        .container { background: var(--card-bg); padding: 36px; border-radius: 20px; box-shadow: var(--shadow-lg); border: 1px solid var(--border); animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid var(--border); flex-wrap: wrap; gap: 20px; }
        .search-box { display: flex; gap: 12px; align-items: center; }
        .search-input { padding: 12px 20px; border: 2px solid var(--border); border-radius: 12px; width: 320px; outline: none; background: #f8fafc; }
        .filter-buttons { display: flex; gap: 8px; margin-bottom: 15px; }
        .filter-btn { padding: 8px 15px; border-radius: 8px; border: 1px solid var(--border); background: white; cursor: pointer; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .filter-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 16px 18px; text-align: left; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        td { padding: 16px 18px; border-bottom: 1px solid #f0f2f5; font-size: 14px; }
        .status-select { padding: 8px 12px; border-radius: 10px; border: 2px solid var(--border); font-weight: 700; font-size: 13px; }
        .supplier-input { padding: 10px 14px; border-radius: 10px; border: 2px solid var(--border); width: 150px; background: #f8fafc; font-weight: 600; }
        .supplier-input.editing { background: white; border-color: var(--primary); }
        .btn-edit { background: var(--primary); color: white; border: none; padding: 10px 18px; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 13px; }
        .job-badge { background: #e3f2fd; color: #1976d2; padding: 6px 12px; border-radius: 8px; font-weight: 800; }
        .save-toast { position: fixed; bottom: 30px; right: 30px; background: #1e293b; color: white; padding: 16px 28px; border-radius: 12px; display: none; z-index: 1000; box-shadow: var(--shadow-lg); }
    </style>
</head>
<body>

<div class="page-container">
    <div class="page-header">
        <h1>🛡️ Warranty Management</h1>
        <p>Track and manage warranty devices efficiently</p>
    </div>

    <div class="container">
        <div class="filter-buttons">
            <button class="filter-btn <?php echo !isset($_GET['range']) ? 'active' : ''; ?>" onclick="window.location.href='?'">All</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == 'today' ? 'active' : ''; ?>" onclick="window.location.href='?range=today'">Today</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == '2weeks' ? 'active' : ''; ?>" onclick="window.location.href='?range=2weeks'">Last 2 Weeks</button>
            <button class="filter-btn <?php echo ($_GET['range'] ?? '') == 'month' ? 'active' : ''; ?>" onclick="window.location.href='?range=month'">This Month</button>
        </div>

        <div class="header-flex">
            <h2>📋 Warranty Devices</h2>
            <div class="search-box">
                <input type="text" id="warrantySearch" class="search-input" placeholder="🔍 Search Job, Name, Phone..." onkeyup="filterWarranty()">
                <button class="btn-edit" style="background:var(--danger);" onclick="window.location.href='?'">✕</button>
            </div>
        </div>

        <div class="table-container">
            <table id="warrantyTable">
                <thead>
                    <tr>
                        <th>Job No</th>
                        <th>Device & Category</th>
                        <th>Customer / Phone</th>
                        <th>Status</th>
                        <th>Supplier Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT jd.*, j.job_date, c.customer_name, c.phone_number 
                              FROM job_device jd
                              JOIN job j ON jd.job_no = j.job_no
                              JOIN customer c ON j.phone_number = c.phone_number
                              $filter_query 
                              ORDER BY j.job_date DESC";
                    $result = mysqli_query($conn, $query);

                    while($row = mysqli_fetch_assoc($result)): 
                        $id = $row['job_device_id'];
                        $cat_val = $row['issue_category'] ?? 'Hardware';
                    ?>
                    <tr>
                        <td><span class="job-badge">#<?= $row['job_no'] ?></span></td>
                        <td>
                            <strong>📱 <?= htmlspecialchars($row['device_name']) ?></strong><br>
                            <small><?= htmlspecialchars($row['issue_name']) ?></small><br>
                            <select id="cat-<?= $id ?>" class="status-select" style="margin-top:5px; height:30px; font-size:11px;" onchange="saveAll(<?= $id ?>)">
                                <option value="Hardware" <?= $cat_val=='Hardware'?'selected':'' ?>>⚙️ Hardware</option>
                                <option value="Software" <?= $cat_val=='Software'?'selected':'' ?>>💻 Software</option>
                            </select>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['customer_name']) ?></strong><br>
                            <span style="color:var(--text-muted); font-size:12px;"><?= htmlspecialchars($row['phone_number']) ?></span>
                        </td>
                        <td>
                            <select class="status-select" id="stat-<?= $id ?>" onchange="saveAll(<?= $id ?>)">
                                <option value="Pending" <?= $row['device_status']=='Pending'?'selected':'' ?>>⏳ Pending</option>
                                <option value="Sent to Warranty" <?= $row['device_status']=='Sent to Warranty'?'selected':'' ?>>📦 Sent</option>
                                <option value="Completed" <?= $row['device_status']=='Completed'?'selected':'' ?>>✅ Completed</option>
                                <option value="Rejected" <?= $row['device_status']=='Rejected'?'selected':'' ?>>❌ Rejected</option>
                            </select>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <input type="text" id="sup-<?= $id ?>" class="supplier-input" value="<?= htmlspecialchars($row['supplier_name'] ?? '') ?>" readonly>
                                <button id="btn-<?= $id ?>" class="btn-edit" onclick="toggleSupplier(<?= $id ?>)">✏️</button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="saveMsg" class="save-toast">✅ Saved!</div>

<script>
function filterWarranty() {
    let input = document.getElementById("warrantySearch").value.toUpperCase();
    let tr = document.getElementById("warrantyTable").getElementsByTagName("tr");
    for (let i = 1; i < tr.length; i++) {
        let text = tr[i].innerText.toUpperCase();
        tr[i].style.display = text.includes(input) ? "" : "none";
    }
}

function saveAll(id) {
    let status = document.getElementById('stat-' + id).value;
    let supplier = document.getElementById('sup-' + id).value;
    let category = document.getElementById('cat-' + id).value;
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "update_warranty_list.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            showToast("Updated!");
        }
    };
    // මෙහි category එකත් යවනවා update_warranty_list.php එකට
    xhr.send("id=" + id + "&supplier=" + encodeURIComponent(supplier) + "&status=" + encodeURIComponent(status) + "&category=" + encodeURIComponent(category));
}

function toggleSupplier(id) {
    let input = document.getElementById('sup-' + id);
    let btn = document.getElementById('btn-' + id);
    if (input.readOnly) {
        input.readOnly = false;
        input.classList.add('editing');
        btn.innerHTML = "💾";
        input.focus();
    } else {
        saveAll(id);
        input.readOnly = true;
        input.classList.remove('editing');
        btn.innerHTML = "✏️";
    }
}

function showToast(text) {
    let toast = document.getElementById('saveMsg');
    toast.innerText = "✅ " + text;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2000);
}
</script>
</body>
<?php include 'chatbot.php'; ?>
</html>