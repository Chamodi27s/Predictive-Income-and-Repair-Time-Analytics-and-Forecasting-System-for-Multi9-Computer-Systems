<?php
include 'db_config.php';

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // දත්ත ලබා ගැනීම
    $sql = "SELECT jd.job_no, j.phone_number FROM job_device jd 
            JOIN job j ON jd.job_no = j.job_no 
            WHERE jd.job_device_id = '$id'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    
    $phone = "94" . ltrim(ltrim($row['phone_number'], '94'), '0');
    $job_no = $row['job_no'];
    
    // ඔයාට අවශ්‍ය මැසේජ් එක
    $sms_msg = "Multi9 Alert: Job #$job_no සූදානම් කර මාස 3ක් ගතවී ඇත. අද සිට ඉදිරි මාස 12 සඳහා මසකට Rs.100 බැගින් Rent එකක් එකතු වේ. ස්තූතියි!";

    // SMS API Curl කොටස
    $api_key = "380|ulpebaPoK21nbP|TNCjeTP9Saij7R2Y19oxluWWf";
    $sender_id = "SMSAPI Demo"; 
    $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
    $data = array('recipient' => $phone, 'sender_id' => $sender_id, 'message' => $sms_msg);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $api_key, "Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    if(curl_exec($ch)) {
        // මැසේජ් එක ගිය බව සටහන් කිරීම
        $conn->query("UPDATE job_device SET rent_warning_sent = 1 WHERE job_device_id = '$id'");
        echo "Rent Warning Sent";
    } else {
        echo "Error sending SMS";
    }
    curl_close($ch);
}
?>