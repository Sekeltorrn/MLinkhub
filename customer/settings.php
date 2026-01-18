<?php
include './includes/header.php';
$user_id = $_SESSION['user_id'];
$message = "";

// 1. Fetch current customer and user data
$query = $conn->prepare("
    SELECT u.username, u.email, c.first_name, c.last_name, c.contact_number, c.address 
    FROM users u 
    JOIN customers c ON u.user_id = c.user_id 
    WHERE u.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$user_data = $query->get_result()->fetch_assoc();

// 2. Handle Password Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_pwd = $_POST['new_password'];
    $confirm_pwd = $_POST['confirm_password'];

    if ($new_pwd === $confirm_pwd) {
        $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $hashed_pwd, $user_id);
        
        if ($update->execute()) {
            $message = "<div class='bg-emerald-50 text-emerald-700 p-4 rounded-lg mb-6 border border-emerald-100 flex items-center gap-2'>
                            <span class='material-symbols-outlined'>check_circle</span> Password updated!
                        </div>";
        }
    } else {
        $message = "<div class='bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-100'>Passwords do not match.</div>";
    }
}
?>

<main class="p-8">
    <div class="max-w-5xl mx-auto space-y-8">
        
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Settings</h2>
                <p class="text-sm text-gray-500">Manage your vault access and profile records.</p>
            </div>
        </div>

        <?php echo $message; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="header-gradient h-24 relative">
                        <div class="absolute -bottom-6 left-8 size-20 rounded-xl bg-white p-1 shadow-md">
                            <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 font-bold text-2xl">
                                <?php echo strtoupper(substr($user_data['first_name'], 0, 1)); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-10 px-8 pb-8 space-y-8">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($user_data['first_name'] . " " . $user_data['last_name']); ?></h3>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-widest">Verified Customer Account</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-12 border-t border-gray-50 pt-8">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email Address</label>
                                <p class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($user_data['email']); ?></p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mobile Number</label>
                                <p class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($user_data['contact_number']); ?></p>
                            </div>
                            <div class="sm:col-span-2 space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Verified Address</label>
                                <p class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($user_data['address']); ?></p>
                            </div>
                        </div>

                        <div class="bg-blue-50/50 p-4 rounded-lg border border-blue-100 flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary-blue text-lg">info</span>
                            <p class="text-[11px] text-primary-blue leading-relaxed">
                                <strong>Need to change your profile?</strong> Since this is a pawn system, name and address changes must be done in person at the MLINKHUB branch to verify your identity.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm card-hover">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-blue text-lg">shield_person</span>
                        Security Settings
                    </h3>
                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">New Password</label>
                            <input type="password" name="new_password" class="w-full mt-1 border-gray-200 rounded-md text-sm focus:ring-primary-blue focus:border-primary-blue" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Confirm Password</label>
                            <input type="password" name="confirm_password" class="w-full mt-1 border-gray-200 rounded-md text-sm focus:ring-primary-blue focus:border-primary-blue" placeholder="••••••••" required>
                        </div>
                        <button type="submit" name="update_password" class="w-full bg-matte-red hover:bg-dark-red text-white font-bold py-3 rounded-md transition-all text-[11px] uppercase tracking-widest shadow-lg shadow-red-900/10">
                            Update Password
                        </button>
                    </form>
                </div>

                <div class="bg-gray-900 p-6 rounded-xl border border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Account Status</h4>
                        <span class="size-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    </div>
                    <p class="text-sm font-bold text-white tracking-tight">Active & Secured</p>
                    <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-tighter">Last Login: <?php echo date('M d, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include './includes/footer.php'; ?>