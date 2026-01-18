<?php
session_start();
// Adjust path if your DB file is elsewhere
require_once '../db.php'; 

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. COLLECT DATA
    $loan_id      = $_POST['loan_id'];
    $customer_id  = $_POST['customer_id'];
    $amount       = $_POST['amount'];
    $pay_type     = $_POST['payment_type']; // 'interest', 'partial', 'full'
    $ref_no       = $_POST['ref_no'];       // The simulated Ref No.
    
    // Hardcoded values for the simulation
    $method       = "gcash"; 
    $status       = "pending"; // Critical: Admin must verify this later
    
    // 2. PREPARE THE INSERT
    // We insert into 'payments' table using 'reference_number' column
    $query = "INSERT INTO payments (
                loan_id, 
                customer_id, 
                amount, 
                payment_method, 
                reference_number, 
                payment_type, 
                status, 
                payment_date
              ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($query);
    
    // Bind Params: i=int, d=decimal/string, s=string
    // i i d s s s s
    $stmt->bind_param("iidssss", $loan_id, $customer_id, $amount, $method, $ref_no, $pay_type, $status);

    // 3. EXECUTE & REDIRECT
    if ($stmt->execute()) {
        // Success: Set session flag for the toast notification
        $_SESSION['booking_success'] = true; 
        
        // Redirect back to main payments page
        header("Location: payments.php");
        exit();
    } else {
        // Error Handling
        die("Error processing payment: " . $stmt->error);
    }
} else {
    // Block direct access
    header("Location: payments.php");
    exit();
}
?>