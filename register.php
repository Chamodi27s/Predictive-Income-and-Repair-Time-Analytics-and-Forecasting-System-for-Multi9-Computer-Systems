<?php
include 'db_config.php';


$setting_query = "SELECT next_job_no FROM system_settings LIMIT 1";
$setting_result = mysqli_query($conn, $setting_query);
$setting_data = mysqli_fetch_assoc($setting_result);


$new_number = ($setting_data && isset($setting_data['next_job_no'])) ? $setting_data['next_job_no'] : 5000;


$job_no = "ORD-" . $new_number;

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

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-light: #f8fafc;
            --card-light: #ffffff;
            --text-light: #1e293b;
            --border-light: rgba(0,0,0,0.08);
            --accent: #10b981;
            --accent-hover: #059669;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
            transition: background 0.3s, color 0.3s;
            padding: 140px 20px 40px;
        }
        body.dark-mode { background: linear-gradient(135deg, #020617, #0f172a); color: #f1f5f9; }

        .container {
            max-width: 900px;
            margin: 0 auto;
            margin-top: 20px;
            padding: 0 20px;
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
            font-size: clamp(20px, 4vw, 28px);
            font-weight: 800;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .page-header p { font-size: 14px; margin: 0; opacity: 0.95; }
        body.dark-mode .page-header h1 { color: white; }

        .form-card {
            background: var(--card-light);
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid var(--border-light);
        }
        body.dark-mode .form-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(20px);
            border-color: rgba(255,255,255,0.05);
        }

        .step-progress { display: none; }
        .step-content { display: block !important; animation: none; margin-bottom: 30px; }

        /* Responsive Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .form-group { display: flex; flex-direction: column; }
        label {
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select, textarea {
            padding: 10px 14px;
            border: 1.5px solid var(--border-light);
            border-radius: 10px;
            outline: none;
            font-size: 13px;
            transition: 0.3s;
            background: #f8fafc;
            color: var(--text-light);
            width: 100%;
            box-sizing: border-box;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
            background: white;
        }
        body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(255,255,255,0.1);
            color: white;
        }
        body.dark-mode input:focus, body.dark-mode select:focus, body.dark-mode textarea:focus {
            background: rgba(15,23,42,0.9);
        }

        .job-no-badge {
            background: rgba(16,185,129,0.1);
            border: 1.5px dashed var(--accent);
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .job-no-badge label { margin: 0; color: var(--accent); }
        .job-no-badge .job-number { font-size: 20px; font-weight: 800; color: var(--accent); }

        .device-card {
            background: #ffffff;
            border: 1.5px solid var(--border-light);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 15px;
            position: relative;
            transition: 0.3s;
        }
        body.dark-mode .device-card { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            text-align: center;
        }
        .btn-prev { background: #e2e8f0; color: #475569; }
        body.dark-mode .btn-prev { background: rgba(255,255,255,0.1); color: white; }
        .btn-prev:hover { background: #cbd5e1; }

        .btn-next, .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: white;
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .btn-next:hover, .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16,185,129,0.4);
        }

        .btn-add {
            background: transparent;
            border: 1.5px dashed var(--accent);
            color: var(--accent);
            width: 100%;
            margin-bottom: 10px;
        }
        .btn-add:hover { background: rgba(16,185,129,0.1); }

        .remove-btn {
            color: #ef4444;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            border: none;
            background: rgba(239, 68, 68, 0.1);
            padding: 6px 12px;
            border-radius: 8px;
        }
        .remove-btn:hover { background: #ef4444; color: white; }

        .loading-text { font-size: 11px; color: var(--accent); display: none; margin-left: 8px; font-weight: 600; }

        /* ==================== RESPONSIVE QUERIES ==================== */

        /* Tablet */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
                margin-top: 15px;
            }
            .form-card {
                padding: 20px 16px;
                border-radius: 18px;
            }
            .form-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .job-no-badge {
                flex-direction: column;
                gap: 8px;
            }
            .job-no-badge .job-number { font-size: 24px; }
            .form-actions {
                flex-direction: column;
            }
            .form-actions button {
                width: 100%;
            }
            .device-card {
                padding: 15px;
            }
        }

        /* Mobile */
        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
                margin-top: 10px;
            }
            .form-card {
                padding: 16px 12px;
                border-radius: 16px;
            }
            .form-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .page-title h1 {
                font-size: 20px;
            }
            .page-title p {
                font-size: 13px;
            }
            input, select, textarea {
                font-size: 15px; /* prevent iOS zoom */
                padding: 11px 12px;
            }
            .btn {
                padding: 13px 18px;
                font-size: 14px;
                width: 100%;
            }
            .btn-primary {
                font-size: 15px;
                padding: 15px;
            }
            .device-card {
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] == 'enabled') ? 'dark-mode' : '' ?>">

<div class="container">
    <div class="page-header">
        <h1> Customer Registration</h1>
        <p>Register new customer and service details</p>
    </div>
    
    <div class="form-card">
        <!-- Progress Bar -->
        <div class="step-progress">
            <div class="step-item active" id="step-marker-1">
                <div class="step-circle">1</div> Customer Details
            </div>
            <div class="step-item" id="step-marker-2">
                <div class="step-circle">2</div> Device & Issue
            </div>
        </div>

        <form action="save_jobs.php" method="POST" enctype="multipart/form-data" id="regForm">
            
            <!-- STEP 1 -->
            <div class="step-content active" id="step-1">
                
                <div class="job-no-badge">
                    <label>Generated Job Number</label>
                    <div class="job-number"><?= $job_no ?></div>
                    <input type="hidden" name="job_no" value="<?= $job_no ?>">
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
                        <label>Email Address</label>
                        <input type="email" name="email" id="customer_email" placeholder="example@mail.com">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="customer_address" placeholder="City / Street">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Assign Technician</label>
                        <select name="technician_id" id="techSelect" required>
                            <option value="">-- Select Technician --</option>
                            <?php mysqli_data_seek($tech_result, 0); while($t = mysqli_fetch_assoc($tech_result)) { ?>
                                <option value="<?= $t['technician_id'] ?>"><?= $t['name'] ?></option>
                            <?php } ?>
                            <option value="new" style="color:#10b981; font-weight:bold;">+ Add New</option>
                        </select>
                        <input type="text" name="new_technician" id="newTechInput" placeholder="Enter Technician Name" style="display:none; margin-top:8px;">
                    </div>
                </div>
            </div>

            <!-- STEP 2 (Now just section 2) -->
            <div class="step-content" id="step-2">
                <div id="devicesContainer"></div>
                <button type="button" class="btn btn-add" onclick="addDevice()"><i class="ph-bold ph-plus"></i> Add Another Device</button>
                
                <div class="form-actions" style="justify-content: center; margin-top: 40px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; max-width: 400px; justify-content: center; font-size: 16px; padding: 15px;"><i class="ph-bold ph-check-circle" style="font-size: 20px;"></i> Complete Registration</button>
                </div>
            </div>
            
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
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-light);">
                <strong style="display:flex; align-items:center; gap:8px;"><i class="ph-fill ph-device-mobile" style="color:var(--accent); font-size:18px;"></i> Device #${deviceCount}</strong>
                ${deviceCount > 1 ? `<button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()"><i class="ph-bold ph-trash"></i> Remove</button>` : ''}
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
                    <label>Issue Type / Fault</label>
                    <select name="issues[]" onchange="toggleNewIssue(this)" required>
                        <option value="">-- Select Issue --</option>
                        <option value="Display Damage">Display Damage</option>
                        <option value="No Power">No Power</option>
                        <option value="Service">Service</option>
                        ${dbOptions}
                        <option value="new" style="color:#10b981; font-weight: bold;">+ Add New Issue</option>
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
                    <label>Device Image</label>
                    <input type="file" name="device_images[]" accept="image/*" style="padding: 7px 10px;">
                </div>
            </div>
            <div class="form-grid" style="grid-template-columns: 1fr 1fr; margin-top:15px;">
                <div class="form-group">
                    <label>Description / Fault Details</label>
                    <textarea name="descriptions[]" rows="2" placeholder="Describe the problem..."></textarea>
                </div>
                <div class="form-group">
                    <label>Expected Solution (Optional)</label>
                    <textarea name="solutions[]" rows="2" placeholder="e.g. Logic Board Repair"></textarea>
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
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>