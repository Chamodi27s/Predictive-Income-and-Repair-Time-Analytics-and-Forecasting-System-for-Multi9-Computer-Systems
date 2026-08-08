<?php
include 'db_config.php';
session_start();


date_default_timezone_set("Asia/Colombo");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['invoice_no'])) {
    
    $inv_no = mysqli_real_escape_string($conn, $_POST['invoice_no']);
    $job_no = mysqli_real_escape_string($conn, $_POST['job_no']);
    $today = date('Y-m-d');

    
    $conn->begin_transaction();

    try {
        
        $update_inv = $conn->query("UPDATE invoice SET payment_status = 'Paid', balance_due = 0 WHERE invoice_no = '$inv_no'");
        if (!$update_inv) throw new Exception("Invoice Update Failed");

        
        $update_job = $conn->query("UPDATE job_device SET device_status = 'billed' WHERE job_no = '$job_no'");
        if (!$update_job) throw new Exception("Job Device Update Failed");

       
        $inv_res = $conn->query("SELECT grand_total FROM invoice WHERE invoice_no = '$inv_no'");
        $inv_row = $inv_res->fetch_assoc();
        $amount = floatval($inv_row['grand_total']);

        // Cashbook balance
        $cash_res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $last_balance = ($cash_res->num_rows > 0) ? floatval($cash_res->fetch_assoc()['balance']) : 0;
        $new_balance = $last_balance + $amount;

        $sql_cash = "INSERT INTO cashbook (invoice_no, date, income, balance, acc_id) 
                     VALUES ('$inv_no', '$today', '$amount', '$new_balance', NULL)";
        
        if (!$conn->query($sql_cash)) {
            throw new Exception("Cashbook Insert Failed: " . $conn->error);
        }

        $conn->commit();

        header("Location: generate_bill.php?view_only=true&job_no=" . urlencode($job_no));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("System Error: " . $e->getMessage());
    }

} else {
    echo "Invalid Request Method.";
}
?>