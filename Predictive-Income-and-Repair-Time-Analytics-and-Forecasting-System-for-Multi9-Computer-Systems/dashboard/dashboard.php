<?php
include("../config/db.php");
include("../includes/header.php");

// counts
$customers = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM customer"));
$pending = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM job WHERE job_status='Pending'"));
$completed = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM job WHERE job_status='Completed'"));
?>

<h2>Dashboard</h2>

<p>Total Customers: <?php echo $customers; ?></p>
<p>Pending Repairs: <?php echo $pending; ?></p>
<p>Completed Repairs: <?php echo $completed; ?></p>

<?php include("../includes/footer.php"); ?>
