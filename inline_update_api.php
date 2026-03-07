<?php
include 'db_config.php';

// --- SMSAPI.lk Settings ---
$api_key = "380|ulpebaPoK21nbPlTNCjeTP9Saij7R2Y19ox1uWWf"; // ඔයාගේ නිවැරදි Token එක
$sender_id = "SMSAPI Demo"; // Dashboard එකේ පෙන්වන විදියටම

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // දත්ත ලබා ගැනීම
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';
    $device_name = isset($_POST['device_name']) ? mysqli_real_escape_string($conn, $_POST['device_name']) : '';
    $issue_name = isset($_POST['issue_name']) ? mysqli_real_escape_string($conn, $_POST['issue_name']) : '';
    $status = isset($_POST['device_status']) ? mysqli_real_escape_string($conn, $_POST['device_status']) : '';

    if (!empty($id)) {
        // 1. Database එක Update කිරීම
        $sql = "UPDATE job_device SET 
                device_name = '$device_name', 
                issue_name = '$issue_name', 
                device_status = '$status'";
        
        if ($status === 'Completed') { 
            $sql .= ", completed_date = NOW()"; 
        }
        $sql .= " WHERE job_device_id = '$id'";

        if (mysqli_query($conn, $sql)) {
            
            // 2. SMS එක යැවීමට අවශ්‍ය දත්ත ලබා ගැනීම
            $query = "SELECT j.job_no, j.phone_number FROM job j 
                      INNER JOIN job_device jd ON j.job_no = jd.job_no 
                      WHERE jd.job_device_id = '$id'";
            
            $res = mysqli_query($conn, $query);
            $job_data = mysqli_fetch_assoc($res);
            
            if ($job_data) {
                // දුරකථන අංකය 94XXXXXXXXX විදිහට සකස් කිරීම
                $phone = "94" . ltrim(ltrim($job_data['phone_number'], '94'), '0');
                $msg = "Multi9: Your $device_name (Job #".$job_data['job_no'].") is now $status.";

                // --- SMSAPI.lk v3 API Call ---
                $url = "https://smsapi.lk/api/v3/sms/send"; // නිවැරදි Endpoint එක
                
                $postData = json_encode([
                    'recipient' => $phone,
                    'sender_id' => $sender_id,
                    'message' => $msg
                ]);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer $api_key",
                    "Content-Type: application/json",
                    "Accept: application/json"
                ]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                curl_close($ch);
                
                // සාර්ථක නම් "Success" පෙන්වයි
                echo "Success"; 
            } else {
                echo "Success (Database updated, but SMS data not found)";
            }
        } else {
            echo "Error updating database: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Missing ID";
    }
}
mysqli_close($conn);
?>