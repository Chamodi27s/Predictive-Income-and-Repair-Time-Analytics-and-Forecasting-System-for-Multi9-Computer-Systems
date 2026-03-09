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
            /* Navbar එකේ පෙනුම අනුව padding සකස් කිරීම */
            padding-top: 100px; 
            padding-left: 20px;
            padding-right: 20px;
            min-height: 100vh;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        /* --- NAVBAR ALIGNMENT FIX --- */
        /* මෙය navbar.php එකේ ඇති topbar class එකට බලපායි */
        .topbar {
            height: 70px !important;
            padding: 0 50px !important;
            position: fixed !important;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* --- DARK MODE CSS --- */
        body.dark-mode {
            background: linear-gradient(135deg, #020617, #0f172a) !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-box {
            background: rgba(30, 41, 59, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5) !important;
            backdrop-filter: blur(10px);
        }

        body.dark-mode .header h2, body.dark-mode label {
            color: #ffffff !important;
        }

        body.dark-mode input, body.dark-mode select {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        /* --- FORM DESIGN --- */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-box {
            max-width: 750px;
            margin: 0 auto 50px;
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px -10px rgba(16, 185, 129, 0.15);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }

        .form-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), #34d399);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { font-size: 26px; font-weight: 800; margin: 0 0 5px 0; }
        .header p { color: var(--text-gray); font-size: 14px; margin: 0; }

        .msg { padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .msg.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .msg.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: #374151; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 16px; pointer-events: none; }

        input, select {
            width: 100%;
            padding: 14px 14px 14px 50px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            font-size: 14px;
            background: #f9fafb;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        input:focus, select:focus { border-color: var(--primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }

        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(16, 185, 129, 0.3); }

        @media (max-width: 650px) { .row { grid-template-columns: 1fr; gap: 0; } body { padding-top: 90px; } }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] == 'enabled' ? 'dark-mode' : ''; ?>">

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
                    <option value="Low Stock">Low Stock</option>
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
<?php include 'chatbot.php'; ?>
</html>