<?php
session_start();
require_once "includes/db_connect.php";
$pageTitle = "Meet Trainers - Certified Coaching";
include 'includes/header.php';

// Handle Private Session Booking Request
$booking_success = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_private'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("location: login.php");
        exit;
    }
    // Simulate successful booking
    $booking_success = true;
}

// Fetch coaches from database
$coaches = [];
$res = $conn->query("SELECT id, username, email, full_name, role, elo_rating, phone_number, profile_picture, bio FROM users WHERE role = 'coach' ORDER BY elo_rating DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $coaches[] = $row;
    }
}
?>

    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
        <div class="max-w-7xl mx-auto">
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen dark:text-brandGreen text-xs font-bold uppercase tracking-widest mb-6">
                <i class="fas fa-user-shield text-[10px]"></i> Elite Panel
            </div>
            
            <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-16 gap-8">
                <div>
                    <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-6">
                        Meet Our <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGreen to-brandOrange">Coaches</span>
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                        Learn from certified FIDE masters, national champions, and international experts with a proven track record of developing elite chess strategy.
                    </p>
                </div>
                
                <button onclick="openBookingModal()" class="px-8 py-4 bg-brandGreen hover:bg-brandGreen/90 text-white font-bold rounded-2xl text-xs uppercase tracking-widest shadow-lg shadow-brandGreen/20 transition-all hover:scale-105 active:scale-95">
                    Book Private Session
                </button>
            </div>

            <!-- Booking Success Alert -->
            <?php if ($booking_success): ?>
                <div class="p-6 mb-12 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-3xl flex items-center gap-4 animate-slide-up">
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-sm uppercase tracking-wider">Booking Request Submitted!</h4>
                        <p class="text-xs opacity-90 mt-0.5">Your coach has been notified. They will contact you shortly to confirm the slot and share payment/access details.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Coaches Listing -->
            <div class="grid md:grid-cols-2 gap-8 lg:gap-12">
                <?php 
                $accentColors = ['brandGreen', 'brandGold', 'brandOrange', 'purple-500'];
                $i = 0;
                foreach ($coaches as $coach): 
                    $color = $accentColors[$i % count($accentColors)];
                    $initial = '';
                    $nameClean = str_replace(['GM ', 'IM ', 'CM '], '', $coach['full_name']);
                    $initial = mb_strtoupper(mb_substr($nameClean, 0, 1));
                    
                    // Determine credentials
                    $title = 'Certified Coach';
                    if (strpos($coach['full_name'], 'GM') !== false) $title = 'FIDE Grandmaster (GM)';
                    elseif (strpos($coach['full_name'], 'IM') !== false) $title = 'International Master (IM)';
                    elseif (strpos($coach['full_name'], 'CM') !== false) $title = 'Candidate Master (CM)';
                    
                    // Fetch courses taught by this coach
                    $coach_id = $coach['id'];
                    $courses = [];
                    $courses_res = $conn->query("SELECT id, title, level FROM academy_courses WHERE coach_id = $coach_id");
                    if ($courses_res) {
                        while ($co_row = $courses_res->fetch_assoc()) {
                            $courses[] = $co_row;
                        }
                    }
                ?>
                <div class="p-8 md:p-10 bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[40px] shadow-xl relative overflow-hidden glass hover:border-brandGreen/40 transition-all flex flex-col justify-between">
                    <div>
                        <!-- Coach Header Info -->
                        <div class="flex items-center gap-6 mb-8">
                            <div class="relative w-20 h-20 shrink-0">
                                <?php if (!empty($coach['profile_picture']) && file_exists(__DIR__ . '/' . $coach['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($coach['profile_picture']); ?>" alt="<?php echo htmlspecialchars($coach['full_name']); ?>" class="w-full h-full object-cover rounded-[24px] border-2 border-brandGreen">
                                <?php else: ?>
                                    <div class="w-full h-full rounded-[24px] bg-<?php echo $color; ?>/10 text-<?php echo $color; ?> flex items-center justify-center text-3xl font-black border-2 border-dashed border-<?php echo $color; ?>/30">
                                        <?php echo htmlspecialchars($initial); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-slate-950 dark:bg-white text-white dark:text-black text-[9px] font-black rounded-lg flex items-center justify-center shadow">
                                    <?php echo $coach['elo_rating']; ?>
                                </span>
                            </div>
                            
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight"><?php echo htmlspecialchars($coach['full_name']); ?></h3>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1"><?php echo htmlspecialchars($title); ?></p>
                                <p class="text-[10px] text-brandGreen font-bold uppercase tracking-widest mt-1"><i class="fas fa-star mr-1"></i> Peak ELO: <?php echo $coach['elo_rating']; ?></p>
                            </div>
                        </div>

                        <!-- Bio -->
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8">
                            <?php echo htmlspecialchars($coach['bio'] ?: 'No bio available yet for this certified trainer.'); ?>
                        </p>
                        
                        <!-- Courses List -->
                        <?php if (!empty($courses)): ?>
                        <div class="mb-8">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Courses Taught:</h4>
                            <div class="space-y-2">
                                <?php foreach ($courses as $c): ?>
                                <a href="coaching_levels.php#<?php echo $c['level']; ?>" class="flex items-center justify-between p-3 rounded-2xl bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors border border-slate-200/50 dark:border-slate-800/50">
                                    <span class="text-xs font-bold uppercase tracking-tight text-slate-900 dark:text-white"><?php echo htmlspecialchars($c['title']); ?></span>
                                    <span class="text-[9px] px-2.5 py-1 rounded-full bg-brandGreen/15 text-brandGreen font-black uppercase tracking-widest"><?php echo ucfirst($c['level']); ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- CTA -->
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-bold uppercase">Dynamic Private Syllabus available</span>
                        <button onclick="openBookingModal('<?php echo htmlspecialchars($coach['full_name']); ?>')" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-brandGreen hover:text-white dark:hover:bg-brandGreen dark:hover:text-white text-slate-700 dark:text-slate-300 font-bold rounded-xl text-[10px] uppercase tracking-widest transition-all">
                            Request Coaching
                        </button>
                    </div>
                </div>
                <?php 
                    $i++;
                endforeach; 
                ?>
            </div>
        </div>
    </section>

    <!-- Booking Modal Overlay -->
    <div id="booking-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-950/80 backdrop-blur-md opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl relative overflow-hidden flex flex-col transform scale-95 transition-transform duration-300">
            
            <!-- Modal Header -->
            <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Book Private Training</h2>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-1">Request custom over-the-board/online lessons</p>
                </div>
                <button onclick="closeBookingModal()"
                    class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form method="POST">
                <div class="p-8 space-y-6">
                    <!-- Coach Select -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Select Coach</label>
                        <select name="coach_name" id="modal-coach-select" required
                            class="w-full px-5 py-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-sm font-bold focus:outline-none focus:border-brandGreen transition-colors text-slate-900 dark:text-white">
                            <?php foreach ($coaches as $coach): ?>
                                <option value="<?php echo htmlspecialchars($coach['full_name']); ?>"><?php echo htmlspecialchars($coach['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date & Time -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Date</label>
                            <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>"
                                class="w-full px-5 py-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-sm font-bold focus:outline-none focus:border-brandGreen transition-colors text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Preffered Time</label>
                            <input type="time" name="booking_time" required
                                class="w-full px-5 py-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-sm font-bold focus:outline-none focus:border-brandGreen transition-colors text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Format -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Format</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 cursor-pointer hover:border-brandGreen transition-colors">
                                <input type="radio" name="booking_format" value="online" checked class="accent-brandGreen">
                                <span class="text-xs font-bold uppercase tracking-wider">Online (Zoom)</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 cursor-pointer hover:border-brandGreen transition-colors">
                                <input type="radio" name="booking_format" value="in-person" class="accent-brandGreen">
                                <span class="text-xs font-bold uppercase tracking-wider">In-Person</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-8 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <button type="submit" name="book_private"
                            class="w-full py-5 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest text-sm shadow-xl shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                            Submit Request
                        </button>
                    <?php else: ?>
                        <a href="login.php"
                            class="block w-full py-5 bg-slate-900 dark:bg-white text-white dark:text-black rounded-2xl text-center font-black uppercase tracking-widest text-sm hover:scale-[1.02] active:scale-95 transition-all">
                            Login to Book Session
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Booking Modal Logic -->
    <script>
        const modal = document.getElementById('booking-modal');
        const modalContainer = modal.querySelector('div');
        const coachSelect = document.getElementById('modal-coach-select');

        function openBookingModal(coachName = '') {
            if (coachName) {
                coachSelect.value = coachName;
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.replace('opacity-0', 'opacity-100');
                modalContainer.classList.replace('scale-95', 'scale-100');
            }, 10);

            document.body.classList.add('overflow-hidden');
        }

        function closeBookingModal() {
            modal.classList.replace('opacity-100', 'opacity-0');
            modalContainer.classList.replace('scale-100', 'scale-95');

            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        }

        // Close backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeBookingModal();
        });
    </script>

<?php include 'includes/footer.php'; ?>
