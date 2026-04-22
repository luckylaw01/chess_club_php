        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-chart-pie w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-users w-5"></i>
                <span>Manage Users</span>
            </a>
            <a href="tournaments.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'tournaments.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-trophy w-5"></i>
                <span>Tournaments</span>
            </a>
            <a href="academy.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'academy.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-graduation-cap w-5"></i>
                <span>Academy</span>
            </a>
            <a href="plans.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'plans.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-credit-card w-5"></i>
                <span>Membership</span>
            </a>
            <a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-shopping-bag w-5"></i>
                <span>Shop</span>
            </a>
            <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-clipboard-list w-5"></i>
                <span>Orders</span>
            </a>
            <a href="communications.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'communications.php' ? 'bg-brandGreen/10 text-brandGreen font-bold' : ''; ?>">
                <i class="fas fa-bullhorn w-5"></i>
                <span>Communications</span>
            </a>
        </nav>