<?php
include 'db_config.php';
include 'navbar.php';

// ශ්‍රී ලංකාවේ වේලාව නිවැරදිව ලබා ගැනීමට (වේලාව සම්බන්ධ ගැටළු මගහරවා ගැනීමට)
date_default_timezone_set("Asia/Colombo");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$invoice_saved = false;
$saved_items = [];

// URL එකෙන් එන Rent Fee එක ලබා ගැනීම
$delay_fee = isset($_GET['fee']) ? floatval($_GET['fee']) : (isset($_POST['delay_fee']) ? floatval($_POST['delay_fee']) : 0);

if (isset($_POST['save_invoice'])) {
    $inv_no = $_POST['invoice_no'];
    $job_no = $_POST['job_no'];
    
    // දිනය ලබා ගන්නා ආකාරය වඩාත් සුරක්ෂිත කර ඇත
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
        // SQL 1: Invoice table එකට දත්ත ඇතුළත් කිරීම (ssdddds -> sssddds ලෙස වෙනස් කර ඇත string දිනය සඳහා)
        $sql1 = "INSERT INTO invoice (invoice_no, job_no, invoice_date, service_charge, parts_total, grand_total, items_json) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sssddds", $inv_no, $job_no, $inv_date, $s_charge, $p_total, $g_total, $items_json);
        $stmt1->execute();

        // SQL 2: Job status එක වෙනස් කිරීම
        $conn->query("UPDATE job_device SET device_status = 'billed' WHERE job_no = '$job_no'");

        // SQL 3: Stock අඩු කිරීම
        if (!empty($temp_items)) {
            foreach ($temp_items as $item) {
                $code = $item['code'];
                $qty = $item['qty'];
                $conn->query("UPDATE stock SET quantity = quantity - $qty WHERE item_code = '$code'");
            }
        }

        // SQL 4: Cashbook එකට දත්ත ඇතුළත් කිරීම
        $balance_res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $last_balance = ($balance_res->num_rows > 0) ? floatval($balance_res->fetch_assoc()['balance']) : 0;
        $new_balance = $last_balance + $g_total;
        
        // Prepared statement එකක් භාවිතා කර cashbook එකට දින ඇතුළත් කිරීම වඩාත් සුරක්ෂිතයි
        $sql_cash = "INSERT INTO cashbook (date, invoice_no, income, balance) VALUES (?, ?, ?, ?)";
        $stmt_cash = $conn->prepare($sql_cash);
        $stmt_cash->bind_param("ssdd", $inv_date, $inv_no, $g_total, $new_balance);
        $stmt_cash->execute();

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
        /* මූලික සැකසුම් */
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #f4f7f6; 
            margin: 0;
            padding: 0;
            padding-top: 100px; /* Navbar එක සඳහා ඉඩ තැබීම */
        }

        .invoice-box { 
            max-width: 900px; 
            margin: 20px auto; 
            background: #fff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            border: 1px solid #e1e8e5;
        }

        .header { 
            text-align: center; 
            border-bottom: 3px solid #043f2e; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .header h1 { margin: 0; color: #043f2e; letter-spacing: 2px; }
        .header p { margin: 5px 0; color: #666; font-size: 14px; }

        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th { 
            background: #065f46; 
            color: white; 
            padding: 15px; 
            text-align: left; 
            font-size: 14px;
            text-transform: uppercase;
        }
        td { 
            border-bottom: 1px solid #f1f1f1; 
            padding: 15px; 
            text-align: left; 
            color: #333;
        }
        tr:nth-child(even) { background-color: #fcfdfc; }

        .add-item-box {
            background: #e8f5e9; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
            display: flex; 
            gap: 15px;
            align-items: center;
        }
        .add-item-box select, .add-item-box input {
            padding: 12px;
            border: 1px solid #c8d6cf;
            border-radius: 6px;
            outline: none;
        }

        .total-section { 
            text-align: right; 
            margin-top: 30px; 
            padding: 20px; 
            background: #fdfdfd; 
            border: 1px solid #eee;
            border-radius: 8px; 
        }
        .total-section p { margin: 8px 0; font-size: 15px; color: #444; }
        .grand-total-h2 { 
            color: #065f46; 
            font-size: 28px; 
            margin-top: 15px;
            border-top: 2px solid #065f46;
            display: inline-block;
            padding-top: 10px;
        }

        .btn { 
            padding: 15px 25px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            width: 100%; 
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-save { background: #065f46; color: white; margin-bottom: 10px; }
        .btn-save:hover { background: #043f2e; }
        .btn-print { background: #3498db; color: white; }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-weight: 500;
        }

        @media print { 
            .no-print, .add-item-box { display: none !important; } 
            body { padding-top: 0; background: white; }
            .invoice-box { box-shadow: none; border: none; padding: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <h1>MULTI9 COMPUTER REPAIR</h1>
        <p>Invoice No: <strong>#<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?></strong></p>
        <p>Job No: <strong><?= htmlspecialchars($job_no_display) ?></strong> | Date: <?= date("Y-m-d") ?></p>
    </div>

    <form method="POST" id="invoiceForm">
        <input type="hidden" name="invoice_no" value="<?= $invoice_saved ? $_POST['invoice_no'] : $next_invoice_no ?>">
        <input type="hidden" name="job_no" value="<?= $job_no_display ?>">
        <input type="hidden" name="parts_total" id="p_total_val" value="<?= $invoice_saved ? $_POST['parts_total'] : '0' ?>">
        <input type="hidden" name="delay_fee" id="delay_fee_val" value="<?= $delay_fee ?>">
        <input type="hidden" name="grand_total" id="g_total_val" value="<?= $invoice_saved ? $_POST['grand_total'] : '0' ?>">

        <?php if (!$invoice_saved): ?>
        <div class="add-item-box no-print">
            <select id="itemSelect" style="flex:3;">
                <option value="">-- Select Parts --</option>
                <?php foreach($stock_items as $i): ?>
                    <option value="<?= $i['item_code'] ?>" data-name="<?= $i['item_name'] ?>" data-price="<?= $i['unit_price'] ?>">
                        <?= $i['item_name'] ?> (Rs. <?= number_format($i['unit_price'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="qty" value="1" min="1" style="flex:0.5;">
            <button type="button" onclick="addItem()" style="flex:1; background:#2ecc71; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; height:45px;">+ ADD ITEM</button>
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
                    <input type="number" name="service_charge" id="s_charge" value="0" step="0.01" oninput="calcTotal()" style="text-align: right; padding: 8px; width: 120px; border: 1px solid #ddd; border-radius: 4px;">
                <?php else: ?>
                    <strong id="s_charge_display">Rs. <?= number_format($_POST['service_charge'], 2) ?></strong>
                    <input type="hidden" id="s_charge" value="<?= $_POST['service_charge'] ?>">
                <?php endif; ?>
            </p>

            <?php if($delay_fee > 0): ?>
            <p style="color: #d32f2f; font-weight: bold;">Late Collection Fee (Rent): Rs. <?= number_format($delay_fee, 2) ?></p>
            <?php endif; ?>

            <div class="grand-total-h2">Grand Total: Rs. <span id="g_disp"><?= $invoice_saved ? number_format($_POST['grand_total'], 2) : '0.00' ?></span></div>
        </div>

        <div style="margin-top:30px;">
            <?php if (!$invoice_saved): ?>
                <button type="submit" name="save_invoice" class="btn btn-save no-print">💾 SAVE & COMPLETE INVOICE</button>
            <?php else: ?>
                <button type="button" onclick="window.print()" class="btn btn-print no-print">🖨️ PRINT INVOICE</button>
                <a href="job_list.php" class="back-link no-print">← Back to Order Page</a>
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
    
    document.getElementById('p_disp').innerText = pTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
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