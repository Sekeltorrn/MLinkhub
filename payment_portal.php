<?php 
require_once 'db.php'; 
// Assuming you have a session with the user_id. For testing, we'll use 1.
$customer_id = 1; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Digital Payment Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0f18] text-white p-6">

<div class="max-w-md mx-auto">
    <div class="bg-slate-900 border border-white/10 rounded-3xl p-8 shadow-2xl">
        <h2 class="text-2xl font-black mb-6 text-blue-400">GCASH GATEWAY</h2>
        
        <form action="process_online_payment.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Select Active Loan</label>
                <select name="loan_id" required class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                    <?php
                    $loans = $conn->query("SELECT loan_id, pawn_ticket_number, item_name, principal_amount FROM loans WHERE customer_id = '$customer_id' AND status = 'active'");
                    while($l = $loans->fetch_assoc()) {
                        echo "<option value='".$l['loan_id']."'>".$l['item_name']." (".$l['pawn_ticket_number'].")</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Transaction Type</label>
                <select name="payment_type" class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                    <option value="interest">Renew (Pay Interest Only)</option>
                    <option value="full_redemption">Redeem (Pay Full Principal)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Amount to Pay (₱)</label>
                <input type="number" name="amount" required placeholder="0.00" class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
            </div>

            <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl">
                <p class="text-[11px] text-blue-300">You will be redirected to the secure GCash payment screen upon clicking pay.</p>
            </div>

            <button type="submit" class="w-full bg-[#007dfe] hover:bg-blue-500 text-white font-black py-5 rounded-2xl uppercase tracking-widest text-sm transition-all shadow-lg">
                Pay with GCash
            </button>
        </form>
    </div>
</div>

</body>
</html>