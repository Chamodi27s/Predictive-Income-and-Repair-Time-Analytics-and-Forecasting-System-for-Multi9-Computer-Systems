<?php
include 'db_config.php';

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $sql = "SELECT jd.job_no, j.phone_number FROM job_device jd 
            JOIN job j ON jd.job_no = j.job_no 
            WHERE jd.job_device_id = '$id'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    
    $phone = "94" . ltrim(ltrim($row['phone_number'], '94'), '0');
    $job_no = $row['job_no'];
    
    $sms_msg = "FINAL NOTICE: Job #$job_no වසරකට වඩා පරණ බැවින් සතියක් ඇතුළත රැගෙන නොගියහොත් විනාශ කරනු ලැබේ. Multi9 Repair.";

    // SMS API Curl කොටස (කලින් විදිහටම)
    $api_key = "379|OCV7ch8N7DpdjC5x5YMjg39tuko9SBft5FG4TAr9";
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
        // Destroy Warning දිනේ සටහන් කිරීම (මෙතන සිට දින 7ක් ගණන් කරලා Destroy වෙනවා)
        $conn->query("UPDATE job_device SET destroy_notice_sent_date = NOW() WHERE job_device_id = '$id'");
        echo "Destroy Notice Sent Successfully";
    }
    curl_close($ch);
}
?>