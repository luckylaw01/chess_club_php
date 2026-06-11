<?php
session_start();
require_once "../includes/db_connect.php";

// Security Check: Only admins allowed
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$pageTitle = "Academy Management";
include "admin_header.php";

$alert_success = "";
$alert_error = "";

// Handle Course Creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_course'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $coach_id = intval($_POST['coach_id']);
    $price = floatval($_POST['price']);
    $level = $conn->real_escape_string($_POST['level']);
    $duration = $conn->real_escape_string($_POST['duration']);

    $sql = "INSERT INTO academy_courses (title, description, coach_id, price, level, duration) VALUES ('$title', '$description', $coach_id, $price, '$level', '$duration')";
    if ($conn->query($sql)) {
        $alert_success = "Course created successfully!";
    } else {
        $alert_error = "Error creating course: " . $conn->error;
    }
}

// Handle Course Edit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_course'])) {
    $course_id = intval($_POST['course_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $coach_id = intval($_POST['coach_id']);
    $price = floatval($_POST['price']);
    $level = $conn->real_escape_string($_POST['level']);
    $duration = $conn->real_escape_string($_POST['duration']);

    $sql = "UPDATE academy_courses SET title='$title', description='$description', coach_id=$coach_id, price=$price, level='$level', duration='$duration' WHERE id=$course_id";
    if ($conn->query($sql)) {
        $alert_success = "Course updated successfully!";
    } else {
        $alert_error = "Error updating course: " . $conn->error;
    }
}

// Handle Course Deletion
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    // Cascade delete is handled by database foreign keys, but we double check
    if ($conn->query("DELETE FROM academy_courses WHERE id = $del_id")) {
        $alert_success = "Course deleted successfully.";
    } else {
        $alert_error = "Error deleting course: " . $conn->error;
    }
}

// Fetch general stats
$stats = [
    'courses' => $conn->query("SELECT COUNT(*) FROM academy_courses")->fetch_row()[0],
    'enrollments' => $conn->query("SELECT COUNT(*) FROM course_enrollments")->fetch_row()[0],
    'coaches' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'coach'")->fetch_row()[0],
    'revenue' => 0.00
];

$rev_q = $conn->query("SELECT SUM(c.price) FROM course_enrollments ce JOIN academy_courses c ON ce.course_id = c.id");
if ($rev_q) {
    $stats['revenue'] = floatval($rev_q->fetch_row()[0]);
}

// Fetch all coaches for select dropdown
$coaches = [];
$coaches_res = $conn->query("SELECT id, username, full_name FROM users WHERE role = 'coach' ORDER BY full_name, username");
if ($coaches_res) {
    while ($row = $coaches_res->fetch_assoc()) {
        $coaches[] = $row;
    }
}

// Fetch all courses
$courses = [];
$courses_res = $conn->query("SELECT c.*, u.full_name as coach_name FROM academy_courses c LEFT JOIN users u ON c.coach_id = u.id ORDER BY c.created_at DESC");
if ($courses_res) {
    while ($row = $courses_res->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<div class="mb-8 flex justify-between items-center text-slate-900 dark:text-slate-100">
    <div>
        <h3 class="text-2xl font-black uppercase tracking-tight">Academy Management Portal</h3>
        <p class="text-slate-500 font-medium">Create curriculums, assign coaches, and track school enrollments</p>
    </div>
    
    <button onclick="document.getElementById('courseModal').classList.remove('hidden')" class="px-6 py-3 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brandGreen/90 transition-all active:scale-95 shadow-lg shadow-brandGreen/20 flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>New Program</span>
    </button>
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

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 text-slate-900 dark:text-slate-100">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 text-brandGreen flex items-center justify-center text-xl">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Courses</p>
                <h3 class="text-2xl font-black"><?php echo $stats['courses']; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-xl">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Enrollments</p>
                <h3 class="text-2xl font-black"><?php echo $stats['enrollments']; ?></h3>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-xl">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Active Coaches</p>
                <h3 class="text-2xl font-black"><?php echo $stats['coaches']; ?></h3>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xl">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Academy Revenue</p>
                <h3 class="text-2xl font-black">KES <?php echo number_format($stats['revenue']); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Courses List -->
<div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden text-slate-900 dark:text-slate-100">
    <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <h4 class="font-black text-lg uppercase tracking-tight">Active Curriculums</h4>
    </div>
    
    <div class="p-0 overflow-x-auto">
        <?php if (empty($courses)): ?>
            <div class="text-center py-20">
                <p class="text-slate-500 font-medium">No courses found. Add a course to launch the academy.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-4">Course Program</th>
                        <th class="px-8 py-4">Assigned Coach</th>
                        <th class="px-8 py-4">Level</th>
                        <th class="px-8 py-4">Duration</th>
                        <th class="px-8 py-4">Price</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php foreach ($courses as $c): ?>
                    <tr class="text-sm hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-8 py-4">
                            <p class="font-bold"><?php echo htmlspecialchars($c['title']); ?></p>
                            <p class="text-xs text-slate-400 line-clamp-1 max-w-sm"><?php echo htmlspecialchars($c['description']); ?></p>
                        </td>
                        <td class="px-8 py-4 text-slate-600 dark:text-slate-300 font-semibold">
                            <?php echo htmlspecialchars($c['coach_name'] ?: 'Unassigned'); ?>
                        </td>
                        <td class="px-8 py-4">
                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                <?php echo htmlspecialchars($c['level']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-4 text-slate-500 font-medium">
                            <?php echo htmlspecialchars($c['duration']); ?>
                        </td>
                        <td class="px-8 py-4 font-mono font-bold">
                            <?php echo $c['price'] > 0 ? "KES " . number_format($c['price']) : 'FREE'; ?>
                        </td>
                        <td class="px-8 py-4 text-right space-x-2">
                            <button onclick='openEditModal(<?php echo json_encode($c); ?>)' class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center hover:bg-brandGreen hover:text-white transition-colors text-slate-400">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <a href="academy.php?delete_id=<?php echo $c['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?')" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors text-slate-400">
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

<!-- Create Course Modal -->
<div id="courseModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight">Create New Course</h3>
            <button onclick="document.getElementById('courseModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Course Title</label>
                <input type="text" name="title" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Description</label>
                <textarea name="description" rows="3" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Assigned Coach</label>
                    <select name="coach_id" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium text-slate-900 dark:text-white">
                        <?php foreach ($coaches as $coach): ?>
                            <option value="<?php echo $coach['id']; ?>"><?php echo htmlspecialchars($coach['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Level</label>
                    <select name="level" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium text-slate-900 dark:text-white">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="master">Master</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Price (KES)</label>
                    <input type="number" name="price" value="0.00" step="0.01" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Duration (e.g. 8 Weeks)</label>
                    <input type="text" name="duration" required placeholder="e.g. 12 Lessons" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                </div>
            </div>
            <button type="submit" name="create_course" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Create Course
            </button>
        </form>
    </div>
</div>

<!-- Edit Course Modal -->
<div id="editCourseModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight">Edit Course Curriculum</h3>
            <button onclick="document.getElementById('editCourseModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="course_id" id="edit-course-id">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Course Title</label>
                <input type="text" name="title" id="edit-title" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Description</label>
                <textarea name="description" id="edit-description" rows="3" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Assigned Coach</label>
                    <select name="coach_id" id="edit-coach-id" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium text-slate-900 dark:text-white">
                        <?php foreach ($coaches as $coach): ?>
                            <option value="<?php echo $coach['id']; ?>"><?php echo htmlspecialchars($coach['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Level</label>
                    <select name="level" id="edit-level" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium text-slate-900 dark:text-white">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="master">Master</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Price (KES)</label>
                    <input type="number" name="price" id="edit-price" step="0.01" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Duration (e.g. 8 Weeks)</label>
                    <input type="text" name="duration" id="edit-duration" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                </div>
            </div>
            <button type="submit" name="edit_course" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Save Changes
            </button>
        </form>
    </div>
</div>

<script>
function openEditModal(course) {
    document.getElementById('edit-course-id').value = course.id;
    document.getElementById('edit-title').value = course.title;
    document.getElementById('edit-description').value = course.description;
    document.getElementById('edit-coach-id').value = course.coach_id;
    document.getElementById('edit-level').value = course.level;
    document.getElementById('edit-price').value = course.price;
    document.getElementById('edit-duration').value = course.duration;
    document.getElementById('editCourseModal').classList.remove('hidden');
}
</script>

<?php include "admin_footer.php"; ?>