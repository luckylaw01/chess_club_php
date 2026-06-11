<?php
session_start();
require_once "includes/db_connect.php";
$pageTitle = "Explore Levels - Chess Academy";
include 'includes/header.php';

// Handle Enrollment Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enroll_course'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("location: login.php");
        exit;
    }
    $c_id = intval($_POST['course_id']);
    $u_id = $_SESSION['id'];
    
    // Check if already enrolled
    $check = $conn->query("SELECT id FROM course_enrollments WHERE user_id = $u_id AND course_id = $c_id");
    if ($check->num_rows === 0) {
        $conn->query("INSERT INTO course_enrollments (user_id, course_id) VALUES ($u_id, $c_id)");
        echo "<script>alert('Enrollment successful!'); window.location.href='course_view.php?id=" . $c_id . "';</script>";
    } else {
        header("location: course_view.php?id=" . $c_id);
        exit;
    }
}

// Fetch user enrolled courses if logged in
$enrolled_courses = [];
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $user_id = $_SESSION['id'];
    $en_res = $conn->query("SELECT course_id FROM course_enrollments WHERE user_id = $user_id");
    while ($en_row = $en_res->fetch_assoc()) {
        $enrolled_courses[] = $en_row['course_id'];
    }
}

// Fetch all courses grouped by level
$levels_data = [
    'beginner' => [
        'name' => 'Foundations (Level 1)',
        'badge' => 'Beginner',
        'color' => 'brandGreen',
        'icon' => 'fa-chess-pawn',
        'desc' => 'Designed for absolute beginners. Learn board coordinates, piece movement, basic checkmate patterns, and core rules like castling and en passant.',
        'courses' => []
    ],
    'intermediate' => [
        'name' => 'Tactics & Strategy (Level 2)',
        'badge' => 'Intermediate',
        'color' => 'brandGold',
        'icon' => 'fa-chess-knight',
        'desc' => 'Advance your understanding with basic tactical motifs (forks, pins, skewers), fundamental opening concepts, and basic king & pawn endgames.',
        'courses' => []
    ],
    'advanced' => [
        'name' => 'Positional Mastery (Level 3)',
        'badge' => 'Advanced',
        'color' => 'brandOrange',
        'icon' => 'fa-chess-rook',
        'desc' => 'Go deeper into long-term strategic plans, pawn structures, positional weaknesses, active piece placement, and complex minor piece endgames.',
        'courses' => []
    ],
    'master' => [
        'name' => 'Competitive Elite (Level 4)',
        'badge' => 'Master / Elite',
        'color' => 'purple-500',
        'icon' => 'fa-chess-king',
        'desc' => 'Prepare for tournaments. Deep-dive into opening prep with engines, theoretical endgames, psychological preparation, and clock management strategies.',
        'courses' => []
    ]
];

$res = $conn->query("SELECT c.*, u.full_name as coach_name FROM academy_courses c LEFT JOIN users u ON c.coach_id = u.id ORDER BY c.level, c.created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $lvl = $row['level'];
        if (isset($levels_data[$lvl])) {
            $levels_data[$lvl]['courses'][] = $row;
        }
    }
}
?>

    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
        <div class="max-w-7xl mx-auto">
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen dark:text-brandGreen text-xs font-bold uppercase tracking-widest mb-6">
                <i class="fas fa-graduation-cap text-[10px]"></i> Study Roadmap
            </div>
            <h1 class="text-5xl lg:text-7xl font-extrabold mb-8 tracking-tight text-slate-900 dark:text-white">
                Coaching <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandGreen to-brandOrange">Levels</span>
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed mb-16">
                Our structured training is divided into four comprehensive coaching levels, ensuring every player moves at their own optimal pace toward chess mastery.
            </p>

            <!-- Levels Navigation & Roadmap -->
            <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-8 mb-24">
                <?php foreach ($levels_data as $key => $lvl): ?>
                <a href="#<?php echo $key; ?>"
                    class="group p-8 rounded-[32px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-brandGreen transition-all shadow-lg hover:shadow-brandGreen/5 flex flex-col justify-between cursor-pointer">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-<?php echo $lvl['color']; ?>/10 flex items-center justify-center text-<?php echo $lvl['color']; ?> text-xl mb-6">
                            <i class="fas <?php echo $lvl['icon']; ?>"></i>
                        </div>
                        <h3 class="text-lg font-black uppercase tracking-tight text-slate-900 dark:text-white mb-2"><?php echo htmlspecialchars($lvl['name']); ?></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-6">
                            <?php echo htmlspecialchars($lvl['desc']); ?>
                        </p>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-brandGreen flex items-center gap-2">
                        View Curriculum <i class="fas fa-arrow-down text-[8px] animate-bounce"></i>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Detailed Curriculums -->
            <div class="space-y-32">
                <?php foreach ($levels_data as $key => $lvl): ?>
                <div id="<?php echo $key; ?>" class="scroll-mt-32">
                    <div class="flex flex-col lg:flex-row gap-12 items-start border-t border-slate-200 dark:border-slate-800 pt-16">
                        <!-- Left Side info -->
                        <div class="lg:w-1/3 space-y-6">
                            <div class="inline-block px-4 py-1.5 rounded-full bg-<?php echo $lvl['color']; ?>/10 text-<?php echo $lvl['color']; ?> text-xs font-bold uppercase tracking-widest">
                                <?php echo htmlspecialchars($lvl['badge']); ?>
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white uppercase tracking-tight">
                                <?php echo htmlspecialchars($lvl['name']); ?>
                            </h2>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed text-sm">
                                <?php echo htmlspecialchars($lvl['desc']); ?>
                            </p>
                            
                            <!-- Static Syllabus Bullet points for visuals -->
                            <div class="p-6 bg-slate-100 dark:bg-slate-900/40 rounded-3xl border border-slate-200/50 dark:border-slate-800/50 space-y-4">
                                <h4 class="font-bold text-xs uppercase tracking-widest text-slate-400">Core Objectives</h4>
                                <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                                    <li class="flex items-center gap-2"><i class="fas fa-check text-brandGreen"></i> Structured calculation</li>
                                    <li class="flex items-center gap-2"><i class="fas fa-check text-brandGreen"></i> Practical game applications</li>
                                    <li class="flex items-center gap-2"><i class="fas fa-check text-brandGreen"></i> Regular rating tournament reviews</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Right Side courses & topics -->
                        <div class="lg:w-2/3 w-full space-y-8">
                            <h3 class="text-xl font-bold uppercase tracking-tight text-slate-400">Available Courses</h3>
                            
                            <?php if (empty($lvl['courses'])): ?>
                                <div class="p-10 rounded-[32px] border border-dashed border-slate-200 dark:border-slate-800 text-center">
                                    <p class="text-slate-500 text-sm font-bold uppercase tracking-wider">New programs under construction for this level!</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-8">
                                    <?php foreach ($lvl['courses'] as $course): ?>
                                    <div class="p-8 md:p-10 bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-[40px] shadow-xl relative overflow-hidden glass hover:border-brandGreen/40 transition-all">
                                        <div class="flex flex-col md:flex-row justify-between gap-6 items-start md:items-center mb-6">
                                            <div>
                                                <h4 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight"><?php echo htmlspecialchars($course['title']); ?></h4>
                                                <p class="text-xs text-brandGreen font-bold uppercase tracking-widest mt-1">Instructor: <?php echo htmlspecialchars($course['coach_name']); ?></p>
                                            </div>
                                            <div class="text-right">
                                                <span class="block text-2xl font-black text-slate-900 dark:text-white"><?php echo $course['price'] > 0 ? "KES " . number_format($course['price']) : "FREE"; ?></span>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest"><?php echo htmlspecialchars($course['duration']); ?> Course</span>
                                            </div>
                                        </div>

                                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8">
                                            <?php echo htmlspecialchars($course['description']); ?>
                                        </p>

                                        <!-- Syllabus Topics inside this Course -->
                                        <div class="mb-8">
                                            <h5 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">What you will study:</h5>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <?php
                                                // Fetch syllabus topics dynamically
                                                $c_id = $course['id'];
                                                $topics_res = $conn->query("SELECT * FROM course_topics WHERE course_id = $c_id ORDER BY order_number ASC");
                                                if ($topics_res && $topics_res->num_rows > 0):
                                                    while ($topic = $topics_res->fetch_assoc()):
                                                ?>
                                                        <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800/80">
                                                            <div class="w-6 h-6 rounded-lg bg-brandGreen/10 flex items-center justify-center shrink-0 text-[10px] text-brandGreen font-bold mt-0.5">
                                                                <?php echo $topic['order_number']; ?>
                                                            </div>
                                                            <div>
                                                                <h6 class="text-xs font-black uppercase tracking-tight text-slate-950 dark:text-slate-200"><?php echo htmlspecialchars($topic['title']); ?></h6>
                                                                <p class="text-[10px] text-slate-500 leading-normal mt-0.5"><?php echo htmlspecialchars($topic['description']); ?></p>
                                                            </div>
                                                        </div>
                                                <?php
                                                    endwhile;
                                                else:
                                                ?>
                                                    <p class="text-xs text-slate-500 italic">Detailed syllabus coming soon.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Enrollment Buttons -->
                                        <div class="flex flex-wrap gap-4 items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800">
                                            <span class="text-xs text-slate-400 font-bold uppercase">Dynamic Progress Tracking Included</span>
                                            <?php if (in_array($course['id'], $enrolled_courses)): ?>
                                                <a href="course_view.php?id=<?php echo $course['id']; ?>"
                                                    class="px-8 py-3.5 bg-brandGreen text-white font-bold rounded-2xl text-[11px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-lg shadow-brandGreen/20">
                                                    Continue Learning
                                                </a>
                                            <?php else: ?>
                                                <form method="POST">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <button type="submit" name="enroll_course"
                                                        class="px-8 py-3.5 border-2 border-slate-900 dark:border-white text-slate-900 dark:text-white hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 font-bold rounded-2xl text-[11px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all">
                                                        Enroll Program
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
