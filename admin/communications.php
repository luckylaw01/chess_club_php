<?php
session_start();
require_once "../includes/db_connect.php";

// Check if user is admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['id'];
$pageTitle = "Communications Hub";
include "admin_header.php";

$message = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $conn->real_escape_string($_POST['subject']);
    $content = $conn->real_escape_string($_POST['message']);
    $type = $conn->real_escape_string($_POST['type']);
    $target = $_POST['target']; // 'all', 'active', 'inactive', or user IDs
    $channels = $_POST['channels'] ?? []; // 'app', 'email'

    if (empty($subject) || empty($content)) {
        $error = "Subject and message are required.";
    } else {
        // Prepare target user IDs
        $userIds = [];
        if ($target === 'all') {
            $result = $conn->query("SELECT id FROM users");
            while ($row = $result->fetch_assoc()) $userIds[] = $row['id'];
        } elseif ($target === 'active') {
            $result = $conn->query("SELECT id FROM users WHERE membership_status = 'active'");
            while ($row = $result->fetch_assoc()) $userIds[] = $row['id'];
        } elseif ($target === 'inactive') {
            $result = $conn->query("SELECT id FROM users WHERE membership_status != 'active'");
            while ($row = $result->fetch_assoc()) $userIds[] = $row['id'];
        } elseif (is_array($target)) {
            $userIds = array_map('intval', $target);
        }

        if (empty($userIds)) {
            $error = "No target users found.";
        } else {
            // 1. Create notification content
            $stmt = $conn->prepare("INSERT INTO notification_content (title, message, type, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $subject, $content, $type, $admin_id);
            
            if ($stmt->execute()) {
                $contentId = $stmt->insert_id;
                $stmt->close();

                // 2. Send in-app notifications
                if (in_array('app', $channels)) {
                    $values = [];
                    foreach ($userIds as $uid) {
                        $values[] = "($uid, $contentId)";
                    }
                    if (!empty($values)) {
                        $conn->query("INSERT INTO notifications (user_id, content_id) VALUES " . implode(',', $values));
                    }
                }

                // 3. Send emails
                if (in_array('email', $channels)) {
                    // Collect emails
                    $emailStmt = $conn->prepare("SELECT email FROM users WHERE id IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")");
                    $emailStmt->bind_param(str_repeat('i', count($userIds)), ...$userIds);
                    $emailStmt->execute();
                    $emailResult = $emailStmt->get_result();

                    $from = "admin@ascendingpawnchess.com";
                    $headers = "From: Ascending Pawn Chess Club <$from>\r\n";
                    $headers .= "Reply-To: $from\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    $emailBody = "
                    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                        <h2 style='color: #1a202c;'>$subject</h2>
                        <div style='color: #4a5568; line-height: 1.6;'>
                            $content
                        </div>
                        <hr style='margin: 20px 0; border: none; border-top: 1px solid #e2e8f0;'>
                        <p style='font-size: 12px; color: #718096;'>You are receiving this email as a member of Ascending Pawn Chess Club.</p>
                    </div>";

                    while ($row = $emailResult->fetch_assoc()) {
                        mail($row['email'], $subject, $emailBody, $headers);
                    }
                }

                $message = "Communication sent successfully to " . count($userIds) . " users.";
            } else {
                $error = "Failed to save message: " . $conn->error;
            }
        }
    }
}

// Fetch some users for individual selection
$users = $conn->query("SELECT id, username, email FROM users ORDER BY username ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black">Communications</h1>
            <p class="text-slate-500">Send updates, alerts, and promotions to your members.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-emerald-100 text-emerald-700 rounded-2xl border border-emerald-200">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-rose-100 text-rose-700 rounded-2xl border border-rose-200">
            <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden glass">
        <form action="communications.php" method="POST" class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Message Details -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Subject / Title</label>
                        <input type="text" name="subject" required
                            class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all"
                            placeholder="Announcing the Grand Masters Open 2026">
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Message Content</label>
                        <textarea name="message" rows="6" required
                            class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all"
                            placeholder="Write your message here... (HTML supported)"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Category</label>
                            <select name="type" class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                                <option value="system">System Notification</option>
                                <option value="announcement">Announcement</option>
                                <option value="promotion">Promotion</option>
                                <option value="alert">Alert</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Channels</label>
                            <div class="flex gap-4 items-center h-[52px]">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="channels[]" value="app" checked class="rounded text-brandGreen">
                                    <span class="text-sm">In-App</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="channels[]" value="email" class="rounded text-brandGreen">
                                    <span class="text-sm">Email</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Targeting -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Target Audience</label>
                        <select name="target" id="targetSelect" onchange="toggleUserSelection()"
                            class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                            <option value="all">All Registered Users</option>
                            <option value="active">Active Members Only</option>
                            <option value="inactive">Inactive Players</option>
                            <option value="specific">Select Specific Users</option>
                        </select>
                    </div>

                    <div id="userSelection" class="hidden">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Select Members</label>
                        <div class="max-h-[250px] overflow-y-auto p-4 border border-slate-100 dark:border-slate-800 rounded-2xl space-y-2">
                            <?php foreach ($users as $user): ?>
                                <label class="flex items-center justify-between p-2 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="target[]" value="<?php echo $user['id']; ?>" class="rounded text-brandGreen">
                                        <span class="text-sm font-bold"><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 lowercase"><?php echo htmlspecialchars($user['email']); ?></span>
                                </label>
                            <?php     endforeach; ?>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 dark:bg-slate-800/30 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                        <h4 class="text-xs font-black uppercase tracking-widest mb-2 flex items-center gap-2">
                            <i class="fas fa-info-circle text-brandGreen"></i> Delivery Info
                        </h4>
                        <ul class="text-[11px] text-slate-500 space-y-2">
                            <li>• In-app messages appear in the user's dashboard notifications tab.</li>
                            <li>• Emails will be sent from <strong>admin@ascendingpawnchess.com</strong> via Namecheap Mail.</li>
                            <li>• For bulk messages (>100 users), processing may take a few moments.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 dark:border-slate-800 pt-8">
                <button type="submit"
                    class="px-10 py-4 bg-brandGreen text-white font-black rounded-2xl shadow-lg shadow-brandGreen/30 hover:shadow-xl hover:-translate-y-1 active:translate-y-0 transition-all flex items-center gap-3">
                    <i class="fas fa-paper-plane"></i>
                    SEND COMMUNICATION
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleUserSelection() {
    const select = document.getElementById('targetSelect');
    const userDiv = document.getElementById('userSelection');
    if (select.value === 'specific') {
        userDiv.classList.remove('hidden');
    } else {
        userDiv.classList.add('hidden');
    }
}
</script>

<?php include "admin_footer.php"; ?>
