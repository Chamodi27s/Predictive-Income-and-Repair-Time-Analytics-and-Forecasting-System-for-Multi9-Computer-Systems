<?php
/*
 * Multi9 Assistant - single-file chatbot
 * Put this file in the project root, next to db_config.php.
 * Add before </body> on each root-level page:
 * <?php include_once __DIR__ . '/chatbot.php'; ?>
 * Add your Gemini key below. Keep this PHP file private on the server.
 */

if (!defined('M9_GEMINI_API_KEY')) {
    define('M9_GEMINI_API_KEY', 'PASTE_YOUR_GEMINI_API_KEY_HERE');
}
if (!defined('M9_GEMINI_MODEL')) {
    define('M9_GEMINI_MODEL', 'gemini-2.5-flash');
}
if (!defined('M9_ML_URL')) {
    define('M9_ML_URL', 'https://predictive-income-and-repair-time-pcrr.onrender.com/predict');
}

if (!function_exists('m9_json')) {
    function m9_json($success, $text, $status = 200)
    {
        http_response_code($status);
        echo json_encode(
            $success ? array('success' => true, 'reply' => $text) : array('success' => false, 'message' => $text),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    function m9_lower($text)
    {
        return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    }

    function m9_contains($text, $words)
    {
        foreach ($words as $word) {
            if (strpos($text, m9_lower($word)) !== false) {
                return true;
            }
        }
        return false;
    }

    function m9_language($message)
    {
        if (preg_match('/[\x{0D80}-\x{0DFF}]/u', $message)) {
            return 'si';
        }
        $roman = array('kohomada', 'mage', 'eka', 'balanna', 'oni', 'kiyada', 'koheda', 'hadala', 'kawada');
        return m9_contains(m9_lower($message), $roman) ? 'si' : 'en';
    }

    function m9_job_no($message)
    {
        if (!preg_match('/\bORD\s*-?\s*\d+\b/i', $message, $match)) {
            return null;
        }
        $number = preg_replace('/\D+/', '', $match[0]);
        return $number ? 'ORD-' . $number : null;
    }

    function m9_phone($message)
    {
        if (!preg_match('/(?:\+94|0)[\s-]?\d(?:[\s-]?\d){8}/', $message, $match)) {
            return null;
        }
        $phone = preg_replace('/\D+/', '', $match[0]);
        if (strpos($phone, '94') === 0 && strlen($phone) === 11) {
            $phone = '0' . substr($phone, 2);
        }
        return strlen($phone) === 10 ? $phone : null;
    }

    function m9_intent($message, $jobNo)
    {
        if (m9_contains($message, array(
            'duration', 'predict', 'ready date', 'how long', 'repair time',
            'කවදා', 'කොච්චර කල්', 'කාලය', 'kiyada', 'kawada'
        ))) {
            return 'duration';
        }
        if ($jobNo || m9_contains($message, array(
            'status', 'track', 'job number', 'order', 'තත්ත්වය',
            'හදලාද', 'status eka', 'repair eka'
        ))) {
            return 'status';
        }
        return 'general';
    }

    function m9_clean($value, $fallback = 'Not available')
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : $fallback;
    }

    function m9_verified_job($conn, $jobNo, $phone)
    {
        $sql = "SELECT j.job_no, j.phone_number, j.job_date, j.job_status,
                       j.actual_repair_time_days, j.estimated_cost, j.advance_paid,
                       c.customer_name, t.name AS technician_name
                FROM job j
                LEFT JOIN customer c ON c.phone_number = j.phone_number
                LEFT JOIN technicians t ON t.technician_id = j.technician_id
                WHERE j.job_no = ? AND j.phone_number = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('ss', $jobNo, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $job ?: null;
    }

    function m9_devices($conn, $jobNo)
    {
        $sql = "SELECT job_device_id, device_name, model, warranty_status, issue_name,
                       description, device_status, completed_date, issue_category,
                       solution, final_status
                FROM job_device WHERE job_no = ? ORDER BY job_device_id";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return array();
        }
        $stmt->bind_param('s', $jobNo);
        $stmt->execute();
        $result = $stmt->get_result();
        $devices = array();
        while ($result && ($row = $result->fetch_assoc())) {
            $devices[] = $row;
        }
        $stmt->close();
        return $devices;
    }

    function m9_status_reply($job, $devices, $language)
    {
        $cost = (float) $job['estimated_cost'];
        $advance = (float) $job['advance_paid'];
        $balance = max(0, $cost - $advance);

        $lines = array(
            $language === 'si' ? '✅ Job එක verify වුණා.' : '✅ Job verified successfully.',
            'Job Number: ' . $job['job_no'],
            'Customer: ' . m9_clean($job['customer_name']),
            'Job Date: ' . m9_clean($job['job_date']),
            'Overall Status: ' . m9_clean($job['job_status']),
            'Technician: ' . m9_clean($job['technician_name'], $language === 'si' ? 'තවම assign කර නැහැ' : 'Not assigned'),
            'Estimated Cost: Rs. ' . number_format($cost, 2),
            'Advance Paid: Rs. ' . number_format($advance, 2),
            'Estimated Balance: Rs. ' . number_format($balance, 2)
        );

        if (!$devices) {
            $lines[] = $language === 'si' ? 'Device details හමු වුණේ නැහැ.' : 'No device details found.';
            return implode("\n", $lines);
        }

        $lines[] = '';
        $lines[] = 'Device Details:';
        foreach ($devices as $index => $device) {
            $lines[] = ($index + 1) . '. ' . m9_clean($device['device_name']) . ' (' . m9_clean($device['model'], 'N/A') . ')';
            $lines[] = '   Status: ' . m9_clean($device['device_status']);
            $lines[] = '   Issue: ' . m9_clean($device['issue_name']);
            $lines[] = '   Warranty: ' . m9_clean($device['warranty_status']);
            if (trim((string) $device['solution']) !== '') {
                $lines[] = '   Solution: ' . trim($device['solution']);
            }
            if (!empty($device['completed_date'])) {
                $lines[] = '   Completed: ' . $device['completed_date'];
            }
        }
        return implode("\n", $lines);
    }

    function m9_call_ml($url, $payload)
    {
        if (!function_exists('curl_init')) {
            return null;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Accept: application/json'),
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ));
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!is_string($response) || $status < 200 || $status >= 300) {
            return null;
        }
        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    function m9_pick($data, $keys)
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }
        if (isset($data['prediction']) && is_array($data['prediction'])) {
            return m9_pick($data['prediction'], $keys);
        }
        return null;
    }

    function m9_duration_reply($job, $devices, $language)
    {
        if (!empty($job['actual_repair_time_days'])) {
            return $language === 'si'
                ? 'Recorded repair time එක දින ' . (int) $job['actual_repair_time_days'] . 'යි.'
                : 'The recorded repair time is ' . (int) $job['actual_repair_time_days'] . ' day(s).';
        }
        if (!$devices) {
            return $language === 'si' ? 'Prediction සඳහා device details හමු වුණේ නැහැ.' : 'No device details found for prediction.';
        }

        $lines = array($language === 'si' ? '🔮 Estimated repair duration:' : '🔮 Estimated repair duration:');
        $found = false;
        foreach ($devices as $device) {
            $fault = trim(m9_clean($device['issue_name'], '') . ' ' . m9_clean($device['description'], ''));
            $payload = array(
                'fault_description' => $fault !== '' ? $fault : 'Unknown fault',
                'device_type' => m9_clean($device['device_name'], 'Unknown'),
                'item_model' => m9_clean($device['model'], 'Unknown'),
                'technician' => m9_clean($job['technician_name'], 'Unknown'),
                'repair_path' => stripos((string) $device['warranty_status'], 'no') !== false ? 'Non-Warranty' : 'Warranty',
                'warranty' => m9_clean($device['warranty_status'], 'Unknown'),
                'solution' => m9_clean($device['solution'], 'Pending diagnosis'),
                'date_in' => m9_clean($job['job_date'], date('Y-m-d'))
            );
            $result = m9_call_ml(M9_ML_URL, $payload);
            if ($result) {
                $days = m9_pick($result, array('predicted_days', 'prediction_days', 'repair_days', 'days'));
                $date = m9_pick($result, array('predicted_date', 'ready_date', 'completion_date'));
                if ($days !== null || $date !== null) {
                    $found = true;
                    $line = '- ' . m9_clean($device['device_name']);
                    if ($days !== null) {
                        $line .= ': ' . $days . ($language === 'si' ? ' දින' : ' day(s)');
                    }
                    if ($date !== null) {
                        $line .= ' | Ready date: ' . $date;
                    }
                    $lines[] = $line;
                }
            }
        }

        if ($found) {
            $lines[] = $language === 'si'
                ? 'මෙය ML estimate එකක් පමණයි. Repair complexity අනුව වෙනස් විය හැක.'
                : 'This is an ML estimate and may change depending on repair complexity.';
            return implode("\n", $lines);
        }

        return ($language === 'si'
            ? 'Prediction service එක දැන් response කරන්නේ නැහැ. ටික වේලාවකින් නැවත උත්සාහ කරන්න.'
            : 'The prediction service is not responding. Please try again shortly.')
            . "\nPrediction page: duration.php?job_no=" . rawurlencode($job['job_no']);
    }

    function m9_shop($conn)
    {
        $shop = array(
            'shop_name' => 'Multi9 Computer Systems',
            'shop_address' => 'Not available',
            'shop_phone' => 'Not available',
            'shop_email' => 'Not available'
        );
        $result = $conn->query('SELECT shop_name, shop_address, shop_phone, shop_email FROM system_settings ORDER BY id LIMIT 1');
        if ($result && ($row = $result->fetch_assoc())) {
            $shop = array_merge($shop, $row);
        }
        return $shop;
    }

    function m9_faq($conn, $message, $language)
    {
        $shop = m9_shop($conn);
        if (m9_contains($message, array('contact', 'phone', 'email', 'support', 'call', 'අමතන්න', 'දුරකථන'))) {
            return ($language === 'si' ? 'Multi9 support:' : 'Contact Multi9 support:')
                . "\nPhone: " . m9_clean($shop['shop_phone'])
                . "\nEmail: " . m9_clean($shop['shop_email']);
        }
        if (m9_contains($message, array('address', 'location', 'where', 'map', 'ලිපිනය', 'කොහෙද', 'koheda'))) {
            return ($language === 'si' ? 'Shop ලිපිනය: ' : 'Shop address: ') . m9_clean($shop['shop_address']);
        }
        if (m9_contains($message, array('open', 'close', 'hours', 'වේලාව'))) {
            return ($language === 'si' ? 'Opening hours දැනගැනීමට call කරන්න: ' : 'Call to confirm opening hours: ')
                . m9_clean($shop['shop_phone']);
        }
        if (m9_contains($message, array('warranty', 'වගකීම්', 'waranti'))) {
            return $language === 'si'
                ? 'Warranty repair සඳහා job number, purchase proof සහ supplier details සූදානම් කරගන්න. අවසන් තීරණය inspection එකෙන් පසුව ලබාදේ.'
                : 'For warranty repair, keep the job number, proof of purchase and supplier details ready. Eligibility is confirmed after inspection.';
        }
        if (m9_contains($message, array('payment', 'invoice', 'cash', 'card', 'ගෙවීම', 'bill'))) {
            return $language === 'si'
                ? 'Payment details job එක අනුව වෙනස් වේ. Repair Status option එකෙන් verified cost details බලන්න.'
                : 'Payment details vary by job. Use Repair Status to view verified cost details.';
        }
        return null;
    }

    function m9_gemini($message, $language)
    {
        if (M9_GEMINI_API_KEY === '' || M9_GEMINI_API_KEY === 'PASTE_YOUR_GEMINI_API_KEY_HERE') {
            return $language === 'si'
                ? 'Repair status, duration, warranty, payment, location සහ support ගැන මට උදව් කළ හැක. AI troubleshooting සඳහා Gemini API key එක chatbot.php file එකට දාන්න.'
                : 'I can help with repair status, duration, warranty, payments, location and support. Add the Gemini API key to chatbot.php for AI troubleshooting.';
        }
        if (!function_exists('curl_init')) {
            return $language === 'si' ? 'Server cURL extension එක අවශ්‍යයි.' : 'The server cURL extension is required.';
        }

        $system = 'You are Multi9 Assistant for Multi9 Computer Systems, a repair shop. '
            . ($language === 'si' ? 'Reply in simple natural Sinhala; technical words may be English. ' : 'Reply in simple English. ')
            . 'Keep answers under 130 words. Give only safe general troubleshooting. '
            . 'Never claim to check the database, invent job details, prices, dates, opening hours or warranty decisions. '
            . 'For job status, ask the user to use Repair Status. '
            . 'For smoke, burning smell, liquid damage, swollen batteries or electric risk, tell the user to switch off and unplug if safe and contact a technician. '
            . 'Never tell users to open batteries, power supplies or other hazardous components.';

        $payload = array(
            'systemInstruction' => array('parts' => array(array('text' => $system))),
            'contents' => array(array(
                'role' => 'user',
                'parts' => array(array('text' => $message))
            )),
            'generationConfig' => array('temperature' => 0.35, 'maxOutputTokens' => 350)
        );

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode(M9_GEMINI_MODEL) . ':generateContent';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'x-goog-api-key: ' . M9_GEMINI_API_KEY
            ),
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 30
        ));
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($response) || $status < 200 || $status >= 300) {
            return $language === 'si' ? 'AI assistant එක තාවකාලිකව unavailable.' : 'The AI assistant is temporarily unavailable.';
        }
        $data = json_decode($response, true);
        $text = isset($data['candidates'][0]['content']['parts'][0]['text'])
            ? trim($data['candidates'][0]['content']['parts'][0]['text'])
            : '';
        return $text !== '' ? $text : ($language === 'si' ? 'ප්‍රශ්නය වෙනත් විදිහකට අහන්න.' : 'Please rephrase the question.');
    }
}

/* AJAX API: the same chatbot.php file handles all messages. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['chat_api'])) {
    date_default_timezone_set('Asia/Colombo');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');

    $dbFile = __DIR__ . '/db_config.php';
    if (!is_file($dbFile)) {
        m9_json(false, 'db_config.php file not found.', 500);
    }
    require_once $dbFile;
    if (!isset($conn) || !($conn instanceof mysqli)) {
        m9_json(false, 'Database connection is not available.', 500);
    }
    $conn->set_charset('utf8mb4');

    $input = json_decode(file_get_contents('php://input'), true);
    $message = isset($input['message']) && is_string($input['message']) ? trim($input['message']) : '';
    if ($message === '') {
        m9_json(false, 'Please type a message.', 422);
    }
    $length = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
    if ($length > 600) {
        m9_json(false, 'Message is too long.', 422);
    }

    $language = m9_language($message);
    $normal = m9_lower(preg_replace('/\s+/u', ' ', $message));
    $jobNo = m9_job_no($message);
    $phone = m9_phone($message);

    if (!$jobNo && !empty($_SESSION['m9_pending_job']) && $phone) {
        $jobNo = $_SESSION['m9_pending_job'];
    }

    $intent = m9_intent($normal, $jobNo);
    if ($jobNo && !empty($_SESSION['m9_pending_intent'])) {
        $intent = $_SESSION['m9_pending_intent'];
    }

    if ($jobNo && in_array($intent, array('status', 'duration'), true)) {
        if (!$phone) {
            $_SESSION['m9_pending_job'] = $jobNo;
            $_SESSION['m9_pending_intent'] = $intent;
            m9_json(true, $language === 'si'
                ? $jobNo . ' verify කිරීමට job එක ලබාදුන් phone number එක ඇතුළත් කරන්න.'
                : 'Enter the phone number used for ' . $jobNo . ' to verify the job.');
        }

        unset($_SESSION['m9_pending_job'], $_SESSION['m9_pending_intent']);
        $job = m9_verified_job($conn, $jobNo, $phone);
        if (!$job) {
            m9_json(true, $language === 'si'
                ? 'Job number එක සහ phone number එක ගැළපෙන්නේ නැහැ.'
                : 'The job number and phone number do not match.');
        }

        $devices = m9_devices($conn, $jobNo);
        m9_json(true, $intent === 'duration'
            ? m9_duration_reply($job, $devices, $language)
            : m9_status_reply($job, $devices, $language));
    }

    if ($intent === 'status') {
        $_SESSION['m9_pending_intent'] = 'status';
        m9_json(true, $language === 'si'
            ? 'Repair status බලන්න job number එක ඇතුළත් කරන්න. උදා: ORD-80'
            : 'Enter your job number. Example: ORD-80');
    }

    if ($intent === 'duration') {
        $_SESSION['m9_pending_intent'] = 'duration';
        m9_json(true, $language === 'si'
            ? 'Repair duration predict කරන්න job number එක ඇතුළත් කරන්න. උදා: ORD-80'
            : 'Enter your job number to predict repair duration. Example: ORD-80');
    }

    $faq = m9_faq($conn, $normal, $language);
    m9_json(true, $faq !== null ? $faq : m9_gemini($message, $language));
}

/* Build the correct chatbot API URL even when this file is included by another page. */
$m9FilePath = str_replace('\\', '/', __FILE__);
$m9DocumentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$m9DocumentRoot = $m9DocumentRoot ? rtrim(str_replace('\\', '/', $m9DocumentRoot), '/') : '';
if ($m9DocumentRoot !== '' && strpos($m9FilePath, $m9DocumentRoot) === 0) {
    $m9ChatEndpoint = substr($m9FilePath, strlen($m9DocumentRoot));
} else {
    $m9ChatEndpoint = '/Predictive-Income-and-Repair-Time-Analytics-and-Forecasting-System-for-Multi9-Computer-Systems/chatbot.php';
}
?>

<style>
    :root{--m9-blue:#198754;--m9-dark:#0f5132;--m9-bg:#f2fbf6;--m9-text:#173528;--m9-border:#d7eadf}
    .m9-toggle{position:fixed;right:24px;bottom:24px;z-index:9998;width:58px;height:58px;border:0;border-radius:50%;background:linear-gradient(135deg,var(--m9-blue),var(--m9-dark));color:#fff;font-size:25px;box-shadow:0 12px 30px #1987545c;cursor:pointer}
    .m9-widget{position:fixed;right:24px;bottom:94px;z-index:9999;display:grid;grid-template-rows:auto minmax(0,1fr) auto;width:min(390px,calc(100vw - 32px));height:min(600px,calc(100vh - 125px));overflow:hidden;border:1px solid var(--m9-border);border-radius:22px;background:#fff;color:var(--m9-text);box-shadow:0 18px 55px #0f172a3d;font-family:Arial,sans-serif;animation:m9in .2s ease-out}
    .m9-widget[hidden]{display:none}@keyframes m9in{from{opacity:0;transform:translateY(12px) scale(.97)}to{opacity:1;transform:none}}
    .m9-head{display:flex;align-items:center;gap:12px;padding:15px 16px;color:#fff;background:linear-gradient(135deg,var(--m9-blue),var(--m9-dark))}
    .m9-avatar{display:grid;place-items:center;width:43px;height:43px;flex:0 0 43px;border:2px solid #ffffffb3;border-radius:50%;background:#fff;color:var(--m9-dark);font-size:13px;font-weight:800}
    .m9-title{display:flex;flex:1;flex-direction:column}.m9-title strong{font-size:15px}.m9-title span{margin-top:2px;color:#ffffffd6;font-size:12px}.m9-dot{display:inline-block;width:7px;height:7px;margin-right:5px;border-radius:50%;background:#86efac}
    .m9-close{width:34px;height:34px;border:0;border-radius:50%;background:#ffffff1f;color:#fff;font-size:25px;cursor:pointer}
    .m9-messages{overflow-y:auto;padding:18px 14px;background:var(--m9-bg);scroll-behavior:smooth}
    .m9-msg{width:fit-content;max-width:84%;margin-bottom:11px;padding:10px 13px;border-radius:16px;font-size:14px;line-height:1.52;overflow-wrap:anywhere;white-space:pre-wrap}
    .m9-bot{margin-right:auto;border:1px solid var(--m9-border);border-bottom-left-radius:5px;background:#fff;box-shadow:0 3px 10px #0f172a0d}.m9-user{margin-left:auto;border-bottom-right-radius:5px;background:var(--m9-blue);color:#fff}.m9-error{border-color:#fecaca;background:#fee2e2;color:#991b1b}
    .m9-actions{display:flex;flex-wrap:wrap;gap:7px;margin:4px 0 14px}.m9-actions button{padding:7px 10px;border:1px solid #b7dfc7;border-radius:999px;background:#effbf3;color:var(--m9-dark);font-size:12px;cursor:pointer}.m9-actions button:hover{background:var(--m9-blue);color:#fff}
    .m9-input{display:flex;align-items:center;gap:10px;padding:12px;border-top:1px solid var(--m9-border);background:#fff}.m9-input input{flex:1;min-width:0;height:44px;padding:0 14px;border:1px solid var(--m9-border);border-radius:13px;outline:0;background:#f8fffa;font-size:14px}.m9-input input:focus{border-color:#78c596;box-shadow:0 0 0 3px #19875421}.m9-send{display:grid;place-items:center;width:44px;height:44px;flex:0 0 44px;border:0;border-radius:13px;background:var(--m9-blue);color:#fff;font-size:18px;cursor:pointer}.m9-send:disabled,.m9-input input:disabled{opacity:.6;cursor:not-allowed}
    .m9-typing{display:inline-flex;gap:4px}.m9-typing i{width:6px;height:6px;border-radius:50%;background:#94a3b8;animation:m9dot 1.1s infinite}.m9-typing i:nth-child(2){animation-delay:.14s}.m9-typing i:nth-child(3){animation-delay:.28s}@keyframes m9dot{0%,60%,100%{transform:none;opacity:.55}30%{transform:translateY(-4px);opacity:1}}
    @media(max-width:520px){.m9-toggle{right:16px;bottom:16px}.m9-widget{right:8px;bottom:84px;width:calc(100vw - 16px);height:min(650px,calc(100vh - 100px));border-radius:18px}}
</style>

<button type="button" class="m9-toggle" id="m9Toggle" aria-label="Open Multi9 Assistant">💬</button>

<section class="m9-widget" id="m9Widget" aria-label="Multi9 Assistant" hidden>
    <header class="m9-head">
        <div class="m9-avatar">M9</div>
        <div class="m9-title">
            <strong>Multi9 Assistant</strong>
            <span><i class="m9-dot"></i>Online</span>
        </div>
        <button type="button" class="m9-close" id="m9Close" aria-label="Close">&times;</button>
    </header>

    <div class="m9-messages" id="chatBox" aria-live="polite">
        <div class="m9-msg m9-bot">ආයුබෝවන්! 👋 මම Multi9 Assistant.
Hi! I’m the Multi9 Assistant.
ඔබට අද මම උදව් කරන්නේ කොහොමද?</div>

        <div class="m9-actions" id="m9Actions">
            <button type="button" data-msg="Check repair status">Repair Status</button>
            <button type="button" data-msg="Predict repair duration">Repair Duration</button>
            <button type="button" data-msg="I need troubleshooting help">Troubleshooting</button>
            <button type="button" data-msg="Contact support">Contact Support</button>
        </div>
    </div>

    <form class="m9-input" id="m9Form">
        <input type="text" id="userInp" maxlength="600" placeholder="සිංහලෙන් හෝ English වලින් අහන්න..." autocomplete="off">
        <button type="submit" class="m9-send" aria-label="Send">➤</button>
    </form>
</section>

<script>
(function(){
    'use strict';
    var widget=document.getElementById('m9Widget');
    var toggle=document.getElementById('m9Toggle');
    var closeBtn=document.getElementById('m9Close');
    var form=document.getElementById('m9Form');
    var input=document.getElementById('userInp');
    var box=document.getElementById('chatBox');
    var actions=document.getElementById('m9Actions');
    var endpoint=<?php echo json_encode($m9ChatEndpoint . '?chat_api=1', JSON_UNESCAPED_SLASHES); ?>;
    var sending=false;
    if(!widget||!toggle||!form||!input||!box){return;}

    function openChat(){widget.hidden=false;toggle.style.display='none';setTimeout(function(){input.focus();},50)}
    function closeChat(){widget.hidden=true;toggle.style.display='';toggle.focus()}
    function bottom(){box.scrollTop=box.scrollHeight}
    function add(text,type,extra){var d=document.createElement('div');d.className='m9-msg '+(type==='user'?'m9-user':'m9-bot')+(extra?' '+extra:'');d.textContent=text;box.appendChild(d);bottom();return d}
    function typing(){var d=document.createElement('div');d.className='m9-msg m9-bot';d.innerHTML='<span class="m9-typing"><i></i><i></i><i></i></span>';box.appendChild(d);bottom();return d}
    function lock(value){sending=value;input.disabled=value;form.querySelector('button').disabled=value}

    async function sendMessage(forced){
        if(sending){return}
        var message=(typeof forced==='string'?forced:input.value).trim();
        if(!message){input.focus();return}
        add(message,'user');input.value='';lock(true);var wait=typing();
        try{
            var response=await fetch(endpoint,{
                method:'POST',credentials:'same-origin',
                headers:{'Content-Type':'application/json','Accept':'application/json'},
                body:JSON.stringify({message:message})
            });
            var data=await response.json();wait.remove();
            if(!response.ok||!data.success){throw new Error(data.message||'Request failed')}
            add(data.reply,'bot');
        }catch(error){if(wait.isConnected){wait.remove()}add('Chatbot temporarily unavailable. කරුණාකර නැවත උත්සාහ කරන්න.\n'+error.message,'bot','m9-error')}
        finally{lock(false);input.focus()}
    }

    toggle.addEventListener('click',openChat);
    closeBtn.addEventListener('click',closeChat);
    form.addEventListener('submit',function(e){e.preventDefault();sendMessage()});
    actions.addEventListener('click',function(e){var b=e.target.closest('button[data-msg]');if(b){sendMessage(b.getAttribute('data-msg'))}});
    window.sendMessage=sendMessage;
    window.sendQuickMessage=sendMessage;
}());
</script>