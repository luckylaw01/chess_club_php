<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/email_config.php";

// Check if user is admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle AJAX Live Search for specific members
if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $search = $conn->real_escape_string(trim($_GET['ajax_search']));
    if (strlen($search) > 0) {
        $query = "SELECT id, username, email FROM users WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' ORDER BY username ASC LIMIT 20";
        $res = $conn->query($query);
        $found = [];
        while ($r = $res->fetch_assoc()) {
            $found[] = $r;
        }
        echo json_encode($found);
    } else {
        echo json_encode([]);
    }
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
                    $headers = "From: ASCENDING PAWN CHESS <$from>\r\n";
                    $headers .= "Reply-To: $from\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    $emailBody = "
                    <html>
                    <body style='font-family: sans-serif; background-color: #f7fafc; padding: 40px;'>
                        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                            <div style='text-align: center; margin-bottom: 30px;'>
                                <h1 style='color: #80D200; margin: 0; font-size: 24px;'>ASCENDING PAWN CHESS</h1>
                            </div>
                            <h2 style='color: #1a202c; border-bottom: 2px solid #80D200; padding-bottom: 10px;'>$subject</h2>
                            <div style='color: #4a5568; line-height: 1.8; font-size: 16px;'>
                                $content
                            </div>
                            <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;'>
                                <p style='font-size: 12px; color: #718096;'>
                                    You are receiving this email from ASCENDING PAWN CHESS.<br>
                                    &copy; " . date('Y') . " ASCENDING PAWN CHESS. All rights reserved.
                                </p>
                            </div>
                        </div>
                    </body>
                    </html>";

                    $sentCount = 0;
                    $failCount = 0;
                    while ($row = $emailResult->fetch_assoc()) {
                        // Using -f parameter for envelope-from (helps SPF/DKIM verification)
                        if (mail($row['email'], $subject, $emailBody, $headers, "-f $from")) {
                            $sentCount++;
                        } else {
                            $failCount++;
                        }
                    }
                    
                    if ($failCount > 0) {
                        $message = "Communication processed: In-app sent. Emails: $sentCount successful, $failCount failed. Check server mail logs.";
                    } else {
                        $message = "Communication sent successfully to " . count($userIds) . " users via selected channels.";
                    }
                } else {
                    $message = "In-app communication sent successfully to " . count($userIds) . " users.";
                }
            } else {
                $error = "Failed to save message: " . $conn->error;
            }
        }
    }
}

// Fetch some users for individual selection
$users = $conn->query("SELECT id, username, email FROM users ORDER BY username ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch recently sent notifications
$sentQuery = $conn->query("SELECT title, type, created_at FROM notification_content WHERE created_by = $admin_id ORDER BY created_at DESC LIMIT 5");
$sentNotifications = $sentQuery ? $sentQuery->fetch_all(MYSQLI_ASSOC) : [];

// Fetch recently received notifications
$receivedQuery = $conn->query("SELECT nc.title, nc.type, n.is_read, n.created_at FROM notifications n JOIN notification_content nc ON n.content_id = nc.id WHERE n.user_id = $admin_id ORDER BY n.created_at DESC LIMIT 5");
$receivedNotifications = $receivedQuery ? $receivedQuery->fetch_all(MYSQLI_ASSOC) : [];

// Fetch emails from info@ascendingpawnchess.com mailbox
$inboxEmails = fetchEmailsFromIMAP('INBOX', 10);
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-black">Communications</h1>
            <p class="text-slate-500">Send updates, alerts, and promotions to your members.</p>
        </div>
    </div>
    
    <!-- Tabs Header -->
    <div class="flex gap-4 border-b border-slate-200 dark:border-slate-800 mb-8">
        <button id="tab-inbox" onclick="switchTab('inbox')" class="px-6 py-3 font-bold text-slate-500 uppercase tracking-widest text-sm hover:text-brandGreen transition-colors border-b-2 border-transparent">
            <i class="fas fa-inbox mr-2"></i> Inbox & History
        </button>
        <button id="tab-compose" onclick="switchTab('compose')" class="px-6 py-3 font-bold text-slate-500 uppercase tracking-widest text-sm hover:text-brandGreen transition-colors border-b-2 border-transparent">
            <i class="fas fa-pen mr-2"></i> Compose
        </button>
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

    <!-- Compose Tab Content -->
    <div id="content-compose" class="hidden">
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
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Search & Select Members</label>
                        
                        <!-- Selected Users Chips Container -->
                        <div id="selectedUsersContainer" class="flex flex-wrap gap-2 mb-3 empty:mb-0"></div>

                        <!-- Live Search Input -->
                        <div class="relative">
                            <input type="text" id="userLiveSearch" placeholder="Type username or email..." autocomplete="off"
                                class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all">
                            
                            <!-- Search Results Dropdown -->
                            <div id="searchResults" class="absolute z-10 w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden">
                            </div>
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

    <!-- Inbox Tab Content -->
    <div id="content-inbox" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Sent History -->
        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6">
            <h3 class="font-black text-lg mb-6 flex items-center justify-between">
                <span class="flex items-center gap-2"><i class="fas fa-paper-plane text-brandGreen"></i> Recently Sent</span>
            </h3>
            <div class="space-y-4">
                <?php if (empty($sentNotifications)): ?>
                    <p class="text-sm text-slate-500 italic p-4 text-center">No messages sent yet.</p>
                <?php else: ?>
                    <?php foreach ($sentNotifications as $notif): ?>
                        <div class="flex flex-col gap-1 p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-sm truncate pr-2 max-w-[70%]"><?php echo htmlspecialchars($notif['title']); ?></h4>
                                <span class="bg-brandGreen/10 text-brandGreen text-[9px] px-2 py-0.5 rounded-full uppercase font-bold"><?php echo htmlspecialchars($notif['type']); ?></span>
                            </div>
                            <span class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i> <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Received In-App Notifications -->
        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 relative">
            <h3 class="font-black text-lg mb-6 flex items-center justify-between">
                <span class="flex items-center gap-2"><i class="fas fa-bell text-brandGreen"></i> Notifications</span>
            </h3>
            <div class="space-y-4">
                <?php if (empty($receivedNotifications)): ?>
                    <p class="text-sm text-slate-500 italic p-4 text-center">Your inbox is empty.</p>
                <?php else: ?>
                    <?php foreach ($receivedNotifications as $notif): ?>
                        <div class="flex flex-col gap-1 p-3 rounded-xl border <?php echo !$notif['is_read'] ? 'border-brandGreen/30 bg-brandGreen/5' : 'border-slate-100 dark:border-slate-800'; ?> hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-sm truncate pr-2 max-w-[70%] <?php echo !$notif['is_read'] ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-300'; ?>">
                                    <?php echo htmlspecialchars($notif['title']); ?>
                                </h4>
                                <?php if (!$notif['is_read']): ?>
                                    <span class="bg-amber-100 text-amber-600 text-[9px] px-2 py-0.5 rounded-full uppercase font-bold">Unread</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i> <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <a href="../notifications.php" class="block text-center mt-6 text-xs font-bold text-brandGreen hover:underline uppercase tracking-widest">View All <i class="fas fa-arrow-right ml-1"></i></a>
        </div>

        <!-- Email Inbox from info@ascendingpawnchess.com -->
        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 relative">
            <h3 class="font-black text-lg mb-6 flex items-center justify-between">
                <span class="flex items-center gap-2"><i class="fas fa-envelope text-brandGreen"></i> Email Inbox</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase">info@ascendingpawnchess.com</span>
            </h3>
            <div class="space-y-4">
                <?php if (empty($inboxEmails)): ?>
                    <p class="text-sm text-slate-500 italic p-4 text-center">
                        <?php if ($inboxEmails === false || (is_array($inboxEmails) && count($inboxEmails) === 0)): ?>
                            No emails received yet or unable to connect to mailbox.
                        <?php else: ?>
                            Mailbox is empty.
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <?php foreach ($inboxEmails as $email): ?>
                        <div class="flex flex-col gap-1 p-3 rounded-xl border <?php echo !$email['is_read'] ? 'border-blue-300/30 bg-blue-50 dark:bg-blue-900/10' : 'border-slate-100 dark:border-slate-800'; ?> hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-sm truncate <?php echo !$email['is_read'] ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-300'; ?>">
                                        <?php echo htmlspecialchars($email['subject']); ?>
                                    </h4>
                                    <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($email['from_name'] ? $email['from_name'] . ' <' . $email['from'] . '>' : $email['from']); ?></p>
                                </div>
                                <?php if (!$email['is_read']): ?>
                                    <span class="bg-blue-100 text-blue-600 text-[9px] px-2 py-0.5 rounded-full uppercase font-bold shrink-0">New</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i> <?php echo date('M d, Y H:i', strtotime($email['date'])); ?></span>
                            <p class="text-xs text-slate-500 line-clamp-2 mt-1"><?php echo htmlspecialchars(substr($email['body'], 0, 100)); ?>...</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
// Tab Switching Logic
function switchTab(tabId) {
    const tabInboxBtn = document.getElementById('tab-inbox');
    const tabComposeBtn = document.getElementById('tab-compose');
    const contentInbox = document.getElementById('content-inbox');
    const contentCompose = document.getElementById('content-compose');

    // Reset styles
    tabInboxBtn.classList.remove('text-brandGreen', 'border-brandGreen');
    tabInboxBtn.classList.add('text-slate-500', 'border-transparent');
    tabComposeBtn.classList.remove('text-brandGreen', 'border-brandGreen');
    tabComposeBtn.classList.add('text-slate-500', 'border-transparent');
    
    contentInbox.classList.add('hidden');
    contentCompose.classList.add('hidden');

    // Apply active styles
    if (tabId === 'inbox') {
        tabInboxBtn.classList.add('text-brandGreen', 'border-brandGreen');
        tabInboxBtn.classList.remove('text-slate-500', 'border-transparent');
        contentInbox.classList.remove('hidden');
        localStorage.setItem('adminCommTab', 'inbox');
    } else {
        tabComposeBtn.classList.add('text-brandGreen', 'border-brandGreen');
        tabComposeBtn.classList.remove('text-slate-500', 'border-transparent');
        contentCompose.classList.remove('hidden');
        localStorage.setItem('adminCommTab', 'compose');
    }
}

// Restore tab from local storage or set default
document.addEventListener('DOMContentLoaded', () => {
    let hasMessage = <?php echo ($message || $error) ? 'true' : 'false'; ?>;
    let storedTab = localStorage.getItem('adminCommTab') || 'inbox';
    if (hasMessage) storedTab = 'compose'; // default to compose if we just submitted a form
    switchTab(storedTab);
});

function toggleUserSelection() {
    const select = document.getElementById('targetSelect');
    const userDiv = document.getElementById('userSelection');
    if (select.value === 'specific') {
        userDiv.classList.remove('hidden');
    } else {
        userDiv.classList.add('hidden');
    }
}

// Live Search Logic
let selectedUsers = new Map();
let searchTimeout = null;

const searchInput = document.getElementById('userLiveSearch');
const resultsDropdown = document.getElementById('searchResults');
const selectedContainer = document.getElementById('selectedUsersContainer');

if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const q = e.target.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (q.length < 2) {
            resultsDropdown.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch('communications.php?ajax_search=' + encodeURIComponent(q))
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        let html = data.map(user => {
                            if (selectedUsers.has(user.id)) return '';
                            let jUser = user.username.replace(/'/g, "\\'");
                            let jEmail = user.email.replace(/'/g, "\\'");
                            return `
                            <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer border-b border-slate-100 dark:border-slate-700 last:border-0" 
                                 onclick="addSpecificUser('${user.id}', '${jUser}', '${jEmail}')">
                                <div class="font-bold text-sm text-slate-900 dark:text-white">${user.username}</div>
                                <div class="text-xs text-slate-500">${user.email}</div>
                            </div>`;
                        }).join('');
                        
                        if(html.trim() === '') {
                            html = `<div class="px-4 py-4 text-sm text-slate-500 text-center">All matching users are already selected.</div>`;
                        }
                        resultsDropdown.innerHTML = html;
                        resultsDropdown.classList.remove('hidden');
                    } else {
                        resultsDropdown.innerHTML = `<div class="px-4 py-4 text-sm text-slate-500 text-center">No users found matching "${q}".</div>`;
                        resultsDropdown.classList.remove('hidden');
                    }
                });
        }, 300); // 300ms debounce
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#userSelection')) {
            resultsDropdown.classList.add('hidden');
        }
    });
}

function addSpecificUser(id, username, email) {
    if (!selectedUsers.has(id)) {
        selectedUsers.set(id, {username, email});
        renderSelectedUsers();
    }
    searchInput.value = '';
    resultsDropdown.classList.add('hidden');
    searchInput.focus();
}

function removeSpecificUser(id) {
    selectedUsers.delete(id);
    renderSelectedUsers();
}

function renderSelectedUsers() {
    selectedContainer.innerHTML = Array.from(selectedUsers.entries()).map(([id, user]) => `
        <div class="flex items-center gap-2 px-3 py-1.5 bg-brandGreen/10 text-brandGreen rounded-xl border border-brandGreen/20">
            <input type="hidden" name="target[]" value="${id}">
            <div class="text-sm font-bold">${user.username}</div>
            <button type="button" onclick="removeSpecificUser('${id}')" class="ml-1 hover:text-brandOrange transition-colors" title="Remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}
</script>

<?php include "admin_footer.php"; ?>
