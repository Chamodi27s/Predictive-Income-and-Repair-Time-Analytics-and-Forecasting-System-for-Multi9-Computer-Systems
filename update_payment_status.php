<?php
include 'db_config.php';

// කාල කලාපය ලංකාවට සැකසීම
date_default_timezone_set("Asia/Colombo");

// POST එක හරහා දත්ත ලැබෙනවාදැයි පරීක්ෂා කිරීම
if (isset($_POST['invoice_no']) && isset($_POST['status'])) {
    $inv_no = $_POST['invoice_no'];
    $status = $_POST['status']; 
    
    // Rent එකත් එක්ක එන අලුත්ම මුදල (final_total) ලබා ගැනීම
    $final_amount = isset($_POST['final_total']) ? floatval($_POST['final_total']) : null;
    $date = date('Y-m-d');

    $conn->begin_transaction();
    try {
        // 1. Invoice එකේ දැනට පවතින මුදල ලබාගැනීම
        $stmt_get = $conn->prepare("SELECT grand_total FROM invoice WHERE invoice_no = ?");
        $stmt_get->bind_param("s", $inv_no);
        $stmt_get->execute();
        $inv_res = $stmt_get->get_result();

        if ($inv_res->num_rows == 0) {
            throw new Exception("Invoice not found!");
        }
        $inv_data = $inv_res->fetch_assoc();
        
        // පමාවූ ගාස්තු ඇතුළත් අලුත් මුදලක් එවා ඇත්නම් එය භාවිතා කරයි, නැත්නම් පරණ මුදලම ගනී
        $amount_to_save = ($final_amount !== null) ? $final_amount : floatval($inv_data['grand_total']);

        // 2. Invoice Status එක සහ මුළු මුදල (Grand Total) Update කිරීම
        // මෙහිදී 'Paid' status එක වැටෙන අතර Rent එක නිසා වැඩි වූ ගාණද Database එකේ save වේ
        $stmt_upd = $conn->prepare("UPDATE invoice SET payment_status = ?, grand_total = ? WHERE invoice_no = ?");
        $stmt_upd->bind_param("sds", $status, $amount_to_save, $inv_no);
        $stmt_upd->execute();

        // 3. Cashbook එකේ අන්තිම balance එක ලබාගෙන අලුත් balance එක සෑදීම
        $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $last_balance = 0;
        if ($res && $row = $res->fetch_assoc()) {
            $last_balance = floatval($row['balance']);
        }
        $new_balance = $last_balance + $amount_to_save;

        // 4. Cashbook එකට අදාළ දත්ත ඇතුළත් කිරීම
        $stmt_cash = $conn->prepare("INSERT INTO cashbook (date, invoice_no, income, balance) VALUES (?, ?, ?, ?)");
        $stmt_cash->bind_param("ssdd", $date, $inv_no, $amount_to_save, $new_balance);
        $stmt_cash->execute();

        $conn->commit();
        echo "success"; 
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo '{"status":"error","message":"Invalid Request!"}';
}
?>