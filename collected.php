<?php 
include 'db_config.php';
include 'navbar.php'; 
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
            padding: 140px 20px 40px 20px;
            color: var(--text-main);
        }

        .page-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header Card */
        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 36px 40px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: white;
            text-align: center;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .page-header p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 500;
        }

        /* Container */
        .container {
            background: var(--card-bg);
            padding: 36px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            animation: fadeIn 0.5s ease-out;
        }

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

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border);
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-section h2 {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 5px solid var(--primary);
            padding-left: 16px;
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
            background: var(--danger);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-clear:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        /* Table Container */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            text-align: left;
            padding: 16px 18px;
            font-size: 13px;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
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
            font-size: 14px;
            border-bottom: 1px solid #f0f2f5;
            color: var(--text-main);
        }

        td strong {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 15px;
        }

        /* Table Input */
        .table-input {
            width: 100%;
            border: 2px solid transparent;
            background: transparent;
            padding: 8px 12px;
            outline: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .editing-active {
            background: white !important;
            border: 2px solid var(--primary) !important;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15) !important;
        }

        /* Warranty Badge */
        .warranty-badge {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #e65100;
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 12px;
            border: 2px solid #ffcc80;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        /* Status Select */
        .status-select {
            padding: 10px 14px;
            border-radius: 10px;
            border: 2px solid var(--border);
            outline: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.3s ease;
            background: white;
        }

        .status-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        .status-pending {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #3730a3;
            border-color: #a5b4fc;
        }

        .status-approved {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border-color: #86efac;
        }

        /* Save Message */
        .save-msg {
            font-size: 12px;
            color: var(--success);
            display: none;
            font-weight: 800;
            margin-left: 12px;
            animation: fadeIn 0.3s ease-out;
        }

        /* Edit Button */
        .btn-edit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-dark) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 204, 113, 0.4);
        }

        .btn-save-active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
        }

        .btn-save-active:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4) !important;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 120px 15px 30px 15px;
            }

            .page-header {
                padding: 24px 28px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .container {
                padding: 24px;
            }

            .header-section {
                flex-direction: column;
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
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="page-header">
        <h1>🛠️ Jobs Management</h1>
        <p>Manage non-warranty jobs and approvals</p>
    </div>
</div>

<div class="container">
    <div class="header-section">
        <h2>📋 All Non-Warranty Jobs</h2>
        
        <div class="search-box">
            <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search Job No, Issue, or Phone..." onkeyup="filterTable()">
            <button class="btn-clear" onclick="clearSearch()">✕ Clear</button>
        </div>
    </div>
    
    <div class="table-container">
        <table id="jobsTable">
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
                        WHERE jd.warranty_status = 'No Warranty' 
                        ORDER BY j.job_no DESC";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $id = $row['job_no'];
                        $status_val = $row['job_status'] ?? 'Pending';
                        $status_class = ($status_val == 'Approved') ? 'status-approved' : 'status-pending';
                ?>
                <tr id="row-<?php echo $id; ?>">
                    <td>
                        <span class="job-badge">#<?php echo $id; ?></span>
                    </td>
                    <td>
                        <input type="text" id="name-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['customer_name']); ?>" readonly>
                    </td>
                    <td>
                        <input type="text" id="issue-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['issue_name']); ?>" readonly>
                    </td>
                    <td>
                        <input type="text" id="phone-<?php echo $id; ?>" class="table-input" value="<?php echo htmlspecialchars($row['phone_number']); ?>" readonly>
                    </td>
                    
                    <input type="hidden" id="email-<?php echo $id; ?>" value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>">

                    <td>
                        <span class="warranty-badge">⚡ <?php echo htmlspecialchars($row['warranty_status']); ?></span>
                    </td>
                    <td>
                        <select id="stat-<?php echo $id; ?>" class="status-select <?php echo $status_class; ?>" onchange="updateStatusOnly('<?php echo $id; ?>')">
                            <option value="Pending" <?php if($status_val=='Pending') echo 'selected'; ?>>⏳ Pending</option>
                            <option value="not-Approved" <?php if($status_val=='not-Approved') echo 'selected'; ?>>❌ Not Approved</option>
                            <option value="Approved" <?php if($status_val=='Approved') echo 'selected'; ?>>✅ Approved</option>
                        </select>
                        <span id="msg-<?php echo $id; ?>" class="save-msg">✓ Saved</span>
                    </td>
                    <td>
                        <button id="btn-edit-<?php echo $id; ?>" class="btn-edit" onclick="toggleEdit('<?php echo $id; ?>')">✏️ Edit</button>
                    </td>
                </tr>
                <?php 
                    }
                } else { 
                    echo "<tr><td colspan='7' class='empty-state'><div class='empty-state-icon'>📋</div><strong>No jobs found.</strong></td></tr>"; 
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Search Functionality
function filterTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toUpperCase();
    const table = document.getElementById("jobsTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let showRow = false;
        const jobNoCol = tr[i].getElementsByTagName("td")[0];
        const issueCol = tr[i].getElementsByTagName("td")[2];
        const phoneCol = tr[i].getElementsByTagName("td")[3];

        if (jobNoCol || issueCol || phoneCol) {
            const jobText = jobNoCol.textContent || jobNoCol.innerText;
            const issueInput = issueCol.getElementsByTagName("input")[0].value;
            const phoneInput = phoneCol.getElementsByTagName("input")[0].value;

            if (jobText.toUpperCase().indexOf(filter) > -1 || 
                issueInput.toUpperCase().indexOf(filter) > -1 || 
                phoneInput.toUpperCase().indexOf(filter) > -1) {
                showRow = true;
            }
        }
        tr[i].style.display = showRow ? "" : "none";
    }
}

// Clear Search
function clearSearch() {
    document.getElementById("searchInput").value = "";
    filterTable();
}

// Edit button toggle
function toggleEdit(id) {
    const fields = ['name', 'issue', 'phone'];
    const btn = document.getElementById('btn-edit-' + id);
    const isReadOnly = document.getElementById('name-' + id).readOnly;

    if (isReadOnly) {
        fields.forEach(f => {
            let el = document.getElementById(f + '-' + id);
            el.readOnly = false;
            el.classList.add('editing-active');
        });
        btn.innerHTML = "💾 Save";
        btn.classList.add('btn-save-active');
    } else {
        saveToDB(id, () => {
            fields.forEach(f => {
                let el = document.getElementById(f + '-' + id);
                el.readOnly = true;
                el.classList.remove('editing-active');
            });
            btn.innerHTML = "✏️ Edit";
            btn.classList.remove('btn-save-active');
        });
    }
}

function updateStatusOnly(id) {
    const statSelect = document.getElementById('stat-' + id);
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