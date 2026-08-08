<?php
include 'db_config.php';

echo "Jobs:\n";
$res = mysqli_query($conn, "SELECT * FROM job ORDER BY job_no DESC LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

echo "\nJob Devices:\n";
$res = mysqli_query($conn, "SELECT * FROM job_device ORDER BY job_no DESC LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
