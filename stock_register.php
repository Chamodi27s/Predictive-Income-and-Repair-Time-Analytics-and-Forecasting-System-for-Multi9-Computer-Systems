<?php
include 'db_config.php';
include 'navbar.php';

$msg = "";

/* =========================
   SAVE STOCK ITEM
========================= */
if (isset($_POST['save'])) {

    $item_code     = trim($_POST['item_code']);
    $item_name     = trim($_POST['item_name']);
    $category_name = trim($_POST['category_name']);
    $qty           = intval($_POST['quantity']);
    $price         = floatval($_POST['unit_price']);
    $status        = $_POST['status']; // Get status from dropdown

    // Check for duplicate item code
    $dupCheck = $conn->query("SELECT * FROM stock WHERE item_code='$item_code'");
    if($dupCheck->num_rows > 0){
        $msg = "❌ Item code already exists!";
    } else {
        // Check if category exists
        $catCheck = $conn->query("SELECT category_id FROM category WHERE category_name='$category_name'");
        if($catCheck->num_rows > 0){
            $category_id = $catCheck->fetch_assoc()['category_id'];
        } else {
            // Add new category
            $conn->query("INSERT INTO category (category_name) VALUES ('$category_name')");
            $category_id = $conn->insert_id;
        }

        $sql = "INSERT INTO stock 
                (item_code, item_name, category_id, quantity, unit_price, status)
                VALUES 
                ('$item_code', '$item_name', '$category_id', '$qty', '$price', '$status')";

        if ($conn->query($sql)) {
            $msg = "✅ Item added successfully";
        } else {
            $msg = "❌ Error: " . $conn->error;
        }
    }
}

// Fetch all categories
$categories = $conn->query("SELECT category_name FROM category ORDER BY category_name");

// Fetch all items grouped by category
$cat_items = [];
$result = $conn->query("SELECT c.category_name, s.item_name FROM stock s JOIN category c ON s.category_id=c.category_id ORDER BY c.category_name, s.item_name");
while($row = $result->fetch_assoc()){
    $cat = $row['category_name'];
    $item = $row['item_name'];
    if(!isset($cat_items[$cat])) $cat_items[$cat] = [];
    if(!in_array($item, $cat_items[$cat])) $cat_items[$cat][] = $item;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef2f5; margin: 0; padding: 0; }
        .form-box { max-width: 600px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
        h2 { text-align: center; color: #166534; margin-bottom: 25px; font-size: 28px; }
        input, select { width: 100%; padding: 12px 15px; margin-bottom: 18px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 15px; }
        input:focus, select:focus { border-color: #16a34a; outline: none; }
        button { width: 100%; padding: 15px; border: none; border-radius: 30px; font-size: 16px; cursor: pointer; background: linear-gradient(90deg,#22c55e,#16a34a); color: #fff; font-weight: bold; transition: 0.3s; }
        button:hover { opacity: 0.9; transform: translateY(-2px); }
        .msg { background: #dcfce7; color: #166534; padding: 12px; border-radius: 10px; margin-bottom: 18px; text-align: center; font-size: 15px; }
        label { font-weight: bold; margin-bottom: 5px; display: block; color: #333; }
        @media (max-width: 640px){ .form-box { margin: 20px; padding: 20px; } }
        .tooltip { position: relative; display: inline-block; cursor: help; }
        .tooltip .tooltiptext { visibility: hidden; width: 220px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 6px; position: absolute; z-index: 1; bottom: 125%; left: 50%; transform: translateX(-50%); opacity: 0; transition: opacity 0.3s; font-size: 13px; }
        .tooltip:hover .tooltiptext { visibility: visible; opacity: 1; }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Stock Register</h2>

    <?php if($msg != ""){ ?>
        <div class="msg"><?php echo $msg; ?></div>
    <?php } ?>

    <form method="post" enctype="multipart/form-data">
        <label>Item Code :</label>
        <input type="text" name="item_code" placeholder="#ITM-01" required>

        <label>Category : <span class="tooltip">?<span class="tooltiptext">Select an existing category or type a new one.</span></span></label>
        <input list="category_suggestions" name="category_name" id="category_name" placeholder="Type or select category" required>
        <datalist id="category_suggestions">
            <?php while($c = $categories->fetch_assoc()){ echo "<option value='{$c['category_name']}'></option>"; } ?>
        </datalist>

        <label>Item Name : <span class="tooltip">?<span class="tooltiptext">Select an existing item for the category or type a new one.</span></span></label>
        <input type="text" name="item_name" id="item_name" list="item_suggestions" placeholder="Type or select item" required>
        <datalist id="item_suggestions"></datalist>

        <label>Quantity :</label>
        <input type="number" name="quantity" placeholder="10" min="0" required>

        <label>Unit Price :</label>
        <input type="number" step="0.01" name="unit_price" placeholder="6000" min="0" required>

        <label>Status :</label>
        <select name="status" required>
            <option value="In Stock">In Stock</option>
            <option value="Out Stock">Out Stock</option>
        </select>

        <button name="save">Save Item</button>
    </form>
</div>

<script>
// Preload items grouped by category
const catItems = <?php echo json_encode($cat_items); ?>;

// Update Item Name suggestions when category changes
document.getElementById('category_name').addEventListener('input', function(){
    const category = this.value;
    const datalist = document.getElementById('item_suggestions');
    datalist.innerHTML = ''; // Clear previous options

    if(catItems[category]){
        catItems[category].forEach(function(item){
            const option = document.createElement('option');
            option.value = item;
            datalist.appendChild(option);
        });
    }
});
</script>

</body>
</html>
