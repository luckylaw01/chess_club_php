<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Dashboard Overview";
include "admin_header.php";

// Fetch some basic stats
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0],
    'active_members' => $conn->query("SELECT COUNT(*) FROM users WHERE membership_status = 'active'")->fetch_row()[0],
    'tournaments' => $conn->query("SELECT COUNT(*) FROM tournaments WHERE status = 'upcoming'")->fetch_row()[0],
    'orders' => $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0],
];

// Fetch recent users
$recentUsers = $conn->query("SELECT id, username, email, membership_status, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-xl">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Users</p>
                <h3 class="text-2xl font-black"><?php echo $stats['users']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Unique registered accounts</p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xl">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Active Plans</p>
                <h3 class="text-2xl font-black"><?php echo $stats['active_members']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Currently active memberships</p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl">
                <i class="fas fa-trophy"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Open Events</p>
                <h3 class="text-2xl font-black"><?php echo $stats['tournaments']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Upcoming club tournaments</p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-xl">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Pending Orders</p>
                <h3 class="text-2xl font-black"><?php echo $stats['orders']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Orders awaiting processing</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <h3 class="font-bold">Recent Registrations</h3>
        <a href="users.php" class="text-xs font-bold uppercase tracking-widest text-brandGreen hover:underline">View All Users</a>
    </div>
    <div class="p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                    <th class="px-8 py-4">User</th>
                    <th class="px-8 py-4">Status</th>
                    <th class="px-8 py-4">Joined At</th>
                    <th class="px-8 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($recentUsers as $user): ?>
                <tr class="text-sm hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                    <td class="px-8 py-4">
                        <p class="font-bold"><?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="text-xs text-slate-500 lowercase"><?php echo htmlspecialchars($user['email']); ?></p>
                    </td>
                    <td class="px-8 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php 
                            echo $user['membership_status'] == 'active' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'; 
                        ?>">
                            <?php echo $user['membership_status']; ?>
                        </span>
                    </td>
                    <td class="px-8 py-4 text-slate-500 font-medium">
                        <?php echo date("M j, Y", strtotime($user['created_at'])); ?>
                    </td>
                    <td class="px-8 py-4 text-right">
                        <a href="users.php?edit_id=<?php echo $user['id']; ?>" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "admin_footer.php"; ?>