<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_config.php';
include 'navbar.php';

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
    <style>
        /* Base Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            min-height: 100vh;
            padding-top: 100px;
            padding-bottom: 50px;
            color: #2c3e50;
            transition: all 0.3s ease;
        }

        /* ================= DARK MODE GLASS CORE ================= */
        body.dark-mode {
            background: linear-gradient(135deg, #020617 0%, #0f172a 100%) !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-card, 
        body.dark-mode .device-card,
        body.dark-mode .job-no-badge {
            background: rgba(30, 41, 59, 0.6) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
        }

        body.dark-mode input, 
        body.dark-mode select, 
        body.dark-mode textarea {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid #334155 !important;
            color: #f1f5f9 !important;
        }

        body.dark-mode .section-header {
            border-bottom-color: #2ecc71 !important;
        }

        body.dark-mode h1, body.dark-mode h3, body.dark-mode .job-number {
            color: #ffffff !important;
        }

        body.dark-mode label {
            color: #94a3b8 !important;
        }

        /* ================= Page Elements ================= */
        .container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
        .page-title { text-align: center; margin-bottom: 30px; }
        
        .form-card { 
            background: white;
            padding: 35px;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .section { margin-bottom: 30px; }
        .section-header { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            margin-bottom: 20px; 
            padding-bottom: 10px; 
            border-bottom: 3px solid #2ecc71; 
        }

        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
        }

        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        label { font-weight: 700; font-size: 11px; color: #64748b; text-transform: uppercase; margin-bottom: 6px; }

        input, select, textarea { 
            padding: 12px; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            font-size: 14px;
        }

        .job-no-badge { 
            background: #f8fafc; 
            padding: 20px; 
            border-radius: 16px; 
            text-align: center; 
            margin-bottom: 25px; 
            border: 2px dashed #2ecc71;
        }

        .device-card { 
            background: #fdfdfd; 
            border: 1px solid #edf2f7; 
            padding: 20px; 
            border-radius: 18px; 
            margin-bottom: 15px; 
        }

        .btn-primary { 
            background: #2ecc71; 
            color: white; 
            border: none; 
            padding: 18px; 
            border-radius: 14px; 
            width: 100%; 
            font-weight: 700; 
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-add { 
            background: transparent; 
            border: 2px solid #2ecc71; 
            color: #2ecc71; 
            padding: 10px; 
            border-radius: 12px; 
            width: 100%; 
            cursor: pointer; 
            font-weight: 600;
        }

        .loading-text { color: #2ecc71; font-size: 10px; display: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="page-title">
        <h1>🔧 Service Registration</h1>
        <p>Create a new repair job sheet</p>
    </div>
    
    <div class="form-card">
        <form action="save_jobs.php" method="POST" enctype="multipart/form-data">
            
            <div class="section">
                <div class="section-header"><h3>👤 Customer Details</h3></div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Phone <span id="searching" class="loading-text">Searching...</span></label>
                        <input type="text" name="phone_number" id="customer_phone" required>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="customer_name" id="customer_name" required>
                    </div>
                </div>
            </div>

            <div class="job-no-badge">
                <label>Assigned Job Number</label>
                <div class="job-number" style="font-size: 28px; font-weight: 800;"><?= $job_no ?></div>
                <input type="hidden" name="job_no" value="<?= $job_no ?>">
            </div>

            <div class="form-group">
                <label>Assign Technician</label>
                <select name="technician_id" id="techSelect" required>
                    <option value="">-- Select --</option>
                    <?php mysqli_data_seek($tech_result, 0); while($t = mysqli_fetch_assoc($tech_result)) { ?>
                        <option value="<?= $t['technician_id'] ?>"><?= $t['name'] ?></option>
                    <?php } ?>
                    <option value="new">+ Add New</option>
                </select>
                <input type="text" name="new_technician" id="newTechInput" style="display:none; margin-top:10px;" placeholder="Name">
            </div>

            <div class="section">
                <div class="section-header"><h3>📱 Device List</h3></div>
                <div id="devicesContainer"></div>
                <button type="button" class="btn-add" onclick="addDevice()">+ Add Device</button>
            </div>

            <button type="submit" class="btn-primary">✓ Save & Print Jobsheet</button>
        </form>
    </div>
</div>

<script>
    // Dark Mode එක toggle කරන කොට පරීක්ෂා කිරීමට (Optional)
    // navbar.php එකේ ඇති function එක මගින් මෙය පාලනය වේ.

    // Phone Auto-fill
    document.getElementById('customer_phone').addEventListener('input', function() {
        let phone = this.value;
        if(phone.length >= 10) {
            document.getElementById('searching').style.display = 'inline';
            fetch('get_customer.php?phone=' + phone)
            .then(res => res.json())
            .then(data => {
                document.getElementById('searching').style.display = 'none';
                if(data.found) {
                    document.getElementById('customer_name').value = data.name;
                }
            });
        }
    });

    let deviceCount = 0;
    const dbIssueList = [<?php mysqli_data_seek($issue_result, 0); while($i = mysqli_fetch_assoc($issue_result)) { echo "{name:'".addslashes($i['issue_name'])."'},"; } ?>];

    function addDevice() {
        deviceCount++;
        const container = document.getElementById('devicesContainer');
        const div = document.createElement('div');
        div.className = 'device-card';
        
        let issues = dbIssueList.map(i => `<option value="${i.name}">${i.name}</option>`).join('');

        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <strong>Device #${deviceCount}</strong>
                ${deviceCount > 1 ? '<span style="color:red; cursor:pointer;" onclick="this.parentElement.parentElement.remove()">Remove</span>' : ''}
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Type</label>
                    <select name="devices[]" required>
                        <option value="Laptop">Laptop</option>
                        <option value="Desktop">Desktop</option>
                        <option value="Printer">Printer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Issue</label>
                    <select name="issues[]" onchange="if(this.value=='new'){this.nextElementSibling.style.display='block'}">
                        <option value="">-- Select --</option>
                        ${issues}
                        <option value="new">+ Add New</option>
                    </select>
                    <input type="text" name="new_issues[]" style="display:none; margin-top:5px;">
                </div>
            </div>
            <textarea name="descriptions[]" style="width:100%; margin-top:10px;" placeholder="Notes..."></textarea>
        `;
        container.appendChild(div);
    }
    addDevice();

    document.getElementById('techSelect').addEventListener('change', function() {
        document.getElementById('newTechInput').style.display = (this.value === 'new') ? 'block' : 'none';
    });
</script>
</body>
<?php include 'chatbot.php'; ?>
</html>