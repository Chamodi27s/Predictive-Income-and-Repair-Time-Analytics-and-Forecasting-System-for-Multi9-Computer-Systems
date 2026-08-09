<?php 
include 'db_config.php';

// --- Manual Entry 
if (isset($_POST['add_manual_transaction'])) {
    
    $date = $conn->real_escape_string($_POST['date']);
    $amount = floatval($_POST['amount']);
    $acc_id = $conn->real_escape_string($_POST['acc_id']);
    $ref = $conn->real_escape_string($_POST['reference']);

    
    $conn->begin_transaction();

    try {
        // 1.  Cashbook Balance 
        $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $row = $res->fetch_assoc();
        $last_balance = ($row) ? floatval($row['balance']) : 0;
        
        $new_balance = $last_balance + $amount;

        // 2. add Cashbook 
        $sql = "INSERT INTO cashbook (date, invoice_no, income, balance, acc_id) 
                VALUES ('$date', '$ref', '$amount', '$new_balance', '$acc_id')";
        $conn->query($sql);

        //3.update account balance
        $update_acc_sql = "UPDATE accounts SET balance = balance + $amount WHERE acc_id = '$acc_id'";
        $conn->query($update_acc_sql);

        
        $conn->commit();
        
        header("Location: cashbook_view.php?status=success");
        exit();

    } catch (Exception $e) {

        $conn->rollback();
        $error_msg = "Error: Something went wrong!";
    }
}

include_once 'navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashbook Management | Smart Finance</title>
    <link rel="stylesheet" href="CSS/global.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 0;
        }

        .page-header {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            padding: 36px 40px;
            border-radius: 20px;
            margin-top: 10px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.35);
            color: white;
            text-align: center;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .container {
            background: var(--light-surface);
            padding: 36px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-light);
            margin-bottom: 32px;
            animation: fadeIn 0.5s ease-out;
            transition: all 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section {
            background: var(--primary-green-light);
            padding: 28px;
            border-radius: 12px;
            margin-bottom: 32px;
            border: 2px solid rgba(4, 217, 146, 0.2);
        }

        .grid-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark); }

        .form-control {
            padding: 14px 16px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            transition: var(--transition);
            background: var(--light-bg);
            color: var(--text-dark);
        }

        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(4, 217, 146, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(4, 217, 146, 0.4);
        }

        .success-banner {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 18px 24px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-weight: 700;
            text-align: center;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-light);
        }

        .section-header h3 {
            font-size: 26px;
            font-weight: 800;
            border-left: 5px solid var(--primary-green);
            padding-left: 16px;
            color: var(--text-dark);
            display: flex; align-items: center; gap: 10px;
        }

        .search-input {
            padding: 12px 20px;
            border: 2px solid var(--border-light);
            border-radius: 12px;
            width: 280px;
            background: var(--light-bg);
            color: var(--text-dark);
            outline: none;
        }

        .table-container { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border-light); }
        table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1000px; }
        th {
            background: var(--light-bg);
            padding: 16px 18px;
            color: var(--text-muted);
            font-size: 13px;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-light);
        }

        tbody tr { background: var(--light-surface); }
        tbody tr:hover { background: var(--light-bg); transform: translateX(4px); transition: var(--transition); }
        td { padding: 16px 18px; border-bottom: 1px solid var(--border-light); color: var(--text-dark); }

        .amount-positive {
            color: #15803d;
            font-weight: 800;
            background: #dcfce7;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .balance-bold {
            font-weight: 800;
            background: var(--light-bg);
            padding: 8px 16px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-green);
        }

        .account-badge {
            background: rgba(4, 217, 146, 0.1);
            color: var(--primary-green-dark);
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
        }

        /* --- Dark Mode Fixing Styles --- */
        body.dark-mode .container {
            background: var(--dark-surface) !important;
            border-color: #334155;
        }

        body.dark-mode .form-section {
            background: rgba(4, 217, 146, 0.1);
            border-color: var(--primary-green-dark);
        }
        
        body.dark-mode .form-section h3 {
            color: var(--accent-green);
        }

        body.dark-mode .form-control, body.dark-mode .search-input {
            background: #0f172a !important;
            color: #f1f5f9 !important;
            border-color: #334155;
        }

        body.dark-mode .section-header h3 {
            color: #f1f5f9;
        }

        /* Hover fix in Dark Mode */
        body.dark-mode th {
            background: rgba(0,0,0,0.2);
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        body.dark-mode tbody tr { background: var(--dark-surface); }
        body.dark-mode tbody tr:hover {
            background: #1e293b !important;
            color: white !important;
        }

        body.dark-mode td { 
            border-bottom-color: #334155;
            color: #cbd5e1;
        }
        
        body.dark-mode td strong {
            color: white;
        }

        body.dark-mode .balance-bold { 
            background: #1e293b; 
            color: var(--accent-green); 
        }

        body.dark-mode .account-badge {
            background: rgba(4, 217, 146, 0.1);
            color: var(--accent-green);
        }

        /* ==================== RESPONSIVE MEDIA QUERIES ==================== */

        @media (max-width: 1024px) {
            .page-container {
                padding-top: 0;
                padding-bottom: 40px;
                width: 94%;
            }

            .page-header {
                margin-top: 6px;
                padding: 28px 24px;
                margin-bottom: 24px;
            }

            .page-header h1 {
                font-size: 26px;
            }

            .container {
                padding: 24px;
            }

            .grid-form {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .page-container {
                padding-top: 0;
                padding-bottom: 100px; /* Chatbot float space */
                width: 94%;
            }

            .page-header {
                margin-top: 4px;
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

            .form-section {
                padding: 18px 14px;
                border-radius: 16px;
                margin-bottom: 22px;
            }

            .form-section h3 {
                font-size: 18px;
                margin-bottom: 16px;
            }

            .grid-form {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .btn-primary {
                width: 100%;
                padding: 14px;
                font-size: 15px;
                border-radius: 12px;
            }

            .section-header {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
                margin-bottom: 20px;
                padding-bottom: 16px;
            }

            .section-header h3 {
                font-size: 20px;
                justify-content: center;
                border-left: none;
                padding-left: 0;
            }

            .search-box {
                width: 100%;
            }

            .search-input {
                width: 100%;
            }

            /* Transform Table into Responsive Cards */
            .table-container {
                border: none;
                background: transparent;
                border-radius: 0;
                overflow: visible;
            }

            table#transactionsTable,
            table#transactionsTable tbody,
            table#transactionsTable tr,
            table#transactionsTable td {
                display: block;
                width: 100%;
            }

            table#transactionsTable {
                min-width: 0 !important;
            }

            table#transactionsTable thead {
                display: none;
            }

            table#transactionsTable tbody tr {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px 10px;
                background: var(--light-surface);
                border: 1px solid var(--border-light);
                border-radius: 20px;
                margin-bottom: 16px;
                padding: 16px;
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
                position: relative;
            }

            body.dark-mode table#transactionsTable tbody tr {
                background: rgba(30, 41, 59, 0.9) !important;
                border-color: rgba(255, 255, 255, 0.1);
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
            }

            table#transactionsTable td {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
                padding: 0;
                border: none;
                font-size: 14px;
            }

            table#transactionsTable td::before {
                content: attr(data-label);
                font-weight: 800;
                font-size: 11px;
                color: var(--text-muted);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            body.dark-mode table#transactionsTable td::before {
                color: #94a3b8;
            }

            /* Row 1: Date (Left) & Account Badge (Right) */
            table#transactionsTable td:nth-child(1) {
                grid-column: 1 / 2;
                grid-row: 1;
                padding-bottom: 8px;
                border-bottom: 1.5px dashed var(--border-light);
            }
            table#transactionsTable td:nth-child(1)::before {
                display: none;
            }

            table#transactionsTable td:nth-child(2) {
                grid-column: 2 / 3;
                grid-row: 1;
                align-items: flex-end;
                padding-bottom: 8px;
                border-bottom: 1.5px dashed var(--border-light);
            }
            table#transactionsTable td:nth-child(2)::before {
                display: none;
            }

            /* Row 2: Reference */
            table#transactionsTable td:nth-child(3) {
                grid-column: 1 / -1;
                grid-row: 2;
            }

            /* Row 3: Income (Left) & Running Balance (Right) */
            table#transactionsTable td:nth-child(4) {
                grid-column: 1 / 2;
                grid-row: 3;
                background: var(--light-bg);
                padding: 10px;
                border-radius: 12px;
                border: 1px solid var(--border-light);
            }

            table#transactionsTable td:nth-child(5) {
                grid-column: 2 / 3;
                grid-row: 3;
                background: var(--light-bg);
                padding: 10px;
                border-radius: 12px;
                border: 1px solid var(--border-light);
            }

            body.dark-mode table#transactionsTable td:nth-child(4),
            body.dark-mode table#transactionsTable td:nth-child(5) {
                background: rgba(15, 23, 42, 0.6);
                border-color: rgba(255, 255, 255, 0.08);
            }

            .amount-positive,
            .balance-bold {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .page-container {
                padding-top: 95px;
                padding-bottom: 100px;
                width: 94%;
            }

            .container {
                padding: 12px 10px;
            }

            table#transactionsTable tbody tr {
                padding: 14px 12px;
            }
        }
    </style>
</head>
<body id="cashbookBody">

<div class="page-container">
    <div class="page-header">
        <h1><i class="ph-fill ph-book-open-text"></i> Cashbook Management</h1>
        <p>Track all bank transactions and financial records</p>
    </div>

    <div class="container">
        <div class="form-section">
            <h3><i class="ph-fill ph-plus-circle"></i> Add New Transaction</h3>
            
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div id="success-msg" class="success-banner">✓ Transaction Successfully Added!</div>
                <script>
                    setTimeout(() => {
                        document.getElementById('success-msg').style.display = 'none';
                        window.history.replaceState({}, document.title, "cashbook_view.php");
                    }, 3000);
                </script>
            <?php endif; ?>

            <form method="POST" class="grid-form">
                <div class="form-group">
                    <label> Date</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label> Account Name</label>
                    <select name="acc_id" id="acc_select" class="form-control" onchange="showAccNo()" required>
                        <option value="">-- Select Account --</option>
                        <?php 
                        $accounts_res = $conn->query("SELECT * FROM accounts ORDER BY acc_name ASC");
                        while($acc = $accounts_res->fetch_assoc()): ?>
                            <option value="<?= $acc['acc_id'] ?>" data-no="<?= $acc['account_no'] ?>"><?= $acc['acc_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label> Account Number</label>
                    <input type="text" id="display_acc_no" class="form-control" placeholder="Auto-filled" readonly>
                </div>
                
                <div class="form-group">
                    <label> Reference</label>
                    <input type="text" name="reference" class="form-control" placeholder="e.g. Deposit" required>
                </div>
                
                <div class="form-group">
                    <label> Amount (Rs.)</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add_manual_transaction" class="btn-primary"><i class="ph ph-paper-plane-right"></i> ADD TRANSACTION</button>
                </div>
            </form>
        </div>

        <div class="section-header">
            <h3><i class="ph-fill ph-clock-counter-clockwise"></i> Transaction History</h3>
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder=" Search records..." onkeyup="filterTable()">
            </div>
        </div>

        <div class="table-container">
            <table id="transactionsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Account</th>
                        <th>Reference</th>
                        <th>Income (Rs.)</th>
                        <th>Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT c.*, a.acc_name FROM cashbook c 
                            LEFT JOIN accounts a ON c.acc_id = a.acc_id 
                            ORDER BY c.cashid DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $accName = $row['acc_name'] ? $row['acc_name'] : "cash";
                            echo "<tr>
                                    <td data-label='Date'><strong>{$row['date']}</strong></td>
                                    <td data-label='Account'><span class='account-badge'>{$accName}</span></td>
                                    <td data-label='Reference'>{$row['invoice_no']}</td>
                                    <td data-label='Income'><span class='amount-positive'>+ " . number_format($row['income'], 2) . "</span></td>
                                    <td data-label='Running Balance'><span class='balance-bold'>" . number_format($row['balance'], 2) . "</span></td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='empty-state' style='text-align:center; padding: 50px;'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// --- Theme Handling & Auto Refresh ---
function applySavedTheme() {
    const body = document.getElementById('cashbookBody');
    const isDark = localStorage.getItem("darkMode") === "enabled";
    if (isDark) {
        body.classList.add("dark-mode");
    } else {
        body.classList.remove("dark-mode");
    }
}

applySavedTheme();

// Listen for storage changes to sync theme across tabs
window.addEventListener('storage', (e) => {
    if (e.key === 'darkMode') {
        applySavedTheme();
    }
});

// Periodic check for local theme changes
let lastThemeState = localStorage.getItem("darkMode");
setInterval(() => {
    let currentThemeState = localStorage.getItem("darkMode");
    if (currentThemeState !== lastThemeState) {
        lastThemeState = currentThemeState;
        applySavedTheme();
    }
}, 500);

// Account No Display Logic
function showAccNo() {
    var select = document.getElementById("acc_select");
    var accNoInput = document.getElementById("display_acc_no");
    var selectedOption = select.options[select.selectedIndex];
    accNoInput.value = selectedOption.getAttribute("data-no") || "";
}

// Global Search Logic
function filterTable() {
    const filter = document.getElementById("searchInput").value.toUpperCase();
    const rows = document.querySelector("#transactionsTable tbody").rows;
    for (let i = 0; i < rows.length; i++) {
        const text = rows[i].textContent.toUpperCase();
        rows[i].style.display = text.includes(filter) ? "" : "none";
    }
}
</script>
<?php include_once __DIR__ . '/chatbot.php'; ?>

</body>

</html>