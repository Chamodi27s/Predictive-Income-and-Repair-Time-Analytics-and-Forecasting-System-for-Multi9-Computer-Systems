<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi9 | AI Repair Predictor</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            --primary-color: #27ae60;
            --bg-color: #f0f4f8;
            --card-bg: #ffffff;
            --text-main: #2c3e50;
            --text-muted: #7f8c8d;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            background-image: radial-gradient(#2ecc7115 1px, transparent 1px);
            background-size: 20px 20px;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            background: var(--card-bg);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(39, 174, 96, 0.15);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header .icon-box {
            width: 60px; height: 60px; background: var(--primary-gradient);
            border-radius: 18px; display: flex; justify-content: center;
            align-items: center; margin: 0 auto 15px;
        }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 8px; color: #34495e; text-transform: uppercase; }

        input, select {
            width: 100%; padding: 12px 12px 12px 40px;
            border: 2px solid #edf2f7; border-radius: 12px;
            font-size: 14px; outline: none; box-sizing: border-box;
        }

        button {
            width: 100%; padding: 15px; background: var(--primary-gradient);
            color: white; border: none; border-radius: 12px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            margin-top: 10px; transition: transform 0.2s;
        }

        button:hover { transform: translateY(-2px); }

        .result-box {
            margin-top: 25px; padding: 20px;
            background: #f0fff4; border-radius: 18px;
            border: 2px dashed #2ecc71; text-align: center;
        }

        .stats-container {
            margin-top: 25px; padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .stat-badge {
            flex: 1; background: #f8fafc; padding: 10px;
            border-radius: 12px; text-align: center;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <div class="icon-box"><i class="fas fa-bolt-lightning" style="color:white; font-size: 24px;"></i></div>
        <h2>Multi9 Predictor</h2>
        <p>AI-Powered Repair Analytics</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label>Device Category</label>
            <input type="text" name="device" placeholder="Laptop" required>
        </div>
        <div class="form-group">
            <label>Fault</label>
            <input type="text" name="fault" placeholder="Display issue" required>
        </div>
        <div class="form-group">
            <label>Technician</label>
            <input type="text" name="tech" placeholder="Kasun" required>
        </div>
        <div style="display: flex; gap: 10px;">
            <select name="path" style="flex:1;"><option value="In-House">In-House</option><option value="Agent">Agent</option></select>
            <select name="warranty" style="flex:1;"><option value="No">No</option><option value="Yes">Yes</option></select>
        </div>
        <button type="submit" name="predict">Calculate Forecast</button>
    </form>

    <?php
    if (isset($_POST['predict'])) {
        $cmd = "python bridge.py \"{$_POST['device']}\" \"{$_POST['fault']}\" \"{$_POST['tech']}\" \"{$_POST['path']}\" \"{$_POST['warranty']}\" 2>&1";
        $output = shell_exec($cmd);

        if($output && is_numeric(trim($output))) {
            $days = floatval(trim($output));
            $hours = round($days * 24, 1);
            $ready = date('M d, h:i A', strtotime("+".round($hours)." hours"));
            
            echo "<div class='result-box'>";
            echo "<h4>Estimated Delivery</h4>";
            echo "<div style='font-size:32px; font-weight:800; color:#1a2a1f;'>".($days < 1 ? "$hours Hours" : "$days Days")."</div>";
            echo "<div style='color:#27ae60; font-weight:600;'>Ready by: $ready</div>";
            echo "</div>";
        }
    }

    // AI Stats Load කිරීම
    $stats = json_decode(@file_get_contents('model_stats.json'), true);
    $acc = $stats['accuracy'] ?? '85.0';
    $mae = $stats['mae'] ?? '0.8';
    ?>

    <div class="stats-container">
        <p style="font-size: 11px; font-weight: 700; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase;">
            <i class="fas fa-microchip"></i> AI Engine Health
        </p>
        <div style="display: flex; gap: 10px;">
            <div class="stat-badge">
                <div style="font-size: 9px; color: #94a3b8;">ACCURACY</div>
                <div style="font-size: 14px; font-weight: 700; color: #2ecc71;"><?php echo $acc; ?>%</div>
            </div>
            <div class="stat-badge">
                <div style="font-size: 9px; color: #94a3b8;">AVG. ERROR</div>
                <div style="font-size: 14px; font-weight: 700; color: #e67e22;">±<?php echo $mae; ?>d</div>
            </div>
            <div class="stat-badge">
                <div style="font-size: 9px; color: #94a3b8;">STATUS</div>
                <div style="font-size: 14px; font-weight: 700; color: #3498db;">Active</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>