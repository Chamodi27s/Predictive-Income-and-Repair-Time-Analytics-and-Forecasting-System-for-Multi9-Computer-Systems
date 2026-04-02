<?php
include 'db_config.php';
session_start();

date_default_timezone_set("Asia/Colombo");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$invoice_saved = false;
$saved_items = [];
$delay_fee = 0; 
$service_charge_val = 0;
$estimate_amount = 0;
$advance_paid = 0;

// --- SMSAPI.lk හරහා SMS යැවීමේ Function එක ---
function sendSMS($mobile, $message) {
    $api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
    $sender_id = "SMSAPI Demo"; 
    $url = "https://dashboard.smsapi.lk/api/v3/sms/send";

    $mobile = preg_replace('/[^0-9]/', '', $mobile); 
    if (substr($mobile, 0, 1) == '0') {
        $mobile = '94' . substr($mobile, 1);
    } elseif (strlen($mobile) == 9) {
        $mobile = '94' . $mobile;
    }

    $data = [
        "recipient" => $mobile,
        "sender_id" => $sender_id,
        "message"   => $message,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code == 200 || $http_code == 201);
}

// --- දත්ත ලබා ගැනීම ---
$job_no_param = $_GET['job_no'] ?? ($_POST['job_no'] ?? '');
if (!empty($job_no_param)) {
    $job_res = $conn->query("SELECT estimated_cost, advance_paid FROM job WHERE job_no = '$job_no_param'");
    if ($job_res->num_rows > 0) {
        $job_info = $job_res->fetch_assoc();
        $estimate_amount = floatval($job_info['estimated_cost'] ?? 0);
        $advance_paid = floatval($job_info['advance_paid'] ?? 0);
    }
}

// --- View Only Mode ---
if (isset($_GET['view_only']) && $_GET['view_only'] == 'true' && isset($_GET['job_no'])) {
    $v_job_no = $_GET['job_no'];
    $check_inv = $conn->query("SELECT * FROM invoice WHERE job_no = '$v_job_no'");
    if ($check_inv->num_rows > 0) {
        $inv_data = $check_inv->fetch_assoc();
        $_POST['invoice_no'] = $inv_data['invoice_no'];
        $_POST['service_charge'] = $inv_data['service_charge'];
        $_POST['parts_total'] = $inv_data['parts_total'];
        $_POST['grand_total'] = $inv_data['grand_total'];
        $service_charge_val = floatval($inv_data['service_charge']);
        $_POST['payment_status'] = ($inv_data['payment_status'] == 'Paid') ? 'Complete' : 'Pending';
        $saved_items = json_decode($inv_data['items_json'] ?? '[]', true);
        $invoice_saved = true;
    }
}

// --- Invoice Save Logic ---
if (isset($_POST['save_invoice'])) {
    $inv_no = $_POST['invoice_no'];
    $job_no = $_POST['job_no'];
    $inv_date = date("Y-m-d"); 
    $s_charge = floatval($_POST['service_charge']);
    $p_total = floatval($_POST['parts_total']);
    $g_total = floatval($_POST['grand_total']);
    $pay_status = $_POST['payment_status'] ?? 'Pending'; 

    $temp_items = [];
    if (isset($_POST['item_codes'])) {
        foreach ($_POST['item_codes'] as $key => $code) {
            $temp_items[] = [
                'code'  => $code,
                'name'  => $_POST['item_names'][$key],
                'price' => $_POST['item_prices'][$key],
                'qty'   => $_POST['item_qtys'][$key],
                'sub'   => floatval($_POST['item_prices'][$key]) * intval($_POST['item_qtys'][$key])
            ];
        }
    }
    $items_json = json_encode($temp_items);

    $conn->begin_transaction();
    try {
        $sql1 = "INSERT INTO invoice (invoice_no, job_no, invoice_date, service_charge, parts_total, grand_total, items_json, payment_status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
                 service_charge=VALUES(service_charge), parts_total=VALUES(parts_total), grand_total=VALUES(grand_total), items_json=VALUES(items_json), payment_status=VALUES(payment_status)";
        
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sssdddss", $inv_no, $job_no, $inv_date, $s_charge, $p_total, $g_total, $items_json, $pay_status);
        $stmt1->execute();
        
        $conn->query("UPDATE job_device SET device_status = 'Completed' WHERE job_no = '$job_no'");

        if (!empty($temp_items)) {
            foreach ($temp_items as $item) {
                $code = $item['code']; $qty = $item['qty'];
                $conn->query("UPDATE stock SET quantity = quantity - $qty WHERE item_code = '$code'");
            }
        }

        // --- Detailed SMS Logic (Updated with Parts, Prices & Service Charge) ---
        $cust_res = $conn->query("SELECT phone_number FROM job WHERE job_no = '$job_no'");
        $customer_mobile = $cust_res->fetch_assoc()['phone_number'] ?? '';

        if (!empty($customer_mobile)) {
            $balance = $g_total - $advance_paid;
            
            // Parts සහ ඒවායේ මිල ගණන් පේළියෙන් පේළියට සකස් කිරීම
            $parts_list_sms = "";
            if (!empty($temp_items)) {
                $parts_list_sms = "\n--- Parts ---";
                foreach ($temp_items as $item) {
                    $parts_list_sms .= "\n" . $item['name'] . ": Rs." . number_format($item['sub'], 0);
                }
            }
            
            $sms_msg = "Multi9: Inv #$inv_no\n"
                     . "Job: $job_no"
                     . $parts_list_sms . "\n"
                     . "S.Charge: Rs." . number_format($s_charge, 0) . "\n"
                     . "----------------\n"
                     . "Total: Rs." . number_format($g_total, 0) . "\n"
                     . "Adv.Paid: Rs." . number_format($advance_paid, 0) . "\n"
                     . "Balance: Rs." . number_format($balance, 0) . "\n"
                     . "Thank you!";

            if(sendSMS($customer_mobile, $sms_msg)) {
                $_SESSION['sms_msg'] = "Invoice Saved & SMS Sent!";
                $_SESSION['sms_type'] = "success";
            } else {
                $_SESSION['sms_msg'] = "Invoice Saved, but SMS Failed!";
                $_SESSION['sms_type'] = "error";
            }
        }

        $conn->commit();
        header("Location: generate_bill.php?view_only=true&job_no=" . urlencode($job_no));
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
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
        .grand-total { font-size: 24px; color: #065f46; font-weight: bold; border-top: 2px solid #065f46; margin-top: 10px; padding-top: 10px; }
        .btn { padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; margin-top: 10px; text-decoration: none; display: inline-block; text-align: center; box-sizing: border-box; }
        .btn-save { background: #065f46; color: white; }
        .btn-print { background: #3498db; color: white; }
        .btn-back { background: #6c757d; color: white; }
        .sms-alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="invoice-box">
    <?php if (isset($_SESSION['sms_msg'])): ?>
        <div class="sms-alert alert-<?= $_SESSION['sms_type'] ?>">
            <?= $_SESSION['sms_msg'] ?>
        </div>
        <?php unset($_SESSION['sms_msg'], $_SESSION['sms_type']); ?>
    <?php endif; ?>

    <div class="header">
        <h1>MULTI9 COMPUTER REPAIR</h1>
        <p>Invoice: <strong>#<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?></strong> | Job: <strong><?= htmlspecialchars($job_no_param) ?></strong></p>
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
                <tr>
                    <th>Description</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th style="text-align:right;">Total</th>
                </tr>
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
            <p>Original Estimate: <strong>Rs. <?= number_format($estimate_amount, 2) ?></strong></p>
            <p>Parts Total: Rs. <span id="p_disp">0.00</span></p>
            <p>Service Charge: 
                <?php if(!$invoice_saved): ?>
                    <input type="number" name="service_charge" id="s_charge" value="0" step="0.01" oninput="calcTotal()" style="text-align:right; padding:5px; width:120px;">
                <?php else: ?>
                    <strong>Rs. <?= number_format($service_charge_val, 2) ?></strong>
                    <input type="hidden" id="s_charge" value="<?= $service_charge_val ?>">
                <?php endif; ?>
            </p>
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
            <a href="invoice_list.php" class="btn btn-back">⬅ BACK</a>
        </div>
    </form>
</div>

<script>
const advAmount = <?= $advance_paid ?>;

function calcTotal() {
    let pTotal = 0;
    document.querySelectorAll('#billTable tbody tr').forEach(row => {
        let val = parseFloat(row.cells[3].innerText.replace(/,/g, ''));
        if(!isNaN(val)) pTotal += val;
    });
    
    let sCharge = parseFloat(document.getElementById('s_charge').value || 0);
    let gTotal = pTotal + sCharge;
    let balance = gTotal - advAmount;

    document.getElementById('p_disp').innerText = pTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('g_disp').innerText = gTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('balance_disp').innerText = balance.toLocaleString(undefined, {minimumFractionDigits: 2});
    
    document.getElementById('p_total_val').value = pTotal;
    document.getElementById('g_total_val').value = gTotal;
}

function addItem() {
    const sel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    if(!opt.value) return alert('Please select an item');
    
    const qty = document.getElementById('qty').value;
    const price = parseFloat(opt.dataset.price);
    const sub = price * qty;

    const row = `<tr>
        <td>${opt.dataset.name}
            <input type="hidden" name="item_names[]" value="${opt.dataset.name}">
            <input type="hidden" name="item_codes[]" value="${opt.value}">
            <input type="hidden" name="item_prices[]" value="${price}">
            <input type="hidden" name="item_qtys[]" value="${qty}">
        </td>
        <td>${price.toFixed(2)}</td>
        <td>${qty}</td>
        <td style="text-align:right;">${sub.toFixed(2)}</td>
    </tr>`;
    
    document.querySelector('#billTable tbody').innerHTML += row;
    calcTotal();
    sel.selectedIndex = 0;
}

window.onload = calcTotal;
</script>
</body>
</html>