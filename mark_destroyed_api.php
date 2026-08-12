<?php
include 'db_config.php';

if(isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "UPDATE job_device SET device_status = 'Destroyed' WHERE job_device_id = '$id'";
    
    if($conn->query($sql)) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>