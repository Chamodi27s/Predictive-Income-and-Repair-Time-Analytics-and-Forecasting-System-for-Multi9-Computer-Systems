<?php 
include 'db_config.php';
$inv_id = $_GET['id'];
$inv = $conn->query("SELECT * FROM invoice JOIN jobs ON invoice.job_no = jobs.job_no JOIN customer ON jobs.cust_phone = customer.phone WHERE invoice_no='$inv_id'")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .invoice-box { max-width: 600px; margin: auto; padding: 30px; border: 1px solid #eee; font-family: 'Helvetica', sans-serif; }
        .title { font-size: 24px; color: #333; font-weight: bold; text-align: center; }
        table { width: 100%; line-height: inherit; text-align: left; }
        .total { font-weight: bold; background: #eee; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="title">MULTI 9 COMPUTER SYSTEM</div>
        <hr>
        <table>
            <tr><td><b>Invoice No:</b> #<?php echo $inv_id; ?></td><td><b>Date:</b> <?php echo $inv['date']; ?></td></tr>
            <tr><td><b>Customer:</b> <?php echo $inv['name']; ?></td><td><b>Job No:</b> <?php echo $inv['job_no']; ?></td></tr>
        </table>
        <br>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr><th>Description</th><th>Amount (Rs.)</th></tr>
            <tr><td>Service Charge</td><td><?php echo number_format($inv['service_charge'], 2); ?></td></tr>
            <tr class="total"><td>Grand Total</td><td><?php echo number_format($inv['grand_total'], 2); ?></td></tr>
        </table>
        <br>
        <button class="no-print" onclick="window.print()">Print Invoice</button>
    </div>
</body>
</html>