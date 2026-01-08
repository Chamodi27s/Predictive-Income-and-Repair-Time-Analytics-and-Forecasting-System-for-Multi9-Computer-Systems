<?php
include 'db_config.php';
include 'navbar.php';

// URL එකෙන් Job No එක ලබා ගැනීම
$job_no = isset($_GET['job_no']) ? mysqli_real_escape_string($conn, $_GET['job_no']) : '';

if (!$job_no) {
    echo "<div style='padding:100px; text-align:center;'><h2>Job Number Not Found!</h2><a href='job_list.php'>Go Back</a></div>";
    exit();
}

// ඩේටාබේස් එකෙන් දත්ත ලබා ගැනීම (Auto-retrieval)
$query = "SELECT j.job_no, j.job_date, jd.device_name, jd.issue_name, t.name as tech_name, jd.warranty_status
          FROM job j
          JOIN job_device jd ON j.job_no = jd.job_no
          LEFT JOIN technicians t ON j.technician_id = t.technician_id
          WHERE j.job_no = '$job_no' LIMIT 1";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

$predicted_time = "";

if (isset($_POST['predict'])) {
    $exp = (int)$_POST['experience'];
    $workload = $_POST['workload'];
    
    // Prediction Logic (Model)
    $days = 1; 
    if ($exp < 3) $days += 2;
    if ($workload == 'High') $days += 3;
    if ($data['warranty_status'] == 'Warranty') $days += 5;

    $predicted_time = $days . " Working Days";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Time Prediction</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2ecc71; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); padding-top: 140px; padding-left: 20px; padding-right: 20px; }
        .predict-card { max-width: 600px; margin: auto; background: var(--card); padding: 30px; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; background: #f1f5f9; padding: 15px; border-radius: 8px; }
        .info-item label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .info-item p { margin: 0; font-weight: 600; font-size: 14px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        select, input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--border); border-radius: 8px; }
        .btn-predict { background: var(--primary); color: white; width: 100%; border: none; padding: 15px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .result-box { margin-top: 25px; padding: 20px; background: #dcfce7; border: 2px dashed #22c55e; border-radius: 12px; text-align: center; }
    </style>
</head>
<body>

<div class="predict-card">
    <h2 style="margin-bottom: 20px;">⏱️ Repair Time Prediction</h2>
    
    <div class="info-grid">
        <div class="info-item"><label>Job No</label><p><?= $data['job_no'] ?></p></div>
        <div class="info-item"><label>Date</label><p><?= $data['job_date'] ?></p></div>
        <div class="info-item"><label>Device</label><p><?= $data['device_name'] ?></p></div>
        <div class="info-item"><label>Issue</label><p><?= $data['issue_name'] ?></p></div>
        <div class="info-item"><label>Technician</label><p><?= $data['tech_name'] ?? 'Unassigned' ?></p></div>
        <div class="info-item"><label>Warranty</label><p><?= $data['warranty_status'] ?></p></div>
    </div>

    <form method="POST">
        <label>Technician Experience (Years)</label>
        <input type="number" name="experience" required placeholder="e.g. 5">

        <label>Workshop Workload</label>
        <select name="workload" required>
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High</option>
        </select>

        <button type="submit" name="predict" class="btn-predict">PREDICT TIME</button>
    </form>

    <?php if ($predicted_time): ?>
        <div class="result-box">
            <p>Estimated Time:</p>
            <h2 style="color: #166534;"><?= $predicted_time ?></h2>
        </div>
    <?php endif; ?>
</div>

</body>
</html>