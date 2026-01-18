<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Identity | Pulse Pawn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0a0f18; color: #cbd5e1; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body class="p-6">

<div class="max-w-lg mx-auto mt-10">
    <div class="glass p-8 rounded-3xl border-t-4 border-amber-500 shadow-2xl">
        <h2 class="text-2xl font-black text-white uppercase tracking-tight mb-2">Verification Center</h2>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-8">Step 2: Complete your Profile</p>

        <form action="process_verification.php" method="POST" class="space-y-4">
            <input type="hidden" name="user_id" value="1"> 

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Full Address</label>
                <textarea name="address" required class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-3 text-sm text-white outline-none focus:border-amber-500"></textarea>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Government ID Type</label>
                <select name="id_type" class="w-full bg-slate-900 border border-white/10 rounded-lg px-4 py-3 text-sm text-white outline-none">
                    <option>UMID</option>
                    <option>Driver's License</option>
                    <option>Passport</option>
                    <option>PhilSys ID</option>
                </select>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 rounded-xl uppercase tracking-widest text-xs transition-all">
                    Submit for Review
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>