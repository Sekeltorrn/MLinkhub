<?php
session_start();
// Adjust path if needed (e.g. if inside /auth/ folder, use ../db.php)
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. DATA RETENTION
    $_SESSION['form_data'] = $_POST;
    unset($_SESSION['form_data']['password']);
    unset($_SESSION['form_data']['confirm_password']);

    // 2. EXTRACT & CLEAN DATA
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $first_name  = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $gender      = $_POST['gender'] ?? '';
    $mobile      = trim($_POST['mobile'] ?? '');
    $pass        = $_POST['password'] ?? '';
    $conf        = $_POST['confirm_password'] ?? '';

    // Format the Date of Birth (YYYY-MM-DD)
    $dob_year  = $_POST['dob_year'] ?? '';
    $dob_month = $_POST['dob_month'] ?? '';
    $dob_day   = $_POST['dob_day'] ?? '';
    $birth_date = "$dob_year-$dob_month-$dob_day";

    // 3. BACKEND AGE VERIFICATION
    try {
        $birthDateObj = new DateTime($birth_date);
        $age = (new DateTime())->diff($birthDateObj)->y;

        if ($age < 18) {
            $_SESSION['error'] = "Access Denied: You must be 18 years or older.";
            header("Location: signup.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Invalid Date of Birth format.";
        header("Location: signup.php");
        exit();
    }

    // 4. BACKEND PASSWORD VALIDATION
    if ($pass !== $conf) {
        $_SESSION['error'] = "Confirmation failed: Passwords do not match.";
        header("Location: signup.php");
        exit();
    }

    // Security Checklist (Matches JS exactly)
    $isStrong = preg_match('@[A-Z]@', $pass) &&          // Uppercase
                preg_match('@[a-z]@', $pass) &&          // Lowercase
                preg_match('@[0-9]@', $pass) &&          // Number
                preg_match('@[^A-Za-z0-9]@', $pass) &&   // Special Char
                strlen($pass) >= 8;                      // Length

    if (!$isStrong) {
        $_SESSION['error'] = "Security Alert: Password does not meet all complexity requirements.";
        header("Location: signup.php");
        exit();
    }

    // 5. DATABASE TRANSACTION
    $conn->begin_transaction();

    try {
        // CHECK DUPLICATES
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception("Account already exists with this Email or Username.");
        }

        // INSERT CREDENTIALS
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $stmt1 = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
        $stmt1->bind_param("sss", $username, $email, $hashed_pass);
        $stmt1->execute();
        
        $new_user_id = $conn->insert_id;

        // INSERT PROFILE
        $stmt2 = $conn->prepare("INSERT INTO customers (user_id, first_name, middle_name, last_name, gender, birth_date, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'unverified')");
        $stmt2->bind_param("issssss", $new_user_id, $first_name, $middle_name, $last_name, $gender, $birth_date, $mobile);
        $stmt2->execute();

        $conn->commit();
        
        // SUCCESS
        unset($_SESSION['form_data']); 
        header("Location: login.php?signup=success");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Registration Failed: " . $e->getMessage();
        header("Location: signup.php");
        exit();
    }
}
?>