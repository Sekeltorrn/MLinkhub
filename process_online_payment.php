<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_id = $_POST['loan_id'];
    $amount = $_POST['amount'];
    $payment_type = $_POST['payment_type'];
    
    // Generate a fake GCash Reference Number for the receipt
    $ref_no = "GC-" . strtoupper(substr(md5(time()), 0, 10));

    // Get customer_id from the loan
    $loan_data = $conn->query("SELECT customer_id FROM loans WHERE loan_id = '$loan_id'")->fetch_assoc();
    $customer_id = $loan_data['customer_id'];

    // Insert into payments table as PENDING
    $sql = "INSERT INTO payments (
                loan_id, 
                customer_id, 
                amount, 
                payment_type, 
                payment_method, 
                reference_number, 
                status
            ) VALUES (
                '$loan_id', 
                '$customer_id', 
                '$amount', 
                '$payment_type', 
                'gcash', 
                '$ref_no', 
                'pending'
            )";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('GCash Payment Successful! Reference: $ref_no. Your ticket will be updated once the admin confirms the transaction.');
                window.location.href='super_test.php'; // Or your customer dashboard
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>