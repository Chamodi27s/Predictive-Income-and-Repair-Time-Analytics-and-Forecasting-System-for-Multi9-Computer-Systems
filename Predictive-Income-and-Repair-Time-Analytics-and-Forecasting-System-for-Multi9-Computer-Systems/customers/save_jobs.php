<?php
include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Safely get POST data
    $phone = isset($_POST['phone_number']) ? $conn->real_escape_string($_POST['phone_number']) : "";
    $name = isset($_POST['customer_name']) ? $conn->real_escape_string($_POST['customer_name']) : "";
    $address = isset($_POST['address']) ? $conn->real_escape_string($_POST['address']) : "";
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : "";
    $job_no = isset($_POST['job_no']) ? $conn->real_escape_string($_POST['job_no']) : "";
    $device = isset($_POST['device_name']) ? $conn->real_escape_string($_POST['device_name']) : "";
    $model = isset($_POST['model']) ? $conn->real_escape_string($_POST['model']) : "";
    $technician = isset($_POST['technician']) ? $conn->real_escape_string($_POST['technician']) : "";

    // --- Optional: Generate unique Job No if duplicate exists ---
    do {
        $check = $conn->query("SELECT job_no FROM job WHERE job_no='$job_no'");
        if ($check->num_rows > 0) {
            $job_no = "ORD-" . rand(1000, 9999); // regenerate
        }
    } while ($check->num_rows > 0);

    // 1. Customer Save/Update
    $customer_sql = "INSERT INTO customer (phone_number, customer_name, address, email)
                     VALUES ('$phone', '$name', '$address', '$email')
                     ON DUPLICATE KEY UPDATE customer_name='$name', address='$address', email='$email'";

    if (!$conn->query($customer_sql)) {
        die("Customer insert/update failed: " . $conn->error);
    }

    // 2. Job Save
    $job_sql = "INSERT INTO job (job_no, phone_number, job_date, technician, job_status)
                VALUES ('$job_no', '$phone', CURDATE(), '$technician', 'Pending')";

    if (!$conn->query($job_sql)) {
        die("Job insert failed: " . $conn->error);
    }

    // 3. Device Save
    $device_sql = "INSERT INTO job_device (job_no, device_name, model)
                   VALUES ('$job_no', '$device', '$model')";

    if (!$conn->query($device_sql)) {
        die("Device insert failed: " . $conn->error);
    }

    // Redirect to job list after successful save
header("Location: http://localhost/Predictive-Income-and-Repair-Time-Analytics-and-Forecasting-System-for-Multi9-Computer-Systems/jobs/job_list.php");
exit;


}
?>
