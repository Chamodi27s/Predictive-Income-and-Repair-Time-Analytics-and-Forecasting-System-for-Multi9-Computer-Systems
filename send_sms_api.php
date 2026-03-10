<?php
include 'db_config.php';

// මේ විස්තර දෙක ඔයාගේ SMSAPI Dashboard එකේ තියෙන විදිහටම තියෙන්න ඕනේ
$api_key = "389|izlIg5IGA1QDWEyhzwLWwbN3Dhfh4ONZ3T8PSWWE ";
$sender_id = "SMSAPI Demo"; 

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
            // දුරකථන අංකය 947XXXXXXXX ලෙස සැකසීම
            $clean_phone = ltrim($job_data['phone_number'], '94'); // කලින් තිබ්බ 94 අයින් කරන්න
            $clean_phone = ltrim($clean_phone, '0'); // මුලට බිංදුව තිබ්බොත් අයින් කරන්න
            $phone = "94" . $clean_phone;

            // පණිවිඩය (Status එක සිංහලෙන් දැමීමටත් පුළුවන්)
            $msg = "Multi9 Update: Your device " . $job_data['device_name'] . " (#" . $job_data['job_no'] . ") is now " . $status . ". Thank you!";

            $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
  
            $data = array(
                'recipient' => $phone,
                'sender_id' => $sender_id,
                'message'   => $msg
            );

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . $api_key,
                "Content-Type: application/json",
                "Accept: application/json"
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                echo "❌ Connection Error: " . $err;
            } else {
                // SMSAPI එක සාමාන්‍යයෙන් 200 හෝ 201 code එකක් එවයි
                if ($http_code == 200 || $http_code == 201) {
                    echo "✅ SMS Sent Successfully!";
                } else {
                    // API එකෙන් එන වැරැද්ද බලාගන්න response එක පෙන්වන්න පුළුවන්
                    echo "⚠️ SMS Failed. Code: " . $http_code . " Resp: " . $response;
                }
            }
        } else {
            echo "Error: Job data not found.";
        }
    } else {
        echo "Error: Missing ID.";
    }
}
mysqli_close($conn);
?>