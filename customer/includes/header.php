<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$full_name = $_SESSION['username'] ?? 'User';
$initials = strtoupper(substr($full_name, 0, 1));

// Helper for Nav Links
function isActive($page) {
    return strpos($_SERVER['PHP_SELF'], $page) !== false 
        ? 'text-primary-blue border-b-2 border-primary-blue font-semibold' 
        : 'text-gray-500 hover:text-primary-blue';
}

// NEW: Helper for Header Icons (Bell and Gear)
function isIconActive($page) {
    return strpos($_SERVER['PHP_SELF'], $page) !== false ? 'text-white' : 'text-white/50 hover:text-white';
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Mlinkhub User Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style type="text/tailwindcss">
        :root {
            --matte-red: #8b1c1c;
            --dark-red: #6e1616;
            --elegant-blue: #004ce6;
            --bg-gray: #f8f9fa;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-gray); }
        .header-gradient { background: linear-gradient(to right, #8b1c1c, #6e1616); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary-blue": "#004ce6",
                        "matte-red": "#8b1c1c",
                        "dark-red": "#6e1616",
                    },
                },
            },
        }
    </script>
</head>
<body class="min-h-screen flex flex-col">

<header class="header-gradient h-16 flex items-center justify-between px-8 z-30 shrink-0">
    <div class="flex items-center gap-3">
        <div class="bg-white/10 p-1.5 rounded flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-xl">account_balance_wallet</span>
        </div>
        <h1 class="text-white text-base font-bold tracking-widest uppercase">MLINKHUB</h1>
    </div>

    <div class="flex-1 max-w-xl px-12">
        <div class="relative group">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/40 text-lg">search</span>
            <input class="w-full bg-black/10 border border-primary-blue/30 rounded-md py-1.5 pl-10 pr-4 text-sm text-white placeholder:text-white/40 focus:ring-1 focus:ring-primary-blue/50 outline-none transition-all" placeholder="Search transactions..." type="text"/>
        </div>
    </div>

    <div class="flex items-center gap-5">
        <div class="flex items-center gap-4">
            <a href="notifs.php" class="<?php echo isIconActive('notifs.php'); ?> transition-colors">
                <span class="material-symbols-outlined text-xl">notifications</span>
            </a>
            <a href="settings.php" class="<?php echo isIconActive('settings.php'); ?> transition-colors">
                <span class="material-symbols-outlined text-xl">settings</span>
            </a>
        </div>

        <div class="flex items-center gap-3 border-l border-white/10 pl-5">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-semibold text-white"><?php echo htmlspecialchars($full_name); ?></p>
                <p class="text-[10px] text-white/50 tracking-wider uppercase"><?php echo $_SESSION['role'] ?? 'USER'; ?> TIER</p>
            </div>
            <div class="size-9 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-white text-xs font-bold"><?php echo $initials; ?></div>
            <a href="../auth/logout.php" class="text-white/40 hover:text-white ml-1">
                <span class="material-symbols-outlined text-lg">logout</span>
            </a>
        </div>
    </div>
</header>

<nav class="bg-white h-14 border-b border-gray-100 flex items-center px-8 z-20 shrink-0 shadow-sm">
    <div class="flex items-center gap-10 h-full">
        <a class="h-full flex items-center gap-2 text-sm px-1 transition-colors <?php echo isActive('dashboard.php'); ?>" href="dashboard.php">
            <span class="material-symbols-outlined text-xl">grid_view</span> Home
        </a>
        <a class="h-full flex items-center gap-2 text-sm px-1 transition-colors <?php echo isActive('loans.php'); ?>" href="loans.php">
            <span class="material-symbols-outlined text-xl">account_balance</span> Loans
        </a>
        <a class="h-full flex items-center gap-2 text-sm px-1 transition-colors <?php echo isActive('payments.php'); ?>" href="payments.php">
            <span class="material-symbols-outlined text-xl">payments</span> Payments
        </a>
        <a class="h-full flex items-center gap-2 text-sm px-1 transition-colors <?php echo isActive('appointments.php'); ?>" href="appointments.php">
            <span class="material-symbols-outlined text-xl">calendar_today</span> Appointments
        </a>
        <a class="h-full flex items-center gap-2 text-sm px-1 transition-colors <?php echo isActive('account.php'); ?>" href="account.php">
            <span class="material-symbols-outlined text-xl">person</span> Account
        </a>
    </div>
</nav>

<div class="flex-1">