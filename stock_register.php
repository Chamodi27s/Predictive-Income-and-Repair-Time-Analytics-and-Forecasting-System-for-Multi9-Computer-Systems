<?php
include 'db_config.php';
include 'navbar.php';

$msg = "";

if (isset($_POST['save'])) {
    $item_code = $_POST['item_code'];
    $item_name = $_POST['item_name'];
    $category  = $_POST['category'];
    $qty       = $_POST['quantity'];
    $price     = $_POST['unit_price'];
    $status    = ($qty > 0) ? "In Stock" : "Out Stock";

    $sql = "INSERT INTO stock(item_code,item_name,category_id,quantity,unit_price,status)
            VALUES('$item_code','$item_name','$category','$qty','$price','$status')";

    if ($conn->query($sql)) {
        $msg = "Item added successfully";
    } else {
        $msg = "Error adding item";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Item</title>

<style>
body{
    background:#f4f7f6;
    font-family:Arial;
}

.form-box{
    max-width:500px;
    margin:40px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:20px;
    color:#166534;
    text-align:center;
}

input, select{
    width:100%;
    padding:10px;
    margin-bottom:14px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:14px;
}

button{
    width:100%;
    padding:12px;
    background:linear-gradient(90deg,#22c55e,#16a34a);
    border:none;
    color:#fff;
    font-size:15px;
    border-radius:25px;
    cursor:pointer;
}

button:hover{
    opacity:0.9;
}

.msg{
    background:#dcfce7;
    color:#166534;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
}
</style>
</head>

<body>

<div class="form-box">
    <h2>Register Stock</h2>

    <?php if($msg!=""){ echo "<div class='msg'>$msg</div>"; } ?>

    <form method="post">
        <input type="text" name="item_code" placeholder="Item Code" required>
        <input type="text" name="item_name" placeholder="Item Name" required>

        <select name="category" required>
            <option value="">Select Category</option>
            <?php
            $cat = $conn->query("SELECT * FROM category");
            while($c = $cat->fetch_assoc()){
                echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
            }
            ?>
        </select>

        <input type="number" name="quantity" placeholder="Quantity" required>
        <input type="number" step="0.01" name="unit_price" placeholder="Unit Price" required>

        <button name="save">+ Add Item</button>
    </form>
</div>

</body>
</html>
