<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Course Assignments";
include "coach_header.php";

$coach_id = $_SESSION["id"];
$alert_success = "";
$alert_error = "";

// Handle New Assignment Creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_assignment'])) {
    $course_id = intval($_POST['course_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $max_points = intval($_POST['max_points']);
    $due_date = !empty($_POST['due_date']) ? $conn->real_escape_string($_POST['due_date']) : null;
    
    // Check if coach owns the course
    $verify = $conn->query("SELECT id FROM academy_courses WHERE id = $course_id AND coach_id = $coach_id");
    if ($verify && $verify->num_rows > 0) {
        $due_val = $due_date ? "'$due_date'" : "NULL";
        $sql = "INSERT INTO assignments (course_id, title, description, max_points, due_date) VALUES ($course_id, '$title', '$description', $max_points, $due_val)";
        if ($conn->query($sql)) {
            $alert_success = "Assignment created successfully!";
            
            // Auto-assign to all currently enrolled students
            $new_assign_id = $conn->insert_id;
            $students_res = $conn->query("SELECT user_id FROM course_enrollments WHERE course_id = $course_id AND status = 'active'");
            if ($students_res) {
                while ($st = $students_res->fetch_assoc()) {
                    $u_id = $st['user_id'];
                    $conn->query("INSERT INTO student_assignments (assignment_id, user_id, status) VALUES ($new_assign_id, $u_id, 'assigned')");
                }
            }
        } else {
            $alert_error = "Error: " . $conn->error;
        }
    } else {
        $alert_error = "Invalid course selection.";
    }
}

// Handle Grading Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['grade_submission'])) {
    $sub_id = intval($_POST['submission_id']);
    $grade = intval($_POST['grade']);
    $feedback = $conn->real_escape_string($_POST['feedback']);
    
    // Verify coach owns the assignment being graded
    $verify_sub = $conn->query("SELECT sa.id FROM student_assignments sa JOIN assignments a ON sa.assignment_id = a.id JOIN academy_courses c ON a.course_id = c.id WHERE sa.id = $sub_id AND c.coach_id = $coach_id");
    if ($verify_sub && $verify_sub->num_rows > 0) {
        $sql = "UPDATE student_assignments SET grade = $grade, feedback = '$feedback', status = 'graded', graded_at = CURRENT_TIMESTAMP WHERE id = $sub_id";
        if ($conn->query($sql)) {
            $alert_success = "Submission graded successfully!";
        } else {
            $alert_error = "Error grading submission: " . $conn->error;
        }
    } else {
        $alert_error = "Access denied.";
    }
}

// Handle Delete Assignment
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    // Verify ownership
    $verify_del = $conn->query("SELECT a.id FROM assignments a JOIN academy_courses c ON a.course_id = c.id WHERE a.id = $del_id AND c.coach_id = $coach_id");
    if ($verify_del && $verify_del->num_rows > 0) {
        $conn->query("DELETE FROM assignments WHERE id = $del_id");
        $alert_success = "Assignment deleted successfully.";
    }
}

// Fetch Coach's Courses
$courses = [];
$courses_res = $conn->query("SELECT id, title FROM academy_courses WHERE coach_id = $coach_id");
if ($courses_res) {
    while ($row = $courses_res->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Fetch Created Assignments
$assignments = [];
$assign_res = $conn->query("SELECT a.*, c.title as course_title, 
                           (SELECT COUNT(*) FROM student_assignments WHERE assignment_id = a.id) as total_assigned,
                           (SELECT COUNT(*) FROM student_assignments WHERE assignment_id = a.id AND status = 'submitted') as total_submitted
                           FROM assignments a 
                           JOIN academy_courses c ON a.course_id = c.id 
                           WHERE c.coach_id = $coach_id 
                           ORDER BY a.created_at DESC");
if ($assign_res) {
    while ($row = $assign_res->fetch_assoc()) {
        $assignments[] = $row;
    }
}

// Fetch Submissions for Grading
$submissions = [];
$sub_query = "SELECT sa.*, u.username, u.full_name, a.title as assignment_title, a.max_points, c.title as course_title 
              FROM student_assignments sa 
              JOIN assignments a ON sa.assignment_id = a.id 
              JOIN academy_courses c ON a.course_id = c.id 
              JOIN users u ON sa.user_id = u.id 
              WHERE c.coach_id = $coach_id AND sa.status = 'submitted' 
              ORDER BY sa.submitted_at ASC";
$sub_res = $conn->query($sub_query);
if ($sub_res) {
    while ($row = $sub_res->fetch_assoc()) {
        $submissions[] = $row;
    }
}
?>

<div class="mb-8 flex justify-between items-center text-slate-900 dark:text-slate-100">
    <div>
        <h3 class="text-2xl font-black uppercase tracking-tight">Assignments Management</h3>
        <p class="text-slate-500 font-medium">Issue tasks, review submissions, and award points to students</p>
    </div>
    
    <div class="flex gap-4">
        <button onclick="document.getElementById('assignModal').classList.remove('hidden')" class="px-6 py-3 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brandGreen/90 transition-all active:scale-95 shadow-lg shadow-brandGreen/20 flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Create Assignment</span>
        </button>
    </div>
</div>

<?php if (!empty($alert_success)): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-2xl mb-6 font-bold flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <?php echo $alert_success; ?>
    </div>
<?php endif; ?>

<?php if (!empty($alert_error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-2xl mb-6 font-bold flex items-center gap-3">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo $alert_error; ?>
    </div>
<?php endif; ?>

<!-- Tabs -->
<div class="flex gap-4 mb-8 border-b border-slate-200 dark:border-slate-800 pb-4">
    <button onclick="showTab('list')" id="tab-btn-list" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest bg-brandGreen text-white transition-all shadow">
        Assignment List
    </button>
    <button onclick="showTab('review')" id="tab-btn-review" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
        Submissions to Review (<?php echo count($submissions); ?>)
    </button>
</div>

<!-- Tab: List of Assignments -->
<div id="tab-list" class="space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden text-slate-900 dark:text-slate-100">
        <div class="p-0 overflow-x-auto">
            <?php if (empty($assignments)): ?>
                <div class="text-center py-20">
                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mx-auto mb-4">
                        <i class="fas fa-edit text-2xl"></i>
                    </div>
                    <p class="text-slate-500 font-medium">No assignments created yet.</p>
                </div>
            <?php else: ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                            <th class="px-8 py-4">Assignment</th>
                            <th class="px-8 py-4">Course</th>
                            <th class="px-8 py-4">Points</th>
                            <th class="px-8 py-4">Due Date</th>
                            <th class="px-8 py-4">Submissions</th>
                            <th class="px-8 py-4 text-right font-bold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($assignments as $a): ?>
                        <tr class="text-sm hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-8 py-4">
                                <p class="font-bold"><?php echo htmlspecialchars($a['title']); ?></p>
                                <p class="text-xs text-slate-400 line-clamp-1 max-w-xs"><?php echo htmlspecialchars($a['description']); ?></p>
                            </td>
                            <td class="px-8 py-4 text-slate-600 dark:text-slate-300 font-medium">
                                <?php echo htmlspecialchars($a['course_title']); ?>
                            </td>
                            <td class="px-8 py-4 font-mono font-bold">
                                <?php echo $a['max_points']; ?>
                            </td>
                            <td class="px-8 py-4 text-slate-500 font-medium">
                                <?php echo $a['due_date'] ? date("M j, Y", strtotime($a['due_date'])) : 'No deadline'; ?>
                            </td>
                            <td class="px-8 py-4">
                                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-[10px] font-black uppercase rounded-lg">
                                    <?php echo $a['total_submitted']; ?> / <?php echo $a['total_assigned']; ?>
                                </span>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <a href="assignments.php?delete_id=<?php echo $a['id']; ?>" onclick="return confirm('Are you sure you want to delete this assignment?')" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center hover:bg-red-500 hover:text-white text-slate-400 transition-colors">
                                    <i class="fas fa-trash text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tab: Submissions to Review -->
<div id="tab-review" class="hidden space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden text-slate-900 dark:text-slate-100">
        <div class="p-0 overflow-x-auto">
            <?php if (empty($submissions)): ?>
                <div class="text-center py-20">
                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mx-auto mb-4">
                        <i class="fas fa-clipboard-check text-2xl"></i>
                    </div>
                    <p class="text-slate-500 font-medium">No pending submissions to grade.</p>
                </div>
            <?php else: ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                            <th class="px-8 py-4">Student</th>
                            <th class="px-8 py-4">Assignment</th>
                            <th class="px-8 py-4">Submission Text</th>
                            <th class="px-8 py-4">Submitted Date</th>
                            <th class="px-8 py-4 text-right font-bold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($submissions as $sub): 
                            $st_name = $sub['full_name'] ?: $sub['username'];
                        ?>
                        <tr class="text-sm hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-8 py-4">
                                <p class="font-bold"><?php echo htmlspecialchars($st_name); ?></p>
                                <p class="text-xs text-slate-500">@<?php echo htmlspecialchars($sub['username']); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <p class="font-bold"><?php echo htmlspecialchars($sub['assignment_title']); ?></p>
                                <p class="text-xs text-slate-400"><?php echo htmlspecialchars($sub['course_title']); ?></p>
                            </td>
                            <td class="px-8 py-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                <?php echo htmlspecialchars($sub['submission_text'] ?: 'Submitted without text'); ?>
                            </td>
                            <td class="px-8 py-4 text-slate-500 font-medium">
                                <?php echo date("M j, Y, g:i A", strtotime($sub['submitted_at'])); ?>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <button onclick='openGradeModal(<?php echo $sub['id']; ?>, "<?php echo addslashes($st_name); ?>", "<?php echo addslashes($sub['assignment_title']); ?>", "<?php echo addslashes($sub['submission_text']); ?>", <?php echo $sub['max_points']; ?>)' class="px-4 py-2 bg-brandGreen text-white text-xs font-black uppercase tracking-widest rounded-xl hover:scale-105 active:scale-95 transition-all shadow-md shadow-brandGreen/20">
                                    Grade Task
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Assignment Modal -->
<div id="assignModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight">Create Assignment</h3>
            <button onclick="document.getElementById('assignModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Select Course</label>
                <select name="course_id" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium text-slate-900 dark:text-white">
                    <?php foreach ($courses as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Assignment Title</label>
                <input type="text" name="title" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Description / Instructions</label>
                <textarea name="description" rows="3" required placeholder="Describe task details, e.g. Solve the puzzle or submit analysis..." class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Max Points</label>
                    <input type="number" name="max_points" value="100" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Due Date (Optional)</label>
                    <input type="date" name="due_date" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                </div>
            </div>
            <button type="submit" name="create_assignment" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Create & Assign
            </button>
        </form>
    </div>
</div>

<!-- Grade Modal -->
<div id="gradeModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black uppercase tracking-tight">Grade Student Submission</h3>
                <p id="grade-student-title" class="text-xs font-bold text-brandGreen uppercase mt-1"></p>
            </div>
            <button onclick="document.getElementById('gradeModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="submission_id" id="grade-sub-id">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Submission Content</label>
                <div id="grade-sub-text" class="p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/50 text-sm max-h-48 overflow-y-auto whitespace-pre-wrap"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Score Awarded</label>
                    <input type="number" name="grade" id="grade-score-input" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-bold">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Max Points Available</label>
                    <input type="text" id="grade-max-points" disabled class="w-full px-5 py-4 bg-slate-100 dark:bg-slate-800/80 border-0 rounded-2xl font-bold text-slate-400 cursor-not-allowed">
                </div>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Feedback Notes</label>
                <textarea name="feedback" rows="3" required placeholder="Write helpful feedback or correction guides for student..." class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium"></textarea>
            </div>
            <button type="submit" name="grade_submission" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Submit Review
            </button>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    if (tabName === 'list') {
        document.getElementById('tab-list').classList.remove('hidden');
        document.getElementById('tab-review').classList.add('hidden');
        
        document.getElementById('tab-btn-list').classList.add('bg-brandGreen', 'text-white', 'shadow');
        document.getElementById('tab-btn-list').classList.remove('text-slate-500', 'dark:text-slate-400');
        
        document.getElementById('tab-btn-review').classList.remove('bg-brandGreen', 'text-white', 'shadow');
        document.getElementById('tab-btn-review').classList.add('text-slate-500', 'dark:text-slate-400');
    } else {
        document.getElementById('tab-list').classList.add('hidden');
        document.getElementById('tab-review').classList.remove('hidden');
        
        document.getElementById('tab-btn-review').classList.add('bg-brandGreen', 'text-white', 'shadow');
        document.getElementById('tab-btn-review').classList.remove('text-slate-500', 'dark:text-slate-400');
        
        document.getElementById('tab-btn-list').classList.remove('bg-brandGreen', 'text-white', 'shadow');
        document.getElementById('tab-btn-list').classList.add('text-slate-500', 'dark:text-slate-400');
    }
}

function openGradeModal(subId, studentName, assignmentTitle, subText, maxPoints) {
    document.getElementById('grade-sub-id').value = subId;
    document.getElementById('grade-student-title').innerText = studentName + " - " + assignmentTitle;
    document.getElementById('grade-sub-text').innerText = subText || "Submitted without text content.";
    document.getElementById('grade-score-input').value = maxPoints;
    document.getElementById('grade-score-input').max = maxPoints;
    document.getElementById('grade-max-points').value = maxPoints;
    document.getElementById('gradeModal').classList.remove('hidden');
}
</script>

<?php include "coach_footer.php"; ?>
