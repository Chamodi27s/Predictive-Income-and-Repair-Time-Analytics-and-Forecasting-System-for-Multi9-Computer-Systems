<?php
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Registration | Smart Repair</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #007bff; --bg: #f4f7fe; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); padding: 20px; }
        .container { max-width: 950px; margin: 0 auto; }
        .form-card { background: white; padding: 35px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 0.85rem; color: #444; }
        input, select, textarea { padding: 12px; border: 2px solid #e2e8f0; border-radius: 10px; outline: none; font-family: inherit; }
        .device-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; margin-bottom: 15px; position: relative; }
        .btn-primary { background: var(--primary); color: white; border: none; padding: 15px; border-radius: 10px; width: 100%; cursor: pointer; font-weight: bold; }
        .btn-add { background: white; border: 2px solid var(--primary); color: var(--primary); padding: 10px; border-radius: 10px; cursor: pointer; margin-bottom: 15px; font-weight: 600; }
        .remove-btn { position: absolute; top: 10px; right: 10px; color: #ff4d4d; cursor: pointer; font-size: 0.8rem; border: none; background: none; }
        .loading-text { font-size: 11px; color: var(--primary); display: none; margin-left: 5px; }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <h2 style="text-align:center;">🔧 Service Registration</h2>
        <form action="save_jobs.php" method="POST" enctype="multipart/form-data">
            
            <div class="section">
                <label style="font-size: 1.1rem; color: var(--primary);">👤 Customer Information</label>
                <div style="margin-top: 15px;" class="form-grid">
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
                <label style="font-size: 1.1rem; color: var(--primary);">📱 Devices & Detailed Info</label>
                <div id="devicesContainer" style="margin-top: 15px;"></div>
                <button type="button" class="btn-add" onclick="addDevice()">+ Add Another Device</button>
            </div>

            <div class="section">
                <label style="font-size: 1.1rem; color: var(--primary);">📋 Assignment</label>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Job No (Auto)</label>
                        <input type="text" name="job_no" value="<?= $job_no ?>" readonly style="background:#f1f3f5;">
                    </div>
                    <div class="form-group">
                        <label>Technician</label>
                        <select name="technician_id" id="techSelect" required>
                            <option value="">-- Select --</option>
                            <?php mysqli_data_seek($tech_result, 0); while($t = mysqli_fetch_assoc($tech_result)) { ?>
                                <option value="<?= $t['technician_id'] ?>"><?= $t['name'] ?></option>
                            <?php } ?>
                            <option value="new" style="color:blue; font-weight:bold;">+ Add New Technician</option>
                        </select>
                        <input type="text" name="new_technician" id="newTechInput" placeholder="Enter Technician Name" style="display:none; margin-top:10px; border-color: #007bff;">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">Complete Registration</button>
        </form>
    </div>
</div>

<script>
    // 1. Auto-fill Customer Data by Phone Number
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
                    document.getElementById('customer_email').value = data.email;
                    document.getElementById('customer_address').value = data.address;
                }
            })
            .catch(err => {
                document.getElementById('searching').style.display = 'none';
            });
        }
    });

    // 2. Add New Technician Input Toggle
    document.getElementById('techSelect').addEventListener('change', function() {
        document.getElementById('newTechInput').style.display = (this.value === 'new') ? 'block' : 'none';
        if(this.value === 'new') document.getElementById('newTechInput').focus();
    });

    // 3. Dynamic Device Adding
    let deviceCount = 0;
    function addDevice() {
        deviceCount++;
        const container = document.getElementById('devicesContainer');
        const div = document.createElement('div');
        div.className = 'device-card';
        div.innerHTML = `
            ${deviceCount > 1 ? `<button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕ Remove</button>` : ''}
            <b style="display:block; margin-bottom:12px; color: #555;">Device #${deviceCount}</b>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Device Type</label>
                    <select name="devices[]" required>
                        <option value="Mobile">Mobile Phone</option>
                        <option value="Printer">Printer</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Desktop">Desktop PC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Issue</label>
                    <select name="issues[]" required>
                        <option value="Display">Display Damage</option>
                        <option value="Power">Power Issue</option>
                        <option value="Software">Software Issue</option>
                        <option value="Charging">Charging Port</option>
                        <option value="Service">Full Service</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Warranty Status</label>
                    <select name="warranty_status[]" required>
                        <option value="Non-Warranty">Non-Warranty</option>
                        <option value="Warranty">Under Warranty</option>
                    </select>
                </div>
            </div>

            <div class="form-grid" style="margin-top:15px; grid-template-columns: 2fr 1fr;">
                <div class="form-group">
                    <label>Description / Conditions / Note</label>
                    <textarea name="descriptions[]" rows="2" placeholder="e.g. Back cover broken, No SIM tray..."></textarea>
                </div>
                <div class="form-group">
                    <label>Upload Device Image</label>
                    <input type="file" name="device_images[]" accept="image/*">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    addDevice(); // Initial device call
</script>
</body>
</html>