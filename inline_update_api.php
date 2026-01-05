<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // POST එකෙන් එන දත්ත ගමු
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';
    $device_name = isset($_POST['device_name']) ? mysqli_real_escape_string($conn, $_POST['device_name']) : '';
    $issue_name = isset($_POST['issue_name']) ? mysqli_real_escape_string($conn, $_POST['issue_name']) : '';
    $status = isset($_POST['device_status']) ? mysqli_real_escape_string($conn, $_POST['device_status']) : '';

    if (!empty($id)) {
        // 1. මූලික Update Query එක
        $sql = "UPDATE job_device 
                SET device_name = '$device_name', 
                    issue_name = '$issue_name', 
                    device_status = '$status'";

        // 2. 🔥 Status එක 'Completed' නම් පමණක් completed_date එකට දැනට පවතින වේලාව (NOW()) දානවා
        if ($status === 'Completed') {
            $sql .= ", completed_date = NOW()";
        }

        $sql .= " WHERE job_device_id = '$id'";

        if (mysqli_query($conn, $sql)) {
            
            // 3. Status එක වෙනස් වෙද්දී පණිවිඩයක් යැවීමට අවශ්‍ය නම් (උදාහරණයක් ලෙස)
            if ($status === 'Completed') {
                // මෙතනදී ඔයාට SMS API එකක් මගින් "Your item is ready" වගේ මැසේජ් එකක් යවන්න පුළුවන්
            }

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