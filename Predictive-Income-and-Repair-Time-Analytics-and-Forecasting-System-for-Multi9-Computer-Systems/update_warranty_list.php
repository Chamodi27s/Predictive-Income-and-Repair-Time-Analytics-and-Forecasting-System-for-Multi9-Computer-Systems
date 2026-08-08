<?php
// දත්ත ගබඩාවට සම්බන්ධ වීම
include 'db_config.php';

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // අලුතින් එකතු කළ Category අගය ලබා ගැනීම
    $category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';

    // Job_Device Table එක Update කිරීම (issue_category එකත් සමඟ)
    $sql = "UPDATE job_device SET 
            supplier_name = '$supplier', 
            device_status = '$status',
            issue_category = '$category' 
            WHERE job_device_id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    echo "Access Denied";
}
?>