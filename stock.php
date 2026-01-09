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

<?php
// ... (ඔබේ පැරණි PHP code එක එලෙසම තබා ගන්න)
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock Management</title>
<style>
/* ===== BODY & CONTAINER ===== */
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f3f4f6;
    margin: 0;
    padding-top: 110px;   
    padding-left: 40px;
    padding-right: 40px;
}
.container { max-width: 1400px; margin: auto; padding: 20px; }

/* ===== ORANGE ALERT (LOOP LOGIC) ===== */
.popup {
    position: fixed;
    top: 110px;
    right: 20px;
    background: linear-gradient(135deg, #fef3c7, #f97316);
    color: #1f2937;
    padding: 16px 22px;
    border-radius: 16px;
    box-shadow: 0 12px 28px rgba(0,0,0,.2);
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 260px;
    transition: all 0.6s ease;
    opacity: 0;
    pointer-events: none; /* සැඟවී ඇති විට click කළ නොහැක */
    z-index: 900;
}
.popup.show { opacity: 1; pointer-events: auto; }
.popup h4 { margin: 0; font-size: 16px; font-weight: bold; }
.popup p { margin: 0; font-size: 14px; }
.popup button {
    align-self: flex-start;
    background: #fff;
    color: #f97316;
    border: none;
    padding: 6px 14px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: bold;
}

/* ===== CARDS (ඔබේ මුල් ස්ටයිල් එක) ===== */
.cards { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; }
.card {
    flex: 0 0 180px; height: 110px; padding: 15px; border-radius: 16px;
    cursor: pointer; box-shadow: 0 8px 20px rgba(0,0,0,.08); transition: 0.3s;
    display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
}
.card:hover { transform: translateY(-5px); }
.card span { font-size: 24px; margin-bottom: 5px; }
.card h3 { margin: 0; font-size: 11px; font-weight: 700; color: #5a6c7d; text-transform: uppercase; }
.card h1 { margin: 2px 0 0; font-size: 28px; font-weight: 800; color: #2c3e50; }

/* Card Colors - Gradients ඇතුළත් කර ඇත */
.total { background: linear-gradient(135deg, #d1fae5, #a7f3d0); border: 1px solid rgba(34,197,94,.3);}
.in    { background: linear-gradient(135deg, #dbeafe, #bfdbfe); border: 1px solid rgba(59,130,246,.3);}
.out   { background: linear-gradient(135deg, #fee2e2, #fecaca); border: 2px solid rgba(239,68,68,.3);}
.low   { background: linear-gradient(135deg, #fff7ed, #fed7aa); border: 1px solid rgba(249,115,22,.3);}

/* ===== SEARCH & TABLE ===== */
.search-add-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.search-box { padding: 10px 15px 10px 35px; width: 250px; border-radius: 20px; border: 1px solid #ddd; background: #fff; }
.add-btn { background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; padding: 10px 30px; border-radius: 25px; text-decoration: none; font-weight: 700; font-size: 14px; }

.table-box { background: #fff; padding: 20px; border-radius: 18px; box-shadow: 0 8px 20px rgba(0,0,0,.05); overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; font-size: 13px; }
.status { padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: bold; }
.in-stock {background: #dcfce7; color: #166534;}
.low-stock {background: #ffedd5; color: #c2410c;}
.out-stock {background: #fee2e2; color: #b91c1c;}

.pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
.pagination button { padding: 6px 12px; border: none; border-radius: 6px; background: #e2e8f0; cursor: pointer; }
.pagination button.active { background: #22c55e; color: #fff; }
</style>
</head>
<body>

<div class="popup" id="lowPopup">
    <h4>⚠️ Stock Alert</h4>
    <p><b><?= $lowStock ?> items</b> are running low.</p>
    <button onclick="filterLow()">Review Now</button>
</div>

<div class="container">
    <div class="cards">
        <div class="card total" onclick="showAll()"><span>📦</span><h3>Total Items</h3><h1><?= $totalItems ?></h1></div>
        <div class="card in" onclick="filterIn()"><span>✅</span><h3>In Stock</h3><h1><?= $inStock ?></h1></div>
        <div class="card out" onclick="filterOut()"><span>❌</span><h3>Out Stock</h3><h1><?= $outStock ?></h1></div>
        <div class="card low" onclick="filterLow()"><span>⚠️</span><h3>Low Stock</h3><h1><?= $lowStock ?></h1></div>
    </div>

    <div class="search-add-bar">
        <input type="text" class="search-box" placeholder="Search item..." onkeyup="searchTable(this.value)">
        <a href="stock_register.php" class="add-btn">+ Add Item</a>
    </div>

    <div class="table-box">
        <table>
            <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Qty</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
            <tbody id="tableBody">
                <?php 
                $stocks->data_seek(0);
                while($r=$stocks->fetch_assoc()){
                    if($r['quantity']==0){$st="Out Stock";$cl="out-stock";}
                    elseif($r['quantity']<=5){$st="Low Stock";$cl="low-stock";}
                    else{$st="In Stock";$cl="in-stock";}
                ?>
                <tr>
                    <td><?= $r['item_code'] ?></td>
                    <td><?= $r['item_name'] ?></td>
                    <td><?= $r['category_name'] ?></td>
                    <td><input type="number" class="qty-input" value="<?= $r['quantity'] ?>" disabled style="width:45px; border:none; text-align:center; background:transparent; font-weight:bold;"></td>
                    <td>Rs.<?= number_format($r['unit_price'],2) ?></td>
                    <td><span class="status <?= $cl ?>"><?= $st ?></span></td>
                    <td><button onclick="toggleEdit(this)" style="cursor:pointer; border:none; background:none; color:#f97316; font-weight:bold;">Edit</button></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<script>
// --- ALERT LOOP LOGIC (3s ON, 3s OFF) ---
function startAlertLoop() {
    const lCount = <?= $lowStock ?>;
    const popup = document.getElementById("lowPopup");
    if(lCount > 0){
        // මුලින්ම එකපාරක් පෙන්වීමට
        setTimeout(() => popup.classList.add("show"), 500);
        setTimeout(() => popup.classList.remove("show"), 3500);

        // සෑම තත්පර 6කට වරක්ම Loop එක ක්‍රියාත්මක වේ (3s show + 3s hide)
        setInterval(() => {
            popup.classList.add("show");
            setTimeout(() => {
                popup.classList.remove("show");
            }, 3000); // තත්පර 3ක් පෙන්වා තබයි
        }, 6000); // සම්පූර්ණ කාලය තත්පර 6කි
    }
}
window.onload = startAlertLoop;

// --- TABLE LOGIC ---
const rows=[...document.querySelectorAll("#tableBody tr")];
let rowsPerPage=8, page=1;

function showPage(p){
    page=p;
    rows.forEach((r,i)=>r.style.display=(i>=(p-1)*rowsPerPage && i<p*rowsPerPage)?"":"none");
    renderPagination();
}

function renderPagination(){
    let pages=Math.ceil(rows.length/rowsPerPage);
    const pagin=document.getElementById("pagination");
    pagin.innerHTML="";
    for(let i=1;i<=pages;i++){
        let b=document.createElement("button");
        b.textContent=i; if(i===page) b.classList.add("active");
        b.onclick=()=>showPage(i); pagin.appendChild(b);
    }
}
showPage(1);

function searchTable(v){
    v=v.toLowerCase();
    rows.forEach(r=>r.style.display=r.textContent.toLowerCase().includes(v)?"":"none");
}

function filterLow(){
    document.getElementById("lowPopup").classList.remove("show");
    rows.forEach(r=>{
        let q=parseInt(r.querySelector("input").value);
        r.style.display=(q>0 && q<=5)?"":"none";
    });
}
function showAll(){ location.reload(); }

function toggleEdit(btn){
    const tr = btn.closest("tr");
    const input = tr.querySelector("input");
    if(btn.innerText==="Edit"){
        input.disabled=false; input.focus(); btn.innerText="Save";
    } else {
        input.disabled=true;
        fetch("stock_update.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: `item_code=${tr.children[0].innerText}&quantity=${input.value}`
        }).then(()=>location.reload());
    }
}
</script>
</body>
</html>