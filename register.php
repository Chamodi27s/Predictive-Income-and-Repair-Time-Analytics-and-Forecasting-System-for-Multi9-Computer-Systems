<?php
include 'db_config.php';
include 'navbar.php';

$job_no = "ORD-" . rand(1000, 9999);
$today = date('Y-m-d');

/* Example technician list (later DB eken ganna puluwan) */
$technicians = ["Kamal", "Nimal", "Saman"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

    

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            text-align: center;
            color: #1f2937;
            margin-bottom: 30px;
            animation: fadeIn 0.6s ease-out;
        }

        .header h1 {
            font-size: clamp(24px, 5vw, 36px);
            font-weight: 700;
            margin-bottom: 8px;
            color: #059669;
        }

        .header p {
            font-size: clamp(14px, 2.5vw, 16px);
            color: #6b7280;
        }

        .form-card {
            background: white;
            border-radius: 20px;
            padding: clamp(20px, 4vw, 40px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            animation: slideUp 0.6s ease-out;
        }

        .section {
            margin-bottom: 35px;
        }

        .section-title {
            font-size: clamp(18px, 3vw, 22px);
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #10b981;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 6px;
            height: 24px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 3px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        input, select, textarea {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .device-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%);
            border: 2px solid #a7f3d0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            animation: slideIn 0.4s ease-out;
        }

        .device-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .device-number {
            font-size: 16px;
            font-weight: 700;
            color: #059669;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .device-number::before {
            content: '📱';
            font-size: 20px;
        }

        .remove-device {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .remove-device:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .warranty-toggle {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .warranty-option {
            flex: 1;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            font-weight: 600;
        }

        .warranty-option:hover {
            border-color: #10b981;
            transform: translateY(-2px);
        }

        .warranty-option.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-color: #10b981;
        }

        .image-upload {
            border: 2px dashed #cbd5e0;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .image-upload:hover {
            border-color: #10b981;
            background: #f7fafc;
        }

        .image-upload input {
            display: none;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
        }

        .btn-secondary {
            background: white;
            color: #10b981;
            border: 2px solid #10b981;
        }

        .btn-secondary:hover {
            background: #10b981;
            color: white;
            transform: translateY(-2px);
        }

        .add-device-btn {
            width: 100%;
            margin-top: 10px;
        }

        .technician-section {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .technician-section .form-group {
            flex: 1;
            min-width: 200px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .device-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .remove-device {
                width: 100%;
            }
        }
    </style>
</head>
<body>
   

    <div class="container">
        <div class="header">
            <h1>🔧 Multi-Device Service Registration</h1>
            <p>Register new service jobs with complete customer and device information</p>
        </div>

        <div class="form-card">
            <form id="serviceForm" action="save_jobs.php" method="POST" enctype="multipart/form-data">
                <!-- Customer Details Section -->
                <div class="section">
                    <div class="section-title">Customer Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone_number" placeholder="+94 XX XXX XXXX" required>
                        </div>
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" name="customer_name" placeholder="Enter full name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="customer@example.com">
                        </div>
                        <div class="form-group full-width">
                            <label>Address</label>
                            <textarea name="address" rows="2" placeholder="Customer address..."></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label>Description / Notes</label>
                            <textarea name="admin_notes" placeholder="Additional information about the customer or service..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Job Details Section -->
                <div class="section">
                    <div class="section-title">Job Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Job No</label>
                            <input type="text" name="job_no" value="<?= $job_no ?>" placeholder="Auto-generated" readonly style="background: #e2e8f0;">
                        </div>
                        <div class="form-group">
                            <label>Job Date</label>
                            <input type="date" name="job_date" value="<?= $today ?>" required>
                        </div>
                        <div class="form-group full-width">
                            <label>Technician</label>
                            <div class="technician-section">
                                <div class="form-group" style="margin: 0;">
                                    <select name="technician" id="technicianSelect" required>
                                        <option value="">Select Technician</option>
                                        <?php foreach($technicians as $t){ ?>
                                        <option value="<?= $t ?>"><?= $t ?></option>
                                        <?php } ?>
                                        <option value="new">➕ Add New Technician</option>
                                    </select>
                                </div>
                                <input type="text" name="new_technician" id="newTech" 
                                       placeholder="Enter new technician name" 
                                       style="display:none; flex: 1; min-width: 200px; margin: 0;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Devices Section -->
                <div class="section">
                    <div class="section-title">Device Information</div>
                    <div id="devicesContainer">
                        <!-- Device cards will be added here -->
                    </div>
                    <button type="button" class="btn btn-secondary add-device-btn" onclick="addDevice()">
                        ➕ Add Another Device
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">✓ Register Job</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let deviceCount = 0;

        function addDevice() {
            deviceCount++;
            const container = document.getElementById('devicesContainer');
            const deviceCard = document.createElement('div');
            deviceCard.className = 'device-card';
            deviceCard.innerHTML = `
                <div class="device-header">
                    <div class="device-number">Device #${deviceCount}</div>
                    ${deviceCount > 1 ? '<button type="button" class="remove-device" onclick="removeDevice(this)">✕ Remove</button>' : ''}
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Device Type</label>
                        <select name="devices[]" required>
                            <option value="">-- Select Device --</option>
                            <option value="mobile">📱 Mobile</option>
                            <option value="laptop">💻 Laptop</option>
                            <option value="tablet">📱 Tablet</option>
                            <option value="printer">🖨️ Printer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Issue</label>
                        <select name="issues[]" required>
                            <option value="">-- Select Issue --</option>
                            <option value="screen">🔲 Screen Damage</option>
                            <option value="battery">🔋 Battery Issue</option>
                            <option value="power">⚡ No Power</option>
                            <option value="water">💧 Water Damage</option>
                            <option value="software">💿 Software Issue</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Warranty</label>
                        <div class="warranty-toggle">
                            <div class="warranty-option" onclick="toggleWarranty(this, 'no')">No</div>
                            <div class="warranty-option" onclick="toggleWarranty(this, 'yes')">Yes</div>
                        </div>
                        <input type="hidden" name="warranty[]" class="warranty-value" value="">
                    </div>
                    <div class="form-group full-width">
                        <label>Capture Device Image (Optional)</label>
                        <label class="image-upload">
                            <input type="file" name="device_images[]" accept="image/*" capture="environment" onchange="handleImageUpload(this)">
                            <div class="upload-icon">📷</div>
                            <div>Click to upload device image</div>
                            <div style="font-size: 12px; color: #718096; margin-top: 5px;">PNG, JPG up to 5MB</div>
                        </label>
                    </div>
                </div>
            `;
            container.appendChild(deviceCard);
        }

        function removeDevice(btn) {
            btn.closest('.device-card').remove();
            updateDeviceNumbers();
        }

        function updateDeviceNumbers() {
            const devices = document.querySelectorAll('.device-number');
            devices.forEach((device, index) => {
                device.textContent = `Device #${index + 1}`;
            });
        }

        function toggleWarranty(element, value) {
            const options = element.parentElement.querySelectorAll('.warranty-option');
            options.forEach(opt => opt.classList.remove('active'));
            element.classList.add('active');
            element.parentElement.nextElementSibling.value = value;
        }

        function handleImageUpload(input) {
            if (input.files && input.files[0]) {
                const label = input.parentElement;
                label.innerHTML = `
                    <div class="upload-icon">✓</div>
                    <div style="color: #10b981; font-weight: 600;">Image uploaded successfully</div>
                    <div style="font-size: 12px; color: #718096; margin-top: 5px;">${input.files[0].name}</div>
                `;
            }
        }

        document.getElementById('serviceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Form will submit to save_jobs.php via POST
            this.submit();
        });

        // Technician add new functionality
        document.getElementById("technicianSelect").addEventListener("change", function() {
            document.getElementById("newTech").style.display = 
                (this.value === "new") ? "block" : "none";
        });

        // Add first device on load
        addDevice();
    </script>
</body>
</html>