<?php
include './includes/header.php';
$user_id = $_SESSION['user_id'];

// 1. GET CUSTOMER DATA
$cust_q = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
$cust_q->bind_param("i", $user_id);
$cust_q->execute();
$cust_data = $cust_q->get_result()->fetch_assoc();
$customer_id = $cust_data['customer_id'] ?? 0;

// 2. GET ACTIVE LOANS
// Formula: Principal + (Principal * 0.03) + Penalty (if any, simplified here as 0 for display)
$loans_q = $conn->prepare("
    SELECT 
        loan_id, 
        item_name, 
        pawn_ticket_number, 
        principal_amount, 
        (principal_amount * 0.03) as interest_due,
        (principal_amount + (principal_amount * 0.03)) as full_redemption
    FROM loans 
    WHERE customer_id = ? AND status = 'active'
");
$loans_q->bind_param("i", $customer_id);
$loans_q->execute();
$active_loans = $loans_q->get_result();

// 3. GET TRANSACTION HISTORY
$history_stmt = $conn->prepare("SELECT p.*, l.item_name FROM payments p JOIN loans l ON p.loan_id = l.loan_id WHERE p.customer_id = ? ORDER BY p.payment_date DESC LIMIT 5");
$history_stmt->bind_param("i", $customer_id);
$history_stmt->execute();
$history = $history_stmt->get_result();
?>

<style>
    .header-gradient { background: linear-gradient(to right, #8b1c1c, #5c1212); }
    .bento-card { @apply bg-white rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 overflow-hidden; }
    .input-field { @apply w-full bg-slate-50 border border-slate-200 rounded-lg py-3 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-matte-red/10 focus:border-matte-red outline-none transition-all; }
    .soft-label { @apply text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 block; }
    
    /* Custom Scrollbar */
    .loan-scroll-area::-webkit-scrollbar { width: 5px; }
    .loan-scroll-area::-webkit-scrollbar-track { background: transparent; }
    .loan-scroll-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>

<main class="flex-1 p-6 lg:p-10 overflow-y-auto bg-[#F5F7FA]">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <?php if(isset($_SESSION['booking_success'])): ?>
            <div id="toast-notification" class="transition-opacity duration-500 bg-emerald-50 border-2 border-emerald-100 px-6 py-4 rounded-2xl flex items-center gap-3 text-emerald-700 shadow-sm animate-fade-in-down">
               <span class="material-symbols-outlined font-bold">verified</span>
               <div>
                   <p class="text-xs font-black uppercase tracking-widest">Request Submitted</p>
                   <p class="text-[10px] opacity-80 font-medium">Please proceed to the branch or wait for verification.</p>
               </div>
            </div>
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('toast-notification');
                    if(toast) toast.style.opacity = '0';
                    setTimeout(() => toast?.remove(), 500);
                }, 4000);
            </script>
            <?php unset($_SESSION['booking_success']); ?>
        <?php endif; ?>

        <form action="gcash_gateway.php" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            
            <div class="lg:col-span-8 space-y-6">
                <div class="bento-card bg-white p-6 lg:p-8 border border-slate-200 rounded-2xl">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined text-matte-red">payments</span>
                        Configure Payment
                    </h2>

                    <div class="mb-8">
                        <label class="soft-label">Select Payment Type</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="payment_type" value="interest" checked class="peer sr-only type-selector">
                                <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-matte-red peer-checked:bg-red-50/10 transition-all h-full text-center sm:text-left">
                                    <span class="material-symbols-outlined text-orange-600 mb-2">percent</span>
                                    <h3 class="font-bold text-slate-800 text-sm">Interest Only</h3>
                                    <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-tight font-medium">Renew Loan</p>
                                </div>
                            </label>
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="payment_type" value="partial" class="peer sr-only type-selector">
                                <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-matte-red peer-checked:bg-red-50/10 transition-all h-full text-center sm:text-left">
                                    <span class="material-symbols-outlined text-blue-600 mb-2">add_circle</span>
                                    <h3 class="font-bold text-slate-800 text-sm">Partial</h3>
                                    <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-tight font-medium">Reduce Debt</p>
                                </div>
                            </label>
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="payment_type" value="full_redemption" class="peer sr-only type-selector">
                                <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-matte-red peer-checked:bg-red-50/10 transition-all h-full text-center sm:text-left">
                                    <span class="material-symbols-outlined text-green-600 mb-2">verified</span>
                                    <h3 class="font-bold text-slate-800 text-sm">Redeem</h3>
                                    <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-tight font-medium">Claim Item</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t pt-8">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800 mb-3">Payment Method</h2>
                            <select name="payment_method" id="pay_method" class="input-field cursor-pointer" onchange="updateButtonText()">
                                <option value="gcash">GCash Digital Wallet</option>
                                <option value="bank_transfer">Bank Transfer (InstaPay)</option>
                                
                            </select>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-800 mb-3">Amount to Pay</h2>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold group-focus-within:text-matte-red transition-colors">₱</span>
                                <input type="number" step="0.01" name="amount" id="form_amount" class="input-field pl-9 text-lg font-bold bg-slate-100 cursor-not-allowed" readonly required>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1 hidden" id="amount_hint">Enter partial payment amount.</p>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t flex flex-col sm:flex-row items-center justify-end gap-6">
                        <button type="submit" id="submit_btn" class="w-full sm:w-auto px-10 py-4 bg-matte-red text-white text-sm font-bold rounded-xl hover:bg-dark-red transition-all shadow-lg flex items-center justify-center gap-2">
                            Proceed to Review <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                </div>

                <div class="bento-card bg-white p-6 border border-slate-200 rounded-2xl">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 mb-4 uppercase tracking-widest">
                        <span class="material-symbols-outlined text-slate-400 text-lg">history</span> Recent Activity
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tr class="text-[10px] text-slate-400 uppercase tracking-wider border-b">
                                <th class="pb-3">Date</th>
                                <th class="pb-3">Item</th>
                                <th class="pb-3 text-right">Amount</th>
                                <th class="pb-3 text-right">Status</th>
                            </tr>
                            <?php if($history->num_rows > 0): ?>
                                <?php while($h = $history->fetch_assoc()): ?>
                                <tr class="text-xs border-b last:border-0 hover:bg-slate-50/50">
                                    <td class="py-4 text-slate-500"><?php echo date('M d', strtotime($h['payment_date'])); ?></td>
                                    <td class="py-4 font-bold text-slate-700"><?php echo $h['item_name']; ?></td>
                                    <td class="py-4 text-right font-black">₱<?php echo number_format($h['amount'], 2); ?></td>
                                    <td class="py-4 text-right">
                                        <?php 
                                            $status_color = match($h['status']) {
                                                'confirmed', 'approved' => 'bg-emerald-50 text-emerald-600',
                                                'rejected' => 'bg-red-50 text-red-600',
                                                default => 'bg-amber-50 text-amber-600'
                                            };
                                        ?>
                                        <span class="px-2 py-1 rounded-lg text-[9px] font-bold uppercase <?php echo $status_color; ?>">
                                            <?php echo $h['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="py-4 text-center text-xs text-slate-400">No recent history.</td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Select Item</h2>
                    <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-bold">Active Pawns</span>
                </div>
                
                <div class="loan-scroll-area pr-2 space-y-3 max-h-[600px] overflow-y-auto overflow-x-hidden rounded-2xl">
                    <?php if($active_loans->num_rows > 0): ?>
                        <?php 
                        $active_loans->data_seek(0);
                        while($l = $active_loans->fetch_assoc()): 
                        ?>
                        <label class="block relative cursor-pointer group">
                            <input type="radio" name="loan_id" value="<?php echo $l['loan_id']; ?>" 
                                   data-interest="<?php echo number_format($l['interest_due'], 2, '.', ''); ?>"
                                   data-full="<?php echo number_format($l['full_redemption'], 2, '.', ''); ?>"
                                   class="peer sr-only loan-selector" required>
                            <div class="bento-card p-4 border-2 border-transparent peer-checked:border-matte-red peer-checked:bg-red-50/5 hover:border-slate-300 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="size-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-red-50 group-hover:text-matte-red transition-colors">
                                        <span class="material-symbols-outlined">auto_awesome</span>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-slate-800 text-xs truncate w-32"><?php echo $l['item_name']; ?></h3>
                                        <p class="text-[10px] text-slate-500">Ticket #<?php echo $l['pawn_ticket_number']; ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Interest</p>
                                        <p class="font-black text-matte-red text-sm">₱<?php echo number_format($l['interest_due'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                            <p class="text-xs text-slate-400">No active loans found.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-2xl">
                    <p class="text-[10px] text-blue-700 font-medium leading-relaxed italic">
                        * Selecting a pawn automatically calculates the payment due.
                    </p>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    const amountInput = document.getElementById('form_amount');
    const hint = document.getElementById('amount_hint');
    let selectedInterest = 0;
    let selectedFull = 0;

    // 1. Handle Loan Selection
    document.querySelectorAll('.loan-selector').forEach(radio => {
        radio.addEventListener('change', function() {
            selectedInterest = parseFloat(this.getAttribute('data-interest'));
            selectedFull = parseFloat(this.getAttribute('data-full'));
            updateAmountField();
        });
    });

    // 2. Handle Payment Type Change
    document.querySelectorAll('.type-selector').forEach(radio => {
        radio.addEventListener('change', updateAmountField);
    });

    function updateAmountField() {
        const type = document.querySelector('input[name="payment_type"]:checked').value;
        
        if (type === 'interest') {
            amountInput.value = selectedInterest.toFixed(2);
            amountInput.setAttribute('readonly', true);
            amountInput.classList.add('bg-slate-100', 'cursor-not-allowed');
            hint.classList.add('hidden');
        } else if (type === 'full_redemption') {
            amountInput.value = selectedFull.toFixed(2);
            amountInput.setAttribute('readonly', true);
            amountInput.classList.add('bg-slate-100', 'cursor-not-allowed');
            hint.classList.add('hidden');
        } else {
            // Partial
            amountInput.value = '';
            amountInput.removeAttribute('readonly');
            amountInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
            amountInput.focus();
            hint.classList.remove('hidden');
        }
    }

    // 3. Dynamic Button Text
    function updateButtonText() {
        const method = document.getElementById('pay_method').value;
        const btn = document.getElementById('submit_btn');
        
        if (method === 'cash') {
            btn.innerHTML = 'Book Cash Appointment <span class="material-symbols-outlined">event</span>';
        } else {
            btn.innerHTML = 'Proceed to Review <span class="material-symbols-outlined">arrow_forward</span>';
        }
    }
</script>

<?php include './includes/footer.php'; ?>