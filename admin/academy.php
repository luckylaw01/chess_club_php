<?php
session_start();
$pageTitle = "Academy Management";
include "admin_header.php";
?>

<div class="min-h-[60vh] flex flex-col items-center justify-center text-center p-8">
    <div class="w-24 h-24 mb-8 bg-purple-500/10 text-purple-500 flex items-center justify-center rounded-[32px] text-4xl animate-bounce">
        <i class="fas fa-graduation-cap"></i>
    </div>
    <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter text-brandGreen">Coming <span class="text-slate-900 dark:text-white">Soon</span></h1>
    <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto font-medium leading-relaxed">
        The masterclasses are in preparation. Our academy management portal will soon allow you to assign coaches, upload lessons, and track student ratings.
    </p>
    <div class="mt-10">
        <div class="inline-flex items-center gap-3 px-6 py-2 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-black uppercase tracking-widest text-slate-400">
            <span class="w-1.5 h-1.5 bg-brandGreen rounded-full animate-pulse"></span>
            Under Development
        </div>
    </div>
</div>

<?php include "admin_footer.php"; ?>