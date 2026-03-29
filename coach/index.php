<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Coach Dashboard";
include "coach_header.php";

// Fetch some basic stats for coach (Mock counts for now)
$stats = [
    'students' => 12,
    'assignments' => 5,
    'courses' => 3,
    'pending_reviews' => 8,
];
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 text-brandGreen flex items-center justify-center text-xl">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Students</p>
                <h3 class="text-2xl font-black"><?php echo $stats['students']; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center text-xl">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Active Assignments</p>
                <h3 class="text-2xl font-black"><?php echo $stats['assignments']; ?></h3>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-xl">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">My Courses</p>
                <h3 class="text-2xl font-black"><?php echo $stats['courses']; ?></h3>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-xl">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Pending Reviews</p>
                <h3 class="text-2xl font-black"><?php echo $stats['pending_reviews']; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-black uppercase tracking-tight">Recent Student Activity</h3>
            <p class="text-slate-400 text-sm font-medium">Keep track of your students' progress</p>
        </div>
    </div>
    <div class="p-8">
        <div class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mx-auto mb-4">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
            <p class="text-slate-500 font-medium">No recent activity found. Assign a lesson to get started!</p>
        </div>
    </div>
</div>

<?php include "coach_footer.php"; ?>
