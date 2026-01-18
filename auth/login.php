<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NEW: Clear errors and data if arriving fresh
if (!isset($_GET['signup']) && !isset($_POST['username'])) {
    unset($_SESSION['error']);
    // You can also unset specific login form data if you have it
}

// Absolute path for InfinityFree
$db_path = '/home/vol13_3/infinityfree.com/if0_40625987/mlinkh.wuaze.com/htdocs/db.php';
require_once($db_path);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? ''); 
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $email_regex = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!preg_match($email_regex, $email)) {
        $error = "Please provide a valid email address format.";
    } else {
        // UPDATED: Added assigned_branch_id to the query
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, assigned_branch_id FROM users WHERE email = ? AND username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                // Temporary store the assigned branch to verify in stage 2
                $_SESSION['temp_branch_id'] = $user['assigned_branch_id'];

                if ($user['role'] === 'customer') {
                    header("Location: /customer/dashboard.php");
                } elseif ($user['role'] === 'admin') {
                    header("Location: /admin/dashboard.php");
                } elseif ($user['role'] === 'employee') {
                    // STAGE 2 REDIRECT: Go to branch selection instead of dashboard
                    header("Location: /auth/select_branch.php");
                }
                exit();
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "No account found matching those details.";
        }
    }
}
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Sleek Mlinkhub Login Interface</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style type="text/tailwindcss">
        :root {
            --matte-red: #8b1c1c;
            --elegant-blue: #004ce6;
            --form-bg: #ffffff;
        }
        body { font-family: 'Inter', sans-serif; }
        .matte-finish { background: linear-gradient(135deg, #961e1e 0%, #7a1818 100%); }
        .thin-border { border-width: 1px; border-color: rgba(0, 0, 0, 0.08); }
        .grid-subtle {
            background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 0);
            background-size: 40px 40px;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary-blue": "#004ce6",
                        "matte-red": "#8b1c1c",
                    },
                },
            },
        }
    </script>
</head>
<body class="matte-finish min-h-screen relative flex flex-col items-center justify-center">
    <div class="absolute inset-0 grid-subtle pointer-events-none"></div>
    
    <header class="absolute top-0 w-full flex items-center justify-between px-12 py-8 z-20">
        <div class="flex items-center gap-3">
            <div class="bg-white/10 p-2 rounded-lg backdrop-blur-md">
                <span class="material-symbols-outlined text-white">account_balance_wallet</span>
            </div>
            <h2 class="text-white text-lg font-semibold tracking-wider">MLINKHUB</h2>
        </div>
        <nav class="hidden md:flex items-center gap-8">
            <a class="text-white/70 hover:text-white text-xs font-medium tracking-widest uppercase transition-colors" href="signup.php">SIGN UP</a>
        </nav>
    </header>

    <main class="relative z-10 w-full max-w-7xl mx-auto px-6 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-10 md:p-12">
            <div class="text-center mb-10">
                <h1 class="text-gray-900 text-3xl font-light mb-2">Welcome Back</h1>
                <p class="text-gray-400 text-sm">Access your secure asset portfolio.</p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 text-[11px] p-3 rounded-lg mb-6 text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div class="space-y-1.5">
                    <label class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider ml-1">Username</label>
                    <input name="username" class="w-full h-11 thin-border rounded-lg bg-gray-50/50 px-4 text-gray-800 text-sm focus:ring-1 focus:ring-primary-blue/30 focus:border-primary-blue/50 outline-none transition-all placeholder:text-gray-300" placeholder="Enter your username" type="text" required/>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider ml-1">Email Address</label>
                    <input name="email" 
                           class="w-full h-11 thin-border rounded-lg bg-gray-50/50 px-4 text-gray-800 text-sm focus:ring-1 focus:ring-primary-blue/30 focus:border-primary-blue/50 outline-none transition-all placeholder:text-gray-300" 
                           placeholder="your@email.com" 
                           type="email" 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" 
                           required/>
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Secure Password</label>
                        <a href="forgot_password.php" class="text-[10px] text-primary-blue font-bold hover:underline uppercase tracking-tight">Forgot?</a>
                    </div>
                    <div class="relative">
                        <input id="loginPassword" name="password" class="w-full h-11 thin-border rounded-lg bg-gray-50/50 px-4 text-gray-800 text-sm focus:ring-1 focus:ring-primary-blue/30 focus:border-primary-blue/50 outline-none transition-all placeholder:text-gray-300" placeholder="••••••••" type="password" required/>
                        <button type="button" onclick="toggleLoginPass()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary-blue transition-colors">
                            <span id="eyeIcon" class="material-symbols-outlined text-lg">visibility</span>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="w-full h-12 bg-white border border-primary-blue/20 hover:border-primary-blue hover:bg-gray-50 text-primary-blue transition-all rounded-lg flex items-center justify-center gap-2 font-semibold text-sm shadow-sm">
                    Access Secured Account
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </button>
            </form>

            <div class="mt-10 pt-8 border-t border-gray-50 text-center">
                <p class="text-xs text-gray-400 font-medium">
                    New to the hub? 
                    <a class="text-primary-blue font-bold hover:underline ml-1" href="signup.php">Create Account</a>
                </p>
            </div>
        </div>
    </main>

    <footer class="absolute bottom-8 w-full px-12 flex justify-between items-center z-10">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 text-white/50">
                <span class="material-symbols-outlined text-sm">verified_user</span>
                <span class="text-[10px] font-medium tracking-widest uppercase">Verified System</span>
            </div>
        </div>
        <p class="text-[10px] text-white/30 font-medium tracking-[0.3em] uppercase">M-02.V2 // 2026</p>
    </footer>

    <script>
        function toggleLoginPass() {
            const passField = document.getElementById('loginPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passField.type === 'password') {
                passField.type = 'text';
                eyeIcon.innerText = 'visibility_off';
            } else {
                passField.type = 'password';
                eyeIcon.innerText = 'visibility';
            }
        }
    </script>
</body>
</html>