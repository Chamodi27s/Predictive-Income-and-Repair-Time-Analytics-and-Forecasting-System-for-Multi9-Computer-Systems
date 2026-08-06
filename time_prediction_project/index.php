<?php
$job_no = isset($_GET['job_no']) ? $_GET['job_no'] : '';
$device_type = '';
$item_model = '';
$fault_description = '';

// If job_no is provided, fetch defaults from database (optional, but good for UX)
if ($job_no) {
    include '../db_config.php';
    if(isset($conn)) {
        $q = mysqli_query($conn, "SELECT * FROM job_device WHERE job_no = '$job_no' LIMIT 1");
        if($row = mysqli_fetch_assoc($q)) {
            $device_type = $row['device_name'] ?? '';
            $item_model = $row['device_name'] ?? ''; // Using device_name as fallback
            $fault_description = $row['issue_name'] ?? '';
        }
    }
}

$prediction_result = null;
$error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device_type = $_POST['device_type'] ?? '';
    $item_model = $_POST['item_model'] ?? '';
    $fault_description = $_POST['fault_description'] ?? '';
    
    // Prepare data with hardcoded defaults for the other 4 fields
    $payload = [
        "Device_Type" => $device_type,
        "Item_Model" => $item_model,
        "Fault_Description" => $fault_description,
        "Technician" => "Default Tech",
        "Repair_Path" => "Carry-In",
        "Warranty" => "No",
        "Solution" => "Pending Diagnosis"
    ];
    
    // Call predict_api.py
    $json_payload = json_encode($payload);
    
    // Check if python is accessible
    $command = "python predict_api.py";
    $process = proc_open($command, [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ], $pipes);
    
    if (is_resource($process)) {
        fwrite($pipes[0], $json_payload);
        fclose($pipes[0]);
        
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        $return_value = proc_close($process);
        
        if ($return_value === 0 && is_numeric(trim($output))) {
            $prediction_result = trim($output);
        } else {
            $error_msg = "Prediction Failed. Error: " . htmlspecialchars($error . $output);
        }
    } else {
        $error_msg = "Could not execute Python script.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Repair Time Prediction</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #1aa353;
            --primary-green-dark: #1aa353;
            --dark-bg: #0f172a;
            --dark-surface: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            color: var(--text-light);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: radial-gradient(circle at 50% -20%, #1aa35320, transparent 40%);
        }

        .container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .header i {
            font-size: 48px;
            color: var(--primary-green);
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(26, 163, 83, 0.5));
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .header p {
            color: var(--text-muted);
            margin-top: 8px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(26, 163, 83, 0.15);
        }

        .btn-predict {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark));
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(26, 163, 83, 0.4);
        }

        .btn-predict:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(26, 163, 83, 0.5);
        }
        
        .btn-back {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .btn-back:hover {
            color: white;
        }

        .result-card {
            background: rgba(26, 163, 83, 0.1);
            border: 1px solid rgba(26, 163, 83, 0.3);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            margin-bottom: 30px;
            animation: slideDown 0.4s ease-out;
        }

        .result-card h3 {
            margin: 0 0 5px 0;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .result-card .days {
            font-size: 42px;
            font-weight: 800;
            color: var(--primary-green);
            margin: 0;
            text-shadow: 0 0 20px rgba(26, 163, 83, 0.4);
        }
        
        .error-card {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.5;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="glass-card">
        <div class="header">
            <i class="ph-fill ph-brain"></i>
            <h1>AI Time Prediction</h1>
            <p>Enter device details to estimate repair duration</p>
        </div>

        <?php if ($prediction_result !== null): ?>
            <div class="result-card">
                <h3>Estimated Repair Time</h3>
                <div class="days"><?= htmlspecialchars($prediction_result) ?> Days</div>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg !== null): ?>
            <div class="error-card">
                <i class="ph ph-warning"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Device Type</label>
                <input type="text" name="device_type" class="form-control" placeholder="e.g., Laptop, Desktop PC" value="<?= htmlspecialchars($device_type) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Item Model</label>
                <input type="text" name="item_model" class="form-control" placeholder="e.g., Dell XPS 15" value="<?= htmlspecialchars($item_model) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Fault Description</label>
                <textarea name="fault_description" class="form-control" rows="3" placeholder="e.g., No Power, Broken Screen" required><?= htmlspecialchars($fault_description) ?></textarea>
            </div>

            <button type="submit" class="btn-predict">
                <i class="ph-bold ph-magic-wand"></i> Calculate Prediction
            </button>
        </form>
        
        <a href="../add_customer.php" class="btn-back">
            <i class="ph ph-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>
