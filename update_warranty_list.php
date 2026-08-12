<?php

include 'db_config.php';

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';

    // Job_Device Table Update 
    $sql = "UPDATE job_device SET 
            supplier_name = '$supplier', 
            device_status = '$status',
            issue_category = '$category' 
            WHERE job_device_id = '$id'";

    if (mysqli_query($conn, $sql)) {
        
        // Get customer details for sending SMS
        $fetch_info = "SELECT jd.device_name, jd.job_no, c.phone_number, c.customer_name 
                       FROM job_device jd
                       JOIN job j ON jd.job_no = j.job_no
                       JOIN customer c ON j.phone_number = c.phone_number
                       WHERE jd.job_device_id = '$id'";
        
        $result = mysqli_query($conn, $fetch_info);
        if($row = mysqli_fetch_assoc($result)) {
            $phone = trim($row['phone_number']);
            $customer_name = $row['customer_name'];
            $device_name = $row['device_name'];
            $job_no = $row['job_no'];

            // Phone number format fixing (Convert 077... to 9477...)
            if (substr($phone, 0, 1) === '0') {
                $phone = '94' . substr($phone, 1);
            } elseif (substr($phone, 0, 1) === '+') {
                $phone = ltrim($phone, '+');
            }

            // Send SMS based on status
            $message = "";
            $send_sms = false;

            if($status == 'Completed') {
                $message = "Dear $customer_name, Your warranty device ($device_name - Job #$job_no) repair is completed. - Smart Repair";
                $send_sms = true;
            } elseif($status == 'Returned') {
                $message = "Dear $customer_name, Your warranty device ($device_name - Job #$job_no) has been returned. Please collect it. - Smart Repair";
                $send_sms = true;
            } elseif($status == 'Sent to Warranty') {
                $message = "Dear $customer_name, Your device ($device_name - Job #$job_no) has been sent to warranty supplier. - Smart Repair";
                $send_sms = true;
            }

            // smsapi.lk (v3) cURL මඟින් SMS යැවීම
            if($send_sms && !empty($phone)) {
                $api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRPqyiUQzXzb";
                $sender_id = "SMSAPI Demo";
                $url = "https://dashboard.smsapi.lk/api/v3/sms/send";

                $data = [
                    'recipient' => $phone,
                    'sender_id' => $sender_id,
                    'message'   => $message
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . $api_key,
                    "Content-Type: application/json",
                    "Accept: application/json"
                ]);

                $response = curl_exec($ch);
                curl_close($ch);
            }
        }

        echo "success";
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    echo "Access Denied";
}
?>