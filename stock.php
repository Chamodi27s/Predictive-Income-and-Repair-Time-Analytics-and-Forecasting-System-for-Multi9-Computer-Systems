<?php
include 'db_config.php';
include 'navbar.php';

/* COUNTS */
$totalItems = $conn->query("SELECT COUNT(*) total FROM stock")->fetch_assoc()['total'];
$inStock = $conn->query("SELECT COUNT(*) total FROM stock WHERE quantity > 5")->fetch_assoc()['total'];
$outStock = $conn->query("SELECT COUNT(*) total FROM stock WHERE quantity = 0")->fetch_assoc()['total'];
$lowStock = $conn->query("SELECT COUNT(*) total FROM stock WHERE quantity > 0 AND quantity <= 5")->fetch_assoc()['total'];

/* STOCK LIST */
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
body{font-family:'Segoe UI',Arial,sans-serif;background:#f3f4f6;margin:0;padding:0}
.container{max-width:1300px;margin:auto;padding:25px}

/* ===== TOP BAR & SEARCH ===== */
.search-add-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:15px;
    margin-bottom:20px; 
    flex-wrap:wrap;
}
.search-box{
    padding:10px 18px 10px 38px;
    width:280px;
    border-radius:28px;
    border:1px solid #ddd;
    font-size:14px;
    background:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23999" viewBox="0 0 24 24"><path d="M21.71 20.29l-3.388-3.387A8.948 8.948 0 0018 10a9 9 0 10-9 9 8.948 8.948 0 006.902-2.678l3.388 3.387a1 1 0 001.414-1.414zM4 10a6 6 0 1112 0 6 6 0 01-12 0z"/></svg>') no-repeat 10px center;
    background-size:18px;
}
.add-btn{
    background:linear-gradient(90deg,#22c55e,#16a34a);
    color:#fff;
    padding:10px 24px;
    border-radius:36px;
    text-decoration:none;
    font-weight:bold;
    box-shadow:0 8px 18px rgba(0,0,0,.15);
    transition:.3s;
}
.add-btn:hover{transform:translateY(-2px);opacity:.95}

/* ===== CARDS ===== */
.cards{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;justify-content:flex-start}
.card{
    flex:0 0 200px; 
    height:140px;   
    padding:20px;
    border-radius:20px;
    position:relative;
    cursor:pointer;
    box-shadow:0 10px 20px rgba(0,0,0,.08);
    transition:.3s;
}
.card:hover{transform:translateY(-3px)}
.card span{font-size:32px;position:absolute;top:15px;right:15px}
.card h3{margin:0;font-size:14px;color:#1f2937}
.card h1{margin-top:8px;font-size:28px;color:#1f2937}

/* Card colors – light/pastel */
.total{background:linear-gradient(135deg,#d1fae5,#a7f3d0)}   /* Light Green */
.in{background:linear-gradient(135deg,#dbeafe,#bfdbfe)}      /* Light Blue */
.out{background:linear-gradient(135deg,#fee2e2,#fecaca)}     /* Light Red */
.low{background:linear-gradient(135deg,#ffedd5,#fed7aa)}     /* Light Orange */

/* ===== TABLE ===== */
.table-box{
    background:#fff;padding:22px;border-radius:18px;box-shadow:0 10px 25px rgba(0,0,0,.08);
    margin-bottom:15px;
}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left}
th{background:#f9fafb;font-size:13px;color:#555}
.qty-input{width:70px;padding:6px;border-radius:6px;border:1px solid #ccc;text-align:center;pointer-events:none;}
.edit-btn{padding:6px 12px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;transition:.3s;}
.edit-btn:hover{opacity:.85;}

/* Status */
.status{
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}
.in-stock{background:#dcfce7;color:#166534}
.low-stock{background:#ffedd5;color:#c2410c}
.out-stock{background:#fee2e2;color:#b91c1c}

/* ===== PAGINATION BELOW TABLE ===== */
.pagination{
    display:flex;
    justify-content:center; 
    gap:8px;
    margin:20px 0 40px;
}
.pagination button{
    padding:8px 14px;
    border:none;
    border-radius:6px; 
    background:#22c55e;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
    transition:.3s;
}
.pagination button:hover{opacity:.85}
.pagination button.active{background:#16a34a}

/* ===== POPUP ===== */
.popup{
    position:fixed;
    top:-100px;
    right:20px;
    background: linear-gradient(135deg,#fef3c7,#f97316);
    color:#1f2937;
    padding:16px 22px;
    border-radius:16px;
    box-shadow:0 12px 28px rgba(0,0,0,.2);
    display:flex;
    flex-direction:column;
    gap:6px;
    min-width:220px;
    transition:top 0.6s ease, opacity 0.6s ease;
    opacity:0;
    z-index:999;
}
.popup.show{
    top:20px;
    opacity:1;
}
.popup h4{margin:0;font-size:16px;font-weight:bold}
.popup p{margin:0;font-size:14px}
.popup button{
    align-self:flex-start;
    background:#fff;
    color:#f97316;
    border:none;
    padding:6px 14px;
    border-radius:20px;
    cursor:pointer;
    font-weight:bold;
    transition:.3s;
}
.popup button:hover{opacity:.85}
</style>
</head>
<body>
<div class="container">

<!-- 🔔 LOW STOCK POPUP -->
<div class="popup" id="lowPopup">
    <h4>🔔 Low Stock Alert</h4>
    <p id="lowCount"><?= $lowStock ?> items are running low!</p>
    <button onclick="filterLow()">View Items</button>
</div>

<!-- CARDS -->
<div class="cards">
    <div class="card total" onclick="showAll()">
        <span>📦</span><h3>Total Items</h3><h1><?= $totalItems ?></h1>
    </div>
    <div class="card in" onclick="filterIn()">
        <span>✅</span><h3>In Stock</h3><h1><?= $inStock ?></h1>
    </div>
    <div class="card out" onclick="filterOut()">
        <span>❌</span><h3>Out Stock</h3><h1><?= $outStock ?></h1>
    </div>
    <div class="card low" onclick="filterLow()">
        <span>⚠️</span><h3>Low Stock</h3><h1><?= $lowStock ?></h1>
    </div>
</div>

<!-- SEARCH + ADD -->
<div class="search-add-bar">
    <input type="text" class="search-box" placeholder="🔍 Search item..." onkeyup="searchTable(this.value)">
    <a href="stock_register.php" class="add-btn">+ Add Item</a>
</div>

<!-- TABLE -->
<div class="table-box">
<table>
<thead>
<tr>
    <th>Code</th>
    <th>Name</th>
    <th>Category</th>
    <th>Qty</th>
    <th>Price</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody id="tableBody">
<?php while($r=$stocks->fetch_assoc()){
    if($r['quantity']==0){$st="Out Stock";$cl="out-stock";}
    elseif($r['quantity']<=5){$st="Low Stock";$cl="low-stock";}
    else{$st="In Stock";$cl="in-stock";}
?>
<tr>
<td><?= $r['item_code'] ?></td>
<td><?= $r['item_name'] ?></td>
<td><?= $r['category_name'] ?></td>
<td>
    <input type="number" class="qty-input" min="0" value="<?= $r['quantity'] ?>" disabled>
</td>
<td>Rs.<?= number_format($r['unit_price'],2) ?></td>
<td><span class="status <?= $cl ?>"><?= $st ?></span></td>
<td>
    <button class="edit-btn <?= $cl ?>" onclick="toggleEdit(this)">Edit</button>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<!-- PAGINATION BELOW TABLE -->
<div class="pagination" id="pagination"></div>
</div>

<script>
const rows=[...document.querySelectorAll("#tableBody tr")];
let rowsPerPage=8,page=1;

function showPage(p){
    page=p;
    rows.forEach((r,i)=>r.style.display=(i>=(p-1)*rowsPerPage && i<p*rowsPerPage)?"":"none");
    renderPagination();
}
function renderPagination(){
    let pages=Math.ceil(rows.length/rowsPerPage);
    const pagination=document.getElementById("pagination");
    pagination.innerHTML="";
    for(let i=1;i<=pages;i++){
        let b=document.createElement("button");
        b.textContent=i;
        if(i===page)b.classList.add("active");
        b.onclick=()=>showPage(i);
        pagination.appendChild(b);
    }
}
showPage(1);

function searchTable(v){
    v=v.toLowerCase();
    rows.forEach(r=>r.style.display=r.textContent.toLowerCase().includes(v)?"":"none");
}
function showAll(){location.reload()}
function filterLow(){rows.forEach(r=>{let q=r.querySelector(".qty-input").value;r.style.display=(q>0&&q<=5)?"":"none"})}
function filterIn(){rows.forEach(r=>{let q=r.querySelector(".qty-input").value;r.style.display=(q>5)?"":"none"})}
function filterOut(){rows.forEach(r=>{let q=r.querySelector(".qty-input").value;r.style.display=(q==0)?"":"none"})}

// Toggle Edit/Save button
function toggleEdit(btn){
    const tr = btn.closest("tr");
    const input = tr.querySelector(".qty-input");
    if(btn.innerText==="Edit"){
        input.disabled=false;
        input.focus();
        btn.innerText="Save";
    } else {
        input.disabled=true;
        let qty = parseInt(input.value);
        if(isNaN(qty)||qty<0) qty=0;
        input.value=qty;

        fetch("stock_update.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: `item_code=${encodeURIComponent(tr.children[0].innerText)}&quantity=${qty}`
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.status==="success"){
                // Update row status
                const statusSpan = tr.querySelector(".status");
                let newStatus, cl;
                if(qty===0){ newStatus="Out Stock"; cl="out-stock"; }
                else if(qty<=5){ newStatus="Low Stock"; cl="low-stock"; }
                else{ newStatus="In Stock"; cl="in-stock"; }
                statusSpan.innerText=newStatus;
                statusSpan.className="status "+cl;
                btn.className="edit-btn "+cl;

                // Update cards
                let totalQty=0,inStock=0,outStock=0,lowStock=0;
                rows.forEach(r=>{
                    let q=parseInt(r.querySelector(".qty-input").value);
                    totalQty+=q;
                    if(q===0) outStock++;
                    else if(q<=5) lowStock++;
                    else inStock++;
                });
                document.querySelector(".card.total h1").innerText=totalQty;
                document.querySelector(".card.in h1").innerText=inStock;
                document.querySelector(".card.out h1").innerText=outStock;
                document.querySelector(".card.low h1").innerText=lowStock;
            } else alert("Error: "+data.message);
        });

        btn.innerText="Edit";
    }
}

// Low stock popup
const lowPopup=document.getElementById("lowPopup");
const lowCount=parseInt(document.getElementById("lowCount").innerText);
if(lowCount>0){
    setTimeout(()=>lowPopup.classList.add("show"),800);
    setTimeout(()=>lowPopup.classList.remove("show"),5800);
}
</script>
</body>
</html>
