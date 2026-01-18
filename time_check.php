<?php
// Include your master database file
require_once 'db.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>System Time Sync Check</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class='bg-gray-50 flex items-center justify-center min-h-screen p-6'>

    <div class='max-w-md w-full bg-white rounded-[2.5rem] border-2 border-gray-300 shadow-2xl p-10'>
        <div class='flex items-center gap-3 mb-8'>
            <div class='p-3 bg-gray-900 rounded-2xl'>
                <span class='material-symbols-outlined text-white text-2xl'>schedule</span>
            </div>
            <div>
                <h1 class='text-xl font-black text-gray-900 uppercase tracking-tight'>Time Sync Test</h1>
                <p class='text-[10px] text-gray-400 font-bold uppercase tracking-widest'>Verification Tool</p>
            </div>
        </div>

        <div class='space-y-6'>";

            // --- 1. CHECK PHP TIME ---
            $php_time = date('Y-m-d h:i:s A');
            $php_timezone = date_default_timezone_get();
            
            echo "
            <div class='p-6 bg-blue-50 border-2 border-blue-100 rounded-3xl'>
                <p class='text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2'>PHP (Web Engine) Time</p>
                <p class='text-lg font-black text-blue-900'>$php_time</p>
                <p class='text-[10px] font-bold text-blue-700 mt-1 uppercase'>Zone: $php_timezone</p>
            </div>";

            // --- 2. CHECK MYSQL TIME ---
            $db_res = $conn->query("SELECT NOW() as db_now, @@session.time_zone as tz");
            $db_row = $db_res->fetch_assoc();
            $mysql_time = date('Y-m-d h:i:s A', strtotime($db_row['db_now']));
            $mysql_tz = $db_row['tz'];

            echo "
            <div class='p-6 bg-emerald-50 border-2 border-emerald-100 rounded-3xl'>
                <p class='text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2'>MySQL (Database) Time</p>
                <p class='text-lg font-black text-emerald-900'>$mysql_time</p>
                <p class='text-[10px] font-bold text-emerald-700 mt-1 uppercase'>Offset: $mysql_tz</p>
            </div>";

            // --- 3. VERDICT ---
            $is_synced = ($php_time === $mysql_time);
            $bg_color = $is_synced ? 'bg-emerald-500' : 'bg-rose-500';
            $status_text = $is_synced ? 'System is Synchronized' : 'Sync Mismatch Detected';

            echo "
            <div class='mt-8 pt-6 border-t-2 border-gray-100 flex items-center justify-between'>
                <div class='flex items-center gap-2'>
                    <div class='w-3 h-3 rounded-full $bg_color animate-pulse'></div>
                    <span class='text-[11px] font-black uppercase tracking-widest text-gray-900'>$status_text</span>
                </div>
                <button onclick='window.location.reload()' class='text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors underline'>Refresh</button>
            </div>
        </div>
    </div>
    
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0' />
</body>
</html>";