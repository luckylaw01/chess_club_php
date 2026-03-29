    <!-- Footer -->
    <footer class="pt-24 pb-12 px-6 bg-slate-50 dark:bg-black border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-2 space-y-6">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold tracking-tight uppercase">Ascending<span
                                class="text-brandGreen">Pawn</span></span>
                    </div>
                    <p class="max-w-xs text-slate-500 text-sm leading-relaxed">
                        The ultimate destination for chess players in Kenya. Home to the Ascending Pawn Club, Academy,
                        and national-level tournaments.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-brandGreen hover:scale-110 transition-all duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-brandGreen hover:scale-110 transition-all duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-brandGreen hover:scale-110 transition-all duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold mb-6 text-sm uppercase text-brandGreen">Explore</h4>
                    <ul class="space-y-4 text-sm text-slate-500 font-medium">
                        <li class="hover:text-brandGreen cursor-pointer transition-colors"><a href="club.php">Club Membership</a></li>
                        <li class="hover:text-brandGreen cursor-pointer transition-colors"><a href="academy.php">Academy Programs</a></li>
                        <li class="hover:text-brandGreen cursor-pointer transition-colors"><a href="tournaments.php">Tournaments</a></li>
                        <li class="hover:text-brandGreen cursor-pointer transition-colors"><a href="shop.php">Shop</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-6 text-sm uppercase text-brandGreen">Contact</h4>
                    <p class="text-sm text-slate-500 font-medium leading-relaxed">Nairobi, Kenya<br>info@ascendingpawn.co.ke</p>
                </div>
            </div>
            <div
                class="pt-8 border-t border-slate-200 dark:border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-widest flex justify-between items-center">
                <span>© <?php echo date("Y"); ?> Ascending Pawn Chess Club. All Rights Reserved.</span>
                <span class="text-slate-300 dark:text-slate-700">Master the Board</span>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 20) {
                nav.classList.add('bg-white/80', 'dark:bg-black/80', 'backdrop-blur-md', 'shadow-lg', 'py-3');
                nav.classList.remove('py-6');
            } else {
                nav.classList.remove('bg-white/80', 'dark:bg-black/80', 'backdrop-blur-md', 'shadow-lg', 'py-3');
                nav.classList.add('py-6');
            }
        });

        // Theme and Navigation Logic
        (function () {
            const themeBtn = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const html = document.documentElement;

            function updateThemeElements(isDark) {
                if (isDark) {
                    html.classList.add('dark');
                    if (themeIcon) themeIcon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    html.classList.remove('dark');
                    if (themeIcon) themeIcon.classList.replace('fa-sun', 'fa-moon');
                }
            }

            // Mobile Menu Logic
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('close-menu');
            const mobileMenu = document.getElementById('mobile-menu');
            const closeBtnIcon = document.getElementById('close-menu-btn');

            if (mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', () => {
                    mobileMenu.classList.remove('translate-x-full');
                });
            }

            const hideMenu = () => {
                if(mobileMenu) mobileMenu.classList.add('translate-x-full');
            };

            if (closeBtn) closeBtn.addEventListener('click', hideMenu);
            if (closeBtnIcon) closeBtnIcon.addEventListener('click', hideMenu);

            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    const isDark = html.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    updateThemeElements(isDark);
                });
            }
        })();
    </script>
</body>

</html>
