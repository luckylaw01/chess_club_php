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

<div class="space-y-6">
    <?php while($r = $result->fetch_assoc()): ?>
        <?php
            $participantsStmt = $conn->prepare("SELECT * FROM tournament_registration_participants WHERE registration_id = ? ORDER BY is_primary DESC, id ASC");
            $participants = [];
            if ($participantsStmt) {
                $participantsStmt->bind_param('i', $r['id']);
                $participantsStmt->execute();
                $participantsResult = $participantsStmt->get_result();
                while ($participantsResult && ($participantRow = $participantsResult->fetch_assoc())) {
                    $participants[] = $participantRow;
                }
                $participantsStmt->close();
            }
        ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[32px] overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-slate-100 dark:bg-slate-800 text-slate-500"><?php echo htmlspecialchars($r['registration_type']); ?></span>
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-brandGreen/10 text-brandGreen">Participants: <?php echo (int)$r['participant_count']; ?></span>
                        <?php if (!empty($r['team_name'])): ?>
                            <span class="text-sm font-black text-slate-900 dark:text-white uppercase"><?php echo htmlspecialchars($r['team_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs font-bold text-slate-500"><?php echo date('M d, Y', strtotime($r['registration_date'])); ?></p>
                    <?php if($r['user_id']): ?>
                        <p class="text-[9px] font-bold text-brandGreen uppercase mt-1">Registered User: <?php echo htmlspecialchars($r['username']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <?php if (!empty($r['document_path'])): ?>
                        <a href="../<?php echo htmlspecialchars($r['document_path']); ?>" target="_blank" class="px-4 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-black uppercase text-[10px] tracking-widest">View Document</a>
                    <?php endif; ?>
                    <button onclick="kickUser(<?php echo $r['id']; ?>, <?php echo $tid; ?>)" class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center gap-2 hover:bg-red-500 hover:text-white transition-all shadow-sm font-black uppercase text-[10px] tracking-widest" title="Remove registration">
                        <i class="fas fa-trash-alt text-xs"></i>
                        Delete Registration
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Name</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Email</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Phone</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">DOB</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Gender</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Club Type</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Club Name</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Category</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Guardian</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($participants as $participant): ?>
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-4 py-4">
                                    <input type="text" value="<?php echo htmlspecialchars($participant['full_name']); ?>" class="participant-field w-full bg-transparent border-none font-black text-slate-900 dark:text-white uppercase" data-field="full_name" data-participant-id="<?php echo $participant['id']; ?>">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="email" value="<?php echo htmlspecialchars($participant['email']); ?>" class="participant-field w-full bg-transparent border-none text-slate-500" data-field="email" data-participant-id="<?php echo $participant['id']; ?>">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" value="<?php echo htmlspecialchars($participant['phone']); ?>" class="participant-field w-full bg-transparent border-none text-slate-500" data-field="phone" data-participant-id="<?php echo $participant['id']; ?>">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="date" value="<?php echo htmlspecialchars($participant['date_of_birth'] ?? ''); ?>" class="participant-field w-full bg-transparent border-none text-slate-500" data-field="date_of_birth" data-participant-id="<?php echo $participant['id']; ?>">
                                </td>
                                <td class="px-4 py-4">
                                    <select class="participant-field w-full bg-transparent border-none text-slate-500" data-field="gender" data-participant-id="<?php echo $participant['id']; ?>">
                                        <option value="">Choose</option>
                                        <option value="male" <?php echo ($participant['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo ($participant['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo ($participant['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <select class="participant-field w-full bg-transparent border-none text-slate-500" data-field="club_type" data-participant-id="<?php echo $participant['id']; ?>">
                                        <option value="chess" <?php echo (($participant['club_type'] ?? 'chess') === 'chess') ? 'selected' : ''; ?>>Chess Club</option>
                                        <option value="school" <?php echo (($participant['club_type'] ?? '') === 'school') ? 'selected' : ''; ?>>School Club</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" value="<?php echo htmlspecialchars($participant['club_name'] ?? ''); ?>" class="participant-field w-full bg-transparent border-none text-slate-500" data-field="club_name" data-participant-id="<?php echo $participant['id']; ?>">
                                </td>
                                <td class="px-4 py-4">
                                    <select class="participant-field w-full bg-transparent border-none text-slate-500" data-field="category" data-participant-id="<?php echo $participant['id']; ?>">
                                        <option value="Under 7" <?php echo (($participant['category'] ?? '') === 'Under 7') ? 'selected' : ''; ?>>Under 7</option>
                                        <option value="Under 9" <?php echo (($participant['category'] ?? '') === 'Under 9') ? 'selected' : ''; ?>>Under 9</option>
                                        <option value="Under 11" <?php echo (($participant['category'] ?? '') === 'Under 11') ? 'selected' : ''; ?>>Under 11</option>
                                        <option value="Under 13" <?php echo (($participant['category'] ?? '') === 'Under 13') ? 'selected' : ''; ?>>Under 13</option>
                                        <option value="Under 15" <?php echo (($participant['category'] ?? '') === 'Under 15') ? 'selected' : ''; ?>>Under 15</option>
                                        <option value="Under 17" <?php echo (($participant['category'] ?? '') === 'Under 17') ? 'selected' : ''; ?>>Under 17</option>
                                        <option value="Open" <?php echo (($participant['category'] ?? '') === 'Open') ? 'selected' : ''; ?>>Open</option>
                                        <option value="Blitz" <?php echo (($participant['category'] ?? '') === 'Blitz') ? 'selected' : ''; ?>>Blitz</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" value="<?php echo htmlspecialchars($participant['guardian_phone'] ?? ''); ?>" class="participant-field w-full bg-transparent border-none text-slate-500" data-field="guardian_phone" data-participant-id="<?php echo $participant['id']; ?>">
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex gap-2">
                                        <button type="button" onclick="updateParticipant(<?php echo $participant['id']; ?>)" class="px-3 py-2 rounded-xl bg-brandGreen/10 text-brandGreen font-black uppercase text-[10px] tracking-widest">Save</button>
                                        <button type="button" onclick="deleteParticipant(<?php echo $participant['id']; ?>, <?php echo $tid; ?>)" class="px-3 py-2 rounded-xl bg-red-50 text-red-500 font-black uppercase text-[10px] tracking-widest">Remove</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endwhile; ?>
</div>
