<?php
include 'db_config.php';

// Config - මේවා වෙනම file එකක තිබේ නම් වඩාත් හොඳයි
$api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
$sender_id = "SMSAPI Demo"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

    if (!empty($id)) {
        $query = "SELECT j.job_no, j.phone_number, jd.device_name 
                  FROM job j 
                  INNER JOIN job_device jd ON j.job_no = jd.job_no 
                  WHERE jd.job_device_id = '$id'";
        
        $res = mysqli_query($conn, $query);
        $job_data = mysqli_fetch_assoc($res);
        
        if ($job_data) {
            // Phone number formatting
            $raw_phone = $job_data['phone_number'];
            $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone); // ඉලක්කම් පමණක් ඉතිරි කරන්න
            $clean_phone = ltrim($clean_phone, '94'); 
            $clean_phone = ltrim($clean_phone, '0'); 
            $phone = "94" . $clean_phone;

            $msg = "Multi9 Update: Your device " . $job_data['device_name'] . " (#" . $job_data['job_no'] . ") status is: " . $status . ". Thank you!";

            $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
  
            $data = json_encode(array(
                'recipient' => $phone,
                'sender_id' => $sender_id,
                'message'   => $msg
            ));

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . trim($api_key),
                "Content-Type: application/json",
                "Accept: application/json"
            ));

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                echo "❌ Connection Error: " . $err;
            } else {
                $res_data = json_decode($response, true);
                // සාර්ථක නම් බොහෝවිට status 'success' ලෙස හෝ code 200/201 ලෙස පැමිණේ
                if ($http_code == 200 || $http_code == 201) {
                    echo "✅ SMS Sent Successfully to " . $phone;
                } else {
                    $error_msg = isset($res_data['message']) ? $res_data['message'] : 'Unknown Error';
                    echo "⚠️ SMS Failed. Code: $http_code | Error: $error_msg";
                }
            }
        } else {
            echo "❌ Error: Job data not found.";
        }
    } else {
        echo "❌ Error: Missing ID.";
    }
}
mysqli_close($conn);
?>