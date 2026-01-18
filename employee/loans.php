<?php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();
include './includes/header.php'; 

$branch_id = $_SESSION['branch_id'];

// 1. FETCH REAL DATA (Branch Locked)
// UPDATED: Now selects item_condition, expiry_date, etc.
$query = "SELECT l.*, c.first_name, c.last_name, c.status as cust_status 
          FROM loans l
          JOIN customers c ON l.customer_id = c.user_id
          WHERE l.branch_id = ? 
          ORDER BY l.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<main class="flex-1 p-8 bg-midnight custom-scrollbar overflow-y-auto">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <?php if(isset($_GET['new_ticket'])): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-2xl flex items-center gap-3 animate-pulse">
                <span class="material-symbols-outlined">check_circle</span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest">Success</p>
                    <p class="text-xs">Ticket <strong><?= htmlspecialchars($_GET['new_ticket']) ?></strong> has been authorized and saved.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-light text-white uppercase tracking-tight">Loan Inventory</h2>
                <p class="text-sm text-slate-400">Manage active pawn tickets and vault items.</p>
            </div>
            <a href="create_ticket.php" class="bg-brand-red hover:bg-red-700 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-900/20 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-base">add</span> New Pawn Ticket
            </a>
        </div>

        <div class="bg-navy-700 p-4 rounded-2xl border border-white/5 flex gap-4">
            <div class="flex-1 relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">search</span>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search Ticket #, Item Name, or Customer..." class="w-full bg-midnight border border-white/10 rounded-xl py-3 pl-12 pr-4 text-sm text-white focus:border-brand-red outline-none transition-colors">
            </div>
            <select id="statusFilter" onchange="filterTable()" class="bg-midnight border border-white/10 rounded-xl px-6 text-sm text-white focus:border-brand-red outline-none cursor-pointer">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="paid">Paid</option>
                <option value="expired">Expired</option>
                <option value="void">Void</option>
            </select>
        </div>

        <div class="bg-navy-700 rounded-3xl border border-white/5 overflow-hidden shadow-2xl">
            <table class="w-full text-left" id="loansTable">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-6 py-4">Ticket ID</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Item Details</th>
                        <th class="px-6 py-4 text-right">Principal</th>
                        <th class="px-6 py-4 text-center">Due Date</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-white">
                    <?php if ($result->num_rows === 0): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 italic">
                                No active loans found in this branch vault.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php while($row = $result->fetch_assoc()): 
                        $isVoid = ($row['status'] === 'void');
                    ?>
                    <tr class="<?= $isVoid ? 'opacity-40 grayscale' : 'hover:bg-white/5' ?> transition-all group">
                        
                        <td class="px-6 py-4 font-mono text-emerald-400 text-xs tracking-tight">
                            <?= $row['pawn_ticket_number'] ?>
                        </td>

                        <td class="px-6 py-4">
                            <p class="font-bold text-white"><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name']) ?></p>
                            <p class="text-[9px] <?= $row['cust_status'] === 'verified' ? 'text-emerald-500' : 'text-amber-500' ?> uppercase font-bold flex items-center gap-1 mt-0.5">
                                <?= $row['cust_status'] === 'verified' ? '✓ Verified' : '⚠ Unverified' ?>
                            </p>
                        </td>

                        <td class="px-6 py-4 text-slate-300 text-xs max-w-xs truncate">
                            <span class="block font-medium"><?= htmlspecialchars($row['item_name']) ?></span>
                            <div class="flex gap-2 mt-1">
                                <span class="text-[9px] bg-white/10 px-2 py-0.5 rounded text-slate-400 border border-white/5">
                                    <?= htmlspecialchars($row['item_condition'] ?? 'N/A') ?>
                                </span>
                                <span class="text-[9px] text-slate-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">inventory_2</span> 
                                    <?= htmlspecialchars($row['storage_location'] ?? 'Vault') ?>
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right font-mono font-bold tracking-tight">
                            ₱<?= number_format($row['principal_amount'], 2) ?>
                        </td>

                        <td class="px-6 py-4 text-center text-xs font-mono text-slate-400">
                            <?= date('M d, Y', strtotime($row['due_date'])) ?>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <?php 
                                $status = $row['status'];
                                $color = match($status) {
                                    'active'   => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
                                    'expired'  => 'text-brand-red bg-brand-red/10 border-brand-red/20',
                                    'paid'     => 'text-blue-400 bg-blue-500/10 border-blue-500/20',
                                    'void'     => 'text-slate-500 bg-slate-500/10 border-slate-500/20',
                                    default    => 'text-slate-400 bg-slate-500/10'
                                };
                            ?>
                            <span class="<?= $color ?> border px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider">
                                <?= $status ?>
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <button onclick="openPrintWindow('<?= $row['pawn_ticket_number'] ?>')" 
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/5 hover:bg-brand-red hover:text-white transition-all shadow-lg" 
                                    title="Print Ticket">
                                <span class="material-symbols-outlined text-sm">print</span>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
// 1. Pop-up Print Function
function openPrintWindow(ticketNo) {
    // Clean Pop-up Dimensions
    const width = 900;
    const height = 600;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;

    const printWindow = window.open(
        `print_ticket.php?ticket_no=${ticketNo}`, 
        'PrintTicket', 
        `width=${width},height=${height},top=${top},left=${left},resizable=yes,scrollbars=yes`
    );

    if (printWindow) {
        printWindow.focus();
    }
}

// 2. Search Filter Logic
function filterTable() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const statusFilter = document.getElementById("statusFilter").value.toUpperCase();
    const table = document.getElementById("loansTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let textFound = false;
        let statusFound = false;
        
        const tdTicket = tr[i].getElementsByTagName("td")[0];
        const tdName = tr[i].getElementsByTagName("td")[1];
        const tdItem = tr[i].getElementsByTagName("td")[2];
        const tdStatus = tr[i].getElementsByTagName("td")[5];

        if (tdTicket && tdName && tdItem) {
            const txtValue = tdTicket.textContent + tdName.textContent + tdItem.textContent;
            if (txtValue.toUpperCase().indexOf(input) > -1) textFound = true;
        }

        if (statusFilter === "" || (tdStatus && tdStatus.textContent.toUpperCase().indexOf(statusFilter) > -1)) {
            statusFound = true;
        }

        tr[i].style.display = (textFound && statusFound) ? "" : "none";
    }
}
</script>

<?php include './includes/footer.php'; ?>