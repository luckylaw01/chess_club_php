<?php
session_start();
$pageTitle = "Chess Academy";
include 'includes/header.php';
?>

    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
        <div class="max-w-7xl mx-auto">
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen dark:text-brandGreen text-xs font-bold uppercase tracking-widest mb-6">
                <i class="fas fa-graduation-cap text-[10px]"></i> Learning Ecosystem
            </div>
            <h1 class="text-5xl lg:text-7xl font-extrabold mb-8 tracking-tight text-slate-900 dark:text-white">
                The Master's <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGreen to-brandOrange">Academy</span>
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed mb-16">
                Systematic learning programs tailored for absolute beginners to aspiring grandmasters. Our curriculum is
                designed by certified FIDE trainers and seasoned competitors.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Level 1: Foundations -->
                <div
                    class="group p-10 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-brandGreen transition-all shadow-xl hover:shadow-brandGreen/10 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-brandGreen/5 blur-[40px] rounded-full -mr-10 -mt-10 transition-all group-hover:bg-brandGreen/10">
                    </div>
                    <div
                        class="w-16 h-16 rounded-2xl bg-brandGreen/10 flex items-center justify-center text-brandGreen text-2xl mb-8">
                        <i class="fas fa-chess-pawn"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-slate-900 dark:text-white">Foundations Program</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-8 font-medium leading-relaxed">
                        For absolute beginners. Master the rules, movement, and fundamental principles of the Royal Game.
                    </p>
                    <button onclick="openModal('foundations')"
                        class="w-full py-4 rounded-2xl border-2 border-slate-900 dark:border-white font-bold uppercase tracking-widest text-[11px] hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition-all active:scale-95">
                        View Syllabus
                    </button>
                </div>

                <!-- Level 2: Tactics & Strategy -->
                <div
                    class="group p-10 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-brandGreen transition-all shadow-xl hover:shadow-brandGreen/10 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-brandOrange/5 blur-[40px] rounded-full -mr-10 -mt-10 transition-all group-hover:bg-brandOrange/10">
                    </div>
                    <div
                        class="w-16 h-16 rounded-2xl bg-brandOrange/10 flex items-center justify-center text-brandOrange text-2xl mb-8">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-slate-900 dark:text-white">Tactics & Strategy</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-8 font-medium leading-relaxed">
                        Intermediate level. Explore tactical motifs, positional play, and essential opening concepts.
                    </p>
                    <button onclick="openModal('tactics')"
                        class="w-full py-4 rounded-2xl border-2 border-slate-900 dark:border-white font-bold uppercase tracking-widest text-[11px] hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition-all active:scale-95">
                        View Syllabus
                    </button>
                </div>

                <!-- Level 3: Competitive Mastery -->
                <div
                    class="group p-10 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-brandGreen transition-all shadow-xl hover:shadow-brandGreen/10 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-brandGold/5 blur-[40px] rounded-full -mr-10 -mt-10 transition-all group-hover:bg-brandGold/10">
                    </div>
                    <div
                        class="w-16 h-16 rounded-2xl bg-brandGold/10 flex items-center justify-center text-brandGold text-2xl mb-8">
                        <i class="fas fa-chess-king"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-slate-900 dark:text-white">Competitive Mastery</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-8 font-medium leading-relaxed">
                        Advanced program. Prepare for high-level tournaments with professional analysis and endgame
                        technique.
                    </p>
                    <button onclick="openModal('mastery')"
                        class="w-full py-4 rounded-2xl border-2 border-slate-900 dark:border-white font-bold uppercase tracking-widest text-[11px] hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition-all active:scale-95">
                        View Syllabus
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Syllabus Modal Overlay -->
    <div id="syllabus-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-950/80 backdrop-blur-md opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 w-full max-w-2xl max-h-[80vh] rounded-[40px] shadow-2xl relative overflow-hidden flex flex-col transform scale-95 transition-transform duration-300">
            <!-- Modal Header -->
            <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h2 id="modal-title" class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        Syllabus Overview</h2>
                    <p id="modal-subtitle" class="text-slate-500 text-sm font-bold uppercase tracking-widest mt-1">Level 1:
                        Foundations</p>
                </div>
                <button onclick="closeModal()"
                    class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-8 overflow-y-auto flex-grow custom-scrollbar">
                <div id="syllabus-content" class="space-y-6">
                    <!-- Dynamic Content -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-8 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                <button onclick="closeModal()"
                    class="w-full py-5 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest text-sm shadow-xl shadow-brandGreen/20 hover:scale-[1.02] active:scale-95 transition-all">
                    Close Overview
                </button>
            </div>
        </div>
    </div>

    <script>
        // Syllabus Modal Logic
        const syllabusData = {
            foundations: {
                title: "Foundations Program",
                subtitle: "Level 1: Absolute Beginner",
                content: [
                    { title: "The Board & Pieces", description: "Master the battlefield layout and unique movement rules for each piece." },
                    { title: "Special Rules", description: "Castling, En Passant, and Pawn Promotion explained in detail." },
                    { title: "Core Tactics", description: "Learn Forks, Pins, and Skewers—the building blocks of calculation." },
                    { title: "Checkmate Patterns", description: "Fundamental patterns: Ladder mate, Back-rank mate, and Scholar's Mate defense." }
                ]
            },
            tactics: {
                title: "Tactics & Strategy",
                subtitle: "Level 2: Intermediate Player",
                content: [
                    { title: "Positional Mastery", description: "Space, pawn structure, and the power of outposts for your pieces." },
                    { title: "Opening Theory", description: "Developing a reliable repertoire for both White and Black." },
                    { title: "Endgame Mechanics", description: "King and Pawn endgames, opposition, and technical draw techniques." },
                    { title: "Calculation Speed", description: "Training drills to visualize 3-5 moves ahead with precision." }
                ]
            },
            mastery: {
                title: "Competitive Mastery",
                subtitle: "Level 3: Advanced Competitor",
                content: [
                    { title: "Complex Endgames", description: "Deep dive into Rook vs. Pawn and minor piece technical wins." },
                    { title: "Elite Opening Prep", description: "Mastering theoretical variations and preparing specifically for opponents." },
                    { title: "Psychology & Clock", description: "Managing time pressure and psychological aspects of tournament play." },
                    { title: "Game Analysis", description: "Methodical database review and engine-assisted post-game analysis." }
                ]
            }
        };

        const modalOverlay = document.getElementById('syllabus-modal');
        const modalContainer = modalOverlay.querySelector('div');
        const modalTitle = document.getElementById('modal-title');
        const modalSubtitle = document.getElementById('modal-subtitle');
        const syllabusContent = document.getElementById('syllabus-content');

        function openModal(levelId) {
            const data = syllabusData[levelId];
            if (!data) return;

            modalTitle.innerText = data.title;
            modalSubtitle.innerText = data.subtitle;

            syllabusContent.innerHTML = data.content.map(item => `
                <div class="flex gap-6 p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 transition-all hover:border-brandGreen/20">
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-brandGreen text-white flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1 uppercase tracking-tight">${item.title}</h4>
                        <p class="text-slate-500 dark:text-slate-400 font-medium leading-relaxed">${item.description}</p>
                    </div>
                </div>
            `).join('');

            modalOverlay.classList.remove('hidden');
            modalOverlay.classList.add('flex');
            
            // Allow browser to process layout before animating
            setTimeout(() => {
                modalOverlay.classList.replace('opacity-0', 'opacity-100');
                modalContainer.classList.replace('scale-95', 'scale-100');
            }, 10);

            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modalOverlay.classList.replace('opacity-100', 'opacity-0');
            modalContainer.classList.replace('scale-100', 'scale-95');

            setTimeout(() => {
                modalOverlay.classList.remove('flex');
                modalOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        }

        // Close on backdrop click
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeModal();
        });
    </script>

<?php include 'includes/footer.php'; ?>
