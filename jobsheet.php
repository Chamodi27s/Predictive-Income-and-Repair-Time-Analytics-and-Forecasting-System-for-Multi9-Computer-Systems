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
*{box-sizing:border-box;font-family:Segoe UI,Arial,sans-serif}
body{
  margin:0;
  background:#f6f4ef;
  color:#083024;
}

.page{
  width:92%;
  max-width:1200px;
  margin:30px auto 60px;
}

.grid{
  display:grid;
  grid-template-columns:1fr 360px;
  gap:35px;
}

/* LEFT FORM */
.card{
  background:#fdeff0;
  border:2px solid #7fd0b9;
  border-radius:22px;
  padding:25px;
}

.card label{
  display:block;
  margin-top:12px;
  font-weight:600;
}

.input, .textarea{
  width:100%;
  padding:12px;
  margin-top:8px;
  border-radius:10px;
  border:1px solid #ddd;
  font-size:15px;
}

.textarea{resize:vertical}

/* RIGHT SIDE */
.right{
  display:flex;
  flex-direction:column;
  gap:20px;
}

.pill{
  padding:15px;
  border-radius:10px;
}

.pill label{
  display:block;
  font-weight:600;
  margin-bottom:6px;
}

.green{background:#e8faf2}
.pink{background:#fdeef0}

.pill input{
  width:100%;
  border:none;
  background:transparent;
  font-size:15px;
}

/* SIGN BOX */
.sign{
  border:1px solid #ccc;
  background:#eee;
}

.sign .head{
  padding:10px;
  text-align:center;
  font-weight:700;
}

.sign .body{
  padding:20px;
  text-align:center;
}

.received{background:#f7a8a8}
.issued{background:#06b48c}

.line{
  margin:10px 0;
  color:#999;
  letter-spacing:3px;
}

/* TERMS */
.terms{
  background:#f1fff9;
  padding:18px;
  margin:20px 0;
  border-radius:6px;
}

/* BOTTOM */
.bottom{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:15px;
}

.bottom input{
  padding:8px;
  border-radius:6px;
  border:1px solid #ccc;
}

.print{
  padding:18px 40px;
  font-size:24px;
  font-weight:700;
  border:none;
  border-radius:10px;
  color:#fff;
  background:linear-gradient(#0aa37a,#056d52);
  cursor:pointer;
}

@media(max-width:900px){
  .grid{grid-template-columns:1fr}
}
</style>
</head>

<body>

<div class="page">

  <div class="grid">
    <!-- LEFT -->
    <div class="card">
      <label>Job No :</label>
      <input class="input" type="text">

      <label>Customer Id :</label>
      <input class="input" type="text">

      <label>Customer Name :</label>
      <input class="input" type="text">

      <label>Contact Number :</label>
      <input class="input" type="text">

      <label>Email :</label>
      <input class="input" type="email">

      <label>Order Id :</label>
      <input class="input" type="text">

      <label>Description :</label>
      <textarea class="textarea" rows="2"></textarea>
    </div>

    <!-- RIGHT -->
    <div class="right">
      <div class="pill green">
        <label>Device Name :</label>
        <input type="text" value="Mac book 14">
      </div>

      <div class="pill pink">
        <label>Services :</label>
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
    <p>*If the goods are collecting without repair an inspection fee will be charged.</p>
    <p>*Goods will be returned by producing this work order, paid full in CASH and thereafter Multi9 will have no responsibility for any new faults or damages.</p>
    <p>*Multi9 shall not be responsible for items not collected within 3 months of completing the repair.</p>
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
