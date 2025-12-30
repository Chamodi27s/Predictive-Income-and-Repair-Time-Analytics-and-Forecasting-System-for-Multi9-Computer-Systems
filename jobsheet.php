<?php 
include 'db_config.php';
include 'navbar.php';

// 1. URL එකෙන් job_no එක ලබා ගැනීම
$job_no_param = isset($_GET['job_no']) ? $conn->real_escape_string($_GET['job_no']) : '';

if (!empty($job_no_param)) {
    // පාරිභෝගික සහ ජොබ් දත්ත ලබා ගැනීම
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

// 2. අදාළ ජොබ් එකේ සියලුම ඩිවයිස් ලබා ගැනීම
$devices_res = $conn->query("SELECT * FROM job_device WHERE job_no = '$job_no'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Jobsheet - <?= $job_no ?></title>
<style>
    /* ඔයාගේ පරණ CSS එකමයි, කිසිම වෙනසක් නැහැ */
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
      <input class="input" type="text" value="<?= htmlspecialchars($job_main['customer_name']) ?>">
      <label>Contact Number :</label>
      <input class="input" type="text" value="<?= htmlspecialchars($job_main['phone_number']) ?>">
      <label>Email :</label>
      <input class="input" type="email" value="<?= htmlspecialchars($job_main['customer_email']) ?>">
    </div>
    
    <div class="right">
      <?php 
      // ඩිවයිස් එකක් හෝ කිහිපයක් තිබේ නම් ඒවා පෙන්වන ලූපය
      while($d = $devices_res->fetch_assoc()): 
      ?>
      <div class="device-group" style="margin-bottom: 10px;">
          <div class="pill green">
            <label>Device Name :</label>
            <input type="text" value="<?= htmlspecialchars($d['device_name']) ?>">
          </div>
          <div class="pill pink" style="margin-top:10px;">
            <label>Services (Issue) :</label>
            <input type="text" value="<?= htmlspecialchars($d['issue_name']) ?>">
          </div>
          <div style="padding: 5px 15px; font-size: 13px; color: #666;">
              Note: <?= htmlspecialchars($d['description']) ?>
          </div>
      </div>
      <?php endwhile; ?>

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