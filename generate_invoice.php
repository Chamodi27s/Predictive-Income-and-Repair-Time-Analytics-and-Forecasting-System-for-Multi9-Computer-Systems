<?php
include 'db_config.php';
include 'navbar.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_no = $_POST['job_no'];
    $service = (float)$_POST['service_charge'];
    $parts = (float)$_POST['parts_total'];
    $grand_total = $service + $parts;
    $pay_method = $_POST['payment_method']; // අලුතින් එක් කළා

    // 1. Invoice එක Save කිරීම
    $sql_inv = "INSERT INTO invoice (job_no, invoice_date, service_charge, parts_total, grand_total) 
                VALUES ('$job_no', CURDATE(), '$service', '$parts', '$grand_total')";
    
    if ($conn->query($sql_inv)) {
        $inv_id = $conn->insert_id;

        // 2. Payment වගුවට දත්ත ඇතුළත් කිරීම
        $conn->query("INSERT INTO payment (invoice_no, payment_method, amount, payment_date, status) 
                      VALUES ('$inv_id', '$pay_method', '$grand_total', CURDATE(), 'Paid')");

        // 3. Cashbook එකේ අවසන් Balance එක ගෙන Update කිරීම
        $last_entry = $conn->query("SELECT balance FROM cashbook ORDER BY cashid DESC LIMIT 1")->fetch_assoc();
        $old_bal = $last_entry ? $last_entry['balance'] : 0;
        $new_bal = $old_bal + $grand_total;
        
        $conn->query("INSERT INTO cashbook (invoice_no, date, income, balance) 
                      VALUES ('$inv_id', CURDATE(), '$grand_total', '$new_bal')");

        // 4. Job Status එක 'Completed' කිරීම
        $conn->query("UPDATE job SET job_status = 'Completed' WHERE job_no = '$job_no'");

        echo "<script>alert('බිල්පත සාර්ථකයි! මුදල් පොත යාවත්කාලීන විය.'); window.location='view_invoice.php?id=$inv_id';</script>";
    }
}
?>

<div style="max-width: 400px; margin: 40px auto; font-family: sans-serif; border: 1px solid #2e7d32; padding: 25px; border-radius: 10px; background: #f9fff9;">
    <h3 style="color: #2e7d32; text-align: center;">Finalize Invoice</h3>
    <form method="POST">
        <label>Job No:</label>
        <input type="text" name="job_no" value="<?php echo $_GET['job_no']; ?>" readonly style="width: 100%; padding: 8px; margin-bottom: 15px; background: #eee; border: 1px solid #ccc;">

        <label>Service Charge (Rs.):</label>
        <input type="number" name="service_charge" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc;">

        <label>Parts Total (Rs.):</label>
        <input type="number" name="parts_total" value="0" style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc;">

        <label>Payment Method:</label>
        <select name="payment_method" style="width: 100%; padding: 8px; margin-bottom: 20px; border: 1px solid #ccc;">
            <option value="Cash">Cash</option>
            <option value="Online Transfer">Online Transfer / Card</option>
        </select>

        <button type="submit" style="width: 100%; padding: 12px; background: #2e7d32; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            Complete & Add to Cashbook
        </button>
    </form>
</div>