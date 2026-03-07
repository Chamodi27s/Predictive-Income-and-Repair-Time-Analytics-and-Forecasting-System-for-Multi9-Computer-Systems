<?php 
include 'db_config.php';

// ශ්‍රී ලංකා වේලාව නිවැරදිව ලබා ගැනීමට
date_default_timezone_set('Asia/Colombo');

// URL එකෙන් job_no එක ලබා ගැනීම
$job_no_param = isset($_GET['job_no']) ? $conn->real_escape_string($_GET['job_no']) : '';

if (!empty($job_no_param)) {
    $query = "SELECT j.*, c.customer_name, c.email as customer_email, c.phone_number
              FROM job j
              INNER JOIN customer c ON j.phone_number = c.phone_number
              WHERE j.job_no = '$job_no_param' LIMIT 1"; 
} else {
    $query = "SELECT j.*, c.customer_name, c.email as customer_email, c.phone_number
              FROM job j
              INNER JOIN customer c ON j.phone_number = c.phone_number
              ORDER BY j.job_no DESC LIMIT 1";
}

$result = $conn->query($query);
$job_main = $result->fetch_assoc();

if (!$job_main) {
    die("දත්ත සොයාගත නොහැක.");
}

$job_no = $job_main['job_no'];
$devices_res = $conn->query("SELECT * FROM job_device WHERE job_no = '$job_no'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JobSheet_<?= $job_no ?></title>

<style>
/* මෙතැනින් Browser එකේ Headers සහ Footers ඉවත් කරයි */
@page {
    margin: 5mm; 
    size: auto;
}

*{box-sizing:border-box;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif}
body{margin:0;background:#f6f4ef;color:#083024;font-size:13px; padding: 20px; transition: background 0.3s ease;}

.page{width:95%;max-width:1100px;margin:0 auto; background:#fff; padding:30px; border-radius:10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); transition: background 0.3s ease;}
.grid{display:grid;grid-template-columns:1fr 320px;gap:20px; margin-top: 20px;}

.company-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #083024;
    padding-bottom: 15px;
    margin-bottom: 25px;
}
.left-info { display: flex; align-items: flex-start; gap: 15px; }
.company-logo img { width: 90px; }
.company-details { font-size: 13px; line-height: 1.5; color: #083024; }
.company-details strong { font-size: 20px; color: #083024; display: block; margin-bottom: 4px; }
.date-time-box { text-align: right; font-size: 14px; line-height: 1.8; }
.dt-row { display: flex; justify-content: flex-end; gap: 10px; }
.dt-label { font-weight: 700; }

.card{background:#fdeff0;border:1.5px solid #7fd0b9;border-radius:10px;padding:15px}
.compact-form .row{display:grid;grid-template-columns:130px 1fr;gap:8px;margin-bottom:6px}
.compact-form label{font-weight:600;font-size:13px; color:#444; align-self: center;}
.compact-form input{
   width:100%;padding:5px 8px;font-size:13px;border:1px solid #9fd9c6;border-radius:5px;background:#fff;color:#000; height: 30px;
}

.right{display:flex;flex-direction:column;gap:10px}
.pill{border-radius:8px;padding:8px 10px}
.pill.device{background:#e8faf2;border:1px solid #7fd0b9}
.pill.service{background:#fdeef0;border:1px solid #f3a5b5}
.pill label{font-weight:600;font-size:11.5px;display:block;margin-bottom:1px}
.pill input{width:100%;border:none;background:transparent;font-size:13px;font-weight:600;color:#000; padding:0; height:auto}

.sign{border:1px solid #bbb;border-radius:8px;overflow:hidden;margin-top:2px}
.sign .head{padding:5px;text-align:center;font-weight:700;font-size:12px}
.sign .head.received{background:#f7a8a8}
.sign .head.issued{background:#06b48c;color:#fff}
.sign .body{padding:12px;text-align:center;background:#fff}

.sig-line {
    border-bottom: 1px dotted #999;
    width: 80%;
    margin: 10px auto 8px auto;
}

.terms{background:#f1fff9;border:1px dashed #9fd9c6;padding:12px;margin-top:20px;border-radius:8px;font-size:12px; line-height:1.5}
.bottom{display:flex;justify-content:flex-end;margin-top:15px}
.print{padding:10px 25px;font-size:15px;font-weight:700;border:none;border-radius:8px;background:linear-gradient(#0aa37a,#056d52);color:#fff;cursor:pointer}

/* ===============================
    DARK MODE SCREEN STYLES
================================ */
body.dark-mode {
    background: #0f172a !important;
    color: #f1f5f9 !important;
}
body.dark-mode .page { background: #1e293b !important; border: 1px solid #334155 !important; }
body.dark-mode .company-header { border-bottom-color: #334155 !important; }
body.dark-mode .company-details, body.dark-mode .company-details strong, body.dark-mode .dt-value, body.dark-mode .dt-label { color: #f1f5f9 !important; }
body.dark-mode .card { background: #334155 !important; border-color: #475569 !important; }
body.dark-mode .compact-form label { color: #cbd5e1 !important; }
body.dark-mode .compact-form input { background: #0f172a !important; border-color: #475569 !important; color: #ffffff !important; }
body.dark-mode .pill.device { background: #064e3b !important; border-color: #065f46 !important; }
body.dark-mode .pill.service { background: #7f1d1d !important; border-color: #991b1b !important; }
body.dark-mode .pill label { color: #e2e8f0 !important; }
body.dark-mode .pill input { color: #ffffff !important; }
body.dark-mode .sign .body { background: #1e293b !important; color: #f1f5f9 !important; }
body.dark-mode .terms { background: #0f172a !important; border-color: #334155 !important; color: #cbd5e1 !important; }

/* ===============================
    PRINT STYLES (ENHANCED)
================================ */
@media print {
    /* Print එකේදී Dark mode අක්‍රිය කර සම්පූර්ණයෙන්ම සුදු පැහැති පසුබිමක් ලබා දෙයි */
    body, body.dark-mode { 
        background: white !important; 
        color: black !important; 
        padding: 0 !important; 
        margin: 0 !important;
        -webkit-print-color-adjust: exact; /* Background වර්ණ ලබා ගැනීමට මෙය අත්‍යවශ්‍යයි */
        print-color-adjust: exact;
    }
    
    .page, body.dark-mode .page { 
        width: 100% !important; 
        max-width: 100% !important;
        margin: 0 !important; 
        padding: 15px !important;
        box-shadow: none !important; 
        border: none !important; 
        background: white !important;
    }

    .no-print, .print, .bottom { display: none !important; }

    /* Headers සහ Text පැහැදිලි කිරීමට */
    .company-header, body.dark-mode .company-header { border-bottom: 2px solid #083024 !important; }
    .company-details strong, body.dark-mode .company-details strong, 
    .dt-label, .dt-value, body.dark-mode .dt-value { color: #083024 !important; }
    
    /* Input field අකුරු නොපෙනී යාම වැළැක්වීමට */
    .card, body.dark-mode .card { background: #fdeff0 !important; border: 1.5px solid #7fd0b9 !important; }
    .compact-form label { color: black !important; }
    .compact-form input, body.dark-mode .compact-form input { 
        background: white !important; 
        color: black !important; 
        border: 1px solid #9fd9c6 !important; 
        opacity: 1 !important;
    }
    
    /* Pill වර්ණ මුද්‍රණයේදී පවත්වා ගැනීම */
    .pill.device, body.dark-mode .pill.device { background: #e8faf2 !important; border: 1px solid #7fd0b9 !important; }
    .pill.service, body.dark-mode .pill.service { background: #fdeef0 !important; border: 1px solid #f3a5b5 !important; }
    .pill label { color: black !important; }
    .pill input, body.dark-mode .pill input { color: black !important; font-weight: bold !important; }
    
    /* අත්සන් සඳහා කොටස් */
    .sign .body, body.dark-mode .sign .body { background: white !important; color: black !important; border-top: 1px solid #bbb !important; }
    .sig-line { border-bottom: 1px dotted #000 !important; }

    /* කොන්දේසි (Terms) */
    .terms, body.dark-mode .terms { background: #f1fff9 !important; border: 1px dashed #9fd9c6 !important; color: black !important; }
}

@media(max-width:900px){ .grid{grid-template-columns:1fr} }
</style>
</head>
<body>

<div class="page">
    <div class="company-header">
        <div class="left-info">
            <div class="company-logo">
                <img src="uploads/devices/logo.png" alt="Logo">
            </div>
            <div class="company-details">
                <strong>Multi9 Computer Systems</strong>
                No. 97/8, Stanley Thilakarathne Mawatha, Nugegoda<br>
                Tel: 0115 299 147 | 0772 022 701<br>
                Email: workshop@multi9.lk
            </div>
        </div>
        
        <div class="date-time-box">
            <div class="dt-row">
                <span class="dt-label">Date:</span>
                <span class="dt-value"><?= date('Y-m-d') ?></span>
            </div>
            <div class="dt-row">
                <span class="dt-label">Time:</span>
                <span class="dt-value"><?= date('h:i A') ?></span>
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="card compact-form">
            <div class="row"><label>Job No</label><input value="<?= $job_no ?>" readonly></div>
            <div class="row"><label>Customer Name</label><input value="<?= htmlspecialchars($job_main['customer_name']) ?>" readonly></div>
            <div class="row"><label>Contact No</label><input value="<?= htmlspecialchars($job_main['phone_number']) ?>" readonly></div>
            <div class="row"><label>Email Address</label><input value="<?= htmlspecialchars($job_main['customer_email']) ?>" readonly></div>
        </div>

        <div class="right">
            <?php mysqli_data_seek($devices_res, 0); while($d = $devices_res->fetch_assoc()): ?>
            <div class="device-item">
                <div class="pill device">
                    <label>Device Name</label>
                    <input value="<?= htmlspecialchars($d['device_name']) ?>" readonly>
                </div>
                <div class="pill service" style="margin-top: 5px;">
                    <label>Service (Issue)</label>
                    <input value="<?= htmlspecialchars($d['issue_name']) ?>" readonly>
                </div>
            </div>
            <?php endwhile; ?>

            <div class="sign">
                <div class="head received">Received By</div>
                <div class="body">
                    <div class="sig-line"></div>
                    <strong>Multi9 Computers</strong>
                </div>
            </div>
            <div class="sign">
                <div class="head issued">Issued To</div>
                <div class="body">
                    <div class="sig-line"></div>
                    <strong>Customer Signature</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="terms">
        <p><strong>Terms & Conditions:</strong></p>
        <p>* If goods are collected without repair an inspection fee will be charged.</p>
        <p>* Goods will be returned by producing this work order, paid full in CASH and there after multi9 will have no responsibility for any new faults or damagers.</p>
        <p>* Multi9 shall not be responsible for the items which are not collected within 3 months of completing the repair.</p>
    </div>

    <div class="bottom no-print">
        <button class="print" onclick="window.print()">Print Now</button>
    </div>
</div>

<script>
// Dark Mode පරීක්ෂාව සහ යෙදීම
function applySystemTheme() {
    const isDark = localStorage.getItem('darkMode') === 'enabled';
    if (isDark) {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
}
applySystemTheme();

// මුද්‍රණයෙන් පසු Redirect කිරීම
window.onafterprint = function() {
    window.location.href = "customer_details.php?phone=<?= urlencode($job_main['phone_number']) ?>";
};
</script>

</body>
<?php include 'chatbot.php'; ?>
</html>