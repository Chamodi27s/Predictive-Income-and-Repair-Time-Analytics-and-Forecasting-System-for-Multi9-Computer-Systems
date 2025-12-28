<?php
include 'db_config.php';
include 'navbar.php';

/* Summary counts */
$totalItems = $conn->query("SELECT COUNT(*) total FROM stock")->fetch_assoc()['total'];
$inStock    = $conn->query("SELECT COUNT(*) total FROM stock WHERE quantity > 0")->fetch_assoc()['total'];
$outStock   = $conn->query("SELECT COUNT(*) total FROM stock WHERE quantity = 0")->fetch_assoc()['total'];

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
<title>Stock Management</title>
<style>
body{
    font-family:Arial, sans-serif;
    background:#f9fafb;
    margin:0;
}
.container{
    max-width:1200px;
    margin:auto;
    padding:25px;
}

/* ===== CARDS ===== */
.card-container{
    display:flex;
    gap:25px;
    flex-wrap:wrap;
    margin-bottom:40px;
}

.card{
    flex:1 1 280px;
    min-height:150px;
    padding:25px 30px;
    border-radius:24px;
    cursor:pointer;
    position:relative;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    transition: all 0.4s ease;
    display:flex;
    flex-direction:column;
    justify-content:center;
    background:linear-gradient(135deg,#fff,#fefefe);
}

.card:hover{
    transform:translateY(-8px) scale(1.03);
    box-shadow:0 20px 40px rgba(0,0,0,0.12);
}

.card h3{
    margin:0;
    font-size:14px;
    letter-spacing:1px;
    color:#6b7280;
    font-weight:500;
}

.card h1{
    margin:8px 0 0;
    font-size:36px;
    font-weight:700;
    color:#1f2937;
}

.card .emoji{
    font-size:34px;
    position:absolute;
    top:15px;
    right:20px;
    transform:rotate(-15deg);
    transition: transform 0.3s ease;
}

.card:hover .emoji{
    transform:rotate(0deg) scale(1.2);
}

/* LIGHT PASTEL COLORS FOR ALL CARDS */
#cardTotalItems{
    background: linear-gradient(135deg,#dcfce7,#bbf7d0); /* Soft green */
    border:2px solid #86efac;
}
#cardInStock{
    background: linear-gradient(135deg,#fef9c3,#fef3c7); /* Soft yellow */
    border:2px solid #fde68a;
}
#cardOutStock{
    background: linear-gradient(135deg,#fee2e2,#fecaca); /* Soft pink */
    border:2px solid #fca5a5;
}

/* ===== ADD BUTTON ===== */
.add-btn-wrap{
    text-align:right;
    margin-bottom:25px;
}
.add-stock-btn{
    background:#22c55e;
    color:#fff;
    padding:14px 34px;
    border-radius:50px;
    font-size:15px;
    font-weight:bold;
    text-decoration:none;
    box-shadow:0 8px 20px rgba(0,0,0,.18);
    transition:.3s;
}
.add-stock-btn:hover{
    background:#16a34a;
    transform:translateY(-3px);
}

/* ===== TABLE ===== */
.table-box{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
}
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}
th,td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
}
th{
    background:#f3f4f6;
    font-size:12px;
    text-transform:uppercase;
}
tbody tr:hover{background:#f9fafb}

.status{
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}
.in{background:#dcfce7;color:#166534}
.out{background:#fee2e2;color:#b91c1c}

/* ACTION BUTTONS */
.action button{
    padding:6px 14px;
    border:none;
    border-radius:8px;
    color:#fff;
    cursor:pointer;
}
.editBtn{background:#22c55e}
.saveBtn{background:#3b82f6}
.cancelBtn{background:#ef4444}

/* PAGINATION BUTTONS */
#prevBtn, #nextBtn{
    padding:8px 16px;
    margin:5px;
    border:none;
    border-radius:6px;
    background:#3b82f6;
    color:#fff;
    cursor:pointer;
    transition:0.3s;
}
#prevBtn:hover, #nextBtn:hover{
    background:#2563eb;
}
</style>
</head>

<body>
<div class="container">

<!-- CARDS -->
<div class="card-container">
    <div class="card" id="cardTotalItems">
        <span class="emoji">📦</span>
        <h3>Total Items</h3>
        <h1><?= $totalItems ?></h1>
    </div>

    <div class="card" id="cardInStock">
        <span class="emoji">✅</span>
        <h3>In Stock</h3>
        <h1><?= $inStock ?></h1>
    </div>

    <div class="card" id="cardOutStock">
        <span class="emoji">❌</span>
        <h3>Out of Stock</h3>
        <h1><?= $outStock ?></h1>
    </div>
</div>

<!-- ADD BUTTON -->
<div class="add-btn-wrap">
    <a href="stock_register.php" class="add-stock-btn">+ Add New Item</a>
</div>

<!-- TABLE -->
<div class="table-box">
<table id="stockTable">
<thead>
<tr>
    <th>Item Code</th>
    <th>Item Name</th>
    <th>Category</th>
    <th>Quantity</th>
    <th>Unit Price</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody id="tableBody">
<?php while($r=$stocks->fetch_assoc()){
$status=$r['quantity']>0?'In Stock':'Out Stock';
?>
<tr>
<td><?= $r['item_code'] ?></td>
<td><?= $r['item_name'] ?></td>
<td><?= $r['category_name'] ?></td>
<td class="qty"><?= $r['quantity'] ?></td>
<td>Rs.<?= number_format($r['unit_price'],2) ?></td>
<td>
    <span class="status <?= $status=='In Stock'?'in':'out' ?>"><?= $status ?></span>
</td>
<td class="action">
    <button class="editBtn">Edit</button>
    <button class="saveBtn" style="display:none">Save</button>
    <button class="cancelBtn" style="display:none">Cancel</button>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<!-- Pagination Buttons -->
<div style="margin-top:15px; text-align:center;">
    <button id="prevBtn">Previous</button>
    <button id="nextBtn">Next</button>
</div>
</div>
</div>

<script>
// ===== EDIT ROW =====
document.querySelectorAll('.editBtn').forEach(b=>{
    b.onclick=()=>{
        let r=b.closest('tr');
        let q=r.querySelector('.qty');
        q.innerHTML=`<input type="number" min="0" value="${q.textContent}">`;
        b.style.display='none';
        r.querySelector('.saveBtn').style.display='inline-block';
        r.querySelector('.cancelBtn').style.display='inline-block';
    };
});

// CANCEL
document.querySelectorAll('.cancelBtn').forEach(b=>{
    b.onclick=()=>location.reload();
});

// SAVE
document.querySelectorAll('.saveBtn').forEach(b=>{
    b.onclick=()=>{
        let r=b.closest('tr');
        let code=r.cells[0].textContent;
        let qty=r.querySelector('.qty input').value;

        fetch('stock_update.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`item_code=${code}&quantity=${qty}`
        })
        .then(res=>res.json())
        .then(d=>{
            if(d.status==='success') location.reload();
            else alert(d.message);
        });
    };
});

// ===== CARD FILTERS =====
cardOutStock.onclick=()=>{
    document.querySelectorAll('tbody tr').forEach(r=>{
        r.style.display=parseInt(r.querySelector('.qty').textContent)==0?'':'none';
    });
};
cardInStock.onclick=()=>{
    document.querySelectorAll('tbody tr').forEach(r=>{
        r.style.display=parseInt(r.querySelector('.qty').textContent)>0?'':'none';
    });
};
cardTotalItems.onclick=()=>location.reload();

// ===== PAGINATION =====
const table = document.getElementById('tableBody');
const rows = Array.from(table.querySelectorAll('tr'));
const rowsPerPage = 10;
let currentPage = 1;
const totalPages = Math.ceil(rows.length / rowsPerPage);

function showPage(page){
    const start = (page-1)*rowsPerPage;
    const end = start + rowsPerPage;
    rows.forEach((row,i)=>{
        row.style.display = (i >= start && i < end) ? '' : 'none';
    });
}
showPage(currentPage);

document.getElementById('prevBtn').onclick = ()=>{
    if(currentPage > 1){
        currentPage--;
        showPage(currentPage);
    }
}
document.getElementById('nextBtn').onclick = ()=>{
    if(currentPage < totalPages){
        currentPage++;
        showPage(currentPage);
    }
}
</script>
</body>
</html>
