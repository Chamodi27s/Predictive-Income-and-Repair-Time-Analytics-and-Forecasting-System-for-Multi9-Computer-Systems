<?php
include 'db_config.php';
include 'navbar.php';

// 1. Job No ලබා ගැනීම
$job_no = isset($_GET['job_no']) ? mysqli_real_escape_string($conn, $_GET['job_no']) : '';

// දත්ත ගබඩාවෙන් Job එකට අදාළ පොදු විස්තර ලබා ගැනීම
$job_data = null;
if ($job_no) {
    $query = "SELECT j.job_no, j.job_date, t.name as tech_name 
              FROM job j 
              LEFT JOIN technicians t ON j.technician_id = t.technician_id 
              WHERE j.job_no = '$job_no' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $job_data = mysqli_fetch_assoc($result);
}

$predicted_warranty = "";
$predicted_non_warranty = "";

if (isset($_POST['predict']) && $job_no) {
    $exp = (int)$_POST['experience'];
    $workload = $_POST['workload'];
    
    // Warranty දින ගණනය (Base: 5 days + workload)
    $w_days = 5;
    if ($exp < 3) $w_days += 2;
    if ($workload == 'High') $w_days += 3;
    $predicted_warranty = $w_days . " Working Days";

    // Non-Warranty දින ගණනය (Base: 1 day + workload)
    $nw_days = 1;
    if ($exp < 3) $nw_days += 2;
    if ($workload == 'High') $nw_days += 3;
    $predicted_non_warranty = $nw_days . " Working Days";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Time Prediction</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2ecc71; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --blue: #4361ee; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); padding-top: 140px; padding-left: 20px; padding-right: 20px; }
        .predict-card { max-width: 700px; margin: auto; background: var(--card); padding: 30px; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; background: #f1f5f9; padding: 15px; border-radius: 8px; }
        .info-item label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .info-item p { margin: 0; font-weight: 600; font-size: 14px; }
        .device-list { margin-bottom: 20px; padding: 10px; border-left: 4px solid var(--blue); background: #f8faff; }
        .device-item { font-size: 13px; margin-bottom: 5px; }
        .badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; color: white; }
        .bg-w { background: #2ecc71; } .bg-nw { background: #e63946; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        select, input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--border); border-radius: 8px; }
        .btn-predict { background: var(--blue); color: white; width: 100%; border: none; padding: 15px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .result-container { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 25px; }
        .result-box { padding: 15px; border-radius: 12px; text-align: center; border: 2px dashed; }
        .w-box { background: #dcfce7; border-color: #2ecc71; color: #166534; }
        .nw-box { background: #fee2e2; border-color: #e63946; color: #991b1b; }
    </style>
</head>
<body>

<div class="predict-card">
    <h2 style="margin-bottom: 20px;">⏱️ Repair Time Prediction</h2>
    
    <?php if ($job_data): ?>
    <div class="info-grid">
        <div class="info-item"><label>Job No</label><p><?= $job_data['job_no'] ?></p></div>
        <div class="info-item"><label>Date</label><p><?= $job_data['job_date'] ?></p></div>
        <div class="info-item"><label>Technician</label><p><?= $job_data['tech_name'] ?? 'Unassigned' ?></p></div>
    </div>

    <div class="device-list">
        <label>Devices in this Job:</label>
        <?php
        $dev_res = mysqli_query($conn, "SELECT device_name, warranty_status FROM job_device WHERE job_no = '$job_no'");
        while($dev = mysqli_fetch_assoc($dev_res)):
            $b_class = ($dev['warranty_status'] == 'Warranty') ? 'bg-w' : 'bg-nw';
        ?>
            <div class="device-item">
                📱 <?= htmlspecialchars($dev['device_name']) ?> 
                <span class="badge <?= $b_class ?>"><?= $dev['warranty_status'] ?></span>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
        <p style="text-align:center; color:#64748b;">Please select a job from the dashboard to predict time.</p>
    <?php endif; ?>

    <form method="POST">
        <label>Technician Experience (Years)</label>
        <input type="number" name="experience" required placeholder="e.g. 5">

        <label>Workshop Workload</label>
        <select name="workload" required>
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High</option>
        </select>

        <button type="submit" name="predict" class="btn-predict" <?= !$job_no ? 'disabled' : '' ?>>PREDICT TIME</button>
    </form>

    <?php if ($predicted_warranty || $predicted_non_warranty): ?>
        <div class="result-container">
            <div class="result-box w-box">
                <small>Warranty Devices</small>
                <h3><?= $predicted_warranty ?></h3>
            </div>
            <div class="result-box nw-box">
                <small>Non-Warranty Devices</small>
                <h3><?= $predicted_non_warranty ?></h3>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>