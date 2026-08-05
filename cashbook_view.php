<?php 
include 'db_config.php';

// --- Manual Entry සේව් කිරීමේ කොටස ---
if (isset($_POST['add_manual_transaction'])) {
    // Inputs ආරක්ෂිතව ලබාගැනීම
    $date = $conn->real_escape_string($_POST['date']);
    $amount = floatval($_POST['amount']);
    $acc_id = $conn->real_escape_string($_POST['acc_id']);
    $ref = $conn->real_escape_string($_POST['reference']);

    // Database එකේ වැඩ දෙකක් එකවර සිදුවන බැවින් Transaction එකක් ආරම්භ කිරීම
    $conn->begin_transaction();

    try {
        // 1. අන්තිම Cashbook Balance එක ලබාගැනීම
        $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $row = $res->fetch_assoc();
        $last_balance = ($row) ? floatval($row['balance']) : 0;
        
        $new_balance = $last_balance + $amount;

        // 2. Cashbook එකට ඇතුළත් කිරීම
        $sql = "INSERT INTO cashbook (date, invoice_no, income, balance, acc_id) 
                VALUES ('$date', '$ref', '$amount', '$new_balance', '$acc_id')";
        $conn->query($sql);

        // 3. අදාළ Account එකේ balance එක update කිරීම
        $update_acc_sql = "UPDATE accounts SET balance = balance + $amount WHERE acc_id = '$acc_id'";
        $conn->query($update_acc_sql);

        // සියල්ල සාර්ථක නම් පමණක් Database එක ස්ථිරවම Update කරන්න
        $conn->commit();
        
        header("Location: cashbook_view.php?status=success");
        exit();

    } catch (Exception $e) {
        // කිසියම් දෝෂයක් ආවොත් කළ වෙනස්කම් සියල්ල අවලංගු කරන්න
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
            margin-top: 25px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            padding: 36px 40px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(15, 118, 110, 0.4);
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
            border: 2px solid rgba(20, 184, 166, 0.2);
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
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.3);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(20, 184, 166, 0.4);
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
            background: rgba(20, 184, 166, 0.1);
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
            background: rgba(20, 184, 166, 0.1);
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
            background: rgba(20, 184, 166, 0.1);
            color: var(--accent-green);
        }

        @media (max-width: 768px) {
            body { padding-top: 100px; }
            .grid-form { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; gap: 15px; }
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
                                    <td><strong>{$row['date']}</strong></td>
                                    <td><span class='account-badge'>{$accName}</span></td>
                                    <td>{$row['invoice_no']}</td>
                                    <td><span class='amount-positive'>+ " . number_format($row['income'], 2) . "</span></td>
                                    <td><span class='balance-bold'>" . number_format($row['balance'], 2) . "</span></td>
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

</body>

</html>