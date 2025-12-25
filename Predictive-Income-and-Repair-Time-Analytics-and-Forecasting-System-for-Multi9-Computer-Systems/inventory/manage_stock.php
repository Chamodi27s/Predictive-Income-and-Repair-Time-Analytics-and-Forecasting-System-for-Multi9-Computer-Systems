<?php include("../config/db.php"); 

if(isset($_POST['add_stock'])) {
    $code = mysqli_real_escape_string($conn, $_POST['item_code']);
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $qty = (int)$_POST['quantity'];
    $price = (float)$_POST['unit_price'];

    // අයිතමය දැනටමත් ඇත්නම් ප්‍රමාණය පමණක් එකතු කරයි
    $sql = "INSERT INTO stock (item_code, item_name, quantity, unit_price) 
            VALUES ('$code', '$name', '$qty', '$price') 
            ON DUPLICATE KEY UPDATE quantity = quantity + $qty, unit_price = $price";
    $conn->query($sql);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Stock</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f8f9fa; }
        .box { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 900px; margin: auto; }
        input { padding: 10px; margin: 5px; border: 1px solid #ddd; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #007bff; color: white; }
        .low { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="box">
    <h2>Inventory Management</h2>
    <form method="POST">
        <input type="text" name="item_code" placeholder="Item Code" required>
        <input type="text" name="item_name" placeholder="Item Name" required>
        <input type="number" name="quantity" placeholder="Qty" required>
        <input type="number" step="0.01" name="unit_price" placeholder="Price" required>
        <button type="submit" name="add_stock" style="background: #28a745; color: white; border: none; padding: 11px 20px; border-radius: 5px; cursor: pointer;">Add to Stock</button>
    </form>

    <table>
        <tr><th>Code</th><th>Name</th><th>Available Qty</th><th>Unit Price</th></tr>
        <?php
        $res = $conn->query("SELECT * FROM stock");
        while($r = $res->fetch_assoc()) {
            $class = ($r['quantity'] <= 5) ? 'low' : '';
            echo "<tr><td>{$r['item_code']}</td><td>{$r['item_name']}</td><td class='$class'>{$r['quantity']}</td><td>".number_format($r['unit_price'], 2)."</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>