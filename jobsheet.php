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
        <div class="body"><div class="line">.......................</div><strong>Multi9 Computers</strong></div>
      </div>
      <div class="sign">
        <div class="head issued">Issued To</div>
        <div class="body"><div class="line">.......................</div><strong>Customer Signature</strong></div>
      </div>
    </div>
  </div>
  <div class="terms">
    <p>*If the goods are collecting without repair an inspection fee will be charged.</p>
  </div>
  <div class="bottom">
    <button class="print" onclick="window.print()">Print Now</button>
  </div>
</div>
</body>
</html>