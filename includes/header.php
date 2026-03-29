<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " | Ascending Pawn Chess Club" : "Ascending Pawn Chess Club | Master the Game"; ?></title>
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
    </style>
</head>

<body class="bg-slate-50 dark:bg-darkBg text-slate-900 dark:text-slate-100 transition-colors duration-500 font-sans">
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-500 py-6">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="bg-white p-2.5 rounded-2xl shadow-lg ring-1 ring-slate-200/50 group-hover:scale-110 transition-transform duration-300">
                    <img src="assets/images/logo1.png" alt="Ascending Pawn Logo" class="w-6 h-6 object-contain">
                </div>
                <span class="text-2xl font-black tracking-tighter uppercase text-slate-900 dark:text-white">Ascending<span class="text-brandGreen">Pawn</span></span>
            </a>
            <div class="hidden lg:flex items-center gap-10 text-slate-900 dark:text-slate-100">
                <div class="flex items-center gap-8">
                    <a href="club.php" class="text-[13px] font-bold hover:text-brandGreen transition-all uppercase tracking-[0.15em]">Club</a>
                    <a href="academy.php" class="text-[13px] font-bold hover:text-brandGreen transition-all uppercase tracking-[0.15em]">Academy</a>
                    <a href="tournaments.php" class="text-[13px] font-bold hover:text-brandGreen transition-all uppercase tracking-[0.15em]">Tournaments</a>
                    <a href="shop.php" class="text-[13px] font-bold hover:text-brandGreen transition-all uppercase tracking-[0.15em]">Shop</a>
                </div>
                <div class="h-8 w-px bg-slate-200 dark:bg-slate-800 mx-2"></div>
                <div class="flex items-center gap-6">
                    <button id="theme-toggle" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brandGreen transition-all duration-300 shadow-sm border border-slate-200 dark:border-slate-700"><i id="theme-icon" class="fas fa-moon"></i></button>
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <span class="text-[13px] font-bold text-slate-600 dark:text-slate-400">Welcome, <?php echo htmlspecialchars($_SESSION["first_name"]); ?></span>
                        <a href="logout.php" class="text-[13px] font-bold uppercase tracking-widest hover:text-red-500 transition-colors">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-[13px] font-bold uppercase tracking-widest hover:text-brandGreen transition-colors">Login</a>
                        <a href="register.php" class="bg-brandGreen text-white px-8 py-3.5 rounded-2xl text-[13px] font-bold uppercase tracking-widest hover:bg-brandNeonGreen hover:scale-105 transition-all duration-300 shadow-lg shadow-brandGreen/20">Join Club</a>
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
                <a href="club.php" class="text-xl font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Club</a>
                <a href="academy.php" class="text-xl font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Academy</a>
                <a href="tournaments.php" class="text-xl font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Tournaments</a>
                <a href="shop.php" class="text-xl font-bold text-slate-900 dark:text-white hover:text-brandGreen transition-colors uppercase tracking-widest">Shop</a>
                <div class="h-px bg-slate-100 dark:bg-slate-800 my-4"></div>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <div class="text-lg font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-4">Welcome, <?php echo htmlspecialchars($_SESSION["first_name"]); ?></div>
                    <a href="logout.php" class="text-lg font-bold text-red-500 uppercase tracking-widest">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-lg font-bold text-slate-900 dark:text-white uppercase tracking-widest">Login</a>
                    <a href="register.php" class="bg-brandGreen text-white text-center py-5 rounded-3xl font-bold uppercase tracking-widest shadow-lg shadow-brandGreen/20">Join Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
