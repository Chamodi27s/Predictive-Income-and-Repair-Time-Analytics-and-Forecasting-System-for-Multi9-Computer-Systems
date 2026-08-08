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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock Management</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary: #2ecc71;
    --primary-hover: #27ae60;
    --bg-main: #f8fafc;
    --card-bg: #ffffff;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --shadow-md: 0 4px 12px rgba(0,0,0,0.05);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
    margin: 0;
    padding: 125px 25px 40px; 
    color: var(--text-main);
}

.page-container {
    max-width: 1250px;
    margin: auto;
}

/* ===== COMPACT CARDS ===== */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-top: 15px;
    margin-bottom: 28px;
}

.card {
    padding: 16px 14px;
    border-radius: 12px;
    cursor: pointer;
    text-align: center;
    box-shadow: var(--shadow-md);
    transition: 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

.card span {
    font-size: 26px;
    margin-bottom: 8px;
}

.card h3 {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: #475569;
    margin-bottom: 4px;
    letter-spacing: 0.5px;
}

.card h1 {
    font-size: 32px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}

/* ENHANCED SOLID COLORS WITH HIGH CONTRAST TEXT */
.card.total {
    background: #dbeafe;
    border-color: #bfdbfe;
}
.card.total h3 { color: #1e40af !important; }
.card.total h1 { color: #1e3a8a !important; }

.card.in {
    background: #dcfce7;
    border-color: #bbf7d0;
}
.card.in h3 { color: #15803d !important; }
.card.in h1 { color: #14532d !important; }

.card.out {
    background: #fee2e2;
    border-color: #fecaca;
}
.card.out h3 { color: #b91c1c !important; }
.card.out h1 { color: #7f1d1d !important; }

.card.low {
    background: #ffedd5;
    border-color: #fed7aa;
}
.card.low h3 { color: #c2410c !important; }
.card.low h1 { color: #7c2d12 !important; }

/* ===== MAIN BOX ===== */
.content-box {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 22px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
}

/* ===== SEARCH BAR ===== */
.search-add-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.search-box {
    width: 290px;
    padding: 13px 18px;
    border: 2px solid var(--border);
    border-radius: 14px;
    font-size: 14px;
    outline: none;
    background: #f8fafc;
    transition: 0.3s;
}

.search-box:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(46,204,113,0.15);
}

.add-btn {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    padding: 13px 26px;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
    box-shadow: 0 5px 14px rgba(46,204,113,0.30);
    transition: 0.3s;
}

.add-btn:hover {
    transform: translateY(-2px);
}

/* ===== TABLE ===== */
.table-box {
    overflow-x: auto;
    border-radius: 16px;
    border: 1px solid var(--border);
}

table {
    width: 100%;
    min-width: 950px;
    border-collapse: separate;
    border-spacing: 0;
}

th {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    padding: 16px;
    text-align: left;
    font-size: 13px;
    text-transform: uppercase;
}

td {
    padding: 15px 16px;
    border-bottom: 1px solid #eef2f7;
    font-size: 14px;
    color: #334155;
}

tbody tr {
    transition: 0.25s;
}

tbody tr:hover {
    background: #f8fafc;
    transform: translateX(4px);
}

.qty-input {
    width: 55px;
    border: none;
    background: #f8fafc;
    text-align: center;
    font-weight: 800;
    padding: 6px;
    border-radius: 8px;
}

.qty-input:enabled {
    border: 2px solid #f97316;
    background: #fff7ed;
}

.status {
    padding: 7px 13px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
}

.in-stock {
    background: #dcfce7;
    color: #166534;
}

.low-stock {
    background: #ffedd5;
    color: #c2410c;
}

.out-stock {
    background: #fee2e2;
    color: #b91c1c;
}

.edit-btn {
    border: none;
    background: #fff7ed;
    color: #f97316;
    padding: 7px 14px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 800;
}

/* ===== PAGINATION ===== */
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 22px;
}

.pagination button {
    padding: 8px 13px;
    border: none;
    border-radius: 10px;
    background: #e2e8f0;
    cursor: pointer;
    font-weight: 700;
    color: #1e293b;
}

.pagination button.active {
    background: #22c55e;
    color: white;
}

/* ===== DARK MODE ===== */
body.dark-mode {
    background: linear-gradient(135deg, #020617 0%, #0f172a 100%) !important;
    color: #e2e8f0 !important;
}

body.dark-mode .content-box {
    background: rgba(30,41,59,0.85) !important;
    border-color: rgba(255,255,255,0.08);
}

body.dark-mode td {
    color: #cbd5e1;
    border-bottom-color: rgba(255,255,255,0.06);
}

body.dark-mode tbody tr:hover {
    background: rgba(255,255,255,0.05);
}

body.dark-mode .search-box,
body.dark-mode .qty-input {
    background: rgba(15,23,42,0.8);
    color: white;
    border-color: rgba(255,255,255,0.10);
}

body.dark-mode .table-box {
    border-color: rgba(255,255,255,0.08);
}

/* ==================== RESPONSIVE MEDIA QUERIES ==================== */

@media (max-width: 1024px) {
    body {
        padding: 115px 16px 40px;
    }

    .cards {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 15px;
    }

    .content-box {
        padding: 24px;
    }
}

@media (max-width: 768px) {
    body {
        padding: 110px 12px 100px; /* Space below topbar and chatbot protection */
    }

    .cards {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 15px;
        margin-bottom: 20px;
    }

    .card {
        padding: 14px 10px;
        border-radius: 14px;
    }

    .card span {
        font-size: 22px;
        margin-bottom: 4px;
    }

    .card h3 {
        font-size: 10.5px;
    }

    .card h1 {
        font-size: 24px;
    }

    .content-box {
        padding: 16px 12px;
        border-radius: 18px;
    }

    .search-add-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        margin-bottom: 18px;
    }

    .search-box {
        width: 100%;
    }

    .add-btn {
        width: 100%;
        text-align: center;
        display: block;
        padding: 12px;
        font-size: 14px;
    }

    /* Transform Table into Responsive Grid Cards */
    .table-box {
        border: none;
        background: transparent;
        border-radius: 0;
        overflow: visible;
    }

    table,
    tbody,
    tr,
    td {
        display: block;
        width: 100%;
    }

    table {
        min-width: 0 !important;
    }

    thead {
        display: none;
    }

    tbody tr {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 18px;
        margin-bottom: 14px;
        padding: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    tbody tr:hover {
        transform: none;
    }

    body.dark-mode tbody tr {
        background: rgba(30, 41, 59, 0.9) !important;
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
    }

    td {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        padding: 0;
        border: none;
        font-size: 14px;
    }

    td::before {
        content: attr(data-label);
        font-weight: 800;
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    body.dark-mode td::before {
        color: #94a3b8;
    }

    /* Row 1: Code (Left) & Status (Right) */
    td:nth-child(1) {
        grid-column: 1 / 2;
        grid-row: 1;
        padding-bottom: 6px;
        border-bottom: 1.5px dashed var(--border);
    }
    td:nth-child(1)::before {
        display: none;
    }

    td:nth-child(6) {
        grid-column: 2 / 3;
        grid-row: 1;
        align-items: flex-end;
        padding-bottom: 6px;
        border-bottom: 1.5px dashed var(--border);
    }
    td:nth-child(6)::before {
        display: none;
    }

    /* Row 2: Item Name */
    td:nth-child(2) {
        grid-column: 1 / -1;
        grid-row: 2;
        font-size: 15px;
    }

    /* Row 3: Category (Left) & Unit Price (Right) */
    td:nth-child(3) {
        grid-column: 1 / 2;
        grid-row: 3;
    }

    td:nth-child(5) {
        grid-column: 2 / 3;
        grid-row: 3;
        align-items: flex-end;
        font-weight: 700;
    }

    /* Row 4: Quantity (Left) & Action Edit button (Right) */
    td:nth-child(4) {
        grid-column: 1 / 2;
        grid-row: 4;
        padding-top: 6px;
    }

    td:nth-child(7) {
        grid-column: 2 / 3;
        grid-row: 4;
        align-items: flex-end;
        justify-content: flex-end;
        padding-top: 6px;
    }
    td:nth-child(7)::before {
        display: none;
    }

    .qty-input {
        width: 70px;
        font-size: 14px;
        padding: 6px 8px;
    }

    .edit-btn {
        padding: 8px 18px;
        font-size: 13px;
    }
}
</style>
</head>

<body>

<div class="page-container">

    <div class="cards">
        <div class="card total" onclick="showAll()">
            <span></span>
            <h3>Total Items</h3>
            <h1><?= $totalItems ?></h1>
        </div>

        <div class="card in" onclick="filterIn()">
            <span></span>
            <h3>In Stock</h3>
            <h1><?= $inStock ?></h1>
        </div>

        <div class="card out" onclick="filterOut()">
            <span></span>
            <h3>Out Stock</h3>
            <h1><?= $outStock ?></h1>
        </div>

        <div class="card low" onclick="filterLow()">
            <span></span>
            <h3>Low Stock</h3>
            <h1><?= $lowStock ?></h1>
        </div>
    </div>

    <div class="content-box">
        <div class="search-add-bar">
            <input type="text" class="search-box" placeholder="🔍 Search item..." onkeyup="searchTable(this.value)">
            <a href="stock_register.php" class="add-btn">+ Add Item</a>
        </div>

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
                <?php 
                while($r = $stocks->fetch_assoc()) {
                    if ($r['quantity'] == 0) {
                        $st = "Out Stock";
                        $cl = "out-stock";
                    } elseif ($r['quantity'] <= 5) {
                        $st = "Low Stock";
                        $cl = "low-stock";
                    } else {
                        $st = "In Stock";
                        $cl = "in-stock";
                    }
                ?>
                    <tr>
                        <td data-label="Code"><strong><?= $r['item_code'] ?></strong></td>
                        <td data-label="Item Name"><strong><?= $r['item_name'] ?></strong></td>
                        <td data-label="Category"><?= $r['category_name'] ?></td>
                        <td data-label="Quantity">
                            <input type="number" class="qty-input" value="<?= $r['quantity'] ?>" disabled>
                        </td>
                        <td data-label="Unit Price">Rs.<?= number_format($r['unit_price'], 2) ?></td>
                        <td data-label="Status"><span class="status <?= $cl ?>"><?= $st ?></span></td>
                        <td data-label="Action">
                            <button class="edit-btn" onclick="toggleEdit(this)">Edit</button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pagination"></div>
    </div>
</div>

<script>
/* ===== TABLE PAGINATION ===== */
const rows = [...document.querySelectorAll("#tableBody tr")];
let rowsPerPage = 8;
let page = 1;
let currentRows = rows;

function showPage(p) {
    page = p;

    rows.forEach(r => r.style.display = "none");

    currentRows.forEach((r, i) => {
        if (i >= (p - 1) * rowsPerPage && i < p * rowsPerPage) {
            r.style.display = "";
        }
    });

    renderPagination();
}

function renderPagination() {
    const pages = Math.ceil(currentRows.length / rowsPerPage);
    const pagin = document.getElementById("pagination");
    pagin.innerHTML = "";

    for (let i = 1; i <= pages; i++) {
        const b = document.createElement("button");
        b.textContent = i;

        if (i === page) {
            b.classList.add("active");
        }

        b.onclick = () => showPage(i);
        pagin.appendChild(b);
    }
}

showPage(1);

/* ===== SEARCH ===== */
function searchTable(value) {
    value = value.toLowerCase();

    currentRows = rows.filter(r => 
        r.textContent.toLowerCase().includes(value)
    );

    showPage(1);
}

/* ===== FILTERS ===== */
function filterIn() {
    currentRows = rows.filter(r => {
        let q = parseInt(r.querySelector(".qty-input").value);
        return q > 5;
    });

    showPage(1);
}

function filterOut() {
    currentRows = rows.filter(r => {
        let q = parseInt(r.querySelector(".qty-input").value);
        return q === 0;
    });

    showPage(1);
}

function filterLow() {
    currentRows = rows.filter(r => {
        let q = parseInt(r.querySelector(".qty-input").value);
        return q > 0 && q <= 5;
    });

    showPage(1);
}

function showAll() {
    currentRows = rows;
    showPage(1);
}

/* ===== EDIT / SAVE ===== */
function toggleEdit(btn) {
    const tr = btn.closest("tr");
    const input = tr.querySelector(".qty-input");

    if (btn.innerText === "Edit") {
        input.disabled = false;
        input.focus();
        btn.innerText = "Save";
        btn.style.background = "#dcfce7";
        btn.style.color = "#166534";
    } else {
        input.disabled = true;

        fetch("stock_update.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `item_code=${encodeURIComponent(tr.children[0].innerText)}&quantity=${encodeURIComponent(input.value)}`
        }).then(() => location.reload());
    }
}
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>