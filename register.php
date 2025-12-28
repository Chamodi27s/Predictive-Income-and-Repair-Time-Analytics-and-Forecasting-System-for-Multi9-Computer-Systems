<?php
include 'db_config.php';
include 'navbar.php';

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root { 
            --primary: #059669;
            --primary-hover: #047857;
            --secondary: #3b82f6;
            --bg: #f0f2f5;
            --card-bg: white;
            --text-dark: #374151;
            --border: #d1d5db;
            --success: #10b981;
            --danger: #ef4444;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg);
            padding: 20px;
            color: var(--text-dark);
        }
        
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .page-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        .form-card { 
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .section { 
            margin-bottom: 35px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .section-header::before {
            content: '';
            width: 4px;
            height: 24px;
            background: var(--primary);
            margin-right: 12px;
            border-radius: 2px;
        }
        
        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 18px; 
        }
        
        .form-group { 
            display: flex; 
            flex-direction: column;
        }
        
        label { 
            font-weight: 600; 
            margin-bottom: 8px; 
            font-size: 0.875rem; 
            color: var(--text-dark);
        }
        
        label .required {
            color: var(--danger);
            margin-left: 2px;
        }
        
        input, select, textarea { 
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            outline: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        input:focus, select:focus, textarea:focus { 
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        input[readonly] {
            background: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .device-card { 
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 2px solid #a7f3d0;
            padding: 25px;
            border-radius: 14px;
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .device-card:hover {
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.15);
        }
        
        .device-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .device-number {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary);
        }
        
        .remove-btn { 
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .upload-area:hover {
            border-color: var(--primary);
            background: #f0fdf4;
        }
        
        .upload-area.has-image {
            border-style: solid;
            border-color: var(--success);
        }
        
        .upload-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #9ca3af;
        }
        
        .upload-text {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .upload-subtext {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            margin: 10px auto;
            display: none;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .btn-add { 
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 14px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-add:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        
        .btn-primary { 
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 10px;
            width: 100%;
            cursor: pointer;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }
        
        .loading-text { 
            font-size: 0.75rem;
            color: var(--secondary);
            display: none;
            font-weight: 600;
        }
        
        .description-group {
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .form-card {
                padding: 25px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>🔧Customer Registration Form</h1>
        <p>Complete the form below to register a new service request</p>
    </div>
    
    <div class="form-card">
        <form action="save_jobs.php" method="POST" enctype="multipart/form-data">
            
            <!-- Customer Information Section -->
            <div class="section">
                <div class="section-header">
                    👤 Customer Information
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            Phone Number <span class="required">*</span>
                            <span id="searching" class="loading-text">(Searching...)</span>
                        </label>
                        <input type="text" name="phone_number" id="customer_phone" placeholder="07xxxxxxxx" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Customer Name <span class="required">*</span></label>
                        <input type="text" name="customer_name" id="customer_name" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="customer_email" placeholder="customer@example.com">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="customer_address" placeholder="Enter customer address">
                    </div>
                </div>
            </div>

            <!-- Job Details Section -->
            <div class="section">
                <div class="section-header">
                    📋 Job Details
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Job Number (Auto-Generated)</label>
                        <input type="text" name="job_no" value="<?= $job_no ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Assign Technician <span class="required">*</span></label>
                        <select name="technician_id" id="techSelect" required>
                            <option value="">-- Select Technician --</option>
                            <?php while($t = mysqli_fetch_assoc($tech_result)) { ?>
                                <option value="<?= $t['technician_id'] ?>"><?= $t['name'] ?></option>
                            <?php } ?>
                            <option value="new" style="color:#059669; font-weight:600;">+ Add New Technician</option>
                        </select>
                    </div>
                    <div class="form-group" id="newTechContainer" style="display:none; grid-column: 1 / -1;">
                        <label>New Technician Name <span class="required">*</span></label>
                        <input type="text" name="new_technician" id="newTech" placeholder="Enter technician name">
                    </div>
                </div>
            </div>

            <!-- Devices Section -->
            <div class="section">
                <div class="section-header">
                    📱 Devices & Service Details
                </div>
                <div id="devicesContainer"></div>
                <button type="button" class="btn-add" onclick="addDevice()">
                    <span style="font-size: 1.2rem;">+</span> Add Another Device
                </button>
            </div>

            <button type="submit" class="btn-primary">✓ Complete Registration</button>
        </form>
    </div>
</div>

<script>
    // Customer Auto-fill logic
    document.getElementById('customer_phone').addEventListener('keyup', function() {
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
            });
        }
    });

    // Technician dropdown handler
    document.getElementById('techSelect').addEventListener('change', function() {
        document.getElementById('newTechContainer').style.display = (this.value === 'new') ? 'block' : 'none';
        document.getElementById('newTech').required = (this.value === 'new');
    });

    // Device management
    let deviceCount = 0;
    
    function addDevice() {
        deviceCount++;
        const container = document.getElementById('devicesContainer');
        const html = `
            <div class="device-card" id="device-row-${deviceCount}">
                <div class="device-header">
                    <div class="device-number">📱 Device #${deviceCount}</div>
                    ${deviceCount > 1 ? `<button type="button" class="remove-btn" onclick="removeDevice(${deviceCount})">✕ Remove</button>` : ''}
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Device Type <span class="required">*</span></label>
                        <select name="devices[]" required>
                            <option value="">-- Select Device --</option>
                            <option value="Mobile">📱 Mobile Phone</option>
                            <option value="Printer">🖨️ Printer</option>
                            <option value="Desktop">🖥️ Desktop PC</option>
                            <option value="Laptop">💻 Laptop</option>
                            <option value="Tablet">📲 Tablet</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Issue Type <span class="required">*</span></label>
                        <select name="issues[]" required>
                            <option value="">-- Select Issue --</option>
                            <option value="Display">🔳 Display Damage</option>
                            <option value="Power">⚡ Power Issue</option>
                            <option value="Service">🔧 Full Service</option>
                            <option value="Software">💿 Software/OS</option>
                            <option value="Battery">🔋 Battery Problem</option>
                            <option value="Hardware">⚙️ Hardware Fault</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Warranty Status <span class="required">*</span></label>
                        <select name="warranty_status[]" required>
                            <option value="">-- Select Status --</option>
                            <option value="Non-Warranty">❌ Non-Warranty</option>
                            <option value="Warranty">✅ Under Warranty</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group description-group">
                    <label>Device Description / Additional Notes</label>
                    <textarea name="descriptions[]" placeholder="Enter device model, serial number, condition, or any additional details about the issue..." rows="3"></textarea>
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label>Device Image (Optional)</label>
                    <div class="upload-area" id="uploadArea${deviceCount}" onclick="document.getElementById('fileInput${deviceCount}').click()">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Click to upload device photo</div>
                        <div class="upload-subtext">PNG, JPG, JPEG (Max 5MB)</div>
                        <img id="preview${deviceCount}" class="image-preview" alt="Preview">
                    </div>
                    <input type="file" id="fileInput${deviceCount}" name="device_images[]" accept="image/*" onchange="previewImage(${deviceCount})">
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
    
    function removeDevice(id) {
        const element = document.getElementById('device-row-' + id);
        if(element) {
            element.remove();
        }
    }
    
    function previewImage(id) {
        const input = document.getElementById('fileInput' + id);
        const preview = document.getElementById('preview' + id);
        const uploadArea = document.getElementById('uploadArea' + id);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                uploadArea.classList.add('has-image');
                uploadArea.querySelector('.upload-icon').style.display = 'none';
                uploadArea.querySelector('.upload-text').textContent = '✓ Image uploaded - Click to change';
                uploadArea.querySelector('.upload-subtext').style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Add first device on page load
    addDevice();
</script>

</body>
</html>