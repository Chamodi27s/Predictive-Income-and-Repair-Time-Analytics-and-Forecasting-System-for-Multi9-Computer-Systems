<?php
// කිසිදු හිස්තැනක් (Space) නොමැතිව පහත පරිදි ලියන්න
$conn = new mysqli("localhost", "root", "", "servidedb");

if ($conn->connect_error) { 
    die("සම්බන්ධතාවය බිඳ වැටුණි: " . $conn->connect_error); 
}
?>
