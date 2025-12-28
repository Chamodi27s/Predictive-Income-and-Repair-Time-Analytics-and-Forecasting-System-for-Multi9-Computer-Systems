<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['device_id'])) {
    // ID එක clean කරගන්නවා
    $id = mysqli_real_escape_string($conn, $_POST['device_id']);

    // Job_device table එකෙන් අදාළ record එක delete කරන query එක
    // සටහන: මෙතනින් delete වෙන්නේ job_device එකේ row එක විතරයි. 
    // මුළු Job එකම (Customer details එක්ක) delete කරන්න ඕන නම් query එක වෙනස් වෙන්න ඕනේ.
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