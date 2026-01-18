<?php
require_once 'db.php';

// Enable error reporting to catch any hidden issues
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_id      = $_POST['loan_id'];
    $amount       = $_POST['amount_paid'];
    $payment_type = $_POST['payment_type']; // We will map this to your ENUM
    
    // 1. First, we need to find the CUSTOMER_ID associated with this loan
    $loan_info = $conn->query("SELECT customer_id FROM loans WHERE loan_id = '$loan_id'")->fetch_assoc();
    $customer_id = $loan_info['customer_id'];

    // 2. Insert into the PAYMENTS table using YOUR specific columns
    // Using 'interest' and 'full_redemption' to match your ENUM
    $pay_sql = "INSERT INTO payments (
                    loan_id, 
                    customer_id, 
                    amount, 
                    payment_type, 
                    payment_method, 
                    remarks
                ) VALUES (
                    '$loan_id', 
                    '$customer_id', 
                    '$amount', 
                    '$payment_type', 
                    'cash', 
                    'Processed via Admin Dashboard'
                )";
    
    if ($conn->query($pay_sql) === TRUE) {
        
        // 3. Update the LOAN table based on the type
        if ($payment_type == 'interest') {
            // RENEWAL: Push the due date forward by 30 days
            $update_sql = "UPDATE loans 
                           SET due_date = DATE_ADD(due_date, INTERVAL 30 DAY) 
                           WHERE loan_id = '$loan_id'";
        } 
        elseif ($payment_type == 'full_redemption') {
            // REDEMPTION: Close the loan
            $update_sql = "UPDATE loans SET status = 'paid' WHERE loan_id = '$loan_id'";
        }

        if (isset($update_sql) && $conn->query($update_sql) === TRUE) {
            echo "<script>
                    alert('Payment Successful! Digital Ticket Updated.');
                    window.location.href='super_test.php';
                  </script>";
        } else {
             // If it's just a principal payment with no date change, just redirect
             echo "<script>window.location.href='super_test.php';</script>";
        }
    } else {
        echo "Database Error: " . $conn->error;
    }
}
?>