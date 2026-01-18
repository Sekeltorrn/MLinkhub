<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Payment | Pulse Pawn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0a0f18; color: #cbd5e1; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body class="p-6 md:p-12">

<?php require_once 'db.php'; ?>

<div class="max-w-md mx-auto">
    <div class="glass p-8 rounded-3xl border-t-4 border-blue-500 shadow-2xl">
        <h2 class="text-2xl font-black text-white uppercase tracking-tight mb-6">Record <span class="text-blue-500">Payment</span></h2>
        
        <form action="process_payment.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Select Active Ticket</label>
                <select name="loan_id" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-blue-500 outline-none">
                    <?php
                    $loans = $conn->query("SELECT l.loan_id, l.pawn_ticket_number, c.first_name, l.principal_amount 
                                         FROM loans l 
                                         JOIN customers c ON l.customer_id = c.customer_id 
                                         WHERE l.status = 'active'");
                    while($row = $loans->fetch_assoc()) {
                        echo "<option value='".$row['loan_id']."'>".$row['pawn_ticket_number']." - ".$row['first_name']." (₱".$row['principal_amount'].")</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Amount Paid (₱)</label>
                <input type="number" step="0.01" name="amount_paid" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Payment Type</label>
                <select name="payment_type" class="...">
                <option value="interest">Interest Only (Renewal)</option>
                <option value="full_redemption">Full Redemption (Close Loan)</option>
                <option value="principal">Partial Principal Payment</option>
            </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-5 rounded-2xl uppercase tracking-widest text-sm transition-all shadow-lg shadow-blue-900/20">
                Confirm Payment
            </button>
        </form>
    </div>
</div>

</body>
</html>