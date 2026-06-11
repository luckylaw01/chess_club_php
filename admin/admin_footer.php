        </main>
        
        <footer class="mt-auto p-4 md:p-8 border-t border-slate-200 dark:border-slate-800 text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-widest text-center">
            &copy; <?php echo date("Y"); ?> Ascending Pawn Admin Portal. Built with &hearts; for Chess Masters.
        </footer>
    </div>
    
    <script>
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