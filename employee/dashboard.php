<?php
// employee/dashboard.php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 1. GET BRANCH CONTEXT ---
// We fetch the branch ID assigned to this user to enforce data isolation.
$user_q = $conn->prepare("SELECT assigned_branch_id, username FROM users WHERE user_id = ?");
$user_q->bind_param("i", $user_id);
$user_q->execute();
$user_data = $user_q->get_result()->fetch_assoc();

$branch_id = $user_data['assigned_branch_id'];
// Store in session for header to use
$_SESSION['branch_id'] = $branch_id;
$_SESSION['username']  = $user_data['username'];

// --- 2. AUTONOMOUS SWEEP (Branch Specific) ---
// Expire old pending appointments ONLY for this branch
$sweep = $conn->prepare("UPDATE appointments SET status = 'expired' 
                         WHERE status = 'pending' 
                         AND branch_id = ? 
                         AND CONCAT(appointment_date, ' ', appointment_time) < (NOW() - INTERVAL 15 MINUTE)");
$sweep->bind_param("i", $branch_id);
$sweep->execute();

// --- 3. FETCH METRICS ---

// A. Daily Collections (Placeholder logic: Sum payments for today)
// Note: If payments table has no branch_id, this sums global payments for today.
$coll_q = $conn->query("SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = CURDATE()");
$daily_collection = $coll_q->fetch_assoc()['total'] ?? 0;

// B. Today's Traffic (Appointments today at THIS branch)
$traffic_q = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE appointment_date = CURDATE() AND branch_id = ?");
$traffic_q->bind_param("i", $branch_id);
$traffic_q->execute();
$todays_traffic = $traffic_q->get_result()->fetch_assoc()['total'] ?? 0;

// C. In Lobby (Arrived status at THIS branch)
$lobby_q = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE status = 'arrived' AND branch_id = ?");
$lobby_q->bind_param("i", $branch_id);
$lobby_q->execute();
$in_lobby = $lobby_q->get_result()->fetch_assoc()['total'] ?? 0;

// D. New Appraisals (Loans created today)
$appr_q = $conn->query("SELECT COUNT(*) as total FROM loans WHERE DATE(created_at) = CURDATE()");
$new_appraisals = $appr_q->fetch_assoc()['total'] ?? 0;


// --- 4. FETCH LIVE QUEUE (Branch Specific) ---
$queue_sql = "SELECT a.*, c.first_name, c.last_name, 
              TIMESTAMPDIFF(MINUTE, CONCAT(a.appointment_date, ' ', a.appointment_time), NOW()) as minutes_late
              FROM appointments a
              JOIN customers c ON a.customer_id = c.customer_id
              WHERE a.status IN ('pending', 'arrived') 
              AND a.appointment_date = CURDATE()
              AND a.branch_id = ? 
              ORDER BY 
                  CASE WHEN a.status = 'arrived' THEN 1 ELSE 2 END ASC, 
                  a.appointment_time ASC LIMIT 5";

$stmt = $conn->prepare($queue_sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$queue = $stmt->get_result();

include './includes/header.php'; 
?>

<main class="flex-1 overflow-y-auto p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-light text-white">Employee Command Center</h2>
                <p class="text-sm text-slate-400">Real-time oversight for Branch ID: <span class="text-white font-bold">#<?= $branch_id ?></span></p>
            </div>
            <div class="flex gap-4">
                <div class="text-right bg-white/5 px-4 py-2 rounded-lg border border-white/5">
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Queue Status</p>
                    <p class="text-xl font-bold text-white flex items-center justify-end gap-2">
                        <?php if($in_lobby < 3): ?>
                            <span class="size-2 bg-green-500 rounded-full animate-pulse"></span> Optimal
                        <?php elseif($in_lobby < 6): ?>
                            <span class="size-2 bg-amber-500 rounded-full animate-pulse"></span> Busy
                        <?php else: ?>
                            <span class="size-2 bg-brand-red rounded-full animate-pulse"></span> High Traffic
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-brand-red/10 border border-brand-red/30 rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-brand-red">emergency_home</span>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Real-time Action Required</h3>
                </div>
                <span class="text-[10px] bg-brand-red text-white px-2 py-0.5 rounded font-bold uppercase">System Active</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if($in_lobby > 0): ?>
                    <div class="bg-midnight/60 border border-white/10 rounded-lg p-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="size-10 bg-amber-500/10 rounded flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-400">group</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-white">Customers Waiting</h4>
                                <p class="text-xs text-slate-500"><?= $in_lobby ?> people currently in lobby</p>
                            </div>
                        </div>
                        <button onclick="window.location.href='appointments.php'" class="px-4 py-1.5 bg-brand-red text-white text-[11px] font-bold rounded uppercase hover:bg-red-700 transition-colors">Manage</button>
                    </div>
                <?php else: ?>
                    <div class="bg-midnight/60 border border-white/10 rounded-lg p-4 flex items-center justify-between opacity-50">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined text-green-400">check_circle</span>
                            <h4 class="text-sm font-semibold text-slate-400">No urgent queue alerts</h4>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-navy-700 p-6 rounded-xl border border-white/5">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-brand-red bg-brand-red/10 p-2 rounded-lg">payments</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Daily Collections</span>
                </div>
                <p class="text-2xl font-bold text-white">₱<?= number_format($daily_collection, 2) ?></p>
                <div class="mt-2 text-xs text-green-500 font-medium">Live Data</div>
            </div>
            <div class="bg-navy-700 p-6 rounded-xl border border-white/5">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-blue-400 bg-blue-400/10 p-2 rounded-lg">inventory_2</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">New Appraisals</span>
                </div>
                <p class="text-2xl font-bold text-white"><?= $new_appraisals ?> Items</p>
                <div class="mt-2 text-xs text-slate-500">Recorded Today</div>
            </div>
            <div class="bg-navy-700 p-6 rounded-xl border border-white/5">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-purple-400 bg-purple-400/10 p-2 rounded-lg">group</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Today's Traffic</span>
                </div>
                <p class="text-2xl font-bold text-white"><?= $todays_traffic ?> Clients</p>
                <div class="mt-2 text-xs text-slate-500 font-medium">Appointments</div>
            </div>
            <div class="bg-navy-700 p-6 rounded-xl border border-white/5">
                <div class="flex items-center justify-between mb-4">
                    <span class="material-symbols-outlined text-slate-400 bg-white/5 p-2 rounded-lg">timer</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Service Level</span>
                </div>
                <p class="text-2xl font-bold text-white">Optimal</p>
                <div class="mt-2 text-xs text-green-500 font-medium">Within targets</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Today's Live Queue</h3>
                    <a href="appointments.php" class="text-xs text-brand-red font-semibold hover:underline">Full Calendar</a>
                </div>
                <div class="bg-navy-700 rounded-xl border border-white/5 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-white/5 text-[10px] uppercase tracking-widest text-slate-500 font-bold">
                            <tr>
                                <th class="px-6 py-4">Time</th>
                                <th class="px-6 py-4">Client / Service</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-white/5">
                            <?php if($queue->num_rows > 0): ?>
                                <?php while($row = $queue->fetch_assoc()): 
                                    $time_str = date('h:i A', strtotime($row['appointment_time']));
                                    $is_arrived = ($row['status'] === 'arrived');
                                ?>
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs text-slate-400"><?= $time_str ?></td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-white"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
                                        <p class="text-[10px] text-slate-500"><?= htmlspecialchars($row['service_type']) ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if($is_arrived): ?>
                                            <span class="px-2 py-1 bg-green-500/10 text-green-400 text-[10px] font-bold rounded uppercase">Arrived</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-white/5 text-slate-400 text-[10px] font-bold rounded uppercase">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="px-6 py-8 text-center text-slate-500 text-xs">No active appointments for this branch today.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">System Feed</h3>
                    <button class="px-3 py-1 bg-white/5 border border-white/10 text-white text-[10px] font-bold rounded uppercase hover:bg-white/10 transition-colors">Clear</button>
                </div>
                <div class="space-y-3">
                    <div class="bg-navy-700 p-4 rounded-xl border border-white/5 flex items-start gap-4">
                        <span class="material-symbols-outlined text-brand-red mt-1">history_edu</span>
                        <div>
                            <h4 class="text-sm font-semibold text-white">System Ready</h4>
                            <p class="text-xs text-slate-400">Branch ID #<?= $branch_id ?> Loaded Successfully</p>
                            <p class="text-[10px] text-slate-600 mt-1 uppercase font-bold tracking-tighter">Just now</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include './includes/footer.php'; ?>