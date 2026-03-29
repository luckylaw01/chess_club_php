<?php
session_start();
$pageTitle = 'SHOP';
include 'includes/header.php';
?>

    <!-- Header Section -->
    <header class="pt-32 pb-12 px-6 text-center animate-slide-up">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-5xl lg:text-6xl font-extrabold mb-4 tracking-tight">The Pawn's <span
                    class="text-brandGreen">Collection</span></h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">Premium chess equipment and apparel designed
                for masters of strategy.</p>
        </div>
    </header>

    <!-- Product Grid -->
    <section class="pb-24 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- Product 1 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="assets/images/shop/magnetic_board.png" alt="Magnetic Chess Board"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div
                        class="absolute top-4 right-4 bg-blue-600 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                        Premium</div>
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Magnetic Chess Board</h3>
                    <p class="text-slate-500 text-xs mb-4">Sleek wood finish, travel-friendly.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$45.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="assets/images/shop/slotted_board.png" alt="Slotted Chess Board"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Slotted Chess Board</h3>
                    <p class="text-slate-500 text-xs mb-4">Luxury Walnut & Maple design.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$60.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product 3 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="assets/images/shop/hoodie.png" alt="Black Knights Hoodie"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div
                        class="absolute top-4 right-4 bg-slate-900 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                        New Arrival</div>
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Ascending Pawn Hoodie</h3>
                    <p class="text-slate-500 text-xs mb-4">Minimalist pawn embroidery.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$35.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product 4 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="assets/images/shop/chess_set.png" alt="Professional Chess Set"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Professional Chess Set</h3>
                    <p class="text-slate-500 text-xs mb-4">Staunton weighted pieces.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$85.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product 5 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="assets/images/shop/chess_clock.png" alt="Digital Chess Clock"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Digital Chess Clock</h3>
                    <p class="text-slate-500 text-xs mb-4">Pro tournament LED display.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$40.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product 6 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="assets/images/shop/scorebook.png" alt="Chess Scorebook"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Chess Scorebook</h3>
                    <p class="text-slate-500 text-xs mb-4">Luxury leather-bound edition.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$15.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product 7 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="https://images.unsplash.com/photo-1529699211952-734e80c4d42b?auto=format&fit=crop&q=80&w=400"
                        alt="Training Keychain"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Training Keychain</h3>
                    <p class="text-slate-500 text-xs mb-4">Silver-plated pawn piece.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$10.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product 8 -->
            <div
                class="group bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                <div class="aspect-square overflow-hidden relative">
                    <img src="https://images.unsplash.com/photo-1588850567047-1849a444bc68?auto=format&fit=crop&q=80&w=400"
                        alt="Grandmaster Cap"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-1">Grandmaster Cap</h3>
                    <p class="text-slate-500 text-xs mb-4">Embroidered navy classic.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-black text-brandGreen">$25.00</span>
                        <button
                            class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

<?php include 'includes/footer.php'; ?>
</body>

</html>

