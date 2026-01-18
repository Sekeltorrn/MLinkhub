<?php
// employee/process_ticket.php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id   = $_SESSION['branch_id'];
    $branch_name = $_SESSION['branch_name'] ?? "ML";
    
    // 1. Inputs from the Form
    $customer_id      = $_POST['customer_id'];
    $item_type        = $_POST['item_type']; // 'jewelry' or 'non-jewelry'
    $item_name        = $_POST['item_name']; // e.g., "18k Gold Ring"
    
    // NOTE: We map the 'ID Details' input to the 'item_description' column 
    // to satisfy Sec. 4323P.q (ID Presented)
    $id_proof_info    = $_POST['item_description']; 
    
    $item_condition   = $_POST['item_condition_text']; // Captured from JS logic
    $principal        = $_POST['principal_amount'];
    $net_proceeds     = $_POST['net_proceeds'];
    $service_charge   = $_POST['service_charge']; // Captured from JS logic (Max 5.00)
    $storage_location = $_POST['storage_location'];
    
    // 2. Categorization
    // 1 = Jewelry, 2 = Non-Jewelry/Gadget
    $category_id = ($item_type === 'jewelry') ? 1 : 2;

    // 3. Date Logic (Compliance with Sec. 4323P)
    $loan_date   = date('Y-m-d');
    $due_date    = date('Y-m-d', strtotime('+30 days')); // Maturity Date
    $expiry_date = date('Y-m-d', strtotime('+120 days')); // Redemption Expiry (30 Days + 90 Days Grace)
    
    // 4. Generate Ticket Number
    // Format: ML-[BRANCH]-YYYY-[RANDOM]
    $ticket_no = "ML-" . strtoupper(substr($branch_name, 0, 3)) . "-" . date('Y') . "-" . rand(1000, 9999);

    // 5. Image Upload Handler
    $img_path = NULL;
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $target_dir = "../uploads/items/";
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Clean filename using ticket number
        $file_ext = pathinfo($_FILES["item_image"]["name"], PATHINFO_EXTENSION);
        $filename = $ticket_no . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_dir . $filename)) {
            $img_path = "/uploads/items/" . $filename;
        }
    }

    // 6. Insert into Database
    // We explicitly set interest_rate to 3.00 as per your settings
    $sql = "INSERT INTO loans (
        customer_id, 
        branch_id, 
        category_id, 
        pawn_ticket_number, 
        item_name, 
        item_description, 
        item_condition, 
        principal_amount, 
        net_proceeds, 
        interest_rate, 
        service_charge,
        loan_date, 
        due_date, 
        expiry_date, 
        status, 
        item_image, 
        storage_location
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 3.00, ?, ?, ?, ?, 'active', ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Bind Parameters
    // Types: i=int, s=string, d=decimal
    // Count matches the ? placeholders exactly
    $stmt->bind_param("iiisssdddssssss", 
        $customer_id, 
        $branch_id, 
        $category_id, 
        $ticket_no, 
        $item_name, 
        $id_proof_info, 
        $item_condition, 
        $principal, 
        $net_proceeds,
        $service_charge, 
        $loan_date, 
        $due_date, 
        $expiry_date,
        $img_path, 
        $storage_location
    );

    // 7. Execute and Redirect
    if ($stmt->execute()) {
        // Redirect to Inventory Page with Success Flag
        header("Location: loans.php?new_ticket=" . $ticket_no);
        exit();
    } else {
        // Error Handling
        die("System Error (Database Insert Failed): " . $stmt->error);
    }
} else {
    // If user tries to access this page directly without POST
    header("Location: create_ticket.php");
    exit();
}
?>