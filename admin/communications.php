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

// Handle AJAX Mark Email as Read
if (isset($_POST['mark_email_read_uid'])) {
    header('Content-Type: application/json');
    $uid = (int)$_POST['mark_email_read_uid'];
    $success = markEmailAsRead($uid);
    echo json_encode(['success' => $success]);
    exit();
}

$admin_id = $_SESSION['id'];
$pageTitle = "Communications Hub";
include "admin_header.php";
?>
<!-- Quill Rich Text Editor Styles -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        border-color: rgba(226, 232, 240, 1) !important;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        background-color: #f8fafc;
    }
    .dark .ql-toolbar.ql-snow {
        border-color: rgba(51, 65, 85, 1) !important;
        background-color: #1e293b;
    }
    .ql-container.ql-snow {
        border-color: rgba(226, 232, 240, 1) !important;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
        font-family: inherit;
        font-size: 14px;
        min-height: 200px;
    }
    .dark .ql-container.ql-snow {
        border-color: rgba(51, 65, 85, 1) !important;
        background-color: rgba(30, 41, 59, 0.5) !important;
    }
    .ql-editor {
        min-height: 200px;
    }
    .dark .ql-snow .ql-stroke {
        stroke: #94a3b8 !important;
    }
    .dark .ql-snow .ql-fill {
        fill: #94a3b8 !important;
    }
    .dark .ql-snow .ql-picker {
        color: #94a3b8 !important;
    }
    .dark .ql-snow .ql-picker-options {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
</style>
<?php

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
$inboxEmailsResult = fetchEmailsFromIMAP('INBOX', 25);
$inboxEmails = [];
$inboxError = null;
$inboxDebug = [];
if (is_array($inboxEmailsResult) && array_key_exists('success', $inboxEmailsResult)) {
    if ($inboxEmailsResult['success'] === true) {
        $inboxEmails = $inboxEmailsResult['emails'];
    } else {
        $inboxError = $inboxEmailsResult['error'] ?? 'Unknown error while fetching mailbox.';
        $inboxDebug = $inboxEmailsResult['debug'] ?? [];
    }
} else {
    // Backwards compatibility: if function returns simple array
    if ($inboxEmailsResult === false) {
        $inboxError = 'Failed to fetch mailbox (IMAP function returned false).';
    } elseif (is_array($inboxEmailsResult)) {
        $inboxEmails = $inboxEmailsResult;
    }
}
$inboxPreviewEmails = array_slice($inboxEmails, 0, 5);
$inboxEmailCount = is_array($inboxEmails) ? count($inboxEmails) : 0;
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-black">Communications</h1>
            <p class="text-slate-500">Send updates, alerts, and promotions to your members.</p>
        </div>
    </div>
    
    <!-- Tabs Header -->
    <div class="flex flex-wrap gap-2 md:gap-4 border-b border-slate-200 dark:border-slate-800 mb-8">
        <button id="tab-inbox" onclick="switchTab('inbox')" class="px-4 md:px-6 py-3 font-bold text-slate-500 uppercase tracking-widest text-xs md:text-sm hover:text-brandGreen transition-colors border-b-2 border-transparent">
            <i class="fas fa-envelope mr-2"></i> Email Inbox
        </button>
        <button id="tab-notifications" onclick="switchTab('notifications')" class="px-4 md:px-6 py-3 font-bold text-slate-500 uppercase tracking-widest text-xs md:text-sm hover:text-brandGreen transition-colors border-b-2 border-transparent">
            <i class="fas fa-bell mr-2"></i> Notifications
        </button>
        <button id="tab-sent" onclick="switchTab('sent')" class="px-4 md:px-6 py-3 font-bold text-slate-500 uppercase tracking-widest text-xs md:text-sm hover:text-brandGreen transition-colors border-b-2 border-transparent">
            <i class="fas fa-paper-plane mr-2"></i> Recently Sent
        </button>
        <button id="tab-compose" onclick="switchTab('compose')" class="px-4 md:px-6 py-3 font-bold text-slate-500 uppercase tracking-widest text-xs md:text-sm hover:text-brandGreen transition-colors border-b-2 border-transparent">
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
                        <div id="editor-container" class="w-full bg-slate-50 dark:bg-slate-800/50 rounded-2xl transition-all"></div>
                        <input type="hidden" name="message" id="message-input" required>
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

    <!-- Email Inbox Tab Content -->
    <div id="content-inbox" class="hidden">
        <!-- Email Inbox from info@ascendingpawnchess.com -->
        <div class="relative overflow-hidden rounded-[34px] border border-slate-200/80 dark:border-slate-800 bg-gradient-to-br from-white via-slate-50 to-slate-100 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 shadow-[0_18px_50px_-24px_rgba(15,23,42,0.35)]">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brandGreen via-brandGold to-brandOrange"></div>
            <div class="p-8">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 text-brandGreen flex items-center justify-center ring-1 ring-brandGreen/15 shrink-0">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-xl leading-tight tracking-tight text-slate-900 dark:text-white">Email Inbox</h3>
                            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mt-1 truncate">info@ascendingpawnchess.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1 rounded-full bg-brandGreen/10 text-brandGreen text-[9px] font-black uppercase tracking-[0.2em]">IMAP</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <?php if (empty($inboxPreviewEmails)): ?>
                        <div class="rounded-[28px] border border-dashed border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-950/40 p-5 text-center shadow-inner">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                <?php if (!empty($inboxError)): ?>
                                    <span class="font-bold text-rose-600 dark:text-rose-300">Mailbox Error:</span> <?php echo htmlspecialchars($inboxError); ?>
                                <?php elseif ($inboxEmails === false || (is_array($inboxEmails) && count($inboxEmails) === 0)): ?>
                                    No emails received yet or unable to connect to mailbox.
                                <?php else: ?>
                                    Mailbox is empty.
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if (!empty($inboxDebug)): ?>
                            <div class="mt-4 p-4 rounded-3xl border border-rose-200/70 bg-rose-50/80 dark:bg-rose-900/10 dark:border-rose-900/30 text-xs text-rose-700 dark:text-rose-200 overflow-auto shadow-sm">
                                <div class="font-black uppercase tracking-[0.2em] mb-2 flex items-center gap-2">
                                    <i class="fas fa-triangle-exclamation"></i> IMAP Debug
                                </div>
                                <pre class="whitespace-pre-wrap break-words leading-relaxed"><?php echo htmlspecialchars(print_r($inboxDebug, true)); ?></pre>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php foreach ($inboxPreviewEmails as $index => $email): ?>
                            <button type="button" onclick="toggleEmailPreview(<?php echo (int)$index; ?>, <?php echo (int)$email['uid']; ?>, this)" class="email-preview-card group w-full text-left flex flex-col gap-3 p-4 rounded-[24px] border <?php echo !$email['is_read'] ? 'border-brandGreen/25 bg-brandGreen/5' : 'border-slate-200/70 dark:border-slate-800'; ?> bg-white/90 dark:bg-slate-900/70 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/50 dark:hover:shadow-black/20 transition-all duration-300">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="font-extrabold text-sm truncate tracking-tight <?php echo !$email['is_read'] ? 'text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-200'; ?>">
                                                <?php echo htmlspecialchars($email['subject']); ?>
                                            </h4>
                                            <?php if (!$email['is_read']): ?>
                                                <span class="bg-brandGreen text-white text-[9px] px-2.5 py-1 rounded-full uppercase font-black tracking-widest shrink-0 shadow-sm">New</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($email['from_name'] ? $email['from_name'] . ' <' . $email['from'] . '>' : $email['from']); ?></p>
                                    </div>
                                    <span class="shrink-0 text-slate-400 transition-transform duration-300 email-chevron" data-chevron-for="<?php echo (int)$index; ?>">
                                        <i class="fas fa-chevron-down"></i>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 font-semibold">
                                    <i class="far fa-clock"></i>
                                    <span><?php echo date('M d, Y H:i', strtotime($email['date'])); ?></span>
                                </div>
                                <div class="email-preview-body hidden text-xs text-slate-500 leading-relaxed border-t border-slate-200/70 dark:border-slate-800 pt-3 mt-1">
                                    <?php echo nl2br(htmlspecialchars($email['body'])); ?>
                                </div>
                            </button>
                        <?php endforeach; ?>

                        <div class="flex items-center justify-between gap-3 pt-2">
                            <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-[0.2em]">
                                Showing latest <?php echo count($inboxPreviewEmails); ?> of <?php echo (int)$inboxEmailCount; ?> emails
                            </p>
                            <button type="button" onclick="openInboxModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all">
                                See All <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Tab Content -->
    <div id="content-notifications" class="hidden">
        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-8 relative">
            <h3 class="font-black text-lg mb-6 flex items-center justify-between">
                <span class="flex items-center gap-2"><i class="fas fa-bell text-brandGreen"></i> Received Notifications</span>
            </h3>
            <div class="space-y-4">
                <?php if (empty($receivedNotifications)): ?>
                    <p class="text-sm text-slate-500 italic p-4 text-center">Your inbox is empty.</p>
                <?php else: ?>
                    <?php foreach ($receivedNotifications as $notif): ?>
                        <div class="flex flex-col gap-1 p-4 rounded-2xl border <?php echo !$notif['is_read'] ? 'border-brandGreen/30 bg-brandGreen/5' : 'border-slate-100 dark:border-slate-800'; ?> hover:bg-slate-50 dark:hover:bg-slate-800/50 transition animate-slide-up">
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
    </div>

    <!-- Recently Sent Tab Content -->
    <div id="content-sent" class="hidden">
        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-8">
            <h3 class="font-black text-lg mb-6 flex items-center justify-between">
                <span class="flex items-center gap-2"><i class="fas fa-paper-plane text-brandGreen"></i> Recently Sent Communications</span>
            </h3>
            <div class="space-y-4">
                <?php if (empty($sentNotifications)): ?>
                    <p class="text-sm text-slate-500 italic p-4 text-center">No messages sent yet.</p>
                <?php else: ?>
                    <?php foreach ($sentNotifications as $notif): ?>
                        <div class="flex flex-col gap-1 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition animate-slide-up">
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
    </div>
</div>

<!-- Inbox Modal -->
<div id="inboxModal" class="fixed inset-0 z-[80] hidden">
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeInboxModal()"></div>
    <div class="relative mx-auto my-6 w-[min(1000px,calc(100vw-1.5rem))] max-h-[calc(100vh-3rem)] overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-2xl">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Inbox</h3>
                <p class="text-xs text-slate-500">Latest <?php echo (int)$inboxEmailCount; ?> fetched emails from info@ascendingpawnchess.com</p>
            </div>
            <button type="button" onclick="closeInboxModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="max-h-[calc(100vh-10rem)] overflow-y-auto p-6 space-y-4">
            <?php if (!empty($inboxEmails)): ?>
                <?php foreach ($inboxEmails as $email): ?>
                    <div onclick="markModalEmailRead(<?php echo (int)$email['uid']; ?>, this)" class="cursor-pointer transition-all rounded-[24px] border <?php echo !$email['is_read'] ? 'border-brandGreen/25 bg-brandGreen/5' : 'border-slate-200/70 dark:border-slate-800'; ?> bg-white/90 dark:bg-slate-900/70 p-4">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="min-w-0 flex-1">
                                <h4 class="font-extrabold text-sm tracking-tight text-slate-900 dark:text-white truncate"><?php echo htmlspecialchars($email['subject']); ?></h4>
                                <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($email['from_name'] ? $email['from_name'] . ' <' . $email['from'] . '>' : $email['from']); ?></p>
                            </div>
                            <?php if (!$email['is_read']): ?>
                                <span class="bg-brandGreen text-white text-[9px] px-2.5 py-1 rounded-full uppercase font-black tracking-widest shrink-0">New</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 font-semibold mb-3">
                            <i class="far fa-clock"></i>
                            <span><?php echo date('M d, Y H:i', strtotime($email['date'])); ?></span>
                        </div>
                        <div class="text-xs text-slate-500 leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($email['body']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="rounded-[24px] border border-dashed border-slate-200 dark:border-slate-800 p-8 text-center text-slate-500">No emails to display.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function markEmailAsReadUI(uid, card) {
    if (uid && card.classList.contains('bg-brandGreen/5')) {
        card.classList.remove('border-brandGreen/25', 'bg-brandGreen/5');
        card.classList.add('border-slate-200/70', 'dark:border-slate-800');
        
        const title = card.querySelector('h4');
        if (title) {
            title.classList.remove('text-slate-900', 'dark:text-white');
            title.classList.add('text-slate-700', 'dark:text-slate-200');
        }
        
        const badge = card.querySelector('span.bg-brandGreen');
        if (badge && badge.textContent.trim() === 'New') {
            badge.remove();
        }

        let formData = new FormData();
        formData.append('mark_email_read_uid', uid);
        fetch('communications.php', {
            method: 'POST',
            body: formData
        }).catch(err => console.error(err));
    }
}

function toggleEmailPreview(index, uid, btn) {
    const card = btn || document.querySelector('.email-preview-card[onclick*="toggleEmailPreview(' + index + '"]');
    if (!card) return;
    const body = card.querySelector('.email-preview-body');
    const chevron = card.querySelector('.email-chevron');
    if (!body || !chevron) return;
    body.classList.toggle('hidden');
    chevron.classList.toggle('rotate-180');
    
    markEmailAsReadUI(uid, card);
}

function markModalEmailRead(uid, card) {
    markEmailAsReadUI(uid, card);
}

function openInboxModal() {
    const modal = document.getElementById('inboxModal');
    if (modal) modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeInboxModal() {
    const modal = document.getElementById('inboxModal');
    if (modal) modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Tab Switching Logic
function switchTab(tabId) {
    const tabs = ['inbox', 'notifications', 'sent', 'compose'];
    
    tabs.forEach(t => {
        const btn = document.getElementById('tab-' + t);
        const content = document.getElementById('content-' + t);
        
        if (btn && content) {
            // Reset styles
            btn.classList.remove('text-brandGreen', 'border-brandGreen');
            btn.classList.add('text-slate-500', 'border-transparent');
            content.classList.add('hidden');
            
            // Apply active styles if matching
            if (t === tabId) {
                btn.classList.add('text-brandGreen', 'border-brandGreen');
                btn.classList.remove('text-slate-500', 'border-transparent');
                content.classList.remove('hidden');
                localStorage.setItem('adminCommTab', tabId);
            }
        }
    });
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

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    const toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', () => {
        const fileInput = document.createElement('input');
        fileInput.setAttribute('type', 'file');
        fileInput.setAttribute('accept', 'image/*');
        fileInput.click();
        
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('image', file);
                
                const range = quill.getSelection(true);
                quill.insertText(range.index, '[Uploading Image...]');
                
                fetch('upload_campaign_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    quill.deleteText(range.index, '[Uploading Image...]'.length);
                    if (data.url) {
                        quill.insertEmbed(range.index, 'image', data.url);
                    } else {
                        alert(data.error || 'Failed to upload image.');
                    }
                })
                .catch(err => {
                    quill.deleteText(range.index, '[Uploading Image...]'.length);
                    console.error(err);
                    alert('Upload error. Try again.');
                });
            }
        });
    });

    const form = document.querySelector('form[action="communications.php"]');
    if (form) {
        form.addEventListener('submit', (e) => {
            const html = quill.root.innerHTML;
            if (html === '<p><br></p>' || html.trim() === '') {
                alert('Message content cannot be empty.');
                e.preventDefault();
                return;
            }
            document.getElementById('message-input').value = html;
        });
    }
});
</script>

<?php include "admin_footer.php"; ?>
