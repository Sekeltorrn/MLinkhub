<?php
// Use the path style from your login.php for consistency
$db_path = '/home/vol13_3/infinityfree.com/if0_40625987/mlinkh.wuaze.com/htdocs/db.php';
require_once($db_path);

$message = "";
$error = "";

// 1. Fetch real branches from your Bulacan locations
$branches_query = $conn->query("SELECT branch_id, branch_name, location FROM branches ORDER BY branch_id ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $branch_id = intval($_POST['branch_id']);

    if (empty($username) || empty($email) || empty($password) || empty($branch_id)) {
        $error = "All fields are required.";
    } else {
        // Use password_hash to work with your login.php's password_verify()
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // SQL: role is hardcoded as 'employee' for this specific form
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, assigned_branch_id) VALUES (?, ?, ?, 'employee', ?)");
        $stmt->bind_param("sssi", $username, $email, $hashed_password, $branch_id);

        if ($stmt->execute()) {
            $message = "✅ Success! Staff account for <strong>$username</strong> created and assigned to Branch #$branch_id.";
        } else {
            $error = "❌ Database Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mlinkhub | Register Staff Terminal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0a1128; }
        .matte-card { background-color: #111d3d; border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-lg matte-card rounded-[2.5rem] p-10 shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-white text-2xl font-black uppercase tracking-tight">Staff Registration</h1>
            <p class="text-slate-400 text-xs mt-1 uppercase tracking-widest font-bold">Internal Provisioning Tool</p>
        </div>

        <?php if($message): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-4 rounded-xl mb-6">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs p-4 rounded-xl mb-6">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Username</label>
                    <input type="text" name="username" required placeholder="makati_staff" 
                           class="w-full bg-[#0a1128] border border-white/10 rounded-xl p-3 text-white text-sm focus:ring-1 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full bg-[#0a1128] border border-white/10 rounded-xl p-3 text-white text-sm focus:ring-1 focus:ring-red-500 outline-none">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                <input type="email" name="email" required placeholder="staff@mlinkh.wuaze.com" 
                       class="w-full bg-[#0a1128] border border-white/10 rounded-xl p-3 text-white text-sm focus:ring-1 focus:ring-red-500 outline-none">
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Branch Assignment</label>
                <select name="branch_id" required class="w-full bg-[#0a1128] border border-white/10 rounded-xl p-3 text-white text-sm focus:ring-1 focus:ring-red-500 outline-none appearance-none mt-1">
                    <option value="" disabled selected>Select from Bulacan Branches...</option>
                    <?php while($b = $branches_query->fetch_assoc()): ?>
                        <option value="<?= $b['branch_id'] ?>">
                            ID #<?= $b['branch_id'] ?>: <?= htmlspecialchars($b['branch_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <p class="text-[9px] text-slate-500 mt-2 px-1">Note: This will lock the employee's dashboard to this specific location.</p>
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-2xl uppercase tracking-widest text-xs transition-all shadow-lg shadow-red-900/40">
                Register Employee Account
            </button>
            
            <div class="text-center mt-6">
                <a href="auth/login.php" class="text-[10px] font-bold text-slate-500 hover:text-white uppercase transition-colors">Return to Security Login</a>
            </div>
        </form>
    </div>

</body>
</html>