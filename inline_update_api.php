<?php
include 'db_config.php';

// --- SMSAPI.lk Settings ---
$api_key = "380|ulpebaPoK21nbPlTNCjeTP9Saij7R2Y19ox1uWWf"; 
$sender_id = "SMSAPI Demo"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';
    $device_name = isset($_POST['device_name']) ? mysqli_real_escape_string($conn, $_POST['device_name']) : '';
    $issue_name = isset($_POST['issue_name']) ? mysqli_real_escape_string($conn, $_POST['issue_name']) : '';
    $status = isset($_POST['device_status']) ? mysqli_real_escape_string($conn, $_POST['device_status']) : '';

    if (!empty($id)) {
        // 1. කලින් තිබුණු status එක පරීක්ෂා කිරීම (Duplicate SMS වැළැක්වීමට)
        $check_query = "SELECT device_status FROM job_device WHERE job_device_id = '$id'";
        $check_res = mysqli_query($conn, $check_query);
        $old_data = mysqli_fetch_assoc($check_res);
        $old_status = $old_data['device_status'] ?? '';

        // 2. Database එක Update කිරීම (Unique job_device_id එකට පමණයි)
        $sql = "UPDATE job_device SET 
                device_name = '$device_name', 
                issue_name = '$issue_name', 
                device_status = '$status'";
        
        if ($status === 'Completed' && $old_status !== 'Completed') { 
            $sql .= ", completed_date = NOW()"; 
        }
        $sql .= " WHERE job_device_id = '$id'";

        if (mysqli_query($conn, $sql)) {
            
            // 3. SMS එක යැවිය යුත්තේ Status එක වෙනස් වුනොත් පමණයි
            if ($old_status !== $status) {
                $query = "SELECT j.job_no, j.phone_number FROM job j 
                          INNER JOIN job_device jd ON j.job_no = jd.job_no 
                          WHERE jd.job_device_id = '$id' LIMIT 1";
                
                $res = mysqli_query($conn, $query);
                $job_data = mysqli_fetch_assoc($res);
                
                if ($job_data) {
                    $phone = "94" . ltrim(ltrim($job_data['phone_number'], '94'), '0');
                    $msg = "Multi9: Your $device_name (Job #".$job_data['job_no'].") is now $status.";

                    // --- SMSAPI.lk v3 API Call ---
                    $url = "https://smsapi.lk/api/v3/sms/send"; 
                    
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
                }
            }
            
            echo "Success"; 
        } else {
            echo "Error updating database: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Missing ID";
    }
}
mysqli_close($conn);
?>