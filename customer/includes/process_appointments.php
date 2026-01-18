<?php
// includes/process_appointments.php

// 1. DATABASE & SESSION SETUP
require_once __DIR__ . '/../../db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$customer_id = $_SESSION['user_id'];

// =========================================================
// 2. THE "AUTONOMOUS SWEEP" (Auto-Expire Logic)
// =========================================================
// Runs silently to clean up late appointments
$sweep_query = "UPDATE appointments 
                SET status = 'expired' 
                WHERE status = 'pending' 
                AND CONCAT(appointment_date, ' ', appointment_time) < (NOW() - INTERVAL 15 MINUTE)";
mysqli_query($conn, $sweep_query);


// =========================================================
// 3. HANDLE BOOKING SUBMISSION
// =========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_now'])) {
    $branch_id = $_POST['branch_id']; 
    $service = $_POST['service_type'];
    $date = $_POST['appt_date']; 
    $time = $_POST['appt_time']; 
    $notes = $_POST['notes'];

    $is_allowed = true;
    $error_reason = "";

    // --- [DEFENSE LAYER 0] TIME TRAVEL CHECK (NEW) ---
    // Rule: Cannot book a time that has already passed today.
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    if ($date < $current_date) {
        $is_allowed = false;
        $error_reason = "You cannot book for a past date.";
    } elseif ($date == $current_date && $time < $current_time) {
        $is_allowed = false;
        $error_reason = "You cannot book a time that has already passed today. Please choose a future slot.";
    }

    // --- [DEFENSE LAYER 1] GLOBAL STATION CAPACITY ---
    // Rule: Only 2 customers allowed per specific time slot per branch.
    if ($is_allowed) {
        $cap_sql = "SELECT COUNT(*) as total FROM appointments 
                    WHERE branch_id = ? 
                    AND appointment_date = ? 
                    AND appointment_time = ? 
                    AND status NOT IN ('cancelled', 'expired', 'no show')";
        
        $cap_stmt = $conn->prepare($cap_sql);
        $cap_stmt->bind_param("iss", $branch_id, $date, $time);
        $cap_stmt->execute();
        $cap_res = $cap_stmt->get_result()->fetch_assoc();

        if ($cap_res['total'] >= 2) {
            $is_allowed = false;
            $error_reason = "This time slot is fully occupied. Please select a time at least 30 minutes before or after this one.";
        }
    }

    // --- [DEFENSE LAYER 2] INDIVIDUAL LIMITS ---
    // Rule: Max 2 appointments per day + 2 Hour Gap.
    if ($is_allowed) {
        $check_sql = "SELECT appointment_time FROM appointments 
                      WHERE customer_id = ? 
                      AND appointment_date = ? 
                      AND status NOT IN ('cancelled', 'expired', 'no show')";
        
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $customer_id, $date);
        $check_stmt->execute();
        $existing_appts = $check_stmt->get_result();

        // A. Max 2 Per Day Rule
        if ($existing_appts->num_rows >= 2) {
            $is_allowed = false;
            $error_reason = "Daily limit reached. You cannot book more than 2 appointments on the same day.";
        } 
        
        // B. 2-Hour Gap Rule
        if ($is_allowed) {
            $new_time_stamp = strtotime($time); 
            
            while ($row = $existing_appts->fetch_assoc()) {
                $existing_time_stamp = strtotime($row['appointment_time']);
                $diff_hours = abs($new_time_stamp - $existing_time_stamp) / 3600;
                
                if ($diff_hours < 2) {
                    $is_allowed = false;
                    $error_reason = "Gap Rule: Appointments on the same day must be at least 2 hours apart.";
                    break;
                }
            }
        }
    }

    // --- [DEFENSE LAYER 3] OPERATING HOURS ---
    if ($is_allowed) {
        $h_stmt = $conn->prepare("SELECT branch_name, mon_sat_open, mon_sat_close, sun_open, sun_close FROM branches WHERE branch_id = ?");
        $h_stmt->bind_param("i", $branch_id);
        $h_stmt->execute();
        $branch = $h_stmt->get_result()->fetch_assoc();

        $day_of_week = date('w', strtotime($date)); // 0 = Sunday

        if ($day_of_week == 0) { 
            if ($branch['sun_open'] == '00:00:00' || empty($branch['sun_open'])) {
                $is_allowed = false;
                $error_reason = $branch['branch_name'] . " is closed on Sundays.";
            } elseif ($time < $branch['sun_open'] || $time > $branch['sun_close']) {
                $is_allowed = false;
                $error_reason = "Sunday hours: " . date('h:i A', strtotime($branch['sun_open'])) . " - " . date('h:i A', strtotime($branch['sun_close']));
            }
        } else {
            if ($time < $branch['mon_sat_open'] || $time > $branch['mon_sat_close']) {
                $is_allowed = false;
                $error_reason = "Branch hours: " . date('h:i A', strtotime($branch['mon_sat_open'])) . " - " . date('h:i A', strtotime($branch['mon_sat_close']));
            }
        }
    }

    // --- FINAL DECISION ---
    if (!$is_allowed) {
        $_SESSION['booking_error'] = $error_reason;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Insert if valid
    $sql = "INSERT INTO appointments (customer_id, branch_id, service_type, appointment_date, appointment_time, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iissss", $customer_id, $branch_id, $service, $date, $time, $notes);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['booking_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// =========================================================
// 4. HANDLE CANCELLATION
// =========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_id'])) {
    $appt_to_cancel = $_POST['cancel_id'];
    $cancel_q = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND customer_id = ?");
    $cancel_q->bind_param("ii", $appt_to_cancel, $customer_id);
    if ($cancel_q->execute()) {
        $_SESSION['cancel_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// =========================================================
// 5. FETCH HISTORY
// =========================================================
$history_q = $conn->prepare("SELECT a.*, b.branch_name 
                             FROM appointments a 
                             JOIN branches b ON a.branch_id = b.branch_id 
                             WHERE a.customer_id = ? 
                             ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$history_q->bind_param("i", $customer_id);
$history_q->execute();
$appointments_log = $history_q->get_result();
?>