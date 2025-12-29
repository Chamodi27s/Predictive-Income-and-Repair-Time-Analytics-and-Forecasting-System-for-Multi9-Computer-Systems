<?php
include 'db_config.php';

// URL eken job_no eka gannawa (e.g., delete.php?job_no=ORD-123)
if (isset($_GET['job_no'])) {
    $job_no = $_GET['job_no'];

    // Database eke prashna mathu nowi delete kirima sadaha 'Transaction' ekak use karamu
    $conn->begin_transaction();

    try {
        // 1. Mulinma 'job_device' table eke thiyena data delete karanna (Foreign Key nisa)
        $sql1 = "DELETE FROM job_device WHERE job_no = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("s", $job_no);
        $stmt1->execute();

        // 2. Dewanuwa 'job' table eke record eka delete karanna
        $sql2 = "DELETE FROM job WHERE job_no = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("s", $job_no);
        $stmt2->execute();

        // Okkoma hari nam database ekata commit karanna
        $conn->commit();

        // Delete unata passe ayeth dashboard (collected.php) ekata yanawa
        header("Location: collected.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        // Mokak hari waraduna nam okkoma cancel (Rollback) karanna
        $conn->rollback();
        echo "Error deleting record: " . $e->getMessage();
    }

} else {
    // job_no eka natham ayeth dashboard ekata yanawa
    header("Location: collected.php");
    exit();
}
?>