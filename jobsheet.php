<?php
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Jobsheet</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{
  box-sizing:border-box;
  font-family:Segoe UI,Arial,sans-serif
}

body{
  margin:0;
  background:#f6f4ef;
  color:#083024;
  font-size:14px;
}

/* PAGE */
.page{
  width:90%;
  max-width:1100px;
  margin:20px auto 40px;
}

/* GRID */
.grid{
  display:grid;
  grid-template-columns:1fr 300px;
  gap:25px;
}

/* LEFT CARD */
.card{
  background:#fdeff0;
  border:2px solid #7fd0b9;
  border-radius:15px;
  padding:18px;
}

/* COMPACT FORM */
.compact-form .row{
  display:grid;
  grid-template-columns:140px 1fr;
  align-items:center;
  gap:10px;
  margin-bottom:8px;
}

.compact-form label{
  font-size:13px;
  font-weight:600;
}

.compact-form input,
.compact-form textarea{
  width:100%;
  padding:6px 8px;
  font-size:13px;
  border-radius:6px;
  border:1px solid #ccc;
}

.compact-form textarea{
  resize:none;
}

.compact-form .full{
  grid-template-columns:1fr;
}

.compact-form .full label{
  margin-bottom:4px;
}

/* RIGHT SIDE */
.right{
  display:flex;
  flex-direction:column;
  gap:15px;
}

.pill{
  padding:12px;
  border-radius:8px;
}

.pill label{
  display:block;
  font-size:13px;
  font-weight:600;
  margin-bottom:4px;
}

.green{background:#e8faf2}
.pink{background:#fdeef0}

.pill input{
  width:100%;
  border:none;
  background:transparent;
  font-size:13px;
}

/* SIGN BOX */
.sign{
  border:1px solid #ccc;
  background:#eee;
  border-radius:8px;
}

.sign .head{
  padding:8px;
  text-align:center;
  font-weight:700;
  font-size:13px;
}

.sign .body{
  padding:15px;
  text-align:center;
}

.received{background:#f7a8a8}
.issued{background:#06b48c;color:#fff}

.line{
  margin:8px 0;
  color:#999;
  letter-spacing:3px;
}

/* TERMS */
.terms{
  background:#f1fff9;
  padding:14px;
  margin:15px 0;
  border-radius:6px;
  font-size:12.5px;
  line-height:1.4;
}

/* BOTTOM */
.bottom{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:15px;
}

.bottom input{
  padding:6px;
  border-radius:5px;
  border:1px solid #ccc;
  width:80px;
}

.print{
  padding:12px 25px;
  font-size:16px;
  font-weight:700;
  border:none;
  border-radius:8px;
  color:#fff;
  background:linear-gradient(#0aa37a,#056d52);
  cursor:pointer;
}

/* RESPONSIVE */
@media(max-width:900px){
  .grid{grid-template-columns:1fr}
}

/* PRINT */
@media print{
  nav,.print{display:none}
  body{background:#fff;color:#000}
  .page{width:100%;margin:0}
}
</style>
</head>

<body>

<div class="page">

  <div class="grid">

    <!-- LEFT FORM -->
    <div class="card compact-form">

      <div class="row">
        <label>Job No</label>
        <input type="text">
      </div>

      <div class="row">
        <label>Customer ID</label>
        <input type="text">
      </div>

      <div class="row">
        <label>Customer Name</label>
        <input type="text">
      </div>

      <div class="row">
        <label>Contact No</label>
        <input type="text">
      </div>

      <div class="row">
        <label>Email</label>
        <input type="email">
      </div>

      <div class="row">
        <label>Order ID</label>
        <input type="text">
      </div>

      <div class="row full">
        <label>Description</label>
        <textarea rows="2"></textarea>
      </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="right">

      <div class="pill green">
        <label>Device Name</label>
        <input type="text" value="MacBook 14">
      </div>

      <div class="pill pink">
        <label>Services</label>
        <input type="text" value="Display Replace">
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
    <p>* If goods are collected without repair an inspection fee will be charged.</p>
    <p>* Goods will be returned only by producing this work order and paying in full (CASH).</p>
    <p>* Multi9 is not responsible for items not collected within 3 months.</p>
  </div>

  <div class="bottom">
    <div>
      <strong>Inspection Charge: Rs.</strong>
      <input type="text">
    </div>
    <button class="print" onclick="window.print()">Print Now</button>
  </div>

</div>

</body>
</html>
