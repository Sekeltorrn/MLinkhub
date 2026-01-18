<?php 
include './includes/header.php';
$loan_id = $_GET['id']; // Get ID from URL

// Fetch detailed loan info
$query = "SELECT l.*, c.first_name, c.last_name 
          FROM loans l 
          JOIN customers c ON l.customer_id = c.customer_id 
          WHERE l.loan_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();

// Logic for dates (assuming 30-day cycles)
$pawn_date = new DateTime($loan['loan_date']);
$maturity_date = clone $pawn_date;
$maturity_date->modify('+30 days');
$expiry_date = clone $pawn_date;
$expiry_date->modify('+120 days'); // Usually 4 months before auction
?>

<main class="flex-1 p-6 lg:p-10 bg-[#F5F7FA]">
    <div class="max-w-2xl mx-auto">
        
        <a href="dashboard.php" class="flex items-center gap-2 text-slate-400 hover:text-matte-red transition-colors mb-6 font-bold text-xs uppercase tracking-widest">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Back to Dashboard
        </a>

        <div class="bg-white rounded-[2rem] shadow-xl overflow-hidden border border-slate-200 relative">
            
            <div class="absolute top-24 -left-3 size-6 bg-[#F5F7FA] rounded-full border-r border-slate-200"></div>
            <div class="absolute top-24 -right-3 size-6 bg-[#F5F7FA] rounded-full border-l border-slate-200"></div>

            <div class="bg-slate-900 p-8 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Official Pawn Ticket</p>
                        <h1 class="text-2xl font-black italic tracking-tighter">#<?php echo $loan['pawn_ticket_number']; ?></h1>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-widest">
                            <?php echo $loan['status']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-8">
                
                <div class="flex items-center gap-6 pb-8 border-b border-dashed border-slate-200">
                    <div class="size-20 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl">diamond</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-800"><?php echo $loan['item_name']; ?></h2>
                        <p class="text-sm text-slate-500 italic"><?php echo $loan['item_description'] ?? 'No additional description provided.'; ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-3 bg-slate-50 rounded-2xl">
                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Date Loaned</p>
                        <p class="text-xs font-bold text-slate-700"><?php echo $pawn_date->format('M d, Y'); ?></p>
                    </div>
                    <div class="text-center p-3 bg-red-50 rounded-2xl">
                        <p class="text-[9px] font-bold text-red-400 uppercase mb-1">Maturity</p>
                        <p class="text-xs font-bold text-red-600"><?php echo $maturity_date->format('M d, Y'); ?></p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-2xl">
                        <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">Expiry</p>
                        <p class="text-xs font-bold text-slate-700"><?php echo $expiry_date->format('M d, Y'); ?></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-medium">Principal Amount</span>
                        <span class="font-bold text-slate-800">₱<?php echo number_format($loan['principal_amount'], 2); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-medium">Monthly Interest Rate</span>
                        <span class="font-bold text-slate-800">3.0%</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 font-medium italic">Service Fee (Adv. Interest)</span>
                        <span class="font-bold text-slate-800">₱<?php echo number_format($loan['interest_due'], 2); ?></span>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-lg font-black text-slate-800 uppercase tracking-tighter">Total Net Cash</span>
                        <span class="text-2xl font-black text-matte-red">₱<?php echo number_format($loan['principal_amount'], 2); ?></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <button onclick="window.print()" class="flex items-center justify-center gap-2 py-4 rounded-2xl border-2 border-slate-100 font-bold text-slate-600 hover:bg-slate-50 transition-all text-sm">
                        <span class="material-symbols-outlined text-sm">print</span> Print Ticket
                    </button>
                    <a href="payments.php" class="flex items-center justify-center gap-2 py-4 rounded-2xl bg-matte-red text-white font-bold hover:bg-dark-red transition-all text-sm shadow-lg shadow-red-100">
                        <span class="material-symbols-outlined text-sm">payments</span> Pay Now
                    </a>
                </div>
            </div>

            <div class="bg-slate-50 p-6 text-center border-t border-slate-100">
                <p class="text-[10px] text-slate-400 leading-relaxed uppercase font-bold tracking-widest">
                    Bring this digital ticket or a valid ID when claiming your item in-store.
                </p>
            </div>
        </div>
    </div>
</main>