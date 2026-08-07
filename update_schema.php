<?php
include 'db_config.php';

$queries = [
    "ALTER TABLE job_device ADD COLUMN item_model VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE job_device ADD COLUMN repair_path VARCHAR(255) DEFAULT 'Carry-In'",
    "ALTER TABLE job_device ADD COLUMN solution VARCHAR(255) DEFAULT NULL"
];

foreach ($queries as $q) {
    if (mysqli_query($conn, $q)) {
        echo "Successfully added column.\n";
    } else {
        echo "Error or column already exists: " . mysqli_error($conn) . "\n";
    }
}
echo "Schema update complete.";
?>
