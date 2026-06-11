<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " | Ascending Pawn Chess Club" : "Ascending Pawn Chess Club | Master the Game"; ?></title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="shortcut icon" href="assets/images/logo.png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        // Check for saved theme or system preference immediately to avoid flash
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkBg: '#2B2B2B',
                        brandGreen: '#80D200',
                        brandNeonGreen: '#00FF00',
                        brandMint: '#B3FFB3',
                        brandDarkGreen: '#003300',
                        brandOrange: '#FFA500',
                        brandGold: '#FFCC66',
                        brandBrown: '#805300',
                        accentGreen: '#80D200',
                    },
                    animation: {
                        'fade-in': 'fadeIn 1s ease-out forwards',
                        'slide-up': 'slideUp 0.8s ease-out forwards',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .animate-fade-in { opacity: 0; animation: fadeIn 1s ease-out forwards; }
        .animate-slide-up { opacity: 0; animation: slideUp 0.8s ease-out forwards; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-400 { animation-delay: 0.4s; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #80D200; border-radius: 10px; }
        .glass { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }

        /* Transparent nav for hero pages */
        .nav-transparent .nav-brand-text,
        .nav-transparent .nav-links a,
        .nav-transparent .nav-auth-links a,
        .nav-transparent .nav-auth-links span { color: #fff !important; }
        .nav-transparent .nav-logo-wrap { background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); box-shadow: none; ring: none; border: 1px solid rgba(255,255,255,0.2); }
        .nav-transparent .nav-icon-btn { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff; }
        .nav-transparent .nav-divider { background: rgba(255,255,255,0.15); }
        .nav-transparent .nav-join-btn { background: #80D200; color: #fff; }
        /* When scrolled, remove transparent overrides */
        .nav-scrolled .nav-brand-text,
        .nav-scrolled .nav-links a,
        .nav-scrolled .nav-auth-links a,
        .nav-scrolled .nav-auth-links span { color: inherit !important; }
        .nav-scrolled .nav-logo-wrap { background: #fff; backdrop-filter: none; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .nav-scrolled .nav-icon-btn { background: inherit; border-color: inherit; color: inherit; }
    </style>
</head>

<body class="bg-slate-50 dark:bg-darkBg text-slate-900 dark:text-slate-100 transition-colors duration-500 font-sans">
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-500 py-6<?php if(basename($_SERVER['PHP_SELF']) === 'index.php') echo ' nav-transparent'; ?>">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 group shrink-0">
                <div class="nav-logo-wrap bg-white p-2 md:p-2.5 rounded-2xl shadow-lg ring-1 ring-slate-200/50 group-hover:scale-110 transition-all duration-300">
                    <img src="assets/images/logo1.png" alt="Ascending Pawn Logo" class="w-5 h-5 md:w-6 md:h-6 object-contain">
                </div>
                <span class="nav-brand-text text-[15px] sm:text-2xl font-black tracking-tighter uppercase text-slate-900 dark:text-white transition-colors duration-500 leading-[1.1]">Ascending Pawn <br class="sm:hidden"><span class="text-brandGreen">Chess</span></span>
            </a>
            <div class="hidden lg:flex items-center gap-6 xl:gap-10 text-slate-900 dark:text-slate-100">
                <?php
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $club_active = in_array($current_page, ['club.php', 'profile.php', 'notifications.php']);
                    $academy_active = in_array($current_page, ['academy.php', 'course_view.php', 'coaching_schedule.php', 'coaching_trainers.php', 'coaching_levels.php', 'strategy_guides.php', 'daily_puzzles.php']);
                    $tournaments_active = in_array($current_page, ['tournaments.php', 'register_tournament.php']);
                    $shop_active = in_array($current_page, ['shop.php', 'cart.php', 'checkout.php']);
                    $donate_active = ($current_page == 'donate.php');
                ?>
                <div class="nav-links flex items-center gap-5 xl:gap-8 text-[12px] font-bold uppercase tracking-[0.14em]">
                    <a href="club.php" class="<?php echo $club_active ? 'text-brandGreen' : 'hover:text-brandGreen text-slate-900 dark:text-slate-100'; ?> transition-all">Club</a>
                    <a href="academy.php" class="<?php echo $academy_active ? 'text-brandGreen' : 'hover:text-brandGreen text-slate-900 dark:text-slate-100'; ?> transition-all">Academy</a>
                    <a href="tournaments.php" class="<?php echo $tournaments_active ? 'text-brandGreen' : 'hover:text-brandGreen text-slate-900 dark:text-slate-100'; ?> transition-all">Tournaments</a>
                    <a href="shop.php" class="<?php echo $shop_active ? 'text-brandGreen' : 'hover:text-brandGreen text-slate-900 dark:text-slate-100'; ?> transition-all">Shop</a>
                    <a href="donate.php" class="<?php echo $donate_active ? 'text-brandGreen' : 'hover:text-brandGreen text-slate-900 dark:text-slate-100'; ?> transition-all">Donate</a>
                </div>
                <div class="nav-divider h-8 w-px bg-slate-200 dark:bg-slate-800 mx-2 transition-colors duration-500"></div>
                <div class="flex items-center gap-4 xl:gap-6">
                    <a href="cart.php" class="nav-icon-btn relative w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brandGreen transition-all duration-300 shadow-sm border border-slate-200 dark:border-slate-700">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-brandGreen text-white text-[10px] font-black flex items-center justify-center rounded-full shadow-lg border-2 border-white dark:border-darkBg <?php echo (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) ? 'hidden' : ''; ?>">
                            <?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : '0'; ?>
                        </span>
                    </a>
                    <button id="theme-toggle" class="nav-icon-btn w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brandGreen transition-all duration-300 shadow-sm border border-slate-200 dark:border-slate-700">
                        <i id="theme-icon" class="fas fa-moon"></i>
                    </button>
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <?php
                            // Fetch unread notification count
                            require_once "db_connect.php";
                            $unreadCount = 0;
                            if (isset($conn) && isset($_SESSION['id'])) {
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                                $stmt->bind_param("i", $_SESSION['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $unreadCount = $result->fetch_row()[0];
                                $stmt->close();
                            }
                        ?>
                        <div class="relative group">
                            <a href="notifications.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brandGreen transition-all duration-300 shadow-sm border border-slate-200 dark:border-slate-700">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white text-[8px] font-black flex items-center justify-center rounded-full animate-pulse">
                                        <?php echo $unreadCount; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <!-- Dropdown pointer -->
                            <div class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 py-4 hidden group-hover:block z-[60] glass">
                                <div class="px-6 py-2 border-b border-slate-50 dark:border-slate-800 flex justify-between items-center mb-2">
                                    <h4 class="font-black text-xs uppercase tracking-widest">Notifications</h4>
                                    <a href="notifications.php" class="text-[10px] font-bold text-brandGreen hover:underline uppercase">View All</a>
                                </div>
                                <div class="max-h-[300px] overflow-y-auto px-2">
                                    <?php
                                    if (isset($conn) && isset($_SESSION['id'])) {
                                        $notifQuery = "SELECT n.id, nc.title, nc.message, nc.type, n.created_at 
                                                       FROM notifications n 
                                                       JOIN notification_content nc ON n.content_id = nc.id 
                                                       WHERE n.user_id = ? 
                                                       ORDER BY n.created_at DESC LIMIT 5";
                                        $stmt = $conn->prepare($notifQuery);
                                        $stmt->bind_param("i", $_SESSION['id']);
                                        $stmt->execute();
                                        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                        
                                        if (empty($notifications)) {
                                            echo "<p class='text-center py-8 text-xs text-slate-400 font-bold uppercase tracking-widest'>No new messages</p>";
                                        } else {
                                            foreach ($notifications as $n) {
                                                $icon = 'fa-info-circle';
                                                $color = 'text-blue-500';
                                                if ($n['type'] == 'alert') { $icon = 'fa-exclamation-triangle'; $color = 'text-rose-500'; }
                                                if ($n['type'] == 'announcement') { $icon = 'fa-bullhorn'; $color = 'text-brandGreen'; }
                                                
                                                echo "
                                                <div class='p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-2xl transition-colors cursor-pointer border-b border-slate-50/50 dark:border-slate-800/50 last:border-0' onclick=\"window.location.href='notifications.php'\">
                                                    <div class='flex gap-3'>
                                                        <div class='mt-1 $color'><i class='fas $icon text-xs'></i></div>
                                                        <div>
                                                            <p class='text-xs font-black leading-tight mb-1'>".htmlspecialchars($n['title'])."</p>
                                                            <p class='text-[10px] text-slate-500 line-clamp-2'>".strip_tags($n['message'])."</p>
                                                            <p class='text-[9px] text-slate-400 mt-2 font-bold uppercase'>".date('M d, H:i', strtotime($n['created_at']))."</p>
                                                        </div>
                                                    </div>
                                                </div>";
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Compact User Profile Dropdown -->
                        <div class="relative group ml-1 pl-4 border-l border-slate-200 dark:border-slate-800 flex items-center gap-3">
                            <button class="flex items-center gap-2.5 focus:outline-none py-1">
                                <?php if (isset($_SESSION["profile_picture"]) && !empty($_SESSION["profile_picture"]) && file_exists(__DIR__ . '/../' . $_SESSION["profile_picture"])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION["profile_picture"]); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full object-cover border-2 border-brandGreen shadow-sm">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center border-2 border-slate-300 dark:border-slate-600 shadow-sm">
                                        <i class="fas fa-user text-slate-400 dark:text-slate-500 text-[10px]"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="hidden xl:flex flex-col text-left leading-none">
                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5">Account</span>
                                    <span class="text-xs font-black text-slate-700 dark:text-slate-200 max-w-[80px] truncate"><?php echo htmlspecialchars($_SESSION["first_name"]); ?></span>
                                </div>
                                <i class="fas fa-chevron-down text-[9px] text-slate-400 group-hover:text-brandGreen transition-colors ml-0.5"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 py-2 hidden group-hover:block z-[60] glass animate-fade-in">
                                <a href="profile.php" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-brandGreen transition-all">
                                    <i class="fas fa-id-card text-sm opacity-70 w-4 text-center"></i> My Profile
                                </a>
                                <a href="club.php" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-brandGreen transition-all">
                                    <i class="fas fa-chess-knight text-sm opacity-70 w-4 text-center"></i> Club Portal
                                </a>
                                <hr class="border-slate-100 dark:border-slate-800 my-1">
                                <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all">
                                    <i class="fas fa-sign-out-alt text-sm w-4 text-center"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-[12px] font-bold uppercase tracking-widest hover:text-brandGreen transition-colors">Login</a>
                        <a href="register.php" class="bg-brandGreen text-white px-8 py-3.5 rounded-2xl text-[12px] font-bold uppercase tracking-widest hover:bg-brandGreen/80 hover:scale-105 transition-all duration-300 shadow-lg shadow-brandGreen/20">Join Club</a>
                    <?php endif; ?>
                </div>
            </div>
            <button id="mobile-menu-btn" class="lg:hidden w-12 h-12 flex items-center justify-center text-slate-900 dark:text-white bg-white/10 rounded-xl backdrop-blur-sm border border-white/10 shadow-lg"><i class="fas fa-bars-staggered text-xl"></i></button>
        </div>
    </nav>
    <div id="mobile-menu" class="fixed inset-0 z-[60] translate-x-full transition-transform duration-500 lg:hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" id="close-menu"></div>
        <div class="relative w-80 h-full bg-white dark:bg-slate-900 ml-auto shadow-2xl flex flex-col p-8">
            <div class="flex justify-between items-center mb-12"><span class="text-xl font-bold uppercase tracking-tight text-slate-900 dark:text-white">Menu</span><button id="close-menu-btn" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-brandGreen rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700"><i class="fas fa-times"></i></button></div>
            <div class="flex flex-col gap-6">
                <a href="club.php" class="text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Club</a>
                <a href="academy.php" class="text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Academy</a>
                <a href="tournaments.php" class="text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Tournaments</a>
                <a href="shop.php" class="text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Shop</a>
                <a href="donate.php" class="text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Donate</a>
                <div class="h-px bg-slate-100 dark:bg-slate-800 my-4"></div>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <div class="flex items-center gap-4 mb-4">
                        <?php if (isset($_SESSION["profile_picture"]) && !empty($_SESSION["profile_picture"]) && file_exists(__DIR__ . '/../' . $_SESSION["profile_picture"])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION["profile_picture"]); ?>" alt="Profile Picture" class="w-10 h-10 rounded-full object-cover border-2 border-brandGreen">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center border-2 border-slate-300 dark:border-slate-600">
                                <i class="fas fa-user text-slate-400 dark:text-slate-500"></i>
                            </div>
                        <?php endif; ?>
                        <div class="text-lg font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Welcome, <?php echo htmlspecialchars($_SESSION["first_name"]); ?></div>
                    </div>
                    
                    <a href="notifications.php" class="flex items-center gap-3 text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">
                        <div class="relative">
                            <i class="fas fa-bell"></i>
                            <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                                <span class="absolute -top-1 -right-2 w-3 h-3 bg-rose-500 rounded-full border-2 border-white dark:border-slate-900 animate-pulse"></span>
                            <?php endif; ?>
                        </div>
                        Notifications
                        <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                            <span class="bg-rose-500 text-white text-xs px-2 py-0.5 rounded-full ml-auto"><?php echo $unreadCount; ?> New</span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="profile.php" class="flex items-center gap-3 text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">
                        <i class="fas fa-id-card"></i> Profile
                    </a>

                    <a href="club.php" class="flex items-center gap-3 text-lg font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">
                        <i class="fas fa-chess-knight"></i> Club Portal
                    </a>
                    
                    <a href="logout.php" class="text-lg font-bold text-red-500 uppercase tracking-widest mt-4">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-widest">Login</a>
                    <a href="register.php" class="bg-brandGreen text-white text-center py-5 rounded-3xl text-sm font-bold uppercase tracking-widest shadow-lg shadow-brandGreen/20 hover:bg-brandGreen/80 hover:scale-[1.02] active:scale-95 transition-all">Join Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
