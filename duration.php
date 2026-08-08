<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db_config.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is not available. Check db_config.php.');
}

$conn->set_charset('utf8mb4');

/* Load navbar PHP before output, but render its HTML inside <body>. */
ob_start();
require __DIR__ . '/navbar.php';
$navbar_html = (string) ob_get_clean();

/* =========================================================
   RENDER ML API CONFIGURATION
========================================================= */

if (!defined('ML_API')) {
    define(
        'ML_API',
        'https://predictive-income-and-repair-time-pcrr.onrender.com'
    );
}


/* =========================================================
   CALL FLASK ML API
========================================================= */

function call_ml_api(
    string $endpoint,
    array $payload = [],
    string $method = 'GET'
): array {
    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'error' => 'PHP cURL extension is not enabled.'
        ];
    }

    $url = rtrim(ML_API, '/') . '/' . ltrim($endpoint, '/');

    $ch = curl_init($url);

    $headers = [
        'Accept: application/json'
    ];

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        // Keep server-side requests below InfinityFree's PHP execution limit.
        // The browser health check below wakes the Render service first.
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_ENCODING => '',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'Multi9-Repair-System/1.0'
    ];

    if (strtoupper($method) === 'POST') {
        $jsonPayload = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($jsonPayload === false) {
            return [
                'success' => false,
                'error' => 'Unable to encode prediction data.'
            ];
        }

        $headers[] = 'Content-Type: application/json';

        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = $jsonPayload;
    }

    $options[CURLOPT_HTTPHEADER] = $headers;

    curl_setopt_array($ch, $options);

    $rawResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($rawResponse === false) {
        return [
            'success' => false,
            'error' => 'Unable to contact the ML API: ' . ($curlError ?: 'Unknown cURL error')
        ];
    }

    $decodedResponse = json_decode($rawResponse, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        return [
            'success' => false,
            'error' => is_array($decodedResponse) && !empty($decodedResponse['error'])
                ? (string) $decodedResponse['error']
                : 'ML API returned HTTP status ' . $httpCode
        ];
    }

    if (!is_array($decodedResponse)) {
        return [
            'success' => false,
            'error' => 'Invalid JSON response from ML API.'
        ];
    }

    return $decodedResponse;
}


/* =========================================================
   API STATUS

   Do not call /health from PHP while the page is loading.
   A sleeping Render free service can take longer than the PHP
   execution limit. JavaScript checks and wakes the API instead.
========================================================= */

$api_alive = true;

if (empty($_SESSION['duration_csrf'])) {
    $_SESSION['duration_csrf'] = bin2hex(random_bytes(32));
}
$csrf_token = (string) $_SESSION['duration_csrf'];


/* =========================================================
   LOAD JOB INFORMATION
========================================================= */

$job_no = isset($_GET['job_no'])
    ? trim($_GET['job_no'])
    : '';

$job_data = null;
$devices = [];

if (!empty($job_no)) {

    /* Get job and technician information */

    $jobQuery = "
        SELECT
            j.job_no,
            j.job_date,
            t.name AS tech_name
        FROM job j
        LEFT JOIN technicians t
            ON j.technician_id = t.technician_id
        WHERE j.job_no = ?
        LIMIT 1
    ";

    $jobStatement = mysqli_prepare($conn, $jobQuery);

    if ($jobStatement) {
        mysqli_stmt_bind_param(
            $jobStatement,
            's',
            $job_no
        );

        mysqli_stmt_execute($jobStatement);

        $jobResult = mysqli_stmt_get_result(
            $jobStatement
        );

        $job_data = mysqli_fetch_assoc(
            $jobResult
        );

        mysqli_stmt_close($jobStatement);
    }


    /* Get all devices belonging to the job */

    $deviceQuery = "
        SELECT
            device_name,
            model,
            issue_name,
            warranty_status,
            solution,
            description
        FROM job_device
        WHERE job_no = ?
    ";

    $deviceStatement = mysqli_prepare(
        $conn,
        $deviceQuery
    );

    if ($deviceStatement) {
        mysqli_stmt_bind_param(
            $deviceStatement,
            's',
            $job_no
        );

        mysqli_stmt_execute($deviceStatement);

        $deviceResult = mysqli_stmt_get_result(
            $deviceStatement
        );

        while ($row = mysqli_fetch_assoc($deviceResult)) {
            $devices[] = $row;
        }

        mysqli_stmt_close($deviceStatement);
    }
}


/* =========================================================
   PREDICTION RESULT VARIABLES
========================================================= */

$predicted_warranty = '';
$predicted_non_warranty = '';

$warranty_date = '';
$non_warranty_date = '';

$predict_err = '';


/* =========================================================
   GENERATE PREDICTIONS
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['predict'])) {
    $postedToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';

    if (!hash_equals($csrf_token, $postedToken)) {
        $predict_err = 'Invalid request token. Refresh the page and try again.';
    } elseif (empty($job_no) || empty($job_data)) {
        $predict_err = 'The selected job could not be found.';
    } elseif (empty($devices)) {
        $predict_err = 'No devices found for this job.';
    } elseif (!$api_alive) {
        $predict_err = 'ML prediction service is temporarily unavailable. Please try again.';
    } else {
        $date_in = (string) $job_data['job_date'];
        $warranty_days = [];
        $non_warranty_days = [];
        $predictionErrors = [];

        foreach ($devices as $device) {
            $deviceName = trim((string) ($device['device_name'] ?? ''));
            $deviceModel = trim((string) ($device['model'] ?? ''));
            $warrantyStatus = strtolower(trim((string) ($device['warranty_status'] ?? '')));
            $isWarrantyDevice = $warrantyStatus === 'warranty';

            $faultDescription = trim(
                (string) ($device['issue_name'] ?? '') . ' ' .
                (string) ($device['description'] ?? '')
            );

            /* Map database values to the categories used to train the model. */
            $payload = [
                'fault_description' => $faultDescription !== '' ? $faultDescription : 'Unknown fault',
                'device_type' => $deviceName !== '' ? $deviceName : 'Unknown',
                'item_model' => $deviceModel !== '' ? $deviceModel : $deviceName,
                'technician' => trim((string) ($job_data['tech_name'] ?? '')),
                'repair_path' => $isWarrantyDevice ? 'Agent' : 'In-House',
                'warranty' => $isWarrantyDevice ? 'Yes' : 'No',
                'solution' => trim((string) ($device['solution'] ?? '')),
                'date_in' => $date_in
            ];

            $result = call_ml_api('/predict', $payload, 'POST');

            if (!empty($result['success']) && isset($result['predicted_days'])) {
                $predictedDays = max(0, (int) round((float) $result['predicted_days']));

                if ($isWarrantyDevice) {
                    $warranty_days[] = $predictedDays;
                } else {
                    $non_warranty_days[] = $predictedDays;
                }
            } else {
                $predictionErrors[] = $deviceName . ': ' . ($result['error'] ?? 'Unknown prediction error');
            }
        }

        if (!empty($warranty_days)) {
            $maximumWarrantyDays = max($warranty_days);
            $predicted_warranty = $maximumWarrantyDays . ' Day' . ($maximumWarrantyDays !== 1 ? 's' : '');
            $warranty_date = date(
                'M d, Y',
                strtotime($date_in . " +{$maximumWarrantyDays} days")
            );
        }

        if (!empty($non_warranty_days)) {
            $maximumNonWarrantyDays = max($non_warranty_days);
            $predicted_non_warranty = $maximumNonWarrantyDays . ' Day' . ($maximumNonWarrantyDays !== 1 ? 's' : '');
            $non_warranty_date = date(
                'M d, Y',
                strtotime($date_in . " +{$maximumNonWarrantyDays} days")
            );
        }

        if (!empty($predictionErrors)) {
            $predict_err = 'Some predictions failed: ' . implode(' | ', $predictionErrors);
        }

        if (empty($warranty_days) && empty($non_warranty_days) && $predict_err === '') {
            $predict_err = 'The ML API did not return a prediction.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Time Prediction -
        Job #<?= htmlspecialchars($job_no) ?>
    </title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

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
            background: linear-gradient(
                135deg,
                #f8fafc 0%,
                #e8eef5 100%
            );
            padding: 140px 20px 40px 20px;
            color: var(--text-main);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            background: linear-gradient(
                135deg,
                #2ecc71 0%,
                #27ae60 100%
            );
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

        .api-status {
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .api-ok {
            background: #dcfce7;
            color: #14532d;
            border: 2px solid #86efac;
        }

        .api-err {
            background: #fee2e2;
            color: #7f1d1d;
            border: 2px solid #fca5a5;
        }

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

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            background: linear-gradient(
                135deg,
                #f8fafc 0%,
                #f1f5f9 100%
            );
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

        .device-list {
            margin-bottom: 30px;
            padding: 24px;
            border-left: 5px solid var(--primary);
            background: linear-gradient(
                135deg,
                #f0fdf4 0%,
                #dcfce7 100%
            );
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

        .device-issue {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 2px;
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
            background: linear-gradient(
                135deg,
                #dcfce7,
                #bbf7d0
            );
            color: #14532d;
            border: 2px solid #86efac;
        }

        .badge-no-warranty {
            background: linear-gradient(
                135deg,
                #fee2e2,
                #fecaca
            );
            color: #7f1d1d;
            border: 2px solid #fca5a5;
        }

        .btn-predict {
            background: linear-gradient(
                135deg,
                var(--primary) 0%,
                var(--primary-hover) 100%
            );
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
            background: linear-gradient(
                135deg,
                var(--primary-hover) 0%,
                var(--primary-dark) 100%
            );
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46, 204, 113, 0.5);
        }

        .btn-predict:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .error-box {
            background: #fee2e2;
            border: 2px solid #fca5a5;
            color: #7f1d1d;
            padding: 16px 20px;
            border-radius: 12px;
            margin-top: 24px;
            font-weight: 700;
        }

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

        .warranty-box {
            background: linear-gradient(
                135deg,
                #dcfce7 0%,
                #bbf7d0 100%
            );
            border-color: var(--primary);
        }

        .non-warranty-box {
            background: linear-gradient(
                135deg,
                #fee2e2 0%,
                #fecaca 100%
            );
            border-color: var(--danger);
        }

        .result-icon {
            font-size: 32px;
            display: block;
            margin-bottom: 8px;
        }

        .result-box small {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
        }

        .result-box h3 {
            font-size: 22px;
            font-weight: 800;
            margin: 8px 0 4px;
        }

        .result-date {
            font-size: 13px;
            font-weight: 700;
            margin-top: 6px;
            opacity: 0.85;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        body.dark-mode {
            background: #0f172a !important;
            color: #f1f5f9 !important;
        }

        body.dark-mode .predict-card {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
        }

        body.dark-mode .section-title,
        body.dark-mode .info-item p,
        body.dark-mode .device-list-title,
        body.dark-mode label,
        body.dark-mode .device-item {
            color: #f1f5f9 !important;
        }

        body.dark-mode .info-grid {
            background: #111827 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .device-list {
            background: rgba(46, 204, 113, 0.1) !important;
            box-shadow: none !important;
        }

        body.dark-mode .device-item {
            background: #111827 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .warranty-box {
            background: rgba(46, 204, 113, 0.15) !important;
        }

        body.dark-mode .non-warranty-box {
            background: rgba(239, 68, 68, 0.15) !important;
        }

        body.dark-mode .warranty-box h3,
        body.dark-mode .warranty-box small,
        body.dark-mode .warranty-box .result-date {
            color: #4ade80 !important;
        }

        body.dark-mode .non-warranty-box h3,
        body.dark-mode .non-warranty-box small,
        body.dark-mode .non-warranty-box .result-date {
            color: #f87171 !important;
        }

        body.dark-mode .api-ok {
            background: rgba(46, 204, 113, 0.15);
            color: #4ade80;
            border-color: #166534;
        }

        body.dark-mode .api-err {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border-color: #7f1d1d;
        }

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

<?= $navbar_html ?>

<div class="container">

    <div class="page-header">
        <h1>⏱️ Repair Time Prediction</h1>

        <p>
            AI-powered completion estimate using your trained ML model
        </p>
    </div>

    <!-- API status is checked in the user's browser, so a Render cold start
         does not consume InfinityFree's PHP execution time. -->
    <div id="mlApiStatus" class="api-status api-err">
        ⏳ Connecting to the ML prediction service...
    </div>

    <div class="predict-card">

        <?php if ($job_data): ?>

            <div class="section-title">
                📋 Job Information
            </div>
            <div class="info-grid">

                <div class="info-item">
                    <label>Job Number</label>

                    <p>
                        <?= htmlspecialchars($job_data['job_no']) ?>
                    </p>
                </div>

                <div class="info-item">
                    <label>Job Date</label>

                    <p>
                        <?= date(
                            'M d, Y',
                            strtotime($job_data['job_date'])
                        ) ?>
                    </p>
                </div>

                <div class="info-item">
                    <label>Technician</label>

                    <p>
                        <?= htmlspecialchars(
                            $job_data['tech_name'] ?? 'Unassigned'
                        ) ?>
                    </p>
                </div>

            </div>


            <div class="device-list">
                <div class="device-list-title">
                    📱 Devices in This Job
                </div>

                <?php if (!empty($devices)): ?>

                    <?php foreach ($devices as $device): ?>

                        <?php
                        $warrantyStatus = strtolower(
                            trim($device['warranty_status'] ?? '')
                        );

                        $isWarranty = (
                            $warrantyStatus === 'warranty'
                        );

                        $badgeClass = $isWarranty
                            ? 'badge-warranty'
                            : 'badge-no-warranty';

                        $badgeIcon = $isWarranty
                            ? '✓'
                            : '✗';
                        ?>

                        <div class="device-item">

                            <div class="device-name">

                                <span>📱</span>

                                <div>
                                    <div>
                                        <?= htmlspecialchars(
                                            $device['device_name'] ?? ''
                                        ) ?>
                                    </div>

                                    <div class="device-issue">
                                        <?= htmlspecialchars(
                                            $device['issue_name'] ?? ''
                                        ) ?>
                                    </div>
                                </div>
                            </div>

                            <span class="badge <?= $badgeClass ?>">
                                <?= $badgeIcon ?>

                                <?= htmlspecialchars(
                                    $device['warranty_status'] ?? ''
                                ) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p
                        style="
                            color: var(--text-muted);
                            font-size: 14px;
                        "
                    >
                        No devices found for this job.
                    </p>

                <?php endif; ?>

            </div>


            <div class="section-title">
                🤖 ML Prediction
            </div>

            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>"
                >

                <button
                    type="submit"
                    name="predict"
                    id="predictionButton"
                    class="btn-predict"
                    disabled
                >
                    <span>🎯</span>

                    <span id="predictionButtonText">
                        Checking ML API...
                    </span>
                </button>

            </form>


            <?php if (!empty($predict_err)): ?>

                <div class="error-box">
                    <?= htmlspecialchars($predict_err) ?>
                </div>

            <?php endif; ?>


            <?php if (
                !empty($predicted_warranty) ||
                !empty($predicted_non_warranty)
            ): ?>

                <div class="result-container">

                    <?php if (!empty($predicted_warranty)): ?>

                        <div class="result-box warranty-box">

                            <span class="result-icon">✅</span>

                            <small>Warranty Devices</small>

                            <h3>
                                <?= htmlspecialchars(
                                    $predicted_warranty
                                ) ?>
                            </h3>

                            <div class="result-date">
                                📅 Ready by
                                <?= htmlspecialchars($warranty_date) ?>
                            </div>

                        </div>

                    <?php endif; ?>


                    <?php if (!empty($predicted_non_warranty)): ?>

                        <div class="result-box non-warranty-box">

                            <span class="result-icon">⚡</span>

                            <small>Non-Warranty Devices</small>

                            <h3>
                                <?= htmlspecialchars(
                                    $predicted_non_warranty
                                ) ?>
                            </h3>

                            <div class="result-date">
                                📅 Ready by
                                <?= htmlspecialchars(
                                    $non_warranty_date
                                ) ?>
                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            <?php endif; ?>


        <?php else: ?>

            <div class="empty-state">

                <div class="empty-state-icon">
                    🔍
                </div>

                <p>
                    <strong>No Job Selected</strong>
                    <br>
                    Please select a job from the dashboard to predict repair time.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>


<script>
    const mlHealthUrl = <?= json_encode(
        rtrim(ML_API, '/') . '/health',
        JSON_UNESCAPED_SLASHES
    ) ?>;

    function applySystemTheme() {
        const isDark =
            localStorage.getItem('darkMode') === 'enabled';

        if (isDark) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    }

    applySystemTheme();

    async function checkMLApi(attempt = 1) {
        const statusBox = document.getElementById('mlApiStatus');
        const predictButton = document.getElementById('predictionButton');
        const buttonText = document.getElementById('predictionButtonText');

        if (!statusBox) {
            return;
        }

        statusBox.className = 'api-status api-err';
        statusBox.textContent = attempt === 1
            ? '⏳ Connecting to the ML prediction service...'
            : '⏳ The ML service is waking up. Please wait...';

        if (predictButton) {
            predictButton.disabled = true;
        }

        if (buttonText) {
            buttonText.textContent = 'Checking ML API...';
        }

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 90000);

        try {
            const response = await fetch(mlHealthUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                cache: 'no-store',
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.status !== 'ok') {
                throw new Error('Unexpected health response');
            }

            statusBox.className = 'api-status api-ok';
            statusBox.textContent = '✅ ML Prediction Engine is online and ready';

            if (predictButton) {
                predictButton.disabled = false;
            }

            if (buttonText) {
                buttonText.textContent = 'Calculate Prediction';
            }
        } catch (error) {
            if (attempt < 3) {
                setTimeout(() => checkMLApi(attempt + 1), 5000);
                return;
            }

            statusBox.className = 'api-status api-err';
            statusBox.textContent =
                '⚠️ ML prediction service is unavailable. Refresh and try again.';

            if (buttonText) {
                buttonText.textContent = 'API Offline';
            }
        } finally {
            clearTimeout(timeoutId);
        }
    }

    checkMLApi();
</script>

<?php include_once __DIR__ . '/chatbot.php'; ?>

</body>
</html>