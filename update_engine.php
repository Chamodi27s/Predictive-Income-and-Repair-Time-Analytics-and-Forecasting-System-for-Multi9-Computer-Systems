<?php 
include 'db_config.php';

if (isset($_POST['id']) && isset($_POST['data'])) {
    $id = $_POST['id'];
    $data = json_decode($_POST['data'], true);

    if (!$data) {
        die("Invalid JSON format");
    }

    // --- Database එකෙන් අංකය ලබා ගැනීම (Security එකට වඩාත් හොඳයි) ---
    // JSON එකේ අංකය නැති වුණත් Job No එක හරහා DB එකෙන් අංකය ගමු.
    $stmt_get_phone = $conn->prepare("SELECT phone_num FROM job WHERE job_no = ?");
    $stmt_get_phone->bind_param("s", $id);
    $stmt_get_phone->execute();
    $res_phone = $stmt_get_phone->get_result();
    $row_phone = $res_phone->fetch_assoc();
    $current_phone = $row_phone['phone_num'] ?? '';
    $stmt_get_phone->close();

    if (empty($current_phone)) {
        die("Error: Customer phone number not found for this job.");
    }

    // 1. Customer table එක update කිරීම (Phone Number එක EDIT වෙන්නේ නැත)
    // මෙහිදී අපි update කරන්නේ නම සහ email විතරයි.
    $sql1 = "UPDATE customer SET customer_name = ?, email = ? WHERE phone_number = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("sss", $data['customer_name'], $data['email'], $current_phone);
    $stmt1->execute();

    // 2. Job table එක update කිරීම
    $sql2 = "UPDATE job SET job_status = ?, estimated_cost = ? WHERE job_no = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sds", $data['job_status'], $data['estimated_cost'], $id);
    $stmt2->execute();

    // 3. Job Device table එක update කිරීම
    $sql3 = "UPDATE job_device SET issue_name = ?, issue_category = ? WHERE job_no = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("sss", $data['issue_name'], $data['issue_category'], $id);
    
    if ($stmt3->execute()) {
        // --- SMS Logic ආරම්භය ---
        if ($data['job_status'] === 'Approved') {
            $api_key = "380|ulpebaPoK21nbPlTNCjeTP9Saij7R2Y19ox1uWWf";
            
            // අංකය 947xxxxxxxx format එකට සැකසීම
            $clean_phone = preg_replace('/[^0-9]/', '', $current_phone);
            $phone = "94" . ltrim($clean_phone, '0');
            
            $cost = number_format($data['estimated_cost'], 2);
            $issue = $data['issue_name'];
            $message = "Multi9 Repair: Your job #$id ($issue) is APPROVED. Estimated Cost: Rs.$cost. Thank you!";

            $url = "https://dashboard.smsapi.lk/api/v3/sms/send";
            $sms_payload = array(
                'recipient' => $phone,
                'sender_id' => "SMSAPI Demo",
                'message' => $message
            );

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . $api_key,
                "Content-Type: application/json"
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms_payload));
            curl_exec($ch);
            curl_close($ch);
        }
        // --- SMS Logic අවසානය ---

        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
    
    $stmt1->close();
    $stmt2->close();
    $stmt3->close();
    $conn->close();
}
?>