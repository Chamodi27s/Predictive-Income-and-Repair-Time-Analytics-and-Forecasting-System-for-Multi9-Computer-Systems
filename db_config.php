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

// Auto-migrate missing tables and columns if not present
$migrations = [
    "CREATE TABLE IF NOT EXISTS sms_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_device_id INT,
        phone_number VARCHAR(20),
        message TEXT,
        status VARCHAR(50),
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "ALTER TABLE job_device ADD COLUMN item_model VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE job_device ADD COLUMN repair_path VARCHAR(255) DEFAULT 'Carry-In'",
    "ALTER TABLE job_device ADD COLUMN solution VARCHAR(255) DEFAULT NULL"
];

foreach ($migrations as $sql) {
    try {
        $conn->query($sql);
    } catch (Throwable $e) {
        // Ignore if already exists
    }
}
?>