<?php 
include 'db_config.php';

// --- Manual Entry සේව් කිරීමේ කොටස ---
if (isset($_POST['add_manual_transaction'])) {
    $date = $_POST['date'];
    $amount = floatval($_POST['amount']);
    $acc_id = $_POST['acc_id'];
    $ref = $conn->real_escape_string($_POST['reference']);

    // අන්තිම Balance එක ලබාගැනීම
    $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
    $row = $res->fetch_assoc();
    $last_balance = ($row) ? floatval($row['balance']) : 0;
    
    // සියල්ල Income ලෙස සලකා balance එකට එකතු කරයි
    $new_balance = $last_balance + $amount;

    // Cashbook එකට ඇතුළත් කිරීම
    $sql = "INSERT INTO cashbook (date, invoice_no, income, balance, acc_id) 
            VALUES ('$date', '$ref', '$amount', '$new_balance', '$acc_id')";

    if ($conn->query($sql)) {
        // Account balance එක update කිරීම
        $conn->query("UPDATE accounts SET balance = balance + $amount WHERE acc_id = '$acc_id'");
        
        // සාර්ථක පණිවිඩය පෙන්වීමට status එක URL එකට යවනවා
        header("Location: cashbook_view.php?status=success");
        exit();
    } else {
        $error_msg = "Error: " . $conn->error;
    }
}

include_once 'navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashbook Management | Smart Finance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2ecc71;                 /* Collected page eke එකම primary color */
            --primary-hover: #27ae60;           /* එකම hover color */
            --primary-dark: #229954;            /* එකම dark color */
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --secondary: #64748b;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1a202c;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%); /* Collected page eke එකම background */
            padding: 140px 20px 40px 20px;
            color: var(--text-main);
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header Card - Collected page eke එකම style */
        .page-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); /* Collected page eke එකම gradient */
            padding: 36px 40px;
            border-radius: 20px; /* Collected page eke එකම radius */
            margin-bottom: 32px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4); /* Collected page eke එකම shadow */
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

        .page-header p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 500;
        }

        /* Container - Collected page eke එකම style */
        .container {
            background: var(--card-bg);
            padding: 36px;
            border-radius: 20px; /* Collected page eke එකම radius */
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            margin-bottom: 32px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Section - Green theme */
        .form-section {
            background: linear-gradient(135deg, #e8f5e9 0%, #d4edda 100%);
            padding: 28px;
            border-radius: 12px; /* Collected page eke similar radius */
            margin-bottom: 32px;
            border: 2px solid #c8e6c9;
        }

        .form-section h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Grid Form */
        .grid-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-control {
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 12px; /* Collected page eke එකම radius */
            font-size: 15px;
            font-weight: 500;
            outline: none;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15); /* Collected page eke එකම focus effect */
        }

        .form-control:read-only {
            background: #f8fafc;
            color: var(--text-muted);
            cursor: not-allowed;
        }

        /* Buttons - Collected page eke එකම button style */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 12px; /* Collected page eke එකම radius */
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3); /* Collected page eke එකම shadow */
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: fit-content;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-dark) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(46, 204, 113, 0.4); /* Collected page eke එකම hover shadow */
        }

        /* Success Message */
        .success-banner {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 18px 24px;
            border-radius: 12px;
            margin-bottom: 28px;
            border: 1px solid #c3e6cb;
            font-weight: 700;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Section Header - Collected page eke header section style */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border); /* Collected page eke එකම border */
            flex-wrap: wrap;
            gap: 20px;
        }

        .section-header h3 {
            font-size: 26px; /* Collected page eke එකම font size */
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 5px solid var(--primary); /* Collected page eke එකම border-left */
            padding-left: 16px;
        }

        /* Search Box - Collected page eke එකම style */
        .search-box {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-input {
            padding: 12px 20px;
            border: 2px solid var(--border);
            border-radius: 12px; /* Collected page eke එකම radius */
            width: 280px;
            outline: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8fafc; /* Collected page eke එකම background */
        }

        .search-input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15); /* Collected page eke එකම focus effect */
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px; /* Collected page eke එකම radius */
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-1px);
        }

        /* Table Container - Collected page eke එකම style */
        .table-container {
            overflow-x: auto;
            border-radius: 12px; /* Collected page eke එකම radius */
            border: 1px solid var(--border);
        }

        /* Table Styling - Collected page eke එකම table style */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); /* Collected page eke එකම gradient */
            text-align: left;
            padding: 16px 18px; /* Collected page eke එකම padding */
            font-size: 13px;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        th:first-child {
            border-top-left-radius: 12px; /* Collected page eke එකම radius */
        }

        th:last-child {
            border-top-right-radius: 12px; /* Collected page eke එකම radius */
        }

        tbody tr {
            transition: all 0.3s ease;
            background: white;
        }

        tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%); /* Collected page eke එකම hover effect */
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        td {
            padding: 16px 18px; /* Collected page eke එකම padding */
            font-size: 14px;
            border-bottom: 1px solid #f0f2f5;
            color: var(--text-main);
        }

        /* Amount Styling - Green theme */
        .amount-positive {
            color: #155724; /* Dark green color */
            font-weight: 800;
            background: #d4edda;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        .balance-bold {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 15px; /* Collected page eke එකම font size */
            background: #f8fafc;
            padding: 8px 16px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        /* Account Badge - Collected page eke job-badge style */
        .account-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(25, 118, 210, 0.15);
        }

        /* Empty State - Collected page eke එකම style */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Responsive Design - Collected page eke එකම responsive */
        @media (max-width: 768px) {
            body {
                padding: 120px 15px 30px 15px;
            }

            .page-header {
                padding: 24px 28px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .container {
                padding: 24px;
            }

            .section-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                flex-direction: column;
            }

            .search-input {
                width: 100%;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="page-header">
        <h1>💰 Cashbook Management</h1>
        <p>Track all bank transactions and financial records</p>
    </div>

    <div class="container">
        <div class="form-section">
            <h3>➕ Add New Transaction</h3>
            
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div id="success-msg" class="success-banner">
                    ✓ Transaction Successfully Added!
                </div>
                <script>
                    setTimeout(function() {
                        var msg = document.getElementById('success-msg');
                        if(msg) msg.style.display = 'none';
                        window.history.replaceState({}, document.title, "cashbook_view.php");
                    }, 3000);
                </script>
            <?php endif; ?>

            <form method="POST" class="grid-form">
                <div class="form-group">
                    <label>📅 Date</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>🏦 Account Name</label>
                    <select name="acc_id" id="acc_select" class="form-control" onchange="showAccNo()" required>
                        <option value="">-- Select Account --</option>
                        <?php 
                        $accounts_res = $conn->query("SELECT * FROM accounts");
                        while($acc = $accounts_res->fetch_assoc()): ?>
                            <option value="<?= $acc['acc_id'] ?>" data-no="<?= $acc['account_no'] ?>">
                                <?= $acc['acc_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>🔢 Account Number</label>
                    <input type="text" id="display_acc_no" class="form-control" placeholder="Auto-filled" readonly>
                </div>
                
                <div class="form-group">
                    <label>📝 Reference</label>
                    <input type="text" name="reference" class="form-control" placeholder="e.g. Online Transfer, Cash Deposit" required>
                </div>
                
                <div class="form-group">
                    <label>💵 Amount (Rs.)</label>
                    <input type="number" name="amount" class="form-control" step="0.01" required placeholder="0.00" min="0">
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" name="add_manual_transaction" class="btn-primary">
                        💾 ADD TRANSACTION
                    </button>
                </div>
            </form>
        </div>

        <div class="section-header">
            <h3>📊 Transaction History</h3>
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search by Account or Reference..." onkeyup="filterTable()">
                <button class="btn-secondary" onclick="clearSearch()">✕ Clear</button>
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
                            echo "<tr>
                                    <td><strong>{$row['date']}</strong></td>
                                    <td><span class='account-badge'>{$row['acc_name']}</span></td>
                                    <td>" . (!empty($row['invoice_no']) ? $row['invoice_no'] : 'Manual Entry') . "</td>
                                    <td><span class='amount-positive'>+ " . number_format($row['income'], 2) . "</span></td>
                                    <td><span class='balance-bold'>" . number_format($row['balance'], 2) . "</span></td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='empty-state'>
                                <div class='empty-state-icon'>💰</div>
                                <strong>No transactions found.</strong><br>
                                <small>Add your first transaction using the form above.</small>
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Account එක තේරූ විට Account Number එක පෙන්වීමට
function showAccNo() {
    var select = document.getElementById("acc_select");
    var accNoInput = document.getElementById("display_acc_no");
    var selectedOption = select.options[select.selectedIndex];
    
    var accNo = selectedOption.getAttribute("data-no");
    accNoInput.value = (accNo && accNo !== 'NULL') ? accNo : "";
}

// Search Functionality
function filterTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toUpperCase();
    const table = document.getElementById("transactionsTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let showRow = false;
        const accountCol = tr[i].getElementsByTagName("td")[1];
        const refCol = tr[i].getElementsByTagName("td")[2];

        if (accountCol || refCol) {
            const accountText = accountCol.textContent || accountCol.innerText;
            const refText = refCol.textContent || refCol.innerText;

            if (accountText.toUpperCase().indexOf(filter) > -1 || 
                refText.toUpperCase().indexOf(filter) > -1) {
                showRow = true;
            }
        }
        tr[i].style.display = showRow ? "" : "none";
    }
}

// Clear Search
function clearSearch() {
    document.getElementById("searchInput").value = "";
    filterTable();
}
</script>

</body>
</html>