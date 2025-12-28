<?php
include 'db_config.php';
include 'navbar.php';

$invoice_saved = false;
$saved_items = [];

if (isset($_POST['save_invoice'])) {
    $inv_no = $_POST['invoice_no'];
    $job_no = $_POST['job_no'];
    $inv_date = date("Y-m-d");
    $s_charge = floatval($_POST['service_charge']);
    $p_total = floatval($_POST['parts_total']);
    $g_total = floatval($_POST['grand_total']);

    // 1. භාණ්ඩ ලැයිස්තුව JSON එකක් විදියට සකස් කරගන්න
    $temp_items = [];
    if (isset($_POST['item_codes'])) {
        for ($i = 0; $i < count($_POST['item_codes']); $i++) {
            $temp_items[] = [
                'code'  => $_POST['item_codes'][$i],
                'name'  => $_POST['item_names'][$i],
                'price' => $_POST['item_prices'][$i],
                'qty'   => $_POST['item_qtys'][$i],
                'sub'   => floatval($_POST['item_prices'][$i]) * intval($_POST['item_qtys'][$i])
            ];
        }
    }
    $items_json = $conn->real_escape_string(json_encode($temp_items));

    $conn->begin_transaction();

    try {
        // 2. Invoice දත්ත DB එකට ඇතුළත් කිරීම
        $sql1 = "INSERT INTO invoice (invoice_no, job_no, invoice_date, service_charge, parts_total, grand_total, items_json) 
                 VALUES ('$inv_no', '$job_no', '$inv_date', '$s_charge', '$p_total', '$g_total', '$items_json')";
        $conn->query($sql1);

        // 3. Stock Update කිරීම
        if (isset($_POST['item_codes'])) {
            for ($i = 0; $i < count($_POST['item_codes']); $i++) {
                $code = $conn->real_escape_string($_POST['item_codes'][$i]);
                $qty = intval($_POST['item_qtys'][$i]);
                $conn->query("UPDATE stock SET quantity = quantity - $qty WHERE item_code = '$code'");
            }
        }

        $conn->commit();
        $invoice_saved = true;
        $saved_items = $temp_items; 
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

// Dropdown & Next Invoice Logic
$stock_res = $conn->query("SELECT item_code, item_name, unit_price, quantity FROM stock WHERE quantity > 0");
$stock_items = [];
while($row = $stock_res->fetch_assoc()) { $stock_items[] = $row; }

$job_no_display = isset($_GET['job_no']) ? $_GET['job_no'] : (isset($_POST['job_no']) ? $_POST['job_no'] : 'N/A');
$inv_res = $conn->query("SELECT MAX(invoice_no) AS last_id FROM invoice");
$inv_row = $inv_res->fetch_assoc();
$next_invoice_no = ($inv_row['last_id']) ? $inv_row['last_id'] + 1 : 1;
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Generator</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .invoice-box { max-width: 850px; background: #fff; margin: auto; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #2ecc71; padding: 30px; color: white; text-align: center; }
        .invoice-meta { display: flex; justify-content: space-around; margin-top: 15px; background: rgba(0,0,0,0.1); padding: 10px; border-radius: 8px; }
        .content-area { padding: 30px; }
        .add-item-section { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #2ecc71; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-area { background: #f9f9f9; padding: 20px; border-radius: 8px; text-align: right; }
        .total-row { margin-bottom: 10px; font-size: 16px; }
        .grand-total { font-size: 24px; color: #2ecc71; font-weight: bold; border-top: 2px solid #ddd; padding-top: 10px; }
        .btn { padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-add { background: #2ecc71; color: white; width: auto; }
        .btn-save { background: #27ae60; color: white; margin-top: 15px; }
        .btn-print { background: #2ecc71; color: white; margin-top: 15px; }
        input[type="number"], select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
            .invoice-box { box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <h1>MULTI9 COMPUTER REPAIR</h1>
        <div class="invoice-meta">
            <div><strong>Inv No:</strong> #<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?></div>
            <div><strong>Job No:</strong> <?= htmlspecialchars($job_no_display) ?></div>
            <div><strong>Date:</strong> <?= date("Y-m-d") ?></div>
        </div>
    </div>

    <div class="content-area">
        <?php if(!$invoice_saved): ?>
        <div class="add-item-section no-print">
            <div style="display: flex; gap: 10px;">
                <select id="itemSelect" style="flex: 3;">
                    <option value="">-- Select Parts --</option>
                    <?php foreach($stock_items as $i): ?>
                        <option value="<?= $i['item_code'] ?>" data-name="<?= $i['item_name'] ?>" data-price="<?= $i['unit_price'] ?>">
                            <?= $i['item_name'] ?> (Rs. <?= number_format($i['unit_price'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" id="qty" value="1" min="1" style="flex: 1;">
                <button type="button" class="btn btn-add" onclick="addItem()">ADD</button>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="invoice_no" value="<?= $next_invoice_no ?>">
            <input type="hidden" name="job_no" value="<?= $job_no_display ?>">
            <input type="hidden" name="parts_total" id="p_total_val" value="<?= $invoice_saved ? $_POST['parts_total'] : '0' ?>">
            <input type="hidden" name="grand_total" id="g_total_val" value="<?= $invoice_saved ? $_POST['grand_total'] : '0' ?>">

            <table id="billTable">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th style="text-align: right;">Total</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($invoice_saved): ?>
                        <?php foreach($saved_items as $item): ?>
                            <tr>
                                <td><?= $item['name'] ?></td>
                                <td><?= number_format($item['price'], 2) ?></td>
                                <td><?= $item['qty'] ?></td>
                                <td style="text-align: right;"><?= number_format($item['sub'], 2) ?></td>
                                <td class="no-print"></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="total-area">
                <div class="total-row">
                    Parts Total: <strong>Rs. <span id="p_disp"><?= $invoice_saved ? number_format($_POST['parts_total'], 2) : '0.00' ?></span></strong>
                </div>
                <div class="total-row">
                    Service Charge: 
                    <?php if(!$invoice_saved): ?>
                        <input type="number" name="service_charge" id="s_charge" value="0" step="0.01" oninput="calcTotal()" style="text-align: right; width: 120px;">
                    <?php else: ?>
                        <strong>Rs. <?= number_format($_POST['service_charge'], 2) ?></strong>
                        <input type="hidden" name="service_charge" value="<?= $_POST['service_charge'] ?>">
                    <?php endif; ?>
                </div>
                <div class="grand-total">
                    Grand Total: Rs. <span id="g_disp"><?= $invoice_saved ? number_format($_POST['grand_total'], 2) : '0.00' ?></span>
                </div>
            </div>

            <?php if(!$invoice_saved): ?>
                <button type="submit" name="save_invoice" class="btn btn-save no-print">💾 SAVE INVOICE</button>
            <?php else: ?>
                <button type="button" class="btn btn-print" onclick="window.print()">🖨️ PRINT INVOICE</button>
                <div class="no-print" style="text-align: center; margin-top: 15px;">
                    <a href="generate_bill.php" style="color: #27ae60; text-decoration: none; font-weight: bold;">+ New Invoice</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function addItem() {
    const sel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    if(!opt.value) return alert('Select an item!');

    const name = opt.getAttribute('data-name');
    const price = parseFloat(opt.getAttribute('data-price'));
    const qty = parseInt(document.getElementById('qty').value);

    const tbody = document.querySelector('#billTable tbody');
    const row = tbody.insertRow();
    row.innerHTML = `
        <td>${name} 
            <input type="hidden" name="item_names[]" value="${name}">
            <input type="hidden" name="item_codes[]" value="${opt.value}">
            <input type="hidden" name="item_prices[]" value="${price}">
        </td>
        <td>${price.toFixed(2)}</td>
        <td>${qty} <input type="hidden" name="item_qtys[]" value="${qty}"></td>
        <td style="text-align: right;">${(price * qty).toFixed(2)}</td>
        <td class="no-print"><button type="button" onclick="this.parentElement.parentElement.remove(); calcTotal();" style="color:red; cursor:pointer; border:none; background:none;">✖</button></td>
    `;
    calcTotal();
    sel.selectedIndex = 0;
}

function calcTotal() {
    let pTotal = 0;
    document.querySelectorAll('#billTable tbody tr').forEach(row => {
        const val = parseFloat(row.cells[3].innerText);
        if(!isNaN(val)) pTotal += val;
    });
    
    document.getElementById('p_disp').innerText = pTotal.toFixed(2);
    document.getElementById('p_total_val').value = pTotal;
    
    const sCharge = parseFloat(document.getElementById('s_charge')?.value) || parseFloat('<?= $invoice_saved ? $_POST['service_charge'] : 0 ?>') || 0;
    const gTotal = pTotal + sCharge;
    
    document.getElementById('g_disp').innerText = gTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('g_total_val').value = gTotal;
}
</script>
</body>
</html>