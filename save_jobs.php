<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_no = $_POST['job_no'];
    $phone = $_POST['phone_number'];
    $cust_name = $_POST['customer_name'];
    $tech_id = $_POST['technician_id'];
    $job_date = $_POST['job_date'] ?? date('Y-m-d');

    // 1. New Technician handle
    if ($tech_id == 'new' && !empty($_POST['new_technician'])) {
        $new_name = mysqli_real_escape_string($conn, $_POST['new_technician']);
        mysqli_query($conn, "INSERT INTO technicians (name) VALUES ('$new_name')");
        $tech_id = mysqli_insert_id($conn);
    }

    // 2. Customer save (Update if exists)
    mysqli_query($conn, "INSERT INTO customer (phone_number, customer_name) 
                         VALUES ('$phone', '$cust_name') 
                         ON DUPLICATE KEY UPDATE customer_name='$cust_name'");

    // 3. Job save
    mysqli_query($conn, "INSERT INTO job (job_no, job_date, phone_number, technician_id) 
                         VALUES ('$job_no', '$job_date', '$phone', '$tech_id')");

    // 4. Multiple Devices save
    foreach ($_POST['devices'] as $key => $device) {
        $issue = $_POST['issues'][$key];
        $warranty = $_POST['warranty_status'][$key];
        mysqli_query($conn, "INSERT INTO job_device (job_no, device_name, issue_name, device_status, warranty_status) 
                             VALUES ('$job_no', '$device', '$issue', 'Pending', '$warranty')");
    }

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <body style='font-family:sans-serif;'>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Job Saved!',
            text: 'Job Number: $job_no',
            confirmButtonText: 'Go to List'
        }).then(() => {
            window.location = 'job_list.php';
        });
    </script>
    </body>";
}
?>