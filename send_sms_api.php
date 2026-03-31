<?php
include 'db_config.php';

// Config
$api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
$sender_id = "SMSAPI Demo"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : '';

    if (!empty($id)) {
        // Rent එක ගණනය කිරීමට completed_date එකත් query එකට එකතු කර ගන්නවා
        $query = "SELECT j.job_no, j.phone_number, jd.device_name, jd.completed_date, c.customer_name 
                  FROM job j 
                  INNER JOIN job_device jd ON j.job_no = jd.job_no 
                  INNER JOIN customer c ON j.phone_number = c.phone_number
                  WHERE jd.job_device_id = '$id'";
        
        $res = mysqli_query($conn, $query);
        $job_data = mysqli_fetch_assoc($res);
        
        if ($job_data && $job_data['completed_date']) {
            // --- Rent Calculation Logic ---
            $completed_date = strtotime($job_data['completed_date']);
            $today = time();
            $days_passed = floor(($today - $completed_date) / 86400); // ගතවූ දින ගණන
            
            $rent_fee = 0;
            if($days_passed > 90) {
                // දින 90න් පසු සෑම දින 30කටම (මාසයකට) රු. 100 බැගින්
                $rent_fee = ceil(($days_passed - 90) / 30) * 100;
            }

            // Phone number formatting
            $raw_phone = $job_data['phone_number'];
            $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);
            $clean_phone = ltrim($clean_phone, '94'); 
            $clean_phone = ltrim($clean_phone, '0'); 
            $phone = "94" . $clean_phone;

            // --- නව පණිවිඩය (Custom Message with Rent) ---
            $msg = "Hi " . $job_data['customer_name'] . ", your " . $job_data['device_name'] . " has been ready for $days_passed days. ";
            if($rent_fee > 0) {
                $msg .= "Current rent fee is Rs. $rent_fee. Please collect it soon from Multi9.";
            } else {
                $msg .= "Please collect it from Multi9 at your earliest.";
            }

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
                if ($http_code == 200 || $http_code == 201) {
                    echo "✅ Rent Reminder Sent Successfully!";
                } else {
                    echo "⚠️ SMS Failed. Code: $http_code";
                }
            }
        } else {
            echo "❌ Error: Job data or Completion date not found.";
        }
    } else {
        echo "❌ Error: Missing ID.";
    }
}
mysqli_close($conn);
?>