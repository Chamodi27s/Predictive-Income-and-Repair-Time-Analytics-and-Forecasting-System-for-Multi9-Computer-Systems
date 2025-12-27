<?php
include 'db_config.php';

if (isset($_POST['device_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['device_id']);
    // job_device වගුවෙන් අදාළ පේළිය මකා දැමීම
    $sql = "DELETE FROM job_device WHERE job_device_id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo mysqli_error($conn);
    }
}
?>