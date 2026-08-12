<?php
include 'db_config.php';

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';

    //  Update Database
    $sql = "UPDATE job_device SET 
            supplier_name = '$supplier', 
            device_status = '$status',
            issue_category = '$category' 
            WHERE job_device_id = '$id'";

    if (mysqli_query($conn, $sql)) {
        
        //  Fetch all required data 
        $query = "SELECT j.job_no, j.phone_number, jd.device_name, jd.completed_date, c.customer_name, jd.supplier_name 
                  FROM job j 
                  INNER JOIN job_device jd ON j.job_no = jd.job_no 
                  INNER JOIN customer c ON j.phone_number = c.phone_number
                  WHERE jd.job_device_id = '$id'";
        
        $res = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($res);

        if ($data) {
            // SMS 
            if ($status == 'Completed' || $status == 'Returned' || $status == 'Sent to Warranty') {
                
                $completed_date = $data['completed_date'] ? $data['completed_date'] : date('Y-m-d');
                $days_passed = floor((time() - strtotime($completed_date)) / 86400);
                $rent_fee = ($days_passed > 90) ? ceil(($days_passed - 90) / 30) * 100 : 0;
                
                // Supplier name
                $supplier_name = !empty($data['supplier_name']) ? $data['supplier_name'] : "N/A";

                // Message Formatting
                $msg = "Dear " . $data['customer_name'] . ", your " . $data['device_name'] . " (Job #" . $data['job_no'] . ") status: $status. ";
                $msg .= "Supplier: $supplier_name. ";
                
                if($status == 'Completed' && $rent_fee > 0) {
                    $msg .= "Rent: Rs. $rent_fee. Please collect soon. - Multi9 Repair";
                } else {
                    $msg .= "- Multi9 Repair";
                }

                // Phone Number Formatting
                $phone = "94" . ltrim(ltrim(preg_replace('/[^0-9]/', '', $data['phone_number']), '94'), '0');

                //  Send SMS (CURL)
                $api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
                $sender_id = "SMSAPI Demo";
                
                $ch = curl_init("https://dashboard.smsapi.lk/api/v3/sms/send");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['recipient' => $phone, 'sender_id' => $sender_id, 'message' => $msg]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $api_key", "Content-Type: application/json"]);
                
                curl_exec($ch);
                curl_close($ch);

                //  Save to History
                mysqli_query($conn, "INSERT INTO sms_history (job_device_id, phone_number, message, status) VALUES ('$id', '$phone', '" . mysqli_real_escape_string($conn, $msg) . "', 'Sent')");
                mysqli_query($conn, "UPDATE job_device SET last_sms_sent_date = CURDATE() WHERE job_device_id = '$id'");
            }
        }
        echo "success";
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
}
?>