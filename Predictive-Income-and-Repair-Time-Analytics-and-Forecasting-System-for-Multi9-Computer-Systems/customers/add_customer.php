<?php include("../config/db.php"); ?>

<h2>Add Customer</h2>

<form method="post">
    <input type="text" name="phone" placeholder="Phone Number" required><br><br>
    <input type="text" name="name" placeholder="Customer Name" required><br><br>
    <input type="text" name="address" placeholder="Address"><br><br>
    <input type="email" name="email" placeholder="Email"><br><br>
    <button type="submit" name="save">Save</button>
</form>

<?php
if (isset($_POST['save'])) {
    $sql = "INSERT INTO customer VALUES(
        '{$_POST['phone']}',
        '{$_POST['name']}',
        '{$_POST['address']}',
        '{$_POST['email']}'
    )";
    mysqli_query($conn, $sql);
    echo "Customer Added Successfully";
}
?>
