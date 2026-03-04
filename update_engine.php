<?php 
include 'db_config.php';

if (isset($_POST['id']) && isset($_POST['data'])) {
    $id = $_POST['id'];
    // JSON data එක array එකක් විදිහට decode කරගන්නවා
    $data = json_decode($_POST['data'], true);

    if (!$data) {
        die("Invalid JSON format");
    }

    // Database updates පියවරෙන් පියවර සිදු කරමු

    // 1. Customer table එක update කිරීම (customer_name සහ email)
    $sql1 = "UPDATE customer SET customer_name = ?, email = ? WHERE phone_number = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("sss", $data['customer_name'], $data['email'], $data['phone_number']);
    $stmt1->execute();

    // 2. Job table එකේ status එක update කිරීම
    $sql2 = "UPDATE job SET job_status = ? WHERE job_no = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ss", $data['job_status'], $id);
    $stmt2->execute();

    // 3. Job Device table එකේ issue එක සහ Diagnosis Category එක update කිරීම
    // මෙහිදී අලුතින් issue_category එක update වීමට කේතය සකස් කර ඇත
    $sql3 = "UPDATE job_device SET issue_name = ?, issue_category = ? WHERE job_no = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("sss", $data['issue_name'], $data['issue_category'], $id);
    
    if ($stmt3->execute()) {
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