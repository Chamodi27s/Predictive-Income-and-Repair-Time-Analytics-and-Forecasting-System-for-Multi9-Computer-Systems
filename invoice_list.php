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
<title>Invoice List | Multi9</title>
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
    max-width: 1250px;
    margin: auto;
}

.page-header {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    padding: 36px 40px;
    border-radius: 22px;
    margin-bottom: 32px;
    box-shadow: 0 12px 30px rgba(46,204,113,0.35);
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
    overflow-x: auto;
    border-radius: 16px;
    border: 1px solid var(--border);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1100px;
}

th {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    padding: 16px 18px;
    color: white;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 13px;
    text-align: left;
}

td {
    padding: 16px 18px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 14px;
    vertical-align: middle;
}

tbody tr {
    transition: 0.25s;
}

tbody tr:hover {
    background: #f8fafc;
    transform: translateX(4px);
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
    background: #e0f2fe;
    color: #075985;
    padding: 7px 13px;
    border-radius: 20px;
    font-weight: 800;
    display: inline-block;
}

.subtotal-badge {
    background: #f1f5f9;
    color: #334155;
    padding: 7px 13px;
    border-radius: 20px;
    font-weight: 800;
    display: inline-block;
}

.late-badge {
    background: linear-gradient(135deg, #ffedd5, #fed7aa);
    color: #c2410c;
    padding: 7px 13px;
    border-radius: 20px;
    font-weight: 800;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(249,115,22,0.12);
}

.final-badge {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #166534;
    padding: 7px 13px;
    border-radius: 20px;
    font-weight: 900;
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
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 11px 16px;
    border-radius: 13px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 5px 12px rgba(59,130,246,0.28);
}

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(59,130,246,0.38);
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
    background: rgba(59,130,246,0.18);
    color: #93c5fd;
}

body.dark-mode .late-badge {
    background: rgba(249,115,22,0.18);
    color: #fdba74;
}

body.dark-mode .final-badge {
    background: rgba(34,197,94,0.18);
    color: #86efac;
}

@media(max-width: 768px) {
    body {
        padding: 110px 15px 30px;
    }

    .page-header {
        padding: 28px 20px;
    }

    .page-header h1 {
        font-size: 25px;
    }

    .container {
        padding: 20px;
    }

    .search-box input,
    .search-btn {
        width: 100%;
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
                <button type="submit" class="search-btn">🔍 Search</button>
            </form>
        </div>

        <div class="table-container">
            <table>
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
                        <td><span class="inv-badge">#<?= $row['invoice_no'] ?></span></td>
                        <td><strong><?= $row['job_no'] ?></strong></td>

                        <td>
                            <span class="customer-name"><?= $row['customer_name'] ?></span><br>
                            <span class="customer-phone"><?= $row['phone_number'] ?></span>
                        </td>

                        <td><?= $row['device_name'] ?></td>

                        <td style="text-align:right;">
                            <span class="subtotal-badge">Rs. <?= number_format($grand_total - $late_fee, 2) ?></span>
                        </td>

                        <td style="text-align:right;">
                            <?php if ($late_fee > 0): ?>
                                <span class="late-badge">Rs. <?= number_format($late_fee, 2) ?></span>
                            <?php else: ?>
                                <span class="subtotal-badge">-</span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align:right;">
                            <span class="final-badge">Rs. <?= number_format($grand_total, 2) ?></span>
                        </td>

                        <td>
                            <span class="status <?= ($row['payment_status'] == 'Paid') ? 'status-paid' : 'status-pending' ?>">
                                <?= $row['payment_status'] ?>
                            </span>
                        </td>

                        <td>
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