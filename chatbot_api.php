<?php
session_start();
include 'db_config.php'; 

// User ewana message eka (Lowercase karala gannawa lesiyata)
$msg = isset($_POST['message']) ? strtolower(trim($_POST['message'])) : "";

date_default_timezone_set('Asia/Colombo');
$today = date('Y-m-d');

if (empty($msg)) {
    echo "Please ask me something about the system.";
    exit();
}

// =================================================================
// 1. DYNAMIC DATA QUERIES (Database eken kelinma ganna ewa)
// =================================================================

// Pending Jobs
if (strpos($msg, 'pending') !== false || strpos($msg, 'waiting') !== false) {
    $c = $conn->query("SELECT COUNT(*) c FROM job_device WHERE device_status='Pending'")->fetch_assoc()['c'];
    echo "There are <b>$c Pending Repairs</b> waiting for action.";
}
// Income / Revenue
elseif (strpos($msg, 'income') !== false || strpos($msg, 'revenue') !== false || strpos($msg, 'collection') !== false) {
    $row = $conn->query("SELECT SUM(income) t FROM cashbook WHERE DATE(date)='$today'")->fetch_assoc();
    $tot = number_format($row['t'] ?? 0, 2);
    echo "Today's total revenue is <b>Rs. $tot</b> 💰";
}
// Low Stock
elseif (strpos($msg, 'low stock') !== false || strpos($msg, 'stock') !== false) {
    $c = $conn->query("SELECT COUNT(*) c FROM stock WHERE quantity <= 5")->fetch_assoc()['c'];
    echo "There are <b>$c items</b> running low on stock. Please check the Stock page.";
}
// Customer Count
elseif (strpos($msg, 'how many customer') !== false || strpos($msg, 'total customer') !== false) {
    $c = $conn->query("SELECT COUNT(*) c FROM customer")->fetch_assoc()['c'];
    echo "We have <b>$c registered customers</b> in the system.";
}

// =================================================================
// 2. SYSTEM KNOWLEDGE BASE (System eka gana prashna walata uththara)
// =================================================================

// Introduction
elseif (strpos($msg, 'who are you') !== false || strpos($msg, 'what is this') !== false) {
    echo "I am the <b>Multi9 System Assistant</b>. This is a Computer Repair Management System designed to handle jobs, inventory, and billing efficiently.";
}

// Add Customer
elseif (strpos($msg, 'add customer') !== false || strpos($msg, 'register') !== false || strpos($msg, 'new client') !== false) {
    echo "To add a new customer:<br>1. Go to the <b>'Register'</b> page.<br>2. Fill in the name, phone number, and address.<br>3. Click 'Save'.";
}

// Create Job / Order
elseif (strpos($msg, 'create job') !== false || strpos($msg, 'new job') !== false || strpos($msg, 'add job') !== false || strpos($msg, 'order') !== false) {
    echo "To create a new repair job:<br>Go to <b>'Order' > 'Add Job'</b>. Select the customer and enter the device details and fault description.";
}

// Warranty
elseif (strpos($msg, 'warranty') !== false) {
    echo "You can check warranty status in the <b>'Warranty'</b> page. The system tracks warranty periods for all repaired devices.";
}

// Backup
elseif (strpos($msg, 'backup') !== false || strpos($msg, 'save data') !== false) {
    echo "You can download a database backup from the <b>User Menu (Top Right) > Database Backup</b>.";
}

// Reports
elseif (strpos($msg, 'report') !== false || strpos($msg, 'print') !== false) {
    echo "Go to the <b>'Report'</b> page to generate monthly or daily reports. You can print job reports and income summaries there.";
}

// Payments / Billing
elseif (strpos($msg, 'payment') !== false || strpos($msg, 'bill') !== false || strpos($msg, 'invoice') !== false) {
    echo "Payments are managed in the <b>'Payment'</b> or <b>'Cashbook'</b> section. You can add payments when a job is completed.";
}

// Forgot Password
elseif (strpos($msg, 'password') !== false || strpos($msg, 'login') !== false) {
    echo "If you forgot your password, please contact the System Administrator or reset it directly from the database (admin access required).";
}

// Developer Info
elseif (strpos($msg, 'developer') !== false || strpos($msg, 'created by') !== false) {
    echo "This system was developed for the Multi9 Computer Shop project.";
}

// Thanks / Greetings
elseif (strpos($msg, 'thank') !== false || strpos($msg, 'good') !== false) {
    echo "You're welcome! Happy to help. 😊";
}
elseif (strpos($msg, 'hello') !== false || strpos($msg, 'hi') !== false) {
    echo "Hello! 👋 Ask me anything about adding customers, creating jobs, or checking stock.";
}

// =================================================================
// 3. FALLBACK (Danne nathi deyak ahuwoth)
// =================================================================
else {
    echo "I'm sorry, I don't have information about that yet. 🤖<br><br>
    Try asking:<br>
    - <i>'How to add customer?'</i><br>
    - <i>'How to create job?'</i><br>
    - <i>'Check income'</i><br>
    - <i>'Backup data'</i>";
}
?>