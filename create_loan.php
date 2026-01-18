<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Pawn Ticket | Pulse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0a0f18; color: #cbd5e1; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body class="p-6 md:p-12">

<?php require_once 'db.php'; ?>

<div class="max-w-2xl mx-auto">
    <div class="glass p-8 rounded-3xl border-t-4 border-emerald-500 shadow-2xl">
        <h2 class="text-2xl font-black text-white uppercase tracking-tight mb-6 text-emerald-500">Issue New Pawn Ticket</h2>
        
        <form action="process_loan.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-2">Select Verified Pawner</label>
                <select name="customer_id" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-emerald-500 outline-none appearance-none">
                    <?php
                    $customers = $conn->query("SELECT customer_id, first_name, last_name FROM customers");
                    while($row = $customers->fetch_assoc()) {
                        echo "<option value='".$row['customer_id']."'>".$row['first_name']." ".$row['last_name']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Item Description</label>
                    <input type="text" name="item_name" placeholder="e.g. 18k Gold Necklace" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Category</label>
                    <select name="category_id" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-emerald-500 outline-none">
                        <?php
                        $cats = $conn->query("SELECT * FROM categories");
                        while($cat = $cats->fetch_assoc()) {
                            echo "<option value='".$cat['category_id']."'>".$cat['category_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Principal Amount (₱)</label>
                    <input type="number" step="0.01" name="amount" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Monthly Interest (%)</label>
                    <input type="number" step="0.1" name="interest" value="3.5" class="w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-emerald-500 outline-none">
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-5 rounded-2xl uppercase tracking-widest text-sm transition-all shadow-lg shadow-emerald-900/20">
                Authorize & Generate Ticket
            </button>
        </form>
    </div>
</div>

</body>
</html>