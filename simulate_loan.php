<?php
require_once 'db.php';

echo "<h2>Starting System Stress Test...</h2>";

// 1. Create a Test Customer if one doesn't exist
$check_cust = $conn->query("SELECT customer_id FROM customers LIMIT 1");
if($check_cust->num_rows == 0) {
    $conn->query("INSERT INTO users (email, password, role) VALUES ('test@pawn.com', '123', 'customer')");
    $user_id = $conn->insert_id;
    $conn->query("INSERT INTO customers (user_id, first_name, last_name, contact_number) VALUES ($user_id, 'Juan', 'Dela Cruz', '09123456789')");
    $customer_id = $conn->insert_id;
    echo "✅ Test Customer Created.<br>";
} else {
    $customer_id = $check_cust->fetch_assoc()['customer_id'];
    echo "ℹ️ Using Existing Customer.<br>";
}

// 2. Create a Test Category if one doesn't exist
$check_cat = $conn->query("SELECT category_id FROM categories LIMIT 1");
if($check_cat->num_rows == 0) {
    $conn->query("INSERT INTO categories (category_name, default_interest_rate) VALUES ('Gold Jewelry', 3.50)");
    $category_id = $conn->insert_id;
    echo "✅ Test Category Created.<br>";
} else {
    $category_id = $check_cat->fetch_assoc()['category_id'];
    echo "ℹ️ Using Existing Category.<br>";
}

// 3. GENERATE THE LOAN (The big test)
$ticket = "TKT-" . time(); // Unique ticket number
$sql = "INSERT INTO loans (customer_id, pawn_ticket_number, item_name, principal_amount, interest_rate, status) 
        VALUES ($customer_id, '$ticket', 'Testing 18k Ring', 5000.00, 3.50, 'active')";

if ($conn->query($sql) === TRUE) {
    echo "<h1>🚀 SUCCESS!</h1>";
    echo "A test loan of ₱5,000.00 has been added to the database.<br>";
    echo "<a href='super_test.php' style='padding:10px; background:red; color:white; text-decoration:none;'>GO BACK TO DASHBOARD</a>";
} else {
    echo "❌ ERROR: " . $conn->error;
}
?>