<?php 
include("../config/db.php");
$job_no = $_GET['job_no'] ?? '';

if(isset($_POST['save_bill'])) {
    $service_charge = (float)$_POST['service_charge'];
    $items = $_POST['item_code']; 
    $qtys = $_POST['qty'];       
    $date = date('Y-m-d');
    
    $parts_total = 0;
    $conn->begin_transaction();

    try {
        foreach($items as $i => $code) {
            $q = (int)$qtys[$i];
            if(!empty($code) && $q > 0) {
                $st_res = $conn->query("SELECT unit_price, quantity FROM stock WHERE item_code = '$code'");
                $st_data = $st_res->fetch_assoc();

                if($st_data && $st_data['quantity'] >= $q) {
                    $parts_total += ($st_data['unit_price'] * $q);
                    $conn->query("UPDATE stock SET quantity = quantity - $q WHERE item_code = '$code'");
                } else {
                    throw new Exception("ස්ටොක් මදි: " . $code);
                }
            }
        }

        $grand_total = $service_charge + $parts_total;
        $conn->query("INSERT INTO invoice (job_no, invoice_date, service_charge, parts_total, grand_total) 
                      VALUES ('$job_no', '$date', '$service_charge', '$parts_total', '$grand_total')");
        $inv_id = $conn->insert_id;

        $lb = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1")->fetch_assoc();
        $new_bal = ($lb['balance'] ?? 0) + $grand_total;
        $conn->query("INSERT INTO cashbook (invoice_no, date, income, balance) 
                      VALUES ('$inv_id', '$date', '$grand_total', '$new_bal')");

        $conn->commit();
        echo "<script>alert('සාර්ථකයි!'); window.location='view_invoice.php?id=$inv_id';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('".$e->getMessage()."');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Invoice</title>
    <style>
        body { font-family: sans-serif; background: #fdfdfd; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 15px; }
        .item-row { display: flex; gap: 10px; margin-bottom: 15px; background: #f9f9f9; padding: 10px; border-radius: 8px; align-items: center; }
        select, input { padding: 12px; border: 1px solid #ccc; border-radius: 6px; }
        .btn-add { background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-save { background: #007bff; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-size: 18px; cursor: pointer; margin-top: 20px; }
        .remove-btn { background: #ff4d4d; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h3>New Invoice for Job #<?php echo $job_no; ?></h3>
    <form method="POST">
        <label>Service Charge (Rs.)</label><br>
        <input type="number" name="service_charge" value="0" style="width: 200px; margin-bottom: 25px;">
        
        <div id="item-list">
            <div class="item-row">
                <select name="item_code[]" style="flex: 3;" required>
                    <option value="">-- Select Used Part --</option>
                    <?php 
                    $parts = $conn->query("SELECT * FROM stock WHERE quantity > 0");
                    while($p = $parts->fetch_assoc()) {
                        echo "<option value='{$p['item_code']}'>{$p['item_name']} (Rs.{$p['unit_price']})</option>";
                    }
                    ?>
                </select>
                <input type="number" name="qty[]" value="1" min="1" style="flex: 1;" placeholder="Qty">
                <button type="button" class="remove-btn" onclick="removeRow(this)">X</button>
            </div>
        </div>

        <button type="button" class="btn-add" onclick="addRow()">+ Add Another Item</button>
        <button type="submit" name="save_bill" class="btn-save">Confirm & Save Bill</button>
    </form>
</div>

<script>
// අලුත් පේළියක් එකතු කිරීමේ function එක
function addRow() {
    let container = document.getElementById('item-list');
    let rows = document.getElementsByClassName('item-row');
    let newRow = rows[0].cloneNode(true); // පළමු පේළිය කොපි කරයි
    
    // කොපි කළ පේළියේ අගයන් හිස් කිරීම
    newRow.querySelector('select').value = "";
    newRow.querySelector('input').value = "1";
    
    container.appendChild(newRow);
}

// පේළියක් ඉවත් කිරීමේ function එක
function removeRow(btn) {
    let rows = document.getElementsByClassName('item-row');
    if(rows.length > 1) {
        btn.parentElement.remove();
    } else {
        alert("අවම වශයෙන් එක අයිතමයක් තිබිය යුතුය.");
    }
}
</script>

</body>
</html>