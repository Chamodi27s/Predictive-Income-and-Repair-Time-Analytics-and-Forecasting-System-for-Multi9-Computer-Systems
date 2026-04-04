<?php
include 'db_config.php';
session_start();

// ශ්‍රී ලංකාවේ වේලාව සැකසීම
date_default_timezone_set("Asia/Colombo");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['invoice_no'])) {
    
    $inv_no = mysqli_real_escape_string($conn, $_POST['invoice_no']);
    $job_no = mysqli_real_escape_string($conn, $_POST['job_no']);
    $today = date('Y-m-d');

    // දත්ත සමුදායේ වැඩ කිහිපයක් එකවර සිදුවන නිසා Transaction එකක් භාවිතා කරමු
    $conn->begin_transaction();

    try {
        // 1. Invoice වගුව යාවත්කාලීන කිරීම
        $update_inv = $conn->query("UPDATE invoice SET payment_status = 'Paid', balance_due = 0 WHERE invoice_no = '$inv_no'");
        if (!$update_inv) throw new Exception("Invoice Update Failed");

        // 2. job_device වගුවේ status එක 'billed' ලෙස යාවත්කාලීන කිරීම
        $update_job = $conn->query("UPDATE job_device SET device_status = 'billed' WHERE job_no = '$job_no'");
        if (!$update_job) throw new Exception("Job Device Update Failed");

        // 3. Cashbook එකට දත්ත ඇතුළත් කිරීම
        // මුලින්ම අදාළ ඉන්වොයිස් එකේ මුළු මුදල ලබාගන්න
        $inv_res = $conn->query("SELECT grand_total FROM invoice WHERE invoice_no = '$inv_no'");
        $inv_row = $inv_res->fetch_assoc();
        $amount = floatval($inv_row['grand_total']);

        // Cashbook එකේ දැනට පවතින අවසාන ශේෂය (Balance) ලබාගන්න
        $cash_res = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1");
        $last_balance = ($cash_res->num_rows > 0) ? floatval($cash_res->fetch_assoc()['balance']) : 0;
        $new_balance = $last_balance + $amount;

        // ඔයාගේ Database Structure එකට අනුව (invoice_no, date, income, balance, acc_id) ඇතුළත් කිරීම
        // මෙහි acc_id සඳහා දැනට NULL අගය ලබා දී ඇත
        $sql_cash = "INSERT INTO cashbook (invoice_no, date, income, balance, acc_id) 
                     VALUES ('$inv_no', '$today', '$amount', '$new_balance', NULL)";
        
        if (!$conn->query($sql_cash)) {
            throw new Exception("Cashbook Insert Failed: " . $conn->error);
        }

        // සියලු පියවර සාර්ථක නම් දත්ත සුරැකීම (Commit) කරන්න
        $conn->commit();

        // සාර්ථක වූ පසු නැවත බිල පෙන්වන පිටුවට යොමු කරන්න
        header("Location: generate_bill.php?view_only=true&job_no=" . urlencode($job_no));
        exit();

    } catch (Exception $e) {
        // කිසියම් දෝෂයක් ආවොත් සිදුකළ වෙනස්කම් අවලංගු කරන්න
        $conn->rollback();
        die("System Error: " . $e->getMessage());
    }

} else {
    echo "Invalid Request Method.";
}
?>