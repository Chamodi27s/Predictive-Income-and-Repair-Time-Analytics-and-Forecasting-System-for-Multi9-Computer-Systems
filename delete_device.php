<?php
// delete_device.php
include 'db_config.php';

// CSS hari Navbar hari meheta include karanna EPA. 
// "Success" kiyana wachane witharak echo wenna ona.

if (isset($_POST['device_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['device_id']);
    
    // Check if ID is not empty
    if(!empty($id)) {
        $sql = "DELETE FROM job_device WHERE job_device_id = '$id'";
        if (mysqli_query($conn, $sql)) {
            echo "Success";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Empty ID Received";
    }
} else {
    echo "No ID Received";
}
exit();
?>