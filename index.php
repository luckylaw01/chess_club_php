<?php
session_start();
$pageTitle = "Home";
include 'includes/header.php';
require_once 'includes/home_images.php';
?>
    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 overflow-hidden min-h-[90vh] flex items-center">
        <!-- Background Image with Blending Overlays -->
        <div class="absolute inset-0 -z-20">
            <img src="<?php echo htmlspecialchars(get_home_image('hero_background')); ?>" class="w-full h-full object-cover object-center<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="hero_background"
                alt="Chess Background">
            <!-- Main Dark Overlay -->
            <div class="absolute inset-0 bg-slate-50/90 dark:bg-darkBg/95 transition-colors duration-500"></div>
            <!-- Gradient Glows -->
            <div
                class="absolute top-0 right-0 w-1/2 h-full opacity-20 dark:opacity-40 blur-[120px] bg-gradient-to-br from-brandGreen to-brandDarkGreen rounded-full">
            </div>
            <div
                class="absolute bottom-0 left-0 w-1/3 h-1/2 opacity-10 dark:opacity-30 blur-[100px] bg-brandOrange rounded-full">
            </div>
            <!-- Texture/Pattern Overlay -->
            <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05] pointer-events-none"
                style="background-image: url('data:image/svg+xml,%3Csvg width=\" 60\" height=\"60\" viewBox=\"0 0 60
                60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg
                fill=\"%239C92AC\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6
                34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4v-4H4v4H0v2h4v4h2v-4h4v-2H6zM36
                4v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center relative z-10">
            <div class="space-y-8 animate-slide-up">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen dark:text-brandGreen text-xs font-bold uppercase tracking-widest">
                    <i class="fas fa-chess-pawn text-[10px]"></i> Home to the chess champions of Kenya
                </div>
                <h1
                    class="text-5xl lg:text-7xl font-extrabold leading-[1.1] tracking-tight text-slate-900 dark:text-white">
                    Master Strategy <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGreen to-brandOrange">
                        at Ascending Pawn
                    </span>
                </h1>
                <p class="text-lg text-slate-600 dark:text-slate-400 max-w-xl leading-relaxed">
                    Welcome to Ascending Pawn Chess. From our elite Academy to high-stakes Tournaments, we
                    provide the ultimate ecosystem for chess enthusiasts of all levels.
                </p>

                <!-- Status Bar -->
                <div
                    class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl flex flex-wrap items-center justify-between gap-4 shadow-xl glass transition-all hover:border-brandGreen/30">
                    <div class="flex items-center gap-4">
                        <div class="bg-brandGreen/10 dark:bg-brandGreen/20 p-3 rounded-2xl">
                            <i class="fas fa-calendar-alt text-brandGreen text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm">Join the 2024 Membership</h4>
                            <p class="text-xs text-slate-500">Get exclusive access to chess nights and rating
                                tournaments.</p>
                        </div>
                    </div>
                    <a href="<?php echo isset($_SESSION['id']) ? 'club.php' : 'register.php'; ?>"
                        class="bg-brandGreen hover:bg-brandGreen/90 text-white px-6 py-2.5 rounded-2xl text-xs font-bold transition-all uppercase tracking-wider">
                        Join Now
                    </a>
                </div>

                <div class="flex flex-wrap gap-4 pt-4">
                    <a href="<?php echo isset($_SESSION['id']) ? 'club.php' : 'register.php'; ?>"
                        class="px-8 py-4 bg-slate-900 dark:bg-white text-white dark:text-black font-bold rounded-2xl hover:scale-105 active:scale-95 transition-all flex items-center gap-2 uppercase text-sm tracking-tight text-center">
                        Start Your Journey <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    <button
                        class="px-8 py-4 border border-slate-300 dark:border-slate-700 font-bold rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-95 transition-all flex items-center gap-2 uppercase text-sm tracking-tight">
                        Learn More
                    </button>
                </div>
            </div>

            <!-- Image/Stats Montage -->
            <div class="relative animate-fade-in delay-400">
                <div
                    class="flex items-center justify-center lg:justify-end gap-3 sm:gap-6 h-[450px] sm:h-[550px] lg:h-[650px]">
                    <!-- Main Vertical Card -->
                    <div
                        class="relative w-2/3 lg:w-3/5 h-[90%] sm:h-full rounded-[40px] sm:rounded-[60px] overflow-hidden shadow-[0_32px_64px_-16px_rgba(0,0,0,0.3)] border-8 border-white dark:border-slate-900 group">
                        <img src="<?php echo htmlspecialchars(get_home_image('hero_main')); ?>"
                            class="w-full h-full object-cover grayscale-[0.2] group-hover:grayscale-0 transition-all duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="hero_main"
                            alt="Chess Session">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-6 sm:p-10">
                            <span class="text-brandGreen text-xs font-bold uppercase tracking-[0.2em] mb-2">Ascending
                                Pawn
                                Academy</span>
                            <h3 class="text-white font-bold text-xl sm:text-3xl leading-tight">Interactive <br>Coaching
                                Sessions</h3>
                        </div>
                    </div>

                    <!-- Side Column -->
                    <div class="flex flex-col gap-4 sm:gap-8 w-1/3 lg:w-2/5 h-full py-4 sm:py-10">
                        <!-- Small Image Card -->
                        <div
                            class="h-2/5 rounded-[30px] sm:rounded-[45px] overflow-hidden shadow-2xl border-4 sm:border-8 border-white dark:border-slate-900">
                            <img src="https://images.unsplash.com/photo-1586165368502-1bad197a6461?auto=format&fit=crop&q=80&w=400"
                                class="w-full h-full object-cover" alt="Strategy Board">
                        </div>
                        <!-- Blue Stats Card -->
                        <div
                            class="h-2/5 rounded-[30px] sm:rounded-[45px] overflow-hidden shadow-[0_20px_40px_-10px_rgba(128,210,0,0.4)] border-4 sm:border-8 border-white dark:border-slate-900 bg-brandGreen flex flex-col items-center justify-center text-white p-4 text-center">
                            <div class="bg-white/20 p-2 sm:p-3 rounded-2xl mb-2 sm:mb-4">
                                <i class="fas fa-award text-2xl sm:text-4xl text-white"></i>
                            </div>
                            <p class="text-2xl sm:text-5xl font-black mb-1">1000+</p>
                            <p class="text-[9px] sm:text-[11px] font-bold uppercase tracking-[0.2em] opacity-90">Members
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 px-6 bg-white dark:bg-black">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20">
                <h2 class="text-3xl lg:text-5xl font-bold mb-4">Excellence in Every Move</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">Our holistic approach combines tradition with modern
                    technology to deliver the best chess experience.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div
                    class="group p-8 rounded-[32px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                    <div
                        class="w-14 h-14 bg-brandGreen/10 dark:bg-brandGreen/20 rounded-2xl flex items-center justify-center text-brandGreen mb-6 transition-transform group-hover:scale-110">
                        <i class="fas fa-bullseye text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Structured Learning</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">Curriculum-based coaching designed for
                        Beginners and Advanced players with progress tracking.</p>
                    <div class="text-brandGreen font-bold text-sm cursor-pointer">Explore Levels <i
                            class="fas fa-arrow-right ml-1"></i></div>
                </div>

                <div
                    class="group p-8 rounded-[32px] bg-brandGreen text-white shadow-xl shadow-brandGreen/20 hover:scale-[1.02] transition-all duration-500">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white mb-6">
                        <i class="fas fa-globe text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Hybrid Academy</h3>
                    <p class="text-white/80 text-sm leading-relaxed mb-6">Attend in-person sessions at our Nyeri hub
                        or join global interactive online classes.</p>
                    <div class="text-white font-bold text-sm cursor-pointer">See Schedule <i
                            class="fas fa-arrow-right ml-1"></i></div>
                </div>

                <div
                    class="group p-8 rounded-[32px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                    <div
                        class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center text-purple-600 mb-6 transition-transform group-hover:scale-110">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Certified Coaching</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">Learn from FIDE instructors and national
                        league players with a proven track record.</p>
                    <div class="text-purple-600 font-bold text-sm cursor-pointer">Meet Trainers <i
                            class="fas fa-arrow-right ml-1"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Community Hub (New Sections) -->
    <section class="py-24 px-6 bg-slate-50 dark:bg-darkBg transition-colors duration-500">
        <div class="max-w-7xl mx-auto">
            
            <!-- Member Profiles & Leaderboard Personality -->
            <div class="mb-24">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
                    <div>
                        <h2 class="text-3xl lg:text-5xl font-extrabold tracking-tight mb-4">Elite Members</h2>
                        <p class="text-slate-500 max-w-xl">Meet the grandmasters and rising stars of our community. Every pawn has the potential to become a queen.</p>
                    </div>
                    <a href="club.php" class="text-brandGreen font-bold uppercase tracking-widest text-xs hover:underline decoration-2 underline-offset-8 transition-all">View Leaderboard <i class="fas fa-external-link-alt ml-2"></i></a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php
                    // Fetch top 3 members by elo_rating
                    require_once 'includes/db_connect.php';
                    $topMembers = [];
                    if (isset($conn)) {
                        $sql = "SELECT id, username, full_name, elo_rating, profile_picture FROM users WHERE role IN ('user','coach') AND membership_status = 'active' AND elo_rating IS NOT NULL ORDER BY elo_rating DESC LIMIT 3";
                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $topMembers = $res->fetch_all(MYSQLI_ASSOC);
                            $stmt->close();
                        }
                    }

                    $accentClasses = ['brandGreen', 'brandGold', 'brandBrown'];
                    if (!empty($topMembers)):
                        $i = 0;
                        foreach ($topMembers as $m):
                            $initial = '';
                            $name = !empty($m['full_name']) ? $m['full_name'] : $m['username'];
                            if (!empty($m['full_name'])) {
                                $initial = mb_strtoupper(mb_substr($m['full_name'], 0, 1));
                            } else {
                                $initial = mb_strtoupper(mb_substr($m['username'], 0, 1));
                            }
                            $rating = !empty($m['elo_rating']) ? intval($m['elo_rating']) : 1200;
                            $profile = !empty($m['profile_picture']) ? $m['profile_picture'] : '';
                            $hasPic = $profile && file_exists(__DIR__ . '/' . $profile);
                            $accent = $accentClasses[$i % count($accentClasses)];
                    ?>
                    <div class="bg-white dark:bg-slate-900/50 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 hover:border-brandGreen/40 transition-all group glass">
                        <div class="relative w-20 h-20 mb-6 mx-auto">
                            <?php if ($hasPic): ?>
                                <img src="<?php echo htmlspecialchars($profile); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="w-full h-full object-cover rounded-2xl">
                            <?php else: ?>
                                <div class="w-full h-full rounded-2xl bg-<?php echo $accent; ?>/20 flex items-center justify-center text-<?php echo $accent; ?> text-3xl font-black"><?php echo htmlspecialchars($initial); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="text-center">
                            <h3 class="font-black text-lg uppercase tracking-tight"><?php echo htmlspecialchars($name); ?></h3>
                            <p class="text-[10px] font-bold text-<?php echo $accent; ?> uppercase tracking-widest mb-4">Rating: <?php echo $rating; ?></p>
                            <p class="text-xs text-slate-500 italic mb-6">Top player in our community.</p>
                            <div class="flex justify-center gap-2">
                                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[9px] font-bold text-slate-500 uppercase">Top Player</span>
                            </div>
                        </div>
                    </div>
                    <?php
                            $i++;
                        endforeach;
                    else:
                    ?>
                    <div class="col-span-3 text-center p-8 bg-white dark:bg-slate-900/50 rounded-[32px] border border-slate-200 dark:border-slate-800">
                        <p class="font-bold text-slate-600 dark:text-slate-300">No members found yet. Be the first to join!</p>
                    </div>
                    <?php endif; ?>

                    <!-- Add Personal Profile Link -->
                    <a href="register.php" class="bg-brandGreen/5 border-2 border-dashed border-brandGreen/30 p-6 rounded-[32px] flex flex-col items-center justify-center text-center group cursor-pointer hover:bg-brandGreen/10 transition-all">
                        <div class="w-12 h-12 rounded-full bg-brandGreen text-white flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3 class="font-bold text-sm uppercase tracking-widest text-brandGreen">Join the Ranks</h3>
                        <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-tighter">Create your profile</p>
                    </a>
                </div>
            </div>

            <!-- Events & Learning Resources -->
            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Upcoming Events -->
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 flex items-center justify-center text-brandGreen">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold uppercase tracking-tight">Events Calendar</h2>
                    </div>

                    <div class="space-y-4">
                        <!-- Event 1 -->
                        <div class="flex items-center gap-6 p-5 bg-white dark:bg-slate-900/40 rounded-3xl border border-slate-100 dark:border-slate-800 hover:shadow-lg transition-all">
                            <div class="flex flex-col items-center justify-center min-w-[60px] h-[60px] bg-brandGreen rounded-2xl text-white">
                                <span class="text-lg font-black leading-none">15</span>
                                <span class="text-[9px] font-black uppercase tracking-widest leading-none mt-1">Mar</span>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-bold text-sm uppercase tracking-tight">Spring Blitz Championship</h4>
                                <p class="text-xs text-slate-500 mt-0.5"><i class="far fa-clock mr-1"></i> 6:00 PM â€¢ Nyeri Hub</p>
                            </div>
                            <button class="px-4 py-2 border border-brandGreen/20 text-brandGreen text-[10px] font-bold rounded-xl hover:bg-brandGreen hover:text-white transition-all uppercase tracking-widest">Register</button>
                        </div>

                        <!-- Event 2 -->
                        <div class="flex items-center gap-6 p-5 bg-white dark:bg-slate-900/40 rounded-3xl border border-slate-100 dark:border-slate-800 hover:shadow-lg transition-all">
                            <div class="flex flex-col items-center justify-center min-w-[60px] h-[60px] bg-brandOrange rounded-2xl text-white">
                                <span class="text-lg font-black leading-none">20</span>
                                <span class="text-[9px] font-black uppercase tracking-widest leading-none mt-1">Mar</span>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-bold text-sm uppercase tracking-tight">GM Strategy Workshop</h4>
                                <p class="text-xs text-slate-500 mt-0.5"><i class="fas fa-video mr-1"></i> Interactive Online Class</p>
                            </div>
                            <button class="px-4 py-2 border border-brandOrange/20 text-brandOrange text-[10px] font-bold rounded-xl hover:bg-brandOrange hover:text-white transition-all uppercase tracking-widest">Join</button>
                        </div>

                        <!-- Event 3 -->
                        <div class="flex items-center gap-6 p-5 bg-white dark:bg-slate-900/40 rounded-3xl border border-slate-100 dark:border-slate-800 hover:shadow-lg transition-all">
                            <div class="flex flex-col items-center justify-center min-w-[60px] h-[60px] bg-slate-200 dark:bg-slate-800 rounded-2xl text-slate-500 dark:text-slate-400">
                                <span class="text-lg font-black leading-none">28</span>
                                <span class="text-[9px] font-black uppercase tracking-widest leading-none mt-1">Mar</span>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-bold text-sm uppercase tracking-tight">Casual Friday Meetup</h4>
                                <p class="text-xs text-slate-500 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i> Player Lounge</p>
                            </div>
                            <span class="px-4 py-2 text-slate-400 text-[10px] font-bold uppercase tracking-widest">Free Entry</span>
                        </div>
                    </div>
                </div>

                <!-- Learning Resources -->
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brandGold/10 flex items-center justify-center text-brandGold">
                            <i class="fas fa-graduation-cap text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold uppercase tracking-tight">Learning Lab</h2>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="p-6 bg-brandGreen/5 dark:bg-brandGreen/10 rounded-3xl border border-brandGreen/10 hover:border-brandGreen/30 transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-brandGreen text-white flex items-center justify-center mb-4">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <h4 class="font-bold text-sm mb-2 group-hover:text-brandGreen transition-colors">Daily Puzzles</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mb-4">Sharpen your tactics with our curated daily chess puzzles.</p>
                            <a href="#" class="text-[10px] font-bold uppercase tracking-widest text-brandGreen flex items-center gap-2">Solve Now <i class="fas fa-arrow-right text-[8px]"></i></a>
                        </div>

                        <div class="p-6 bg-brandGold/5 dark:bg-brandGold/10 rounded-3xl border border-brandGold/10 hover:border-brandGold/30 transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-brandGold text-white flex items-center justify-center mb-4">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <h4 class="font-bold text-sm mb-2 group-hover:text-brandGold transition-colors">Strategy Guides</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mb-4">Comprehensive guides from opening theory to endgame mastery.</p>
                            <a href="#" class="text-[10px] font-bold uppercase tracking-widest text-brandGold flex items-center gap-2">Browse Guides <i class="fas fa-arrow-right text-[8px]"></i></a>
                        </div>
                    </div>

                    <!-- News Widget -->
                    <div class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[32px] glass">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-[11px] uppercase tracking-widest opacity-60">Chess News</h4>
                            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        </div>
                        <h3 class="font-bold text-sm mb-3">Ascending Pawn Welcomes 1000th Member!</h3>
                        <p class="text-xs text-slate-500 mb-4">Our community continues to grow at an unprecedented pace. Thank you for making us Kenya's #1 chess destination.</p>
                        <hr class="border-slate-100 dark:border-slate-800 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 rounded-full bg-slate-200 border-2 border-white dark:border-slate-900"></div>
                                <div class="w-6 h-6 rounded-full bg-slate-300 border-2 border-white dark:border-slate-900"></div>
                                <div class="w-6 h-6 rounded-full bg-slate-400 border-2 border-white dark:border-slate-900"></div>
                            </div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">12m ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Community Outreach & Badges -->
            <div class="mt-24 pt-24 border-t border-slate-200 dark:border-slate-800">
                <div class="grid lg:grid-cols-3 gap-12">
                    <div class="lg:col-span-2">
                        <h2 class="text-3xl font-black uppercase tracking-tighter mb-6">Beyond the <span class="text-brandGreen">Board</span></h2>
                        <div class="aspect-video rounded-[40px] overflow-hidden relative group">
                            <img src="https://images.unsplash.com/photo-1529699211952-734e80c4d42b?auto=format&fit=crop&q=80&w=1200" class="w-full h-full object-cover grayscale transition-all duration-700 group-hover:grayscale-0 scale-105 group-hover:scale-100" alt="Outreach">
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent p-10 flex flex-col justify-end">
                                <span class="text-brandGreen font-black text-xs uppercase tracking-widest mb-2">Community Outreach</span>
                                <h3 class="text-white text-2xl font-bold uppercase tracking-tight mb-4">Chess in the Park: Nyeri Edition</h3>
                                <p class="text-white/70 text-sm max-w-xl mb-6">Every first Sunday of the month, we bring the game to the heart of the city, teaching kids and hosting casual blitz matches for everyone.</p>
                                <button class="self-start px-6 py-3 bg-white text-black text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-brandGreen hover:text-white transition-all">Support Initiative</button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div>
                            <h4 class="font-bold text-[11px] uppercase tracking-[0.2em] text-slate-400 mb-6">Member Achievements</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1">
                                    <div class="w-12 h-12 bg-brandOrange/10 text-brandOrange rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Tournament Ace</p>
                                </div>
                                <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1">
                                    <div class="w-12 h-12 bg-brandGreen/10 text-brandGreen rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Blitz Master</p>
                                </div>
                                <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1">
                                    <div class="w-12 h-12 bg-red-400/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-fire"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest">10 Win Streak</p>
                                </div>
                                <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1">
                                    <div class="w-12 h-12 bg-brandGold/10 text-brandGold rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Grand Guardian</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-slate-900 dark:bg-slate-100 rounded-[40px] text-white dark:text-black">
                            <h3 class="font-black text-xl leading-tight mb-4 uppercase">Discuss <br>Strategy</h3>
                            <p class="text-xs opacity-70 mb-6">Join our private Discord for real-time discussions, game analysis, and member socialization.</p>
                            <a href="#" class="inline-flex items-center gap-3 font-bold text-xs uppercase tracking-widest border-b-2 border-brandGreen pb-1 hover:gap-5 transition-all">Join Discord <i class="fab fa-discord"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="py-32 px-6 overflow-hidden bg-white dark:bg-darkBg">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16 animate-slide-up">
                <div class="space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandOrange/10 dark:bg-brandOrange/20 text-brandOrange dark:text-brandOrange text-xs font-bold uppercase tracking-widest">
                        <i class="fas fa-camera text-[10px]"></i> Moments in Action
                    </div>
                    <h2 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Our <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGreen to-brandOrange">Gallery</span>
                    </h2>
                </div>
                <p class="text-lg text-slate-600 dark:text-slate-400 max-w-md leading-relaxed">
                    A glimpse into our vibrant community, high-stakes matches, and the rising stars of the Royal Game.
                </p>
            </div>

            <!-- Bento-style Gallery Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 animate-fade-in delay-200">
                <!-- Large Feature -->
                <div class="col-span-2 row-span-2 group relative overflow-hidden rounded-[40px] shadow-2xl">
                    <img src="<?php echo htmlspecialchars(get_home_image('gallery1')); ?>" alt="Chess Tournament Scene"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="gallery1">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end p-8">
                        <span class="text-white font-bold uppercase tracking-widest text-sm">Major Championships</span>
                    </div>
                </div>

                <!-- Tall Image -->
                <div class="col-span-1 row-span-2 group relative overflow-hidden rounded-[40px] shadow-xl">
                    <img src="<?php echo htmlspecialchars(get_home_image('gallery2')); ?>" alt="Player Focus"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="gallery2">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end p-6">
                        <span class="text-white font-bold uppercase tracking-widest text-xs">Deep Concentration</span>
                    </div>
                </div>

                <!-- Small Images -->
                <div class="col-span-1 group relative overflow-hidden rounded-[32px] shadow-xl aspect-square">
                    <img src="<?php echo htmlspecialchars(get_home_image('gallery3')); ?>" alt="Academy Session"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="gallery3">
                    <div class="absolute inset-0 bg-brandGreen/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                        <i class="fas fa-eye text-white text-xl"></i>
                    </div>
                </div>

                <div class="col-span-1 group relative overflow-hidden rounded-[32px] shadow-xl aspect-square">
                    <img src="<?php echo htmlspecialchars(get_home_image('gallery4')); ?>" alt="Chess Pieces Close-up"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="gallery4">
                    <div class="absolute inset-0 bg-brandOrange/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                        <i class="fas fa-eye text-white text-xl"></i>
                    </div>
                </div>

                <!-- Second Row (Bottom) -->
                <div class="col-span-1 group relative overflow-hidden rounded-[32px] shadow-xl aspect-square">
                    <img src="<?php echo htmlspecialchars(get_home_image('gallery5')); ?>" alt="Winning Moments"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="gallery5">
                    <div class="absolute inset-0 bg-brandGold/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                        <i class="fas fa-eye text-white text-xl"></i>
                    </div>
                </div>

                <div class="col-span-1 group relative overflow-hidden rounded-[32px] shadow-xl aspect-square">
                    <img src="<?php echo htmlspecialchars(get_home_image('gallery6')); ?>" alt="Community Gathering"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="gallery6">
                    <div class="absolute inset-0 bg-brandGreen/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                        <i class="fas fa-eye text-white text-xl"></i>
                    </div>
                </div>

                <div class="col-span-2 group relative overflow-hidden rounded-[40px] shadow-xl h-48 md:h-auto">
                    <img src="<?php echo htmlspecialchars(get_home_image('gallery7')); ?>" alt="Future Grandmasters"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700<?php if(is_admin_user()) echo ' admin-editable'; ?>" data-image-key="gallery7">
                    <div class="absolute inset-0 bg-gradient-to-r from-brandGreen/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center px-8">
                        <span class="text-white font-black uppercase tracking-[0.2em] text-sm">Our Rising Stars</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admin image uploader (only for admins) -->
    <?php if (function_exists('is_admin_user') && is_admin_user()): ?>
    <script>
        (function() {
            const style = document.createElement('style');
            style.textContent = [
                '.admin-image-wrapper .admin-upload-btn {',
                'position:absolute;',
                'top:12px;',
                'right:12px;',
                'z-index:60;',
                'background:rgba(0,0,0,0.75);',
                'color:#fff;',
                'border:1px solid rgba(255,255,255,0.35);',
                'border-radius:9999px;',
                'padding:8px 12px;',
                'font-size:11px;',
                'font-weight:700;',
                'letter-spacing:0.04em;',
                'text-transform:uppercase;',
                'cursor:pointer;',
                'opacity:0;',
                'transform:translateY(-6px);',
                'transition:all 0.18s ease;',
                'pointer-events:none;',
                'backdrop-filter:blur(6px);',
                '-webkit-backdrop-filter:blur(6px);',
                '}',
                '.admin-image-wrapper:hover .admin-upload-btn {',
                'opacity:1;',
                'transform:translateY(0);',
                'pointer-events:auto;',
                '}',
                '.admin-image-wrapper {',
                'outline:2px dashed rgba(128,210,0,0);',
                'outline-offset:-2px;',
                'transition:outline-color 0.18s ease;',
                '}',
                '.admin-image-wrapper:hover {',
                'outline-color:rgba(128,210,0,0.75);',
                '}'
            ].join('');
            document.head.appendChild(style);

            const uploaderInput = document.createElement('input');
            uploaderInput.type = 'file';
            uploaderInput.accept = 'image/*';
            uploaderInput.style.display = 'none';
            document.body.appendChild(uploaderInput);

            let activeImg = null;
            let activeButton = null;

            document.querySelectorAll('img.admin-editable[data-image-key]').forEach(function(img) {
                const imageKey = img.getAttribute('data-image-key');
                let wrapper = img.parentElement;
                if (imageKey === 'hero_background') {
                    const heroSection = img.closest('section');
                    if (heroSection) wrapper = heroSection;
                }
                if (!wrapper) return;

                const wrapperStyle = window.getComputedStyle(wrapper);
                if (wrapperStyle.position === 'static') {
                    wrapper.style.position = 'relative';
                }

                wrapper.classList.add('admin-image-wrapper');

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'admin-upload-btn';
                button.textContent = 'Update photo';
                button.title = 'Upload a new image';

                button.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    activeImg = img;
                    activeButton = button;
                    uploaderInput.value = null;
                    uploaderInput.click();
                });

                wrapper.appendChild(button);
            });

            uploaderInput.addEventListener('change', async function() {
                if (!activeImg) return;
                const file = uploaderInput.files[0];
                if (!file) return;

                const key = activeImg.getAttribute('data-image-key');
                const fd = new FormData();
                fd.append('image', file);
                fd.append('key', key);

                try {
                    if (activeButton) {
                        activeButton.disabled = true;
                        activeButton.textContent = 'Uploading...';
                    }

                    const res = await fetch('upload_home_image.php', { method: 'POST', body: fd });
                    const json = await res.json();
                    if (json.success) {
                        // update image src (cache-bust)
                        activeImg.src = json.path + '?t=' + Date.now();
                        // small flash
                        activeImg.style.transition = 'filter 0.3s ease';
                        activeImg.style.filter = 'grayscale(100%)';
                        setTimeout(function() { activeImg.style.filter = ''; }, 300);
                        if (activeButton) {
                            activeButton.textContent = 'Updated';
                            setTimeout(function() {
                                if (activeButton) activeButton.textContent = 'Update photo';
                            }, 800);
                        }
                        activeImg = null;
                    } else {
                        alert('Upload failed: ' + (json.message || 'unknown'));
                    }
                } catch (err) {
                    alert('Upload error');
                } finally {
                    if (activeButton) {
                        activeButton.disabled = false;
                    }
                    activeButton = null;
                }
            });
        })();
    </script>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

