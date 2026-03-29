<?php
session_start();
include "../includes/db_connect.php";

// Simple check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

if (!isset($_GET['id'])) {
    die("Tournament ID required.");
}

$tid = (int)$_GET['id'];

// Fetch registrations
$query = "SELECT r.*, u.username, u.email as u_email 
          FROM tournament_registrations r 
          LEFT JOIN users u ON r.user_id = u.id 
          WHERE r.tournament_id = $tid 
          ORDER BY r.registration_date DESC";

$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo '<div class="p-10 text-center text-slate-500 font-bold uppercase tracking-widest text-[11px] bg-slate-50 dark:bg-slate-800/50 rounded-3xl">No registrations yet.</div>';
    exit;
}
?>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden shadow-sm">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-800/50">
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Full Name</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Email</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Phone</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Category</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php while($r = $result->fetch_assoc()): ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-slate-500"><?php echo date('M d, Y', strtotime($r['registration_date'])); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-black text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($r['full_name']); ?></span>
                        <?php if($r['user_id']): ?>
                            <div class="text-[9px] font-bold text-brandGreen uppercase mt-1">
                                Registered User: <?php echo htmlspecialchars($r['username']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-slate-500"><?php echo htmlspecialchars($r['email']); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-slate-500"><?php echo htmlspecialchars($r['phone']); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[9px] font-black uppercase tracking-widest">
                            <?php echo htmlspecialchars($r['category']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button 
                            onclick="kickUser(<?php echo $r['id']; ?>, <?php echo $tid; ?>)"
                            class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm"
                            title="Kick from Tournament">
                            <i class="fas fa-user-minus text-xs"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
