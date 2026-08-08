<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['device_id'])) {
    // ID 
    $id = mysqli_real_escape_string($conn, $_POST['device_id']);


    
    $sql = "DELETE FROM job_device WHERE job_device_id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid Request";
}

mysqli_close($conn);
?>