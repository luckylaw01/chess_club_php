<?php
session_start();
require_once "../includes/db_connect.php";

$pageTitle = "Coach Dashboard";
include "coach_header.php";

$coach_id = $_SESSION["id"];

// Fetch dynamic statistics for the coach
$stats = [
    'students' => 0,
    'assignments' => 0,
    'courses' => 0,
    'pending_reviews' => 0,
];

// 1. Total Students
$q_students = $conn->query("SELECT COUNT(DISTINCT ce.user_id) FROM course_enrollments ce JOIN academy_courses c ON ce.course_id = c.id WHERE c.coach_id = $coach_id");
if ($q_students) {
    $stats['students'] = $q_students->fetch_row()[0];
}

// 2. Active Assignments
$q_assignments = $conn->query("SELECT COUNT(*) FROM assignments a JOIN academy_courses c ON a.course_id = c.id WHERE c.coach_id = $coach_id");
if ($q_assignments) {
    $stats['assignments'] = $q_assignments->fetch_row()[0];
}

// 3. My Courses
$q_courses = $conn->query("SELECT COUNT(*) FROM academy_courses WHERE coach_id = $coach_id");
if ($q_courses) {
    $stats['courses'] = $q_courses->fetch_row()[0];
}

// 4. Pending Reviews
$q_reviews = $conn->query("SELECT COUNT(*) FROM student_assignments sa JOIN assignments a ON sa.assignment_id = a.id JOIN academy_courses c ON a.course_id = c.id WHERE c.coach_id = $coach_id AND sa.status = 'submitted'");
if ($q_reviews) {
    $stats['pending_reviews'] = $q_reviews->fetch_row()[0];
}

// Fetch recent student enrollments in coach's courses
$recent_enrollments = [];
$en_res = $conn->query("SELECT u.username, u.full_name, c.title as course_title, ce.enrolled_at 
                       FROM course_enrollments ce 
                       JOIN academy_courses c ON ce.course_id = c.id 
                       JOIN users u ON ce.user_id = u.id 
                       WHERE c.coach_id = $coach_id 
                       ORDER BY ce.enrolled_at DESC LIMIT 5");
if ($en_res) {
    while ($row = $en_res->fetch_assoc()) {
        $recent_enrollments[] = $row;
    }
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 text-slate-900 dark:text-slate-100">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 text-brandGreen flex items-center justify-center text-xl">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Students</p>
                <h3 class="text-2xl font-black"><?php echo $stats['students']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Enrolled in your academy programs</p>
    </div>
    
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center text-xl">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Active Assignments</p>
                <h3 class="text-2xl font-black"><?php echo $stats['assignments']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Lessons assigned to curriculum</p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-xl">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">My Courses</p>
                <h3 class="text-2xl font-black"><?php echo $stats['courses']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Programs currently hosted by you</p>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-xl">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Pending Reviews</p>
                <h3 class="text-2xl font-black"><?php echo $stats['pending_reviews']; ?></h3>
            </div>
        </div>
        <p class="text-xs text-slate-400">Submissions awaiting grading</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden text-slate-900 dark:text-slate-100">
    <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-black uppercase tracking-tight">Recent Enrollments</h3>
            <p class="text-slate-400 text-sm font-medium">Keep track of your newly registered students</p>
        </div>
        <a href="students.php" class="text-xs font-black uppercase tracking-widest text-brandGreen hover:underline">View All Students</a>
    </div>
    
    <div class="p-0 overflow-x-auto">
        <?php if (empty($recent_enrollments)): ?>
            <div class="text-center py-16">
                <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mx-auto mb-4">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
                <p class="text-slate-500 font-medium">No recent student registrations found in your courses.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <th class="px-8 py-4">Student</th>
                        <th class="px-8 py-4">Enrolled Program</th>
                        <th class="px-8 py-4">Joined Date</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php foreach ($recent_enrollments as $en): ?>
                    <tr class="text-sm hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-8 py-4">
                            <p class="font-bold"><?php echo htmlspecialchars($en['full_name'] ?: $en['username']); ?></p>
                            <p class="text-xs text-slate-500 lowercase">@<?php echo htmlspecialchars($en['username']); ?></p>
                        </td>
                        <td class="px-8 py-4 text-slate-600 dark:text-slate-300 font-medium">
                            <?php echo htmlspecialchars($en['course_title']); ?>
                        </td>
                        <td class="px-8 py-4 text-slate-500 font-medium">
                            <?php echo date("M j, Y, g:i A", strtotime($en['enrolled_at'])); ?>
                        </td>
                        <td class="px-8 py-4 text-right">
                            <a href="students.php" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-brandGreen hover:text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                                Profile
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include "coach_footer.php"; ?>
