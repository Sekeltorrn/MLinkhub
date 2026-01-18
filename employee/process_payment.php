<?php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();

$branch_id = $_SESSION['branch_id'];
$loan = null;
$pending_payment = null;
$msg_success = "";
$msg_error = "";

// 1. FETCH SIDEBAR DATA
// A. Pending Online Payments (Needs Verification)
// We filter by branch_id via the loans table since payments table doesn't have it
$pending_sql = "SELECT p.*, l.pawn_ticket_number, c.first_name, c.last_name, l.item_name
                FROM payments p
                JOIN loans l ON p.loan_id = l.loan_id
                JOIN customers c ON l.customer_id = c.user_id
                WHERE l.branch_id = ? AND p.status = 'pending'
                ORDER BY p.payment_date ASC";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$pending_list = $stmt->get_result();

// B. Active Loans (For Walk-ins)
$active_sql = "SELECT l.pawn_ticket_number, c.first_name, c.last_name, l.due_date 
               FROM loans l 
               JOIN customers c ON l.customer_id = c.user_id 
               WHERE l.branch_id = $branch_id AND l.status IN ('active', 'renewed')
               ORDER BY l.due_date ASC";
$active_loans = $conn->query($active_sql);

// 2. LOAD SELECTED DATA
// Case A: Reviewing an Online Payment
if (isset($_GET['review_id'])) {
    $pay_id = $_GET['review_id'];
    $stmt = $conn->prepare("SELECT p.*, l.pawn_ticket_number, l.item_name, c.first_name, c.last_name, l.status as loan_status
                           FROM payments p 
                           JOIN loans l ON p.loan_id = l.loan_id 
                           JOIN customers c ON l.customer_id = c.user_id 
                           WHERE p.payment_id = ?");
    $stmt->bind_param("i", $pay_id);
    $stmt->execute();
    $pending_payment = $stmt->get_result()->fetch_assoc();
}
// Case B: Processing a New Walk-in
elseif (isset($_GET['select_ticket']) || isset($_GET['search_ticket'])) {
    $ticket = $_GET['select_ticket'] ?? $_GET['search_ticket'];
    $sql = "SELECT l.*, c.first_name, c.last_name FROM loans l 
            JOIN customers c ON l.customer_id = c.user_id 
            WHERE l.pawn_ticket_number = ? AND l.branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $ticket, $branch_id);
    $stmt->execute();
    $loan = $stmt->get_result()->fetch_assoc();
}

// 3. HANDLE FORM ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION A: APPROVE/REJECT ONLINE PAYMENT
    if (isset($_POST['verify_action'])) {
        $pay_id = $_POST['payment_id'];
        $action = $_POST['verify_action']; // 'confirmed' or 'rejected'
        $loan_id = $_POST['loan_id'];
        $type = $_POST['payment_type'];
        
        if ($action === 'confirmed') {
            // 1. Update Payment Status
            $conn->query("UPDATE payments SET status = 'confirmed' WHERE payment_id = $pay_id");
            
            // 2. Update Loan Status
            if ($type === 'full_redemption') {
                $conn->query("UPDATE loans SET status = 'redeemed' WHERE loan_id = $loan_id");
            } else {
                // Interest/Renewal
                $conn->query("UPDATE loans SET status = 'renewed', due_date = DATE_ADD(due_date, INTERVAL 30 DAY) WHERE loan_id = $loan_id");
            }
            $msg_success = "Payment Verified Successfully.";
        } else {
            // Reject
            $conn->query("UPDATE payments SET status = 'rejected' WHERE payment_id = $pay_id");
            $msg_error = "Payment Rejected.";
        }
        // Redirect to clear post
        header("Location: process_payment.php?msg=" . urlencode($msg_success ?: $msg_error));
        exit();
    }

    // ACTION B: PROCESS NEW CASH PAYMENT (Walk-in)
    if (isset($_POST['process_new'])) {
        $loan_id = $_POST['loan_id'];
        $cust_id = $_POST['customer_id']; // Required for your table structure
        $amount = $_POST['amount'];
        $type = $_POST['payment_type']; // 'interest' or 'full_redemption'
        $method = 'cash'; 
        
        // Insert directly as Confirmed
        $stmt = $conn->prepare("INSERT INTO payments (loan_id, customer_id, amount, payment_type, payment_method, status, payment_date) VALUES (?, ?, ?, ?, ?, 'confirmed', NOW())");
        $stmt->bind_param("iidss", $loan_id, $cust_id, $amount, $type, $method);
        
        if ($stmt->execute()) {
            if ($type === 'full_redemption') {
                $conn->query("UPDATE loans SET status = 'redeemed' WHERE loan_id = $loan_id");
            } else {
                $conn->query("UPDATE loans SET status = 'renewed', due_date = DATE_ADD(due_date, INTERVAL 30 DAY) WHERE loan_id = $loan_id");
            }
            header("Location: process_payment.php?msg=Cash Payment Recorded");
            exit();
        } else {
            $msg_error = "Database Error: " . $stmt->error;
        }
    }
}

include './includes/header.php'; 
?>

<main class="flex-1 p-8 bg-midnight custom-scrollbar overflow-y-auto">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-4 space-y-6">
            
            <form method="GET" class="relative">
                <input type="text" name="search_ticket" placeholder="Search Ticket #" class="w-full bg-navy-700 border border-white/10 rounded-xl p-4 text-white text-sm focus:border-brand-red outline-none shadow-lg font-mono uppercase">
                <button type="submit" class="absolute right-3 top-3 text-slate-400 hover:text-white"><span class="material-symbols-outlined">search</span></button>
            </form>
            
            <?php if(isset($_GET['msg'])): ?>
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold text-center">
                    <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-navy-700 rounded-3xl border border-amber-500/30 overflow-hidden shadow-2xl">
                <div class="p-4 bg-amber-500/10 border-b border-amber-500/20 flex justify-between items-center">
                    <h3 class="text-amber-500 font-black text-xs uppercase tracking-widest flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">warning</span> Needs Verification
                    </h3>
                    <span class="bg-amber-500 text-black text-[10px] font-bold px-2 rounded-full"><?= $pending_list->num_rows ?></span>
                </div>
                <div class="max-h-[250px] overflow-y-auto custom-scrollbar">
                    <?php if ($pending_list->num_rows > 0): ?>
                        <?php while($p = $pending_list->fetch_assoc()): ?>
                            <a href="?review_id=<?= $p['payment_id'] ?>" class="block p-4 border-b border-white/5 hover:bg-white/5 transition-all group">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-white font-bold text-xs"><?= $p['first_name'] ?> <?= $p['last_name'] ?></p>
                                        <p class="text-[10px] text-slate-400 mt-1"><?= ucfirst($p['payment_method']) ?> • <?= $p['payment_type'] ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-amber-400 font-mono font-bold text-sm">₱<?= number_format($p['amount'], 2) ?></p>
                                        <p class="text-[9px] text-slate-500 font-mono"><?= $p['pawn_ticket_number'] ?></p>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-slate-500 text-xs italic">No pending online payments.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-navy-700 rounded-3xl border border-white/5 overflow-hidden shadow-xl">
                <div class="p-4 bg-white/5 border-b border-white/5">
                    <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest">Active Branch Loans</h3>
                </div>
                <div class="max-h-[300px] overflow-y-auto custom-scrollbar">
                    <?php while($l = $active_loans->fetch_assoc()): ?>
                        <a href="?select_ticket=<?= $l['pawn_ticket_number'] ?>" class="block p-4 border-b border-white/5 hover:bg-brand-red/10 transition-all">
                            <div class="flex justify-between">
                                <span class="text-emerald-400 font-mono text-xs"><?= $l['pawn_ticket_number'] ?></span>
                                <span class="text-slate-500 text-[10px] uppercase">Due: <?= date('M d', strtotime($l['due_date'])) ?></span>
                            </div>
                            <p class="text-white font-bold text-xs mt-1"><?= $l['last_name'] ?>, <?= $l['first_name'] ?></p>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8">
            
            <?php if ($pending_payment): ?>
                <div class="bg-navy-700 rounded-[2.5rem] border-2 border-amber-500/30 p-10 shadow-2xl relative animate-fade-in-up">
                    <span class="absolute top-0 right-0 bg-amber-500 text-black text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-bl-2xl">Verification Mode</span>
                    
                    <h2 class="text-2xl font-black text-white mb-2">Verify Online Payment</h2>
                    <p class="text-slate-400 text-sm mb-8">Compare this reference number with your actual GCash/Bank records.</p>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="bg-midnight p-6 rounded-2xl border border-white/10">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Amount Claimed</p>
                            <p class="text-3xl text-white font-mono font-black mt-2">₱<?= number_format($pending_payment['amount'], 2) ?></p>
                            <div class="mt-4 pt-4 border-t border-white/5">
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Reference No.</p>
                                <p class="text-xl text-amber-400 font-mono tracking-widest mt-1"><?= $pending_payment['reference_number'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                        <div class="space-y-4 pt-2">
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Pawn Ticket</p>
                                <p class="text-white font-mono"><?= $pending_payment['pawn_ticket_number'] ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Customer Name</p>
                                <p class="text-white font-bold"><?= $pending_payment['first_name'] ?> <?= $pending_payment['last_name'] ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Payment Type</p>
                                <span class="bg-white/10 px-2 py-1 rounded text-xs uppercase text-white"><?= $pending_payment['payment_type'] ?></span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="grid grid-cols-2 gap-4">
                        <input type="hidden" name="payment_id" value="<?= $pending_payment['payment_id'] ?>">
                        <input type="hidden" name="loan_id" value="<?= $pending_payment['loan_id'] ?>">
                        <input type="hidden" name="payment_type" value="<?= $pending_payment['payment_type'] ?>">

                        <button type="submit" name="verify_action" value="rejected" class="bg-white/5 hover:bg-red-500/20 hover:text-red-400 text-slate-400 py-4 rounded-xl font-bold uppercase text-xs transition-all border border-transparent hover:border-red-500/50">
                            Reject (Fake)
                        </button>
                        <button type="submit" name="verify_action" value="confirmed" class="bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest shadow-lg shadow-emerald-900/20 transition-all">
                            Confirm Received
                        </button>
                    </form>
                </div>

            <?php elseif ($loan): ?>
                <?php 
                    $principal = $loan['principal_amount'];
                    $interest = $principal * 0.03;
                    $total_redemption = $principal + $interest;
                    // Simple overdue logic
                    $is_overdue = (new DateTime() > new DateTime($loan['due_date']));
                ?>
                <div class="bg-navy-700 rounded-[2.5rem] border border-white/5 p-10 shadow-2xl relative animate-fade-in-up">
                    <span class="absolute top-0 right-0 bg-white/10 text-white text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-bl-2xl">Walk-in Mode</span>
                    
                    <div class="mb-8">
                        <h2 class="text-3xl font-black text-white uppercase tracking-tighter"><?= $loan['pawn_ticket_number'] ?></h2>
                        <p class="text-slate-400 text-sm"><?= $loan['first_name'] ?> <?= $loan['last_name'] ?> &bull; <?= $loan['item_name'] ?></p>
                    </div>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="loan_id" value="<?= $loan['loan_id'] ?>">
                        <input type="hidden" name="customer_id" value="<?= $loan['customer_id'] ?>">
                        <input type="hidden" name="process_new" value="1">

                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_type" value="interest" class="peer sr-only" checked onchange="document.getElementById('disp_amt').innerText = '₱<?= number_format($interest, 2) ?>'; document.getElementById('hid_amt').value = '<?= $interest ?>'">
                                <div class="bg-midnight border border-white/10 peer-checked:border-brand-red peer-checked:bg-brand-red/10 rounded-xl p-6 text-center transition-all hover:bg-white/5">
                                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Renewal</p>
                                    <p class="text-xl font-mono text-brand-red font-bold">₱<?= number_format($interest, 2) ?></p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_type" value="full_redemption" class="peer sr-only" onchange="document.getElementById('disp_amt').innerText = '₱<?= number_format($total_redemption, 2) ?>'; document.getElementById('hid_amt').value = '<?= $total_redemption ?>'">
                                <div class="bg-midnight border border-white/10 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 rounded-xl p-6 text-center transition-all hover:bg-white/5">
                                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Redemption</p>
                                    <p class="text-xl font-mono text-emerald-400 font-bold">₱<?= number_format($total_redemption, 2) ?></p>
                                </div>
                            </label>
                        </div>

                        <div class="bg-midnight p-6 rounded-2xl flex justify-between items-center border border-white/5">
                            <span class="text-slate-400 text-xs font-bold uppercase">Cash to Collect</span>
                            <span id="disp_amt" class="text-2xl font-black text-white">₱<?= number_format($interest, 2) ?></span>
                            <input type="hidden" name="amount" id="hid_amt" value="<?= $interest ?>">
                        </div>

                        <button type="submit" onclick="return confirm('Confirm Cash Payment?')" class="w-full bg-brand-red hover:bg-red-700 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest shadow-lg shadow-red-900/40 transition-all">
                            Confirm Cash Payment
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <div class="h-full bg-navy-700 rounded-[3rem] border-2 border-dashed border-white/5 flex flex-col items-center justify-center text-center p-20 opacity-30">
                    <span class="material-symbols-outlined text-8xl mb-4 text-slate-600">point_of_sale</span>
                    <h3 class="text-2xl font-bold text-white uppercase">Terminal Idle</h3>
                    <p class="text-slate-500 max-w-xs mx-auto mt-2">Select a pending online payment to verify or search a ticket for walk-in transactions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include './includes/footer.php'; ?>