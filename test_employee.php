<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// test_employee.php - STANDALONE STAFF DASHBOARD
require_once 'db.php'; 

// 1. HANDLE ACTIONS (Check-in, Complete, Cancel, Expire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appt_id = $_POST['appt_id'];
    $action  = $_POST['action'];
    
    $new_status = '';
    if ($action === 'check_in') {
        $new_status = 'arrived'; 
    } elseif ($action === 'complete') {
        $new_status = 'completed'; 
    } elseif ($action === 'expire') {
        $new_status = 'expired'; 
    }

    if ($new_status) {
        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
        $stmt->bind_param("si", $new_status, $appt_id);
        $stmt->execute();
        
        header("Location: test_employee.php"); 
        exit();
    }
}

// 2. THE "AUTONOMOUS SWEEP"
$conn->query("UPDATE appointments SET status = 'expired' 
              WHERE status = 'pending' 
              AND CONCAT(appointment_date, ' ', appointment_time) < (NOW() - INTERVAL 15 MINUTE)");

// 3. FETCH THE ACTIVE QUEUE (Hybrid Priority Logic)
// We calculate 'minutes_late' dynamically to sort the queue.
$sql = "SELECT a.*, c.first_name, c.last_name, b.branch_name,
        TIMESTAMPDIFF(MINUTE, CONCAT(a.appointment_date, ' ', a.appointment_time), NOW()) as minutes_late
        FROM appointments a
        JOIN customers c ON a.customer_id = c.customer_id
        LEFT JOIN branches b ON a.branch_id = b.branch_id
        WHERE a.status IN ('pending', 'arrived') 
        ORDER BY 
            -- PRIORITY LEVEL 1: Arrived & On Time (<= 10 mins late)
            CASE WHEN a.status = 'arrived' AND TIMESTAMPDIFF(MINUTE, CONCAT(a.appointment_date, ' ', a.appointment_time), NOW()) <= 10 THEN 1 
            -- PRIORITY LEVEL 2: Arrived but LATE (> 10 mins)
            WHEN a.status = 'arrived' THEN 2
            -- PRIORITY LEVEL 3: Still Pending
            ELSE 3 END ASC, 
            a.appointment_date ASC, 
            a.appointment_time ASC";
$result = $conn->query($sql);

// 4. FETCH RECENT HISTORY (Last 10 Processed)
$history_sql = "SELECT a.*, c.first_name, c.last_name 
                FROM appointments a
                JOIN customers c ON a.customer_id = c.customer_id
                WHERE a.status NOT IN ('pending', 'arrived') 
                ORDER BY a.created_at DESC 
                LIMIT 10";
$history_result = $conn->query($history_sql);

// 5. METRICS
$stats = $conn->query("SELECT 
    COUNT(CASE WHEN status='pending' THEN 1 END) as pending_cnt,
    COUNT(CASE WHEN status='arrived' THEN 1 END) as arrived_cnt,
    COUNT(CASE WHEN status='completed' THEN 1 END) as completed_today
    FROM appointments")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Queue Manager (Test)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0" />
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 min-h-screen p-8">

    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Manager Dashboard</h1>
                <p class="text-gray-500 font-medium">Live Queue & Appointment Processing</p>
            </div>
            <div class="flex gap-4">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                    <span class="material-symbols-outlined text-amber-500 bg-amber-50 p-2 rounded-lg">schedule</span>
                    <div>
                        <p class="text-[10px] uppercase font-black text-gray-400 tracking-widest">Pending</p>
                        <p class="text-xl font-black text-gray-900"><?= $stats['pending_cnt'] ?></p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
                    <span class="material-symbols-outlined text-blue-600 bg-blue-50 p-2 rounded-lg">person_check</span>
                    <div>
                        <p class="text-[10px] uppercase font-black text-gray-400 tracking-widest">In Shop</p>
                        <p class="text-xl font-black text-gray-900"><?= $stats['arrived_cnt'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-xl border border-gray-200 overflow-hidden">
            <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400">format_list_bulleted</span>
                    Active Queue (Priority Sorted)
                </h2>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= date('F d, Y') ?></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-widest text-gray-400 border-b border-gray-100 bg-white">
                            <th class="p-6 font-black">Time</th>
                            <th class="p-6 font-black">Customer</th>
                            <th class="p-6 font-black">Service</th>
                            <th class="p-6 font-black">Status & Priority</th>
                            <th class="p-6 font-black text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $status = strtolower($row['status']);
                                $time = date('h:i A', strtotime($row['appointment_time']));
                                $is_arrived = ($status === 'arrived');
                                
                                // Logic for "Late" Demotion
                                $minutes_late = $row['minutes_late'];
                                $is_late_checkin = ($is_arrived && $minutes_late > 10);
                            ?>
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="p-6">
                                    <div class="font-bold text-gray-900"><?= $time ?></div>
                                    <div class="text-[10px] text-gray-400 font-bold"><?= date('M d', strtotime($row['appointment_date'])) ?></div>
                                </td>
                                <td class="p-6 text-gray-900 font-bold"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td class="p-6">
                                    <span class="inline-flex px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">
                                        <?= htmlspecialchars($row['service_type']) ?>
                                    </span>
                                </td>
                                <td class="p-6">
                                    <?php if ($is_arrived): ?>
                                        <?php if ($is_late_checkin): ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-[10px] font-black uppercase tracking-wide border border-orange-200">
                                                Late Check-in (+<?= $minutes_late ?>m)
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-wide border border-blue-200 shadow-sm">
                                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span> Priority
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-wide border border-amber-200">
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <?php if (!$is_arrived): ?>
                                            <form method="POST">
                                                <input type="hidden" name="appt_id" value="<?= $row['appointment_id'] ?>">
                                                <input type="hidden" name="action" value="check_in">
                                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-blue-200 active:scale-95 transition-all">
                                                    Check In
                                                </button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Mark as No-Show?');">
                                                <input type="hidden" name="appt_id" value="<?= $row['appointment_id'] ?>">
                                                <input type="hidden" name="action" value="expire">
                                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-[10px] font-bold uppercase tracking-widest border border-rose-200 px-3 py-2 rounded-xl transition-colors">
                                                    No-Show
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST">
                                                <input type="hidden" name="appt_id" value="<?= $row['appointment_id'] ?>">
                                                <input type="hidden" name="action" value="complete">
                                                <button type="submit" class="bg-emerald-500 text-white px-6 py-2 rounded-xl text-xs font-bold shadow-lg shadow-emerald-200 active:scale-95 transition-all flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                                    Complete Task
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-12 text-center text-gray-400 font-bold">No active appointments in queue</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-12 bg-white rounded-[2rem] shadow-md border border-gray-200 overflow-hidden opacity-90">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h2 class="font-bold text-gray-600 flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined text-gray-400">history</span> Recently Processed
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <tbody class="divide-y divide-gray-50">
                        <?php if ($history_result->num_rows > 0): ?>
                            <?php while($h_row = $history_result->fetch_assoc()): 
                                $h_status = $h_row['status'];
                                $status_color = match($h_status) {
                                    'completed' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                    'cancelled' => 'text-rose-600 bg-rose-50 border-rose-100',
                                    'expired'    => 'text-gray-500 bg-gray-100 border-gray-200',
                                    default     => 'text-purple-600 bg-purple-50 border-purple-100'
                                };
                            ?>
                            <tr class="text-xs">
                                <td class="p-4 font-bold text-gray-900"><?= $h_row['first_name'] . ' ' . $h_row['last_name'] ?></td>
                                <td class="p-4 text-gray-500"><?= $h_row['service_type'] ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-md font-black uppercase text-[9px] border <?= $status_color ?>">
                                        <?= !empty($h_status) ? $h_status : 'unknown' ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right text-gray-400">
                                    <?= date('M d, h:i A', strtotime($h_row['appointment_date'] . ' ' . $h_row['appointment_time'])) ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td class="p-8 text-center text-gray-400 text-xs">No history yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>