<?php
// 1. Set the PHP timezone to Sri Lanka (Crucial for OTP logic)
date_default_timezone_set('Asia/Colombo');

// 2. Database Connection Details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "servidedb";

// 3. Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// 4. Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// 5. Sync MySQL session time with the PHP timezone (+05:30 for Sri Lanka)
$conn->query("SET time_zone = '+05:30'");
?>