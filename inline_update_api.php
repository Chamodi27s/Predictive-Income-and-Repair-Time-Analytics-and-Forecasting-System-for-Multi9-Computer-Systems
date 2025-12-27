<?php
include 'db_config.php';

if (isset($_POST['id']) && isset($_POST['field']) && isset($_POST['value'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $field = mysqli_real_escape_string($conn, $_POST['field']);
    $value = mysqli_real_escape_string($conn, $_POST['value']);
    $type = $_POST['type'];

    if ($type == 'device') {
        // ඔබේ table එකේ තීරු නම 'device_status' දැයි පරීක්ෂා කරන්න
        $sql = "UPDATE job_device SET $field = '$value' WHERE job_device_id = '$id'";
    } else {
        // Technician update කිරීමට
        $sql = "UPDATE job SET $field = '$value' WHERE job_no = '$id'";
    }

    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    echo "Missing Data";
}
?>