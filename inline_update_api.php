<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // POST එකෙන් එන දත්ත ගමු
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';
    $device_name = isset($_POST['device_name']) ? mysqli_real_escape_string($conn, $_POST['device_name']) : '';
    $issue_name = isset($_POST['issue_name']) ? mysqli_real_escape_string($conn, $_POST['issue_name']) : '';
    $status = isset($_POST['device_status']) ? mysqli_real_escape_string($conn, $_POST['device_status']) : '';

    if (!empty($id)) {
        // Database එකේ job_device table එක update කරන SQL query එක
        // ඔයාගේ table එකේ column names 'device_name' සහ 'issue_name' ද කියා නැවත බලන්න
        $sql = "UPDATE job_device 
                SET device_name = '$device_name', 
                    issue_name = '$issue_name', 
                    device_status = '$status' 
                WHERE job_device_id = '$id'";

        if (mysqli_query($conn, $sql)) {
            // JavaScript එක "Success" බලාපොරොත්තුවෙන් ඉන්න නිසා මේක අනිවාර්යයි
            echo "Success";
        } else {
            echo "Database Error: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid ID";
    }
} else {
    echo "Invalid Request Method";
}

mysqli_close($conn);
?>