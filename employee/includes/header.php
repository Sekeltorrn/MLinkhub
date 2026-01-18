<?php
// employee/includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. SECURITY: Absolute redirect to avoid 404
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: /auth/login.php"); 
    exit();
}

// 2. FILE PATHS: Always use root for DB to ensure it works in subfolders
include_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$staff_username = $_SESSION['username'] ?? 'Staff';
$branch_id = $_SESSION['branch_id'] ?? 'N/A';
$initials = strtoupper(substr($staff_username, 0, 1));

/**
 * Helper function for active states
 * Switches to red accent color when on the current page
 */
function isTabActive($page, $current_page) {
    return ($page === $current_page) 
        ? 'active-tab text-white' 
        : 'text-text-muted hover:text-white';
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Mlinkhub Employee - Command Center</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style type="text/tailwindcss">
        :root {
            --midnight-blue: #0a1128;
            --deep-blue: #001f54;
            --accent-red: #d90429;
            --card-bg: #111d3d;
            --text-main: #ffffff;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--midnight-blue);
            color: var(--text-main);
        }
        .active-tab { position: relative; }
        .active-tab::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 2px;
            background-color: var(--accent-red);
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "midnight": "#0a1128",
                        "deep": "#001f54",
                        "brand-red": "#d90429",
                        "navy-700": "#111d3d",
                    },
                },
            },
        }
    </script>
</head>
<body class="min-h-screen flex flex-col">

<header class="bg-deep h-16 flex items-center justify-between px-8 z-30 shrink-0 border-b border-white/5">
    <div class="flex items-center gap-6">
        <div class="flex items-center gap-3">
            <div class="bg-brand-red p-1.5 rounded flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">token</span>
            </div>
            <h1 class="text-white text-base font-bold tracking-widest">MLINKHUB</h1>
        </div>
        <div class="hidden lg:flex items-center bg-white/5 px-3 py-1 rounded-full border border-white/10">
            <span class="material-symbols-outlined text-[14px] text-brand-red mr-2">location_on</span>
            <span class="text-[10px] font-bold text-white/80 uppercase tracking-tight">Terminal: Branch #<?= $branch_id ?></span>
        </div>
    </div>

    <div class="flex-1 max-w-xl px-12 hidden md:block">
        <div class="relative group">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-lg">search</span>
            <input class="w-full bg-midnight/50 border border-white/10 rounded-lg py-1.5 pl-10 pr-4 text-sm text-white focus:ring-1 focus:ring-brand-red/50 focus:border-brand-red/50 outline-none transition-all" placeholder="Search customer name or ticket ID..." type="text"/>
        </div>
    </div>

    <div class="flex items-center gap-5">
        <div class="text-right hidden sm:block">
            <p class="text-xs font-semibold text-white"><?= htmlspecialchars($staff_username) ?></p>
            <p class="text-[9px] text-brand-red font-bold tracking-widest uppercase">Verified Staff</p>
        </div>
        <div class="size-9 rounded-full bg-brand-red/20 border border-brand-red/40 flex items-center justify-center text-brand-red text-xs font-bold">
            <?= $initials ?>
        </div>
    </div>
</header>

<nav class="bg-midnight h-14 border-b border-white/5 flex items-center px-8 z-20 shrink-0 shadow-sm overflow-x-auto">
    <div class="flex items-center gap-10 h-full min-w-max">
        <a class="h-full flex items-center gap-2 text-sm font-medium px-1 transition-colors <?= isTabActive('dashboard.php', $current_page) ?>" href="dashboard.php">
            <span class="material-symbols-outlined text-xl">grid_view</span>
            Overview
        </a>

        <a class="h-full flex items-center gap-2 text-sm font-medium px-1 transition-colors <?= isTabActive('customers.php', $current_page) ?>" href="customers.php">
            <span class="material-symbols-outlined text-xl">group</span>
            Customer Management
        </a>

        <a class="h-full flex items-center gap-2 text-sm font-medium px-1 transition-colors <?= isTabActive('process_payment.php', $current_page) ?>" href="process_payment.php">
            <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
            Payment Verifier
        </a>

        <a class="h-full flex items-center gap-2 text-sm font-medium px-1 transition-colors <?= isTabActive('loans.php', $current_page) ?>" href="loans.php">
            <span class="material-symbols-outlined text-xl">description</span>
            Loan Management
        </a>

        <a class="h-full flex items-center gap-2 text-sm font-medium px-1 transition-colors <?= isTabActive('appointments.php', $current_page) ?>" href="appointments.php">
            <span class="material-symbols-outlined text-xl">event_note</span>
            Appointment Desk
        </a>

        <a class="h-full flex items-center gap-2 text-sm font-medium px-1 transition-colors <?= isTabActive('reports.php', $current_page) ?>" href="reports.php">
            <span class="material-symbols-outlined text-xl">monitoring</span>
            Reports
        </a>
    </div>
</nav>