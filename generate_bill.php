<?php
include 'db_config.php';
include 'navbar.php';

date_default_timezone_set("Asia/Colombo");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$invoice_saved = false;
$saved_items = [];

// --- නව එකතු කිරීම: View Only Mode (ලිස්ට් එකෙන් එන දත්ත බැලීමට) ---
if (isset($_GET['view_only']) && $_GET['view_only'] == 'true' && isset($_GET['job_no'])) {
    $v_job_no = $_GET['job_no'];
    $check_inv = $conn->query("SELECT * FROM invoice WHERE job_no = '$v_job_no'");
    if ($check_inv->num_rows > 0) {
        $inv_data = $check_inv->fetch_assoc();
        $_POST['invoice_no'] = $inv_data['invoice_no'];
        $_POST['service_charge'] = $inv_data['service_charge'];
        $_POST['parts_total'] = $inv_data['parts_total'];
        $_POST['grand_total'] = $inv_data['grand_total'];
        $_POST['payment_status'] = $inv_data['payment_status'];
        $saved_items = json_decode($inv_data['items_json'], true);
        $invoice_saved = true;
    }
}
// ------------------------------------------------------------

$delay_fee = isset($_GET['fee']) ? floatval($_GET['fee']) : (isset($_POST['delay_fee']) ? floatval($_POST['delay_fee']) : 0);

if (isset($_POST['save_invoice'])) {
    $inv_no = $_POST['invoice_no'];
    $job_no = $_POST['job_no'];
    $inv_date = date("Y-m-d"); 
    
    $s_charge = floatval($_POST['service_charge']);
    $p_total = floatval($_POST['parts_total']);
    $g_total = floatval($_POST['grand_total']);
    $pay_status = $_POST['payment_status']; 

    $temp_items = [];
    $item_names_list = [];
    if (isset($_POST['item_codes'])) {
        foreach ($_POST['item_codes'] as $key => $code) {
            $name = $_POST['item_names'][$key];
            $temp_items[] = [
                'code'  => $code,
                'name'  => $name,
                'price' => $_POST['item_prices'][$key],
                'qty'   => $_POST['item_qtys'][$key],
                'sub'   => floatval($_POST['item_prices'][$key]) * intval($_POST['item_qtys'][$key])
            ];
            $item_names_list[] = $name;
        }
    }
    $items_json = json_encode($temp_items);

    $conn->begin_transaction();
    try {
        $sql1 = "INSERT INTO invoice (invoice_no, job_no, invoice_date, service_charge, parts_total, grand_total, items_json, payment_status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sssdddss", $inv_no, $job_no, $inv_date, $s_charge, $p_total, $g_total, $items_json, $pay_status);
        $stmt1->execute();

        $conn->query("UPDATE job_device SET device_status = 'billed' WHERE job_no = '$job_no'");

        if (!empty($temp_items)) {
            foreach ($temp_items as $item) {
                $code = $item['code'];
                $qty = $item['qty'];
                $conn->query("UPDATE stock SET quantity = quantity - $qty WHERE item_code = '$code'");
            }
        }

        if ($pay_status == 'Paid') {
            $balance_res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
            $last_balance = ($balance_res->num_rows > 0) ? floatval($balance_res->fetch_assoc()['balance']) : 0;
            $new_balance = $last_balance + $g_total;
            
            $sql_cash = "INSERT INTO cashbook (date, invoice_no, income, balance) VALUES (?, ?, ?, ?)";
            $stmt_cash = $conn->prepare($sql_cash);
            $stmt_cash->bind_param("ssdd", $inv_date, $inv_no, $g_total, $new_balance);
            $stmt_cash->execute();
        }

        $conn->commit();
        $invoice_saved = true;
        $saved_items = $temp_items;

        $phone_query = "SELECT phone_number FROM job WHERE job_no = '$job_no'";
        $phone_res = $conn->query($phone_query);
        if ($phone_row = $phone_res->fetch_assoc()) {
            $phone = "94" . ltrim(ltrim($phone_row['phone_number'], '94'), '0');
            $items_txt = !empty($item_names_list) ? implode(', ', $item_names_list) : "Service Charge Only";
            $sms_msg = "Multi9 Invoice: #" . $inv_no . "\nItems: " . $items_txt . "\nTotal: Rs." . number_format($g_total, 2) . "\nStatus: " . $pay_status . "\nThank you!";

            $api_key = "378|Ny4YLhCMaTosGeaTZhiaWt3v7kMSd4woZZdTefLq";
            $sender_id = "SMSAPI"; 
            $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
            $data = array('recipient' => $phone, 'sender_id' => $sender_id, 'message' => $sms_msg);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $api_key, "Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_exec($ch);
            curl_close($ch);
        }
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

$stock_res = $conn->query("SELECT item_code, item_name, unit_price FROM stock WHERE quantity > 0");
$stock_items = $stock_res->fetch_all(MYSQLI_ASSOC);
$job_no_display = $_GET['job_no'] ?? ($_POST['job_no'] ?? 'N/A');
$inv_res = $conn->query("SELECT MAX(invoice_no) AS last_id FROM invoice");
$next_invoice_no = (($inv_row = $inv_res->fetch_assoc()) && $inv_row['last_id']) ? $inv_row['last_id'] + 1 : 1;
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>Invoice - Multi9 Repair</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 100px 0; }
        .invoice-box { max-width: 900px; margin: 20px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #043f2e; padding-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th { background: #065f46; color: white; padding: 15px; }
        td { border-bottom: 1px solid #f1f1f1; padding: 15px; }
        .total-section { text-align: right; margin-top: 30px; padding: 20px; background: #fdfdfd; border-radius: 8px; border: 1px solid #eee; }
        .btn { padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; margin-top: 10px; }
        .btn-save { background: #065f46; color: white; }
        .btn-print { background: #3498db; color: white; }
        .btn-pay { background: #e67e22; color: white; }
        .add-item-box { background: #e8f5e9; padding: 20px; border-radius: 8px; display: flex; gap: 10px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <h1>MULTI9 COMPUTER REPAIR</h1>
        <p>Invoice No: <strong>#<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?></strong></p>
        <p>Job No: <strong><?= htmlspecialchars($job_no_display) ?></strong> | Date: <?= date("Y-m-d") ?></p>
    </div>

    <form method="POST">
        <input type="hidden" name="invoice_no" value="<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?>">
        <input type="hidden" name="job_no" value="<?= $job_no_display ?>">
        <input type="hidden" name="parts_total" id="p_total_val" value="<?= $invoice_saved ? $_POST['parts_total'] : '0' ?>">
        <input type="hidden" name="delay_fee" id="delay_fee_val" value="<?= $delay_fee ?>">
        <input type="hidden" name="grand_total" id="g_total_val" value="<?= $invoice_saved ? $_POST['grand_total'] : '0' ?>">

        <?php if (!$invoice_saved): ?>
        <div class="add-item-box no-print">
            <select id="itemSelect" style="flex:3; padding:10px;">
                <option value="">-- Select Parts --</option>
                <?php foreach($stock_items as $i): ?>
                    <option value="<?= $i['item_code'] ?>" data-name="<?= $i['item_name'] ?>" data-price="<?= $i['unit_price'] ?>">
                        <?= $i['item_name'] ?> (Rs. <?= number_format($i['unit_price'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="qty" value="1" min="1" style="width:60px; padding:10px;">
            <button type="button" onclick="addItem()" style="flex:1; background:#2ecc71; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">+ ADD</button>
        </div>
        <?php endif; ?>

        <table id="billTable">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invoice_saved): ?>
                    <?php foreach ($saved_items as $item): ?>
                        <tr>
                            <td><?= $item['name'] ?></td>
                            <td><?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td style="text-align: right;"><?= number_format($item['sub'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-section">
            <p>Parts Total: <strong>Rs. <span id="p_disp"><?= $invoice_saved ? number_format($_POST['parts_total'], 2) : '0.00' ?></span></strong></p>
            <p>Service Charge: 
                <?php if(!$invoice_saved): ?>
                    <input type="number" name="service_charge" id="s_charge" value="0" step="0.01" oninput="calcTotal()" style="text-align: right; padding: 5px; width: 100px;">
                <?php else: ?>
                    <strong>Rs. <?= number_format($_POST['service_charge'], 2) ?></strong>
                <?php endif; ?>
            </p>
            <?php if($delay_fee > 0): ?>
                <p style="color: red;">Late Collection Fee (Rent): Rs. <?= number_format($delay_fee, 2) ?></p>
            <?php endif; ?>

            <p>Payment Status: 
                <?php if(!$invoice_saved): ?>
                    <select name="payment_status" style="padding: 5px;">
                        <option value="Paid">Paid (Cash Received)</option>
                        <option value="Pending">Pending (Not Paid)</option>
                    </select>
                <?php else: ?>
                    <strong id="currentStatus" style="color: <?= $_POST['payment_status'] == 'Paid' ? 'green' : 'orange' ?>;"><?= $_POST['payment_status'] ?></strong>
                <?php endif; ?>
            </p>

            <div class="grand-total-h2" style="font-size:24px; color:#065f46; border-top:2px solid #065f46; margin-top:10px;">
                Grand Total: Rs. <span id="g_disp"><?= $invoice_saved ? number_format($_POST['grand_total'], 2) : '0.00' ?></span>
            </div>
        </div>

        <div class="no-print">
            <?php if (!$invoice_saved): ?>
                <button type="submit" name="save_invoice" class="btn btn-save">💾 SAVE INVOICE</button>
            <?php else: ?>
                <?php if ($_POST['payment_status'] == 'Pending'): ?>
                    <button type="button" onclick="markAsPaidAndPrint('<?= $_POST['invoice_no'] ?>')" class="btn btn-pay">💰 PAY & PRINT</button>
                <?php else: ?>
                    <button type="button" onclick="window.print()" class="btn btn-print">🖨️ PRINT INVOICE</button>
                <?php endif; ?>
                <a href="invoice_list.php" style="display:block; text-align:center; margin-top:10px; color:#666;">← Back to List</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
window.onload = function() { calcTotal(); };

// --- නව එකතු කිරීම: AJAX මගින් Status එක Update කිරීමේ Function එක ---
function markAsPaidAndPrint(invNo) {
    if (confirm("ඔබට මෙම බිල Paid ලෙස සටහන් කර Print කිරීමට අවශ්‍යද?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_payment_status.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                if (this.responseText.trim() === "success") {
                    document.getElementById('currentStatus').innerText = "Paid";
                    document.getElementById('currentStatus').style.color = "green";
                    window.print();
                } else {
                    alert("Error: " + this.responseText);
                }
            }
        };
        xhr.send("invoice_no=" + invNo + "&status=Paid");
    }
}
// ------------------------------------------------------------------

function addItem() {
    const sel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    if(!opt.value) return alert('Select an item!');
    const qty = document.getElementById('qty').value;
    const price = parseFloat(opt.dataset.price);
    const sub = price * qty;
    const row = `<tr><td>${opt.dataset.name}<input type="hidden" name="item_names[]" value="${opt.dataset.name}"><input type="hidden" name="item_codes[]" value="${opt.value}"><input type="hidden" name="item_prices[]" value="${price}"><input type="hidden" name="item_qtys[]" value="${qty}"></td><td>${price.toFixed(2)}</td><td>${qty}</td><td style="text-align:right;">${sub.toFixed(2)}</td></tr>`;
    document.querySelector('#billTable tbody').innerHTML += row;
    calcTotal();
    sel.selectedIndex = 0;
}
function calcTotal() {
    let pTotal = 0;
    document.querySelectorAll('#billTable tbody tr').forEach(row => {
        const rowTotal = parseFloat(row.cells[3].innerText.replace(/,/g, ''));
        if(!isNaN(rowTotal)) pTotal += rowTotal;
    });
    document.getElementById('p_disp').innerText = pTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('p_total_val').value = pTotal;
    const sCharge = parseFloat(document.getElementById('s_charge')?.value || 0);
    const dFee = parseFloat(document.getElementById('delay_fee_val').value || 0);
    const gTotal = pTotal + sCharge + dFee;
    document.getElementById('g_disp').innerText = gTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('g_total_val').value = gTotal;
}
</script>
</body>
</html>