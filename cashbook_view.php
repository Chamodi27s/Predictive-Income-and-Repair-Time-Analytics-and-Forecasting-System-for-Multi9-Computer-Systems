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
    <title>Cashbook Management</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding-top: 120px;   /* 🔥 navbar height */
    padding-left: 40px;
    padding-right: 40px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-section { background: #e8f5e9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c8e6c9; }
        .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; align-items: end; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #2e7d32; color: white; }
        
        input, select, button { padding: 10px; border-radius: 5px; border: 1px solid #ccc; width: 100%; outline: none; }
        .btn-add { background: #2e7d32; color: white; border: none; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { background: #1b5e20; }

        /* Success Message Styling */
        .success-banner {
            background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; 
            margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: bold; text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Bank Transactions</h2>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div id="success-msg" class="success-banner">
            ✓ Transaction Successfully Added!
        </div>
        <script>
            // තත්පර 3කට පසු පණිවිඩය මැකී යාමට
            setTimeout(function() {
                var msg = document.getElementById('success-msg');
                if(msg) msg.style.display = 'none';
                // URL එක පිරිසිදු කිරීමට (success=1 ඉවත් කිරීමට)
                window.history.replaceState({}, document.title, "cashbook_view.php");
            }, 3000);
        </script>
    <?php endif; ?>

    <div class="form-section">
        <form method="POST" class="grid-form">
            <div>
                <label>Date</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label>Account Name</label>
                <select name="acc_id" id="acc_select" onchange="showAccNo()" required>
                    <option value="">-- Select --</option>
                    <?php 
                    $accounts_res = $conn->query("SELECT * FROM accounts");
                    while($acc = $accounts_res->fetch_assoc()): ?>
                        <option value="<?= $acc['acc_id'] ?>" data-no="<?= $acc['account_no'] ?>">
                            <?= $acc['acc_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Account Number</label>
                <input type="text" id="display_acc_no" placeholder="Auto-filled" readonly style="background: #f1f1f1; color: #555;">
            </div>
            <div>
                <label>Reference (Manual)</label>
                <input type="text" name="reference" placeholder="e.g. Online Transfer" required>
            </div>
            <div>
                <label>Amount (Rs.)</label>
                <input type="number" name="amount" step="0.01" required placeholder="0.00">
            </div>
            <button type="submit" name="add_manual_transaction" class="btn-add">ADD TRANSACTION</button>
        </form>
    </div>

    <h4>History</h4>
    <table>
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

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['date']}</td>
                            <td>{$row['acc_name']}</td>
                            <td>" . (!empty($row['invoice_no']) ? $row['invoice_no'] : 'N/A') . "</td>
                            <td style='color:green; font-weight:bold;'>+ " . number_format($row['income'], 2) . "</td>
                            <td><strong>" . number_format($row['balance'], 2) . "</strong></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No transactions found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
// Account එක තේරූ විට Account Number එක පෙන්වීමට
function showAccNo() {
    var select = document.getElementById("acc_select");
    var accNoInput = document.getElementById("display_acc_no");
    var selectedOption = select.options[select.selectedIndex];
    
    // data-no attribute එකෙන් අංකය ලබාගනී
    var accNo = selectedOption.getAttribute("data-no");
    
    // NULL හෝ හිස් නම් පෙන්වන්නේ නැත
    accNoInput.value = (accNo && accNo !== 'NULL') ? accNo : "";
}
</script>

</body>
</html>