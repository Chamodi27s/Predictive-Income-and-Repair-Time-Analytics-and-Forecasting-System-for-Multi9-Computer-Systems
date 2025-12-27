<?php include 'db_config.php'; 
include 'navbar.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration - Multi 9</title>
    <style>
        body { font-family: sans-serif; background: #e8f5e9; padding: 20px; }
        .form-container { background: white; padding: 25px; border-radius: 12px; display: flex; gap: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; }
        .btn { background: #2e7d32; color: white; padding: 12px 25px; border: none; cursor: pointer; border-radius: 5px; width: 100%; }
    </style>
</head>
<body>
    <form action="save_jobs.php" method="POST">
        <div class="form-container">
            <div style="flex: 1;">
                <h3>Customer Details</h3>
                Phone Number (PK): <input type="text" name="phone_number" required>
                Customer Name: <input type="text" name="customer_name" required>
                Address: <textarea name="address"></textarea>
                Email: <input type="email" name="email">
            </div>
            <div style="flex: 1;">
                <h3>Job Details</h3>
                Job No: <input type="text" name="job_no" value="ORD-<?php echo rand(1000, 9999); ?>" readonly>
                Device Name: <input type="text" name="device_name" required placeholder="Ex: Laptop / Printer">
                Model & Serial: <input type="text" name="model" placeholder="Model No">
                Issue Name: <input type="text" name="issue_name" placeholder="Ex: Display Cracked">
                Technician: <input type="text" name="technician">
            </div>
        </div>
        <button type="submit" class="btn" style="margin-top: 15px;">Save & Register Job</button>
    </form>
</body>
</html>