<?php
include 'db_config.php';

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    
    $sql = "SELECT jd.job_no, j.phone_number, jd.device_name, c.customer_name 
            FROM job_device jd 
            JOIN job j ON jd.job_no = j.job_no 
            JOIN customer c ON j.phone_number = c.phone_number
            WHERE jd.job_device_id = '$id'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    
    $phone = "94" . ltrim(ltrim($row['phone_number'], '94'), '0');
    $job_no = $row['job_no'];
    $device_name = $row['device_name'];
    $customer_name = $row['customer_name'];
    
    // Check whether it's a destroy notice or regular rent notice
    if ($type === 'destroy') {
        $sms_msg = "FINAL NOTICE: Dear $customer_name, your $device_name (Job #$job_no) is over a year old. It will be destroyed within 7 days if not collected. - Multi9 Repair";
    } else {
        // Regular rent/delay SMS fallback if triggered
        $sms_msg = "Dear $customer_name, please collect your $device_name (Job #$job_no) from Multi9 Repair as soon as possible.";
    }

    // SMS API Curl 
    $api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
    $sender_id = "SMSAPI Demo"; 
    $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
    $data = array('recipient' => $phone, 'sender_id' => $sender_id, 'message' => $sms_msg);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $api_key, "Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    
    if($response) {
        if ($type === 'destroy') {
            // Update destroy_notice_sent_date when destroy SMS is sent
            $conn->query("UPDATE job_device SET destroy_notice_sent_date = NOW() WHERE job_device_id = '$id'");
            
            // Also save to sms_history table for tracking
            $safe_msg = $conn->real_escape_string($sms_msg);
            $conn->query("INSERT INTO sms_history (job_device_id, phone_number, message, status) VALUES ('$id', '$phone', '$safe_msg', 'Sent (Destroy Notice)')");
            
            echo "Destroy Notice Sent Successfully. Item will be marked as destroyed after 7 days.";
        } else {
            echo "SMS Sent Successfully";
        }
    } else {
        echo "Failed to send SMS.";
    }
    curl_close($ch);
}
?>