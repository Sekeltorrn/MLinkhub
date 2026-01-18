<?php
require_once 'db.php';

if (isset($_GET['id'])) {
    $payment_id = $_GET['id'];

    // 1. Update the Payment Status to 'rejected'
    // We do NOT update the loan table here because the payment was invalid.
    $sql = "UPDATE payments SET status = 'rejected' WHERE payment_id = '$payment_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Payment Rejected. No changes were made to the loan due date.');
                window.location.href='admin_confirm_payments.php';
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: admin_confirm_payments.php");
}
?>