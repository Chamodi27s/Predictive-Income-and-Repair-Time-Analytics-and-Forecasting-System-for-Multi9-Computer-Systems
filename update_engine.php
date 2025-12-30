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

    // 3. Job Device table එකේ issue එක සහ warranty_status එක update කිරීම
    // මෙහිදී අපි 'warranty_status' එකත් update කරනවා (අවශ්‍ය නම් පමණක්)
    // සාමාන්‍යයෙන් collected පේජ් එකකදී මේක වෙනස් වෙන්නේ නැති වුණත් ආරක්ෂාවට ඇතුළත් කිරීම සුදුසුයි
    $sql3 = "UPDATE job_device SET issue_name = ?, warranty_status = ? WHERE job_no = ?";
    $stmt3 = $conn->prepare($sql3);
    
    // වැදගත්: ඔයාගේ data array එකේ warranty_status එකත් එනවා නම් මෙතන bind කරන්න
    // දැනට issue_name එක විතරක් update වෙනවා නම් පහත පේළිය භාවිතා කරන්න
    $sql3 = "UPDATE job_device SET issue_name = ? WHERE job_no = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("ss", $data['issue_name'], $id);
    
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