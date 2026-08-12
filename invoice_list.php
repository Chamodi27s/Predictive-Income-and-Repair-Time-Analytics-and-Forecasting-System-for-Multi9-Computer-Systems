<?php
include 'db_config.php';
include 'navbar.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$query = "SELECT i.*, j.phone_number, jd.device_name, jd.device_status, c.customer_name 
          FROM invoice i 
          JOIN job j ON i.job_no = j.job_no 
          JOIN job_device jd ON i.job_no = jd.job_no
          JOIN customer c ON j.phone_number = c.phone_number";

if ($search != '') {
    $query .= " WHERE i.invoice_no LIKE '%$search%' 
                OR i.job_no LIKE '%$search%' 
                OR j.phone_number LIKE '%$search%' 
                OR c.customer_name LIKE '%$search%'";
}

$query .= " ORDER BY i.invoice_no DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice List | Multi9</title>
<link rel="stylesheet" href="CSS/global.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root {
    --primary: #2ecc71;
    --primary-hover: #27ae60;
    --success: #22c55e;
    --warning: #f59e0b;
    --blue: #3b82f6;
    --blue-dark: #1d4ed8;
    --orange-soft: #ffedd5;
    --orange-dark: #c2410c;
    --red-soft: #fee2e2;
    --red-dark: #991b1b;
    --bg-main: #f8fafc;
    --card-bg: #ffffff;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.10);
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
    padding: 140px 20px 40px;
    color: var(--text-main);
}

.page-container {
    max-width: 1500px;
    margin: auto;
}

.page-header {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    padding: 36px 40px;
    border-radius: 20px;
    margin-top: 15px;
    margin-bottom: 32px;
    box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
    color: white;
    text-align: center;
}

.page-header h1 {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 8px;
}

.page-header p {
    font-size: 15px;
    opacity: 0.95;
}

.container {
    background: var(--card-bg);
    padding: 34px;
    border-radius: 22px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.section-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-main);
}

.search-box {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-box input {
    width: 300px;
    padding: 12px 16px;
    border-radius: 12px;
    border: 2px solid var(--border);
    outline: none;
    font-weight: 600;
    background: #f8fafc;
    color: #334155;
}

.search-box input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(46,204,113,0.15);
}

.search-btn {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 800;
    transition: 0.3s;
}

.search-btn:hover {
    transform: translateY(-2px);
}

.table-container {
    overflow: visible;
    border-radius: 16px;
    border: 1px solid var(--border);
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th {
    background: #f8fafc;
    padding: 10px 10px;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 0.5px;
    text-align: left;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
    font-size: 13px;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

tbody tr {
}

tbody tr:hover {
    background: #f8fafc;
}

.customer-name {
    font-weight: 800;
    color: #1e293b;
}

.customer-phone {
    color: var(--text-muted);
    font-size: 12px;
}

.inv-badge {
    font-weight: 800;
    font-size: 13px;
    color: #0f172a;
    display: inline-block;
}

.subtotal-badge {
    font-weight: 800;
    font-size: 13px;
    color: #0f172a;
    display: inline-block;
}

.late-badge {
    font-weight: 800;
    font-size: 13px;
    color: #c2410c;
    display: inline-block;
}

.final-badge {
    font-weight: 900;
    font-size: 13px;
    color: #166534;
    display: inline-block;
}

.status {
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    display: inline-block;
}

.status-paid {
    background: #dcfce7;
    color: #166534;
}

.status-pending {
    background: #fee2e2;
    color: #991b1b;
}

.action-btn {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    padding: 8px 12px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    box-shadow: 0 4px 12px rgba(34,197,94,0.3);
}

.action-btn:hover {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.empty-state {
    text-align: center;
    padding: 50px;
    color: #94a3b8;
    font-weight: 700;
}

/* dark mode */
body.dark-mode {
    background: linear-gradient(135deg, #020617, #0f172a) !important;
    color: #e2e8f0 !important;
}

body.dark-mode .container {
    background: rgba(30,41,59,0.75) !important;
    border-color: rgba(255,255,255,0.08);
}

body.dark-mode .section-title,
body.dark-mode .customer-name {
    color: #f8fafc;
}

body.dark-mode td {
    color: #cbd5e1;
    border-bottom-color: rgba(255,255,255,0.06);
}

body.dark-mode tbody tr:hover {
    background: rgba(255,255,255,0.05);
}

body.dark-mode .search-box input {
    background: rgba(15,23,42,0.8);
    color: white;
    border-color: rgba(255,255,255,0.10);
}

body.dark-mode .subtotal-badge {
    background: rgba(148,163,184,0.15);
    color: #cbd5e1;
}

body.dark-mode .inv-badge {
    color: #e2e8f0;
}

body.dark-mode .late-badge {
    color: #fdba74;
}

body.dark-mode .final-badge {
    color: #86efac;
}

/* ==================== RESPONSIVE MEDIA QUERIES ==================== */

@media(max-width: 1024px) {
    body {
        padding: 115px 16px 40px;
    }

    .page-header {
        margin-top: 15px;
        padding: 28px 24px;
        margin-bottom: 24px;
    }

    .container {
        padding: 24px;
    }
}

@media(max-width: 768px) {
    body {
        padding: 110px 12px 100px; /* Floating chatbot protection */
    }

    .page-header {
        margin-top: 15px;
        padding: 22px 16px;
        border-radius: 18px;
        margin-bottom: 18px;
    }

    .page-header h1 {
        font-size: 22px;
    }

    .page-header p {
        font-size: 13px;
    }

    .container {
        padding: 16px 12px;
        border-radius: 18px;
    }

    .top-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 20px;
        text-align: center;
    }

    .search-box {
        width: 100%;
        flex-direction: column;
        gap: 10px;
    }

    .search-box input,
    .search-btn {
        width: 100%;
    }

    /* Transform Table into Responsive Cards */
    .table-container {
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
        gap: 12px 10px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 20px;
        margin-bottom: 16px;
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
        text-align: left !important;
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

    /* Row 1: Inv No (Left) & Status (Right) */
    td:nth-child(1) {
        grid-column: 1 / 2;
        grid-row: 1;
        padding-bottom: 6px;
        border-bottom: 1.5px dashed var(--border);
    }
    td:nth-child(1)::before {
        display: none;
    }

    td:nth-child(8) {
        grid-column: 2 / 3;
        grid-row: 1;
        align-items: flex-end;
        padding-bottom: 6px;
        border-bottom: 1.5px dashed var(--border);
    }
    td:nth-child(8)::before {
        display: none;
    }

    /* Row 2: Customer Details */
    td:nth-child(3) {
        grid-column: 1 / -1;
        grid-row: 2;
    }

    /* Row 3: Device (Left) & Job No (Right) */
    td:nth-child(4) {
        grid-column: 1 / 2;
        grid-row: 3;
    }

    td:nth-child(2) {
        grid-column: 2 / 3;
        grid-row: 3;
        align-items: flex-end;
    }

    /* Row 4: Subtotal (Left) & Late Rent (Right) */
    td:nth-child(5) {
        grid-column: 1 / 2;
        grid-row: 4;
    }

    td:nth-child(6) {
        grid-column: 2 / 3;
        grid-row: 4;
        align-items: flex-end;
    }

    /* Row 5: Final Amount (Left) & View Bill Button (Right) */
    td:nth-child(7) {
        grid-column: 1 / 2;
        grid-row: 5;
        padding-top: 6px;
        border-top: 1.5px dashed var(--border);
    }

    td:nth-child(9) {
        grid-column: 2 / 3;
        grid-row: 5;
        align-items: flex-end;
        justify-content: flex-end;
        padding-top: 6px;
        border-top: 1.5px dashed var(--border);
    }
    td:nth-child(9)::before {
        display: none;
    }

    .action-btn {
        width: 100%;
        text-align: center;
        padding: 10px 14px;
    }
}
</style>
</head>

<body>

<div class="page-container">

    <div class="page-header">
        <h1> Invoice Management</h1>
        <p>View invoices, payment status, late rent and final bill details</p>
    </div>

    <div class="container">
        <div class="top-bar">
            <h2 class="section-title">Invoice List</h2>

            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search invoice, job, customer..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-btn"> Search</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width:8%">   <!-- Inv No -->
                    <col style="width:10%">  <!-- Job No -->
                    <col style="width:20%">  <!-- Customer Details -->
                    <col style="width:12%">  <!-- Device -->
                    <col style="width:10%">  <!-- Subtotal -->
                    <col style="width:10%">  <!-- Late Rent -->
                    <col style="width:12%">  <!-- Final Amount -->
                    <col style="width:9%">   <!-- Status -->
                    <col style="width:9%">   <!-- Actions -->
                </colgroup>
                <thead>
                    <tr>
                        <th>Inv No</th>
                        <th>Job No</th>
                        <th>Customer Details</th>
                        <th>Device</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th style="text-align:right;">Late Rent</th>
                        <th style="text-align:right;">Final Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $late_fee = floatval($row['late_fee'] ?? 0);
                        $grand_total = floatval($row['grand_total']);
                    ?>
                    <tr>
                        <td data-label="Inv No"><span class="inv-badge">#<?= $row['invoice_no'] ?></span></td>
                        <td data-label="Job No"><strong>#<?= $row['job_no'] ?></strong></td>

                        <td data-label="Customer Details">
                            <span class="customer-name"><?= $row['customer_name'] ?></span><br>
                            <span class="customer-phone"><?= $row['phone_number'] ?></span>
                        </td>

                        <td data-label="Device"><?= $row['device_name'] ?></td>

                        <td data-label="Subtotal" style="text-align:right;">
                            <span class="subtotal-badge">Rs. <?= number_format($grand_total - $late_fee, 2) ?></span>
                        </td>

                        <td data-label="Late Rent" style="text-align:right;">
                            <?php if ($late_fee > 0): ?>
                                <span class="late-badge">Rs. <?= number_format($late_fee, 2) ?></span>
                            <?php else: ?>
                                <span class="subtotal-badge">-</span>
                            <?php endif; ?>
                        </td>

                        <td data-label="Final Amount" style="text-align:right;">
                            <span class="final-badge">Rs. <?= number_format($grand_total, 2) ?></span>
                        </td>

                        <td data-label="Status">
                            <span class="status <?= ($row['payment_status'] == 'Paid') ? 'status-paid' : 'status-pending' ?>">
                                <?= $row['payment_status'] ?>
                            </span>
                        </td>

                        <td data-label="Actions">
                            <a href="generate_bill.php?job_no=<?= $row['job_no'] ?>&view_only=true" class="action-btn"> View Bill</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-state">No invoices found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function syncTheme() {
    const body = document.body;
    const isDark = localStorage.getItem("darkMode") === "enabled";

    if (isDark) {
        body.classList.add("dark-mode");
    } else {
        body.classList.remove("dark-mode");
    }
}

syncTheme();
setInterval(syncTheme, 1000);
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>
</body>
</html>