<?php
include 'db_config.php';

// මේ විස්තර දෙක ඔයාගේ SMSAPI Dashboard එකේ තියෙන විදිහටම තියෙන්න ඕනේ
$api_key = "378|Ny4YLhCMaTosGeaTZhiaWt3v7kMSd4woZZdTefLq";
$sender_id = "SMSAPI Demo"; // Dashboard එකේ Approved නම "SMSAPI Demo" නම් ඒකම දාන්න

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

    if (!empty($id)) {
        // Database එකෙන් තොරතුරු ලබා ගැනීම
        $query = "SELECT j.job_no, j.phone_number, jd.device_name 
                  FROM job j 
                  INNER JOIN job_device jd ON j.job_no = jd.job_no 
                  WHERE jd.job_device_id = '$id'";
        
        $res = mysqli_query($conn, $query);
        $job_data = mysqli_fetch_assoc($res);
        
        if ($job_data) {
            // Phone number එක 947XXXXXXXX විදිහට හැදීම
            $phone = "94" . ltrim(ltrim($job_data['phone_number'], '94'), '0');
            $msg = "Multi9 Update: Your device " . $job_data['device_name'] . " (#" . $job_data['job_no'] . ") is now " . $status . ".";

            $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
  
            $data = array(
                'recipient' => $phone,
                'sender_id' => $sender_id,
                'message'   => $msg
            );

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            // --- මේ පේළිය අනිවාර්යයි (Localhost එකේ SSL අවුල් මගහරින්න) ---
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . $api_key,
                "Content-Type: application/json",
                "Accept: application/json"
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                echo "CURL Error: " . $err;
            } else {
                // API එකෙන් එන නියම Response එක පෙන්වනවා
                echo $response; 
            }
        } else {
            echo "Error: Job data not found in database.";
        }
    } else {
        echo "Error: Missing ID.";
    }
}
mysqli_close($conn);
?>