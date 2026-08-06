<?php 
include 'db_config.php';
include 'navbar.php';

// --- 1. Auto Status Update (Destroyed) ---
mysqli_query($conn, "UPDATE job_device SET device_status = 'Destroyed' 
                     WHERE destroy_notice_sent_date IS NOT NULL 
                     AND DATEDIFF(NOW(), destroy_notice_sent_date) >= 7 
                     AND device_status != 'Destroyed'");

// --- 2. Auto SMS Logic (Ready to collect) ---
$auto_sms_query = "SELECT jd.job_device_id, j.phone_number, c.customer_name, jd.device_name, j.job_no 
                   FROM job_device jd
                   INNER JOIN job j ON jd.job_no = j.job_no
                   INNER JOIN customer c ON j.phone_number = c.phone_number
                   WHERE jd.device_status = 'Completed' 
                   AND DATEDIFF(NOW(), jd.completed_date) <= 90
                   AND (jd.last_sms_sent_date IS NULL OR DATEDIFF(NOW(), jd.last_sms_sent_date) >= 2)";

$auto_res = mysqli_query($conn, $auto_sms_query);

while($auto_row = mysqli_fetch_assoc($auto_res)) {
    $j_id = $auto_row['job_device_id'];
    $phone = "94" . ltrim(ltrim($auto_row['phone_number'], '94'), '0');
    $customer = $auto_row['customer_name'];
    $device = $auto_row['device_name'];
    $job_no = $auto_row['job_no'];

    $msg = "Hi $customer, your $device (Job #$job_no) is ready at Multi9. Please collect it soon.";
    $safe_msg = mysqli_real_escape_string($conn, $msg);
    
    $history_sql = "INSERT INTO sms_history (job_device_id, phone_number, message, status) 
                    VALUES ('$j_id', '$phone', '$safe_msg', 'Sent (Auto)')";
    
    if(mysqli_query($conn, $history_sql)) {
        mysqli_query($conn, "UPDATE job_device SET last_sms_sent_date = CURDATE() WHERE job_device_id = $j_id");
    }
}

// --- 3. Data Fetching and Filtering ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$status_flow = ['Pending', 'In Progress', 'Completed', 'Returned'];

$sql = "SELECT j.job_no, j.job_date, j.advance_paid, t.name as technician_name, c.customer_name, j.phone_number, 
                jd.job_device_id, jd.device_name, jd.issue_name, jd.solution,
                CASE WHEN inv.payment_status = 'Paid' THEN 'Returned' ELSE jd.device_status END AS device_status,
                jd.completed_date, jd.destroy_notice_sent_date, jd.rent_warning_sent,
                inv.payment_status
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        LEFT JOIN invoice inv ON jd.job_no = inv.job_no 
        WHERE j.job_status = 'Approved' 
        AND jd.device_status != 'Destroyed'";

if ($filter_status != '') { 
    if($filter_status == 'Returned') { $sql .= " AND inv.payment_status = 'Paid'"; } 
    else { $sql .= " AND jd.device_status = '$filter_status' AND (inv.payment_status != 'Paid' OR inv.payment_status IS NULL)"; }
}

if ($search != '') { $sql .= " AND (j.job_no LIKE '%$search%' OR j.phone_number LIKE '%$search%' OR jd.device_name LIKE '%$search%' OR c.customer_name LIKE '%$search%')"; }

$sql .= " ORDER BY jd.job_device_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - Multi9</title>
    <link rel="stylesheet" href="CSS/global.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .page-container { max-width: 1400px; margin: 0 auto; margin-top: 25px; }
        .page-header { background: linear-gradient(135deg, var(--primary-green), var(--accent-green)); padding: 30px; border-radius: 20px; margin-bottom: 30px; color: white; text-align: center; box-shadow: 0 10px 20px rgba(15, 118, 110, 0.2); }
        .search-container { display: flex; justify-content: center; margin-bottom: 25px; align-items: center; gap: 15px; }
        .search-box { display: flex; background: var(--light-surface); padding: 5px; border-radius: 12px; box-shadow: var(--card-shadow); width: 100%; max-width: 500px; border: 1px solid var(--border-light); }
        .search-box input { flex: 1; border: none; padding: 10px 15px; outline: none; border-radius: 8px; background: transparent; color: var(--text-dark); }
        .search-box button { background: var(--primary-green); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); display: flex; align-items: center; gap: 5px; }
        .search-box button:hover { background: var(--primary-green-dark); }
        
        .filter-container { display: flex; justify-content: center; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .filter-tag { padding: 12px 22px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 13px; color: white; transition: var(--transition); border: 2px solid transparent; }
        .active-tag { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-color: rgba(255,255,255,0.8); }
        
        .table-container { background: var(--light-surface); border-radius: 15px; box-shadow: var(--card-shadow); overflow-x: auto; border: 1px solid var(--border-light); }
        .status-table { width: 100%; border-collapse: collapse; min-width: 1100px; }
        .status-table th { background: var(--light-bg); color: var(--text-muted); padding: 15px; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid var(--border-light); }
        .status-table td { padding: 15px; border-bottom: 1px solid var(--border-light); text-align: center; color: var(--text-dark); transition: var(--transition); }
        
        .inline-input { width: 100%; border: 1px solid transparent; background: var(--light-bg); padding: 8px; border-radius: 8px; text-align: center; font-size: 13px; transition: var(--transition); color: var(--text-dark); }
        .inline-input.editing { border-color: var(--primary-green); background: var(--light-surface); outline: none; box-shadow: 0 0 5px rgba(26, 163, 83, 0.2); }
        .badge { padding: 6px 14px; border-radius: 50px; font-size: 11px; font-weight: 800; }
        .bill-btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 800; }
        .paid-badge { background: #ecfdf5; color: #059669; border: 1px solid #10b981; padding: 8px 15px; border-radius: 8px; font-weight: 800; }
        .sms-btn { background: #3b82f6; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 10px; font-weight: 900; margin-top: 5px; width: 100%; }
        #smsModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; }
        .modal-content { background:white; width:90%; max-width:600px; margin:80px auto; padding:20px; border-radius:15px; position:relative; box-shadow:0 5px 25px rgba(0,0,0,0.2); }
        .close-modal { position:absolute; right:20px; top:15px; font-size:24px; cursor:pointer; font-weight:bold; }
        .advance-amount { color: var(--danger); font-weight: 800; font-size: 14px; }
    </style>
</head>
<body>

<div id="smsModal">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById('smsModal').style.display='none'">&times;</span>
        <h2 style="margin-bottom:15px;">SMS History</h2>
        <div id="historyBody" style="max-height:400px; overflow-y:auto;">Loading...</div>
    </div>
</div>

<div class="page-container">
    <div class="page-header">
        <h1> Job Management System</h1>
        <p>Track repair status, manage billing, and automate notifications</p>
    </div>

    <div class="search-container">
        <form action="" method="GET" class="search-box">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <input type="text" name="search" placeholder="Search by Job No, Phone, Name..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="ph ph-magnifying-glass"></i> Search</button>
        </form>
        <a href="returned_jobs.php" style="background: var(--dark-surface); color: white; padding: 12px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 5px; border: 1px solid var(--border-light);"><i class="ph ph-clock-counter-clockwise"></i> History</a>
    </div>

    <div class="filter-container">
        <a href="?search=<?= $search ?>" class="filter-tag <?= $filter_status == '' ? 'active-tag' : '' ?>" style="background: var(--secondary)"> All Jobs</a>
        <?php foreach (['Pending' => '--warning', 'In Progress' => '--blue', 'Completed' => '--success', 'Returned' => '#10b981'] as $st => $col): ?>
            <a href="?status=<?= $st ?>&search=<?= $search ?>" class="filter-tag <?= $filter_status == $st ? 'active-tag' : '' ?>" style="background: var(<?= $col ?>, <?= $col ?>)"><?= $st ?></a>
        <?php endforeach; ?>
    </div>

    <div class="table-container">
        <table class="status-table">
            <thead>
                <tr>
                    <th>JOB NO</th>
                    <th>CUSTOMER DETAILS</th>
                    <th>DEVICE</th>
                    <th>ISSUE</th>
                    <th style="color: var(--danger);">ADVANCE (Rs.)</th>
                    <th>SOLUTION</th> 
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $id = $row['job_device_id']; // මෙන්න මේ Unique ID එක භාවිතා කරනවා
                        $current_status = $row['device_status'];
                        $is_paid = ($row['payment_status'] == 'Paid');
                        $current_idx = array_search($current_status, $status_flow);

                        $delay_fee = 0;
                        $days_passed = 0;
                        if($current_status == 'Completed' && !empty($row['completed_date'])) {
                            $completed_time = strtotime($row['completed_date']);
                            $days_passed = floor((time() - $completed_time) / 86400);
                            if($days_passed > 90) { 
                                $delay_fee = ceil(($days_passed - 90) / 30) * 100; 
                            }
                        }
                    ?>
                    <tr id="row-<?= $id ?>">
                        <td><span class="badge" style="background:#f1f5f9; color:#475569;">#<?= $row['job_no'] ?></span></td>
                        <td style="text-align: left;">
                            <b style="font-size:14px;"><?= htmlspecialchars($row['customer_name']) ?></b><br>
                            <small style="color:var(--text-muted);"><?= $row['phone_number'] ?></small>
                        </td>
                        
                        <td><input type="text" id="dev-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['device_name']) ?>" readonly></td>
                        <td><input type="text" id="iss-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['issue_name']) ?>" readonly></td>
                        
                        <td class="advance-amount">
                            Rs. <?= number_format($row['advance_paid'], 2) ?>
                        </td>

                        <td><textarea id="sol-<?= $id ?>" class="inline-input" readonly style="min-height:45px;"><?= htmlspecialchars($row['solution'] ?? '') ?></textarea></td>
                        
                        <td style="vertical-align: top;">
                            <select id="stat-<?= $id ?>" onchange="updateStatusAndSMS(<?= $id ?>)" <?= $current_status == 'Returned' ? 'disabled' : '' ?> 
                                    style="padding:8px; border-radius:8px; border:1px solid var(--border); font-weight:600; width: 100%;">
                                <?php foreach ($status_flow as $idx => $opt): ?>
                                    <?php if ($idx >= $current_idx): ?>
                                        <option value="<?= $opt ?>" <?= $current_status == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            
                            <?php if($delay_fee > 0): ?>
                                <div style="margin-top: 8px; padding: 6px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px;">
                                    <div style="color: #dc2626; font-weight: 900; font-size: 11px; margin-bottom: 4px;">
                                         RENT: Rs. <?= $delay_fee ?>
                                    </div>
                                    <button class="sms-btn" onclick="sendManualSMS(<?= $id ?>)" style="background: #ef4444; border-radius: 5px; padding: 5px; font-size: 10px; display:flex; align-items:center; justify-content:center; gap:3px;"><i class="ph ph-paper-plane-tilt"></i> RENT SMS</button>
                                </div>
                            <?php elseif($current_status == 'Completed'): ?>
                                <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">Collection: <?= $days_passed ?> days</div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <button onclick="viewSMSHistory(<?= $id ?>)" title="View SMS History" style="background:var(--secondary); color:white; border:none; padding:10px; border-radius:8px; cursor:pointer;"><i class="ph ph-chat-text"></i></button>
                                
                                <?php if($current_status != 'Completed' && $current_status != 'Returned'): ?>
                                    <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" style="background: var(--blue); color:white; border:none; padding:10px; border-radius:8px; cursor:pointer;"><i class="ph ph-pencil-simple"></i></button>
                                <?php else: ?>
                                    <span style="opacity:0.3; font-size:16px;"></span>
                                <?php endif; ?>

                                <?php if($current_status == 'Completed' || $current_status == 'Returned'): ?>
                                    <?php if(!$is_paid): ?>
                                        <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&fee=<?= $delay_fee ?>" class="bill-btn"><i class="ph ph-receipt"></i> BILL</a>
                                    <?php else: ?>
                                        <span class="paid-badge"><i class="ph ph-check-circle"></i> PAID</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="padding: 60px; color: var(--text-muted);">No active jobs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewSMSHistory(id) {
    document.getElementById('smsModal').style.display = 'block';
    document.getElementById('historyBody').innerHTML = "Loading history...";
    fetch('get_sms_history.php?id=' + id)
        .then(res => res.text())
        .then(data => { document.getElementById('historyBody').innerHTML = data; });
}

function sendManualSMS(id) {
    if(confirm("මෙම පාරිභෝගිකයාට Rent එක පිළිබඳව මතක් කිරීමේ SMS පණිවිඩයක් යවන්නද?")) {
        fetch('./send_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        }).then(res => res.text()).then(data => { alert(data); });
    }
}

function updateStatusAndSMS(id) {
    const status = document.getElementById('stat-' + id).value;
    const device_name = document.getElementById('dev-' + id).value;
    const issue_name = document.getElementById('iss-' + id).value;
    const solution = document.getElementById('sol-' + id).value;

    const params = `id=${id}&device_name=${encodeURIComponent(device_name)}&issue_name=${encodeURIComponent(issue_name)}&solution=${encodeURIComponent(solution)}&device_status=${encodeURIComponent(status)}`;
    
    fetch('./inline_update_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    }).then(res => res.text()).then(data => {
        if(data.trim() === "Success") { 
            location.reload(); 
        } 
        else { 
            alert("Error: " + data); 
        }
    });
}

function toggleEdit(id) {
    let dev = document.getElementById('dev-' + id);
    let iss = document.getElementById('iss-' + id);
    let sol = document.getElementById('sol-' + id);
    let btn = document.getElementById('btn-edit-' + id);
    
    if (dev.readOnly) {
        // Edit Mode On
        dev.readOnly = false; 
        iss.readOnly = false; 
        sol.readOnly = false;
        dev.classList.add('editing'); 
        iss.classList.add('editing'); 
        sol.classList.add('editing');
        dev.focus();
        btn.innerHTML = "<i class='ph ph-floppy-disk'></i>";
        btn.style.background = "var(--success)";
    } else {
        // Save Mode
        updateStatusAndSMS(id);
    }
}
</script>
</body>
</html>