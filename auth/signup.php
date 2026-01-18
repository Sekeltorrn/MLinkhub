<?php 
session_start(); 

// NEW: Clear sticky data if we arrived here fresh (no error active)
if (!isset($_SESSION['error'])) {
    unset($_SESSION['form_data']);
}

$fd = $_SESSION['form_data'] ?? []; 
$sticky_m = $fd['dob_month'] ?? '';
$sticky_d = $fd['dob_day'] ?? '';
$sticky_y = $fd['dob_year'] ?? '';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Sign Up | Pulse Pawn</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { "primary-blue": "#005fb2", "muted-red": "#7a1a1a" } } } }
    </script>
    <style type="text/tailwindcss">
        .diagonal-split { clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%); }
        .input-box { @apply w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-medium focus:ring-2 focus:ring-primary-blue/20 transition-all; }
        .check-item { @apply flex items-center gap-2 text-[10px] font-bold text-slate-300 transition-colors; }
        .check-item.met { @apply text-emerald-500; }
        .check-item.met span { @apply font-black; }
    </style>
</head>
<body class="bg-slate-50 antialiased">
<main class="relative flex min-h-screen w-full items-center justify-center overflow-x-hidden py-12">
    
    <div class="fixed inset-0 z-0 w-[60%] bg-muted-red diagonal-split hidden lg:flex flex-col justify-center px-24">
        <div class="max-w-xl text-white/90">
            <h1 class="text-6xl font-extrabold leading-tight tracking-tight mb-4 text-white">Secure. <br/>Integrated. <br/>Refined.</h1>
            <p class="text-white/40 uppercase tracking-[0.3em] font-bold text-xs">Mlinkhub System v2.0</p>
        </div>
    </div>
    
    <div class="relative z-10 flex w-full max-w-6xl items-center justify-end px-6 lg:px-16">
        <div class="relative w-full max-w-[550px] bg-white rounded-[2.5rem] shadow-2xl border border-white/50 p-10">
            
            <header class="mb-8">
                <h2 class="text-3xl font-extrabold text-slate-900 mb-1">Create Account</h2>
                <p class="text-slate-400 text-[10px] uppercase tracking-widest font-black">All fields are required</p>
            </header>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 text-red-600 p-4 rounded-2xl border border-red-100 text-[11px] font-bold uppercase tracking-tight animate-pulse">
                    ⚠️ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="process_signup.php" class="space-y-4" autocomplete="off">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Username</label>
                    <input name="username" class="input-box" placeholder="Enter unique username" type="text" value="<?= $fd['username'] ?? '' ?>" required/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input name="first_name" class="input-box" placeholder="First Name" type="text" value="<?= $fd['first_name'] ?? '' ?>" required/>
                    <input name="middle_name" class="input-box" placeholder="Middle Name" type="text" value="<?= $fd['middle_name'] ?? '' ?>"/>
                    <input name="last_name" class="input-box" placeholder="Last Name" type="text" value="<?= $fd['last_name'] ?? '' ?>" required/>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <select name="gender" class="input-box" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?= ($fd['gender']??'') == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($fd['gender']??'') == 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                    <div class="space-y-1">
                        <div class="flex gap-1">
                            <select id="dob_m" name="dob_month" class="w-1/3 bg-slate-50 border-none rounded-lg p-2 text-[10px] font-bold" required></select>
                            <select id="dob_d" name="dob_day" class="w-1/3 bg-slate-50 border-none rounded-lg p-2 text-[10px] font-bold" required></select>
                            <select id="dob_y" name="dob_year" class="w-1/3 bg-slate-50 border-none rounded-lg p-2 text-[10px] font-bold" required></select>
                        </div>
                        <p id="ageWarning" class="text-[9px] font-black text-red-500 hidden uppercase mt-1">Under 18 not allowed</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input name="email" class="input-box" placeholder="Email Address" type="email" value="<?= $fd['email'] ?? '' ?>" required/>
                    <input name="mobile" class="input-box" placeholder="Mobile Number (09...)" type="text" value="<?= $fd['mobile'] ?? '' ?>" required/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Secure Password</label>
                        <div class="relative group">
                            <input id="passIn" name="password" class="input-box bg-slate-50 text-slate-900 placeholder:text-slate-300" placeholder="Enter your password" type="password" autocomplete="new-password" required>
                            <button type="button" onclick="toggleVisibility('passIn', 'eye1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-blue">
                                <span id="eye1" class="material-symbols-outlined text-lg">visibility</span>
                            </button>
                        </div>
                        <div class="relative group">
                            <input id="confirmPass" name="confirm_password" class="input-box bg-slate-100" placeholder="Confirm Password" type="password" autocomplete="new-password" required/>
                            <button type="button" onclick="toggleVisibility('confirmPass', 'eye2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-blue">
                                <span id="eye2" class="material-symbols-outlined text-lg">visibility</span>
                            </button>
                        </div>
                        <p id="matchError" class="text-[9px] font-bold text-red-500 hidden uppercase">Passwords do not match</p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 space-y-2 border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase mb-2">Security Rules:</p>
                        <div id="req-len" class="check-item"><span class="material-symbols-outlined text-sm">circle</span> 8+ Characters</div>
                        <div id="req-up" class="check-item"><span class="material-symbols-outlined text-sm">circle</span> 1 Uppercase</div>
                        <div id="req-low" class="check-item"><span class="material-symbols-outlined text-sm">circle</span> 1 Lowercase</div>
                        <div id="req-num" class="check-item"><span class="material-symbols-outlined text-sm">circle</span> 1 Number</div>
                        <div id="req-spec" class="check-item"><span class="material-symbols-outlined text-sm">circle</span> 1 Special Char</div>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-primary-blue text-white font-black py-4 rounded-2xl mt-6 opacity-50 cursor-not-allowed transition-all uppercase tracking-widest text-xs shadow-xl shadow-primary-blue/20">
                    Initialize Account
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col items-center gap-4">
                <p class="text-sm text-slate-500 font-medium">
                    Already a member? 
                    <a class="text-primary-blue font-bold hover:text-slate-900 transition-colors ml-1" href="login.php">
                        Sign In here
                    </a>
                </p>
                <div class="flex gap-4 text-[9px] text-slate-400 font-bold uppercase tracking-[0.2em]">
                    <a href="#" class="hover:text-primary-blue transition-colors">Terms</a>
                    <span class="opacity-20">•</span>
                    <a href="#" class="hover:text-primary-blue transition-colors">Privacy Policy</a>
                </div>
            </div>
        </div> </div> </main>

<script>
    const stickyM = "<?= $sticky_m ?>";
    const stickyD = "<?= $sticky_d ?>";
    const stickyY = "<?= $sticky_y ?>";

    function toggleVisibility(inputId, eyeId) {
        const input = document.getElementById(inputId);
        const eye = document.getElementById(eyeId);
        input.type = input.type === 'password' ? 'text' : 'password';
        eye.innerText = input.type === 'password' ? 'visibility' : 'visibility_off';
    }

    // Dynamic DOB
    const mSel = document.getElementById('dob_m'), dSel = document.getElementById('dob_d'), ySel = document.getElementById('dob_y');
    ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"].forEach((m, i) => {
        const val = i + 1;
        const isSelected = val == stickyM ? 'selected' : '';
        mSel.innerHTML += `<option value="${val}" ${isSelected}>${m}</option>`;
    });
    for(let d=1; d<=31; d++) {
        const isSelected = d == stickyD ? 'selected' : '';
        dSel.innerHTML += `<option value="${d}" ${isSelected}>${d}</option>`;
    }
    for(let y=new Date().getFullYear(); y>=1920; y--) {
        const isSelected = y == stickyY ? 'selected' : '';
        ySel.innerHTML += `<option value="${y}" ${isSelected}>${y}</option>`;
    }

    function checkAge() {
        const y = ySel.value, m = mSel.value, d = dSel.value;
        if(!y || !m || !d) return false;
        const birthDate = new Date(y, m - 1, d);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        if (today.getMonth() < birthDate.getMonth() || (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) age--;
        
        const isOldEnough = age >= 18;
        document.getElementById('ageWarning').classList.toggle('hidden', isOldEnough);
        return isOldEnough;
    }

    // Validation Logic
    const passIn = document.getElementById('passIn');
    const confirmIn = document.getElementById('confirmPass');
    const btn = document.getElementById('submitBtn');

    passIn.addEventListener('input', () => {
        const val = passIn.value;
        const requirements = {
            'req-len': val.length >= 8,
            'req-up': /[A-Z]/.test(val),
            'req-low': /[a-z]/.test(val),
            'req-num': /[0-9]/.test(val),
            'req-spec': /[^A-Za-z0-9]/.test(val)
        };
        for (const [id, met] of Object.entries(requirements)) {
            const el = document.getElementById(id);
            el.classList.toggle('met', met);
            el.querySelector('span').innerText = met ? 'check_circle' : 'circle';
        }
        validateForm();
    });

    [passIn, confirmIn, mSel, dSel, ySel].forEach(el => el.addEventListener('input', validateForm));

    function validateForm() {
        // FIXED: Added /[a-z]/ to ensure lowercase is actually required by the button
        const passMet = passIn.value.length >= 8 && 
                        /[A-Z]/.test(passIn.value) && 
                        /[a-z]/.test(passIn.value) && 
                        /[0-9]/.test(passIn.value) && 
                        /[^A-Za-z0-9]/.test(passIn.value);
        
        const matches = passIn.value === confirmIn.value && passIn.value !== "";
        const oldEnough = checkAge();
        
        document.getElementById('matchError').classList.toggle('hidden', matches || confirmIn.value === "");

        if (passMet && matches && oldEnough) {
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            btn.disabled = false;
        } else {
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btn.disabled = true;
        }
    }
    validateForm();
</script>
</body>
</html>