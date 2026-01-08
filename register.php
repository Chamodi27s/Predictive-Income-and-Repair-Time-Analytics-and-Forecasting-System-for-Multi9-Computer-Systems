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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Registration | Smart Repair</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #ffffff 100%);
            min-height: 100vh;
            padding-top: 120px;   /* 🔥 navbar height */
            padding-left: 40px;
            padding-right: 40px;
            color: #2c3e50;
        }
        
        .container { 
            max-width: 1000px; 
            margin: 0 auto;
            margin-top: 25px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-title h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .page-title p {
            color: #7f8c8d;
            font-size: 15px;
        }
        
        .form-card { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(46, 125, 50, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .section { 
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f2f5;
        }
        
        .section:last-of-type {
            border-bottom: none;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #2ecc71;
        }
        
        .section-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .section-icon {
            font-size: 24px;
        }
        
        .form-grid { 
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .form-group { 
            display: flex;
            flex-direction: column;
        }
        
        label { 
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
            color: #5a6c7d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        input, select, textarea { 
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
        }
        
        input[readonly] {
            background: #f8f9fa;
            color: #7f8c8d;
            cursor: not-allowed;
        }
        
        .job-no-badge {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 2px solid #2ecc71;
            padding: 15px 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .job-no-badge label {
            font-size: 11px;
            color: #27ae60;
            margin-bottom: 5px;
        }
        
        .job-no-badge .job-number {
            font-size: 24px;
            font-weight: 800;
            color: #2c3e50;
            letter-spacing: 1px;
        }
        
        .device-card { 
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px solid #e8ecef;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .device-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .device-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
        }
        
        .device-title {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.3);
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(46, 204, 113, 0.4);
        }
        
        .btn-add { 
            background: white;
            border: 2px solid #2ecc71;
            color: #2ecc71;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            background: #2ecc71;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
        }
        
        .remove-btn { 
            color: #e74c3c;
            cursor: pointer;
            font-size: 14px;
            border: 2px solid #e74c3c;
            background: white;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            background: #e74c3c;
            color: white;
        }
        
        .loading-text { 
            font-size: 11px;
            color: #2ecc71;
            display: none;
            margin-left: 8px;
            font-weight: 600;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        input[type="file"] {
            padding: 10px;
            font-size: 13px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }
            
            .form-card {
                padding: 25px 20px;
            }
            
            .page-title h1 {
                font-size: 26px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-title">
        <h1>🔧 Customer Registration Form</h1>
        <p>Register new customer and service details</p>
    </div>
    
    <div class="form-card">
        <form action="save_jobs.php" method="POST" enctype="multipart/form-data">
            
            <!-- Customer Information Section -->
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

            <!-- Job Assignment Section -->
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

            <!-- Devices Section -->
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
            <div class="device-header">
                <div class="device-title">
                    <span>📱</span>
                    <span>Device #${deviceCount}</span>
                </div>
                ${deviceCount > 1 ? `<button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()">✕ Remove</button>` : ''}
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Device Type</label>
                    <select name="devices[]" required>
                        <option value="">-- Select Device --</option>
                        <option value="Mobile">Mobile Phone</option>
                        <option value="Printer">Printer</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Desktop">Desktop PC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Issue Type</label>
                    <select name="issues[]" required>
                        <option value="">-- Select Issue --</option>
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
                        <option value="No Warranty">No Warranty</option>
                        <option value="Warranty"> Warranty</option>
                    </select>
                </div>
            </div>

            <div class="form-grid" style="margin-top:20px;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Description / Conditions / Note</label>
                    <textarea name="descriptions[]" placeholder="e.g. Back cover broken, No SIM tray, Battery swollen..."></textarea>
                </div>
                <div class="form-group">
                    <label>Device Image (Optional)</label>
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