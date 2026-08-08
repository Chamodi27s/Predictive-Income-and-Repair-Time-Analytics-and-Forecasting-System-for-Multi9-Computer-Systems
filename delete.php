<?php
include 'db_config.php';

// to gete job usisng url
if (isset($_GET['job_no'])) {
    $job_no = $_GET['job_no'];

    
    $conn->begin_transaction();

    try {
        // to delete job device tabale device
        $sql1 = "DELETE FROM job_device WHERE job_no = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("s", $job_no);
        $stmt1->execute();

        //Delete device in job tabale
        $sql2 = "DELETE FROM job WHERE job_no = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("s", $job_no);
        $stmt2->execute();

        $conn->commit();

        
        header("Location: collected.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        
        $conn->rollback();
        echo "Error deleting record: " . $e->getMessage();
    }

} else {
    // job_no eka natham ayeth dashboard ekata yanawa
    header("Location: collected.php");
    exit();
}
?>