<?php 
include 'db_config.php';
include_once 'navbar.php'; 

// --- Manual Income ඇතුළත් කිරීමේ කොටස ---
if (isset($_POST['add_manual_income'])) {
    $date = $_POST['date'];
    $amount = floatval($_POST['amount']);
    $acc_id = $_POST['acc_id'];
    $ref = $conn->real_escape_string($_POST['reference']);

    // අන්තිම Balance එක ලබාගැනීම
    $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
    $row = $res->fetch_assoc();
    $last_balance = ($row) ? floatval($row['balance']) : 0;
    $new_balance = $last_balance + $amount;

    // Cashbook එකට ඇතුළත් කිරීම (invoice_no එකට $ref එක යනවා)
    $sql = "INSERT INTO cashbook (date, invoice_no, income, balance, acc_id) 
            VALUES ('$date', '$ref', '$amount', '$new_balance', '$acc_id')";

    if ($conn->query($sql)) {
        // අදාළ Account එකේ balance එකත් update කිරීම
        $conn->query("UPDATE accounts SET balance = balance + $amount WHERE acc_id = '$acc_id'");
        echo "<script>alert('Income added successfully!'); window.location='cashbook_view.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// ගිණුම් ලැයිස්තුව ලබාගැනීම
$accounts_res = $conn->query("SELECT * FROM accounts");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cashbook & Accounts</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-section { background: #e8f5e9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c8e6c9; }
        .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; align-items: end; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #2e7d32; color: white; }
        .income-text { color: green; font-weight: bold; }
        .acc-badge { background: #1976d2; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        input, select, button { padding: 10px; border-radius: 5px; border: 1px solid #ccc; width: 100%; }
        .btn-add { background: #2e7d32; color: white; border: none; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Cashbook & Online Payments</h2>

    <div class="form-section">
        <h4 style="margin-top:0;">+ Add Manual / Online Income</h4>
        <form method="POST" class="grid-form">
            <div>
                <label>Date</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label>Select Account</label>
                <select name="acc_id" required>
                    <?php while($acc = $accounts_res->fetch_assoc()): ?>
                        <option value="<?= $acc['acc_id'] ?>"><?= $acc['acc_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Amount (Rs.)</label>
                <input type="number" name="amount" step="0.01" required>
            </div>
            <div>
                <label>Description / Ref</label>
                <input type="text" name="reference" placeholder="e.g. Online Pay">
            </div>
            <button type="submit" name="add_manual_income" class="btn-add">ADD INCOME</button>
        </form>
    </div>

    <h4>Transaction History (Cashbook)</h4>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Account</th>
                <th>Ref / Invoice</th>
                <th>Income (Rs.)</th>
                <th>Balance (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Join එකක් පාවිච්චි කරලා ගිණුමේ නම ගන්නවා
            $sql = "SELECT c.*, a.acc_name FROM cashbook c 
                    LEFT JOIN accounts a ON c.acc_id = a.acc_id 
                    ORDER BY c.cashid DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $ref_display = !empty($row['invoice_no']) ? $row['invoice_no'] : "N/A";
                    echo "<tr>
                            <td>#{$row['cashid']}</td>
                            <td>{$row['date']}</td>
                            <td><span class='acc-badge'>{$row['acc_name']}</span></td>
                            <td>{$ref_display}</td>
                            <td class='income-text'>+ ".number_format($row['income'], 2)."</td>
                            <td><strong>".number_format($row['balance'], 2)."</strong></td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>දත්ත කිසිවක් හමු නොවීය.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>