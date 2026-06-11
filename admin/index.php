<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Dashboard Overview";
include "admin_header.php";

// Time boundaries
$thisMonthStart = date('Y-m-01 00:00:00');
$lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
$lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));

function getStatWithTrend($conn, $queryThis, $queryLast, $queryTotal) {
    $thisMonth = $conn->query($queryThis)->fetch_row()[0] ?? 0;
    $lastMonth = $conn->query($queryLast)->fetch_row()[0] ?? 0;
    $total = $conn->query($queryTotal)->fetch_row()[0] ?? 0;
    
    $trend = 0;
    if ($lastMonth > 0) {
        $trend = (($thisMonth - $lastMonth) / $lastMonth) * 100;
    } elseif ($thisMonth > 0) {
        $trend = 100;
    }
    
    return [
        'current' => $total,
        'this_month' => $thisMonth,
        'trend' => round($trend, 1)
    ];
}

$usersStat = getStatWithTrend($conn, 
    "SELECT COUNT(*) FROM users WHERE created_at >= '$thisMonthStart'",
    "SELECT COUNT(*) FROM users WHERE created_at >= '$lastMonthStart' AND created_at <= '$lastMonthEnd'",
    "SELECT COUNT(*) FROM users"
);

$revenueStat = getStatWithTrend($conn,
    "SELECT SUM(amount) FROM payments WHERE status = 'completed' AND created_at >= '$thisMonthStart'",
    "SELECT SUM(amount) FROM payments WHERE status = 'completed' AND created_at >= '$lastMonthStart' AND created_at <= '$lastMonthEnd'",
    "SELECT SUM(amount) FROM payments WHERE status = 'completed'"
);
if (!$revenueStat['current']) $revenueStat['current'] = 0;

$ordersStat = getStatWithTrend($conn,
    "SELECT COUNT(*) FROM orders WHERE order_date >= '$thisMonthStart'",
    "SELECT COUNT(*) FROM orders WHERE order_date >= '$lastMonthStart' AND order_date <= '$lastMonthEnd'",
    "SELECT COUNT(*) FROM orders"
);

// Active members
$activeMembersCount = $conn->query("SELECT COUNT(*) FROM users WHERE membership_status = 'active'")->fetch_row()[0] ?? 0;
$totalMembersCount = $usersStat['current'];
$activeMembersTrend = $totalMembersCount > 0 ? round(($activeMembersCount / $totalMembersCount) * 100, 1) : 0;

// Charts Data (Last 6 months)
$months = [];
$revenueData = [];
$usersData = [];

for ($i = 5; $i >= 0; $i--) {
    $mStart = date('Y-m-01 00:00:00', strtotime("-$i month"));
    $mEnd = date('Y-m-t 23:59:59', strtotime("-$i month"));
    $monthLabel = date('M', strtotime("-$i month"));
    
    $months[] = $monthLabel;
    
    $rev = $conn->query("SELECT SUM(amount) FROM payments WHERE status = 'completed' AND created_at >= '$mStart' AND created_at <= '$mEnd'")->fetch_row()[0] ?? 0;
    $revenueData[] = $rev;
    
    $usr = $conn->query("SELECT COUNT(*) FROM users WHERE created_at >= '$mStart' AND created_at <= '$mEnd'")->fetch_row()[0] ?? 0;
    $usersData[] = $usr;
}

// Membership Data
$membershipLabels = [];
$membershipData = [];
$res = $conn->query("SELECT membership_status, COUNT(*) as count FROM users GROUP BY membership_status");
while ($row = $res->fetch_assoc()) {
    $status = $row['membership_status'] ?: 'none';
    $membershipLabels[] = ucfirst($status);
    $membershipData[] = $row['count'];
}

// Fetch recent users
$recentUsers = $conn->query("SELECT id, username, email, membership_status, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
    <div>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Dashboard <span class="text-brandGreen">Overview</span></h1>
        <p class="text-slate-500 font-medium mt-1">Real-time club statistics and activities.</p>
    </div>
    <!-- Quick Actions -->
    <div class="flex gap-3">
        <a href="users.php" class="px-5 py-3 bg-brandGreen/10 hover:bg-brandGreen text-brandGreen hover:text-white rounded-2xl font-bold text-[10px] uppercase tracking-widest transition-colors flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Add User
        </a>
        <a href="tournaments.php" class="px-5 py-3 bg-brandOrange/10 hover:bg-brandOrange text-brandOrange hover:text-white rounded-2xl font-bold text-[10px] uppercase tracking-widest transition-colors flex items-center gap-2">
            <i class="fas fa-trophy"></i> New Event
        </a>
    </div>
</div>

<!-- Advanced Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Users Card -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-bl-[100px] transition-transform group-hover:scale-110 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-users"></i>
            </div>
            <?php if($usersStat['trend'] >= 0): ?>
                <div class="px-3 py-1 bg-emerald-500/10 text-emerald-500 rounded-full text-[10px] font-black flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> <?php echo $usersStat['trend']; ?>%
                </div>
            <?php else: ?>
                <div class="px-3 py-1 bg-red-500/10 text-red-500 rounded-full text-[10px] font-black flex items-center gap-1">
                    <i class="fas fa-arrow-down"></i> <?php echo abs($usersStat['trend']); ?>%
                </div>
            <?php endif; ?>
        </div>
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1 relative z-10">Total Users</p>
        <h3 class="text-3xl font-black text-slate-900 dark:text-white relative z-10"><?php echo number_format($usersStat['current']); ?></h3>
        <p class="text-xs text-slate-400 mt-2 relative z-10"><span class="font-bold text-slate-500">+<?php echo $usersStat['this_month']; ?></span> this month</p>
    </div>

    <!-- Revenue Card -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-bl-[100px] transition-transform group-hover:scale-110 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-wallet"></i>
            </div>
            <?php if($revenueStat['trend'] >= 0): ?>
                <div class="px-3 py-1 bg-emerald-500/10 text-emerald-500 rounded-full text-[10px] font-black flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> <?php echo $revenueStat['trend']; ?>%
                </div>
            <?php else: ?>
                <div class="px-3 py-1 bg-red-500/10 text-red-500 rounded-full text-[10px] font-black flex items-center gap-1">
                    <i class="fas fa-arrow-down"></i> <?php echo abs($revenueStat['trend']); ?>%
                </div>
            <?php endif; ?>
        </div>
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1 relative z-10">Total Revenue</p>
        <h3 class="text-3xl font-black text-slate-900 dark:text-white relative z-10">KES <?php echo number_format($revenueStat['current']); ?></h3>
        <p class="text-xs text-slate-400 mt-2 relative z-10"><span class="font-bold text-slate-500">KES <?php echo number_format($revenueStat['this_month']); ?></span> this month</p>
    </div>

    <!-- Active Members Card -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-brandGold/5 rounded-bl-[100px] transition-transform group-hover:scale-110 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-brandGold/10 text-brandGold flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-star"></i>
            </div>
            <div class="px-3 py-1 bg-brandGold/10 text-brandGold rounded-full text-[10px] font-black flex items-center gap-1">
                <?php echo $activeMembersTrend; ?>%
            </div>
        </div>
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1 relative z-10">Active Plans</p>
        <h3 class="text-3xl font-black text-slate-900 dark:text-white relative z-10"><?php echo number_format($activeMembersCount); ?></h3>
        <!-- Mini Progress Bar -->
        <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-3 relative z-10 overflow-hidden">
            <div class="bg-brandGold h-full rounded-full" style="width: <?php echo $activeMembersTrend; ?>%"></div>
        </div>
    </div>

    <!-- Orders Card -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/5 rounded-bl-[100px] transition-transform group-hover:scale-110 pointer-events-none"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <?php if($ordersStat['trend'] >= 0): ?>
                <div class="px-3 py-1 bg-emerald-500/10 text-emerald-500 rounded-full text-[10px] font-black flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> <?php echo $ordersStat['trend']; ?>%
                </div>
            <?php else: ?>
                <div class="px-3 py-1 bg-red-500/10 text-red-500 rounded-full text-[10px] font-black flex items-center gap-1">
                    <i class="fas fa-arrow-down"></i> <?php echo abs($ordersStat['trend']); ?>%
                </div>
            <?php endif; ?>
        </div>
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1 relative z-10">Total Orders</p>
        <h3 class="text-3xl font-black text-slate-900 dark:text-white relative z-10"><?php echo number_format($ordersStat['current']); ?></h3>
        <p class="text-xs text-slate-400 mt-2 relative z-10"><span class="font-bold text-slate-500">+<?php echo $ordersStat['this_month']; ?></span> this month</p>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Revenue Chart -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">Revenue Overview</h3>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">Last 6 Months</span>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Membership Doughnut -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">Memberships</h3>
        </div>
        <div class="relative h-64 w-full flex items-center justify-center">
            <canvas id="membershipChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- User Growth Chart -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">User Growth</h3>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">New Accounts</span>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="userChart"></canvas>
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden flex flex-col">
        <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/20">
            <h3 class="font-black text-slate-900 dark:text-white uppercase tracking-tight">Recent Registrations</h3>
            <a href="users.php" class="text-[10px] font-bold uppercase tracking-widest text-brandGreen hover:text-brandOrange transition-colors">View All</a>
        </div>
        <div class="p-0 overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php foreach ($recentUsers as $user): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-8 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-black">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white group-hover:text-brandGreen transition-colors"><?php echo htmlspecialchars($user['username']); ?></p>
                                    <p class="text-[10px] text-slate-500 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-4">
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest <?php 
                                echo $user['membership_status'] == 'active' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'; 
                            ?>">
                                <?php echo $user['membership_status'] ?: 'None'; ?>
                            </span>
                        </td>
                        <td class="px-8 py-4 text-right">
                            <a href="users.php?edit_id=<?php echo $user['id']; ?>" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Theme colors
    const brandGreen = '#80d200';
    const brandGold = '#FFD700';
    const gridColor = document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
    const textColor = document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';

    // Set defaults
    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Inter', sans-serif";

    // 1. Revenue Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    
    // Create Gradient
    let revGradient = revCtx.createLinearGradient(0, 0, 0, 400);
    revGradient.addColorStop(0, 'rgba(128, 210, 0, 0.5)');
    revGradient.addColorStop(1, 'rgba(128, 210, 0, 0.0)');

    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Revenue (KES)',
                data: <?php echo json_encode($revenueData); ?>,
                borderColor: brandGreen,
                backgroundColor: revGradient,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: brandGreen,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });

    // 2. User Growth Chart
    const userCtx = document.getElementById('userChart').getContext('2d');
    new Chart(userCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode($usersData); ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });

    // 3. Membership Doughnut
    const memCtx = document.getElementById('membershipChart').getContext('2d');
    new Chart(memCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($membershipLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($membershipData); ?>,
                backgroundColor: [brandGreen, brandGold, '#3b82f6', '#94a3b8'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 11, weight: 'bold' }
                    }
                }
            }
        }
    });
</script>

<?php include "admin_footer.php"; ?>