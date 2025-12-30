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
include 'db_config.php';   

// ශ්‍රී ලංකා වේලාව නිවැරදිව ලබා ගැනීමට
date_default_timezone_set('Asia/Colombo');

if (!isset($_GET['phone']) || empty($_GET['phone'])) {     
    die("Phone number not provided"); 
}

$phone = mysqli_real_escape_string($conn, $_GET['phone']);  

/* FETCH CUSTOMER */
$customer_q = mysqli_query($conn,"
    SELECT * FROM customer 
    WHERE phone_number='$phone'
");
$customer = mysqli_fetch_assoc($customer_q);
if (!$customer) die("Customer not found");

/* FETCH JOB */
$job_q = mysqli_query($conn,"
    SELECT j.*, t.name AS technician
    FROM job j
    LEFT JOIN technicians t ON j.technician_id=t.technician_id
    WHERE j.phone_number='$phone'
    ORDER BY j.job_date DESC
    LIMIT 1
");
$job = mysqli_fetch_assoc($job_q);
if (!$job) die("No jobs found");

/* FETCH DEVICES */
$devices_q = mysqli_query($conn,"
    SELECT * FROM job_device 
    WHERE job_no='{$job['job_no']}'
");
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
<title></title> 
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif}
body{margin:0;background:#f6f4ef;color:#083024;font-size:14px}

/* ===== SCREEN ONLY ===== */
.no-print{display:block}
.page{width:95%;max-width:1100px;margin:20px auto}
.grid{display:grid;grid-template-columns:1fr 300px;gap:25px}

/* ===== CARDS & FORM ===== */
.card{background:#fdeff0;border:1.5px solid #7fd0b9;border-radius:12px;padding:18px}
.compact-form .row{display:grid;grid-template-columns:140px 1fr;gap:10px;margin-bottom:8px}
.compact-form label{font-weight:600;font-size:13px}
.compact-form input, .compact-form textarea{
   width:100%;padding:7px;font-size:13px;border:1px solid #9fd9c6;border-radius:6px;background:#fff
}
.compact-form textarea{resize:none}

/* ===== PILLS ===== */
.right{display:flex;flex-direction:column;gap:10px}
.pill{border-radius:8px;padding:8px}
.pill.device{background:#e8faf2;border:1px solid #7fd0b9}
.pill.service{background:#fdeef0;border:1px solid #f3a5b5}
.pill label{font-weight:600;font-size:12.5px;display:block;margin-bottom:2px}
.pill input{width:100%;border:none;background:transparent;font-size:13px;font-weight:500}

/* ===== SIGNATURES ===== */
.sign{border:1px solid #bbb;border-radius:8px;overflow:hidden;margin-top:5px}
.sign .head{padding:6px;text-align:center;font-weight:700;font-size:12.5px}
.sign .head.received{background:#f7a8a8}
.sign .head.issued{background:#06b48c;color:#fff}
.sign .body{padding:14px;text-align:center;background:#fff}
.line{letter-spacing:3px;color:#999;margin-bottom:5px}

.terms{background:#f1fff9;border:1px dashed #9fd9c6;padding:12px;margin-top:15px;border-radius:8px;font-size:12.5px}
.bottom{display:flex;justify-content:space-between;margin-top:15px;align-items:center}
.bottom input{border:1px solid #9fd9c6;width:90px;padding:5px;border-radius:5px}
.print{padding:10px 22px;font-size:15px;font-weight:700;border:none;border-radius:8px;background:linear-gradient(#0aa37a,#056d52);color:#fff;cursor:pointer}

/* HIDDEN ON SCREEN */
.company-header { display: none; }

@media(max-width:900px){ .grid{grid-template-columns:1fr} }

/* ===== PRINT STYLES ===== */
@media print{
   @page { margin: 0; size: auto; }
   body { margin: 1.5cm; background: #fff; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
   .no-print, .print{ display:none !important; }
   
   .company-header{
       display: flex !important;
       justify-content: space-between;
       align-items: flex-start;
       border-bottom: 2px solid #083024;
       padding-bottom: 15px;
       margin-bottom: 25px;
   }

   .left-info { display: flex; align-items: flex-start; gap: 15px; }
   .company-logo img { width: 90px; height: auto; }
   .company-details { font-size: 13px; line-height: 1.5; color: #083024; }
   .company-details strong { font-size: 18px; display: block; margin-bottom: 2px; }

   /* Professional Date & Time Layout */
   .date-time-box {
       text-align: right;
       font-size: 14px;
       line-height: 1.8;
       min-width: 180px;
   }
   .dt-row { display: flex; justify-content: flex-end; gap: 10px; }
   .dt-label { font-weight: 700; color: #000; }
   .dt-value { font-weight: 500; color: #333; border-bottom: 1px solid #eee; min-width: 100px; text-align: right; }
}
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
<div class="no-print">
   <?php include 'navbar.php'; ?>
</div>

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

    <div class="grid">
       <div class="card compact-form">
           <div class="row"><label>Job No</label><input value="<?= $job['job_no'] ?>" readonly></div>
           <div class="row"><label>Customer Name</label><input value="<?= htmlspecialchars($customer['customer_name']) ?>" readonly></div>
           <div class="row"><label>Contact No</label><input value="<?= $customer['phone_number'] ?>" readonly></div>
           <div class="row"><label>Email</label><input value="<?= htmlspecialchars($customer['email']) ?>" readonly></div>
           <div class="row"><label>Technician</label><input value="<?= $job['technician'] ?: 'Not Assigned' ?>" readonly></div>
           <div class="row full">
               <label>Description</label>
               <textarea rows="4" readonly><?php 
                   mysqli_data_seek($devices_q,0); 
                   while($d=mysqli_fetch_assoc($devices_q)){
                       echo $d['device_name']." - ".$d['issue_name']."\n"; 
                   } 
               ?></textarea>
           </div>
       </div>

       <div class="right">
           <?php mysqli_data_seek($devices_q,0); while($d=mysqli_fetch_assoc($devices_q)): ?>
           <div class="pill device"><label>Device</label><input value="<?= $d['device_name'] ?>"></div>
           <div class="pill service"><label>Service</label><input value="<?= $d['issue_name'] ?>"></div>
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
       <p>* If goods are collected without repair an inspection fee will be charged.</p>
       <p>* Goods will be returned by producing this work order,paid full in CASH and there after multi9 will have no responsibility for any new faults or damagers.</p>
       <p>* Multi9 shall not be responsible for the items which are not collected within 3 months of completing the repair.</p>
    </div>

    <div class="bottom">
       <div><strong>Inspection Charge Rs:</strong> <input></div>
       <button class="print" onclick="window.print()">Print Now</button>
    </div>

</div>
</body>
</html>