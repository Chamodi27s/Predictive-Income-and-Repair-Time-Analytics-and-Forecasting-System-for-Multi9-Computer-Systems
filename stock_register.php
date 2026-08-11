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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/global.css">
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
            background:
                radial-gradient(circle at 8% 18%, rgba(46, 204, 113, 0.14), transparent 27%),
                radial-gradient(circle at 92% 82%, rgba(16, 185, 129, 0.10), transparent 25%),
                linear-gradient(135deg, #f8fffb 0%, #f3f4f6 100%);
            margin: 0;
            padding: calc(var(--nav-height, 88px) + 26px) 20px 60px;
            min-height: 100vh;
            color: var(--text-dark);
            transition: all 0.3s ease;
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

        body.dark-mode .form-header h2, body.dark-mode label {
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

        .page-shell {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .form-box {
            width: 100%;
            margin: 0;
            background: rgba(255, 255, 255, 0.97);
            padding: 36px;
            border-radius: 24px;
            box-shadow: 0 22px 55px -16px rgba(15, 23, 42, 0.20);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid #e5e7eb;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            backdrop-filter: blur(8px);
        }

        .form-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), #34d399);
            z-index: 2;
        }

        .form-box::after {
            content: '';
            position: absolute;
            width: 190px;
            height: 190px;
            right: -105px;
            top: -105px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.055);
            pointer-events: none;
        }

        .form-box > * {
            position: relative;
            z-index: 1;
        }

        .form-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-header-copy {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .form-header-icon {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #047857;
            font-size: 19px;
            box-shadow: 0 7px 16px rgba(16, 185, 129, 0.16);
        }

        .form-header h2 {
            font-size: 23px;
            font-weight: 800;
            margin: 0 0 4px;
            color: #111827;
        }

        .form-header p {
            color: var(--text-gray);
            font-size: 13px;
            margin: 0;
        }

        .form-back-btn {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 11px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857 !important;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            transition: 0.25s ease;
        }

        .form-back-btn:hover {
            background: #d1fae5;
            border-color: #6ee7b7;
            transform: translateY(-2px);
        }

        body.dark-mode .form-header {
            border-bottom-color: #334155;
        }

        body.dark-mode .form-header-icon {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
        }

        body.dark-mode .form-back-btn {
            background: rgba(16, 185, 129, 0.12);
            border-color: rgba(110, 231, 183, 0.35);
            color: #6ee7b7 !important;
        }

        .msg { padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .msg.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .msg.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        .form-section {
            margin-bottom: 22px;
            padding: 22px 22px 2px;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .form-section:focus-within {
            border-color: #a7f3d0;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.08);
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 20px;
        }

        .section-number {
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            display: grid;
            place-items: center;
            border-radius: 9px;
            background: #d1fae5;
            color: #047857;
            font-size: 12px;
            font-weight: 800;
        }

        .section-heading h3 {
            margin: 0 0 2px;
            color: #1f2937;
            font-size: 15px;
            font-weight: 800;
        }

        .section-heading p {
            margin: 0;
            color: #6b7280;
            font-size: 11px;
        }

        body.dark-mode .form-section {
            background: rgba(15, 23, 42, 0.48);
            border-color: #334155;
        }

        body.dark-mode .section-heading h3 {
            color: #f8fafc;
        }

        body.dark-mode .section-heading p {
            color: #94a3b8;
        }

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

        input:hover,
        select:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        input::placeholder {
            color: #9ca3af;
        }

        input:focus, select:focus { border-color: var(--primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }

        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        .form-actions {
            display: grid;
            grid-template-columns: minmax(150px, 0.7fr) minmax(230px, 1.3fr);
            gap: 14px;
            margin-top: 8px;
        }

        .save-btn,
        .cancel-btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 700;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            box-sizing: border-box;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .save-btn {
            border: none;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.20);
        }

        .cancel-btn {
            border: 2px solid #d1d5db;
            background: #ffffff;
            color: #374151 !important;
        }

        .save-btn:hover,
        .cancel-btn:hover {
            transform: translateY(-2px);
        }

        .save-btn:hover {
            box-shadow: 0 12px 25px rgba(16, 185, 129, 0.30);
        }

        .cancel-btn:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }

        body.dark-mode .cancel-btn {
            background: #0f172a;
            border-color: #475569;
            color: #e2e8f0 !important;
        }

        @media (max-width: 650px) {
            body {
                padding: calc(var(--nav-height, 82px) + 18px) 12px 90px;
            }

            .form-box {
                padding: 26px 18px;
                border-radius: 18px;
            }

            .form-section {
                padding: 18px 14px 1px;
                border-radius: 15px;
            }

            .form-header {
                flex-direction: column;
                align-items: stretch;
            }

            .form-header-copy {
                align-items: flex-start;
            }

            .form-back-btn {
                width: 100%;
                box-sizing: border-box;
            }

            .row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] == 'enabled' ? 'dark-mode' : ''; ?>">

<main class="page-shell">
    <div class="form-box">
        <div class="form-header">
            <div class="form-header-copy">
                <div class="form-header-icon" aria-hidden="true">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div>
                    <h2>Register Stock Item</h2>
                    <p>Complete all required fields before saving the inventory item.</p>
                </div>
            </div>
            <a href="stock.php" class="form-back-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Stock
            </a>
        </div>

    <?php if($msg!=""){ ?>
        <div class="msg <?= $msg_type ?>">
            <?= $msg ?>
        </div>
    <?php } ?>

    <form method="post">
        <section class="form-section">
            <div class="section-heading">
                <span class="section-number">01</span>
                <div>
                    <h3>Item Details</h3>
                    <p>Enter the item identity and category information.</p>
                </div>
            </div>

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

        </section>

        <section class="form-section">
            <div class="section-heading">
                <span class="section-number">02</span>
                <div>
                    <h3>Stock and Pricing</h3>
                    <p>Set the opening quantity, unit price and availability.</p>
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

        </section>

        <div class="form-actions">
            <a href="stock.php" class="cancel-btn">
                <i class="fa-solid fa-xmark"></i>
                Cancel
            </a>
            <button type="submit" name="save" class="save-btn">
                <i class="fa-solid fa-floppy-disk"></i>
                Save Inventory Item
            </button>
        </div>
    </form>
    </div>
</main>

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
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>

</html>