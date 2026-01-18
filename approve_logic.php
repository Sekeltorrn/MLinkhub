<?php
require_once 'db.php';

if (isset($_GET['id']) && isset($_GET['loan_id'])) {
    $payment_id = $_GET['id'];
    $loan_id = $_GET['loan_id'];
    $type = $_GET['type']; // 'interest', 'full_redemption', etc.

    // 1. Update the Payment Status to 'confirmed'
    $conn->query("UPDATE payments SET status = 'confirmed' WHERE payment_id = '$payment_id'");

    // 2. Update the Loan based on payment type
    if ($type == 'interest') {
        // Renewal: Add 30 days to the current due date
        $update_loan = "UPDATE loans SET due_date = DATE_ADD(due_date, INTERVAL 30 DAY) WHERE loan_id = '$loan_id'";
    } elseif ($type == 'full_redemption') {
        // Close the loan
        $update_loan = "UPDATE loans SET status = 'paid' WHERE loan_id = '$loan_id'";
    }

    if (isset($update_loan) && $conn->query($update_loan) === TRUE) {
        echo "<script>
                alert('Payment Confirmed! Loan record updated.');
                window.location.href='admin_confirm_payments.php';
              </script>";
    }
}
?>