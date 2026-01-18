<?php
require_once 'db.php';

// 1. Fetch all PENDING payments
$pending_sql = "SELECT p.*, l.pawn_ticket_number, c.first_name, c.last_name 
                FROM payments p
                JOIN loans l ON p.loan_id = l.loan_id
                JOIN customers c ON p.customer_id = c.customer_id
                WHERE p.status = 'pending'";
$pending_results = $conn->query($pending_sql);
?>

<div class="p-8 bg-slate-900 min-h-screen text-white">
    <h2 class="text-2xl font-black mb-6">Pending <span class="text-yellow-500">GCash Verifications</span></h2>

    <div class="overflow-x-auto bg-slate-800 rounded-3xl border border-white/10">
        <table class="w-full text-left">
            <thead class="bg-white/5 text-[10px] uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="p-6">Customer</th>
                    <th class="p-6">Ticket</th>
                    <th class="p-6">GCash Ref</th>
                    <th class="p-6">Amount</th>
                    <th class="p-6 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php while($row = $pending_results->fetch_assoc()): ?>
                <tr>
                    <td class="p-6 font-bold"><?php echo $row['first_name'] . " " . $row['last_name']; ?></td>
                    <td class="p-6 text-blue-400"><?php echo $row['pawn_ticket_number']; ?></td>
                    <td class="p-6 font-mono text-sm"><?php echo $row['reference_number']; ?></td>
                    <td class="p-6 font-black text-emerald-400">₱<?php echo number_format($row['amount'], 2); ?></td>
                    <td class="p-6 flex gap-2 justify-center">
                        <a href="approve_logic.php?id=<?php echo $row['payment_id']; ?>&loan_id=<?php echo $row['loan_id']; ?>&type=<?php echo $row['payment_type']; ?>" 
                           class="bg-emerald-600 hover:bg-emerald-500 px-4 py-2 rounded-lg text-xs font-bold transition-all">
                           Confirm
                        </a>
                        <a href="reject_logic.php?id=<?php echo $row['payment_id']; ?>" 
                           class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded-lg text-xs font-bold transition-all">
                           Reject
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>