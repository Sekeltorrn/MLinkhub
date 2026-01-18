<?php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Get Customer Info
$cust_q = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
$cust_q->bind_param("i", $user_id);
$cust_q->execute();
$customer = $cust_q->get_result()->fetch_assoc();
$customer_id = $customer['customer_id'] ?? 0;
$first_name = $customer['first_name'] ?? 'Valued Customer';

// 2. Total Active Loans & Total Amount
$active_loans_q = $conn->prepare("SELECT COUNT(*) as total, SUM(principal_amount) as total_amt FROM loans WHERE customer_id = ? AND status = 'active'");
$active_loans_q->bind_param("i", $customer_id);
$active_loans_q->execute();
$loan_stats = $active_loans_q->get_result()->fetch_assoc();
$total_outstanding = $loan_stats['total_amt'] ?? 0;

// 3. Next Upcoming Due Date (Financial Urgency)
$due_q = $conn->prepare("SELECT due_date FROM loans WHERE customer_id = ? AND status = 'active' ORDER BY due_date ASC LIMIT 1");
$due_q->bind_param("i", $customer_id);
$due_q->execute();
$next_due_res = $due_q->get_result()->fetch_assoc();
$next_due = $next_due_res['due_date'] ?? null;

// 4. [NEW] Next Appointment (Operational Urgency)
// We only want future appointments that are pending or arrived
$appt_q = $conn->prepare("SELECT a.*, b.branch_name FROM appointments a 
                          JOIN branches b ON a.branch_id = b.branch_id 
                          WHERE a.customer_id = ? 
                          AND a.status IN ('pending', 'arrived') 
                          AND CONCAT(a.appointment_date, ' ', a.appointment_time) >= NOW()
                          ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 1");
$appt_q->bind_param("i", $customer_id);
$appt_q->execute();
$next_appt = $appt_q->get_result()->fetch_assoc();

// 5. Recent Payments
$payments_q = $conn->prepare("SELECT * FROM payments WHERE customer_id = ? ORDER BY payment_date DESC LIMIT 3");
$payments_q->bind_param("i", $customer_id);
$payments_q->execute();
$recent_activity = $payments_q->get_result();

// 6. Active Loans List
$list_q = $conn->prepare("SELECT * FROM loans WHERE customer_id = ? AND status = 'active' LIMIT 2");
$list_q->bind_param("i", $customer_id);
$list_q->execute();
$active_list = $list_q->get_result();

// --- SMART LOGIC: Determine Banner State ---
$banner_type = 'default'; // default, urgent_loan, upcoming_appt
$days_until_due = 999;

if ($next_due) {
    $days_until_due = ceil((strtotime($next_due) - time()) / 86400);
    if ($days_until_due <= 3) {
        $banner_type = 'urgent_loan';
    }
}

// If no urgent loan, but there IS an appointment, prioritize the appointment view
if ($banner_type === 'default' && $next_appt) {
    $banner_type = 'upcoming_appt';
}

// --- Time Greeting ---
$hour = date('H');
$greeting = "Good Evening";
if ($hour < 12) $greeting = "Good Morning";
elseif ($hour < 18) $greeting = "Good Afternoon";

include './includes/header.php'; 
?>

<main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex flex-col md:flex-row justify-between md:items-end gap-4">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1"><?= date('l, F jS') ?></p>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight"><?= $greeting ?>, <?= htmlspecialchars($first_name) ?>.</h1>
            </div>
            <div class="hidden md:block">
                <p class="text-xs font-bold text-right text-gray-400">System Status</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-sm font-bold text-emerald-600">Operational</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-10 gap-6">
            <div class="md:col-span-6 bento-container p-10 flex flex-col md:flex-row items-center justify-between gap-8 bg-white relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-9xl">account_balance_wallet</span>
                </div>
                <div class="space-y-2 z-10">
                    <h3 class="font-black tracking-widest text-[10px] text-gray-400 uppercase border-b-2 border-gray-100 pb-2 inline-block">Total Outstanding Principal</h3>
                    <p class="text-5xl lg:text-6xl font-black text-gray-900 leading-tight tracking-tight">
                        ₱<?= number_format($total_outstanding, 2) ?>
                    </p>
                    <p class="text-xs text-gray-400 font-medium mt-1">Across <?= $loan_stats['total'] ?> active pawn tickets</p>
                </div>
                <a href="payments.php" class="bg-gray-900 hover:bg-black text-white px-8 py-4 rounded-2xl font-black tracking-widest uppercase text-xs shadow-xl shadow-gray-200 transition-all active:scale-95 shrink-0 z-10 flex items-center gap-2">
                    <span>Quick Pay</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="md:col-span-4 bento-container p-10 flex flex-col justify-center bg-white border-gray-100 relative group">
                <div class="space-y-4 relative z-10">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-matte-red text-xl group-hover:scale-110 transition-transform">event_busy</span>
                        <h3 class="font-black tracking-widest text-[10px] text-gray-400 uppercase border-b-2 border-gray-100 pb-2 inline-block flex-1">Next Maturity Date</h3>
                    </div>
                    <div>
                        <?php if($next_due): ?>
                            <p class="text-2xl font-black text-matte-red"><?= date('M d, Y', strtotime($next_due)) ?></p>
                            <p class="text-sm font-bold text-gray-400 mt-1">
                                <?php 
                                    if ($days_until_due < 0) echo "Overdue by " . abs($days_until_due) . " days";
                                    elseif ($days_until_due == 0) echo "Due Today!";
                                    else echo $days_until_due . " days remaining";
                                ?>
                            </p>
                        <?php else: ?>
                            <p class="text-2xl font-black text-gray-300">No Active Loans</p>
                            <p class="text-xs font-bold text-emerald-500 mt-1">You are debt free!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if($banner_type === 'urgent_loan'): ?>
            <div class="bento-container bg-rose-50 p-6 flex flex-col md:flex-row items-center justify-between gap-4 border-2 border-rose-100 animate-pulse-slow">
                <div class="flex items-center gap-4">
                    <div class="size-12 shrink-0 rounded-xl bg-rose-500 flex items-center justify-center text-white shadow-lg shadow-rose-200">
                        <span class="material-symbols-outlined">warning</span>
                    </div>
                    <div>
                        <h4 class="font-black tracking-widest text-[10px] text-rose-600 uppercase">Action Required: Payment Due</h4>
                        <p class="text-sm text-rose-800 font-bold">You have a loan maturing in <?= $days_until_due ?> days. Avoid penalties by renewing or redeeming now.</p>
                    </div>
                </div>
                <a href="payments.php" class="bg-rose-600 text-white px-6 py-3 rounded-xl font-black tracking-widest uppercase text-xs shadow-lg hover:bg-rose-700 transition-colors w-full md:w-auto text-center">
                    Pay Now
                </a>
            </div>

        <?php elseif($banner_type === 'upcoming_appt'): ?>
            <div class="bento-container bg-blue-50 p-6 flex flex-col md:flex-row items-center justify-between gap-4 border-2 border-blue-100">
                <div class="flex items-center gap-4">
                    <div class="size-12 shrink-0 rounded-xl bg-primary-blue flex items-center justify-center text-white shadow-lg shadow-blue-200">
                        <span class="material-symbols-outlined">calendar_clock</span>
                    </div>
                    <div>
                        <h4 class="font-black tracking-widest text-[10px] text-primary-blue uppercase">Upcoming Appointment</h4>
                        <p class="text-sm text-blue-900 font-bold">
                            <?= date('F j, h:i A', strtotime($next_appt['appointment_date'] . ' ' . $next_appt['appointment_time'])) ?> 
                            <span class="text-blue-400 font-normal">at</span> <?= htmlspecialchars($next_appt['branch_name']) ?>
                        </p>
                    </div>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <a href="appointments.php" class="bg-white text-primary-blue border-2 border-blue-100 px-6 py-3 rounded-xl font-black tracking-widest uppercase text-xs hover:bg-blue-50 transition-colors w-full md:w-auto text-center">
                        View Details
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="bento-container bg-gray-50 p-6 flex flex-col md:flex-row items-center justify-between gap-4 border-dashed border-2 border-gray-200">
                <div class="flex items-center gap-4">
                    <div class="size-12 shrink-0 rounded-xl bg-white flex items-center justify-center text-gray-400 shadow-sm">
                        <span class="material-symbols-outlined">add_task</span>
                    </div>
                    <div>
                        <h4 class="font-black tracking-widest text-[10px] text-gray-400 uppercase">Everything looks good</h4>
                        <p class="text-sm text-gray-500 font-medium">Need cash or want to value an item? Schedule a visit today.</p>
                    </div>
                </div>
                <a href="appointments.php" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-black tracking-widest uppercase text-xs hover:bg-black transition-colors w-full md:w-auto text-center">
                    Book Appointment
                </a>
            </div>
        <?php endif; ?>


        <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            
            <div class="lg:col-span-6 space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="font-black tracking-widest text-[10px] text-gray-400 uppercase">Your Vault (<?= $loan_stats['total'] ?> Items)</h3>
                    <?php if($loan_stats['total'] > 0): ?>
                        <a href="loans.php" class="text-[10px] font-bold text-primary-blue uppercase hover:underline">View All</a>
                    <?php endif; ?>
                </div>
                
                <div class="space-y-4">
                    <?php if($active_list->num_rows > 0): ?>
                        <?php while($item = $active_list->fetch_assoc()): ?>
                            <div class="bento-container p-6 bg-white hover:border-primary-blue/30 transition-all hover:shadow-lg group cursor-pointer">
                                <div class="flex justify-between items-start">
                                    <div class="flex gap-4">
                                        <div class="size-12 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400">
                                            <span class="material-symbols-outlined">diamond</span>
                                        </div>
                                        <div>
                                            <h4 class="text-base font-black text-gray-900 group-hover:text-primary-blue transition-colors"><?= htmlspecialchars($item['item_name']) ?></h4>
                                            <p class="font-bold text-[10px] text-gray-400 uppercase tracking-wide mt-1">Ticket #<?= $item['loan_id'] ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black tracking-widest text-[10px] text-gray-400 uppercase">Principal</p>
                                        <p class="text-sm font-bold text-gray-900">₱<?= number_format($item['principal_amount'], 2) ?></p>
                                        <p class="text-[10px] text-emerald-500 font-bold mt-1 bg-emerald-50 px-2 py-0.5 rounded-md inline-block">Active</p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bento-container p-12 bg-white text-center border-dashed border-2 border-gray-200">
                            <span class="material-symbols-outlined text-gray-200 text-5xl mb-2">lock_open</span>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Your Vault is Empty</p>
                            <a href="appointments.php" class="text-[10px] font-bold text-primary-blue uppercase underline">Get an Appraisal</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="font-black tracking-widest text-[10px] text-gray-400 uppercase">Recent Activity</h3>
                </div>
                
                <div class="bento-container p-6 bg-white h-full min-h-[300px]">
                    <?php if($recent_activity->num_rows > 0): ?>
                        <div class="space-y-6">
                            <?php while($pay = $recent_activity->fetch_assoc()): ?>
                                <div class="flex gap-4 items-start group relative">
                                    <div class="absolute left-5 top-10 bottom-[-24px] w-0.5 bg-gray-50 group-last:hidden"></div>
                                    
                                    <div class="size-10 shrink-0 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors z-10">
                                        <span class="material-symbols-outlined text-green-600 text-sm">payments</span>
                                    </div>
                                    <div class="w-full">
                                        <div class="flex justify-between items-start">
                                            <h5 class="text-xs font-black text-gray-900 uppercase tracking-wide">Payment Received</h5>
                                            <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest"><?= date('M d', strtotime($pay['payment_date'])) ?></span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Confirmed payment of <span class="font-bold text-gray-900">₱<?= number_format($pay['amount'], 2) ?></span></p>
                                        <p class="text-[10px] text-gray-400 mt-1">Method: <?= htmlspecialchars($pay['payment_method']) ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-center py-10 opacity-50">
                            <span class="material-symbols-outlined text-4xl mb-2">history_toggle_off</span>
                            <p class="text-xs font-black uppercase tracking-widest">No recent history</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include './includes/footer.php'; ?>