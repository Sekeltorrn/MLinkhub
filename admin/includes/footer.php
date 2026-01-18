</div> </main>

<footer class="bg-midnight border-t border-white/10 h-16 flex items-center justify-between px-8 z-30 sticky bottom-0">
    <div class="flex items-center gap-8">
        <div class="flex items-center gap-2 px-4 py-2 bg-charcoal border border-brand-red/30 text-white rounded cursor-pointer hover:bg-brand-red transition-all shadow-lg">
            <span class="material-symbols-outlined text-lg">terminal</span>
            <span class="text-[11px] font-bold uppercase tracking-widest">Master Console</span>
        </div>
        <div class="hidden md:flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-500 text-sm">security</span>
                <span class="text-[10px] text-slate-500 font-bold tracking-widest uppercase">Admin ID: <?php echo $_SESSION['user_id'] ?? 'ROOT'; ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-500 text-sm">database</span>
                <span class="text-[10px] text-slate-500 font-bold tracking-widest uppercase">Status: Encrypted</span>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <p class="text-[10px] text-slate-600 font-medium tracking-[0.2em] uppercase">ADMIN CORE V4.1.0-A</p>
        <div class="h-4 w-[1px] bg-white/10"></div>
        <a href="../auth/logout.php" class="text-xs font-bold text-brand-red hover:underline uppercase tracking-widest">Destroy Session</a>
    </div>
</footer>

</body>
</html>