<?php
// 1. Set the PHP timezone to Sri Lanka (Crucial for OTP logic)
date_default_timezone_set('Asia/Colombo');

// 2. Database Connection Details
$servername = "localhost";
$dbname = "servidedb";

// Credentials to try (Primary: multi9 / multi912#)
$credentials_to_try = [
    ['username' => 'multi9',  'password' => 'multi912#'],
    ['username' => 'root',    'password' => 'admin123'],
    ['username' => 'root',    'password' => '']
];

$conn = null;

foreach ($credentials_to_try as $cred) {
    try {
        $test_conn = @new mysqli($servername, $cred['username'], $cred['password'], $dbname);
        if (!$test_conn->connect_error) {
            $conn = $test_conn;
            break;
        }
    } catch (Throwable $e) {
        // Continue trying next credentials set
    }
}

// Check connection
if (!$conn || $conn->connect_error) {
    die("Database Connection Failed. Please check MySQL user credentials and server status.");
}

// Sync MySQL session time with the PHP timezone (+05:30 for Sri Lanka)
$conn->query("SET time_zone = '+05:30'");
?>