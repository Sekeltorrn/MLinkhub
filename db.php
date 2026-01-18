<?php
// Enable error reporting to find out why it's failing
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "sql211.infinityfree.com"; 
$username = "if0_40625987";             
$password = "7y6xRLqwckQ7qDO";        
$dbname = "if0_40625987_0099";   

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// =========================================================
// [TIMEZONE SYNC] - Philippines Standard Time (GMT+8)
// =========================================================

// 1. Set PHP's internal clock to Manila time
date_default_timezone_set('Asia/Manila');

// 2. Set the Database connection to Manila time (+08:00)
$conn->query("SET time_zone = '+08:00'");

// =========================================================
?>