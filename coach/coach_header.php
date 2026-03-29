<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Only coaches allowed
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'coach') {
    header("location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " | Coach Dashboard" : "Coach Dashboard"; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brandGreen: '#22c55e',
                        darkBg: '#0f172a',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-darkBg text-slate-900 dark:text-slate-100 min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col fixed h-full z-50">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800">
            <a href="../index.php" class="flex items-center gap-3">
                <span class="text-xl font-black uppercase tracking-tighter">Coach<span class="text-brandGreen">Pawn</span></span>
            </a>
        </div>
        
        <?php include 'coach_sidebar.php'; ?>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Content Area -->
    <div class="flex-1 ml-64 flex flex-col">
        <header class="h-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8 sticky top-0 z-40">
            <h2 class="text-lg font-bold text-slate-400 uppercase tracking-widest"><?php echo isset($pageTitle) ? $pageTitle : "Overview"; ?></h2>
            <div class="flex items-center gap-4">
                <button class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-brandGreen transition-colors">
                    <i class="fas fa-bell"></i>
                </button>
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-800">
                    <div class="text-right">
                        <p class="text-sm font-black"><?php echo $_SESSION["first_name"] . " " . $_SESSION["last_name"]; ?></p>
                        <p class="text-[10px] font-bold text-brandGreen uppercase tracking-widest">Master Coach</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-8">
