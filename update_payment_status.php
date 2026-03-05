<?php
include 'db_config.php';

if (isset($_POST['payment_id']) && isset($_POST['acc_id'])) {
    $payment_id = $conn->real_escape_string($_POST['payment_id']);
    $acc_id = $conn->real_escape_string($_POST['acc_id']);
    $amount = floatval($_POST['amount']);
    $ref_no = $conn->real_escape_string($_POST['ref_no']); // Invoice No or Loan ID
    $date = date('Y-m-d');

    // Database වැඩ ටික ආරම්භ කිරීම
    $conn->begin_transaction();

    try {
        // 1. Payment එකේ status එක 'Paid' ලෙස update කිරීම
        $update_pay_sql = "UPDATE payments SET status = 'Paid', paid_date = '$date' WHERE pay_id = '$payment_id'";
        $conn->query($update_pay_sql);

        // 2. අන්තිම Cashbook balance එක ලබා ගැනීම
        $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $row = $res->fetch_assoc();
        $last_balance = ($row) ? floatval($row['balance']) : 0;
        $new_balance = $last_balance + $amount;

        // 3. Cashbook එකට record එක ඇතුළත් කිරීම
        $insert_cash_sql = "INSERT INTO cashbook (date, invoice_no, income, balance, acc_id) 
                            VALUES ('$date', 'PAY-$ref_no', '$amount', '$new_balance', '$acc_id')";
        $conn->query($insert_cash_sql);

        // 4. තෝරාගත් බැංකු ගිණුමේ (Account) balance එක update කිරීම
        $update_acc_sql = "UPDATE accounts SET balance = balance + $amount WHERE acc_id = '$acc_id'";
        $conn->query($update_acc_sql);

        // සියල්ල සාර්ථක නම් Commit කරන්න
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Payment processed successfully!']);

    } catch (Exception $e) {
        // වැරැද්දක් වුනොත් ඔක්කොම cancel කරන්න
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Transaction failed!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request!']);
}
?>