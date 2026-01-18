<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. SECURITY: Check Admin Access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// 2. DATABASE: Absolute pathing for InfinityFree
$db_path = '/home/vol13_3/infinityfree.com/if0_40625987/mlinkh.wuaze.com/htdocs/db.php';
require_once($db_path);

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Mlinkhub Admin - The Pulse</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style type="text/tailwindcss">
        :root {
            --midnight-charcoal: #0f172a;
            --deep-navy: #020617;
            --accent-red: #d90429;
            --card-bg: #1e293b;
            --text-main: #ffffff;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--deep-navy);
            color: var(--text-main);
        }
        .active-tab {
            position: relative;
            color: white !important;
            opacity: 1 !important;
        }
        .active-tab::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--accent-red);
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "charcoal": "#0f172a",
                        "midnight": "#020617",
                        "brand-red": "#d90429",
                        "navy-card": "#1e293b",
                        "navy-border": "#334155",
                    },
                },
            },
        }
    </script>
</head>
<body class="min-h-screen flex flex-col">

<header class="bg-midnight h-16 flex items-center justify-between px-8 z-30 shrink-0 border-b border-white/10">
    <div class="flex items-center gap-8">
        <div class="flex items-center gap-3">
            <div class="bg-brand-red p-1.5 rounded flex items-center justify-center">
                <svg class="size-6 text-white" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.57829 8.57829C5.52816 11.6284 3.451 15.5145 2.60947 19.7452C1.76794 23.9758 2.19984 28.361 3.85056 32.3462C5.50128 36.3314 8.29667 39.7376 11.8832 42.134C15.4698 44.5305 19.6865 45.8096 24 45.8096C28.3135 45.8096 32.5302 44.5305 36.1168 42.134C39.7033 39.7375 42.4987 36.3314 44.1494 32.3462C45.8002 28.361 46.2321 23.9758 45.3905 19.7452C44.549 15.5145 42.4718 11.6284 39.4217 8.57829L24 24L8.57829 8.57829Z" fill="currentColor"></path>
                </svg>
            </div>
            <h1 class="text-white text-lg font-bold tracking-[0.2em]">MLINKHUB <span class="text-brand-red font-light">ADMIN</span></h1>
        </div>
        <div class="relative">
            <select class="bg-charcoal border border-white/10 text-white text-xs rounded-md pl-8 pr-4 py-1.5 focus:ring-1 focus:ring-brand-red/50 outline-none appearance-none cursor-pointer">
                <option>All Branches (Aggregated)</option>
                <option>Makati Central</option>
                <option>Quezon City North</option>
                <option>Cebu IT Park</option>
            </select>
            <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-brand-red text-sm">hub</span>
        </div>
    </div>
    
    <div class="flex items-center gap-6">
        <div class="flex items-center gap-2 px-3 py-1 bg-white/5 rounded-full border border-white/5">
            <span class="size-2 bg-green-500 rounded-full"></span>
            <span class="text-[10px] font-bold text-white/60 uppercase tracking-widest">System Online</span>
        </div>
        <div class="h-8 w-[1px] bg-white/10"></div>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <p class="text-xs font-bold text-white uppercase tracking-tight"><?php echo $_SESSION['username'] ?? 'Admin'; ?></p>
                <p class="text-[10px] text-brand-red font-medium tracking-widest uppercase">Root Access</p>
            </div>
            <div class="size-9 rounded bg-charcoal border border-white/20 flex items-center justify-center text-white text-xs font-bold ring-2 ring-brand-red/20">
                <span class="material-symbols-outlined">shield_person</span>
            </div>
        </div>
    </div>
</header>

<nav class="bg-charcoal h-14 border-b border-white/10 flex items-center px-8 z-20 shrink-0">
    <div class="flex items-center gap-12 h-full">
        <a class="h-full flex items-center gap-2 text-white/40 hover:text-white text-sm font-bold uppercase tracking-widest px-1 transition-all <?php echo ($current_page == 'dashboard.php') ? 'active-tab' : ''; ?>" href="dashboard.php">
            Overview
        </a>
        <a class="h-full flex items-center gap-2 text-white/40 hover:text-white text-sm font-bold uppercase tracking-widest px-1 transition-all <?php echo ($current_page == 'manage_customers.php') ? 'active-tab' : ''; ?>" href="manage_customers.php">
            Customer Management
        </a>
        <a class="h-full flex items-center gap-2 text-white/40 hover:text-white text-sm font-bold uppercase tracking-widest px-1 transition-all <?php echo ($current_page == 'audits.php') ? 'active-tab' : ''; ?>" href="audits.php">
            Audits
        </a>
        <a class="h-full flex items-center gap-2 text-white/40 hover:text-white text-sm font-bold uppercase tracking-widest px-1 transition-all <?php echo ($current_page == 'reports.php') ? 'active-tab' : ''; ?>" href="reports.php">
            Reports
        </a>
    </div>
</nav>

<main class="flex-1 overflow-y-auto p-8 bg-midnight">
<div class="max-w-[1600px] mx-auto space-y-8">