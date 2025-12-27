<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $job_no = mysqli_real_escape_string($conn, $_POST['job_no']);
    $job_date = mysqli_real_escape_string($conn, $_POST['job_date']); // දිනය ලබා ගැනීම
    $tech = mysqli_real_escape_string($conn, $_POST['technician']);

    // 1. Customer පරීක්ෂා කර ඇතුළත් කිරීම
    $checkCust = mysqli_query($conn, "SELECT phone_number FROM customer WHERE phone_number = '$phone'");
    if (mysqli_num_rows($checkCust) == 0) {
        mysqli_query($conn, "INSERT INTO customer (phone_number, customer_name, address) VALUES ('$phone', '$name', '$address')");
    }

    // 2. Job Table එකට දිනයත් සමඟ ඇතුළත් කිරීම
    $sql_job = "INSERT INTO job (job_no, phone_number, technician, job_date) 
                VALUES ('$job_no', '$phone', '$tech', '$job_date')";
    
    if (mysqli_query($conn, $sql_job)) {
        // 3. උපකරණ Loop කිරීම...
        if (isset($_POST['devices'])) {
            for ($i = 0; $i < count($_POST['devices']); $i++) {
                $d_name = mysqli_real_escape_string($conn, $_POST['devices'][$i]);
                $d_model = mysqli_real_escape_string($conn, $_POST['models'][$i]);
                $d_issue = mysqli_real_escape_string($conn, $_POST['issues'][$i]);
                
                if (!empty($d_name)) {
                    mysqli_query($conn, "INSERT INTO job_device (job_no, device_name, model, issue_name, device_status) 
                                         VALUES ('$job_no', '$d_name', '$d_model', '$d_issue', 'Pending')");
                }
            }
        }
        echo "<script>alert('Successfully Registered!'); window.location.href='job_list.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>