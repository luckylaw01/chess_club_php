        </main>
        
        <footer class="mt-auto p-4 md:p-8 border-t border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-widest text-center">
            &copy; <?php echo date("Y"); ?> Ascending Pawn Admin Portal. Built with &hearts; for Chess Masters.
        </footer>
    </div>
    
    <!-- Global Toast Container -->
    <div id="toastContainer" class="fixed top-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none"></div>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl border text-sm font-semibold transform -translate-y-2 opacity-0 transition-all duration-300 ${
                type === 'success' 
                ? 'bg-slate-900 text-white border-brandGreen/40 dark:bg-slate-800 dark:border-brandGreen/50' 
                : 'bg-red-900 text-white border-red-500/40 dark:bg-red-950 dark:border-red-500/50'
            }`;

            const iconClass = type === 'success' 
                ? 'fa-circle-check text-brandGreen' 
                : 'fa-circle-exclamation text-red-400';

            toast.innerHTML = `<i class="fa-solid ${iconClass} text-lg"></i><span>${message}</span>`;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('-translate-y-2', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-2', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3500);
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                // Open sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                // Small delay to allow display:block to apply before opacity transition
                setTimeout(() => {
                    overlay.classList.remove('opacity-0');
                    overlay.classList.add('opacity-100');
                }, 10);
            } else {
                // Close sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 300); // match transition duration
            }
        }
    </script>
</body>
</html>