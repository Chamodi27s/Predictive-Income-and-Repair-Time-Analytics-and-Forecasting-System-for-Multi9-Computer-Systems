<?php
include 'db_config.php';
include 'navbar.php';

define('ML_API', 'http://localhost:5001');

// ── Helper: call Flask API ────────────────────────────────────────────────────
function call_ml_api(string $endpoint, array $payload = [], string $method = 'GET') {
    $ch = curl_init(ML_API . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['success' => false, 'error' => "cURL: $err"];
    return json_decode($raw, true) ?? ['success' => false, 'error' => 'Bad JSON'];
}

// ── Check API alive ───────────────────────────────────────────────────────────
$health    = call_ml_api('/health');
$api_alive = !empty($health['status']) && $health['status'] === 'ok';

// ── Load job data using ACTUAL column names from your DB ──────────────────────
$job_no   = isset($_GET['job_no']) ? mysqli_real_escape_string($conn, $_GET['job_no']) : '';
$job_data = null;
$devices  = [];

if ($job_no) {
    // job table: job_no, job_date, phone_number, technician_id
    // technicians table: technician_id, name
    // NO fault_description column in job — using description from job_device instead
    $q = "SELECT j.job_no, j.job_date, t.name AS tech_name
          FROM job j
          LEFT JOIN technicians t ON j.technician_id = t.technician_id
          WHERE j.job_no = '$job_no' LIMIT 1";
    $job_data = mysqli_fetch_assoc(mysqli_query($conn, $q));

    // job_device table actual columns: device_name, issue_name, warranty_status, solution, description
    $dr = mysqli_query($conn, "SELECT device_name, issue_name, warranty_status, solution, description
                                FROM job_device
                                WHERE job_no = '$job_no'");
    while ($row = mysqli_fetch_assoc($dr)) {
        $devices[] = $row;
    }
}

// ── Prediction results ────────────────────────────────────────────────────────
$predicted_warranty     = "";
$predicted_non_warranty = "";
$warranty_date          = "";
$non_warranty_date      = "";
$predict_err            = "";

if (isset($_POST['predict']) && $job_no && $job_data) {

    if (!$api_alive) {
        $predict_err = " ML API is offline. Run: cd model && python predict_api.py";
    } else {
        $date_in = $job_data['job_date'];
        $warranty_days     = [];
        $non_warranty_days = [];

        foreach ($devices as $dev) {
            // Map your actual DB columns to what the ML model expects:
            // device_name  → device_type  (closest match)
            // issue_name   → fault_description
            // description  → also part of fault_description (appended)
            // warranty_status → warranty
            // solution     → solution
            // tech_name    → technician
            $fault = trim(($dev['issue_name'] ?? '') . ' ' . ($dev['description'] ?? ''));

            $payload = [
                'fault_description' => $fault,
                'device_type'       => $dev['device_name']      ?? '',
                'item_model'        => $dev['device_name']      ?? '',  // no item_model column, use device_name
                'technician'        => $job_data['tech_name']   ?? '',
                'repair_path'       => 'Carry-In',
                'warranty'          => $dev['warranty_status']  ?? '',
                'solution'          => $dev['solution']         ?? '',
                'date_in'           => $date_in,
            ];

            $result = call_ml_api('/predict', $payload, 'POST');

            if (!empty($result['success']) && $result['success']) {
                $days = $result['predicted_days'];
                if (strtolower($dev['warranty_status']) === 'warranty') {
                    $warranty_days[] = $days;
                } else {
                    $non_warranty_days[] = $days;
                }
            } else {
                $predict_err = "Prediction error: " . ($result['error'] ?? 'Unknown');
            }
        }

        // Warranty result
        if (!empty($warranty_days)) {
            $w = max($warranty_days);
            $predicted_warranty = $w . " Working Day" . ($w != 1 ? "s" : "");
            $warranty_date      = date('M d, Y', strtotime($date_in . " +$w days"));
        }

        // Non-warranty result
        if (!empty($non_warranty_days)) {
            $nw = max($non_warranty_days);
            $predicted_non_warranty = $nw . " Working Day" . ($nw != 1 ? "s" : "");
            $non_warranty_date      = date('M d, Y', strtotime($date_in . " +$nw days"));
        }

        if (empty($warranty_days) && empty($non_warranty_days) && !$predict_err) {
            $predict_err = "No devices found for this job.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Prediction - Job #<?= htmlspecialchars($job_no) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #2ecc71; --primary-hover: #27ae60; --primary-dark: #229954;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --bg-main: #f8fafc; --card-bg: #ffffff;
            --text-main: #1a202c; --text-dark: #0f172a; --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
            padding: 140px 20px 40px 20px;
            color: var(--text-main);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .container { max-width: 900px; margin: 0 auto; }

        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 36px 40px; border-radius: 20px; margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: white; text-align: center;
        }
        .page-header h1 {
            font-size: 32px; font-weight: 800; margin-bottom: 8px;
            display: flex; align-items: center; justify-content: center; gap: 12px;
        }
        .page-header p { font-size: 16px; opacity: 0.95; font-weight: 500; }

        /* API Status */
        .api-status {
            padding: 12px 20px; border-radius: 12px; margin-bottom: 24px;
            font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;
        }
        .api-ok  { background: #dcfce7; color: #14532d; border: 2px solid #86efac; }
        .api-err { background: #fee2e2; color: #7f1d1d; border: 2px solid #fca5a5; }

        .predict-card {
            background: var(--card-bg); padding: 40px; border-radius: 20px;
            border: 1px solid var(--border); box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }
        .predict-card:hover { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12); }

        .section-title {
            font-size: 20px; font-weight: 800; color: var(--text-dark);
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }

        .info-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 24px; border-radius: 16px; border: 2px solid var(--border);
        }
        .info-item { text-align: center; }
        .info-item label {
            font-size: 12px; text-transform: uppercase; color: var(--text-muted);
            font-weight: 700; letter-spacing: 0.5px; display: block; margin-bottom: 8px;
        }
        .info-item p { margin: 0; font-weight: 800; font-size: 16px; color: var(--text-dark); }

        .device-list {
            margin-bottom: 30px; padding: 24px;
            border-left: 5px solid var(--primary);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 12px; box-shadow: 0 2px 8px rgba(46, 204, 113, 0.1);
        }
        .device-list-title {
            font-size: 15px; font-weight: 800; color: var(--text-dark);
            margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .device-item {
            font-size: 15px; margin-bottom: 12px; padding: 12px 16px;
            background: white; border-radius: 10px;
            display: flex; align-items: center; justify-content: space-between;
            border: 2px solid #e2e8f0; transition: all 0.3s ease;
            font-weight: 600; color: var(--text-dark);
        }
        .device-item:hover { border-color: var(--primary); transform: translateX(4px); box-shadow: 0 4px 12px rgba(46, 204, 113, 0.15); }
        .device-name { display: flex; align-items: center; gap: 10px; }
        .device-issue { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-top: 2px; }

        .badge {
            font-size: 11px; padding: 6px 12px; border-radius: 8px;
            font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .badge-warranty    { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #14532d; border: 2px solid #86efac; }
        .badge-no-warranty { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #7f1d1d; border: 2px solid #fca5a5; }

        .btn-predict {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white; width: 100%; border: none; padding: 18px; border-radius: 12px;
            font-weight: 800; font-size: 16px; cursor: pointer;
            text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-predict:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-dark) 100%);
            transform: translateY(-2px); box-shadow: 0 8px 25px rgba(46, 204, 113, 0.5);
        }
        .btn-predict:disabled { opacity: 0.6; cursor: not-allowed; }

        .error-box {
            background: #fee2e2; border: 2px solid #fca5a5; color: #7f1d1d;
            padding: 16px 20px; border-radius: 12px; margin-top: 24px; font-weight: 700;
        }

        /* Result boxes - same as original */
        .result-container {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
            margin-top: 32px; animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .result-box {
            padding: 28px; border-radius: 16px; text-align: center;
            border: 3px dashed; transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .warranty-box     { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-color: var(--primary); }
        .non-warranty-box { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-color: var(--danger); }
        .result-icon      { font-size: 32px; display: block; margin-bottom: 8px; }
        .result-box small { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
        .result-box h3    { font-size: 22px; font-weight: 800; margin: 8px 0 4px; }
        .result-date      { font-size: 13px; font-weight: 700; margin-top: 6px; opacity: 0.85; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state-icon { font-size: 60px; margin-bottom: 20px; }

        /* Dark Mode */
        body.dark-mode { background: #0f172a !important; color: #f1f5f9 !important; }
        body.dark-mode .predict-card { background: #1e293b !important; border-color: #334155 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.3) !important; }
        body.dark-mode .section-title, body.dark-mode .info-item p,
        body.dark-mode .device-list-title, body.dark-mode label,
        body.dark-mode .device-item { color: #f1f5f9 !important; }
        body.dark-mode .info-grid   { background: #111827 !important; border-color: #334155 !important; }
        body.dark-mode .device-list { background: rgba(46,204,113,0.1) !important; box-shadow: none !important; }
        body.dark-mode .device-item { background: #111827 !important; border-color: #334155 !important; }
        body.dark-mode .warranty-box     { background: rgba(46,204,113,0.15) !important; }
        body.dark-mode .non-warranty-box { background: rgba(239,68,68,0.15) !important; }
        body.dark-mode .warranty-box h3, body.dark-mode .warranty-box small,
        body.dark-mode .warranty-box .result-date { color: #4ade80 !important; }
        body.dark-mode .non-warranty-box h3, body.dark-mode .non-warranty-box small,
        body.dark-mode .non-warranty-box .result-date { color: #f87171 !important; }
        body.dark-mode .api-ok  { background: rgba(46,204,113,0.15); color: #4ade80; border-color: #166534; }
        body.dark-mode .api-err { background: rgba(239,68,68,0.15);  color: #f87171; border-color: #7f1d1d; }

        @media (max-width: 768px) {
            body { padding: 120px 15px 30px 15px; }
            .page-header { padding: 24px 20px; }
            .page-header h1 { font-size: 24px; }
            .predict-card { padding: 24px; }
            .info-grid { grid-template-columns: 1fr; gap: 16px; }
            .result-container { grid-template-columns: 1fr; }
            .device-item { flex-direction: column; align-items: flex-start; gap: 8px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1> Repair Time Prediction</h1>
        <p>AI-powered completion estimate using your trained ML model</p>
    </div>

    <!-- API Status -->
    <?php if ($api_alive): ?>
        <div class="api-status api-ok">ML Prediction Engine is online and ready</div>
    <?php else: ?>
        <div class="api-status api-err"> ML Engine offline — run: <code>cd model && python predict_api.py</code></div>
    <?php endif; ?>

    <div class="predict-card">
        <?php if ($job_data): ?>

            <div class="section-title"> Job Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Job Number</label>
                    <p><?= htmlspecialchars($job_data['job_no']) ?></p>
                </div>
                <div class="info-item">
                    <label>Job Date</label>
                    <p><?= date('M d, Y', strtotime($job_data['job_date'])) ?></p>
                </div>
                <div class="info-item">
                    <label>Technician</label>
                    <p><?= htmlspecialchars($job_data['tech_name'] ?? 'Unassigned') ?></p>
                </div>
            </div>

            <div class="device-list">
                <div class="device-list-title"> Devices in This Job</div>
                <?php
                // Re-query for display — uses same actual columns
                $dev_res = mysqli_query($conn, "SELECT device_name, issue_name, warranty_status FROM job_device WHERE job_no = '$job_no'");
                while ($dev = mysqli_fetch_assoc($dev_res)):
                    $badge_class = (strtolower($dev['warranty_status']) === 'warranty') ? 'badge-warranty' : 'badge-no-warranty';
                    $icon = (strtolower($dev['warranty_status']) === 'warranty') ? '✓' : '✗';
                ?>
                    <div class="device-item">
                        <div class="device-name">
                            <span></span>
                            <div>
                                <div><?= htmlspecialchars($dev['device_name']) ?></div>
                                <div class="device-issue"><?= htmlspecialchars($dev['issue_name']) ?></div>
                            </div>
                        </div>
                        <span class="badge <?= $badge_class ?>">
                            <?= $icon ?> <?= htmlspecialchars($dev['warranty_status']) ?>
                        </span>
                    </div>
                <?php endwhile; ?>
                <?php if (empty($devices)): ?>
                    <p style="color:var(--text-muted);font-size:14px;">No devices found for this job.</p>
                <?php endif; ?>
            </div>

            <div class="section-title">ML Prediction</div>

            <form method="POST">
                <button type="submit" name="predict" class="btn-predict"
                    <?= !$api_alive ? 'disabled' : '' ?>>
                    <span></span>
                    <span><?= $api_alive ? 'Calculate Prediction' : 'API Offline' ?></span>
                </button>
            </form>

            <?php if ($predict_err): ?>
                <div class="error-box"><?= htmlspecialchars($predict_err) ?></div>
            <?php endif; ?>

            <?php if ($predicted_warranty || $predicted_non_warranty): ?>
                <div class="result-container">
                    <?php if ($predicted_warranty): ?>
                    <div class="result-box warranty-box">
                        <span class="result-icon">Result</span>
                        <small>Warranty Devices</small>
                        <h3><?= $predicted_warranty ?></h3>
                        <div class="result-date">Date Ready by <?= $warranty_date ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($predicted_non_warranty): ?>
                    <div class="result-box non-warranty-box">
                        <span class="result-icon">⚡</span>
                        <small>Non-Warranty Devices</small>
                        <h3><?= $predicted_non_warranty ?></h3>
                        <div class="result-date">Date Ready by <?= $non_warranty_date ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <p><strong>No Job Selected</strong><br>
                Please select a job from the dashboard to predict repair time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function applySystemTheme() {
        const isDark = localStorage.getItem('darkMode') === 'enabled';
        if (isDark) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    }
    applySystemTheme();
</script>
</body>
</html>