<?php
session_start();
$pageTitle = "Tournaments";
include 'includes/header.php';
include 'includes/db_connect.php';

// Get current user's details if logged in
$user_info = null;
$registeredIds = [];
if (isset($_SESSION['id'])) {
    $uid = (int)$_SESSION['id'];
    
    // Fetch user details
    $userQuery = "SELECT first_name, last_name, full_name, email, phone FROM users WHERE id = $uid";
    if ($userRes = $conn->query($userQuery)) {
        $user_info = $userRes->fetch_assoc();
        
        // If full_name is empty but we have first/last name, combine them
        if (empty($user_info['full_name']) && (!empty($user_info['first_name']) || !empty($user_info['last_name']))) {
            $user_info['full_name'] = trim(($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? ''));
        }
    }

    // Fetch registered tournament IDs
    $regQuery = "SELECT tournament_id FROM tournament_registrations WHERE user_id = $uid";
    if ($regResult = $conn->query($regQuery)) {
        while ($regRow = $regResult->fetch_assoc()) {
            $registeredIds[] = $regRow['tournament_id'];
        }
    }
}

// Fetch all tournaments ordered by date
$tournaments = [];
$sql = "SELECT * FROM tournaments ORDER BY event_date DESC";
if($result = $conn->query($sql)){
    while($row = $result->fetch_assoc()){
        $tournaments[] = $row;
    }
}

// Separate featured (latest upcoming) and others
$featured = null;
$others = [];

foreach($tournaments as $t) {
    if($t['status'] === 'upcoming' && !$featured) {
        $featured = $t;
    } else {
        $others[] = $t;
    }
}
?>

<style>
    .card-gradient-pattern {
        position: relative;
        background-color: white;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dark .card-gradient-pattern {
        background-color: #1a1a1a;
    }

    .card-gradient-pattern:hover {
        transform: translateY(-5px);
        border-color: rgba(128, 210, 0, 0.4);
    }

    .card-gradient-pattern::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(at 0% 0%, rgba(128, 210, 0, 0.15) 0, transparent 50%),
            radial-gradient(at 100% 100%, rgba(255, 165, 0, 0.1) 0, transparent 50%),
            url("data:image/svg+xml,%3Csvg width='24' height='24' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2380D200' fill-opacity='0.1' fill-rule='evenodd'%3E%3Cpath d='M11 18c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zM1 1h2v2H1V1zm4 4h2v2H4V5zm4 4h2v2H8V9zm4 4h2v2h-2v-2zm4 4h2v2h-2v-2zm4 4h2v2h-2v-2zM11 1h2v2h-2V1zm4 4h2v2h-2V5zm4 4h2v2h-2V9zm-8 8h2v2h-2v-2zm-4-4h2v2h-2v-2zm-4-4h2v2H1V9zm12-8h2v2h-2V1zm4 4h2v2h-2V5zm-4 4h2v2h-2V9zm-4 4h2v2h-2v-2zm-4 4h2v2H1v-2zm16-16h2v2h-2V1zm-4 4h2v2h-2V5zm-4 4h2v2h-2V9zm-4 4h2v2H1v-2z'/%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.15;
        pointer-events: none;
        z-index: 0;
        transition: opacity 0.4s ease;
    }

    .card-gradient-pattern:hover::before {
        opacity: 0.25;
    }

    .card-gradient-pattern>* {
        position: relative;
        z-index: 10;
    }

    .date-box {
        background: linear-gradient(135deg, rgba(128, 210, 0, 0.1), rgba(128, 210, 0, 0.05));
        border: 1px solid rgba(128, 210, 0, 0.2);
        backdrop-filter: blur(8px);
    }

    .dark .date-box {
        background: linear-gradient(135deg, rgba(128, 210, 0, 0.2), rgba(128, 210, 0, 0.05));
    }
</style>

    <section class="pt-32 pb-20 px-6">
        <div class="max-w-7xl mx-auto">
            <!-- Section 1: Featured BIG Tournament -->
            <?php if($featured): ?>
            <div class="mb-20">
                <h2 class="text-sm font-black uppercase tracking-[0.3em] text-brandGreen mb-6 flex items-center gap-4">
                    <span class="w-12 h-[2px] bg-brandGreen"></span>
                    Featured Major Event
                </h2>

                <div
                    class="relative group p-8 md:p-12 rounded-[50px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xl transition-all duration-500">
                    <!-- Dynamic Background -->
                    <div class="absolute inset-0 opacity-20 dark:opacity-40 transition-opacity duration-500">
                        <div class="absolute inset-0 bg-gradient-to-br from-brandGreen via-brandGreen/20 to-brandGreen/40">
                        </div>
                        <!-- Large Chess Piece Silhouette -->
                        <div class="absolute -right-20 -bottom-20 w-[400px] h-[400px] opacity-10 dark:opacity-20 pointer-events-none transform rotate-12">
                            <i class="fas fa-chess-knight text-[400px] text-brandGreen"></i>
                        </div>
                        <div
                            class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(128,210,0,0.2)_1px,transparent_1px)] bg-[size:30px_30px]">
                        </div>
                    </div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <div class="flex items-center gap-3 mb-6">
                                <span
                                    class="px-4 py-1.5 rounded-full bg-brandGreen text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brandGreen/30">Official Edition</span>
                                <span
                                    class="px-4 py-1.5 rounded-full bg-slate-100 dark:bg-white/10 backdrop-blur-md text-slate-600 dark:text-white/80 text-[10px] font-black uppercase tracking-widest border border-slate-200 dark:border-white/10">
                                    <?php echo date('M Y', strtotime($featured['event_date'])); ?>
                                </span>
                            </div>

                            <h1 class="text-5xl md:text-7xl font-black text-slate-900 dark:text-white mb-6 leading-tight">
                                <?php echo htmlspecialchars($featured['title']); ?>
                            </h1>

                            <p class="text-slate-600 dark:text-slate-400 text-lg mb-8 max-w-xl leading-relaxed">
                                <?php echo htmlspecialchars($featured['description']); ?>
                            </p>

                            <div class="flex flex-wrap gap-10 mb-10">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-brandGreen/10 dark:bg-white/5 flex items-center justify-center border border-brandGreen/20 dark:border-white/10">
                                        <i class="fas fa-trophy text-brandGreen text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Prize
                                            Pool</p>
                                        <p class="text-slate-900 dark:text-white font-bold">KES <?php echo number_format($featured['prize_pool']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-brandGreen/10 dark:bg-white/5 flex items-center justify-center border border-brandGreen/20 dark:border-white/10">
                                        <i class="fas fa-location-dot text-brandGreen text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Venue
                                        </p>
                                        <p class="text-slate-900 dark:text-white font-bold"><?php echo htmlspecialchars($featured['location']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <?php if (in_array($featured['id'], $registeredIds)): ?>
                                <button disabled class="bg-brandGreen text-white px-10 py-5 rounded-[25px] font-black uppercase tracking-widest text-xs shadow-xl flex items-center gap-3 opacity-80 cursor-default">
                                    <i class="fas fa-check-circle"></i>
                                    Already Registered
                                </button>
                            <?php else: ?>
                                <button
                                    onclick='openRegistrationModal(<?php echo json_encode(["id" => $featured["id"], "title" => $featured["title"]]); ?>)'
                                    class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-10 py-5 rounded-[25px] font-black uppercase tracking-widest text-xs hover:scale-105 active:scale-95 transition-all shadow-xl flex items-center gap-3 group/btn">
                                    Secure Your Slot
                                    <i class="fas fa-arrow-right transition-transform group-hover/btn:translate-x-2"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-10 px-6">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-4xl md:text-5xl font-black mb-12">All Events</h2>
            <div class="grid grid-cols-1 gap-12">
                <?php if(empty($others) && !$featured): ?>
                    <p class="text-center text-slate-500 py-20 font-bold uppercase tracking-widest">No tournaments scheduled yet.</p>
                <?php else: ?>
                    <?php 
                    $displayTournaments = $others;
                    if(!$featured && !empty($tournaments)) $displayTournaments = $tournaments;
                    
                    foreach($displayTournaments as $t): 
                        $eventDate = strtotime($t['event_date']);
                        $statusColor = $t['status'] === 'upcoming' ? 'bg-brandGreen' : ($t['status'] === 'ongoing' ? 'bg-amber-500' : 'bg-slate-500');
                    ?>
                        <div class="p-8 md:p-10 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 grid grid-cols-1 md:grid-cols-[auto_1fr_auto] items-center gap-6 md:gap-12 shadow-sm transition-all group relative overflow-hidden card-gradient-pattern">
                            <div class="relative z-10 w-24 h-24 md:w-28 md:h-28 rounded-3xl flex flex-col items-center justify-center text-brandGreen date-box shadow-inner">
                                <span class="text-3xl font-black leading-none"><?php echo date('d', $eventDate); ?></span>
                                <span class="text-[12px] font-black uppercase tracking-widest mt-1"><?php echo date('M', $eventDate); ?></span>
                            </div>
                            <div class="relative z-10">
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <span class="text-[10px] font-bold uppercase px-4 py-1.5 rounded-full <?php echo $statusColor; ?> text-white shadow-lg shadow-brandGreen/30"><?php echo ucfirst($t['status']); ?></span>
                                    <span class="text-[10px] font-bold uppercase px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700"><?php echo date('Y', $eventDate); ?></span>
                                </div>
                                <h3 class="text-2xl md:text-3xl font-bold mb-2 text-slate-900 dark:text-white group-hover:text-brandGreen transition-colors tracking-tight">
                                    <?php echo htmlspecialchars($t['title']); ?>
                                </h3>
                                <div class="flex flex-wrap gap-6">
                                    <p class="text-slate-500 text-sm font-semibold flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2 text-brandGreen"></i> 
                                        <?php echo htmlspecialchars($t['location']); ?>
                                    </p>
                                    <p class="text-slate-500 text-sm font-semibold flex items-center">
                                        <i class="fas fa-clock mr-2 text-brandGreen"></i> 
                                        <?php echo date('h:i A', $eventDate); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="relative z-10 text-right md:min-w-[160px]">
                                <?php if($t['status'] === 'upcoming'): ?>
                                    <?php if (in_array($t['id'], $registeredIds)): ?>
                                        <button disabled class="w-full md:w-auto bg-brandGreen text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-[11px] opacity-80 cursor-default flex items-center justify-center gap-2">
                                            <i class="fas fa-check-circle"></i>
                                            Registered
                                        </button>
                                    <?php else: ?>
                                        <button 
                                            onclick='openRegistrationModal(<?php echo json_encode(["id" => $t["id"], "title" => $t["title"]]); ?>)'
                                            class="w-full md:w-auto bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-[11px] shadow-xl hover:shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                                            Register
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button disabled class="w-full md:w-auto bg-slate-100 dark:bg-slate-800 text-slate-400 px-10 py-4 rounded-2xl font-black uppercase tracking-widest text-[11px] cursor-not-allowed">
                                        <?php echo $t['status'] === 'completed' ? 'Finished' : 'In Progress'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Registration Modal -->
    <div id="registrationModal" class="hidden fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-6">
        <div class="bg-white dark:bg-slate-900 w-full max-w-xl rounded-[40px] shadow-2xl relative overflow-hidden border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in duration-300">
            <!-- Modal Header -->
            <div class="px-8 pt-10 pb-4 text-center">
                <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-2 tracking-tight">Register for Tournament</h2>
                <p id="tournamentName" class="text-brandGreen font-bold uppercase tracking-widest text-xs"></p>
                <button onclick="closeRegistrationModal()" class="absolute top-6 right-8 text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Registration Form -->
            <form id="registrationForm" onsubmit="submitRegistration(event)" class="p-8 space-y-6">
                <input type="hidden" name="tournament_id" id="regTournamentId">
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Full Name</label>
                            <input type="text" name="full_name" id="regFullName" required placeholder="John Doe" 
                                value="<?php echo htmlspecialchars($user_info['full_name'] ?? ''); ?>"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Email Address</label>
                            <input type="email" name="email" id="regEmail" required placeholder="john@example.com" 
                                value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Phone Number</label>
                            <input type="tel" name="phone" id="regPhone" required placeholder="0712345678" 
                                value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Category</label>
                            <select name="category" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold appearance-none">
                                <option value="Open">Open</option>
                                <option value="Junior">Junior (U18)</option>
                                <option value="Blitz">Blitz only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="regMessage" class="hidden text-center py-2 px-4 rounded-xl font-bold text-sm"></div>

                <button type="submit" id="regSubmitBtn" class="w-full bg-brandGreen text-white py-5 rounded-[25px] font-black uppercase tracking-widest text-xs hover:scale-[1.02] active:scale-95 transition-all shadow-xl flex items-center justify-center gap-3">
                    Confirm Registration
                    <i class="fas fa-check"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        function openRegistrationModal(tournament) {
            document.getElementById('regTournamentId').value = tournament.id;
            document.getElementById('tournamentName').innerText = tournament.title;
            
            // Re-apply PHP defaults if form was previously reset
            if (<?php echo json_encode($user_info); ?>) {
                const user = <?php echo json_encode($user_info); ?>;
                if(document.getElementById('regFullName').value === '') document.getElementById('regFullName').value = user.full_name || '';
                if(document.getElementById('regEmail').value === '') document.getElementById('regEmail').value = user.email || '';
                if(document.getElementById('regPhone').value === '') document.getElementById('regPhone').value = user.phone || '';
            }
            
            document.getElementById('registrationModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRegistrationModal() {
            document.getElementById('registrationModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Only reset if error occurred or after success, but keep user defaults if they exist
            document.getElementById('regMessage').classList.add('hidden');
        }

        async function submitRegistration(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('regSubmitBtn');
            const message = document.getElementById('regMessage');
            
            const formData = new FormData(form);
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
            
            try {
                const response = await fetch('register_tournament.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                message.innerText = data.message;
                message.classList.remove('hidden', 'bg-red-100', 'text-red-600', 'bg-green-100', 'text-green-600');
                
                if (data.status === 'success') {
                    message.classList.add('bg-green-100', 'text-green-600');
                    form.reset();
                    setTimeout(() => closeRegistrationModal(), 3000);
                } else {
                    message.classList.add('bg-red-100', 'text-red-600');
                }
            } catch (error) {
                message.innerText = 'Network error. Please try again.';
                message.classList.remove('hidden');
                message.classList.add('bg-red-100', 'text-red-600');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Confirm Registration <i class="fas fa-check"></i>';
            }
        }
    </script>

<?php include 'includes/footer.php'; ?>

