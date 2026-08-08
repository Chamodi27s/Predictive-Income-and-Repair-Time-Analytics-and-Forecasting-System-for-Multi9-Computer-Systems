<?php
include 'db_config.php';
$id = mysqli_real_escape_string($conn, $_GET['id']);
$res = mysqli_query($conn, "SELECT sent_at, message, status FROM sms_history WHERE job_device_id = '$id' ORDER BY sent_at DESC");

echo "<table style='width:100%; font-size:12px; border-collapse:collapse;'>";
echo "<tr style='background:#eee;'><th>Date</th><th>Message</th><th>Status</th></tr>";
while($row = mysqli_fetch_assoc($res)) {
    echo "<tr><td>{$row['sent_at']}</td><td>{$row['message']}</td><td>{$row['status']}</td></tr>";
}
echo "</table>";
?>