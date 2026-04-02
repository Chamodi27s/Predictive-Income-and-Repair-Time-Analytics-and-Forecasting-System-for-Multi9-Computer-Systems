<?php
include 'db_config.php';
session_start();

date_default_timezone_set("Asia/Colombo");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$invoice_saved = false;
$saved_items = [];
$delay_fee = 0; 
$months_late = 0;
$service_charge_val = 0;
$estimate_amount = 0;
$advance_paid = 0;
$invoice_date = date("Y-m-d"); 

// --- SMSAPI.lk Function ---
function sendSMS($mobile, $message) {
    $api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
    $sender_id = "SMSAPI Demo"; 
    $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
    $mobile = preg_replace('/[^0-9]/', '', $mobile); 
    if (substr($mobile, 0, 1) == '0') { $mobile = '94' . substr($mobile, 1); }
    $data = ["recipient" => $mobile, "sender_id" => $sender_id, "message" => $message];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $api_key", "Content-Type: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200 || $http_code == 201);
}

// --- දත්ත ලබා ගැනීම (Job සහ Job Device Table එකෙන්) ---
$job_no_param = $_GET['job_no'] ?? ($_POST['job_no'] ?? '');
if (!empty($job_no_param)) {
    $job_res = $conn->query("
        SELECT j.estimated_cost, j.advance_paid, jd.completed_date, c.phone_number 
        FROM job j 
        LEFT JOIN job_device jd ON j.job_no = jd.job_no 
        LEFT JOIN customer c ON j.phone_number = c.phone_number
        WHERE j.job_no = '$job_no_param'
    ");
    
    if ($job_res->num_rows > 0) {
        $job_info = $job_res->fetch_assoc();
        $estimate_amount = floatval($job_info['estimated_cost'] ?? 0);
        $advance_paid = floatval($job_info['advance_paid'] ?? 0);
        $completion_date = $job_info['completed_date'];
        $customer_mobile = $job_info['phone_number'];

        // --- RENT LOGIC: Rs. 100 per every 30 days AFTER first 90 days ---
        if (!empty($completion_date)) {
            $comp_dt = new DateTime($completion_date);
            $today_dt = new DateTime();
            $interval = $comp_dt->diff($today_dt);
            $total_days = $interval->days;

            if ($total_days > 90) {
                $days_after_grace = $total_days - 90;
                $months_late = ceil($days_after_grace / 30); 
                $delay_fee = $months_late * 100; 
            }
        }
    }
}

// --- View Only Mode ---
if (isset($_GET['view_only']) && $_GET['view_only'] == 'true' && isset($_GET['job_no'])) {
    $v_job_no = $_GET['job_no'];
    $check_inv = $conn->query("SELECT * FROM invoice WHERE job_no = '$v_job_no'");
    if ($check_inv->num_rows > 0) {
        $inv_data = $check_inv->fetch_assoc();
        $_POST['invoice_no'] = $inv_data['invoice_no'];
        $service_charge_val = floatval($inv_data['service_charge']);
        $saved_items = json_decode($inv_data['items_json'] ?? '[]', true);
        $invoice_date = $inv_data['invoice_date'];
        $invoice_saved = true;
    }
}

// --- Invoice Save Logic + FULL DETAILS SMS ---
if (isset($_POST['save_invoice'])) {
    $inv_no = $_POST['invoice_no'];
    $job_no = $_POST['job_no'];
    $inv_date = date("Y-m-d"); 
    $s_charge = floatval($_POST['service_charge']);
    $p_total = floatval($_POST['parts_total']);
    $g_total = floatval($_POST['grand_total']);
    $pay_status = $_POST['payment_status'] ?? 'Pending'; 
    $balance = $g_total - $advance_paid;

    // Parts list for SMS
    $item_list_sms = "";
    $temp_items = [];
    if (isset($_POST['item_codes'])) {
        foreach ($_POST['item_codes'] as $key => $code) {
            $name = $_POST['item_names'][$key];
            $price = $_POST['item_prices'][$key];
            $qty = $_POST['item_qtys'][$key];
            $sub = floatval($price) * intval($qty);
            
            $temp_items[] = ['code'=>$code, 'name'=>$name, 'price'=>$price, 'qty'=>$qty, 'sub'=>$sub];
            $item_list_sms .= "\n- $name (Rs.$price x $qty)";
        }
    }
    $items_json = json_encode($temp_items);

    $conn->begin_transaction();
    try {
        $sql1 = "INSERT INTO invoice (invoice_no, job_no, invoice_date, service_charge, parts_total, grand_total, items_json, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE service_charge=VALUES(service_charge), parts_total=VALUES(parts_total), grand_total=VALUES(grand_total), items_json=VALUES(items_json), payment_status=VALUES(payment_status)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sssdddss", $inv_no, $job_no, $inv_date, $s_charge, $p_total, $g_total, $items_json, $pay_status);
        $stmt1->execute();
        
        $conn->query("UPDATE job_device SET device_status = 'Completed' WHERE job_no = '$job_no'");
        
        // --- Full Details SMS ---
        $message = "MULTI9 REPAIR\nInv: #$inv_no | Job: $job_no\n";
        $message .= "Est: Rs.".number_format($estimate_amount, 2)."\n";
        $message .= "Parts:".$item_list_sms."\n";
        $message .= "S.Charge: Rs.".number_format($s_charge, 2)."\n";
        if($delay_fee > 0) $message .= "Late Fee: Rs.".number_format($delay_fee, 2)."\n";
        $message .= "----------------\n";
        $message .= "Grand Total: Rs.".number_format($g_total, 2)."\n";
        $message .= "Advance Paid: Rs.".number_format($advance_paid, 2)."\n";
        $message .= "Balance Due: Rs.".number_format($balance, 2)."\n";
        $message .= "Thank you!";

        if (!empty($customer_mobile)) {
            sendSMS($customer_mobile, $message);
        }

        $conn->commit();
        header("Location: generate_bill.php?view_only=true&job_no=" . urlencode($job_no));
        exit();
    } catch (Exception $e) { $conn->rollback(); die("Error: " . $e->getMessage()); }
}

include 'navbar.php';
$stock_items = $conn->query("SELECT item_code, item_name, unit_price FROM stock WHERE quantity > 0")->fetch_all(MYSQLI_ASSOC);
$next_invoice_no = (($r = $conn->query("SELECT MAX(invoice_no) AS last FROM invoice")->fetch_assoc()) && $r['last']) ? $r['last'] + 1 : 1;
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>Invoice - Multi9 Repair</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 100px 0; }
        .invoice-box { max-width: 850px; margin: auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #065f46; padding-bottom: 15px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #065f46; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-section { background: #fdfdfd; padding: 20px; border-radius: 8px; border: 1px solid #eee; margin-top: 20px; text-align: right; }
        .rent-row { color: #d35400; font-weight: bold; background: #fff3e0; padding: 8px; border-radius: 5px; display: inline-block; margin-bottom: 10px; }
        .grand-total { font-size: 24px; color: #065f46; font-weight: bold; border-top: 2px solid #065f46; margin-top: 10px; padding-top: 10px; }
        .btn { padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; margin-top: 10px; text-decoration: none; display: inline-block; text-align: center; box-sizing: border-box; }
        .btn-save { background: #065f46; color: white; }
        .btn-print { background: #3498db; color: white; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <h1>MULTI9 COMPUTER REPAIR</h1>
        <p>Invoice: <strong>#<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?></strong> | Job: <strong><?= htmlspecialchars($job_no_param) ?></strong></p>
        <p>Date: <?= date('Y-m-d', strtotime($invoice_date)) ?></p>
    </div>

    <form method="POST">
        <input type="hidden" name="invoice_no" value="<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?>">
        <input type="hidden" name="job_no" value="<?= $job_no_param ?>">
        <input type="hidden" name="parts_total" id="p_total_val">
        <input type="hidden" name="grand_total" id="g_total_val">

        <?php if (!$invoice_saved): ?>
        <div class="no-print" style="margin-bottom: 20px; background:#e8f5e9; padding:15px; border-radius:8px; display:flex; gap:10px;">
            <select id="itemSelect" style="flex:3; padding:10px;">
                <option value="">-- Select Parts --</option>
                <?php foreach($stock_items as $i): ?>
                    <option value="<?= $i['item_code'] ?>" data-name="<?= $i['item_name'] ?>" data-price="<?= $i['unit_price'] ?>">
                        <?= $i['item_name'] ?> (Rs. <?= number_format($i['unit_price'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="qty" value="1" min="1" style="width:60px; padding:10px;">
            <button type="button" onclick="addItem()" class="btn" style="flex:1; background:#2ecc71; color:white; margin:0;">+ ADD</button>
        </div>
        <?php endif; ?>

        <table id="billTable">
            <thead>
                <tr><th>Description</th><th>Unit Price</th><th>Qty</th><th style="text-align:right;">Total</th></tr>
            </thead>
            <tbody>
                <?php if($invoice_saved): ?>
                    <?php foreach($saved_items as $it): ?>
                        <tr>
                            <td><?= htmlspecialchars($it['name']) ?></td>
                            <td><?= number_format($it['price'], 2) ?></td>
                            <td><?= $it['qty'] ?></td>
                            <td style="text-align:right;"><?= number_format($it['sub'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-section">
            <p style="color: #555;">Estimated Amount: Rs. <?= number_format($estimate_amount, 2) ?></p>
            <p>Parts Total: Rs. <span id="p_disp">0.00</span></p>
            <p>Service Charge: 
                <?php if(!$invoice_saved): ?>
                    <input type="number" name="service_charge" id="s_charge" value="0" step="0.01" oninput="calcTotal()" style="text-align:right; padding:5px; width:120px;">
                <?php else: ?>
                    <strong>Rs. <?= number_format($service_charge_val, 2) ?></strong>
                    <input type="hidden" id="s_charge" value="<?= $service_charge_val ?>">
                <?php endif; ?>
            </p>

            <?php if ($delay_fee > 0): ?>
            <div class="rent-row">
                ⚠ Late Fee (<?= $months_late ?> Month/s): Rs. <?= number_format($delay_fee, 2) ?>
            </div>
            <?php endif; ?>

            <div class="grand-total">Grand Total: Rs. <span id="g_disp">0.00</span></div>
            <p style="color: #d9534f; font-weight: bold; margin-top: 10px;">Advance Paid: Rs. <?= number_format($advance_paid, 2) ?></p>
            <p style="font-size: 20px;">Balance Due: <strong>Rs. <span id="balance_disp">0.00</span></strong></p>
        </div>

        <div class="no-print">
            <?php if (!$invoice_saved): ?>
                <button type="submit" name="save_invoice" class="btn btn-save">💾 SAVE INVOICE & SEND SMS</button>
            <?php else: ?>
                <button type="button" onclick="window.print()" class="btn btn-print">🖨️ PRINT INVOICE</button>
            <?php endif; ?>
            <a href="invoice_list.php" class="btn btn-back" style="background:#6c757d; color:white;">⬅ BACK</a>
        </div>
    </form>
</div>

<script>
const advAmount = <?= $advance_paid ?>;
const delayFee = <?= $delay_fee ?>; 

function calcTotal() {
    let pTotal = 0;
    document.querySelectorAll('#billTable tbody tr').forEach(row => {
        let valText = row.cells[3].innerText.replace(/,/g, '');
        let val = parseFloat(valText);
        if(!isNaN(val)) pTotal += val;
    });
    
    let sCharge = parseFloat(document.getElementById('s_charge').value || 0);
    let gTotal = pTotal + sCharge + delayFee;
    let balance = gTotal - advAmount;

    document.getElementById('p_disp').innerText = pTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('g_disp').innerText = gTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('balance_disp').innerText = balance.toLocaleString(undefined, {minimumFractionDigits: 2});
    
    if(document.getElementById('p_total_val')) document.getElementById('p_total_val').value = pTotal;
    if(document.getElementById('g_total_val')) document.getElementById('g_total_val').value = gTotal;
}

function addItem() {
    const sel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    if(!opt.value) return alert('Please select an item');
    const qty = document.getElementById('qty').value;
    const price = parseFloat(opt.dataset.price);
    const sub = price * qty;
    const row = `<tr><td>${opt.dataset.name}<input type="hidden" name="item_names[]" value="${opt.dataset.name}"><input type="hidden" name="item_codes[]" value="${opt.value}"><input type="hidden" name="item_prices[]" value="${price}"><input type="hidden" name="item_qtys[]" value="${qty}"></td><td>${price.toFixed(2)}</td><td>${qty}</td><td style="text-align:right;">${sub.toFixed(2)}</td></tr>`;
    document.querySelector('#billTable tbody').innerHTML += row;
    calcTotal();
    sel.selectedIndex = 0;
}
window.onload = calcTotal;
</script>
</body>
</html>