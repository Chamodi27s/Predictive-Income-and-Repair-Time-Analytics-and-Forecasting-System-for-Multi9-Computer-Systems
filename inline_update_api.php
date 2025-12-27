<?php
include 'db_config.php';

// Data tika check karanna
if (isset($_POST['id']) && isset($_POST['device_name']) && isset($_POST['issue_name']) && isset($_POST['device_status'])) {
    
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $dev_name = mysqli_real_escape_string($conn, $_POST['device_name']);
    $iss_name = mysqli_real_escape_string($conn, $_POST['issue_name']);
    $status = mysqli_real_escape_string($conn, $_POST['device_status']);

    // Database eka update karana query eka
    $sql = "UPDATE job_device SET 
            device_name = '$dev_name', 
            issue_name = '$iss_name', 
            device_status = '$status' 
            WHERE job_device_id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    echo "Error: Missing Data"; // Meka thamai oyaata enne
}
exit();
?>