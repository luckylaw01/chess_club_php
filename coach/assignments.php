<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Assignments";
include "coach_header.php";
?>

<div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden py-24">
    <div class="max-w-md mx-auto text-center">
        <div class="w-24 h-24 rounded-full bg-orange-500/10 text-orange-500 flex items-center justify-center text-4xl mx-auto mb-8 animate-bounce">
            <i class="fas fa-edit"></i>
        </div>
        <h2 class="text-3xl font-black uppercase tracking-tight mb-4">Coming Soon</h2>
        <p class="text-slate-500 font-medium mb-8">The Assignments module is in the endgame of development. You'll soon be able to create and review lessons for your students.</p>
        <a href="index.php" class="inline-block px-8 py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brandGreen/90 transition-all active:scale-95 shadow-lg shadow-brandGreen/20">
            Back to Dashboard
        </a>
    </div>
</div>

<?php include "coach_footer.php"; ?>
