<?php
include './includes/header.php';
$user_id = $_SESSION['user_id'];

// Get Customer ID
$cust_q = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
$cust_q->bind_param("i", $user_id);
$cust_q->execute();
$customer_id = $cust_q->get_result()->fetch_assoc()['customer_id'] ?? 0;

// Fetch Notifications
$notif_query = $conn->prepare("
    (SELECT 'Payment' as type, amount as title, payment_date as date, 'payments' as icon, 'primary-blue' as color 
     FROM payments WHERE customer_id = ?)
    UNION
    (SELECT 'Due Date' as type, item_name as title, due_date as date, 'warning' as icon, 'matte-red' as color 
     FROM loans WHERE customer_id = ? AND status = 'active')
    ORDER BY date DESC
");
$notif_query->bind_param("ii", $customer_id, $customer_id);
$notif_query->execute();
$notifications = $notif_query->get_result();

$today_date = date('Y-m-d');
$count = 0; // Counter to handle "Load More" visibility
?>

<main class="flex-1 p-8 lg:p-12 overflow-y-auto custom-scrollbar bg-gray-50/20">
    <div class="max-w-4xl mx-auto space-y-10">
        
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Notifications</h2>
                <p class="text-sm text-gray-500">View your transaction history and upcoming deadlines.</p>
            </div>
            <button class="px-6 py-3 text-[10px] font-black text-primary-blue bg-white hover:bg-blue-50 rounded-2xl transition-all border-2 border-gray-200 uppercase tracking-widest shadow-sm active:scale-95">
                Mark all as read
            </button>
        </div>

        <div class="bg-white rounded-[2.5rem] border-2 border-gray-300 shadow-2xl shadow-gray-200/50 overflow-hidden">
            
            <?php if ($notifications->num_rows > 0): ?>
                <div id="notification-container" class="divide-y-2 divide-gray-100">
                    <?php while($n = $notifications->fetch_assoc()): 
                        $count++;
                        $is_today = (date('Y-m-d', strtotime($n['date'])) == $today_date);
                        // Hide items after the 5th one initially
                        $hidden_class = ($count > 5) ? 'hidden hidden-notification' : '';
                    ?>
                        <div class="notif-item <?= $hidden_class ?> group p-10 flex flex-col md:flex-row gap-8 transition-colors hover:bg-gray-50/40">
                            
                            <div class="size-16 rounded-2xl flex items-center justify-center shrink-0 shadow-sm border-2 
                                <?= $n['type'] === 'Payment' ? 'bg-blue-50 border-blue-200 text-primary-blue' : 'bg-red-50 border-red-200 text-matte-red' ?>">
                                <span class="material-symbols-outlined text-3xl font-light"><?= $n['icon'] ?></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-bold text-gray-900 text-lg tracking-tight">
                                        <?= $n['type'] === 'Payment' ? 'Payment Confirmed: ₱'.number_format($n['title'], 2) : 'Due Date Alert: '.$n['title']; ?>
                                    </h3>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200">
                                        <?= $is_today ? 'Today' : date('M d', strtotime($n['date'])); ?>
                                    </span>
                                </div>
                                
                                <div class="border-b-2 border-gray-100 pb-6 mb-6">
                                    <p class="text-sm text-gray-600 leading-relaxed font-medium">
                                        <?= $n['type'] === 'Payment' ? 'Transaction successful. The amount has been applied to your loan balance.' : 'Action required. Your loan is approaching its due date. Please visit a branch to avoid penalties.'; ?>
                                    </p>
                                </div>

                                <div class="flex items-center gap-6">
                                    <button class="text-[10px] font-black text-gray-900 uppercase tracking-[0.2em] flex items-center gap-2 hover:text-primary-blue">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        View Details
                                    </button>
                                    <span class="text-gray-200">|</span>
                                    <button class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-matte-red">
                                        Dismiss
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-32 text-center">
                    <div class="size-24 rounded-[2.5rem] bg-gray-50 border-2 border-dashed border-gray-300 flex items-center justify-center mb-6 text-gray-300">
                        <span class="material-symbols-outlined text-5xl">notifications_off</span>
                    </div>
                    <h2 class="font-bold text-gray-900">All Quiet</h2>
                    <p class="text-xs text-gray-400 mt-2 uppercase tracking-widest font-black">No recent activity</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($count > 5): ?>
        <div class="flex justify-center pb-12">
            <button id="load-more-btn" class="flex items-center gap-3 px-12 py-5 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-black transition-all shadow-xl active:scale-95">
                <span class="material-symbols-outlined text-sm">expand_more</span>
                Load More Notifications
            </button>
        </div>
        <?php endif; ?>

    </div>
</main>

<script>
document.getElementById('load-more-btn')?.addEventListener('click', function() {
    const hiddenItems = document.querySelectorAll('.hidden-notification');
    hiddenItems.forEach(item => {
        item.classList.remove('hidden');
        // Small delay for a smooth fade-in feel if you add transition classes
    });
    this.parentElement.remove(); // Remove button after showing everything
});
</script>

<?php include './includes/footer.php'; ?>