<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin | System Stress Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0a0f18; color: #cbd5e1; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
        .accent-border { border-left: 4px solid #e11d48; }
    </style>
</head>
<body class="p-4 md:p-10">

<?php
// We use your existing file! 
// This assumes super_test.php is in the same folder as db.php.
require_once 'db.php'; 

// QUICK STATS
$total_loans = $conn->query("SELECT SUM(principal_amount) FROM loans")->fetch_row()[0] ?? 0;
$active_pawn_count = $conn->query("SELECT COUNT(*) FROM loans WHERE status='active'")->fetch_row()[0] ?? 0;
$customer_count = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0] ?? 0;
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-end mb-10">
        <div>
            <h1 class="text-4xl font-black text-white tracking-tighter uppercase italic">Pulse <span class="text-rose-600">SuperAdmin</span></h1>
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.3em]">Environment: God-Mode / Testing Area</p>
        </div>
        <div class="text-right">
            <span class="bg-green-500/10 text-green-500 border border-green-500/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                DB Linked via db.php
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="glass p-6 rounded-2xl accent-border">
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Total Vault Value</p>
            <h2 class="text-3xl font-light text-white">₱<?php echo number_format($total_loans, 2); ?></h2>
        </div>
        <div class="glass p-6 rounded-2xl border-l-4 border-blue-500">
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Active Pawn Tickets</p>
            <h2 class="text-3xl font-light text-white"><?php echo $active_pawn_count; ?></h2>
        </div>
        <div class="glass p-6 rounded-2xl border-l-4 border-amber-500">
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Registered Pawners</p>
            <h2 class="text-3xl font-light text-white"><?php echo $customer_count; ?></h2>
        </div>
    </div>

    <div class="glass rounded-3xl overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/5 bg-white/5 flex justify-between items-center">
            <h3 class="text-sm font-bold text-white uppercase tracking-widest">Live Loan Database</h3>
            <a href="simulate_loan.php" class="bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold px-4 py-2 rounded uppercase tracking-widest transition-all">
                + Simulate New Loan
            </a>
        </div>
        <table class="w-full text-left">
            <thead class="text-[10px] text-slate-500 uppercase tracking-widest bg-slate-900/50">
                <tr>
                    <th class="p-5">Ticket #</th>
                    <th class="p-5">Customer</th>
                    <th class="p-5">Collateral</th>
                    <th class="p-5">Principal</th>
                    <th class="p-5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm">
                <?php
                // Fetching the data using the columns we verified in your SQL file
                $loans = $conn->query("SELECT l.*, c.first_name, c.last_name FROM loans l JOIN customers c ON l.customer_id = c.customer_id ORDER BY l.created_at DESC");
                if($loans && $loans->num_rows > 0):
                    while($row = $loans->fetch_assoc()): ?>
                    <tr class="hover:bg-white/5 transition-all">
                        <td class="p-5 font-mono text-rose-500 font-bold"><?php echo $row['pawn_ticket_number']; ?></td>
                        <td class="p-5 text-white"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                        <td class="p-5 text-slate-400"><?php echo $row['item_name']; ?></td>
                        <td class="p-5 font-bold text-white">₱<?php echo number_format($row['principal_amount'], 2); ?></td>
                        <td class="p-5">
                            <span class="px-2 py-1 rounded text-[9px] font-bold uppercase bg-green-500/10 text-green-500">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5" class="p-20 text-center text-slate-600 uppercase text-xs tracking-[0.5em]">No active loans in database</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>