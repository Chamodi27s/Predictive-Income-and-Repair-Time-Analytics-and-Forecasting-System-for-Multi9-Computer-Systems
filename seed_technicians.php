<?php
include 'db_config.php';

$technicians = ['Nimales', 'Pradeep', 'Kasun', 'Dilshan', 'Sachin', 'NIMALES'];

foreach ($technicians as $tech) {
    // Check if exists
    $query = "SELECT * FROM technicians WHERE name = '$tech'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 0) {
        $insert = "INSERT INTO technicians (name) VALUES ('$tech')";
        if (mysqli_query($conn, $insert)) {
            echo "Inserted technician: $tech\n";
        } else {
            echo "Failed to insert $tech: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Technician already exists: $tech\n";
    }
}
echo "Done.";
?>
