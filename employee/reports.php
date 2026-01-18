<?php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();
include './includes/header.php'; 
?>
<main class="flex-1 p-8 bg-midnight custom-scrollbar overflow-y-auto">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-light text-white uppercase">Financial Reports</h2>
                <p class="text-sm text-slate-400">Generate end-of-day audits and inventory logs.</p>
            </div>
            <div class="flex items-center gap-2 bg-navy-700 p-2 rounded-xl border border-white/5">
                <span class="material-symbols-outlined text-slate-400 ml-2">calendar_month</span>
                <input type="date" class="bg-transparent border-none text-white text-xs font-bold focus:ring-0">
                <span class="text-slate-600">-</span>
                <input type="date" class="bg-transparent border-none text-white text-xs font-bold focus:ring-0">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-navy-700 p-8 rounded-3xl border border-white/5 hover:border-brand-red transition-all cursor-pointer group">
                <div class="size-14 bg-brand-red/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-brand-red group-hover:text-white text-brand-red transition-colors">
                    <span class="material-symbols-outlined text-3xl">receipt_long</span>
                </div>
                <h3 class="text-white font-bold text-lg">Daily Sales Audit</h3>
                <p class="text-slate-400 text-xs mt-2 mb-6 h-10">Summary of all cash-in (payments) and cash-out (loans) for the selected date.</p>
                <button class="w-full py-3 border border-white/10 rounded-xl text-slate-300 text-[10px] font-black uppercase hover:bg-white hover:text-midnight transition-colors">Generate PDF</button>
            </div>

            <div class="bg-navy-700 p-8 rounded-3xl border border-white/5 hover:border-blue-500 transition-all cursor-pointer group">
                <div class="size-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-500 group-hover:text-white text-blue-500 transition-colors">
                    <span class="material-symbols-outlined text-3xl">inventory</span>
                </div>
                <h3 class="text-white font-bold text-lg">Vault Inventory</h3>
                <p class="text-slate-400 text-xs mt-2 mb-6 h-10">List of all unredeemed items currently stored in the branch vault.</p>
                <button class="w-full py-3 border border-white/10 rounded-xl text-slate-300 text-[10px] font-black uppercase hover:bg-white hover:text-midnight transition-colors">Export CSV</button>
            </div>

            <div class="bg-navy-700 p-8 rounded-3xl border border-white/5 hover:border-emerald-500 transition-all cursor-pointer group">
                <div class="size-14 bg-emerald-500/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white text-emerald-500 transition-colors">
                    <span class="material-symbols-outlined text-3xl">group</span>
                </div>
                <h3 class="text-white font-bold text-lg">Customer Ledger</h3>
                <p class="text-slate-400 text-xs mt-2 mb-6 h-10">Detailed history of verified customer activities and transaction frequency.</p>
                <button class="w-full py-3 border border-white/10 rounded-xl text-slate-300 text-[10px] font-black uppercase hover:bg-white hover:text-midnight transition-colors">View Data</button>
            </div>
        </div>

    </div>
</main>
<?php include './includes/footer.php'; ?>