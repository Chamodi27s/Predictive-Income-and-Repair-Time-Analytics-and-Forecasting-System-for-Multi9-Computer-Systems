<?php
include 'db_config.php';
include 'navbar.php';

/* Summary count */
$totalItems = $conn->query("SELECT COUNT(*) total FROM stock")->fetch_assoc()['total'];

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
        body{ font-family: Arial, sans-serif; background:#f9fafb; margin:0; padding:0;}
        .container{ max-width:1200px; margin:0 auto; padding:20px;}
        /* ===== Card ===== */
        .card-container{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px;}
        .card{ flex:1 1 200px; background:#dcfce7; padding:20px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.08); position:relative;}
        .card h3{ font-size:14px; margin:0; color:#166534;}
        .card h1{ font-size:28px; margin:5px 0 0; color:#166534; }
        .add-btn{ background:linear-gradient(90deg,#22c55e,#f97316); color:#fff; padding:14px 24px; border-radius:30px; text-decoration:none; font-weight:bold; transition:0.3s; }
        .add-btn:hover{ opacity:0.9; transform:translateY(-2px); }

        /* ===== Table ===== */
        .table-box{ background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.08);}
        .table-top{ display:flex; justify-content:space-between; margin-bottom:15px; flex-wrap:wrap; gap:10px; }
        .table-top input{ padding:8px 12px; border-radius:20px; border:1px solid #ccc; flex:1 1 200px; }
        table{ width:100%; border-collapse:collapse; font-size:14px;}
        th,td{ padding:12px; border-bottom:1px solid #e5e7eb; text-align:left;}
        th{ background:#f3f4f6; color:#111; text-transform:uppercase; font-size:12px;}
        #stockTable tbody tr:hover { background:#f3f4f6; } /* hover effect */
        .status{ padding:5px 12px; border-radius:20px; font-size:12px; font-weight:bold; display:inline-block;}
        .in{ background:#dcfce7; color:#166534;}
        .out{ background:#fef2f2; color:#b91c1c;}
        .action a{ padding:6px 12px; font-size:12px; border-radius:6px; color:#fff; text-decoration:none; margin-right:5px; transition:0.3s; }
        .edit{ background:#22c55e;}
        .edit:hover{ opacity:0.9; transform:translateY(-2px);}
        .delete{ background:#ef4444;}
        .delete:hover{ opacity:0.9; transform:translateY(-2px);}
        /* Pagination */
        .pagination{ margin-top:15px; display:flex; justify-content:center; gap:5px; }
        .pagination button{ padding:6px 12px; border:none; border-radius:6px; background:#f3f4f6; cursor:pointer; transition:0.3s;}
        .pagination button.active{ background:#22c55e; color:#fff; }
        .pagination button:hover{ background:#16a34a; color:#fff; }
        @media(max-width:768px){ .table-top{ flex-direction:column; gap:10px;} .card-container{ flex-direction:column; gap:10px;} }
    </style>
</head>
<body>
<div class="container">
    <!-- Cards -->
    <div class="card-container">
        <div class="card total-card">
            <div class="card-top">
                <h3>Total Items</h3>
                <span class="card-icon">📦</span>
            </div>
            <h1><?= $totalItems ?></h1>
            <p class="card-sub">↑ 12% vs last week</p>
        </div>
        <a href="stock_register.php" class="add-btn">+ Add Items</a>
    </div>

    <!-- Table -->
    <div class="table-box">
        <div class="table-top">
            <strong>All Items</strong>
            <input type="text" id="searchInput" placeholder="Search here...">
        </div>
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
            <tbody>
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
                        <a href="stock_delete.php?code=<?= $row['item_code'] ?>" class="delete" onclick="return confirm('Are you sure to delete this item?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<script>
// ===== Search + Pagination =====
const rowsPerPage = 8;
const table = document.getElementById('stockTable');
const tbody = table.querySelector('tbody');
let rows = Array.from(tbody.querySelectorAll('tr'));
const pagination = document.getElementById('pagination');
let currentPage = 1;

function showPage(page){
    currentPage = page;
    let start = (page-1)*rowsPerPage;
    let end = start+rowsPerPage;
    rows.forEach((row,i)=>{
        row.style.display = (i>=start && i<end)?'':'none';
    });
    renderPagination();
}

function renderPagination(){
    pagination.innerHTML='';
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    for(let i=1;i<=totalPages;i++){
        let btn = document.createElement('button');
        btn.textContent=i;
        if(i===currentPage) btn.classList.add('active');
        btn.addEventListener('click',()=>showPage(i));
        pagination.appendChild(btn);
    }
}

// Search filter
document.getElementById('searchInput').addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
    // Update rows array for pagination
    rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
    showPage(1);
});

showPage(1);
</script>
</body>
</html>
