<?php
include 'db_config.php';
include 'navbar.php';

/* Summary counts */
$totalItems = $conn->query("SELECT COUNT(*) total FROM stock")->fetch_assoc()['total'];

$lowStock = $conn->query("SELECT COUNT(*) total FROM stock WHERE quantity <= 20 AND quantity > 0")
                ->fetch_assoc()['total'];

$outStock = $conn->query("SELECT COUNT(*) total FROM stock WHERE quantity = 0")
                ->fetch_assoc()['total'];

/* Stock list */
$stocks = $conn->query("
    SELECT s.*, c.category_name 
    FROM stock s 
    LEFT JOIN category c ON s.category_id = c.category_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Stock</title>

<style>
body{
    font-family: Arial, sans-serif;
    background:#f3f6f4;
    margin:0;
}

/* ===== Cards ===== */
.cards{
    display:flex;
    gap:20px;
    padding:25px;
}
.card{
    width: 250px;
    height: 180px;
    background: #ffffff;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card.green{ background:#dcfce7; }
.card.red{ background:#fee2e2; }
.card.yellow{ background:#fef9c3; }

.card h3{ margin:0; font-size:14px; }
.card h1{ margin:10px 0 0; }



/* Add button */
.add-btn{
    margin-left:auto;
    align-self:center;
    background:linear-gradient(to right,#22c55e,#f97316);
    color:#fff;
    padding:14px 24px;
    border-radius:30px;
    text-decoration:none;
    font-weight:bold;
}

/* ===== Table ===== */
.table-box{
    background:#fff;
    margin:0 25px 30px;
    padding:20px;
    border-radius:12px;
    box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

.table-top{
    display:flex;
    justify-content:space-between;
    margin-bottom:15px;
}
.table-top input{
    padding:8px 12px;
    border-radius:20px;
    border:1px solid #ccc;
}

table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}
th,td{
    padding:10px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
}

.status{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}
.in{ background:#dcfce7; color:#166534; }
.out{ background:#fee2e2; color:#991b1b; }

.action a{
    padding:5px 10px;
    font-size:12px;
    border-radius:6px;
    color:#fff;
    text-decoration:none;
}
.edit{ background:#22c55e; }
.delete{ background:#ef4444; }
</style>

</head>
<body>

<!-- ===== Summary cards ===== -->
<div class="cards">

    <div class="card green">
        <h3>Total Items</h3>
        <h1><?= $totalItems ?></h1>
    </div>

    <div class="card red">
        <h3>Low Stock</h3>
        <h1><?= $lowStock ?></h1>
    </div>

    <div class="card yellow">
        <h3>Out of Stock</h3>
        <h1><?= $outStock ?></h1>
    </div>

    <a href="stock_register.php" class="add-btn">+ Add Items</a>
</div>

<!-- ===== Table ===== -->
<div class="table-box">

    <div class="table-top">
        <strong>All Items</strong>
        <input type="text" placeholder="Search here...">
    </div>

    <table>
        <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while($row = $stocks->fetch_assoc()){ ?>
        <tr>
            <td><?= $row['item_code'] ?></td>
            <td><?= $row['item_name'] ?></td>
            <td><?= $row['category_name'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td>Rs.<?= number_format($row['unit_price'],2) ?></td>
            <td>
                <?php if($row['quantity'] > 0){ ?>
                    <span class="status in">In Stock</span>
                <?php } else { ?>
                    <span class="status out">Out Stock</span>
                <?php } ?>
            </td>
            <td class="action">
                <a href="stock_edit.php?code=<?= $row['item_code'] ?>" class="edit">Edit</a>
                <a href="stock_delete.php?code=<?= $row['item_code'] ?>" class="delete">Delete</a>
            </td>
        </tr>
        <?php } ?>

    </table>
</div>

</body>
</html>
