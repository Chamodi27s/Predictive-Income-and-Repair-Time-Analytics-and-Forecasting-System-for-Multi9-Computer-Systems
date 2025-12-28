<?php
include 'db_config.php';
include 'navbar.php';

$msg = "";

// Save stock item
if(isset($_POST['save'])){
    $item_code = trim($_POST['item_code']);
    $item_name = trim($_POST['item_name']);
    $category_name = trim($_POST['category_name']);
    $qty = intval($_POST['quantity']);
    $price = floatval($_POST['unit_price']);
    $status = $_POST['status'];

    // Check duplicate item code
    $dupCheck = $conn->query("SELECT * FROM stock WHERE item_code='$item_code'");
    if($dupCheck->num_rows>0){
        $msg="❌ Item code already exists!";
    } else {
        // Category check or insert
        $catCheck = $conn->query("SELECT category_id FROM category WHERE category_name='$category_name'");
        if($catCheck->num_rows>0){
            $category_id=$catCheck->fetch_assoc()['category_id'];
        } else {
            $conn->query("INSERT INTO category(category_name) VALUES('$category_name')");
            $category_id=$conn->insert_id;
        }

        // Insert stock
        $sql = "INSERT INTO stock(item_code,item_name,category_id,quantity,unit_price,status) 
                VALUES('$item_code','$item_name',$category_id,$qty,$price,'$status')";
        if($conn->query($sql)) $msg="✅ Item added successfully";
        else $msg="❌ Error: ".$conn->error;
    }
}

// Pre-filled category list
$categories = [
    'Desktop Computers','Laptops','Monitors','Keyboards','Mouse',
    'Printers','Networking Devices','Hard Drives','RAM Modules','Graphic Cards',
    'Motherboards','Power Supplies','Cables & Accessories','Software'
];

// Pre-filled items grouped by category
$cat_items = [
    'Desktop Computers'=>['Dell OptiPlex','HP Pavilion','Lenovo ThinkCentre'],
    'Laptops'=>['Dell Inspiron','HP Envy','Lenovo IdeaPad'],
    'Monitors'=>['Dell 24-inch','Samsung 27-inch','LG 24-inch'],
    'Keyboards'=>['Logitech K120','Dell KB216','HP Wired Keyboard'],
    'Mouse'=>['Logitech M185','Dell WM126','HP X1000'],
    'Printers'=>['HP LaserJet','Canon PIXMA','Epson L3150'],
    'Networking Devices'=>['TP-Link Router','Netgear Switch','D-Link Modem'],
    'Hard Drives'=>['Seagate 1TB','WD 500GB','Toshiba 2TB'],
    'RAM Modules'=>['Corsair 8GB','Kingston 16GB','G.Skill 8GB'],
    'Graphic Cards'=>['NVIDIA GTX 1660','RTX 3060','AMD Radeon RX 6600'],
    'Motherboards'=>['ASUS Prime','Gigabyte B450','MSI Tomahawk'],
    'Power Supplies'=>['Corsair 650W','Cooler Master 500W','EVGA 600W'],
    'Cables & Accessories'=>['HDMI Cable','USB Cable','Mouse Pad'],
    'Software'=>['Windows 10','MS Office','Adobe Photoshop']
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Register</title>
    <style>
        body{font-family:Arial;background:#eef2f5;margin:0;padding:0;}
        .form-box{max-width:600px;margin:50px auto;background:#fff;padding:30px;border-radius:16px;box-shadow:0 15px 35px rgba(0,0,0,0.15);}
        h2{text-align:center;color:#166534;margin-bottom:25px;font-size:28px;}
        input,select{width:100%;padding:12px 15px;margin-bottom:18px;border-radius:10px;border:1px solid #cbd5e1;font-size:15px;}
        input:focus,select:focus{border-color:#16a34a;outline:none;}
        button{width:100%;padding:15px;border:none;border-radius:30px;font-size:16px;cursor:pointer;background:linear-gradient(90deg,#22c55e,#16a34a);color:#fff;font-weight:bold;transition:.3s;}
        button:hover{opacity:.9;transform:translateY(-2px);}
        .msg{background:#dcfce7;color:#166534;padding:12px;border-radius:10px;margin-bottom:18px;text-align:center;font-size:15px;}
        label{font-weight:bold;margin-bottom:5px;display:block;color:#333;}
    </style>
</head>
<body>

<div class="form-box">
    <h2>Stock Register</h2>

    <?php if($msg!=""){ ?>
        <div class="msg"><?= $msg ?></div>
    <?php } ?>

    <form method="post">
        <label>Item Code :</label>
        <input type="text" name="item_code" placeholder="#ITM-01" required>

        <label>Category :</label>
        <input list="category_suggestions" name="category_name" id="category_name" placeholder="Select or type category" required>
        <datalist id="category_suggestions">
            <?php foreach($categories as $c){ echo "<option value='$c'></option>"; } ?>
        </datalist>

        <label>Item Name :</label>
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
const catItems = <?= json_encode($cat_items); ?>;

document.getElementById('category_name').addEventListener('input',function(){
    const category=this.value;
    const datalist=document.getElementById('item_suggestions');
    datalist.innerHTML='';
    if(catItems[category]){
        catItems[category].forEach(item=>{
            const option=document.createElement('option');
            option.value=item;
            datalist.appendChild(option);
        });
    }
});
</script>
</body>
</html>
