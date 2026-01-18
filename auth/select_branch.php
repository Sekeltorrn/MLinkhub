<?php
session_start();
require_once '../db.php';

// If not logged in or not an employee, kick back to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$error = "";

// Fetch the branch details for the confirmation box
$stmt = $conn->prepare("SELECT branch_name FROM branches WHERE branch_id = ?");
$stmt->bind_param("i", $_SESSION['temp_branch_id']);
$stmt->execute();
$branch = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_branch = $_POST['branch_id'];

    // Security: Only allow them into their assigned branch
    if ($selected_branch == $_SESSION['temp_branch_id']) {
        $_SESSION['branch_id'] = $selected_branch;
        unset($_SESSION['temp_branch_id']); // Clear the temp variable
        header("Location: /employee/dashboard.php");
        exit();
    } else {
        $error = "You are not authorized to access this branch terminal.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Verification - Mlinkhub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .matte-finish { background: linear-gradient(135deg, #961e1e 0%, #7a1818 100%); }
    </style>
</head>
<body class="matte-finish min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-10 text-center">
        <div class="bg-red-50 size-16 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-red-600 text-3xl">location_on</span>
        </div>
        
        <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Terminal Access</h1>
        <p class="text-gray-400 text-sm mt-2 mb-8">Confirm your assigned workstation.</p>

        <?php if($error): ?>
            <p class="text-red-600 text-[11px] font-bold mb-4"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="bg-gray-50 border border-gray-100 p-6 rounded-2xl">
                <p class="text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">Active Assignment</p>
                <p class="text-lg font-bold text-gray-800"><?= htmlspecialchars($branch['branch_name'] ?? 'Unknown Branch') ?></p>
            </div>

            <input type="hidden" name="branch_id" value="<?= $_SESSION['temp_branch_id'] ?>">

            <button type="submit" class="w-full h-14 bg-[#004ce6] hover:bg-blue-700 text-white rounded-xl font-bold text-sm uppercase tracking-widest transition-all shadow-lg shadow-blue-200">
                Confirm & Open Dashboard
            </button>
            
            <a href="logout.php" class="block text-[11px] font-bold text-gray-400 hover:text-red-600 transition-colors uppercase">Cancel Login</a>
        </form>
    </div>
</body>
</html>