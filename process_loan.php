<?php
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Collect Form Data
    $customer_id = $_POST['customer_id'];
    $item_name   = $conn->real_escape_string($_POST['item_name']);
    $category_id = $_POST['category_id'];
    $amount      = $_POST['amount'];
    $interest    = $_POST['interest'];
    
    // 2. Generate Unique Pawn Ticket Number (e.g., PLS-17158293)
    $ticket_number = "PLS-" . strtoupper(substr(uniqid(), 7));
    
    // 3. Set Dates (Standard 30-day loan)
    $loan_date = date('Y-m-d');
    $due_date  = date('Y-m-d', strtotime('+30 days'));

    // 4. INSERT INTO LOANS TABLE
    $sql = "INSERT INTO loans (
                customer_id, 
                pawn_ticket_number, 
                item_name, 
                category_id, 
                principal_amount, 
                interest_rate, 
                loan_date, 
                due_date, 
                status
            ) VALUES (
                '$customer_id', 
                '$ticket_number', 
                '$item_name', 
                '$category_id', 
                '$amount', 
                '$interest', 
                '$loan_date', 
                '$due_date', 
                'active'
            )";

    if ($conn->query($sql) === TRUE) {
        // Success! Redirect to Dashboard
        echo "<script>
                alert('TICKET GENERATED: $ticket_number');
                window.location.href='super_test.php';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    header("Location: create_loan.php");
}
?>