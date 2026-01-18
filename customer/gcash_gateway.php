<?php
include './includes/header.php';

// 1. Capture Data from payments.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_id      = $_POST['loan_id'];
    $amount       = $_POST['amount'];
    $cust_id      = $_POST['customer_id'];
    $payment_type = $_POST['payment_type'];
    
    // Check if ref number was manually typed (if using manual mode), otherwise auto-generate
    $manual_ref   = $_POST['reference_number'] ?? null; 
} else {
    header("Location: payments.php");
    exit();
}

// 2. SIMULATOR LOGIC: Generate Fake GCash Reference
// Format: YearMonthDay + Random 4 digits (e.g., 202601189921)
// If the user typed one in payments.php, use that. If not, generate one.
$simulated_ref = $manual_ref ? $manual_ref : date('Ymd') . rand(1000, 9999);
?>

<main class="flex-1 p-6 lg:p-10 bg-[#F5F7FA] flex items-center justify-center min-h-[80vh]">
    <div class="max-w-sm w-full animate-in fade-in zoom-in duration-500">
        
        <div class="flex justify-center mb-8">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg" alt="GCash" class="h-10">
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-100">
            <div class="bg-[#007dfe] p-6 text-white text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full bg-white/10 opacity-50" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 10px 10px;"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80 mb-1">Merchant</p>
                    <h2 class="text-xl font-black tracking-tight">MLINKHUB PAWNSHOP</h2>
                </div>
            </div>

            <div class="p-8 text-center">
                <p class="text-slate-400 text-xs font-bold uppercase mb-2 tracking-widest">Total Amount Due</p>
                <div class="text-4xl font-black text-slate-900 mb-8">
                    <span class="text-xl font-bold mr-1 text-[#007dfe]">₱</span><?php echo number_format($amount, 2); ?>
                </div>

                <div class="space-y-6 mb-8 text-left">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 tracking-wider">GCash Mobile Number</label>
                        <div class="relative mt-1 group">
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 text-slate-800 font-bold group-focus-within:text-[#007dfe] transition-colors">+63</span>
                            <input type="text" maxlength="10" placeholder="9XX XXX XXXX" id="sim_phone"
                                   class="w-full border-b-2 border-slate-200 pl-10 py-2 text-lg font-bold outline-none focus:border-[#007dfe] transition-colors bg-transparent text-slate-700 placeholder-slate-300">
                        </div>
                    </div>
                    
                    <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#007dfe] text-lg">receipt_long</span>
                            <span class="text-[10px] font-bold uppercase text-blue-400">Ref No.</span>
                        </div>
                        <span class="text-blue-600 font-mono font-bold tracking-tight text-lg"><?php echo $simulated_ref; ?></span>
                    </div>

                    <p class="text-[9px] text-slate-400 text-center leading-relaxed italic">
                        <span class="font-bold text-[#007dfe]">SIMULATION MODE:</span> Clicking "Pay Now" will instantly create a "Pending" record with the Reference ID above.
                    </p>
                </div>

                <form action="process_integrated_payment.php" method="POST">
                    <input type="hidden" name="loan_id" value="<?php echo $loan_id; ?>">
                    <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                    <input type="hidden" name="customer_id" value="<?php echo $cust_id; ?>">
                    <input type="hidden" name="payment_type" value="<?php echo $payment_type; ?>">
                    
                    <input type="hidden" name="ref_no" value="<?php echo $simulated_ref; ?>">

                    <button type="submit" class="w-full bg-[#007dfe] hover:bg-[#0069d9] text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-blue-200 active:scale-95 flex items-center justify-center gap-2 group">
                        <span>PAY PHP <?php echo number_format($amount, 2); ?></span>
                        <span class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
                </form>

                <a href="payments.php" class="inline-block mt-6 text-xs font-bold text-slate-300 hover:text-slate-500 transition-colors uppercase tracking-widest">
                    Cancel Transaction
                </a>
            </div>
        </div>
        
        <div class="mt-10 flex items-center justify-center gap-6 opacity-20 grayscale">
            <div class="flex flex-col items-center">
                <span class="material-symbols-outlined text-2xl">lock</span>
                <span class="text-[8px] font-bold uppercase mt-1">Secure</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="material-symbols-outlined text-2xl">verified_user</span>
                <span class="text-[8px] font-bold uppercase mt-1">Verified</span>
            </div>
        </div>
    </div>
</main>

<script>
    // Just a little UI flair for the simulation
    const form = document.querySelector('form');
    const btn = form.querySelector('button');

    form.onsubmit = function() {
        btn.innerHTML = '<span class="animate-spin material-symbols-outlined text-sm">sync</span> Processing...';
        btn.style.opacity = "0.7";
        btn.style.pointerEvents = "none";
    };
    
    // Auto-focus the phone input for realism
    document.getElementById('sim_phone').focus();
</script>

<?php include './includes/footer.php'; ?>