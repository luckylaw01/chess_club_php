<?php
session_start();
$pageTitle = "Daily Puzzles | Learning Lab";
include 'includes/header.php';
?>

<style>
    .puzzle-grid {
        background-image: 
            linear-gradient(rgba(128, 210, 0, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(128, 210, 0, 0.05) 1px, transparent 1px);
        background-size: 30px 30px;
    }
    
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .dark .glass-panel {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>

<section class="pt-32 pb-20 px-6 min-h-screen bg-slate-50 dark:bg-darkBg relative overflow-hidden transition-colors duration-500">
    <!-- Abstract Background Elements -->
    <div class="absolute inset-0 pointer-events-none z-0 puzzle-grid"></div>
    <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-brandGreen/20 blur-[100px] rounded-full pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 bg-brandOrange/10 blur-[80px] rounded-full pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-12 gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen text-xs font-bold uppercase tracking-widest mb-4">
                    <i class="fas fa-puzzle-piece text-[10px]"></i> Learning Lab
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-2">Daily <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGreen to-brandOrange">Puzzles</span></h1>
                <p class="text-slate-500 text-sm md:text-base max-w-xl font-medium">Test your tactical vision with curated puzzles updated daily. Find the winning combination and improve your pattern recognition.</p>
            </div>
            
            <!-- Quick Stats -->
            <div class="flex gap-4">
                <div class="glass-panel p-4 rounded-3xl text-center min-w-[120px] shadow-lg">
                    <i class="fas fa-fire text-brandOrange text-2xl mb-2"></i>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Current Streak</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">0 <span class="text-sm font-bold text-slate-500">days</span></p>
                </div>
                <div class="glass-panel p-4 rounded-3xl text-center min-w-[120px] shadow-lg">
                    <i class="fas fa-star text-brandGold text-2xl mb-2"></i>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Puzzles Solved</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">0</p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Puzzle Area (embedded Lichess widget) -->
            <div class="lg:col-span-2 glass-panel rounded-[40px] p-2 md:p-6 shadow-2xl relative">
                <!-- Decorative Top Bar -->
                <div class="flex items-center justify-between px-6 py-4 mb-2 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <div class="w-3 h-3 rounded-full bg-brandGreen"></div>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Interactive Puzzle Board</p>
                </div>
                
                <div class="w-full h-[500px] md:h-[650px] rounded-3xl overflow-hidden bg-slate-900 flex items-center justify-center relative shadow-inner">
                    <!-- Loading Skeleton -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 z-0 text-brandGreen">
                        <i class="fas fa-chess-knight text-6xl animate-pulse mb-4"></i>
                        <p class="text-[10px] font-bold uppercase tracking-widest animate-pulse">Loading Board...</p>
                    </div>
                    <!-- The iframe -->
                    <iframe src="https://lichess.org/training/frame?theme=brown&bg=dark" class="w-full h-full relative z-10 border-0" allowtransparency="true" frameborder="0"></iframe>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Instructions Card -->
                <div class="glass-panel p-8 rounded-[40px] shadow-xl">
                    <h3 class="font-black text-xl uppercase tracking-tight mb-6">How to Play</h3>
                    <ul class="space-y-6 relative before:absolute before:inset-y-0 before:left-[11px] before:w-[2px] before:bg-slate-200 dark:before:bg-slate-800">
                        <li class="relative pl-10">
                            <div class="absolute left-0 top-1 w-6 h-6 rounded-full bg-brandGreen text-white flex items-center justify-center text-[10px] font-black z-10 shadow-lg shadow-brandGreen/40">1</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">Analyze the Position</h4>
                            <p class="text-xs text-slate-500 font-medium">Evaluate king safety, material balance, and active pieces.</p>
                        </li>
                        <li class="relative pl-10">
                            <div class="absolute left-0 top-1 w-6 h-6 rounded-full bg-brandGreen text-white flex items-center justify-center text-[10px] font-black z-10 shadow-lg shadow-brandGreen/40">2</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">Find the Candidate Moves</h4>
                            <p class="text-xs text-slate-500 font-medium">Look for forcing moves: checks, captures, and threats.</p>
                        </li>
                        <li class="relative pl-10">
                            <div class="absolute left-0 top-1 w-6 h-6 rounded-full bg-brandGreen text-white flex items-center justify-center text-[10px] font-black z-10 shadow-lg shadow-brandGreen/40">3</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">Calculate & Execute</h4>
                            <p class="text-xs text-slate-500 font-medium">Play out the line in your head before making your move on the board.</p>
                        </li>
                    </ul>
                </div>

                <!-- Theme of the Day -->
                <div class="glass-panel p-8 rounded-[40px] shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-brandGold/10 rounded-bl-[100px] transition-transform group-hover:scale-110"></div>
                    <h3 class="font-black text-[10px] uppercase tracking-widest text-brandGold mb-2">Today's Focus</h3>
                    <h4 class="font-black text-2xl text-slate-900 dark:text-white mb-4 uppercase tracking-tight">Deflection Tactics</h4>
                    <p class="text-sm text-slate-500 font-medium mb-6">A deflection tactic forces an opponent's piece to leave the square, rank, or file it occupies, thereby exposing the king or a valuable piece.</p>
                    <a href="strategy_guides.php" class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-900 dark:text-white hover:text-brandGold transition-colors border-b-2 border-brandGold pb-1">
                        Read Guide <i class="fas fa-arrow-right text-[8px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
