<?php
include 'db_config.php';

if (isset($_POST['id']) && isset($_POST['data'])) {
    $id = $_POST['id'];
    $data = json_decode($_POST['data'], true);

    if (!$data) {
        die("Invalid JSON format");
    }

    // Database updates piliwelata sidu karamu
    
    // 1. Customer table update kireema
    // Phone number eken update karana nisa eka use karamu
    $sql1 = "UPDATE customer SET customer_name = ?, email = ? WHERE phone_number = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("sss", $data['customer_name'], $data['email'], $data['phone_number']);
    $stmt1->execute();

    // 2. Job Status update kireema
    $sql2 = "UPDATE job SET job_status = ? WHERE job_no = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ss", $data['job_status'], $id);
    $stmt2->execute();

    // 3. Job Device issue update kireema
    $sql3 = "UPDATE job_device SET issue_name = ? WHERE job_no = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("ss", $data['issue_name'], $id);
    
    if ($stmt3->execute()) {
        echo "success";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    
    $stmt1->close();
    $stmt2->close();
    $stmt3->close();
    $conn->close();
}
?>