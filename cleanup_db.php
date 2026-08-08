<?php
include 'db_config.php';
mysqli_query($conn, "DELETE FROM job WHERE job_no = 'ORD-5001'");
mysqli_query($conn, "DELETE FROM job WHERE job_no = 'ORD-5000'"); // In case 5000 is also corrupted
echo "Deleted corrupted jobs.";
?>
