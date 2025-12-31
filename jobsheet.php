<?php 
include 'db_config.php';

include 'navbar.php';

// URL එකේ id එකක් තියෙනවාද කියලා බලනවා
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    // වැදගත්: මෙතන WHERE jd.job_device_id = '$id' ලෙස තිබිය යුතුමයි
    $query = "SELECT jd.*, j.phone_number, c.customer_name, c.email as customer_email
              FROM job_device jd
              INNER JOIN job j ON jd.job_no = j.job_no
              INNER JOIN customer c ON j.phone_number = c.phone_number
              WHERE jd.job_device_id = '$id'"; 
} else {
    // ID එකක් නැතිනම් අන්තිමටම දාපු ජොබ් එක පෙන්වනවා
    $query = "SELECT jd.*, j.phone_number, c.customer_name, c.email as customer_email
              FROM job_device jd
              INNER JOIN job j ON jd.job_no = j.job_no
              INNER JOIN customer c ON j.phone_number = c.phone_number
              ORDER BY jd.job_device_id DESC LIMIT 1";
}

$result = $conn->query($query);
$job = $result->fetch_assoc();

if (!$job) {
    die("දත්ත සොයාගත නොහැක.");
}

// Variables වලට දත්ත දැමීම
$job_no      = $job['job_no'] ?? '';
$cus_name    = $job['customer_name'] ?? '';
$phone       = $job['phone_number'] ?? '';
$email       = $job['customer_email'] ?? '';
$description = $job['description'] ?? '';
$device      = $job['device_name'] ?? '';
$service     = $job['issue_name'] ?? '';

// ශ්‍රී ලංකා වේලාව නිවැරදිව ලබා ගැනීමට
date_default_timezone_set('Asia/Colombo');

// 1. URL එකෙන් job_no එක ලබා ගැනීම
$job_no_param = isset($_GET['job_no']) ? $conn->real_escape_string($_GET['job_no']) : '';

if (!empty($job_no_param)) {
    $query = "SELECT j.*, c.customer_name, c.email as customer_email
              FROM job j
              INNER JOIN customer c ON j.phone_number = c.phone_number
              WHERE j.job_no = '$job_no_param' LIMIT 1"; 
} else {
    $query = "SELECT j.*, c.customer_name, c.email as customer_email
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
<title>Jobsheet - <?= $job_no ?></title>

<style>
    /* ඔයාගේ පරණ CSS එකම මෙතනට දාන්න */
    *{box-sizing:border-box;font-family:Segoe UI,Arial,sans-serif}
    body{margin:0;background:#f6f4ef;color:#083024}
    .page{width:92%;max-width:1200px;margin:30px auto 60px}
    .grid{display:grid;grid-template-columns:1fr 360px;gap:35px}
    .card{background:#fdeff0;border:2px solid #7fd0b9;border-radius:22px;padding:25px}
    .card label{display:block;margin-top:12px;font-weight:600}
    .input, .textarea{width:100%;padding:12px;margin-top:8px;border-radius:10px;border:1px solid #ddd;font-size:15px}
    .right{display:flex;flex-direction:column;gap:20px}
    .pill{padding:15px;border-radius:10px}
    .pill label{display:block;font-weight:600;margin-bottom:6px}
    .green{background:#e8faf2}
    .pink{background:#fdeef0}
    .pill input{width:100%;border:none;background:transparent;font-size:15px}
    .sign{border:1px solid #ccc;background:#eee}
    .sign .head{padding:10px;text-align:center;font-weight:700}
    .sign .body{padding:20px;text-align:center}
    .received{background:#f7a8a8}
    .issued{background:#06b48c}
    .line{margin:10px 0;color:#999;letter-spacing:3px}
    .terms{background:#f1fff9;padding:18px;margin:20px 0;border-radius:6px}
    .bottom{display:flex;justify-content:space-between;align-items:center;gap:15px}
    .bottom input{padding:8px;border-radius:6px;border:1px solid #ccc}
    .print{padding:18px 40px;font-size:24px;font-weight:700;border:none;border-radius:10px;color:#fff;background:linear-gradient(#0aa37a,#056d52);cursor:pointer}
    @media print { .print, nav { display: none; } body { background: white; } }

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif}
body{margin:0;background:#f6f4ef;color:#083024;font-size:13px; padding-top: 120px;   /* 🔥 navbar height */
    padding-left: 40px;
    padding-right: 40px;}

/* ===== SCREEN ONLY ===== */
.no-print{display:block}
.page{width:95%;max-width:1100px;margin:15px auto}
.grid{display:grid;grid-template-columns:1fr 320px;gap:20px}

/* ===== CARDS & FORM (Reduced Height) ===== */
.card{background:#fdeff0;border:1.5px solid #7fd0b9;border-radius:10px;padding:15px}
.compact-form .row{display:grid;grid-template-columns:130px 1fr;gap:8px;margin-bottom:6px}
.compact-form label{font-weight:600;font-size:13px; color:#444; align-self: center;}
.compact-form input{
   width:100%;padding:5px 8px;font-size:13px;border:1px solid #9fd9c6;border-radius:5px;background:#fff;color:#000; height: 30px;
}

/* ===== PILLS (Compact) ===== */
.right{display:flex;flex-direction:column;gap:10px}
.pill{border-radius:8px;padding:8px 10px}
.pill.device{background:#e8faf2;border:1px solid #7fd0b9}
.pill.service{background:#fdeef0;border:1px solid #f3a5b5}
.pill label{font-weight:600;font-size:11.5px;display:block;margin-bottom:1px}
.pill input{width:100%;border:none;background:transparent;font-size:13px;font-weight:600;color:#000; padding:0; height:auto}

/* ===== SIGNATURES ===== */
.sign{border:1px solid #bbb;border-radius:8px;overflow:hidden;margin-top:2px}
.sign .head{padding:5px;text-align:center;font-weight:700;font-size:12px}
.sign .head.received{background:#f7a8a8}
.sign .head.issued{background:#06b48c;color:#fff}
.sign .body{padding:12px;text-align:center;background:#fff}
.line{letter-spacing:3px;color:#999;margin-bottom:3px}

.terms{background:#f1fff9;border:1px dashed #9fd9c6;padding:12px;margin-top:20px;border-radius:8px;font-size:12px; line-height:1.5}
.bottom{display:flex;justify-content:flex-end;margin-top:15px}
.print{padding:10px 25px;font-size:15px;font-weight:700;border:none;border-radius:8px;background:linear-gradient(#0aa37a,#056d52);color:#fff;cursor:pointer}

/* HIDDEN ON SCREEN BY DEFAULT */
.company-header { display: none; }

@media(max-width:900px){ .grid{grid-template-columns:1fr} }

/* ===== PRINT STYLES ===== */
@media print{
   @page { margin: 8mm; size: A4; }
   body { margin: 0; background: #fff; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
   .no-print, .print, nav{ display:none !important; }
   
   .company-header{
       display: flex !important;
       justify-content: space-between;
       align-items: flex-start;
       border-bottom: 2px solid #083024;
       padding-bottom: 10px;
       margin-bottom: 20px;
   }

   .left-info { display: flex; align-items: flex-start; gap: 12px; }
   .company-logo img { width: 80px; height: auto; }
   .company-details { font-size: 12px; line-height: 1.4; color: #083024; }
   .company-details strong { font-size: 18px; display: block; margin-bottom: 2px; }

   .date-time-box {
       text-align: right;
       font-size: 12px;
       line-height: 1.6;
       min-width: 150px;
   }
   .dt-row { display: flex; justify-content: flex-end; gap: 8px; }
   .dt-label { font-weight: 700; color: #000; }
   .dt-value { font-weight: 500; color: #333; border-bottom: 1px solid #eee; min-width: 90px; text-align: right; }
   
   .page { width: 100%; margin: 0; }
   .card, .pill, .sign { border-width: 1px !important; }
}
 main
</style>
</head>
<body>
<div class="page">
  <div class="grid">
    <div class="card">
      <label>Job No :</label>
      <input class="input" type="text" value="<?= $job_no ?>">
      <label>Customer Name :</label>
      <input class="input" type="text" value="<?= $cus_name ?>">
      <label>Contact Number :</label>
      <input class="input" type="text" value="<?= $phone ?>">
      <label>Email :</label>
      <input class="input" type="email" value="<?= $email ?>">
      <label>Description :</label>
      <textarea class="textarea" rows="2"><?= $description ?></textarea>
    </div>

    <div class="right">
      <div class="pill green">
        <label>Device Name :</label>
        <input type="text" value="<?= $device ?>">
      </div>
      <div class="pill pink">
        <label>Services (Issue) :</label>
        <input type="text" value="<?= $service ?>">
      </div>
      <div class="sign">
        <div class="head received">Received By</div>
        <div class="body"><div class="line"></div><strong>Multi9 Computers</strong></div>
      </div>
      <div class="sign">
        <div class="head issued">Issued To</div>
        <div class="body"><div class="line"></div><strong>Customer Signature</strong></div>
      </div>
    </div>
  </div>
  <div class="terms">
    <p>*If the goods are collecting without repair an inspection fee will be charged.</p>
  </div>
  <div class="bottom">
    <button class="print" onclick="window.print()">Print Now</button>
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
                <div class="body"><strong>Multi9 Computers</strong></div>
            </div>
            <div class="sign">
                <div class="head issued">Issued To</div>
                <div class="body"><strong>Customer Signature</strong></div>
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

 main
</div>
</body>
</html>