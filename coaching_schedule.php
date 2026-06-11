<?php
session_start();
require_once "includes/db_connect.php";
$pageTitle = "Training Schedule - Hybrid Academy";
include 'includes/header.php';

// Handle booking request (simple visual mockup action)
$booking_success = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_class'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("location: login.php");
        exit;
    }
    // Record visual success message
    $booking_success = true;
}

// Define the schedules
$classes = [
    [
        'id' => 1,
        'title' => 'Monday Masterclass',
        'day' => 'Monday',
        'time' => '18:00 - 19:30',
        'time_label' => '6:00 PM - 7:30 PM EAT',
        'format' => 'online', // online, in-person
        'level' => 'master', // beginner, intermediate, advanced, master
        'level_label' => 'Master / Elite',
        'coach' => 'GM Magnus Carlsen',
        'venue' => 'Zoom & Lichess Classroom',
        'description' => 'Deep dive into endgame strategy, complex calculations, and analysis of high-level Grandmaster games.',
        'color' => 'purple-500'
    ],
    [
        'id' => 2,
        'title' => 'Wednesday Tactics Lab',
        'day' => 'Wednesday',
        'time' => '17:30 - 19:00',
        'time_label' => '5:30 PM - 7:00 PM EAT',
        'format' => 'online',
        'level' => 'intermediate',
        'level_label' => 'Intermediate',
        'coach' => 'IM Alexander Cherniaev',
        'venue' => 'Zoom Classroom',
        'description' => 'Interactive puzzle sessions focusing on tactical vision, pattern recognition, and speed calculations.',
        'color' => 'brandGold'
    ],
    [
        'id' => 3,
        'title' => 'Thursday Positional Drill',
        'day' => 'Thursday',
        'time' => '18:00 - 19:30',
        'time_label' => '6:00 PM - 7:30 PM EAT',
        'format' => 'online',
        'level' => 'advanced',
        'level_label' => 'Advanced',
        'coach' => 'GM Judit Polgár',
        'venue' => 'Zoom Classroom',
        'description' => 'Understand the strategic concepts of space, weak squares, pawn structures, and prophylactic thinking.',
        'color' => 'brandOrange'
    ],
    [
        'id' => 4,
        'title' => 'Friday Night Blitz & Review',
        'day' => 'Friday',
        'time' => '19:00 - 21:30',
        'time_label' => '7:00 PM - 9:30 PM EAT',
        'format' => 'in-person',
        'level' => 'intermediate',
        'level_label' => 'All Levels Welcome',
        'coach' => 'CM Ben Nguku',
        'venue' => 'Nyeri Hub (Main Hall)',
        'description' => 'Fast-paced friendly blitz matches followed by interactive over-the-board post-game analyses.',
        'color' => 'brandGreen'
    ],
    [
        'id' => 5,
        'title' => 'Saturday Junior Academy',
        'day' => 'Saturday',
        'time' => '10:00 - 12:00',
        'time_label' => '10:00 AM - 12:00 PM EAT',
        'format' => 'in-person',
        'level' => 'beginner',
        'level_label' => 'Beginner / Kids',
        'coach' => 'CM Ben Nguku',
        'venue' => 'Nyeri Hub (Kids Corner)',
        'description' => 'Friendly introductory lessons, chess puzzles, and basic mating pattern exercises for young players.',
        'color' => 'brandGreen'
    ],
    [
        'id' => 6,
        'title' => 'Sunday Open Play & Analysis',
        'day' => 'Sunday',
        'time' => '14:00 - 17:00',
        'time_label' => '2:00 PM - 5:00 PM EAT',
        'format' => 'in-person',
        'level' => 'advanced',
        'level_label' => 'Intermediate & Advanced',
        'coach' => 'GM Judit Polgár',
        'venue' => 'Nyeri Hub (Terrace)',
        'description' => 'Casual match plays, training games, and dynamic feedback from our master coaches.',
        'color' => 'brandOrange'
    ]
];
?>

    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
        <div class="max-w-7xl mx-auto">
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen dark:text-brandGreen text-xs font-bold uppercase tracking-widest mb-6">
                <i class="fas fa-calendar-alt text-[10px]"></i> Timetable
            </div>
            
            <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-16 gap-8">
                <div>
                    <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-6">
                        Academy <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGreen to-brandOrange">Schedules</span>
                    </h1>
                    <p class="text-lg text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                        Join our in-person coaching sessions at our Nyeri Hub or attend interactive global online sessions via Zoom.
                    </p>
                </div>
                
                <!-- Quick Location Card -->
                <div class="p-6 bg-white dark:bg-slate-900/50 rounded-3xl border border-slate-200 dark:border-slate-800 glass flex items-center gap-4 shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 flex items-center justify-center text-brandGreen text-xl">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-widest text-slate-400">Our Hub Location</h4>
                        <p class="text-sm font-black text-slate-900 dark:text-white">Nyeri Hub Office Suite, 2nd Floor</p>
                    </div>
                </div>
            </div>

            <!-- Booking Success Notification -->
            <?php if ($booking_success): ?>
                <div class="p-6 mb-10 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-3xl flex items-center gap-4 animate-slide-up">
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-sm uppercase tracking-wider">Class Booked Successfully!</h4>
                        <p class="text-xs opacity-90 mt-0.5">We have registered your slot. An email confirmation with credentials has been sent to your email.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Filters Section -->
            <div class="flex flex-wrap items-center justify-between gap-6 mb-12 bg-white dark:bg-slate-900/30 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 glass">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="text-xs font-black uppercase tracking-widest text-slate-400">Filters:</span>
                    <!-- Format Filter -->
                    <div class="flex bg-slate-100 dark:bg-slate-800 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700/50">
                        <button onclick="filterFormat('all')" id="btn-format-all" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all bg-brandGreen text-white shadow">All</button>
                        <button onclick="filterFormat('online')" id="btn-format-online" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-slate-500 dark:text-slate-400">Online</button>
                        <button onclick="filterFormat('in-person')" id="btn-format-in-person" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-slate-500 dark:text-slate-400">In-Person</button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button onclick="filterLevel('all')" id="btn-level-all" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl uppercase tracking-widest hover:border-brandGreen transition-all">All Levels</button>
                    <button onclick="filterLevel('beginner')" id="btn-level-beginner" class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-500 text-xs font-bold rounded-xl uppercase tracking-widest hover:border-brandGreen transition-all">Beginner</button>
                    <button onclick="filterLevel('intermediate')" id="btn-level-intermediate" class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-500 text-xs font-bold rounded-xl uppercase tracking-widest hover:border-brandGreen transition-all">Intermediate</button>
                    <button onclick="filterLevel('advanced')" id="btn-level-advanced" class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-500 text-xs font-bold rounded-xl uppercase tracking-widest hover:border-brandGreen transition-all">Advanced</button>
                    <button onclick="filterLevel('master')" id="btn-level-master" class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-500 text-xs font-bold rounded-xl uppercase tracking-widest hover:border-brandGreen transition-all">Master</button>
                </div>
            </div>

            <!-- Schedule Display Grid -->
            <div id="schedule-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($classes as $cl): ?>
                <div class="class-card group p-8 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-brandGreen/40 hover:shadow-2xl transition-all duration-500 flex flex-col justify-between glass"
                     data-format="<?php echo $cl['format']; ?>" 
                     data-level="<?php echo $cl['level']; ?>">
                    
                    <div>
                        <!-- Header badge/times -->
                        <div class="flex items-center justify-between mb-6">
                            <span class="px-4 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50 flex items-center gap-1.5">
                                <?php if ($cl['format'] == 'online'): ?>
                                    <i class="fas fa-video text-brandGreen"></i> Online Session
                                <?php else: ?>
                                    <i class="fas fa-map-marker-alt text-brandOrange"></i> In-Person
                                <?php endif; ?>
                            </span>
                            <span class="text-xs font-black uppercase text-<?php echo $cl['color']; ?> tracking-widest">
                                <?php echo htmlspecialchars($cl['level_label']); ?>
                            </span>
                        </div>

                        <!-- Class Info -->
                        <h3 class="text-2xl font-black uppercase tracking-tight text-slate-900 dark:text-white mb-2"><?php echo htmlspecialchars($cl['title']); ?></h3>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-4">
                            <i class="far fa-clock mr-1 text-brandGreen"></i> <?php echo htmlspecialchars($cl['day']); ?>, <?php echo htmlspecialchars($cl['time_label']); ?>
                        </p>
                        
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-6 font-medium">
                            <?php echo htmlspecialchars($cl['description']); ?>
                        </p>
                    </div>

                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase">Assigned Coach</span>
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($cl['coach']); ?></span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] text-slate-400 font-bold uppercase">Venue/Format</span>
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($cl['format'] == 'online' ? 'Zoom Live' : 'Nyeri Hub'); ?></span>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <form method="POST">
                                <input type="hidden" name="class_id" value="<?php echo $cl['id']; ?>">
                                <button type="submit" name="book_class" class="w-full py-4 rounded-2xl bg-slate-950 dark:bg-white text-white dark:text-black font-bold uppercase tracking-widest text-[10px] hover:scale-[1.02] active:scale-95 transition-all">
                                    Book / Reserve Slot
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="block w-full py-4 text-center rounded-2xl border-2 border-slate-950 dark:border-white font-bold uppercase tracking-widest text-[10px] hover:bg-slate-950 hover:text-white dark:hover:bg-white dark:hover:text-black transition-all">
                                Login to Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty state -->
            <div id="no-classes-msg" class="hidden p-16 rounded-[40px] border-2 border-dashed border-slate-200 dark:border-slate-800 text-center max-w-lg mx-auto mt-12">
                <i class="fas fa-search text-3xl text-slate-400 mb-4"></i>
                <h4 class="text-lg font-black uppercase tracking-tight text-slate-900 dark:text-white">No matches found</h4>
                <p class="text-xs text-slate-500 mt-2">Try adjusting your filters to show different coaching format or experience levels.</p>
            </div>
        </div>
    </section>

    <!-- Schedule filtering script -->
    <script>
        let currentFormat = 'all';
        let currentLevel = 'all';

        function filterFormat(format) {
            currentFormat = format;
            
            // update UI buttons
            document.querySelectorAll('[id^="btn-format-"]').forEach(btn => {
                btn.classList.remove('bg-brandGreen', 'text-white', 'shadow');
                btn.classList.add('text-slate-500', 'dark:text-slate-400');
            });
            const activeBtn = document.getElementById('btn-format-' + format);
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-500', 'dark:text-slate-400');
                activeBtn.classList.add('bg-brandGreen', 'text-white', 'shadow');
            }

            applyFilters();
        }

        function filterLevel(level) {
            currentLevel = level;

            // update UI buttons
            document.querySelectorAll('[id^="btn-level-"]').forEach(btn => {
                btn.classList.remove('bg-slate-900', 'dark:bg-white', 'text-white', 'dark:text-black');
                btn.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-900', 'dark:text-white');
            });
            const activeBtn = document.getElementById('btn-level-' + level);
            if (activeBtn) {
                activeBtn.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-900', 'dark:text-white');
                activeBtn.classList.add('bg-slate-900', 'dark:bg-white', 'text-white', 'dark:text-black');
            }

            applyFilters();
        }

        function applyFilters() {
            let visibleCount = 0;
            const cards = document.querySelectorAll('.class-card');
            
            cards.forEach(card => {
                const format = card.getAttribute('data-format');
                const level = card.getAttribute('data-level');
                
                const formatMatch = (currentFormat === 'all' || format === currentFormat);
                const levelMatch = (currentLevel === 'all' || level === currentLevel);
                
                if (formatMatch && levelMatch) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noClassesMsg = document.getElementById('no-classes-msg');
            if (visibleCount === 0) {
                noClassesMsg.classList.remove('hidden');
            } else {
                noClassesMsg.classList.add('hidden');
            }
        }
    </script>

<?php include 'includes/footer.php'; ?>
