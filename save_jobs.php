<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone_number'];
    $name = $_POST['customer_name'];
    $job_no = $_POST['job_no'];
    $device = $_POST['device_name'];

    // 1. Customer Save/Update
    $conn->query("INSERT INTO customer (phone_number, customer_name, address, email) 
                  VALUES ('$phone', '$name', '{$_POST['address']}', '{$_POST['email']}') 
                  ON DUPLICATE KEY UPDATE customer_name='$name'");

    // 2. Job Save
    $conn->query("INSERT INTO job (job_no, phone_number, job_date, technician, job_status) 
                  VALUES ('$job_no', '$phone', CURDATE(), '{$_POST['technician']}', 'Pending')");

    // 3. Device Save
    $conn->query("INSERT INTO job_device (job_no, device_name, model) 
                  VALUES ('$job_no', '$device', '{$_POST['model']}')");

    header("Location: customer_list.php");
}
?>