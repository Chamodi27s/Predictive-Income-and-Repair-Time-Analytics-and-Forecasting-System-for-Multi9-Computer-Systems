<?php
// 1. Set the PHP timezone to Sri Lanka (Crucial for OTP logic)
date_default_timezone_set('Asia/Colombo');

// 2. Database Connection Details
$servername = "localhost";
$username = "root";
$dbname = "servidedb";

// Try connecting with 'admin123' first, then fallback to empty string '' (XAMPP default)
$passwords_to_try = ["admin123", ""];
$conn = null;

foreach ($passwords_to_try as $password) {
    try {
        $test_conn = @new mysqli($servername, $username, $password, $dbname);
        if (!$test_conn->connect_error) {
            $conn = $test_conn;
            break;
        }
    } catch (Throwable $e) {
        // Continue to try next password
    }
}

// Check connection
if (!$conn || $conn->connect_error) {
    die("Database Connection Failed. Please check MySQL server status and root password in db_config.php.");
}

// Sync MySQL session time with the PHP timezone (+05:30 for Sri Lanka)
$conn->query("SET time_zone = '+05:30'");
?>