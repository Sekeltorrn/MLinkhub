<?php
require_once 'db.php';

// Check if data was actually sent via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Collect data from the form
    $user_id = $_POST['user_id']; // In the future, this comes from $_SESSION['user_id']
    $address = $conn->real_escape_string($_POST['address']);
    $id_type = $conn->real_escape_string($_POST['id_type']);
    
    // 2. Update or Insert into the CUSTOMERS table
    // This completes the "Second Half" of the registration
    $sql = "INSERT INTO customers (user_id, address, contact_number) 
            VALUES ('$user_id', '$address', 'Update Pending') 
            ON DUPLICATE KEY UPDATE address='$address'";

    if ($conn->query($sql) === TRUE) {
        // 3. SUCCESS: Send them to a "Thank You" or Dashboard
        echo "<script>alert('Verification Submitted! Admin will review your ID.'); window.location.href='super_test.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    // If someone tries to open this file directly, kick them back to the form
    header("Location: submit_verification.php");
    exit();
}
?>