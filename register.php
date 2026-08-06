<?php
include 'db_config.php';

// --- 1. System Settings වලින් Next Job Number එක ලබා ගැනීම (නිවැරදි Table Name: system_settings) ---
$setting_query = "SELECT next_job_no FROM system_settings LIMIT 1";
$setting_result = mysqli_query($conn, $setting_query);
$setting_data = mysqli_fetch_assoc($setting_result);

// Settings වල අගයක් ඇත්නම් එය ගන්නවා, නැත්නම් default 5000 ලෙස ගන්නවා
$new_number = ($setting_data && isset($setting_data['next_job_no'])) ? $setting_data['next_job_no'] : 5000;

// Job Number එක පෙන්වන format එක (ORD-5000 වැනි)
$job_no = "ORD-" . $new_number;

// --- 2. අනෙකුත් විස්තර (Technicians/Issues) ලබා ගැනීම ---
$tech_result = mysqli_query($conn, "SELECT * FROM technicians");
$issue_result = mysqli_query($conn, "SELECT * FROM issue"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Registration | Smart Repair</title>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem("darkMode");
            if (savedTheme === "enabled") {
                document.documentElement.classList.add("dark-mode");
            }
        })();
    </script>

    <?php include 'navbar.php'; ?>

    <style>
        .container { max-width: 1000px; margin: 0 auto; margin-top: 25px; }
        .page-title { text-align: center; margin-bottom: 30px; }
        .page-title h1 { font-size: 32px; font-weight: 700; color: var(--primary-green); margin-bottom: 8px; }
        .page-title p { color: var(--text-muted); font-size: 15px; }
        .form-card { background: var(--light-surface); padding: 40px; border-radius: var(--border-radius); box-shadow: var(--card-shadow); border: 1px solid var(--border-light); transition: var(--transition); }
        body.dark-mode .form-card { background: var(--dark-surface); border-color: #334155; }
        
        .section { margin-bottom: 35px; padding-bottom: 25px; border-bottom: 2px solid var(--border-light); }
        body.dark-mode .section { border-bottom-color: #334155; }
        .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 3px solid var(--accent-green); }
        .section-header h3 { font-size: 18px; font-weight: 700; color: var(--text-dark); }
        body.dark-mode .section-header h3 { color: #f1f5f9; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        
        input, select, textarea { padding: 12px 16px; border: 2px solid var(--border-light); border-radius: 10px; outline: none; font-size: 14px; transition: var(--transition); background: var(--light-bg); color: var(--text-dark); }
        input:focus, select:focus, textarea:focus { border-color: var(--primary-green); box-shadow: 0 0 0 3px var(--primary-green-light); }
        body.dark-mode input, body.dark-mode select, body.dark-mode textarea { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        body.dark-mode input:focus, body.dark-mode select:focus, body.dark-mode textarea:focus { box-shadow: 0 0 0 3px rgba(4, 217, 146, 0.2); }
        
        .job-no-badge { background: var(--primary-green-light); border: 2px solid var(--primary-green); padding: 15px 20px; border-radius: 12px; text-align: center; margin-bottom: 25px; }
        body.dark-mode .job-no-badge { background: rgba(4, 217, 146, 0.1); border-color: var(--primary-green); }
        .job-no-badge label { font-size: 11px; color: var(--primary-green-dark); margin-bottom: 5px; }
        body.dark-mode .job-no-badge label { color: var(--accent-green); }
        .job-no-badge .job-number { font-size: 24px; font-weight: 800; color: var(--primary-green); letter-spacing: 1px; }
        
        .device-card { background: var(--light-bg); border: 2px solid var(--border-light); padding: 25px; border-radius: 15px; margin-bottom: 20px; position: relative; transition: var(--transition); }
        body.dark-mode .device-card { background: rgba(15, 23, 42, 0.5); border-color: #1e293b; }
        
        .btn-primary { background: linear-gradient(135deg, var(--primary-green), var(--accent-green)); color: white; border: none; padding: 16px 32px; border-radius: 12px; width: 100%; cursor: pointer; font-weight: 700; font-size: 15px; box-shadow: 0 6px 20px rgba(4, 217, 146, 0.3); margin-top: 20px; transition: var(--transition); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(4, 217, 146, 0.4); }
        
        .btn-add { background: transparent; border: 2px solid var(--primary-green); color: var(--primary-green); padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 14px; width: 100%; margin-bottom: 10px; transition: var(--transition); }
        .btn-add:hover { background: var(--primary-green); color: white; }
        
        .remove-btn { color: #ef4444; cursor: pointer; font-size: 14px; border: 2px solid #ef4444; background: transparent; padding: 6px 14px; border-radius: 8px; font-weight: 600; transition: var(--transition); }
        .remove-btn:hover { background: #ef4444; color: white; }
        
        .loading-text { font-size: 11px; color: var(--accent-green); display: none; margin-left: 8px; font-weight: 600; }
    </style>
</head>
<body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] == 'enabled') ? 'dark-mode' : '' ?>">

<div class="container">
    <div class="page-title">
        <h1> Customer Registration Form</h1>
        <p>Register new customer and service details</p>
    </div>
    
    <div class="form-card">
        <form action="save_jobs.php" method="POST" enctype="multipart/form-data">
            
            <div class="section">
                <div class="section-header">
                    <span class="section-icon"></span>
                    <h3>Customer Information</h3>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Phone Number <span id="searching" class="loading-text">(Searching...)</span></label>
                        <input type="text" name="phone_number" id="customer_phone" placeholder="07xxxxxxxx" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address (Optional)</label>
                        <input type="email" name="email" id="customer_email" placeholder="example@mail.com">
                    </div>
                    <div class="form-group">
                        <label>Address (Optional)</label>
                        <input type="text" name="address" id="customer_address" placeholder="City / Street">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <span class="section-icon"></span>
                    <h3>Job Assignment</h3>
                </div>
                
                <div class="job-no-badge">
                    <label>Job Number (Auto-Generated)</label>
                    <div class="job-number"><?= $job_no ?></div>
                    <input type="hidden" name="job_no" value="<?= $job_no ?>">
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Assign Technician</label>
                        <select name="technician_id" id="techSelect" required>
                            <option value="">-- Select Technician --</option>
                            <?php mysqli_data_seek($tech_result, 0); while($t = mysqli_fetch_assoc($tech_result)) { ?>
                                <option value="<?= $t['technician_id'] ?>"><?= $t['name'] ?></option>
                            <?php } ?>
                            <option value="new" style="color:#2ecc71; font-weight:bold;">+ Add New Technician</option>
                        </select>
                        <input type="text" name="new_technician" id="newTechInput" placeholder="Enter Technician Name" style="display:none; margin-top:12px; border-color: #2ecc71;">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <span class="section-icon"></span>
                    <h3>Device Details</h3>
                </div>
                <div id="devicesContainer"></div>
                <button type="button" class="btn-add" onclick="addDevice()">+ Add Another Device</button>
            </div>

            <button type="submit" class="btn-primary"><i class="ph ph-check-circle" style="margin-right:8px; font-size:18px; vertical-align:middle;"></i> Complete Registration</button>
        </form>
    </div>
</div>

<script>
    function checkTheme() {
        const savedTheme = localStorage.getItem("darkMode");
        if (savedTheme === "enabled") {
            document.body.classList.add("dark-mode");
        } else {
            document.body.classList.remove("dark-mode");
        }
    }

    window.addEventListener('storage', (e) => {
        if (e.key === 'darkMode') checkTheme();
    });

    document.getElementById('customer_phone').addEventListener('input', function() {
        let phone = this.value.replace(/[^0-9+]/g, '');
        this.value = phone;
        if(phone.length >= 10) {
            document.getElementById('searching').style.display = 'inline';
            fetch('get_customer.php?phone=' + phone)
            .then(res => res.json())
            .then(data => {
                document.getElementById('searching').style.display = 'none';
                if(data.found) {
                    document.getElementById('customer_name').value = data.name;
                    document.getElementById('customer_email').value = data.email;
                    document.getElementById('customer_address').value = data.address;
                }
            });
        }
    });

    document.getElementById('techSelect').addEventListener('change', function() {
        document.getElementById('newTechInput').style.display = (this.value === 'new') ? 'block' : 'none';
    });

    const dbIssueList = [
        <?php 
        mysqli_data_seek($issue_result, 0);
        while($issue = mysqli_fetch_assoc($issue_result)) {
            echo "{id: '".addslashes($issue['issue_name'])."', name: '".addslashes($issue['issue_name'])."'},";
        }
        ?>
    ];

    let deviceCount = 0;
    function addDevice() {
        deviceCount++;
        const container = document.getElementById('devicesContainer');
        const div = document.createElement('div');
        div.className = 'device-card';
        let dbOptions = dbIssueList.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('');

        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <strong style="display:flex; align-items:center; gap:8px;"><i class="ph-fill ph-device-mobile" style="color:var(--primary); font-size:18px;"></i> Device #${deviceCount}</strong>
                ${deviceCount > 1 ? `<button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()"><i class="ph ph-x"></i> Remove</button>` : ''}
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Device Type</label>
                    <select name="devices[]" required>
                        <option value="">-- Select Device --</option>
                        <option value="Desktop PC">Desktop PC</option>
                        <option value="UPS">UPS</option>
                        <option value="Printer">Printer</option>
                        <option value="Laptop">Laptop</option>
                        <option value="POS PC">POS PC</option>
                        <option value="Monitor">Monitor</option>
                        <option value="All-in-One PC">All-in-One PC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Issue Type / Fault Description</label>
                    <select name="issues[]" onchange="toggleNewIssue(this)" required>
                        <option value="">-- Select Issue --</option>
                        <option value="Display Damage">Display Damage</option>
                        <option value="No Power">No Power</option>
                        <option value="Service">Service</option>
                        ${dbOptions}
                        <option value="new" style="color:#2ecc71;">+ Add New Issue</option>
                    </select>
                    <input type="text" name="new_issues[]" style="display:none; margin-top:10px;" placeholder="Enter New Issue">
                </div>
                <div class="form-group">
                    <label>Item Model</label>
                    <input type="text" name="item_models[]" placeholder="e.g. Dell XPS 15" required>
                </div>
                <div class="form-group">
                    <label>Warranty Status</label>
                    <select name="warranty[]" required>
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Repair Path</label>
                    <select name="repair_paths[]" required>
                        <option value="In-House">In-House</option>
                        <option value="Agent">Agent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Device Image</label>
                    <input type="file" name="device_images[]" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Expected Solution (Optional)</label>
                    <input type="text" name="solutions[]" placeholder="e.g. Logic Board Repair">
                </div>
            </div>
            <div class="form-grid" style="margin-top:15px;">
                <div class="form-group">
                    <label>Description / Fault Details</label>
                    <textarea name="descriptions[]" rows="2" placeholder="Describe the problem..."></textarea>
                </div>
                <div class="form-group">
                    <label>Another Note (Optional)</label>
                    <textarea name="another_notes[]" rows="2" placeholder="Any additional notes..."></textarea>
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function toggleNewIssue(select) {
        const input = select.nextElementSibling;
        input.style.display = (select.value === 'new') ? 'block' : 'none';
        if(select.value === 'new') input.focus();
    }

    window.onload = addDevice;
</script>

</body>
