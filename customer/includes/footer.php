<?php
// Optional: Fetch the real count of active pawn tickets for the badge
$user_id = $_SESSION['user_id'];
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM loans l JOIN customers c ON l.customer_id = c.customer_id WHERE c.user_id = ? AND l.status = 'active'");
$count_query->bind_param("i", $user_id);
$count_query->execute();
$ticket_count = $count_query->get_result()->fetch_assoc()['total'] ?? 0;
?>

    </div> <footer class="bg-white border-t border-gray-100 h-16 flex items-center justify-between px-8 z-30 sticky bottom-0 mt-auto shrink-0">
        <div class="flex items-center gap-8">
            <div class="flex items-center gap-2 px-4 py-2 bg-matte-red text-white rounded-lg cursor-pointer hover:bg-dark-red transition-colors shadow-sm">
                <span class="material-symbols-outlined text-xl">confirmation_number</span>
                <span class="text-xs font-bold uppercase tracking-wider">Pawn Tickets</span>
                <span class="bg-white/20 px-1.5 py-0.5 rounded text-[10px] ml-1"><?php echo $ticket_count; ?></span>
            </div>
            
            <div class="hidden md:flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400 text-sm">verified_user</span>
                    <span class="text-[10px] text-gray-400 font-bold tracking-widest uppercase">Secured Session</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <p class="text-[10px] text-gray-400 font-medium tracking-[0.2em] uppercase">M-02.V2 // 2026</p>
            <div class="h-4 w-[1px] bg-gray-200"></div>
            <a href="../auth/logout.php" class="text-xs font-bold text-matte-red hover:underline uppercase tracking-wider">Log Out</a>
        </div>
    </footer>
</body>
</html>