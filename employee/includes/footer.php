<footer class="bg-midnight border-t border-white/5 h-16 flex items-center justify-between px-8 z-30 sticky bottom-0">
    <div class="flex items-center gap-8">
        <a href="new_loan.php" class="flex items-center gap-2 px-4 py-2 bg-brand-red text-white rounded-lg cursor-pointer hover:bg-red-700 transition-colors shadow-lg shadow-brand-red/20">
            <span class="material-symbols-outlined text-xl">add_card</span>
            <span class="text-xs font-bold uppercase tracking-wider">New Transaction</span>
        </a>
        
        <div class="hidden md:flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-500 text-sm">badge</span>
                <span class="text-[10px] text-slate-500 font-bold tracking-widest uppercase">
                    ID: <?= $_SESSION['user_id'] ?? 'N/A' ?>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-500 text-sm">vpn_key</span>
                <span class="text-[10px] text-slate-500 font-bold tracking-widest uppercase">Auth: Valid</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <p class="text-[10px] text-slate-600 font-medium tracking-[0.2em] uppercase">SYSTEM V2.4.0</p>
        <div class="h-4 w-[1px] bg-white/10"></div>
        
        <a href="../logout.php" class="text-xs font-bold text-brand-red hover:underline uppercase tracking-wider flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">lock</span>
            Lock Terminal
        </a>
    </div>
</footer>

</body>
</html>