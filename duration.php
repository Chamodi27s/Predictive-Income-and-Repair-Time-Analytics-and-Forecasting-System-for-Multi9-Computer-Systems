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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Prediction - Job #<?= htmlspecialchars($job_no) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2ecc71;
            --primary-hover: #27ae60;
            --primary-dark: #229954;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1a202c;
            --text-dark: #0f172a;
            --text-muted: #64748b;
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
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header Card */
        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            padding: 36px 40px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
            color: white;
            text-align: center;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .page-header p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 500;
        }

        /* Main Prediction Card */
        .predict-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .predict-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Job Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 24px;
            border-radius: 16px;
            border: 2px solid var(--border);
        }

        .info-item {
            text-align: center;
        }

        .info-item label {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 8px;
        }

        .info-item p {
            margin: 0;
            font-weight: 800;
            font-size: 16px;
            color: var(--text-dark);
        }

        /* Device List Section */
        .device-list {
            margin-bottom: 30px;
            padding: 24px;
            border-left: 5px solid var(--primary);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(46, 204, 113, 0.1);
        }

        .device-list-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .device-item {
            font-size: 15px;
            margin-bottom: 12px;
            padding: 12px 16px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            font-weight: 600;
            color: var(--text-dark);
        }

        .device-item:hover {
            border-color: var(--primary);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.15);
        }

        .device-name {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge {
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-warranty {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #14532d;
            border: 2px solid #86efac;
        }

        .badge-no-warranty {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #7f1d1d;
            border: 2px solid #fca5a5;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 28px;
        }

        label {
            display: block;
            margin-bottom: 12px;
            font-weight: 700;
            font-size: 14px;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        select, input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
            transition: all 0.3s ease;
            background: white;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%232ecc71' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 45px;
        }

        /* Predict Button */
        .btn-predict {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            width: 100%;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 16px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-predict:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-dark) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46, 204, 113, 0.5);
        }

        .btn-predict:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Result Container */
        .result-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 32px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-box {
            padding: 28px;
            border-radius: 16px;
            text-align: center;
            border: 3px dashed;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .result-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
        }

        .result-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .warranty-box {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: var(--primary);
        }

        .warranty-box::before {
            background: var(--primary);
        }

        .non-warranty-box {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: var(--danger);
        }

        .non-warranty-box::before {
            background: var(--danger);
        }

        .result-box small {
            display: block;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            opacity: 0.8;
        }

        .warranty-box small {
            color: #14532d;
        }

        .non-warranty-box small {
            color: #7f1d1d;
        }

        .result-box h3 {
            font-size: 28px;
            font-weight: 900;
            margin: 0;
        }

        .warranty-box h3 {
            color: #14532d;
        }

        .non-warranty-box h3 {
            color: #7f1d1d;
        }

        .result-icon {
            font-size: 36px;
            margin-bottom: 8px;
            display: block;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 120px 15px 30px 15px;
            }

            .page-header {
                padding: 24px 20px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .predict-card {
                padding: 24px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .result-container {
                grid-template-columns: 1fr;
            }

            .device-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>⏱️ Repair Time Prediction</h1>
        <p>Estimate completion time based on workload and experience</p>
    </div>

    <div class="predict-card">
        <?php if ($job_data): ?>
            <div class="section-title">📋 Job Information</div>
            
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
                <div class="device-list-title">📱 Devices in This Job</div>
                <?php
                $dev_res = mysqli_query($conn, "SELECT device_name, warranty_status FROM job_device WHERE job_no = '$job_no'");
                while($dev = mysqli_fetch_assoc($dev_res)):
                    $badge_class = ($dev['warranty_status'] == 'Warranty') ? 'badge-warranty' : 'badge-no-warranty';
                    $icon = ($dev['warranty_status'] == 'Warranty') ? '✓' : '✗';
                ?>
                    <div class="device-item">
                        <div class="device-name">
                            <span>📱</span>
                            <span><?= htmlspecialchars($dev['device_name']) ?></span>
                        </div>
                        <span class="badge <?= $badge_class ?>">
                            <?= $icon ?> <?= htmlspecialchars($dev['warranty_status']) ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="section-title">🔧 Prediction Parameters</div>

            <form method="POST">
                <div class="form-group">
                    <label>Technician Experience (Years)</label>
                    <input type="number" name="experience" min="1" max="50" required placeholder="Enter years of experience (e.g., 5)">
                </div>

                <div class="form-group">
                    <label>Current Workshop Workload</label>
                    <select name="workload" required>
                        <option value="Low">🟢 Low - Few pending jobs</option>
                        <option value="Medium" selected>🟡 Medium - Normal workload</option>
                        <option value="High">🔴 High - Many pending jobs</option>
                    </select>
                </div>

                <button type="submit" name="predict" class="btn-predict">
                    <span>🎯</span>
                    <span>Calculate Prediction</span>
                </button>
            </form>

            <?php if ($predicted_warranty || $predicted_non_warranty): ?>
                <div class="result-container">
                    <div class="result-box warranty-box">
                        <span class="result-icon">✅</span>
                        <small>Warranty Devices</small>
                        <h3><?= $predicted_warranty ?></h3>
                    </div>
                    <div class="result-box non-warranty-box">
                        <span class="result-icon">⚡</span>
                        <small>Non-Warranty Devices</small>
                        <h3><?= $predicted_non_warranty ?></h3>
                    </div>
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

</body>
</html>