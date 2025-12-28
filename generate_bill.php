<?php
include 'db_config.php';
include 'navbar.php';

$invoice_saved = false;

// 1. Invoice Save & Stock Update Logic
if (isset($_POST['save_invoice'])) {
    $inv_no = $_POST['invoice_no'];
    $job_no = $_POST['job_no'];
    $inv_date = date("Y-m-d");
    $s_charge = floatval($_POST['service_charge']);
    $p_total = floatval($_POST['parts_total']);
    $g_total = floatval($_POST['grand_total']);

    // Database Transaction ekak patan gammu (Security ekata)
    $conn->begin_transaction();

    try {
        // Invoice Table ekata record eka damma
        $sql1 = "INSERT INTO invoice (invoice_no, job_no, invoice_date, service_charge, parts_total, grand_total) 
                 VALUES ('$inv_no', '$job_no', '$inv_date', '$s_charge', '$p_total', '$g_total')";
        $conn->query($sql1);

        // Bill ekata select karapu badu tika (Array) kiyawamu
        if (isset($_POST['item_codes'])) {
            for ($i = 0; $i < count($_POST['item_codes']); $i++) {
                $code = $conn->real_escape_string($_POST['item_codes'][$i]);
                $qty = intval($_POST['item_qtys'][$i]);

                // *** STOCK TABLE EKE QUANTITY EKA ADU KIRIMA ***
                $sql_update_stock = "UPDATE stock SET quantity = quantity - $qty WHERE item_code = '$code'";
                $conn->query($sql_update_stock);
            }
        }

        $conn->commit();
        $invoice_saved = true;
        echo "<script>alert('Invoice Saved & Stock Updated Successfully!');</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

// 2. Dropdown ekata stock details load kirima
$stock_res = $conn->query("SELECT item_code, item_name, unit_price, quantity FROM stock WHERE quantity > 0");
$stock_items = [];
while($row = $stock_res->fetch_assoc()) {
    $stock_items[] = $row;
}

// Job No eka URL eken ganna
$job_no = isset($_GET['job_no']) ? $_GET['job_no'] : '';

// Anthima Invoice No eka aran 1k ekathu karanna
$inv_res = $conn->query("SELECT MAX(invoice_no) AS last_id FROM invoice");
$inv_row = $inv_res->fetch_assoc();
$next_invoice_no = ($inv_row['last_id']) ? $inv_row['last_id'] + 1 : 1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Bill</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f6f4; margin: 0; padding: 20px; }
        .bill-container { max-width: 900px; background: #fff; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #22c55e; padding-bottom: 15px; margin-bottom: 20px; }
        .input-box { background: #f9fafb; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f3f4f6; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-add { background: #22c55e; color: white; }
        .btn-save { background: #f97316; color: white; width: 100%; font-size: 16px; margin-top: 20px; }
        .total-area { text-align: right; margin-top: 20px; font-size: 18px; }
        @media print { .no-print { display: none; } .bill-container { box-shadow: none; } }
    </style>
</head>
<body>

<div class="bill-container">
    <div class="header">
        <h1>INVOICE</h1>
        <p>Job No: #<strong><?= htmlspecialchars($job_no) ?></strong></p>
    </div>

    <div class="input-box no-print">
        <div style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 2;">
                <label style="font-size: 12px; font-weight: bold;">Select Item</label>
                <select id="itemSelect" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;" onchange="setPrice()">
                    <option value="">-- Choose Item from Stock --</option>
                    <?php foreach($stock_items as $item): ?>
                        <option value="<?= $item['item_code'] ?>" 
                                data-name="<?= $item['item_name'] ?>" 
                                data-price="<?= $item['unit_price'] ?>" 
                                data-avail="<?= $item['quantity'] ?>">
                            <?= $item['item_name'] ?> (Avail: <?= $item['quantity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 12px; font-weight: bold;">Price</label>
                <input type="text" id="uPrice" style="width: 100%; padding: 10px; border: 1px solid #ccc; background: #eee;" readonly>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 12px; font-weight: bold;">Qty</label>
                <input type="number" id="uQty" value="1" min="1" style="width: 100%; padding: 10px; border: 1px solid #ccc;">
            </div>
            <button type="button" class="btn btn-add" onclick="addItem()">Add</button>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="invoice_no" value="<?= $next_invoice_no ?>">
        <input type="hidden" name="job_no" value="<?= $job_no ?>">
        <input type="hidden" name="parts_total" id="p_total_hidden" value="0">
        <input type="hidden" name="grand_total" id="g_total_hidden" value="0">

        <table id="billTable">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Sub Total</th>
                    <th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                </tbody>
        </table>

        <div class="total-area">
            <div class="no-print" style="margin-bottom: 10px;">
                <label style="font-size: 14px;">Service Charge: </label>
                <input type="number" name="service_charge" id="s_charge" value="0" step="0.01" oninput="calcGrandTotal()" style="padding: 8px; width: 150px; text-align: right; border: 1px solid #ccc;">
            </div>
            <p>Parts Total: Rs. <span id="p_total_disp">0.00</span></p>
            <h2 style="color: #22c55e;">Grand Total: Rs. <span id="g_total_disp">0.00</span></h2>
        </div>

        <?php if(!$invoice_saved): ?>
            <button type="submit" name="save_invoice" class="btn btn-save no-print">Save & Generate Bill</button>
        <?php else: ?>
            <button type="button" class="btn btn-save" onclick="window.print()" style="background: #3b82f6;">Print Invoice</button>
        <?php endif; ?>
    </form>
</div>

<script>
    let itemsArr = [];

    function setPrice() {
        const sel = document.getElementById('itemSelect');
        const price = sel.options[sel.selectedIndex].getAttribute('data-price') || 0;
        document.getElementById('uPrice').value = price;
    }

    function addItem() {
        const sel = document.getElementById('itemSelect');
        const selected = sel.options[sel.selectedIndex];
        
        if (!selected.value) return alert("Select an item!");
        
        const code = selected.value;
        const name = selected.getAttribute('data-name');
        const price = parseFloat(selected.getAttribute('data-price'));
        const avail = parseInt(selected.getAttribute('data-avail'));
        const qty = parseInt(document.getElementById('uQty').value);

        if (qty > avail) return alert("Only " + avail + " items available in stock!");

        itemsArr.push({ code, name, price, qty, total: price * qty });
        renderTable();
        
        // Reset inputs
        sel.selectedIndex = 0;
        document.getElementById('uPrice').value = "";
        document.getElementById('uQty').value = 1;
    }

    function renderTable() {
        const tbody = document.querySelector('#billTable tbody');
        tbody.innerHTML = '';
        let pTotal = 0;

        itemsArr.forEach((item, index) => {
            pTotal += item.total;
            tbody.innerHTML += `
                <tr>
                    <td>${item.name} (${item.code}) <input type="hidden" name="item_codes[]" value="${item.code}"></td>
                    <td>${item.price.toFixed(2)}</td>
                    <td>${item.qty} <input type="hidden" name="item_qtys[]" value="${item.qty}"></td>
                    <td style="text-align: right;">${item.total.toFixed(2)}</td>
                    <td class="no-print"><button type="button" onclick="removeItem(${index})" style="background:none; border:none; color:red; cursor:pointer; font-weight:bold;">✕</button></td>
                </tr>
            `;
        });

        document.getElementById('p_total_disp').innerText = pTotal.toFixed(2);
        document.getElementById('p_total_hidden').value = pTotal;
        calcGrandTotal();
    }

    function removeItem(index) {
        itemsArr.splice(index, 1);
        renderTable();
    }

    function calcGrandTotal() {
        const pTotal = parseFloat(document.getElementById('p_total_hidden').value) || 0;
        const sCharge = parseFloat(document.getElementById('s_charge').value) || 0;
        const gTotal = pTotal + sCharge;

        document.getElementById('g_total_disp').innerText = gTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('g_total_hidden').value = gTotal;
    }
</script>

</body>
</html>