<?php 
include 'db_config.php';
include 'navbar.php';

//  Auto Status Update (Destroyed) ---
mysqli_query($conn, "UPDATE job_device SET device_status = 'Destroyed' 
                     WHERE destroy_notice_sent_date IS NOT NULL 
                     AND DATEDIFF(NOW(), destroy_notice_sent_date) >= 7 
                     AND device_status != 'Destroyed'");

// Auto SMS Logic (Ready to collect) ---
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
    
    try {
        $history_sql = "INSERT INTO sms_history (job_device_id, phone_number, message, status) 
                        VALUES ('$j_id', '$phone', '$safe_msg', 'Sent (Auto)')";
        
        if(@mysqli_query($conn, $history_sql)) {
            @mysqli_query($conn, "UPDATE job_device SET last_sms_sent_date = CURDATE() WHERE job_device_id = $j_id");
        }
    } catch (Throwable $e) {
        // Safe fallback if sms_history table issue occurs
    }
}

//  Data Fetching and Filtering ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$status_flow = ['Pending', 'In Progress', 'Completed', 'Returned'];

$sql = "SELECT j.job_no, j.job_date, jd.advance_paid, t.name as technician_name, c.customer_name, j.phone_number, 
               jd.job_device_id, jd.device_name, jd.issue_name, jd.solution,
               CASE WHEN inv.payment_status = 'Paid' THEN 'Returned' ELSE jd.device_status END AS device_status,
               jd.completed_date, jd.destroy_notice_sent_date, jd.rent_warning_sent,
               inv.payment_status
        FROM job j
        INNER JOIN customer c ON j.phone_number = c.phone_number
        INNER JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN technicians t ON j.technician_id = t.technician_id
        LEFT JOIN invoice inv ON jd.job_no = inv.job_no 
        WHERE jd.job_status = 'Approved' 
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
        :root {
            --btn-blue: #2563eb;
            --btn-blue-hover: #1d4ed8;
            --btn-green: #059669;
            --btn-purple: #7c3aed;
            --btn-amber: #d97706;
        }
        
        body { padding-top: var(--nav-height, 100px); }

        .page-container { max-width: 1400px; margin: 0 auto; margin-top: 25px; padding: 0 15px; }
        .page-header { background: linear-gradient(135deg, #059669 0%, #10b981 50%, #047857 100%); padding: 30px; border-radius: 20px; margin-bottom: 30px; color: white; text-align: center; box-shadow: 0 10px 25px rgba(5, 150, 105, 0.25); }
        .page-header h1 { font-size: 28px; font-weight: 800; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .page-header p { font-size: 14px; opacity: 0.9; }

        .search-container { display: flex; justify-content: center; margin-bottom: 25px; align-items: center; gap: 15px; flex-wrap: wrap; }
        .search-box { display: flex; background: var(--light-surface, #ffffff); padding: 6px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); width: 100%; max-width: 550px; border: 1px solid var(--border-light, #e2e8f0); }
        .search-box input { flex: 1; border: none; padding: 10px 18px; outline: none; border-radius: 10px; background: transparent; color: var(--text-dark, #0f172a); font-size: 14px; }
        .search-box button { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 10px 22px; border-radius: 10px; cursor: pointer; font-weight: 700; transition: all 0.2s ease; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
        .search-box button:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4); }

        .history-link { background: var(--dark-surface, #1e293b); color: white; padding: 12px 22px; border-radius: 14px; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.2s; }
        .history-link:hover { background: #0f172a; transform: translateY(-1px); }

        .filter-container { display: flex; justify-content: center; gap: 12px; margin-bottom: 25px; flex-wrap: wrap; }
        .filter-tag { padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 13px; color: white; transition: all 0.2s; border: 2px solid transparent; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .filter-tag:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.15); }
        .active-tag { transform: scale(1.05); box-shadow: 0 6px 18px rgba(0,0,0,0.2) !important; border-color: #ffffff !important; }

        .table-container { 
            background: var(--light-surface, #ffffff); 
            border-radius: 18px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.06); 
            overflow-x: auto; 
            border: 1px solid var(--border-light, #e2e8f0);
            -webkit-overflow-scrolling: touch; 
        }

        /* ==================== RESPONSIVE QUERIES ==================== */
        @media (max-width: 768px) {
            .page-container { margin-top: 15px; padding: 0 10px; }
            .page-header { padding: 20px 15px; border-radius: 16px; margin-bottom: 20px; }
            .page-header h1 { font-size: 22px; }
            
            .search-container { flex-direction: column; align-items: stretch; gap: 10px; }
            .search-box { max-width: 100%; border-radius: 12px; flex-direction: column; padding: 10px; }
            .search-box input { width: 100%; margin-bottom: 10px; padding: 12px; }
            .search-box button { width: 100%; justify-content: center; padding: 12px; }
            .history-link { width: 100%; justify-content: center; border-radius: 12px; }
            
            .filter-container { gap: 8px; }
            .filter-tag { padding: 8px 12px; font-size: 11px; flex: 1 1 calc(33.333% - 8px); text-align: center; }

            .status-table { min-width: 900px; }
            .status-table th { padding: 12px 10px; font-size: 10px; }
            .status-table td { padding: 12px 10px; }

            .inline-input { font-size: 14px; padding: 10px; }
            .btn-action-sms, .btn-action-edit, .bill-btn { padding: 8px 12px; font-size: 11px; }
        }

        .status-table { width: 100%; border-collapse: collapse; min-width: 1100px; }
        .status-table th { background: #f8fafc; color: #64748b; padding: 14px 18px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 700; border-bottom: 2px solid #e2e8f0; white-space: nowrap; text-align: left; }
        .status-table td { padding: 14px 18px; border-bottom: 1px solid #e2e8f0; text-align: left; color: #0f172a; font-size: 13px; vertical-align: middle; }
        .status-table tr:hover td { background: rgba(248, 250, 252, 0.8); }

        .job-badge { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 6px 14px; border-radius: 50px; font-weight: 800; font-size: 12px; display: inline-block; }

        .inline-input { width: 100%; border: 1px solid #cbd5e1; background: var(--light-bg, #f8fafc); padding: 10px 12px; border-radius: 10px; font-size: 13px; color: var(--text-dark, #0f172a); transition: all 0.2s; }
        .inline-input[readonly] { background: rgba(241, 245, 249, 0.6); border-color: #e2e8f0; cursor: not-allowed; }
        .inline-input.editing { border-color: #2563eb !important; background: #ffffff !important; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2) !important; cursor: text !important; }

        /* Action Buttons with High Visibility */
        .btn-action-edit {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            border: none;
            padding: 9px 15px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            transition: all 0.2s;
        }
        .btn-action-edit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.5);
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
        }

        .btn-action-sms {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;
            color: #ffffff !important;
            border: none;
            padding: 9px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.35);
            transition: all 0.2s;
        }
        .btn-action-sms:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.5);
        }

        .bill-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 9px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
            transition: all 0.2s;
        }
        .bill-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(245, 158, 11, 0.5); }

        .paid-badge { background: #ecfdf5; color: #059669; border: 1px solid #10b981; padding: 7px 14px; border-radius: 8px; font-weight: 800; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; }

        .sms-btn { background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 10px; font-weight: 900; margin-top: 5px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 4px; }
        #smsModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index:9999; }
        .modal-content { background:white; width:90%; max-width:600px; margin:80px auto; padding:25px; border-radius:20px; position:relative; box-shadow:0 20px 40px rgba(0,0,0,0.25); }
        .close-modal { position:absolute; right:20px; top:18px; font-size:24px; cursor:pointer; font-weight:bold; color: #64748b; }
        .advance-amount { color: #e11d48; font-weight: 800; font-size: 14px; }
    </style>
</head>
<body>

<div id="smsModal">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById('smsModal').style.display='none'">&times;</span>
        <h2 style="margin-bottom:15px; font-size:20px; color:#0f172a;">SMS History</h2>
        <div id="historyBody" style="max-height:400px; overflow-y:auto; font-size:14px; color:#334155;">Loading...</div>
    </div>
</div>

<div class="page-container">
    <div class="page-header">
        <h1><i class="ph ph-wrench"></i> Job Management System</h1>
        <p>Track repair status, manage billing, and automate notifications</p>
    </div>

    <div class="search-container">
        <form action="" method="GET" class="search-box">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <input type="text" name="search" placeholder="Search by Job No, Phone, Name..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="ph ph-magnifying-glass" style="font-size:16px;"></i> Search</button>
        </form>
        <a href="returned_jobs.php" class="history-link"><i class="ph ph-clock-counter-clockwise" style="font-size:18px;"></i> History</a>
    </div>

    <div class="filter-container">
        <a href="?search=<?= $search ?>" class="filter-tag <?= $filter_status == '' ? 'active-tag' : '' ?>" style="background: #334155;">All Jobs</a>
        <a href="?status=Pending&search=<?= $search ?>" class="filter-tag <?= $filter_status == 'Pending' ? 'active-tag' : '' ?>" style="background: #f59e0b;">Pending</a>
        <a href="?status=In Progress&search=<?= $search ?>" class="filter-tag <?= $filter_status == 'In Progress' ? 'active-tag' : '' ?>" style="background: #2563eb;">In Progress</a>
        <a href="?status=Completed&search=<?= $search ?>" class="filter-tag <?= $filter_status == 'Completed' ? 'active-tag' : '' ?>" style="background: #059669;">Completed</a>
        <a href="?status=Returned&search=<?= $search ?>" class="filter-tag <?= $filter_status == 'Returned' ? 'active-tag' : '' ?>" style="background: #64748b;">Returned</a>
    </div>

    <div class="table-container">
        <table class="status-table">
            <thead>
                <tr>
                    <th>JOB NO</th>
                    <th>CUSTOMER DETAILS</th>
                    <th>DEVICE</th>
                    <th>ISSUE</th>
                    <th style="color: #e11d48;">ADVANCE (RS.)</th>
                    <th>SOLUTION</th> 
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $id = $row['job_device_id'];
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
                        <td><span class="job-badge">#<?= $row['job_no'] ?></span></td>
                        <td style="text-align: left;">
                            <b style="font-size:14px; color:#0f172a;"><?= htmlspecialchars($row['customer_name']) ?></b><br>
                            <small style="color:#64748b; font-weight:500;"><?= $row['phone_number'] ?></small>
                        </td>
                        
                        <td><input type="text" id="dev-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['device_name']) ?>" readonly></td>
                        <td><input type="text" id="iss-<?= $id ?>" class="inline-input" value="<?= htmlspecialchars($row['issue_name']) ?>" readonly></td>
                        
                        <td class="advance-amount">
                            Rs. <?= number_format($row['advance_paid'], 2) ?>
                        </td>

                        <td style="min-width:180px;">
                            <textarea id="sol-<?= $id ?>" class="inline-input" readonly rows="2" style="resize:vertical; min-height:48px;" placeholder="Click Edit button to enter solution..."><?= htmlspecialchars($row['solution'] ?? '') ?></textarea>
                        </td>
                        
                        <td style="vertical-align: middle; min-width: 140px;">
                            <select id="stat-<?= $id ?>" onchange="updateStatusAndSMS(<?= $id ?>)" <?= $current_status == 'Returned' ? 'disabled' : '' ?> 
                                    style="padding:10px 12px; border-radius:10px; border:1px solid #cbd5e1; font-weight:700; font-size:13px; width: 100%; cursor:pointer; background:#ffffff; color:#0f172a;">
                                <?php foreach ($status_flow as $idx => $opt): ?>
                                    <?php if ($idx >= $current_idx): ?>
                                        <option value="<?= $opt ?>" <?= $current_status == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            
                            <?php if($days_passed >= 365 && $current_status == 'Completed'): ?>
                                <div style="margin-top: 8px; padding: 6px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px;">
                                    <div style="color: #dc2626; font-weight: 900; font-size: 11px; margin-bottom: 4px;">
                                         OVER 1 YEAR
                                    </div>
                                    <button class="sms-btn" style="background: #ef4444 !important;" onclick="sendDestroySMS(<?= $id ?>)"><i class="ph ph-trash"></i> DESTROY SMS</button>
                                </div>
                            <?php elseif($delay_fee > 0): ?>
                                <div style="margin-top: 8px; padding: 6px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px;">
                                    <div style="color: #dc2626; font-weight: 900; font-size: 11px; margin-bottom: 4px;">
                                         RENT: Rs. <?= $delay_fee ?>
                                    </div>
                                    <button class="sms-btn" onclick="sendManualSMS(<?= $id ?>)"><i class="ph ph-paper-plane-tilt"></i> RENT SMS</button>
                                </div>
                            <?php elseif($current_status == 'Completed'): ?>
                                <div style="font-size: 10px; color: #64748b; font-weight:600; margin-top: 5px;">Collection: <?= $days_passed ?> days</div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                <button onclick="viewSMSHistory(<?= $id ?>)" title="View SMS History" class="btn-action-sms">
                                    <i class="ph ph-chat-text" style="font-size:15px;"></i> <span>SMS</span>
                                </button>
                                
                                <?php if($current_status != 'Returned'): ?>
                                    <button id="btn-edit-<?= $id ?>" onclick="toggleEdit(<?= $id ?>)" class="btn-action-edit" title="Click to edit details & solution">
                                        <i class="ph ph-pencil-simple" style="font-size:15px;"></i> <span>Edit</span>
                                    </button>
                                <?php endif; ?>

                                <?php if($current_status == 'Completed' || $current_status == 'Returned'): ?>
                                    <?php if($days_passed >= 365 && $current_status == 'Completed'): ?>
                                        <button onclick="sendDestroySMS(<?= $id ?>)" class="btn-action-edit" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;" title="Send Destroy Notice">
                                            <i class="ph ph-trash" style="font-size:15px;"></i> <span>Destroy</span>
                                        </button>
                                    <?php elseif(!$is_paid): ?>
                                        <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&fee=<?= $delay_fee ?>" class="bill-btn"><i class="ph ph-receipt" style="font-size:15px;"></i> BILL</a>
                                    <?php else: ?>
                                        <span class="paid-badge"><i class="ph ph-check-circle" style="font-size:15px;"></i> PAID</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="padding: 60px; color: var(--text-muted, #64748b); font-size:15px; font-weight:600;">No active jobs found.</td></tr>
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
    if(confirm("Should I send an SMS reminder about the rent to this customer?")) {
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
        sol.focus();
        btn.innerHTML = "<i class='ph ph-floppy-disk' style='font-size:15px;'></i> <span>Save</span>";
        btn.style.background = "linear-gradient(135deg, #10b981 0%, #059669 100%)";
    } else {
        // Save Mode
        updateStatusAndSMS(id);
    }
}

// Added JS function for the Destroy SMS button
function sendDestroySMS(id) {
    if(confirm("Are you sure you want to send a destroy notice for this device?")) {
        fetch('./send_sms_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&type=destroy` 
        }).then(res => res.text()).then(data => { 
            alert(data); 
            location.reload(); 
        });
    }
}
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>