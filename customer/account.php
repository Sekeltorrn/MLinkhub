<?php
include './includes/header.php';
$user_id = $_SESSION['user_id'];
$message = "";

// 1. Fetch COMPREHENSIVE data from both tables
$query = $conn->prepare("
    SELECT 
        u.username, u.email, u.created_at,
        c.first_name, c.middle_name, c.last_name, 
        c.contact_number, c.address, c.birth_date, c.gender, c.status,
        c.id_type, c.id_image_path
    FROM users u 
    JOIN customers c ON u.user_id = c.user_id 
    WHERE u.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$user_data = $query->get_result()->fetch_assoc();

// Assign variables for cleaner HTML
$username    = $user_data['username'];
$email       = $user_data['email'];
$created_at  = $user_data['created_at'];
$first_name  = $user_data['first_name'];
$middle_name = $user_data['middle_name'];
$last_name   = $user_data['last_name'];
$phone       = $user_data['contact_number'];
$address     = $user_data['address'] ?? 'No address provided';
$dob         = $user_data['birth_date'] ? date('F d, Y', strtotime($user_data['birth_date'])) : 'Not Set';
$status      = $user_data['status']; // e.g., 'unverified', 'pending', 'verified'

// Verification Logic (Google-style checks)
$has_email   = !empty($email);
$has_profile = !empty($address) && $address !== 'No address provided';
$has_id      = !empty($user_data['id_image_path']);
$is_verified = ($status === 'verified');

// Calculate Verification %
$progress = 0;
if($has_email) $progress += 33;
if($has_profile) $progress += 33;
if($has_id) $progress += 34;
?>

<main class="flex-1 p-8 overflow-y-auto custom-scrollbar">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Account & Security</h2>
                <p class="text-sm text-gray-500">Manage your digital identity and verification documents.</p>
            </div>

            <div class="flex items-center gap-3 bg-white p-2 pr-5 rounded-2xl border-2 border-gray-100 shadow-sm">
                <?php if ($is_verified): ?>
                    <div class="size-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600">
                        <span class="material-symbols-outlined">verified</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">Account Status</p>
                        <p class="text-sm font-bold text-green-700">Fully Verified</p>
                    </div>
                <?php else: ?>
                    <div class="size-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                        <span class="material-symbols-outlined">pending</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none">Account Status</p>
                        <p class="text-sm font-bold text-amber-700"><?= ucfirst($status) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            
            <div class="col-span-12 lg:col-span-5 bg-white rounded-[2.5rem] border-2 border-gray-100 shadow-xl shadow-gray-200/40 p-10">
                <h3 class="font-bold text-gray-900 flex items-center gap-2 mb-10">
                    <span class="material-symbols-outlined text-primary-blue">contact_page</span>
                    Profile Information
                </h3>
                
                <div class="space-y-8">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Username</p>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($username) ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Email Address</p>
                        <div class="border-b border-gray-100 pb-2 flex justify-between">
                            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($email) ?></p>
                            <span class="text-[9px] font-black text-green-500 uppercase">Verified</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Mobile Number</p>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($phone) ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Member Since</p>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-semibold text-gray-800"><?= date('F d, Y', strtotime($created_at)) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-7 bg-white rounded-[2.5rem] border-2 border-gray-100 shadow-xl shadow-gray-200/40 p-10">
                <h3 class="font-bold text-gray-900 flex items-center gap-2 mb-10">
                    <span class="material-symbols-outlined text-matte-red">badge</span>
                    Personal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Full Name</p>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($first_name . " " . $middle_name . " " . $last_name) ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Date of Birth</p>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-semibold text-gray-800"><?= $dob ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Gender</p>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-semibold text-gray-800"><?= $user_data['gender'] ?? 'Not Specified' ?></p>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-1">Home Address</p>
                        <div class="border-b border-gray-100 pb-2">
                            <p class="text-sm font-semibold text-gray-800 leading-relaxed"><?= htmlspecialchars($address) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-6 bg-white rounded-[2.5rem] border-2 border-gray-100 shadow-xl shadow-gray-200/40 p-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500">fact_check</span>
                        Complete Your Profile
                    </h3>
                    <span class="text-[10px] font-black text-primary-blue bg-blue-50 px-3 py-1 rounded-full uppercase"><?= $progress ?>% Done</span>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 <?= $has_email ? 'bg-green-50 border-green-100' : 'bg-gray-50 border-gray-100' ?> rounded-2xl border">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined <?= $has_email ? 'text-green-500' : 'text-gray-300' ?>">check_circle</span>
                            <span class="text-xs font-bold text-gray-700">Account Registration</span>
                        </div>
                        <span class="text-[9px] font-bold text-green-600 uppercase">Complete</span>
                    </div>

                    <div class="flex items-center justify-between p-4 <?= $has_profile ? 'bg-green-50 border-green-100' : 'bg-white border-blue-100 shadow-sm' ?> rounded-2xl border">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined <?= $has_profile ? 'text-green-500' : 'text-blue-500' ?>">
                                <?= $has_profile ? 'check_circle' : 'info' ?>
                            </span>
                            <span class="text-xs font-bold text-gray-700">Personal Details & Address</span>
                        </div>
                        <?php if(!$has_profile): ?>
                            <button class="text-[10px] font-black text-primary-blue hover:underline uppercase">Add Info</button>
                        <?php else: ?>
                            <span class="text-[9px] font-bold text-green-600 uppercase">Complete</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center justify-between p-4 <?= $has_id ? 'bg-green-50 border-green-100' : 'bg-white border-amber-100 shadow-sm' ?> rounded-2xl border">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined <?= $has_id ? 'text-green-500' : 'text-amber-500' ?>">
                                <?= $has_id ? 'check_circle' : 'file_upload' ?>
                            </span>
                            <span class="text-xs font-bold text-gray-700">Government Issued ID</span>
                        </div>
                        <?php if(!$has_id): ?>
                            <a href="upload_id.php" class="text-[10px] font-black text-amber-600 hover:underline uppercase">Upload Now</a>
                        <?php else: ?>
                            <span class="text-[9px] font-bold text-green-600 uppercase">Uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-6 bg-white rounded-[2.5rem] border-2 border-gray-100 shadow-xl shadow-gray-200/40 p-10">
                <h3 class="font-bold text-gray-900 flex items-center gap-2 mb-8">
                    <span class="material-symbols-outlined text-gray-400">inventory_2</span>
                    Identity Documents
                </h3>
                <?php if($has_id): ?>
                    <div class="relative group aspect-video bg-gray-900 rounded-[2rem] overflow-hidden border-2 border-gray-100">
                        <img src="<?= htmlspecialchars($user_data['id_image_path']) ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                        <div class="absolute bottom-4 left-4">
                            <p class="text-[10px] font-black text-white uppercase tracking-widest bg-black/50 px-3 py-1 rounded-lg backdrop-blur-md">
                                Type: <?= htmlspecialchars($user_data['id_type']) ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-48 border-2 border-dashed border-gray-100 rounded-[2rem] text-gray-300">
                        <span class="material-symbols-outlined text-3xl mb-2">cloud_upload</span>
                        <p class="text-[10px] font-bold uppercase tracking-widest">No Documents Found</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-span-12 bg-white rounded-[2.5rem] border-2 border-gray-900 shadow-xl p-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div class="flex items-center gap-6">
                        <div class="size-16 rounded-[1.5rem] bg-gray-900 flex items-center justify-center text-white">
                            <span class="material-symbols-outlined text-3xl">lock</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Security Settings</h3>
                            <p class="text-xs text-gray-400 mt-1">Manage your password and protection level.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-10">
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Current Password</p>
                            <p class="text-sm font-bold tracking-[0.4em]">••••••••</p>
                        </div>
                        <button class="px-8 py-4 bg-gray-900 text-white text-[10px] font-bold rounded-2xl hover:bg-black transition-all uppercase tracking-widest">
                            Update Password
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>