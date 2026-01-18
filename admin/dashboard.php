<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// This include uses the absolute path we fixed earlier
include 'includes/header.php';

/**
 * DATA FETCHING
 * These queries are mapped to your 'loans' table structure.
 */

// 1. Total Principal Out
$res_principal = $conn->query("SELECT SUM(principal_amount) as total FROM loans WHERE status = 'active'");
// Added (float) and ?? 0 to prevent the Deprecated warning
$total_principal = (float)($res_principal->fetch_assoc()['total'] ?? 0);

// 2. Expected Interest Income
$res_interest = $conn->query("SELECT SUM(principal_amount * (interest_rate / 100)) as total FROM loans WHERE status = 'active'");
$expected_interest = (float)($res_interest->fetch_assoc()['total'] ?? 0);

// 3. Expired Tickets
$res_expired = $conn->query("SELECT COUNT(*) as total FROM loans WHERE status = 'expired'");
$expired_count = (int)($res_expired->fetch_assoc()['total'] ?? 0);

// 4. Total Registered Customers
$res_customers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$customer_count = (int)($res_customers->fetch_assoc()['total'] ?? 0);
?>

<div class="mb-8">
    <h2 class="text-3xl font-light text-white tracking-tight">Pawnshop Pulse</h2>
    <p class="text-sm text-slate-400 mt-1 uppercase tracking-widest font-medium">Operations Overview</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-charcoal p-6 rounded-lg border-l-4 border-l-brand-red border border-white/5">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Principal Out</p>
        <span class="text-2xl font-bold text-white">₱<?php echo number_format($total_principal, 2); ?></span>
    </div>

    <div class="bg-charcoal p-6 rounded-lg border border-white/5">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Expected Monthly Int.</p>
        <span class="text-2xl font-bold text-white">₱<?php echo number_format($expected_interest, 2); ?></span>
    </div>

    <div class="bg-charcoal p-6 rounded-lg border border-white/5">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Expired Tickets</p>
        <span class="text-2xl font-bold text-brand-red"><?php echo $expired_count; ?></span>
    </div>

    <div class="bg-charcoal p-6 rounded-lg border border-white/5">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Active Customers</p>
        <span class="text-2xl font-bold text-white"><?php echo $customer_count; ?></span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
    <div class="bg-charcoal rounded-xl border border-white/5 p-6 shadow-2xl">
        <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-brand-red">inventory_2</span>
            Inventory & Tickets
        </h3>
        <p class="text-sm text-slate-400 mb-6">Create new pawn tickets or update existing collateral items.</p>
        <div class="flex gap-4">
            <a href="new_ticket.php" class="px-4 py-2 bg-brand-red text-white text-[10px] font-bold rounded uppercase tracking-widest hover:bg-red-700 transition-all">New Ticket</a>
            <a href="manage_loans.php" class="px-4 py-2 bg-slate-700 text-white text-[10px] font-bold rounded uppercase tracking-widest hover:bg-slate-600 transition-all">View All Loans</a>
        </div>
    </div>

    <div class="bg-charcoal rounded-xl border border-white/5 p-6 shadow-2xl">
        <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Quick Insights</h3>
        <div class="space-y-3">
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">System Time</span>
                <span class="text-white"><?php echo date('Y-m-d H:i'); ?></span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500">Database Engine</span>
                <span class="text-green-500">Connected (MySQLi)</span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>