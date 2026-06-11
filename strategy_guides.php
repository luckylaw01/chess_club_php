<?php
session_start();
$pageTitle = "Strategy Guides | Learning Lab";
include 'includes/header.php';
?>

<style>
    .guide-card-bg {
        background-image: radial-gradient(circle at top right, rgba(128, 210, 0, 0.05), transparent 50%);
    }
    .dark .guide-card-bg {
        background-image: radial-gradient(circle at top right, rgba(128, 210, 0, 0.1), transparent 50%);
    }
    
    .glass-modal {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .dark .glass-modal {
        background: rgba(15, 23, 42, 0.95);
    }
</style>

<section class="pt-32 pb-20 px-6 min-h-screen bg-slate-50 dark:bg-darkBg relative overflow-hidden transition-colors duration-500">
    <!-- Abstract Background -->
    <div class="absolute top-0 inset-x-0 h-96 bg-gradient-to-b from-brandGold/5 dark:from-brandGold/10 to-transparent pointer-events-none z-0"></div>
    <div class="absolute -right-[20%] top-20 w-[600px] h-[600px] bg-brandGreen/10 blur-[150px] rounded-full pointer-events-none z-0"></div>

    <div class="max-w-7xl mx-auto relative z-10">
        
        <!-- Header -->
        <div class="text-center mb-20">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGold/10 dark:bg-brandGold/20 text-brandGold text-xs font-bold uppercase tracking-widest mb-6">
                <i class="fas fa-book-open text-[10px]"></i> Strategy Library
            </div>
            <h1 class="text-5xl md:text-7xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-6">Master the <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGold to-brandOrange">Board</span></h1>
            <p class="text-slate-500 text-lg max-w-2xl mx-auto font-medium">Elevate your game with our comprehensive library of chess strategies. From opening principles to endgame mastery, learn from the grandmasters.</p>
        </div>

        <!-- Filter/Tabs Placeholder -->
        <div class="flex flex-wrap items-center justify-center gap-4 mb-12">
            <button class="px-6 py-3 rounded-full bg-brandGold text-white font-bold text-[10px] uppercase tracking-widest shadow-lg shadow-brandGold/30 transition-all">All Guides</button>
            <button class="px-6 py-3 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-brandGold hover:border-brandGold/30 font-bold text-[10px] uppercase tracking-widest transition-all">Openings</button>
            <button class="px-6 py-3 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-brandGold hover:border-brandGold/30 font-bold text-[10px] uppercase tracking-widest transition-all">Middlegame Tactics</button>
            <button class="px-6 py-3 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-brandGold hover:border-brandGold/30 font-bold text-[10px] uppercase tracking-widest transition-all">Endgame Theory</button>
        </div>

        <!-- Guides Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Guide Card 1 -->
            <div class="group relative bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-slate-200 dark:border-slate-800 shadow-xl hover:shadow-2xl hover:border-brandGold/50 transition-all duration-500 overflow-hidden guide-card-bg cursor-pointer" onclick="openGuideModal('italian')">
                <div class="absolute -right-8 -bottom-8 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                    <i class="fas fa-chess-knight text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <span class="px-3 py-1 bg-brandGold/10 text-brandGold rounded-full text-[9px] font-black uppercase tracking-widest mb-6 inline-block">Openings</span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3 tracking-tight group-hover:text-brandGold transition-colors">The Italian Game</h3>
                    <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed line-clamp-3">One of the oldest and most solid chess openings. Control the center, develop quickly, and prepare for an aggressive middlegame.</p>
                    <div class="flex items-center gap-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span><i class="far fa-clock mr-1"></i> 5 min read</span>
                        <span>&bull;</span>
                        <span>Beginner / Int</span>
                    </div>
                </div>
            </div>

            <!-- Guide Card 2 -->
            <div class="group relative bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-slate-200 dark:border-slate-800 shadow-xl hover:shadow-2xl hover:border-brandOrange/50 transition-all duration-500 overflow-hidden guide-card-bg cursor-pointer" onclick="openGuideModal('sicilian')">
                <div class="absolute -right-8 -bottom-8 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                    <i class="fas fa-chess-queen text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <span class="px-3 py-1 bg-brandOrange/10 text-brandOrange rounded-full text-[9px] font-black uppercase tracking-widest mb-6 inline-block">Openings</span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3 tracking-tight group-hover:text-brandOrange transition-colors">Sicilian Defense</h3>
                    <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed line-clamp-3">The most popular and best-scoring response to e4. Fight for a win with black using asymmetrical and dynamic positions.</p>
                    <div class="flex items-center gap-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span><i class="far fa-clock mr-1"></i> 8 min read</span>
                        <span>&bull;</span>
                        <span>Advanced</span>
                    </div>
                </div>
            </div>

            <!-- Guide Card 3 -->
            <div class="group relative bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-slate-200 dark:border-slate-800 shadow-xl hover:shadow-2xl hover:border-purple-500/50 transition-all duration-500 overflow-hidden guide-card-bg cursor-pointer" onclick="openGuideModal('pins')">
                <div class="absolute -right-8 -bottom-8 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                    <i class="fas fa-chess-bishop text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <span class="px-3 py-1 bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-full text-[9px] font-black uppercase tracking-widest mb-6 inline-block">Middlegame</span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3 tracking-tight group-hover:text-purple-500 transition-colors">Mastering Pins & Skewers</h3>
                    <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed line-clamp-3">Learn to paralyze opponent pieces and win material using the most common tactical motifs in chess.</p>
                    <div class="flex items-center gap-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span><i class="far fa-clock mr-1"></i> 6 min read</span>
                        <span>&bull;</span>
                        <span>Intermediate</span>
                    </div>
                </div>
            </div>

            <!-- Guide Card 4 -->
            <div class="group relative bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-slate-200 dark:border-slate-800 shadow-xl hover:shadow-2xl hover:border-brandGreen/50 transition-all duration-500 overflow-hidden guide-card-bg cursor-pointer" onclick="openGuideModal('endgame')">
                <div class="absolute -right-8 -bottom-8 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                    <i class="fas fa-chess-pawn text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <span class="px-3 py-1 bg-brandGreen/10 text-brandGreen rounded-full text-[9px] font-black uppercase tracking-widest mb-6 inline-block">Endgame</span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3 tracking-tight group-hover:text-brandGreen transition-colors">King & Pawn Endings</h3>
                    <p class="text-slate-500 text-sm font-medium mb-8 leading-relaxed line-clamp-3">Understand the "Rule of the Square", "Opposition", and outflanking to consistently convert advantages into wins.</p>
                    <div class="flex items-center gap-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span><i class="far fa-clock mr-1"></i> 10 min read</span>
                        <span>&bull;</span>
                        <span>All Levels</span>
                    </div>
                </div>
            </div>
            
            <!-- Coming Soon Card -->
            <div class="group relative bg-slate-100/50 dark:bg-slate-800/20 rounded-[40px] p-8 border-2 border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <h3 class="text-lg font-black text-slate-400 uppercase tracking-tight">More Guides Incoming</h3>
                <p class="text-[10px] font-bold uppercase tracking-widest text-brandGold mt-2">Stay Tuned</p>
            </div>

        </div>
    </div>
</section>

<!-- Reading Modal -->
<div id="guideModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-sm animate-in fade-in duration-300">
    <div class="glass-modal w-full max-w-4xl max-h-[90vh] rounded-[40px] shadow-2xl relative overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 sm:p-8 border-b border-slate-200 dark:border-slate-800">
            <div>
                <span id="modalCategory" class="text-[10px] font-black uppercase tracking-widest text-brandGold mb-2 block">Category</span>
                <h2 id="modalTitle" class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Strategy Title</h2>
            </div>
            <button onclick="closeGuideModal()" class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all flex-shrink-0">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div class="p-6 sm:p-10 overflow-y-auto custom-scrollbar prose dark:prose-invert max-w-none">
            <div id="modalContent" class="space-y-6 text-slate-600 dark:text-slate-300">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>
</div>

<script>
    const guideContents = {
        'italian': {
            title: 'The Italian Game',
            category: 'Openings',
            content: `
                <p class="text-lg font-medium text-slate-900 dark:text-white mb-6">The Italian Game begins with the moves: <strong>1. e4 e5 2. Nf3 Nc6 3. Bc4</strong>.</p>
                <p>The core idea of the Italian Game is to control the center with a pawn on e4, develop the knight to f3 to attack e5, and place the bishop on the active c4 square where it eyes the vulnerable f7 square.</p>
                <div class="my-8 p-6 bg-slate-100 dark:bg-slate-800 rounded-3xl border-l-4 border-brandGold">
                    <h4 class="font-bold mb-2">Key Concepts</h4>
                    <ul class="list-disc list-inside space-y-2 marker:text-brandGold">
                        <li>Control of the center.</li>
                        <li>Rapid development of minor pieces.</li>
                        <li>Early castling to secure the King.</li>
                        <li>Pressure on the f7 pawn.</li>
                    </ul>
                </div>
                <p>Depending on Black's 3rd move, the game can branch into the Giuoco Piano (3...Bc5) or the Two Knights Defense (3...Nf6), each leading to rich, dynamic middlegame positions.</p>
            `
        },
        'sicilian': {
            title: 'Sicilian Defense',
            category: 'Openings',
            content: `
                <p class="text-lg font-medium text-slate-900 dark:text-white mb-6">The Sicilian Defense begins with the moves: <strong>1. e4 c5</strong>.</p>
                <p>By playing 1...c5, Black immediately fights for the center while creating an asymmetrical pawn structure. This leads to complex, sharp, and unbalanced positions where both sides play for a win.</p>
                <div class="my-8 p-6 bg-slate-100 dark:bg-slate-800 rounded-3xl border-l-4 border-brandOrange">
                    <h4 class="font-bold mb-2">Popular Variations</h4>
                    <ul class="list-disc list-inside space-y-2 marker:text-brandOrange">
                        <li><strong>Najdorf Variation:</strong> 5...a6 - Extremely flexible and aggressive.</li>
                        <li><strong>Dragon Variation:</strong> 5...g6 - Fianchettoing the bishop, creating a sharp tactical battle.</li>
                        <li><strong>Sveshnikov Variation:</strong> 5...e5 - Forcing the knight back but weakening the d5 square.</li>
                    </ul>
                </div>
                <p>The Sicilian is the most popular defense at the master level and requires deep theoretical preparation to play successfully.</p>
            `
        },
        'pins': {
            title: 'Mastering Pins & Skewers',
            category: 'Middlegame',
            content: `
                <p class="text-lg font-medium text-slate-900 dark:text-white mb-6">A pin is a situation where a piece cannot move without exposing a more valuable piece to capture.</p>
                <p>There are two types of pins:</p>
                <ul class="list-disc list-inside space-y-2 mb-6">
                    <li><strong>Absolute Pin:</strong> A piece is pinned to the King. It is illegal to move the pinned piece.</li>
                    <li><strong>Relative Pin:</strong> A piece is pinned to a piece more valuable than the attacker (like a Queen). Moving it is legal, but usually a bad idea.</li>
                </ul>
                <p>A <strong>skewer</strong> is the reverse of a pin. The more valuable piece is under attack and forced to move, exposing a less valuable piece behind it to capture.</p>
                <div class="my-8 p-6 bg-purple-500/10 rounded-3xl">
                    <h4 class="font-bold mb-2 text-purple-600 dark:text-purple-400">Rule of Thumb</h4>
                    <p class="text-sm">"The pinned piece is a paralyzed piece." Always look for ways to apply pressure on a pinned piece by attacking it with pawns or other minor pieces.</p>
                </div>
            `
        },
        'endgame': {
            title: 'King & Pawn Endings',
            category: 'Endgame Theory',
            content: `
                <p class="text-lg font-medium text-slate-900 dark:text-white mb-6">Pawn endings are the foundation of all endgame play. Every piece ending eventually reduces to a pawn ending.</p>
                <p>Mastering the following concepts is essential:</p>
                <div class="space-y-6 my-6">
                    <div class="p-6 bg-brandGreen/5 border border-brandGreen/20 rounded-3xl">
                        <h4 class="font-bold mb-2">1. The Rule of the Square</h4>
                        <p class="text-sm">Determines if the King can catch a passed pawn without calculating every move. Draw a square from the pawn to the promotion rank. If the King is in the square, it catches the pawn.</p>
                    </div>
                    <div class="p-6 bg-brandGreen/5 border border-brandGreen/20 rounded-3xl">
                        <h4 class="font-bold mb-2">2. Opposition</h4>
                        <p class="text-sm">When two kings face each other with one square in between. The player whose turn it is to move must step aside, allowing the opponent's king to advance.</p>
                    </div>
                    <div class="p-6 bg-brandGreen/5 border border-brandGreen/20 rounded-3xl">
                        <h4 class="font-bold mb-2">3. Outflanking</h4>
                        <p class="text-sm">Using the opposition to force your way past the enemy king to attack pawns or control key squares.</p>
                    </div>
                </div>
            `
        }
    };

    function openGuideModal(id) {
        const guide = guideContents[id];
        if (guide) {
            document.getElementById('modalTitle').textContent = guide.title;
            document.getElementById('modalCategory').textContent = guide.category;
            document.getElementById('modalContent').innerHTML = guide.content;
            document.getElementById('guideModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    function closeGuideModal() {
        document.getElementById('guideModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>

<?php include 'includes/footer.php'; ?>
