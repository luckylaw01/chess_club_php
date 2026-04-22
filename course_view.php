<?php
session_start();
require_once "includes/db_connect.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: login.php");
    exit;
}

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['id'];

// Check enrollment
$check = $conn->query("SELECT * FROM course_enrollments WHERE user_id = $user_id AND course_id = $course_id");
if (!$check || $check->num_rows === 0) {
    die("Access Denied: You are not enrolled in this course.");
}

$res = $conn->query("SELECT c.*, u.full_name as coach_name FROM academy_courses c LEFT JOIN users u ON c.coach_id = u.id WHERE c.id = $course_id");
$course = $res->fetch_assoc();

$pageTitle = $course['title'];
include 'includes/header.php';

// Fetch Topics
$topics = [];
$t_res = $conn->query("SELECT * FROM course_topics WHERE course_id = $course_id ORDER BY order_number, id");
while ($row = $t_res->fetch_assoc()) {
    $topic_id = $row['id'];
    $sub_res = $conn->query("SELECT * FROM course_subtopics WHERE topic_id = $topic_id ORDER BY order_number, id");
    $row['subtopics'] = [];
    while ($sub = $sub_res->fetch_assoc()) {
        $row['subtopics'][] = $sub;
    }
    $topics[] = $row;
}

$selected_subtopic = null;
if (isset($_GET['subtopic'])) {
    $sub_id = intval($_GET['subtopic']);
    $s_res = $conn->query("SELECT * FROM course_subtopics WHERE id = $sub_id");
    $selected_subtopic = $s_res->fetch_assoc();
} else if (!empty($topics) && !empty($topics[0]['subtopics'])) {
    $selected_subtopic = $topics[0]['subtopics'][0];
}
?>

<section class="pt-32 pb-20 px-6 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="w-full lg:w-80 space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800">
                <h3 class="font-black uppercase tracking-tight text-slate-900 dark:text-white mb-4">Course Content</h3>
                <div class="space-y-4">
                    <?php foreach ($topics as $index => $topic): ?>
                        <div>
                            <p class="text-[10px] font-black text-brandGreen uppercase tracking-widest mb-2">Topic <?php echo $index + 1; ?></p>
                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 mb-2"><?php echo htmlspecialchars($topic['title']); ?></h4>
                            <div class="space-y-1 pl-2">
                                <?php foreach ($topic['subtopics'] as $sub): ?>
                                    <a href="course_view.php?id=<?php echo $course_id; ?>&subtopic=<?php echo $sub['id']; ?>" 
                                       class="block px-3 py-2 rounded-xl text-xs font-medium transition-all <?php echo ($selected_subtopic && $selected_subtopic['id'] == $sub['id']) ? 'bg-brandGreen text-white shadow-md' : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'; ?>">
                                        <?php echo htmlspecialchars($sub['title']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="academy.php" class="flex items-center justify-center gap-2 w-full py-4 rounded-2xl bg-slate-200 dark:bg-slate-800 font-bold uppercase tracking-widest text-[10px] hover:bg-slate-300 transition-all">
                <i class="fas fa-arrow-left"></i> All Courses
            </a>
        </div>

        <!-- Content Area -->
        <div class="flex-1 space-y-8">
            <?php if ($selected_subtopic): ?>
                <div class="bg-white dark:bg-slate-900 rounded-[40px] p-8 lg:p-12 shadow-xl border border-slate-200 dark:border-slate-800">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-6"><?php echo htmlspecialchars($selected_subtopic['title']); ?></h2>
                    
                    <?php if ($selected_subtopic['video_url']): ?>
                        <div class="aspect-video rounded-[32px] overflow-hidden bg-black mb-8">
                            <iframe class="w-full h-full" src="<?php echo str_replace('watch?v=', 'embed/', $selected_subtopic['video_url']); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>

                    <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                        <?php echo nl2br(htmlspecialchars($selected_subtopic['content'])); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-slate-900 rounded-[40px] p-20 text-center shadow-xl border border-slate-200 dark:border-slate-800">
                    <div class="w-16 h-16 bg-brandGreen/10 rounded-full flex items-center justify-center text-brandGreen mx-auto mb-6">
                        <i class="fas fa-book-open text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Ready to Start?</h3>
                    <p class="text-slate-500 font-medium">Select a subtopic from the sidebar to begin your journey.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>