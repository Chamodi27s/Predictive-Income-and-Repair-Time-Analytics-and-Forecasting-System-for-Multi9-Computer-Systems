<?php
include 'db_config.php';
include 'navbar.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$invoice_saved = false;
$saved_items = [];

// URL එකෙන් එන Rent Fee එක ලබා ගැනීම
$delay_fee = isset($_GET['fee']) ? floatval($_GET['fee']) : (isset($_POST['delay_fee']) ? floatval($_POST['delay_fee']) : 0);

if (isset($_POST['save_invoice'])) {
    $inv_no = $_POST['invoice_no'];
    $job_no = $_POST['job_no'];
    $inv_date = date("Y-m-d");
    $s_charge = floatval($_POST['service_charge']);
    $p_total = floatval($_POST['parts_total']);
    $g_total = floatval($_POST['grand_total']);

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
        $sql1 = "INSERT INTO invoice (invoice_no, job_no, invoice_date, service_charge, parts_total, grand_total, items_json) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ssdddds", $inv_no, $job_no, $inv_date, $s_charge, $p_total, $g_total, $items_json);
        $stmt1->execute();

        $conn->query("UPDATE job_device SET device_status = 'billed' WHERE job_no = '$job_no'");

        if (!empty($temp_items)) {
            foreach ($temp_items as $item) {
                $code = $item['code'];
                $qty = $item['qty'];
                $conn->query("UPDATE stock SET quantity = quantity - $qty WHERE item_code = '$code'");
            }
        }

        $balance_res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $last_balance = ($balance_res->num_rows > 0) ? floatval($balance_res->fetch_assoc()['balance']) : 0;
        $new_balance = $last_balance + $g_total;
        $conn->query("INSERT INTO cashbook (date, invoice_no, income, balance) 
                     VALUES ('$inv_date', '$inv_no', '$g_total', '$new_balance')");

        $conn->commit();
        $invoice_saved = true;
        $saved_items = $temp_items;
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
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; padding: 20px; padding-top:130px; }
        .invoice-box { max-width: 800px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #2ecc71; padding-bottom: 15px; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #2ecc71; color: white; }
        .total-section { text-align: right; margin-top: 25px; padding: 15px; background: #f9f9f9; border-radius: 5px; }
        .btn { padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-save { background: #27ae60; color: white; }
        .btn-print { background: #3498db; color: white; }
        .no-print { display: block; }
        @media print { .no-print { display: none !important; } .invoice-box { box-shadow: none; border: none; } }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <h1>MULTI9 COMPUTER REPAIR</h1>
        <p>Invoice No: <strong>#<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?></strong> | 
            Job No: <strong><?= htmlspecialchars($job_no_display) ?></strong></p>
        <p>Date: <?= date("Y-m-d") ?></p>
    </div>

    <form method="POST" id="invoiceForm">
        <input type="hidden" name="invoice_no" value="<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?>">
        <input type="hidden" name="job_no" value="<?= $job_no_display ?>">
        <input type="hidden" name="parts_total" id="p_total_val" value="<?= $invoice_saved ? $_POST['parts_total'] : '0' ?>">
        <input type="hidden" name="delay_fee" id="delay_fee_val" value="<?= $delay_fee ?>">
        <input type="hidden" name="grand_total" id="g_total_val" value="<?= $invoice_saved ? $_POST['grand_total'] : '0' ?>">

        <?php if (!$invoice_saved): ?>
        <div class="no-print" style="background:#e8f5e9; padding:15px; border-radius:5px; margin-bottom:20px; display:flex; gap:10px;">
            <select id="itemSelect" style="flex:3; padding:10px;">
                <option value="">-- Select Parts --</option>
                <?php foreach($stock_items as $i): ?>
                    <option value="<?= $i['item_code'] ?>" data-name="<?= $i['item_name'] ?>" data-price="<?= $i['unit_price'] ?>">
                        <?= $i['item_name'] ?> (Rs. <?= number_format($i['unit_price'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="qty" value="1" min="1" style="flex:0.5; padding:10px;">
            <button type="button" onclick="addItem()" style="flex:1; background:#2ecc71; color:white; border:none; border-radius:5px; cursor:pointer;">ADD ITEM</button>
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
            <p>Parts Total: Rs. <span id="p_disp"><?= $invoice_saved ? number_format($_POST['parts_total'], 2) : '0.00' ?></span></p>
            
            <p>Service Charge: 
                <?php if(!$invoice_saved): ?>
                    <input type="number" name="service_charge" id="s_charge" value="0" step="0.01" oninput="calcTotal()" style="text-align: right; padding: 5px;">
                <?php else: ?>
                    <strong id="s_charge_display">Rs. <?= number_format($_POST['service_charge'], 2) ?></strong>
                    <input type="hidden" id="s_charge" value="<?= $_POST['service_charge'] ?>">
                <?php endif; ?>
            </p>

            <?php if($delay_fee > 0): ?>
            <p style="color: #d32f2f;">Late Collection Fee (Rent): <strong>Rs. <?= number_format($delay_fee, 2) ?></strong></p>
            <?php endif; ?>

            <hr>
            <h2 style="color: #2ecc71;">Grand Total: Rs. <span id="g_disp"><?= $invoice_saved ? number_format($_POST['grand_total'], 2) : '0.00' ?></span></h2>
        </div>

        <div style="margin-top:25px;">
            <?php if (!$invoice_saved): ?>
                <button type="submit" name="save_invoice" class="btn btn-save no-print">💾 SAVE & COMPLETE INVOICE</button>
            <?php else: ?>
                <button type="button" onclick="window.print()" class="btn btn-print no-print" style="margin-bottom:10px;">🖨️ PRINT INVOICE</button>
                <div class="no-print" style="text-align:center;">
                    <a href="job_list.php" style="display:inline-block; padding:10px 20px; background:#eee; color:#333; text-decoration:none; border-radius:5px; font-weight:bold; width:95%;">← Back to Order Page</a>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
window.onload = function() {
    calcTotal(); 
};

function addItem() {
    const sel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    if(!opt.value) return alert('කරුණාකර භාණ්ඩයක් තෝරන්න!');
    
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
        <td style="text-align: right;">${sub.toFixed(2)}</td>
    </tr>`;
    
    document.querySelector('#billTable tbody').innerHTML += row;
    calcTotal();
    sel.selectedIndex = 0;
}

function calcTotal() {
    let pTotal = 0;
    document.querySelectorAll('#billTable tbody tr').forEach(row => {
        const rowTotalText = row.cells[3].innerText.replace(/,/g, '');
        const rowTotal = parseFloat(rowTotalText);
        if(!isNaN(rowTotal)) pTotal += rowTotal;
    });
    
    document.getElementById('p_disp').innerText = pTotal.toFixed(2);
    document.getElementById('p_total_val').value = pTotal;
    
    const sChargeInput = document.getElementById('s_charge');
    const sCharge = sChargeInput ? parseFloat(sChargeInput.value || 0) : 0;
    const dFee = parseFloat(document.getElementById('delay_fee_val').value || 0);
    
    const gTotal = pTotal + sCharge + dFee;
    
    document.getElementById('g_disp').innerText = gTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('g_total_val').value = gTotal;
}
</script>
</body>
</html>