<?php
// employee/create_ticket.php
require_once '../db.php'; 
if (session_status() === PHP_SESSION_NONE) session_start();
include './includes/header.php'; 

// Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

$branch_id   = $_SESSION['branch_id'];
$branch_name = $_SESSION['branch_name'] ?? "ML-BRANCH";

// 1. Fetch Customers [CORRECTED QUERY]
$customers = $conn->query("SELECT u.user_id, c.first_name, c.last_name, c.status, c.valid_id_type, c.valid_id_num 
                           FROM users u 
                           JOIN customers c ON u.user_id = c.user_id 
                           WHERE u.role = 'customer'");

// Store customer data for JavaScript
$customer_data = [];
$customer_options = "";

if ($customers) {
    while($c = $customers->fetch_assoc()) {
        $customer_data[$c['user_id']] = [
            'status'    => $c['status'],
            'id_type'   => $c['valid_id_type'], 
            'id_number' => $c['valid_id_num']   
        ];
        $customer_options .= "<option value='{$c['user_id']}'>{$c['last_name']}, {$c['first_name']}</option>";
    }
}

// Settings (Service Fee is now dynamic in JS, not static PHP)
$gold_rate_gram = 4000; 
$ltv_ratio      = 0.60; 
$interest_rate  = 0.03; // 3% Monthly
?>

<main class="flex-1 p-8 bg-midnight custom-scrollbar overflow-y-auto">
    <div class="max-w-7xl mx-auto">
        
        <header class="mb-8 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="loans.php" class="bg-white/5 hover:bg-white/10 text-white p-3 rounded-xl transition-all group">
                    <span class="material-symbols-outlined text-slate-400 group-hover:text-white">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-light text-white uppercase tracking-tight">New Loan Authorization</h2>
                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-[0.2em] mt-1">Terminal: <span class="text-brand-red"><?= $branch_name ?></span></p>
                </div>
            </div>
        </header>

        <form action="process_ticket.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8 space-y-6">
                
                <div class="bg-navy-700 p-8 rounded-[2rem] border border-white/5 shadow-2xl relative overflow-hidden">
                    <h3 class="text-white font-bold mb-6 flex items-center gap-2 text-xs uppercase tracking-widest relative z-10">
                        <span class="material-symbols-outlined text-brand-red">person_pin</span> Customer Identity
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Select Account</label>
                            <select name="customer_id" id="customer_select" onchange="updateCustomerInfo()" required class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm outline-none focus:border-brand-red transition-all mt-1">
                                <option value="" disabled selected>Search Database...</option>
                                <?= $customer_options ?>
                            </select>
                        </div>

                        <div>
                            <div class="flex justify-between">
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Identification Source (Sec. 4323P.q)</label>
                                <span id="id_status_badge" class="text-[9px] font-bold uppercase hidden px-2 rounded bg-emerald-500/20 text-emerald-400">Verified ID Linked</span>
                            </div>
                            
                            <div class="relative mt-1">
                                <input type="text" name="item_description" id="physical_id_input" required placeholder="Enter ID Type & Number (Walk-in)" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm outline-none focus:border-brand-red transition-all">
                                <span class="material-symbols-outlined absolute right-4 top-4 text-slate-600" id="id_lock_icon">edit</span>
                            </div>
                            <p class="text-[9px] text-slate-500 mt-2 px-1" id="id_help_text">Select a customer to check for registered IDs.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-navy-700 p-8 rounded-[2rem] border border-white/5 shadow-2xl">
                    <h3 class="text-white font-bold mb-6 flex items-center gap-2 text-xs uppercase tracking-widest">
                        <span class="material-symbols-outlined text-brand-red">diamond</span> Item Appraisal (Sec. 4323P.m)
                    </h3>
                    
                    <div class="flex gap-4 mb-6 bg-midnight p-2 rounded-2xl">
                        <button type="button" onclick="setMode('jewelry')" id="btn-jewelry" class="flex-1 py-3 rounded-xl bg-brand-red text-white font-black uppercase text-[10px] tracking-widest transition-all">Jewelry</button>
                        <button type="button" onclick="setMode('non-jewelry')" id="btn-non-jewelry" class="flex-1 py-3 rounded-xl bg-transparent text-slate-500 font-black uppercase text-[10px] tracking-widest transition-all">Non-Jewelry / Other</button>
                    </div>

                    <input type="hidden" name="item_type" id="input-item-type" value="jewelry">
                    <input type="hidden" name="item_condition_text" id="item_condition_text" value="Good / Wearable">

                    <div id="jewelry-fields" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Karat</label>
                                <select id="karat" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm mt-1">
                                    <option value="1.0">24k (Pure)</option>
                                    <option value="0.916">22k</option>
                                    <option value="0.75" selected>18k (Standard)</option>
                                    <option value="0.585">14k</option>
                                    <option value="0.417">10k</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Weight (g)</label>
                                <input type="number" id="weight" step="0.01" placeholder="0.00" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm mt-1">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Jewelry Condition (For Audit)</label>
                            <select id="jewelry-condition" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm mt-1">
                                <option value="Good">Good / Wearable</option>
                                <option value="Broken">Broken / Scrap Gold</option>
                                <option value="New">Brand New / Pristine</option>
                                <option value="Pawnable">Pawnable (Stone Missing)</option>
                            </select>
                        </div>
                    </div>

                    <div id="non-jewelry-fields" class="hidden space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Market Value (₱)</label>
                                <input type="number" id="market-val" placeholder="0.00" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm mt-1">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Condition</label>
                                <select id="condition" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm mt-1">
                                    <option value="1.0">Brand New / Sealed (100%)</option>
                                    <option value="0.8" selected>Good Condition (80%)</option>
                                    <option value="0.6">Fair / Used (60%)</option>
                                    <option value="0.4">Damaged / Parts (40%)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Item Description (Sec. 4323P.m)</label>
                        <input type="text" name="item_name" required placeholder="e.g. 18k Gold Ring (5g) or Power Drill" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm mt-1">
                    </div>
                </div>

                <div class="bg-navy-700 p-8 rounded-[2rem] border border-white/5 shadow-2xl">
                    <h3 class="text-white font-bold mb-6 flex items-center gap-2 text-xs uppercase tracking-widest">
                        <span class="material-symbols-outlined text-brand-red">archive</span> Custodianship
                    </h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Vault Location</label>
                            <select name="storage_location" class="w-full bg-midnight border border-white/10 rounded-xl p-4 text-white text-sm mt-1">
                                <option>Main Vault - Row A</option>
                                <option>Non-Jewelry Shelf 1</option>
                                <option>Safe Box</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Proof Photo</label>
                            <input type="file" name="item_image" required accept="image/*" class="w-full text-[10px] text-slate-400 mt-2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-navy-700 p-8 rounded-[2.5rem] border-2 border-brand-red/30 shadow-2xl sticky top-8">
                    
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-6 text-center">Transaction Breakdown</p>

                    <div class="text-center py-8 bg-midnight rounded-[2rem] border border-white/5 mb-6">
                        <p class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Net Cash Out (Sec. 4323P.f)</p>
                        <div class="flex items-center justify-center gap-1">
                            <span class="text-slate-500 text-2xl font-light">₱</span>
                            <span id="display-net" class="text-5xl font-black text-white tracking-tighter">0.00</span>
                        </div>
                    </div>

                    <div class="space-y-3 px-2">
                        <div class="flex justify-between text-xs text-slate-400">
                            <span>Appraised Value (Sec. 4323P.k)</span>
                            <span id="display-appraised" class="text-white">₱0.00</span>
                        </div>
                        
                        <div class="flex justify-between text-xs font-bold text-white border-b border-white/5 pb-3">
                            <span>Principal (<?= $ltv_ratio * 100 ?>% LTV)</span>
                            <span id="display-principal">₱0.00</span>
                        </div>
                        
                        <p class="text-[9px] font-black text-slate-500 uppercase mt-3">Deductions</p>
                        
                        <div class="flex justify-between text-xs text-brand-red">
                            <span>Adv. Interest (<?= $interest_rate * 100 ?>%)</span>
                            <span id="display-interest">- ₱0.00</span>
                        </div>
                        
                        <div class="flex justify-between text-xs text-brand-red">
                            <div class="flex flex-col">
                                <span>Service Charge (Sec. 4303P.2)</span>
                                <span class="text-[8px] text-slate-500 italic">Max ₱5.00 or 1% of Loan</span>
                            </div>
                            <span id="display-fee">- ₱0.00</span>
                        </div>
                    </div>

                    <input type="hidden" name="principal_amount" id="input-principal">
                    <input type="hidden" name="net_proceeds" id="input-net">
                    <input type="hidden" name="service_charge" id="input-service-charge">

                    <button type="submit" class="w-full mt-8 bg-brand-red hover:bg-red-700 text-white font-black py-5 rounded-2xl uppercase tracking-[0.2em] text-[10px] shadow-lg shadow-red-900/40 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">verified</span> Authorize Loan
                    </button>
                    
                    <div class="mt-4 text-center">
                        <p class="text-[9px] text-slate-500">
                            Maturity: 30 Days | Redemption Expiry: 120 Days
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>



<script>
    const CUSTOMERS = <?= json_encode($customer_data) ?>;
    const GOLD_RATE = <?= $gold_rate_gram ?>, LTV = <?= $ltv_ratio ?>, INT_RATE = <?= $interest_rate ?>;
    let currentMode = 'jewelry';

    // Smart ID Logic
    function updateCustomerInfo() {
        const userId = document.getElementById('customer_select').value;
        const idInput = document.getElementById('physical_id_input');
        const badge = document.getElementById('id_status_badge');
        const lockIcon = document.getElementById('id_lock_icon');
        const helpText = document.getElementById('id_help_text');

        if (CUSTOMERS[userId]) {
            const c = CUSTOMERS[userId];
            if (c.id_type && c.id_number) {
                idInput.value = `${c.id_type} - ${c.id_number}`;
                idInput.setAttribute('readonly', true);
                idInput.classList.add('opacity-50', 'cursor-not-allowed');
                badge.classList.remove('hidden');
                lockIcon.innerText = 'lock';
                helpText.innerText = "Using verified ID from customer profile.";
                helpText.classList.add('text-emerald-500');
            } else {
                resetIdField();
                helpText.innerText = "User verified, but no ID on file. Please enter manually.";
            }
        } else {
            resetIdField();
        }
    }

    function resetIdField() {
        const idInput = document.getElementById('physical_id_input');
        const badge = document.getElementById('id_status_badge');
        const lockIcon = document.getElementById('id_lock_icon');
        const helpText = document.getElementById('id_help_text');

        idInput.value = "";
        idInput.removeAttribute('readonly');
        idInput.classList.remove('opacity-50', 'cursor-not-allowed');
        badge.classList.add('hidden');
        lockIcon.innerText = 'edit';
        helpText.innerText = "Enter the ID details presented by the customer.";
        helpText.classList.remove('text-emerald-500');
    }

    // Appraisal Mode
    function setMode(mode) {
        currentMode = mode;
        document.getElementById('input-item-type').value = mode;
        document.getElementById('jewelry-fields').classList.toggle('hidden', mode !== 'jewelry');
        document.getElementById('non-jewelry-fields').classList.toggle('hidden', mode !== 'non-jewelry');

        const activeClass = "flex-1 py-3 rounded-xl bg-brand-red text-white font-black uppercase text-[10px] tracking-widest transition-all";
        const inactiveClass = "flex-1 py-3 rounded-xl bg-transparent text-slate-500 font-black uppercase text-[10px] tracking-widest transition-all";
        
        document.getElementById('btn-jewelry').className = mode === 'jewelry' ? activeClass : inactiveClass;
        document.getElementById('btn-non-jewelry').className = mode === 'non-jewelry' ? activeClass : inactiveClass;

        calculate();
    }

    // FINANCIAL LOGIC (Updated to Regulation Standards)
    function calculate() {
        let appraisedVal = 0;
        
        if (currentMode === 'jewelry') {
            const karat = parseFloat(document.getElementById('karat').value);
            const weight = parseFloat(document.getElementById('weight').value) || 0;
            appraisedVal = weight * karat * GOLD_RATE;
            
            const jCond = document.getElementById('jewelry-condition');
            document.getElementById('item_condition_text').value = jCond.options[jCond.selectedIndex].text;
        } else {
            const market = parseFloat(document.getElementById('market-val').value) || 0;
            const cond = parseFloat(document.getElementById('condition').value);
            appraisedVal = market * cond; 

            const condSelect = document.getElementById('condition');
            document.getElementById('item_condition_text').value = condSelect.options[condSelect.selectedIndex].text;
        }

        const principal = appraisedVal * LTV;
        
        // 1. Interest (Section 4303P.1)
        const interest = principal * INT_RATE;
        
        // 2. Service Charge (Section 4303P.2)
        // Logic: 1% of Principal, but MAX 5.00
        let serviceCharge = principal * 0.01;
        if (serviceCharge > 5.00) {
            serviceCharge = 5.00;
        }
        
        const net = principal - interest - serviceCharge;
        const finalNet = net > 0 ? net : 0;

        const fmt = (num) => num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        document.getElementById('display-appraised').innerText = '₱' + fmt(appraisedVal);
        document.getElementById('display-principal').innerText = '₱' + fmt(principal);
        document.getElementById('display-interest').innerText = '- ₱' + fmt(interest);
        document.getElementById('display-fee').innerText = '- ₱' + fmt(serviceCharge); // Dynamic Display
        document.getElementById('display-net').innerText = fmt(finalNet);

        document.getElementById('input-principal').value = principal.toFixed(2);
        document.getElementById('input-net').value = finalNet.toFixed(2);
        document.getElementById('input-service-charge').value = serviceCharge.toFixed(2); // Send to backend
    }

    ['karat', 'weight', 'market-val', 'condition', 'jewelry-condition'].forEach(id => {
        document.getElementById(id).addEventListener('input', calculate);
    });
</script>

<?php include './includes/footer.php'; ?>