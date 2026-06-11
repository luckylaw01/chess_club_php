<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "My Students";
include "coach_header.php";

$coach_id = $_SESSION["id"];

// Fetch coach's students
$students = [];
$sql = "SELECT DISTINCT u.id, u.username, u.full_name, u.email, u.elo_rating, u.profile_picture, c.title as course_title, ce.enrolled_at 
        FROM course_enrollments ce 
        JOIN academy_courses c ON ce.course_id = c.id 
        JOIN users u ON ce.user_id = u.id 
        WHERE c.coach_id = $coach_id 
        ORDER BY ce.enrolled_at DESC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}

// Handle Mock Performance Review / Elo Update
$alert_success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_elo'])) {
    $student_id = intval($_POST['student_id']);
    $new_elo = intval($_POST['elo_rating']);
    
    $up_stmt = $conn->prepare("UPDATE users SET elo_rating = ? WHERE id = ?");
    $up_stmt->bind_param("ii", $new_elo, $student_id);
    if ($up_stmt->execute()) {
        $alert_success = "Student Elo updated successfully!";
        // Refresh student data
        header("Location: students.php?success=1");
        exit;
    }
    $up_stmt->close();
}

if (isset($_GET['success'])) {
    $alert_success = "Student Elo updated successfully!";
}
?>

<div class="mb-8">
    <h3 class="text-2xl font-black uppercase tracking-tight text-slate-900 dark:text-white">Student Management</h3>
    <p class="text-slate-500 font-medium">Monitor active academy students, check current Elo ratings, and update scores</p>
</div>

<?php if (!empty($alert_success)): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-2xl mb-6 font-bold flex items-center gap-3 animate-slide-up">
        <i class="fas fa-check-circle"></i>
        <?php echo $alert_success; ?>
    </div>
<?php endif; ?>

<div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden text-slate-900 dark:text-slate-100">
    <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h4 class="font-black text-lg uppercase tracking-tight">Active Students (<?php echo count($students); ?>)</h4>
    </div>
    
    <div class="p-0 overflow-x-auto">
        <?php if (empty($students)): ?>
            <div class="text-center py-24">
                <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mx-auto mb-4">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
                <p class="text-slate-500 font-medium mb-2">No students enrolled yet.</p>
                <p class="text-xs text-slate-400">Share your courses on the main academy page to attract students!</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-4">Student</th>
                        <th class="px-8 py-4">Current Course</th>
                        <th class="px-8 py-4">Rating (Elo)</th>
                        <th class="px-8 py-4">Enrolled At</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php foreach ($students as $st): 
                        $name = $st['full_name'] ?: $st['username'];
                        $initial = mb_strtoupper(mb_substr(str_replace(['GM ', 'IM ', 'CM '], '', $name), 0, 1));
                    ?>
                    <tr class="text-sm hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-8 py-4 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-brandGreen/10 text-brandGreen font-black flex items-center justify-center border border-brandGreen/25 shrink-0">
                                <?php if (!empty($st['profile_picture']) && file_exists('../' . $st['profile_picture'])): ?>
                                    <img src="../<?php echo htmlspecialchars($st['profile_picture']); ?>" class="w-full h-full object-cover rounded-xl">
                                <?php else: ?>
                                    <?php echo $initial; ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-bold"><?php echo htmlspecialchars($name); ?></p>
                                <p class="text-xs text-slate-500 lowercase">@<?php echo htmlspecialchars($st['username']); ?></p>
                            </div>
                        </td>
                        <td class="px-8 py-4 text-slate-600 dark:text-slate-300 font-medium">
                            <?php echo htmlspecialchars($st['course_title']); ?>
                        </td>
                        <td class="px-8 py-4">
                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg text-xs font-bold font-mono">
                                <?php echo intval($st['elo_rating']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-4 text-slate-500 font-medium">
                            <?php echo date("M j, Y", strtotime($st['enrolled_at'])); ?>
                        </td>
                        <td class="px-8 py-4 text-right space-x-2">
                            <button onclick="openEloModal(<?php echo $st['id']; ?>, '<?php echo addslashes($name); ?>', <?php echo intval($st['elo_rating']); ?>)" class="px-4 py-2 border border-slate-200 dark:border-slate-800 hover:border-brandGreen hover:text-brandGreen text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                                Adjust Elo
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Elo Adjustment Modal -->
<div id="eloModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight">Adjust Student Elo</h3>
            <button onclick="document.getElementById('eloModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="student_id" id="modal_student_id">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Student</label>
                <p id="modal_student_name" class="font-bold text-lg"></p>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Elo Rating</label>
                <input type="number" name="elo_rating" id="modal_student_elo" required min="100" max="3500" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-mono font-bold">
            </div>
            <button type="submit" name="update_elo" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Update Rating
            </button>
        </form>
    </div>
</div>

<script>
function openEloModal(id, name, elo) {
    document.getElementById('modal_student_id').value = id;
    document.getElementById('modal_student_name').innerText = name;
    document.getElementById('modal_student_elo').value = elo;
    document.getElementById('eloModal').classList.remove('hidden');
}
</script>

<?php include "coach_footer.php"; ?>
