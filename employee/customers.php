<?php
// employee/customers.php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();

include './includes/header.php'; 

// 1. Handle Verification Action
if (isset($_POST['verify_user_id'])) {
    $target_id = $_POST['verify_user_id'];
    $update = $conn->prepare("UPDATE customers SET status = 'verified' WHERE user_id = ?");
    $update->bind_param("i", $target_id);
    if ($update->execute()) {
        echo "<script>alert('Customer Identity Verified Successfully!'); window.location='customers.php';</script>";
    }
}

// 2. Fetch all customers and their current verification status
$query = "
    SELECT u.user_id, u.username, u.email, c.first_name, c.last_name, c.status, c.id_type, c.id_image_path 
    FROM users u 
    JOIN customers c ON u.user_id = c.user_id 
    WHERE u.role = 'customer'
    ORDER BY CASE WHEN c.status = 'pending' THEN 1 ELSE 2 END, c.last_name ASC
";
$result = $conn->query($query);
?>

<main class="flex-1 overflow-y-auto p-8 bg-midnight">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-light text-white uppercase tracking-tight">Customer Management</h2>
                <p class="text-sm text-slate-400">Review identities and manage access for pawn transactions.</p>
            </div>
            <div class="flex gap-4">
                <div class="bg-navy-700 px-4 py-2 rounded-xl border border-white/5">
                    <p class="text-[10px] font-black text-slate-500 uppercase">Total Users</p>
                    <p class="text-xl font-bold text-white"><?= $result->num_rows ?></p>
                </div>
            </div>
        </div>

        <div class="bg-navy-700 rounded-3xl border border-white/5 overflow-hidden shadow-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer Detail</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact Info</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Identity Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-white/[0.02] transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-deep border border-white/10 flex items-center justify-center text-xs font-bold text-brand-red">
                                    <?= strtoupper(substr($row['first_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
                                    <p class="text-[10px] text-slate-500 italic">@<?= htmlspecialchars($row['username']) ?></p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <p class="text-xs text-white/80"><?= htmlspecialchars($row['email']) ?></p>
                            <p class="text-[10px] text-slate-500 font-medium">Verified Email</p>
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex flex-col items-center">
                                <?php if($row['status'] === 'verified'): ?>
                                    <span class="flex items-center gap-1 text-[10px] font-black text-emerald-500 uppercase bg-emerald-500/10 px-3 py-1 rounded-full">
                                        <span class="material-symbols-outlined text-sm">verified</span> Verified
                                    </span>
                                <?php elseif($row['status'] === 'pending'): ?>
                                    <span class="flex items-center gap-1 text-[10px] font-black text-amber-500 uppercase bg-amber-500/10 px-3 py-1 rounded-full animate-pulse">
                                        <span class="material-symbols-outlined text-sm">pending</span> Pending Review
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] font-black text-slate-500 uppercase bg-white/5 px-3 py-1 rounded-full">Unverified</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="px-6 py-5 text-right">
                            <?php if($row['status'] === 'pending'): ?>
                                <a href="<?= $row['id_image_path'] ?>" target="_blank" class="inline-flex items-center gap-1 px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-[10px] font-bold rounded-lg uppercase tracking-tight transition-all mr-2">
                                    <span class="material-symbols-outlined text-sm">visibility</span> View ID
                                </a>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="verify_user_id" value="<?= $row['user_id'] ?>">
                                    <button type="submit" class="px-4 py-2 bg-brand-red hover:bg-red-700 text-white text-[10px] font-bold rounded-lg uppercase tracking-tight transition-all shadow-lg shadow-red-900/20">
                                        Approve
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled class="px-4 py-2 bg-white/5 text-slate-600 text-[10px] font-bold rounded-lg uppercase italic">No Action Required</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php include './includes/footer.php'; ?>