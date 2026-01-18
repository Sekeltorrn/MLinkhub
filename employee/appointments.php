<?php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();
include './includes/header.php'; 
?>
<main class="flex-1 p-8 bg-midnight custom-scrollbar overflow-y-auto">
    <div class="max-w-5xl mx-auto space-y-8">
        
        <div class="flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-light text-white uppercase">Appointment Desk</h2>
                <p class="text-sm text-slate-400">Incoming customer visits for today.</p>
            </div>
            <div class="bg-navy-700 px-4 py-2 rounded-xl border border-white/5">
                <p class="text-[10px] font-black text-brand-red uppercase">Current Date</p>
                <p class="text-lg font-bold text-white"><?= date('F d, Y') ?></p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex gap-6 group">
                <div class="w-20 text-right pt-2">
                    <p class="text-lg font-bold text-white">09:00</p>
                    <p class="text-xs text-slate-500">AM</p>
                </div>
                <div class="flex-1 bg-navy-700 p-6 rounded-2xl border border-white/5 group-hover:border-brand-red/50 transition-all shadow-lg relative">
                    <div class="absolute top-6 right-6">
                        <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">New Appraisal</span>
                    </div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="size-12 rounded-full bg-midnight border border-white/10 flex items-center justify-center text-white font-bold">M</div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Maria Clara</h3>
                            <p class="text-slate-400 text-xs">ID Verified Customer</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-300 bg-midnight/50 p-3 rounded-lg border border-white/5">
                        <span class="text-slate-500 uppercase text-[10px] font-bold mr-2">Note:</span>
                        Coming in to appraise a vintage watch.
                    </p>
                    <div class="mt-4 flex gap-3">
                        <button class="flex-1 bg-brand-red text-white py-2 rounded-lg text-[10px] font-black uppercase">Mark Arrived</button>
                        <button class="px-4 py-2 border border-white/10 text-slate-400 hover:text-white rounded-lg text-[10px] font-black uppercase">Reschedule</button>
                    </div>
                </div>
            </div>

            <div class="flex gap-6 group opacity-50">
                <div class="w-20 text-right pt-2">
                    <p class="text-lg font-bold text-white">10:30</p>
                    <p class="text-xs text-slate-500">AM</p>
                </div>
                <div class="flex-1 bg-navy-700 p-6 rounded-2xl border border-white/5">
                    <p class="text-slate-500 italic text-sm">No appointments scheduled.</p>
                </div>
            </div>
        </div>

    </div>
</main>
<?php include './includes/footer.php'; ?>