<?php
include 'db_config.php';

$api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
$sender_id = "SMSAPI Demo"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $query = "SELECT j.job_no, j.phone_number, jd.device_name, jd.completed_date, c.customer_name 
              FROM job j 
              INNER JOIN job_device jd ON j.job_no = jd.job_no 
              INNER JOIN customer c ON j.phone_number = c.phone_number
              WHERE jd.job_device_id = '$id'";
    
    $res = mysqli_query($conn, $query);
    $job_data = mysqli_fetch_assoc($res);
    
    if ($job_data) {
        // 1. Rent Calculation
        $days_passed = floor((time() - strtotime($job_data['completed_date'])) / 86400);
        $rent_fee = ($days_passed > 90) ? ceil(($days_passed - 90) / 30) * 100 : 0;

        // 2. Phone Formatting
        $phone = "94" . ltrim(ltrim(preg_replace('/[^0-9]/', '', $job_data['phone_number']), '94'), '0');

        // 3. Message
        $msg = "Hi " . $job_data['customer_name'] . ", your " . $job_data['device_name'] . " ready for $days_passed days. ";
        $msg .= ($rent_fee > 0) ? "Current rent: Rs. $rent_fee." : "Collect soon.";

        // 4. Send SMS (CURL)
        $ch = curl_init("https://dashboard.smsapi.lk/api/v3/sms/send");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['recipient' => $phone, 'sender_id' => $sender_id, 'message' => $msg]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $api_key", "Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 5. Save to History & Update last_sent_date
        $status = ($http_code == 200 || $http_code == 201) ? 'Success' : 'Failed';
        mysqli_query($conn, "INSERT INTO sms_history (job_device_id, phone_number, message, status) VALUES ('$id', '$phone', '$msg', '$status')");
        mysqli_query($conn, "UPDATE job_device SET last_sms_sent_date = CURDATE() WHERE job_device_id = '$id'");

        echo ($status == 'Success') ? "✅ Sent & Logged" : "⚠️ Failed";
    }
}
?>