<?php
include 'db_config.php';

// කාල කලාපය ලංකාවට සැකසීම
date_default_timezone_set("Asia/Colombo");

// generate_bill.php එකෙන් ලැබෙන්නේ job_no එකයි
if (isset($_POST['mark_paid']) && isset($_POST['job_no'])) {
    
    $job_no = mysqli_real_escape_string($conn, $_POST['job_no']);
    $date = date('Y-m-d');
    $status = 'Paid';

    $conn->begin_transaction();
    try {
        // 1. Job No එකට අදාළ Invoice විස්තර සහ Rent එක ඇතුළු මුළු මුදල ලබාගැනීම
        // (මෙහිදී අපි invoice_no එකත් සොයාගත යුතුයි cashbook එකට දාන්න)
        $stmt_get = $conn->prepare("SELECT invoice_no, grand_total FROM invoice WHERE job_no = ?");
        $stmt_get->bind_param("s", $job_no);
        $stmt_get->execute();
        $inv_res = $stmt_get->get_result();

        if ($inv_res->num_rows == 0) {
            throw new Exception("Invoice not found for this Job No!");
        }
        $inv_data = $inv_res->fetch_assoc();
        $inv_no = $inv_data['invoice_no'];
        $amount_to_save = floatval($inv_data['grand_total']);

        // 2. Invoice Status එක 'Paid' ලෙස Update කිරීම
        $stmt_upd = $conn->prepare("UPDATE invoice SET payment_status = ? WHERE job_no = ?");
        $stmt_upd->bind_param("ss", $status, $job_no);
        $stmt_upd->execute();

        // 3. Cashbook එකේ අන්තිම balance එක ලබාගැනීම
        $res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $last_balance = 0;
        if ($res && $row = $res->fetch_assoc()) {
            $last_balance = floatval($row['balance']);
        }
        $new_balance = $last_balance + $amount_to_save;

        // 4. Cashbook එකට Income එක ඇතුළත් කිරීම
        $stmt_cash = $conn->prepare("INSERT INTO cashbook (date, invoice_no, income, balance) VALUES (?, ?, ?, ?)");
        $stmt_cash->bind_param("ssdd", $date, $inv_no, $amount_to_save, $new_balance);
        $stmt_cash->execute();

        $conn->commit();
        
        // සාර්ථක නම් නැවත බිලටම යොමු කරන්න
        header("Location: generate_bill.php?view_only=true&job_no=" . urlencode($job_no));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    echo "Invalid Request!";
}
?>