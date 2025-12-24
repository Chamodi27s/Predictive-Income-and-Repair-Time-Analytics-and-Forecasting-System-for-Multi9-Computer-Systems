<?php include("../config/db.php"); ?>

<h2>Cashbook</h2>

<table border="1">
<tr>
    <th>Date</th>
    <th>Invoice No</th>
    <th>Income</th>
    <th>Balance</th>
</tr>

<?php
$result = mysqli_query($conn, "SELECT * FROM cashbook");
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$row['date']}</td>
        <td>{$row['invoice_no']}</td>
        <td>{$row['income']}</td>
        <td>{$row['balance']}</td>
    </tr>";
}
?>
</table>
