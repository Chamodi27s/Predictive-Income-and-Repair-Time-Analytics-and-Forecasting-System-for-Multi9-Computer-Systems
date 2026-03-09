<?php
include 'db_config.php';

// Job Number calculation
$query = "SELECT job_no FROM job ORDER BY job_no DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$last_job = mysqli_fetch_assoc($result);

if ($last_job) {
    $number = preg_replace("/[^0-9]/", "", $last_job['job_no']);
    $new_number = (int)$number + 1;
} else {
    $new_number = 5000;
}
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
        // Navbar එකේ logic එකට ගැලපෙන පරිදි localStorage පරීක්ෂාව
        (function() {
            const savedTheme = localStorage.getItem("darkMode");
            if (savedTheme === "enabled") {
                document.documentElement.classList.add("dark-mode"); // HTML වලටත් දානවා safer වෙන්න
            }
        })();
    </script>

    <?php include 'navbar.php'; ?>

    <style>
        /* --- ඔබේ මුල් CSS (කිසිවක් වෙනස් කර නැත) --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #ffffff 100%);
            min-height: 100vh;
            padding-top: 120px;
            padding-left: 40px;
            padding-right: 40px;
            color: #2c3e50;
        }

        /* --- DARK MODE APPLY (Navbar CSS එකට ගැලපෙන පරිදි) --- */
        /* Navbar එකේ body.dark-mode පාවිච්චි කරන නිසා අපිත් ඒකම පාවිච්චි කරමු */
        body.dark-mode {
            background: linear-gradient(135deg, #020617, #0f172a) !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-card {
            background: rgba(30, 41, 59, 0.7) !important;
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
        }

        body.dark-mode .page-title h1, 
        body.dark-mode .section-header h3,
        body.dark-mode .job-number {
            color: #ffffff !important;
        }

        body.dark-mode label {
            color: #94a3b8 !important;
        }

        body.dark-mode input, 
        body.dark-mode select, 
        body.dark-mode textarea {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }

        body.dark-mode .device-card {
            background: rgba(15, 23, 42, 0.5) !important;
            border-color: #1e293b !important;
        }

        body.dark-mode .job-no-badge {
            background: rgba(34, 197, 94, 0.1) !important;
            border-color: #22c55e !important;
        }

        body.dark-mode .btn-add {
            background: transparent !important;
            color: #22c55e !important;
        }

        /* --- මුල් CSS ඉතිරි කොටස --- */
        .container { max-width: 1000px; margin: 0 auto; margin-top: 25px; }
        .page-title { text-align: center; margin-bottom: 30px; }
        .page-title h1 { font-size: 32px; font-weight: 700; color: #2c3e50; margin-bottom: 8px; }
        .page-title p { color: #7f8c8d; font-size: 15px; }
        .form-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 40px; border-radius: 20px; box-shadow: 0 8px 32px rgba(46, 125, 50, 0.12); border: 1px solid rgba(255, 255, 255, 0.5); transition: 0.3s; }
        .section { margin-bottom: 35px; padding-bottom: 25px; border-bottom: 2px solid #f0f2f5; }
        .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 3px solid #2ecc71; }
        .section-header h3 { font-size: 18px; font-weight: 700; color: #2c3e50; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 13px; color: #5a6c7d; text-transform: uppercase; letter-spacing: 0.5px; }
        input, select, textarea { padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 10px; outline: none; font-family: inherit; font-size: 14px; transition: all 0.3s ease; background: white; }
        input:focus, select:focus, textarea:focus { border-color: #2ecc71; box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1); }
        .job-no-badge { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border: 2px solid #2ecc71; padding: 15px 20px; border-radius: 12px; text-align: center; margin-bottom: 25px; }
        .job-no-badge label { font-size: 11px; color: #27ae60; margin-bottom: 5px; }
        .job-no-badge .job-number { font-size: 24px; font-weight: 800; color: #2c3e50; letter-spacing: 1px; }
        .device-card { background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border: 2px solid #e8ecef; padding: 25px; border-radius: 15px; margin-bottom: 20px; position: relative; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; border: none; padding: 16px 32px; border-radius: 12px; width: 100%; cursor: pointer; font-weight: 700; font-size: 15px; box-shadow: 0 6px 20px rgba(46, 204, 113, 0.3); margin-top: 20px; }
        .btn-add { background: white; border: 2px solid #2ecc71; color: #2ecc71; padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 14px; width: 100%; margin-bottom: 10px; }
        .remove-btn { color: #e74c3c; cursor: pointer; font-size: 14px; border: 2px solid #e74c3c; background: white; padding: 6px 14px; border-radius: 8px; font-weight: 600; }
        .loading-text { font-size: 11px; color: #2ecc71; display: none; margin-left: 8px; font-weight: 600; }
    </style>
</head>
<body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] == 'enabled') ? 'dark-mode' : '' ?>">

<div class="container">
    <div class="page-title">
        <h1>🔧 Customer Registration Form</h1>
        <p>Register new customer and service details</p>
    </div>
    
    <div class="form-card">
        <form action="save_jobs.php" method="POST" enctype="multipart/form-data">
            
            <div class="section">
                <div class="section-header">
                    <span class="section-icon">👤</span>
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
                        <label>Email Address</label>
                        <input type="email" name="email" id="customer_email" placeholder="example@mail.com">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="customer_address" placeholder="City / Street">
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <span class="section-icon">📋</span>
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
                    <span class="section-icon">📱</span>
                    <h3>Device Details</h3>
                </div>
                <div id="devicesContainer"></div>
                <button type="button" class="btn-add" onclick="addDevice()">+ Add Another Device</button>
            </div>

            <button type="submit" class="btn-primary">✓ Complete Registration</button>
        </form>
    </div>
</div>

<script>
    // Navbar එකේ Dark Mode switch එකට respond කිරීම සඳහා අමතර Listener එකක්
    function checkTheme() {
        const savedTheme = localStorage.getItem("darkMode");
        if (savedTheme === "enabled") {
            document.body.classList.add("dark-mode");
        } else {
            document.body.classList.remove("dark-mode");
        }
    }

    // Navbar button එක ක්ලික් කළ විට මේ පිටුවේ styles ද වහාම මාරු වීමට මෙය අවශ්‍යයි
    window.addEventListener('storage', (e) => {
        if (e.key === 'darkMode') {
            checkTheme();
        }
    });

    // --- ඔබේ මුල් JS Logic එලෙසම ---
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
                <strong>📱 Device #${deviceCount}</strong>
                ${deviceCount > 1 ? `<button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()">✕ Remove</button>` : ''}
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Device Type</label>
                    <select name="devices[]" required>
                        <option value="">-- Select Device --</option>
                        <option value="Printer">Printer</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Desktop">Desktop PC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Issue Type</label>
                    <select name="issues[]" onchange="toggleNewIssue(this)" required>
                        <option value="">-- Select Issue --</option>
                        <option value="Display Damage">Display Damage</option>
                        <option value="No Power">No Power</option>
                        <option value="Service">Service</option>
                        ${dbOptions}
                        <option value="new" style="color:#2ecc71;">+ Add New Issue</option>
                    </select>
                    <input type="text" name="new_issues[]" style="display:none; margin-top:10px;">
                </div>
                <div class="form-group">
                    <label>Warranty Status</label>
                    <select name="warranty_status[]" required>
                        <option value="No Warranty">No Warranty</option>
                        <option value="Warranty"> Warranty</option>
                    </select>
                </div>
            </div>
            <div class="form-grid" style="margin-top:20px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Description / Note</label>
                    <textarea name="descriptions[]" placeholder="Notes..."></textarea>
                </div>
                <div class="form-group">
                    <label>Device Image (Optional)</label>
                    <input type="file" name="device_images[]" accept="image/*">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function toggleNewIssue(select) {
        select.nextElementSibling.style.display = (select.value === 'new') ? 'block' : 'none';
    }

    addDevice(); 
</script>
</body>
</html>