        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-chart-pie w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="students.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-user-graduate w-5"></i>
                <span>My Students</span>
            </a>
            <a href="assignments.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-edit w-5"></i>
                <span>Assignments</span>
            </a>
            <a href="courses.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-graduation-cap w-5"></i>
                <span>My Courses</span>
            </a>
        </nav>
