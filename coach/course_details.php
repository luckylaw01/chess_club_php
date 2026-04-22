<?php
session_start();
require_once "../includes/db_connect.php";

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$coach_id = $_SESSION["id"];

// Verify ownership
$check = $conn->query("SELECT * FROM academy_courses WHERE id = $course_id AND coach_id = $coach_id");
if (!$check || $check->num_rows === 0) {
    header("location: courses.php");
    exit;
}

$course = $check->fetch_assoc();
$pageTitle = "Editing: " . $course['title'];
include "coach_header.php";

// Handle Topic Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_topic'])) {
    $title = $conn->real_escape_string($_POST['topic_title']);
    $desc = $conn->real_escape_string($_POST['topic_desc']);
    $conn->query("INSERT INTO course_topics (course_id, title, description) VALUES ($course_id, '$title', '$desc')");
}

// Handle Subtopic Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subtopic'])) {
    $topic_id = intval($_POST['topic_id']);
    $title = $conn->real_escape_string($_POST['subtopic_title']);
    $content = $conn->real_escape_string($_POST['subtopic_content']);
    $video = $conn->real_escape_string($_POST['subtopic_video']);
    $conn->query("INSERT INTO course_subtopics (topic_id, title, content, video_url) VALUES ($topic_id, '$title', '$content', '$video')");
}

// Fetch Topics and Subtopics
$topics = [];
$res = $conn->query("SELECT * FROM course_topics WHERE course_id = $course_id ORDER BY order_number, id");
while ($row = $res->fetch_assoc()) {
    $topic_id = $row['id'];
    $sub_res = $conn->query("SELECT * FROM course_subtopics WHERE topic_id = $topic_id ORDER BY order_number, id");
    $row['subtopics'] = [];
    while ($sub = $sub_res->fetch_assoc()) {
        $row['subtopics'][] = $sub;
    }
    $topics[] = $row;
}
?>

<div class="mb-8 flex justify-between items-center text-slate-900 dark:text-slate-100">
    <div class="flex items-center gap-4">
        <a href="courses.php" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-brandGreen hover:text-white transition-all">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h3 class="text-2xl font-black uppercase tracking-tight"><?php echo htmlspecialchars($course['title']); ?></h3>
            <p class="text-slate-500 font-medium">Curriculum Builder</p>
        </div>
    </div>
    <button onclick="document.getElementById('topicModal').classList.remove('hidden')" class="px-6 py-3 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg shadow-brandGreen/20 transition-all flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>Add Topic</span>
    </button>
</div>

<div class="space-y-6">
    <?php foreach ($topics as $index => $topic): ?>
        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 bg-slate-50/50 dark:bg-slate-800/30 flex justify-between items-center border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brandGreen text-white flex items-center justify-center font-black">
                        <?php echo $index + 1; ?>
                    </div>
                    <div>
                        <h4 class="font-black text-lg text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($topic['title']); ?></h4>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest"><?php echo count($topic['subtopics']); ?> Subtopics</p>
                    </div>
                </div>
                <button onclick="openSubtopicModal(<?php echo $topic['id']; ?>, '<?php echo addslashes($topic['title']); ?>')" class="p-3 text-brandGreen hover:bg-brandGreen/10 rounded-xl transition-all font-black text-xs uppercase tracking-widest">
                    <i class="fas fa-plus mr-2"></i> Add Subtopic
                </button>
            </div>
            
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($topic['subtopics'] as $sub): ?>
                    <div class="p-6 flex items-center justify-between group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-brandGreen/40"></div>
                            <div>
                                <h5 class="font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($sub['title']); ?></h5>
                                <?php if ($sub['video_url']): ?>
                                    <span class="text-[10px] text-brandGreen font-black uppercase tracking-widest flex items-center gap-1 mt-1">
                                        <i class="fas fa-play-circle text-[8px]"></i> Video Included
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="opacity-0 group-hover:opacity-100 transition-all">
                            <button class="p-2 text-slate-400 hover:text-red-500"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($topic['subtopics'])): ?>
                    <div class="p-8 text-center text-slate-400 italic text-sm">
                        No subtopics added yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($topics)): ?>
        <div class="py-20 text-center bg-white dark:bg-slate-900 rounded-[40px] border-2 border-dashed border-slate-200 dark:border-slate-800">
            <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mx-auto mb-4">
                <i class="fas fa-layer-group text-2xl"></i>
            </div>
            <p class="text-slate-500 font-medium">Your curriculum is empty. Start by adding a topic!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Topic Modal -->
<div id="topicModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tight">Add New Topic</h3>
            <button onclick="document.getElementById('topicModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Topic Title</label>
                <input type="text" name="topic_title" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Topic Description (Optional)</label>
                <textarea name="topic_desc" rows="2" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium"></textarea>
            </div>
            <button type="submit" name="add_topic" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Save Topic
            </button>
        </form>
    </div>
</div>

<!-- Subtopic Modal -->
<div id="subtopicModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black uppercase tracking-tight">New Subtopic</h3>
                <p class="text-xs font-bold text-brandGreen uppercase mt-1" id="topicNameLabel"></p>
            </div>
            <button onclick="document.getElementById('subtopicModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="topic_id" id="modal_topic_id">
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Subtopic Title</label>
                <input type="text" name="subtopic_title" required class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Content / Body Text</label>
                <textarea name="subtopic_content" rows="4" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium"></textarea>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Video URL (YouTube/Vimeo)</label>
                <input type="url" name="subtopic_video" placeholder="https://..." class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl focus:ring-2 focus:ring-brandGreen/20 transition-all font-medium">
            </div>
            <button type="submit" name="add_subtopic" class="w-full py-4 bg-brandGreen text-white rounded-2xl font-black uppercase tracking-widest hover:shadow-lg hover:shadow-brandGreen/20 transition-all active:scale-95 mt-4">
                Save Subtopic
            </button>
        </form>
    </div>
</div>

<script>
function openSubtopicModal(id, name) {
    document.getElementById('modal_topic_id').value = id;
    document.getElementById('topicNameLabel').innerText = "Topic: " + name;
    document.getElementById('subtopicModal').classList.remove('hidden');
}
</script>

<?php include "coach_footer.php"; ?>