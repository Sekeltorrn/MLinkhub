<?php
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Includes header (contains session_start, security, and nav)
include './includes/header.php';

include '../db.php';

$user_id = $_SESSION['user_id'];

// 1. Get the customer_id based on the logged-in user_id
$customer_query = $conn->prepare("SELECT customer_id FROM customers WHERE user_id = ?");
$customer_query->bind_param("i", $user_id);
$customer_query->execute();
$customer_data = $customer_query->get_result()->fetch_assoc();

$loans = [];
if ($customer_data) {
    $customer_id = $customer_data['customer_id'];
    
    // 2. Fetch all loans for this customer
    $loan_sql = "SELECT *, DATEDIFF(due_date, CURDATE()) as days_left FROM loans WHERE customer_id = ? ORDER BY due_date ASC";
    $loan_stmt = $conn->prepare($loan_sql);
    $loan_stmt->bind_param("i", $customer_id);
    $loan_stmt->execute();
    $loans = $loan_stmt->get_result();
}
?>

<main class="flex-1 overflow-y-auto p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">My Pawn Tickets</h2>
                <p class="text-sm text-slate-500">View and manage your collateralized assets and maturity dates.</p>
            </div>
            <div class="flex gap-3">
                <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Active Loans</p>
                    <p class="text-lg font-bold text-slate-900"><?php echo $loans ? $loans->num_rows : 0; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <?php if ($loans && $loans->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ticket / Asset</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Principal</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Due Date</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Time Left</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while($row = $loans->fetch_assoc()): 
                                // Logic for status colors
                                $statusClass = "bg-slate-100 text-slate-600";
                                if($row['status'] == 'active') $statusClass = "bg-emerald-50 text-emerald-700";
                                if($row['status'] == 'expired') $statusClass = "bg-red-50 text-red-700";
                                
                                // Logic for time-left colors
                                $daysLeft = $row['days_left'];
                                $timeColor = $daysLeft <= 5 ? "text-red-600 font-bold" : "text-slate-500";
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-xs font-mono text-primary-blue font-bold mb-1"><?php echo $row['pawn_ticket_number']; ?></div>
                                    <div class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($row['item_name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-900">₱<?php echo number_format($row['principal_amount'], 2); ?></div>
                                    <div class="text-[10px] text-slate-400"><?php echo $row['interest_rate']; ?>% Monthly Int.</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600"><?php echo date('M d, Y', strtotime($row['due_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm <?php echo $timeColor; ?>">
                                        <?php 
                                            if($daysLeft > 0) echo $daysLeft . " days left";
                                            elseif($daysLeft == 0) echo "Due Today";
                                            else echo "Overdue";
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase <?php echo $statusClass; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="view_ticket.php?id=<?php echo $row['loan_id']; ?>" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition-all border border-blue-100">
                                        <span class="material-symbols-outlined text-sm">confirmation_number</span>
                                        View Ticket
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-20 text-center">
                    <div class="inline-flex items-center justify-center size-16 bg-slate-50 rounded-full mb-4">
                        <span class="material-symbols-outlined text-slate-300 text-3xl">inventory_2</span>
                    </div>
                    <h3 class="text-slate-900 font-semibold">No active loans found</h3>
                    <p class="text-slate-500 text-sm mt-1 max-w-xs mx-auto">When you pawn an item at our branch, your digital ticket will appear here automatically.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include './includes/footer.php'; ?>