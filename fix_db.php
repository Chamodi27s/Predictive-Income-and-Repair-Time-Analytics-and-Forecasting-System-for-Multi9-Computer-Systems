<?php
include 'db_config.php';

$q = "ALTER TABLE job_device ADD COLUMN another_note TEXT DEFAULT NULL";

if (mysqli_query($conn, $q)) {
    echo "Successfully added another_note column.\n";
} else {
    echo "Error or column already exists: " . mysqli_error($conn) . "\n";
}
?>
