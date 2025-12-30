<?php 
include 'db_config.php';
include 'navbar.php';
// include 'navbar.php'; // Print කරන පේජ් එකක් නිසා සාමාන්‍යයෙන් navbar එක අවශ්‍ය නොවේ

// URL එකෙන් job_no එක එනවාද කියා පරීක්ෂා කිරීම
if (isset($_GET['job_no']) && !empty($_GET['job_no'])) {
    $job_no = $conn->real_escape_string($_GET['job_no']);
    
    // එම job_no එකට අදාළ සියලුම device සහ customer විස්තර ලබා ගැනීම
    $query = "SELECT jd.*, j.job_date, c.customer_name, c.phone_number, c.email as customer_email
              FROM job_device jd
              INNER JOIN job j ON jd.job_no = j.job_no
              INNER JOIN customer c ON j.phone_number = c.phone_number
              WHERE jd.job_no = '$job_no'"; 
} else {
    die("<div style='padding:20px; color:red;'>ජොබ් අංකයක් සොයාගත නොහැක.</div>");
}

$result = $conn->query($query);

if (!$result || $result->num_rows == 0) {
    die("<div style='padding:20px; color:red;'>දත්ත හමු වූයේ නැත.</div>");
}

// පාරිභෝගික විස්තර පොදු බැවින් මුල් පේළියෙන් ලබා ගනිමු
$data_arr = [];
while($row = $result->fetch_assoc()) {
    $data_arr[] = $row;
}

$first = $data_arr[0];
$cus_name  = $first['customer_name'] ?? 'N/A';
$phone     = $first['phone_number'] ?? ''; 
$email     = $first['customer_email'] ?? ''; 
$order_id  = $first['job_no'] ?? '';
$inspection = "0.00"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Jobsheet - <?= $job_no ?></title>
<style>
/* UI එක වෙනස් කර නැත - ඔයාගේ මුල් CSS එකම භාවිතා වේ */
*{box-sizing:border-box;font-family:Segoe UI,Arial,sans-serif}
body{margin:0;background:#f6f4ef;color:#083024}
.page{width:92%;max-width:1200px;margin:30px auto 60px}
.grid{display:grid;grid-template-columns:1fr 360px;gap:35px}
.card{background:#fdeff0;border:2px solid #7fd0b9;border-radius:22px;padding:25px; height: fit-content;}
.card label{display:block;margin-top:12px;font-weight:600}
.input, .textarea{width:100%;padding:12px;margin-top:8px;border-radius:10px;border:1px solid #ddd;font-size:15px; background: #fff;}
.textarea{resize:vertical}
.right{display:flex;flex-direction:column;gap:20px}
.pill{padding:15px;border-radius:10px}
.pill label{display:block;font-weight:600;margin-bottom:6px}
.green{background:#e8faf2}
.pink{background:#fdeef0}
.pill input{width:100%;border:none;background:transparent;font-size:15px; font-weight: 500;}
.sign{border:1px solid #ccc;background:#eee; border-radius: 10px; overflow: hidden;}
.sign .head{padding:10px;text-align:center;font-weight:700}
.sign .body{padding:20px;text-align:center}
.received{background:#f7a8a8}
.issued{background:#06b48c}
.line{margin:10px 0;color:#999;letter-spacing:3px}
.terms{background:#f1fff9;padding:18px;margin:20px 0;border-radius:6px}
.bottom{display:flex;justify-content:space-between;align-items:center;gap:15px}
.bottom input{padding:8px;border-radius:6px;border:1px solid #ccc}
.print{padding:18px 40px;font-size:24px;font-weight:700;border:none;border-radius:10px;color:#fff;background:linear-gradient(#0aa37a,#056d52);cursor:pointer}

/* Device එකකට වඩා ඇති විට පෙන්වන box එක */
.device-item {
    border-bottom: 1px dashed #7fd0b9;
    padding-bottom: 15px;
    margin-bottom: 15px;
}
.device-item:last-child { border: none; }

@media print {
    .print, nav, .navbar { display: none !important; }
    body { background: white; }
    .page { margin: 0; width: 100%; }
    .card { border: 1px solid #7fd0b9; }
}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="page">
  <div class="grid">
    <div class="card">
      <div style="display: flex; justify-content: space-between;">
          <div><label>Job No :</label><input class="input" type="text" value="<?= $job_no ?>"></div>
          <div><label>Date :</label><input class="input" type="text" value="<?= date("Y-m-d", strtotime($first['job_date'])) ?>"></div>
      </div>

      <label>Customer Name :</label>
      <input class="input" type="text" value="<?= $cus_name ?>">

      <label>Contact Number :</label>
      <input class="input" type="text" value="<?= $phone ?>">

      <label>Email :</label>
      <input class="input" type="email" value="<?= $email ?>">

      <hr style="margin: 25px 0; border: 0; border-top: 1px solid #7fd0b9;">
      
      <?php foreach($data_arr as $d): ?>
      <div class="device-item">
          <label style="color: #056d52;">Device Name :</label>
          <input class="input" type="text" value="<?= $d['device_name'] ?>" style="font-weight: bold;">

          <label>Description / Condition :</label>
          <textarea class="textarea" rows="2"><?= $d['description'] ?></textarea>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="right">
      <div class="pill green">
        <label>Total Devices :</label>
        <input type="text" value="<?= count($data_arr) ?> Unit(s)">
      </div>

      <div class="pill pink">
        <label>Primary Issue(s) :</label>
        <?php 
          $issues = array_column($data_arr, 'issue_name');
          echo '<input type="text" value="'.implode(", ", $issues).'">';
        ?>
      </div>

      <div class="sign">
        <div class="head received">Received By</div>
        <div class="body">
          <div class="line">.......................</div>
          <strong>Multi9 Computers</strong>
        </div>
      </div>

      <div class="sign">
        <div class="head issued">Issued To</div>
        <div class="body">
          <div class="line">.......................</div>
          <strong>Customer Signature</strong>
        </div>
      </div>
    </div>
  </div>

  <div class="terms">
    <p>*If the goods are collecting without repair an inspection fee will be charged.</p>
    <p>*Goods will be returned by producing this work order, paid full in CASH.</p>
  </div>

  <div class="bottom">
    <div>
      <strong>Inspection Charge: Rs.</strong>
      <input type="text" value="<?= $inspection ?>">
    </div>
    <button class="print" onclick="window.print()">Print Now</button>
  </div>
</div>

</body>
</html>