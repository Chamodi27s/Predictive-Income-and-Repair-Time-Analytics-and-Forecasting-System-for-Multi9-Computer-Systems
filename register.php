<?php 
include 'db_config.php'; 
include 'navbar.php'; 
$job_no = "ORD-" . rand(1000, 9999);
$today = date('Y-m-d'); // අද දිනය ලබා ගැනීම
?>
<!DOCTYPE html>
<html>
<head>
    <title>Job Registration</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 12px; display: flex; gap: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .section { flex: 1; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-save { background: #27ae60; color: white; padding: 15px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 18px; margin-top: 20px; font-weight: bold; }
        .btn-add { background: #2980b9; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 10px; }
        .device-card { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 5px solid #2980b9; position: relative; }
        .remove-btn { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; float: right; }
    </style>
</head>
<body>
    <h2 style="text-align: center; color: #34495e;">Multi-Device Service Registration</h2>
    <form action="save_jobs.php" method="POST">
        <div class="form-container">
            <div class="section">
                <h3>Customer Details</h3>
                Phone: <input type="text" name="phone_number" required>
                Customer Name: <input type="text" name="customer_name" required>
                Address: <textarea name="address" rows="3"></textarea>
            </div>
            <div class="section">
                <h3>Job & Device Details</h3>
                Job No: <input type="text" name="job_no" value="<?php echo $job_no; ?>" readonly>
                
                Job Date: <input type="date" name="job_date" value="<?php echo $today; ?>" required>

                <div id="device-list">
                    <div class="device-card">
                        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">X</button>
                        <input type="text" name="devices[]" placeholder="Device (e.g. Laptop)" required>
                        <input type="text" name="models[]" placeholder="Model/Serial">
                        <input type="text" name="issues[]" placeholder="Issue (e.g. No Power)" required>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addMore()">+ Add Another Device</button>
                <br>Technician: <input type="text" name="technician">
            </div>
        </div>
        <button type="submit" class="btn-save">Register Job</button>
    </form>
    <script>
        function addMore() {
            const div = document.createElement('div');
            div.className = 'device-card';
            div.innerHTML = `<button type="button" class="remove-btn" onclick="this.parentElement.remove()">X</button>
                             <input type="text" name="devices[]" placeholder="Device" required>
                             <input type="text" name="models[]" placeholder="Model/Serial">
                             <input type="text" name="issues[]" placeholder="Issue" required>`;
            document.getElementById('device-list').appendChild(div);
        }
    </script>
</body>
</html>