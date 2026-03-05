<?php
include 'db_config.php';

// POST එක හරහා දත්ත ලැබෙනවාදැයි පරීක්ෂා කිරීම
if (isset($_POST['invoice_no']) && isset($_POST['status'])) {
    $inv_no = $conn->real_escape_string($_POST['invoice_no']);
    $status = $conn->real_escape_string($_POST['status']); // AJAX එකෙන් එන status එක (Paid/Complete)
    $date = date('Y-m-d');

    $conn->begin_transaction();
    try {
        // 1. Invoice එකේ මුදල ලබාගැනීම
        $inv_res = $conn->query("SELECT grand_total FROM invoice WHERE invoice_no = '$inv_no'");
        if ($inv_res->num_rows == 0) {
            throw new Exception("Invoice not found!");
        }
        $inv_data = $inv_res->fetch_assoc();
        $amount = floatval($inv_data['grand_total']);

        // 2. Invoice Status එක Update කිරීම (ඔයා එවන Status එක මෙතනට වැටේ)
        $conn->query("UPDATE invoice SET payment_status = '$status' WHERE invoice_no = '$inv_no'");

        // 3. Cashbook එකේ අන්තිම balance එක අරන් අලුත් balance එක හදන්න
        $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $last_balance = 0;
        if ($res && $row = $res->fetch_assoc()) {
            $last_balance = floatval($row['balance']);
        }
        $new_balance = $last_balance + $amount;

        // 4. Cashbook එකට දත්ත ඇතුළත් කිරීම
        $sql_cash = "INSERT INTO cashbook (date, invoice_no, income, balance) VALUES ('$date', '$inv_no', '$amount', '$new_balance')";
        $conn->query($sql_cash);

        $conn->commit();
        echo "success"; // සාර්ථක නම් මේක පිට කරයි
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    // දත්ත ලැබුණේ නැත්නම් මේ error එක පෙන්වයි
    echo '{"status":"error","message":"Invalid Request!"}';
}
?>