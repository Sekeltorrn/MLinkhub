<?php
header('Content-Type: application/json');
error_reporting(0);

// Use your actual connection file
require_once '../db.php'; 

if (isset($_GET['branch_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['branch_id']);
    
    // Updated to match your 'describe branches' results
    $query = "SELECT mon_sat_open, mon_sat_close, sun_open, sun_close 
              FROM branches 
              WHERE branch_id = '$id'";
              
    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Branch not found']);
    }
} else {
    echo json_encode(['error' => 'Missing ID']);
}
?>