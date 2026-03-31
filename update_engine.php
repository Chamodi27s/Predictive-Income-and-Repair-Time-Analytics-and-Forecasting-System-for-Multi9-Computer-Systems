<?php 
include 'db_config.php';

if (isset($_POST['id']) && isset($_POST['data'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']); // Job No
    $data = json_decode($_POST['data'], true);

    if (!$data) {
        die("Invalid JSON format");
    }

    // පාරිභෝගිකයාගේ දුරකථන අංකය ලබා ගැනීම
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

    // --- පියවර A: Estimate SMS යැවීමේ Logic එක ---
    if (isset($data['action']) && $data['action'] === 'send_estimate_sms') {
        $total_cost = number_format($data['estimated_cost'], 2);
        $parts_with_prices = $data['parts_details']; 
        $name = $data['customer_name'];
        $issue = $data['issue_name'];

        $stmt_save_parts = $conn->prepare("UPDATE job_device SET parts_json = ? WHERE job_no = ? AND issue_name = ?");
        $stmt_save_parts->bind_param("sss", $parts_with_prices, $id, $issue);
        $stmt_save_parts->execute();
        $stmt_save_parts->close();

        $message = "Multi9: Hi $name, Job #$id ($issue) Estimate:\n";
        $message .= "Parts: $parts_with_prices\n";
        $message .= "Total: Rs.$total_cost\n";
        $message .= "Please visit or call 077 123 4567 to approved and An advance is required to start. Thank you!.";

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

    // --- පියවර B: සාමාන්‍ය Update (Approved/Pending) සහ Advance Save කිරීම ---
    
    // 1. Customer table update 
    $sql1 = "UPDATE customer SET customer_name = ?, email = ? WHERE phone_number = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("sss", $data['customer_name'], $data['email'], $current_phone);
    $stmt1->execute();

    // 2. Job table update (මෙතැනට advance_paid එකතු කළා)
    $advance = isset($data['advance_paid']) ? $data['advance_paid'] : 0;
    $sql2 = "UPDATE job SET job_status = ?, estimated_cost = ?, advance_paid = ? WHERE job_no = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sdds", $data['job_status'], $data['estimated_cost'], $advance, $id);
    $stmt2->execute();

    // 3. Job Device table update 
    $sql3 = "UPDATE job_device SET issue_category = ? WHERE job_no = ? AND issue_name = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("sss", $data['issue_category'], $id, $data['issue_name']);
    
    if ($stmt3->execute()) {
        echo "success";
    } else {
        echo "Error in Device Update: " . $conn->error;
    }
    
    $stmt1->close(); 
    $stmt2->close(); 
    $stmt3->close(); 
    $conn->close();
}
?>