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
    $userQuery = "SELECT first_name, last_name, full_name, email, phone_number, date_of_birth, gender, club_type, club_name FROM users WHERE id = $uid";
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

                <div id="tournament-card-<?php echo $featured['id']; ?>"
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
                                        <i class="fas fa-ticket-alt text-brandGreen text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Entry
                                            Fee</p>
                                        <p class="text-slate-900 dark:text-white font-bold">
                                            <?php if ((float)$featured['entry_fee'] <= 0): ?>
                                                Free Entry
                                            <?php else: ?>
                                                KES <?php echo number_format((float)$featured['entry_fee'], 2); ?>
                                            <?php endif; ?>
                                        </p>
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

                        <!-- Right Side Poster -->
                        <?php if (!empty($featured['poster_url'])): ?>
                        <div class="relative w-full h-[300px] lg:h-full min-h-[300px] rounded-[30px] overflow-hidden shadow-2xl cursor-pointer group/poster ring-4 ring-white/50 dark:ring-slate-800/50" onclick="viewPoster('<?php echo htmlspecialchars($featured['poster_url']); ?>')">
                            <img src="<?php echo htmlspecialchars($featured['poster_url']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover/poster:scale-105" alt="Tournament Poster">
                            <div class="absolute inset-0 bg-black/0 group-hover/poster:bg-black/10 transition-colors duration-300"></div>
                        </div>
                        <?php endif; ?>
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
                <?php if(empty($tournaments)): ?>
                    <p class="text-center text-slate-500 py-20 font-bold uppercase tracking-widest">No tournaments scheduled yet.</p>
                <?php else: ?>
                    <?php 
                    $displayTournaments = $others;
                    if(!$featured && !empty($tournaments)) $displayTournaments = $tournaments;
                    
                    if(empty($displayTournaments)): 
                    ?>
                        <p class="text-center text-slate-500 py-20 font-bold uppercase tracking-widest">No other tournaments scheduled at this moment.</p>
                    <?php else: ?>
                        <?php foreach($displayTournaments as $t): 
                            $eventDate = strtotime($t['event_date']);
                            $statusColor = $t['status'] === 'upcoming' ? 'bg-brandGreen' : ($t['status'] === 'ongoing' ? 'bg-amber-500' : 'bg-slate-500');
                        ?>
                        <div id="tournament-card-<?php echo $t['id']; ?>" class="p-6 md:p-10 rounded-[32px] md:rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 grid grid-cols-1 md:grid-cols-[auto_1fr_auto] items-center gap-6 md:gap-12 shadow-sm transition-all group relative overflow-hidden card-gradient-pattern">
                            <?php if (!empty($t['poster_url'])): ?>
                            <div class="relative z-10 w-full h-48 md:w-28 md:h-28 rounded-3xl overflow-hidden shadow-inner flex-shrink-0 cursor-pointer group/poster" onclick="viewPoster('<?php echo htmlspecialchars($t['poster_url']); ?>')">
                                <img src="<?php echo htmlspecialchars($t['poster_url']); ?>" class="w-full h-full object-cover transition-transform group-hover/poster:scale-110">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/poster:opacity-100 transition-opacity pointer-events-none">
                                    <i class="fas fa-search-plus text-white text-xl"></i>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="relative z-10 w-24 h-24 md:w-28 md:h-28 rounded-3xl flex flex-col items-center justify-center text-brandGreen date-box shadow-inner flex-shrink-0 mx-auto md:mx-0">
                                <span class="text-3xl font-black leading-none"><?php echo date('d', $eventDate); ?></span>
                                <span class="text-[12px] font-black uppercase tracking-widest mt-1"><?php echo date('M', $eventDate); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="relative z-10 text-center md:text-left flex flex-col items-center md:items-start">
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-4">
                                    <span class="text-[10px] font-bold uppercase px-4 py-1.5 rounded-full <?php echo $statusColor; ?> text-white shadow-lg shadow-brandGreen/30"><?php echo ucfirst($t['status']); ?></span>
                                    <span class="text-[10px] font-bold uppercase px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700"><?php echo date('Y', $eventDate); ?></span>
                                </div>
                                <h3 class="text-2xl md:text-3xl font-bold mb-3 text-slate-900 dark:text-white group-hover:text-brandGreen transition-colors tracking-tight">
                                    <?php echo htmlspecialchars($t['title']); ?>
                                </h3>
                                <div class="flex flex-wrap justify-center md:justify-start gap-4 md:gap-6">
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
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Registration Modal -->
    <div id="registrationModal" class="hidden fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-6">
        <div class="bg-white dark:bg-slate-900 w-full max-w-6xl rounded-[40px] shadow-2xl relative overflow-hidden border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in duration-300 max-h-[92vh] overflow-y-auto">
            <div class="px-8 pt-10 pb-4 text-center sticky top-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md z-10 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-2 tracking-tight">Register for Tournament</h2>
                <p id="tournamentName" class="text-brandGreen font-bold uppercase tracking-widest text-xs"></p>
                <button onclick="closeRegistrationModal()" class="absolute top-6 right-8 text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form id="registrationForm" enctype="multipart/form-data" onsubmit="submitRegistration(event)" class="p-8 space-y-8">
                <input type="hidden" name="tournament_id" id="regTournamentId">
                <input type="hidden" name="registration_type" id="registrationType" value="individual">

                <div class="flex flex-wrap gap-3 justify-center">
                    <button type="button" id="modeIndividualBtn" onclick="setRegistrationMode('individual')" class="px-5 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] bg-brandGreen text-white shadow-lg">Individual</button>
                    <button type="button" id="modeTeamBtn" onclick="setRegistrationMode('team')" class="px-5 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500">Team</button>
                </div>

                <div id="individualSection" class="space-y-6">
                    <!-- Guest CTA Card -->
                    <div id="individualGuestCTA" class="hidden p-8 text-center rounded-[30px] border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 space-y-6 max-w-2xl mx-auto my-8">
                        <div class="w-16 h-16 rounded-full bg-brandGreen/10 flex items-center justify-center mx-auto">
                            <i class="fas fa-user-plus text-brandGreen text-2xl"></i>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Individual Registration</h3>
                            <p class="text-slate-500 text-sm font-medium max-w-md mx-auto">
                                Registering as an individual requires a Chess Club account to securely manage your registration, track pairings, and view your ratings.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center pt-2">
                            <button type="button" onclick="openSignupModal()" class="px-8 py-4 bg-brandGreen text-white font-black uppercase tracking-widest text-[11px] rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-lg">
                                Create Account (30s)
                            </button>
                            <button type="button" onclick="openLoginModal()" class="px-8 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-black uppercase tracking-widest text-[11px] rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-lg">
                                Already a Member? Log In
                            </button>
                        </div>
                    </div>

                    <div id="individualFields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Full Name</label>
                            <input type="text" name="full_name" id="regFullName" required placeholder="John Doe"
                                value="<?php echo htmlspecialchars($user_info['full_name'] ?? ''); ?>"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="regDob" required
                                value="<?php echo htmlspecialchars($user_info['date_of_birth'] ?? ''); ?>"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Gender</label>
                            <select name="gender" id="regGender" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold appearance-none">
                                <option value="">Choose</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Category</label>
                            <select name="category" id="regCategory" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold appearance-none">
                                <option value="Under 7">Under 7</option>
                                <option value="Under 9">Under 9</option>
                                <option value="Under 11">Under 11</option>
                                <option value="Under 13">Under 13</option>
                                <option value="Under 15">Under 15</option>
                                <option value="Under 17">Under 17</option>
                                <option value="Open">Open</option>
                                <option value="Blitz">Blitz</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Guardian Phone</label>
                            <input type="tel" name="guardian_phone" id="regGuardianPhone" placeholder="0712345678"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                    </div>
                </div>

                <div id="teamSection" class="hidden space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Team Name</label>
                            <input type="text" name="team_name" placeholder="Team Titans" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Contact Name</label>
                            <input type="text" name="team_contact_name" placeholder="Organizer Name" value="<?php echo htmlspecialchars($user_info['full_name'] ?? ''); ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Declared Participants</label>
                            <input type="number" name="declared_participant_count" min="1" value="1" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h3 class="text-lg font-black uppercase tracking-tight text-slate-900 dark:text-white">Participants</h3>
                            <p class="text-xs text-slate-500">Add one row per team member. Under-18 players need guardian phone numbers.</p>
                        </div>
                        <button type="button" onclick="addParticipantRow()" class="px-5 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] bg-brandGreen text-white shadow-lg">Add Participant</button>
                    </div>

                    <div class="overflow-x-auto rounded-[28px] border border-slate-200 dark:border-slate-800">
                        <table class="w-full min-w-[800px] text-left border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Name</th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">DOB</th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Gender</th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Category</th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Guardian Phone</th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Action</th>
                                </tr>
                            </thead>
                            <tbody id="participantsBody" class="divide-y divide-slate-100 dark:divide-slate-800"></tbody>
                        </table>
                    </div>
                </div>

                <div id="regMessage" class="hidden text-center py-3 px-4 rounded-xl font-bold text-sm"></div>

                <button type="submit" id="regSubmitBtn" class="w-full bg-brandGreen text-white py-5 rounded-[25px] font-black uppercase tracking-widest text-xs hover:scale-[1.02] active:scale-95 transition-all shadow-xl flex items-center justify-center gap-3">
                    Confirm Registration
                    <i class="fas fa-check"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- AJAX Signup Modal -->
    <div id="authSignupModal" class="hidden fixed inset-0 z-[110] bg-slate-900/85 backdrop-blur-md flex items-center justify-center p-6">
        <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl relative border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in duration-300 max-h-[92vh] overflow-y-auto">
            <div class="px-8 pt-10 pb-4 text-center sticky top-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md z-10 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-1 uppercase tracking-tight">Create Account</h2>
                <p class="text-slate-500 text-xs font-semibold uppercase tracking-widest text-brandGreen">Join the Chess Club in seconds</p>
                <button type="button" onclick="closeSignupModal()" class="absolute top-6 right-8 text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="ajaxSignupForm" onsubmit="submitAjaxSignup(event)" class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">First Name</label>
                        <input type="text" name="first_name" required placeholder="Magnus" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Last Name</label>
                        <input type="text" name="last_name" required placeholder="Carlsen" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Email Address</label>
                    <input type="email" name="email" required placeholder="magnus@chess.com" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Phone Number</label>
                        <input type="tel" name="phone_number" required placeholder="0712345678" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Gender</label>
                        <select name="gender" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold appearance-none">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Password</label>
                        <input type="password" name="password" required minlength="6" placeholder="******" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                    </div>
                </div>

                <div id="ajaxSignupMessage" class="hidden text-center py-3 px-4 rounded-xl font-bold text-sm"></div>

                <button type="submit" id="ajaxSignupSubmitBtn" class="w-full bg-brandGreen text-white py-5 rounded-[25px] font-black uppercase tracking-widest text-xs hover:scale-[1.02] active:scale-95 transition-all shadow-xl flex items-center justify-center gap-3">
                    Sign Up & Continue
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- AJAX Login Modal -->
    <div id="authLoginModal" class="hidden fixed inset-0 z-[110] bg-slate-900/85 backdrop-blur-md flex items-center justify-center p-6">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[40px] shadow-2xl relative border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in duration-300">
            <div class="px-8 pt-10 pb-4 text-center border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-1 uppercase tracking-tight">Log In</h2>
                <p class="text-slate-500 text-xs font-semibold uppercase tracking-widest text-brandGreen">Access your account</p>
                <button type="button" onclick="closeLoginModal()" class="absolute top-6 right-8 text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="ajaxLoginForm" onsubmit="submitAjaxLogin(event)" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Email Address</label>
                    <input type="email" name="email" required placeholder="pawn@example.com" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Password</label>
                    <input type="password" name="password" required placeholder="******" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 py-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-brandGreen/50 transition-all font-semibold">
                </div>

                <div id="ajaxLoginMessage" class="hidden text-center py-3 px-4 rounded-xl font-bold text-sm"></div>

                <button type="submit" id="ajaxLoginSubmitBtn" class="w-full bg-brandGreen text-white py-5 rounded-[25px] font-black uppercase tracking-widest text-xs hover:scale-[1.02] active:scale-95 transition-all shadow-xl flex items-center justify-center gap-3">
                    Log In & Continue
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        let currentUser = <?php echo json_encode($user_info); ?>;
        let hasLoggedInUser = <?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>;

        function setRegistrationMode(mode) {
            document.getElementById('registrationType').value = mode;
            const individualSection = document.getElementById('individualSection');
            const teamSection = document.getElementById('teamSection');
            const individualBtn = document.getElementById('modeIndividualBtn');
            const teamBtn = document.getElementById('modeTeamBtn');
            const submitBtn = document.getElementById('regSubmitBtn');

            const guestCTA = document.getElementById('individualGuestCTA');
            const indFields = document.getElementById('individualFields');

            // Find all input and select elements in both sections
            const individualInputs = individualSection.querySelectorAll('input, select, textarea');
            const teamInputs = teamSection.querySelectorAll('input, select, textarea');

            if (mode === 'team') {
                individualSection.classList.add('hidden');
                teamSection.classList.remove('hidden');
                individualBtn.classList.remove('bg-brandGreen', 'text-white');
                individualBtn.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-500');
                teamBtn.classList.add('bg-brandGreen', 'text-white');
                teamBtn.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-500');
                submitBtn.classList.remove('hidden');
                ensureParticipantRow();

                // Disable hidden inputs to prevent HTML validation errors, and enable active ones
                individualInputs.forEach(input => input.disabled = true);
                teamInputs.forEach(input => input.disabled = false);
            } else {
                individualSection.classList.remove('hidden');
                teamSection.classList.add('hidden');
                teamBtn.classList.remove('bg-brandGreen', 'text-white');
                teamBtn.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-500');
                individualBtn.classList.add('bg-brandGreen', 'text-white');
                individualBtn.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-500');
                
                if (!hasLoggedInUser) {
                    guestCTA.classList.remove('hidden');
                    indFields.classList.add('hidden');
                    submitBtn.classList.add('hidden');
                } else {
                    guestCTA.classList.add('hidden');
                    indFields.classList.remove('hidden');
                    submitBtn.classList.remove('hidden');
                }

                // Enable active individual inputs, and disable team inputs
                if (!hasLoggedInUser) {
                    individualInputs.forEach(input => input.disabled = true);
                } else {
                    individualInputs.forEach(input => input.disabled = false);
                }
                teamInputs.forEach(input => input.disabled = true);
            }
        }

        function participantRowMarkup(index, data = {}) {
            return `
                <tr class="align-top">
                    <td class="px-3 py-3"><input type="text" name="participants[${index}][full_name]" value="${data.full_name ?? ''}" class="w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-none px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white" placeholder="Player name"></td>
                    <td class="px-3 py-3"><input type="date" name="participants[${index}][date_of_birth]" value="${data.date_of_birth ?? ''}" class="w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-none px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white"></td>
                    <td class="px-3 py-3">
                        <select name="participants[${index}][gender]" class="w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-none px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white appearance-none">
                            <option value="">Choose</option>
                            <option value="male" ${data.gender === 'male' ? 'selected' : ''}>Male</option>
                            <option value="female" ${data.gender === 'female' ? 'selected' : ''}>Female</option>
                            <option value="other" ${data.gender === 'other' ? 'selected' : ''}>Other</option>
                        </select>
                    </td>
                    <td class="px-3 py-3">
                        <select name="participants[${index}][category]" class="w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-none px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white appearance-none">
                            <option value="Under 7" ${data.category === 'Under 7' ? 'selected' : ''}>Under 7</option>
                            <option value="Under 9" ${data.category === 'Under 9' ? 'selected' : ''}>Under 9</option>
                            <option value="Under 11" ${data.category === 'Under 11' ? 'selected' : ''}>Under 11</option>
                            <option value="Under 13" ${data.category === 'Under 13' ? 'selected' : ''}>Under 13</option>
                            <option value="Under 15" ${data.category === 'Under 15' ? 'selected' : ''}>Under 15</option>
                            <option value="Under 17" ${data.category === 'Under 17' ? 'selected' : ''}>Under 17</option>
                            <option value="Open" ${data.category === 'Open' ? 'selected' : ''}>Open</option>
                            <option value="Blitz" ${data.category === 'Blitz' ? 'selected' : ''}>Blitz</option>
                        </select>
                    </td>
                    <td class="px-3 py-3"><input type="tel" name="participants[${index}][guardian_phone]" value="${data.guardian_phone ?? ''}" class="w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-none px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white" placeholder="Parent/guardian phone"></td>
                    <td class="px-3 py-3 text-center">
                        <button type="button" onclick="removeParticipantRow(this)" class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-900/20 text-red-600 font-black uppercase text-[10px] tracking-widest">Remove</button>
                    </td>
                </tr>`;
        }

        function ensureParticipantRow() {
            const body = document.getElementById('participantsBody');
            if (!body.children.length) {
                body.insertAdjacentHTML('beforeend', participantRowMarkup(0, currentUser ? {
                    full_name: currentUser.full_name || ''
                } : {}));
            }
        }

        function refreshParticipantIndexes() {
            const rows = document.querySelectorAll('#participantsBody tr');
            rows.forEach((row, index) => {
                row.querySelectorAll('input, select').forEach((field) => {
                    field.name = field.name.replace(/participants\[\d+\]/, `participants[${index}]`);
                });
            });
        }

        function addParticipantRow(values = {}) {
            const body = document.getElementById('participantsBody');
            body.insertAdjacentHTML('beforeend', participantRowMarkup(body.children.length, values));
        }

        function removeParticipantRow(button) {
            const body = document.getElementById('participantsBody');
            if (body.children.length === 1) {
                return;
            }
            button.closest('tr').remove();
            refreshParticipantIndexes();
        }

        function openRegistrationModal(tournament) {
            document.getElementById('regTournamentId').value = tournament.id;
            document.getElementById('tournamentName').innerText = tournament.title;
            document.getElementById('regMessage').classList.add('hidden');
            document.getElementById('regMessage').innerText = '';

            if (currentUser) {
                if (document.getElementById('regFullName').value === '') document.getElementById('regFullName').value = currentUser.full_name || '';
                if (document.getElementById('regDob').value === '') document.getElementById('regDob').value = currentUser.date_of_birth || '';
                if (document.getElementById('regGender').value === '') document.getElementById('regGender').value = currentUser.gender || '';
            }

            setRegistrationMode(hasLoggedInUser ? 'individual' : 'team');
            document.getElementById('registrationModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRegistrationModal() {
            document.getElementById('registrationModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
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
                message.innerText = data.message || 'Done.';
                message.classList.remove('hidden', 'bg-red-100', 'text-red-600', 'bg-green-100', 'text-green-600');

                if (data.status === 'success') {
                    message.classList.add('bg-green-100', 'text-green-600');
                    if (data.authorization_url) {
                        setTimeout(() => {
                            window.location.href = data.authorization_url;
                        }, 800);
                    } else {
                        setTimeout(() => location.reload(), 1200);
                    }
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

        const callbackParams = new URLSearchParams(window.location.search);
        if (callbackParams.get('status') === 'callback' && callbackParams.get('reference')) {
            const reference = callbackParams.get('reference');
            fetch('paystack_payment_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'verify', reference })
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message || 'Payment verification complete.');
                window.location.href = 'tournaments.php';
            })
            .catch(() => alert('Unable to verify the payment right now.'));
        }

        function openSignupModal() {
            document.getElementById('ajaxSignupMessage').classList.add('hidden');
            document.getElementById('ajaxSignupForm').reset();
            document.getElementById('authSignupModal').classList.remove('hidden');
        }

        function closeSignupModal() {
            document.getElementById('authSignupModal').classList.add('hidden');
        }

        function openLoginModal() {
            document.getElementById('ajaxLoginMessage').classList.add('hidden');
            document.getElementById('ajaxLoginForm').reset();
            document.getElementById('authLoginModal').classList.remove('hidden');
        }

        function closeLoginModal() {
            document.getElementById('authLoginModal').classList.add('hidden');
        }

        async function submitAjaxSignup(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('ajaxSignupSubmitBtn');
            const message = document.getElementById('ajaxSignupMessage');
            const formData = new FormData(form);
            formData.append('action', 'register');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';

            try {
                const response = await fetch('auth_ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                message.innerText = data.message || 'Done.';
                message.classList.remove('hidden', 'bg-red-100', 'text-red-600', 'bg-green-100', 'text-green-600');

                if (data.status === 'success') {
                    message.classList.add('bg-green-100', 'text-green-600');
                    setTimeout(() => {
                        closeSignupModal();
                        onAuthSuccess(data.user);
                    }, 800);
                } else {
                    message.classList.add('bg-red-100', 'text-red-600');
                }
            } catch (error) {
                message.innerText = 'Network error. Please try again.';
                message.classList.remove('hidden');
                message.classList.add('bg-red-100', 'text-red-600');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Sign Up & Continue <i class="fas fa-arrow-right"></i>';
            }
        }

        async function submitAjaxLogin(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('ajaxLoginSubmitBtn');
            const message = document.getElementById('ajaxLoginMessage');
            const formData = new FormData(form);
            formData.append('action', 'login');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';

            try {
                const response = await fetch('auth_ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                message.innerText = data.message || 'Done.';
                message.classList.remove('hidden', 'bg-red-100', 'text-red-600', 'bg-green-100', 'text-green-600');

                if (data.status === 'success') {
                    message.classList.add('bg-green-100', 'text-green-600');
                    setTimeout(() => {
                        closeLoginModal();
                        onAuthSuccess(data.user);
                    }, 800);
                } else {
                    message.classList.add('bg-red-100', 'text-red-600');
                }
            } catch (error) {
                message.innerText = 'Network error. Please try again.';
                message.classList.remove('hidden');
                message.classList.add('bg-red-100', 'text-red-600');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Log In & Continue <i class="fas fa-arrow-right"></i>';
            }
        }

        function onAuthSuccess(user) {
            // Update global variables
            currentUser = user;
            hasLoggedInUser = true;

            // Pre-fill all fields in the Individual registration section
            document.getElementById('regFullName').value = user.full_name || '';
            document.getElementById('regDob').value = user.date_of_birth || '';
            document.getElementById('regGender').value = user.gender || '';

            // Transition registration mode to individual
            setRegistrationMode('individual');
        }

        setRegistrationMode(hasLoggedInUser ? 'individual' : 'team');
        ensureParticipantRow();

        function viewPoster(url) {
            document.getElementById('posterModalImg').src = url;
            document.getElementById('posterModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePoster() {
            document.getElementById('posterModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Auto-focus and open registration modal if 'id' parameter is passed in URL
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const targetId = urlParams.get('id');
            if (targetId) {
                const card = document.getElementById(`tournament-card-${targetId}`);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.classList.add('ring-4', 'ring-brandGreen/50');
                    setTimeout(() => {
                        card.classList.remove('ring-4', 'ring-brandGreen/50');
                    }, 3000);
                    
                    const regBtn = card.querySelector('button[onclick*="openRegistrationModal"]');
                    if (regBtn) {
                        setTimeout(() => {
                            regBtn.click();
                        }, 800);
                    }
                }
            }
        });
    </script>

    <!-- Poster Lightbox Modal -->
    <div id="posterModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-sm transition-opacity" onclick="closePoster()">
        <button onclick="closePoster()" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors z-10">
            <i class="fas fa-times text-3xl"></i>
        </button>
        <img id="posterModalImg" src="" class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl object-contain" onclick="event.stopPropagation()">
    </div>

<?php include 'includes/footer.php'; ?>

