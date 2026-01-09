<?php
include 'db_config.php';
include 'navbar.php';

$msg = "";
$msg_type = "";

// Save stock item
if(isset($_POST['save'])){
    $item_code = trim($_POST['item_code']);
    $item_name = trim($_POST['item_name']);
    $category_name = trim($_POST['category_name']);
    $qty = intval($_POST['quantity']);
    $price = floatval($_POST['unit_price']);
    $status = $_POST['status'];

    $dupCheck = $conn->query("SELECT * FROM stock WHERE item_code='$item_code'");
    if($dupCheck->num_rows>0){
        $msg="⚠️ Item code already exists!";
        $msg_type = "error";
    } else {
        $catCheck = $conn->query("SELECT category_id FROM category WHERE category_name='$category_name'");
        if($catCheck->num_rows>0){
            $category_id=$catCheck->fetch_assoc()['category_id'];
        } else {
            $conn->query("INSERT INTO category(category_name) VALUES('$category_name')");
            $category_id=$conn->insert_id;
        }

        $sql = "INSERT INTO stock(item_code,item_name,category_id,quantity,unit_price,status) 
                VALUES('$item_code','$item_name',$category_id,$qty,$price,'$status')";
        if($conn->query($sql)) {
            $msg="✅ Item added successfully";
            $msg_type = "success";
        }
        else {
            $msg="❌ Error: ".$conn->error;
            $msg_type = "error";
        }
    }
}

// Lists
$categories = [
    'Desktop Computers','Laptops','Monitors','Keyboards','Mouse',
    'Printers','Networking Devices','Hard Drives','RAM Modules','Graphic Cards',
    'Motherboards','Power Supplies','Cables & Accessories','Software'
];

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-light: #d1fae5;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --bg-color: #f3f4f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #f3f4f6 100%);
            margin: 0;
            padding-top: 120px;
            padding-left: 40px;
            padding-right: 40px;
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* Animated Form Entrance */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-box {
            max-width: 750px;
            margin: 0 auto 50px;
            background: #ffffff;
            padding: 45px;
            border-radius: 24px;
            box-shadow: 0 20px 60px -10px rgba(16, 185, 129, 0.15);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }

        /* Decorative top bar */
        .form-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), #34d399);
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .header h2 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }
        
        .header p {
            color: var(--text-gray);
            font-size: 15px;
            margin: 0;
        }

        /* Message Box */
        .msg {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .msg.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .msg.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Inputs */
        .input-group {
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            color: #374151;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        input, select {
            width: 100%;
            padding: 16px 16px 16px 50px;
            border-radius: 14px;
            border: 2px solid #e5e7eb;
            font-size: 15px;
            font-weight: 500;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
        }

        /* Input Focus Effects */
        input:focus, select:focus {
            border-color: var(--primary);
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.1);
        }

        .input-wrapper input:focus ~ i, 
        .input-wrapper select:focus ~ i {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }

        /* Two Column Layout */
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        /* Button */
        button {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.25);
            margin-top: 15px;
            position: relative;
            overflow: hidden;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.35);
        }
        
        button:active {
            transform: translateY(-1px);
        }

        /* Button Shine Effect */
        button::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        button:hover::after {
            left: 100%;
        }

        @media (max-width: 650px) {
            .row { grid-template-columns: 1fr; gap: 0; }
            .form-box { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="form-box">
    <div class="header">
        <h2>Add New Stock</h2>
        <p>Fill in the details below to update inventory</p>
    </div>

    <?php if($msg!=""){ ?>
        <div class="msg <?= $msg_type ?>">
            <?= $msg ?>
        </div>
    <?php } ?>

    <form method="post">
        <div class="row">
            <div class="input-group">
                <label>Item Code</label>
                <div class="input-wrapper">
                    <input type="text" name="item_code" placeholder="Ex: ITM-2024" required>
                    <i class="fa-solid fa-barcode"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Category</label>
                <div class="input-wrapper">
                    <input list="category_suggestions" name="category_name" id="category_name" placeholder="Choose Category" required>
                    <i class="fa-solid fa-layer-group"></i>
                    <datalist id="category_suggestions">
                        <?php foreach($categories as $c){ echo "<option value='$c'></option>"; } ?>
                    </datalist>
                </div>
            </div>
        </div>

        <div class="input-group">
            <label>Item Name</label>
            <div class="input-wrapper">
                <input type="text" name="item_name" id="item_name" list="item_suggestions" placeholder="Ex: Logitech G502 Mouse" required>
                <i class="fa-solid fa-box-open"></i>
                <datalist id="item_suggestions"></datalist>
            </div>
        </div>

        <div class="row">
            <div class="input-group">
                <label>Quantity</label>
                <div class="input-wrapper">
                    <input type="number" name="quantity" placeholder="0" min="0" required>
                    <i class="fa-solid fa-cubes-stacked"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Unit Price (Rs.)</label>
                <div class="input-wrapper">
                    <input type="number" step="0.01" name="unit_price" placeholder="0.00" min="0" required>
                    <i class="fa-solid fa-tag"></i>
                </div>
            </div>
        </div>

        <div class="input-group">
            <label>Stock Status</label>
            <div class="input-wrapper">
                <select name="status" required>
                    <option value="In Stock">In Stock</option>
                    <option value="Out Stock">Out Stock</option>
                </select>
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <button name="save">Save Inventory Item</button>
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