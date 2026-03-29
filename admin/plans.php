<?php
session_start();
$pageTitle = "Membership Management";
include "admin_header.php";
?>

<div class="min-h-[60vh] flex flex-col items-center justify-center text-center p-8 text-slate-900 dark:text-white">
    <div class="w-32 h-32 mb-10 bg-brandGreen/10 flex items-center justify-center rounded-[40px] text-5xl relative animate-pulse">
        <i class="fas fa-credit-card text-brandGreen"></i>
        <div class="absolute -top-2 -right-2 bg-slate-900 dark:bg-slate-200 text-white dark:text-slate-900 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest">
            Wait
        </div>
    </div>
    
    <h1 class="text-5xl font-black mb-6 uppercase tracking-tight">Coming <span class="text-brandGreen">Soon</span></h1>
    <p class="text-slate-500 dark:text-slate-400 max-w-sm mx-auto font-medium leading-relaxed mb-10">
        Subscription tiers, pricing models, and payment integrations are currently being architected.
    </p>

    <div class="w-full max-w-xs bg-slate-100 dark:bg-slate-900/50 h-2 rounded-full overflow-hidden mb-4">
        <div class="bg-brandGreen h-full w-[65%] rounded-full animate-pulse transition-all duration-1000"></div>
    </div>
    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">65% Processed</p>
</div>

<?php include "admin_footer.php"; ?>