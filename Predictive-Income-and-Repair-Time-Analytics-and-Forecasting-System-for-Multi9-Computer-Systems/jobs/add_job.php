<?php include("../config/db.php"); ?>

<h2>Add Repair Job</h2>

<form method="post">
    <input type="text" name="job_no" placeholder="Job Number" required><br><br>
    <input type="text" name="phone" placeholder="Customer Phone" required><br><br>
    <input type="date" name="job_date" required><br><br>
    <select name="status">
        <option>Pending</option>
        <option>In Progress</option>
        <option>Completed</option>
    </select><br><br>
    <button type="submit" name="add">Add Job</button>
</form>

<?php
if (isset($_POST['add'])) {
    mysqli_query($conn, "INSERT INTO job(job_no, phone_number, job_date, job_status)
    VALUES(
        '{$_POST['job_no']}',
        '{$_POST['phone']}',
        '{$_POST['job_date']}',
        '{$_POST['status']}'
    )");
    echo "Job Added";
}
?>
