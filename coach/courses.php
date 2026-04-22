<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "My Courses";
include "coach_header.php";

$coach_id = $_SESSION["id"];

// Handle Course Creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_course'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $level = $conn->real_escape_string($_POST['level']);
    $duration = $conn->real_escape_string($_POST['duration']);

    $sql = "INSERT INTO academy_courses (title, description, coach_id, price, level, duration) VALUES ('$title', '$description', $coach_id, $price, '$level', '$duration')";
    if ($conn->query($sql)) {
        $success = "Course created successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Fetch Coach's Courses
$courses = [];
$result = $conn->query("SELECT * FROM academy_courses WHERE coach_id = $coach_id ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h3 class="text-2xl font-black uppercase tracking-tight">Course Management</h3>
        <p class="text-slate-500 font-medium">Create and manage your chess curricula</p>
    </div>
    <button onclick="document.getElementById('courseModal').classList.remove('hidden')" class="px-6 py-3 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brandGreen/90 transition-all active:scale-95 shadow-lg shadow-brandGreen/20 flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>New Course</span>
    </button>
</div>

<?php if (isset($success)): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-2xl mb-6 font-bold flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php foreach ($courses as $course): ?>
        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col group hover:shadow-xl transition-all duration-500">
            <div class="relative h-48 bg-slate-100 dark:bg-slate-800 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent z-10"></div>
                <!-- Placeholder for course image -->
                <div class="w-full h-full flex items-center justify-center text-slate-300 group-hover:scale-110 transition-transform duration-700">
                    <i class="fas fa-chess-knight text-6xl"></i>
                </div>
                <div class="absolute bottom-4 left-4 z-20">
                    <span class="px-3 py-1 bg-brandGreen text-white text-[10px] font-black uppercase tracking-widest rounded-lg">
                        <?php echo $course['level']; ?>
                    </span>
                </div>
            </div>
            
            <div class="p-6 flex-1 flex flex-col">
                <h4 class="text-xl font-black mb-2 line-clamp-1"><?php echo htmlspecialchars($course['title']); ?></h4>
                <p class="text-slate-500 text-sm font-medium line-clamp-2 mb-4 flex-1">
                    <?php echo htmlspecialchars($course['description']); ?>
                </p>
                
                <div class="flex items-center gap-4 py-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 text-slate-400 text-xs font-bold uppercase">
                        <i class="fas fa-clock text-brandGreen"></i>
                        <span><?php echo htmlspecialchars($course['duration']); ?></span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="course_details.php?id=<?php echo $course['id']; ?>" class="flex-1 text-center py-3 bg-slate-100 dark:bg-slate-800 hover:bg-brandGreen hover:text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                        Edit Course
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($courses)): ?>
        <div class="col-span-full py-20 text-center bg-white dark:bg-slate-900 rounded-[40px] border-2 border-dashed border-slate-200 dark:border-slate-800">
            <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mx-auto mb-4">
                <i class="fas fa-book-open text-2xl"></i>
            </div>
            <p class="text-slate-500 font-medium">No courses created yet. Launch your first course today!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Simple Course Modal -->
<div id="courseModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight">Create New Course</h3>
            <button onclick="document.getElementById('courseModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4 text-slate-900 dark:text-slate-100">
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
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Price (KES)</label>
                    <input type="number" name="price" value="0.00" step="0.01" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Level</label>
                    <select name="level" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="master">Master</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Duration (e.g., 4 Weeks)</label>
                <input type="text" name="duration" required placeholder="e.g. 10 Lessons" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
            </div>
            <button type="submit" name="create_course" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Create Course
            </button>
        </form>
    </div>
</div>

<?php include "coach_footer.php"; ?>
