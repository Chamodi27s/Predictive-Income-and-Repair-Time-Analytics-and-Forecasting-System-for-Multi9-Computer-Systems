<?php
include 'db_config.php';

// Check if ID, device_id, and data are provided via POST
if (isset($_POST['id']) && isset($_POST['device_id']) && isset($_POST['data'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']); // Job No
    $device_id = mysqli_real_escape_string($conn, $_POST['device_id']); // Unique Device ID
    $data = json_decode($_POST['data'], true);

    if (!$data) {
        die("Invalid JSON format");
    }

    // Fetch current phone number using the job number
    $stmt_get_phone = $conn->prepare("SELECT phone_number FROM job WHERE job_no = ?");
    $stmt_get_phone->bind_param("s", $id);
    $stmt_get_phone->execute();
    $res_phone = $stmt_get_phone->get_result();
    $row_phone = $res_phone->fetch_assoc();
    $current_phone = $row_phone['phone_number'] ?? '';
    $stmt_get_phone->close();

    if (empty($current_phone)) {
        die("Error: Customer phone number not found.");
    }

    $api_key = "391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb";
    $clean_phone = preg_replace('/[^0-9]/', '', $current_phone);
    $phone = "94" . ltrim($clean_phone, '0');

    // 1. Handle Send Estimate SMS Action
    if (isset($data['action']) && $data['action'] === 'send_estimate_sms') {
        $raw_cost = isset($data['estimated_cost']) && is_numeric($data['estimated_cost']) ? $data['estimated_cost'] : 0;
        $issue = $data['issue_name'];
        
        // Update estimated cost for the specific device row using job_device_id
        $stmt_update_cost = $conn->prepare("UPDATE job_device SET estimated_cost = ? WHERE job_device_id = ?");
        $stmt_update_cost->bind_param("di", $raw_cost, $device_id);
        $stmt_update_cost->execute();
        $stmt_update_cost->close();

        // Save parts and prices details
        $total_cost = number_format((float)$raw_cost, 2);
        $parts_with_prices = $data['parts_details']; 
        $name = $data['customer_name'];

        $stmt_save_parts = $conn->prepare("UPDATE job_device SET parts_json = ? WHERE job_device_id = ?");
        $stmt_save_parts->bind_param("si", $parts_with_prices, $device_id);
        $stmt_save_parts->execute();
        $stmt_save_parts->close();

        // Prepare SMS text message
        $message = "Multi9: Hi $name, Job #$id ($issue) Estimate:\n";
        $message .= "Parts: $parts_with_prices\n";
        $message .= "Total: Rs.$total_cost\n";
        $message .= "Please visit or call 077 123 4567 to approve and an advance is required to start. Thank you!";

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

        echo "success";
        exit; 
    }

    // 2. Update Customer table details (Name and Email)
    $sql1 = "UPDATE customer SET customer_name = ?, email = ? WHERE phone_number = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("sss", $data['customer_name'], $data['email'], $current_phone);
    $stmt1->execute();
    $stmt1->close();

    // 3. Update Job Device table (Status, Advance, Category, Estimate, Issue Name) using unique job_device_id
    $advance = isset($data['advance_paid']) ? $data['advance_paid'] : 0;
    $sql3 = "UPDATE job_device SET issue_category = ?, estimated_cost = ?, advance_paid = ?, job_status = ?, issue_name = ? WHERE job_device_id = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("sddssi", $data['issue_category'], $data['estimated_cost'], $advance, $data['job_status'], $data['issue_name'], $device_id);
    
    if ($stmt3->execute()) {
        echo "success";
    } else {
        echo "Error in Device Update: " . $conn->error;
    }
    
    $stmt3->close(); 
    $conn->close();
}
?>