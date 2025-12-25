<?php 
include("../config/db.php"); 


$inv_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

$sql = "SELECT i.*, j.job_no, c.customer_name 
        FROM invoice i 
        JOIN job j ON i.job_no = j.job_no 
        JOIN customer c ON j.phone_number = c.phone_number 
        WHERE i.invoice_no = '$inv_id'";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $inv = $result->fetch_assoc();
} else {
    die("<h2 style='color:red; text-align:center;'>දෝෂයකි: බිල්පත සොයාගත නොහැක!</h2>");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $inv_id; ?></title>
    <style>
        .invoice-box { max-width: 600px; margin: auto; padding: 30px; border: 1px solid #eee; font-family: 'Helvetica', sans-serif; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .title { font-size: 24px; color: #333; font-weight: bold; text-align: center; margin-bottom: 5px; }
        .sub-title { text-align: center; font-size: 14px; color: #666; margin-bottom: 20px; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .items-table { margin-top: 20px; }
        .items-table th { background: #f8f8f8; border: 1px solid #eee; padding: 10px; }
        .items-table td { border: 1px solid #eee; padding: 10px; }
        .total { font-weight: bold; background: #eee; font-size: 18px; }
        .no-print { margin-top: 20px; padding: 10px 20px; background: #2e7d32; color: white; border: none; cursor: pointer; border-radius: 5px; }
        .no-print:hover { background: #1b5e20; }
        @media print { .no-print { display: none; } .invoice-box { border: none; box-shadow: none; } }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="title">MULTI 9 COMPUTER SYSTEM</div>
        <div class="sub-title">Computer Repair & Services | Contact: 011XXXXXXX</div>
        <hr>
        
        <table class="info-table">
            <tr>
                <td><b>Invoice No:</b> #<?php echo $inv['invoice_no']; ?></td>
                <td style="text-align: right;"><b>Date:</b> <?php echo $inv['invoice_date']; ?></td>
            </tr>
            <tr>
                <td><b>Customer:</b> <?php echo $inv['customer_name']; ?></td>
                <td style="text-align: right;"><b>Job No:</b> <?php echo $inv['job_no']; ?></td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Service Charge</td>
                    <td style="text-align: right;"><?php echo number_format($inv['service_charge'], 2); ?></td>
                </tr>
                <tr>
                    <td>Replacement Parts</td>
                    <td style="text-align: right;"><?php echo number_format($inv['parts_total'], 2); ?></td>
                </tr>
                <tr class="total">
                    <td>Grand Total</td>
                    <td style="text-align: right;"><?php echo number_format($inv['grand_total'], 2); ?></td>
                </tr>
            </tbody>
        </table>
        
        <p style="margin-top: 40px; text-align: center; font-size: 12px; color: #999;">Thank you for your business!</p>
        
        <center>
            <button class="no-print" onclick="window.print()">Print Invoice</button>
            <button class="no-print" onclick="window.location.href='customer_list.php'" style="background:#666;">Back to List</button>
        </center>
    </div>
</body>
</html>