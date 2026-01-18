<?php
// appointments.php - UI SECTION
// We connect to the brain here:
include './includes/process_appointments.php'; 
include './includes/header.php'; 
?>

<script>
    // ... [KEEP ALL YOUR EXISTING JAVASCRIPT EXACTLY AS IS] ...
    function setService(val, el) {
        document.getElementById('service_type_input').value = val;
        document.querySelectorAll('.service-option').forEach(node => {
            node.classList.remove('active', 'border-primary-blue', 'bg-blue-50/30');
            node.classList.add('border-gray-200');
            const icon = node.querySelector('.check-icon');
            if(icon) icon.classList.add('hidden');
        });
        el.classList.add('active', 'border-primary-blue', 'bg-blue-50/30');
        el.classList.remove('border-gray-200');
        el.querySelector('.check-icon').classList.remove('hidden');
    }

    async function validateSchedule() {
    const branchSelect = document.getElementById('branch_id');
    const dateInput = document.getElementById('appt_date');
    const timeSelect = document.getElementById('appt_time');
    const confirmBtn = document.querySelector('button[name="book_now"]');

    if (!branchSelect.value || !dateInput.value) return;

    try {
        const response = await fetch(`get_branch_hours.php?branch_id=${branchSelect.value}`);
        const hours = await response.json();

        // Fix for InfinityFree/Browser timezone shifts
        const dateParts = dateInput.value.split('-');
        const selectedDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
        const dayOfWeek = selectedDate.getDay(); // 0=Sun, 1-6=Mon-Sat

        let openTime, closeTime;

        if (dayOfWeek === 0) { // Sunday
            openTime = hours.sun_open;
            closeTime = hours.sun_close;
        } else { // Monday to Saturday (1 to 6)
            openTime = hours.mon_sat_open;
            closeTime = hours.mon_sat_close;
        }

        // Check if closed (Logic: 00:00:00 or Null)
        if (!openTime || openTime === "00:00:00" || openTime === null) {
            confirmBtn.disabled = true;
            confirmBtn.innerText = "BRANCH CLOSED ON THIS DAY";
            confirmBtn.style.backgroundColor = "#f43f5e"; // rose-500
            timeSelect.innerHTML = '<option value="">Branch is closed</option>';
            return;
        }

        // If Open
        confirmBtn.disabled = false;
        confirmBtn.innerText = "CONFIRM APPOINTMENT";
        confirmBtn.style.backgroundColor = "#111827"; // gray-900
        
        generateTimeSlots(openTime, closeTime);

    } catch (error) {
        console.error("AJAX Error:", error);
        timeSelect.innerHTML = '<option value="">Error loading hours</option>';
    }
}

function generateTimeSlots(start, end) {
    const timeSelect = document.getElementById('appt_time');
    timeSelect.innerHTML = ''; // Clear the "Select branch & date first" message

    // Create date objects to compare times easily
    let current = new Date(`2026-01-01T${start}`);
    const stop = new Date(`2026-01-01T${end}`);

    // Add a default "Choose time" option
    const defaultOpt = document.createElement('option');
    defaultOpt.value = "";
    defaultOpt.textContent = "-- Choose a Time --";
    timeSelect.appendChild(defaultOpt);

    while (current < stop) {
        // Formats time to 12-hour format (e.g., 09:00 AM)
        const timeDisplay = current.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit', 
            hour12: true 
        });
        
        // Formats value to 24-hour format for database (e.g., 09:00:00)
        const timeValue = current.toTimeString().split(' ')[0];

        const option = document.createElement('option');
        option.value = timeValue;
        option.textContent = timeDisplay;
        timeSelect.appendChild(option);

        // Move to next 30-minute slot
        current.setMinutes(current.getMinutes() + 30);
    }
}
</script>

				<main class="flex-1 p-8 lg:p-12 overflow-y-auto custom-scrollbar bg-gray-50/20">
    <style>
        .log-scroll::-webkit-scrollbar { width: 5px; }
        .log-scroll::-webkit-scrollbar-track { background: transparent; }
        .log-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        .log-scroll::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        #toast-notification { transition: opacity 0.5s ease; }
    </style>

    <div class="max-w-7xl mx-auto">
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4 px-2">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Branch Appointments</h2>
                <p class="text-sm text-gray-500 font-medium">Schedule your visits and skip the line at our branches.</p>
            </div>
            
            <?php if(isset($_SESSION['booking_success'])): ?>
                <div id="toast-notification" class="bg-emerald-50 border-2 border-emerald-100 px-6 py-3 rounded-2xl flex items-center gap-3 text-emerald-700 shadow-sm animate-bounce">
                   <span class="material-symbols-outlined font-bold">check_circle</span>
                   <p class="text-xs font-black uppercase tracking-widest">Appointment Saved</p>
                </div>
                <?php unset($_SESSION['booking_success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['cancel_success'])): ?>
                <div id="toast-notification" class="bg-rose-50 border-2 border-rose-100 px-6 py-3 rounded-2xl flex items-center gap-3 text-rose-700 shadow-sm">
                   <span class="material-symbols-outlined font-bold">cancel</span>
                   <p class="text-xs font-black uppercase tracking-widest">Appointment Cancelled</p>
                </div>
                <?php unset($_SESSION['cancel_success']); ?>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-8 bg-white rounded-[2.5rem] border-2 border-gray-300 shadow-xl p-10">
                <form action="" method="POST" class="h-full flex flex-col">
                    
                    <?php if(isset($_SESSION['booking_error'])): ?>
                        <div id="error-notification" class="bg-rose-50 border-2 border-rose-100 p-6 rounded-3xl mb-8 flex items-start gap-4 text-rose-700 shadow-sm animate-pulse">
                            <span class="material-symbols-outlined font-bold text-rose-500">error</span>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1">Booking Failed</p>
                                <p class="text-xs font-bold leading-relaxed"><?= $_SESSION['booking_error'] ?></p>
                            </div>
                        </div>
                        <?php unset($_SESSION['booking_error']); ?>
                    <?php endif; ?>

                    <input type="hidden" name="service_type" id="service_type_input" value="Pawn Appraisal">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-6">1. Select Service</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                         <div onclick="setService('Pawn Appraisal', this)" class="service-option active border-2 border-primary-blue bg-blue-50/30 p-5 rounded-3xl cursor-pointer transition-all hover:shadow-md group">
                            <div class="flex justify-between items-start mb-3">
                                <div class="p-2 bg-white rounded-xl shadow-sm border border-gray-100"><span class="material-symbols-outlined text-matte-red text-xl">sell</span></div>
                                <span class="material-symbols-outlined text-primary-blue text-xl check-icon">check_circle</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm">Pawn Appraisal</h4>
                            <p class="text-[11px] text-gray-500 mt-1">Valuation for jewelry or gadgets.</p>
                        </div>
                        <div onclick="setService('Loan Payment', this)" class="service-option border-2 border-gray-200 bg-white p-5 rounded-3xl cursor-pointer transition-all hover:shadow-md group">
                            <div class="flex justify-between items-start mb-3">
                                <div class="p-2 bg-white rounded-xl shadow-sm border border-gray-100"><span class="material-symbols-outlined text-gray-500 text-xl">payments</span></div>
                                <span class="material-symbols-outlined text-primary-blue text-xl check-icon hidden">check_circle</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm">Loan Payment</h4>
                            <p class="text-[11px] text-gray-500 mt-1">Settle interest or principal.</p>
                        </div>
                        <div onclick="setService('Item Redemption', this)" class="service-option border-2 border-gray-200 bg-white p-5 rounded-3xl cursor-pointer transition-all hover:shadow-md group">
                            <div class="flex justify-between items-start mb-3">
                                <div class="p-2 bg-white rounded-xl shadow-sm border border-gray-100"><span class="material-symbols-outlined text-gray-500 text-xl">inventory_2</span></div>
                                <span class="material-symbols-outlined text-primary-blue text-xl check-icon hidden">check_circle</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm">Item Redemption</h4>
                            <p class="text-[11px] text-gray-500 mt-1">Retrieve your pawned items.</p>
                        </div>
                        <div onclick="setService('Consultation', this)" class="service-option border-2 border-gray-200 bg-white p-5 rounded-3xl cursor-pointer transition-all hover:shadow-md group">
                            <div class="flex justify-between items-start mb-3">
                                <div class="p-2 bg-white rounded-xl shadow-sm border border-gray-100"><span class="material-symbols-outlined text-gray-500 text-xl">forum</span></div>
                                <span class="material-symbols-outlined text-primary-blue text-xl check-icon hidden">check_circle</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm">Consultation</h4>
                            <p class="text-[11px] text-gray-500 mt-1">Speak with a financial expert.</p>
                        </div>
                    </div>

                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-6">2. Logistics & Notes</h3>
                    <div class="bg-gray-50/50 rounded-3xl p-8 border-2 border-gray-100 space-y-6 mb-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Select Branch</label>
                                <select name="branch_id" id="branch_id" onchange="validateSchedule()" required class="w-full p-4 border-2 border-gray-200 rounded-2xl text-sm font-bold bg-white outline-none focus:border-primary-blue transition-all">
                                    <option value="">-- Choose a Branch --</option>
                                    <?php
                                    $b_query = "SELECT branch_id, branch_name FROM branches";
                                    $b_result = mysqli_query($conn, $b_query);
                                    while($b_row = mysqli_fetch_assoc($b_result)) {
                                        echo "<option value='".$b_row['branch_id']."'>".$b_row['branch_name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Date</label>
                                    <input name="appt_date" id="appt_date" onchange="validateSchedule()" class="w-full p-4 border-2 border-gray-200 rounded-2xl text-sm font-bold bg-white outline-none focus:border-primary-blue transition-all" type="date" required min="<?= date('Y-m-d') ?>"/>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Time</label>
                                    <select name="appt_time" id="appt_time" class="w-full p-4 border-2 border-gray-200 rounded-2xl text-sm font-bold bg-white outline-none focus:border-primary-blue transition-all" required>
                                        <option value="">Select branch & date first</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Additional Notes</label>
                            <textarea name="notes" placeholder="Tell us more about your request..." rows="3" class="w-full p-4 border-2 border-gray-200 rounded-2xl text-sm font-medium bg-white outline-none focus:border-primary-blue transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <button type="submit" name="book_now" class="w-full py-5 bg-gray-900 text-white font-black text-[11px] uppercase tracking-[0.3em] rounded-2xl hover:bg-black transition-all shadow-xl active:scale-95">
                        Confirm Appointment
                    </button>
                </form>
            </div>

            <div class="col-span-12 lg:col-span-4 bg-white rounded-[2.5rem] border-2 border-gray-300 shadow-xl overflow-hidden flex flex-col h-[850px]">
                <div class="p-10 border-b-2 border-gray-100 bg-gray-50/30">
                    <h3 class="font-bold text-gray-900 flex items-center gap-3 tracking-tight">
                        <span class="material-symbols-outlined text-matte-red">history</span>
                        Appointment Log
                    </h3>
                </div>

                <div class="flex-1 overflow-y-auto log-scroll p-8 space-y-6">
                    <?php if ($appointments_log && $appointments_log->num_rows > 0): ?>
                        <?php while ($row = $appointments_log->fetch_assoc()): 
                            // 1. Get raw status
                            $db_raw = !empty($row['status']) ? $row['status'] : '';
                            $normalized = strtolower(trim($db_raw));

                            // 2. The Clean Mapping
                            $statusConfig = [
                                'pending'    => ['label' => 'Awaiting Visit', 'class' => 'bg-amber-50 text-amber-600 border-amber-200'],
                                'arrived'    => ['label' => 'In Branch',      'class' => 'bg-blue-50 text-blue-600 border-blue-200'],
                                'completed'  => ['label' => 'Finished',       'class' => 'bg-emerald-50 text-emerald-600 border-emerald-200'],
                                'cancelled'  => ['label' => 'Cancelled',      'class' => 'bg-rose-50 text-rose-600 border-rose-200'],
                                'expired'    => ['label' => 'No Show',        'class' => 'bg-gray-100 text-gray-400 border-gray-200']
                            ];

                            // 3. Logic Check
                            if (empty($normalized)) {
                                $currentConfig = ['label' => 'Processed', 'class' => 'bg-gray-100 text-gray-500 border-gray-200'];
                            } elseif (isset($statusConfig[$normalized])) {
                                $currentConfig = $statusConfig[$normalized];
                            } else {
                                $currentConfig = ['label' => ucwords($db_raw), 'class' => 'bg-purple-100 text-purple-700 border-purple-300'];
                            }

                            $is_pending = ($normalized == 'pending');
                        ?>
                            
                            <div class="group p-6 rounded-3xl border-2 border-gray-100 bg-white hover:border-primary-blue/30 transition-all relative">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter border <?= $currentConfig['class'] ?>">
                                        <?= htmlspecialchars($currentConfig['label']) ?>
                                    </span>
                                    
                                    <div class="text-right">
                                        <p class="text-[10px] text-gray-900 font-bold"><?= date('M d, Y', strtotime($row['appointment_date'])) ?></p>
                                        <p class="text-[9px] text-gray-400 font-medium">Ref: #<?= $row['appointment_id'] ?></p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h4 class="font-black text-gray-900 text-sm"><?= htmlspecialchars($row['service_type']) ?></h4>
                                    <div class="flex items-center gap-1 mt-1">
                                        <span class="material-symbols-outlined text-[12px] text-primary-blue">location_on</span>
                                        <p class="text-[10px] text-primary-blue font-bold uppercase tracking-tight"><?= htmlspecialchars($row['branch_name']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between border-t border-gray-50 pt-4 mt-2">
                                    <div class="flex items-center gap-2 text-gray-500">
                                        <span class="material-symbols-outlined text-sm">schedule</span>
                                        <p class="text-[10px] font-black uppercase"><?= date('h:i A', strtotime($row['appointment_time'])) ?></p>
                                    </div>

                                    <?php if ($is_pending): ?>
                                        <form method="POST" onsubmit="return confirm('Cancel this appointment?');">
                                            <input type="hidden" name="cancel_id" value="<?= $row['appointment_id'] ?>">
                                            <button type="submit" class="flex items-center gap-1 text-rose-400 hover:text-rose-600 transition-colors">
                                                <span class="material-symbols-outlined text-sm">cancel</span>
                                                <span class="text-[9px] font-black uppercase">Cancel</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center text-center py-20 px-6 h-full">
                            <span class="material-symbols-outlined text-5xl text-gray-200 mb-4">event_busy</span>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No Activity Found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    window.addEventListener('load', function() {
        const notification = document.getElementById('toast-notification');
        if (notification) {
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 500); 
            }, 3000);
        }
    });
</script>

<?php include './includes/footer.php'; ?>