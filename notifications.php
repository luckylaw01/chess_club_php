<?php
session_start();
require_once "includes/db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

// Handle mark as read
if (isset($_GET['mark_read'])) {
    $notifId = intval($_GET['mark_read']);
    $conn->query("UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = $notifId AND user_id = $user_id");
    header("Location: notifications.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $notifId = intval($_GET['delete']);
    $conn->query("DELETE FROM notifications WHERE id = $notifId AND user_id = $user_id");
    header("Location: notifications.php");
    exit();
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE user_id = $user_id AND is_read = 0");
    header("Location: notifications.php");
    exit();
}

$pageTitle = "My Notifications";
include "includes/header.php";

// Fetch all notifications for the user
$stmt = $conn->prepare("SELECT n.id, n.is_read, n.created_at, nc.title, nc.message, nc.type 
                        FROM notifications n 
                        JOIN notification_content nc ON n.content_id = nc.id 
                        WHERE n.user_id = ? 
                        ORDER BY n.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="pt-32 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div>
                <h1 class="text-4xl lg:text-5xl font-black mb-2 tracking-tight">Notification <span class="text-brandGreen">Center</span></h1>
                <p class="text-slate-500 font-medium">Stay updated with the latest from Ascending Pawn Chess Club.</p>
            </div>
            <?php if (!empty($notifications)): ?>
                <a href="?mark_all_read=1" class="px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-brandGreen hover:text-white hover:border-brandGreen transition-all shadow-sm">
                    Mark All as Read
                </a>
            <?php endif; ?>
        </div>

        <div class="space-y-4">
            <?php if (empty($notifications)): ?>
                <div class="bg-white dark:bg-slate-900/50 p-20 rounded-[40px] text-center border border-dashed border-slate-200 dark:border-slate-800">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-400">
                        <i class="fas fa-bell-slash text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">No notifications yet</h3>
                    <p class="text-slate-400 text-sm">We'll let you know when there's something new for you!</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="group bg-white dark:bg-slate-900/50 p-6 rounded-3xl border <?php echo $n['is_read'] ? 'border-slate-100 dark:border-slate-800 opacity-75' : 'border-brandGreen/30 shadow-lg shadow-brandGreen/5'; ?> transition-all hover:border-brandGreen/50 glass relative overflow-hidden cursor-pointer notification-card" data-notif-id="<?php echo $n['id']; ?>" data-notif-title="<?php echo htmlspecialchars($n['title']); ?>" data-notif-message="<?php echo htmlspecialchars($n['message']); ?>" data-notif-type="<?php echo $n['type']; ?>" data-notif-date="<?php echo date('F d, Y • H:i', strtotime($n['created_at'])); ?>" data-notif-read="<?php echo $n['is_read']; ?>">
                        <?php if (!$n['is_read']): ?>
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-brandGreen"></div>
                        <?php endif; ?>
                        
                        <div class="flex flex-col md:flex-row gap-6 md:items-center justify-between">
                            <div class="flex gap-5">
                                <?php 
                                    $icon = 'fa-info-circle';
                                    $bgClass = 'bg-blue-100 text-blue-500 dark:bg-blue-900/30';
                                    
                                    if ($n['type'] == 'alert') {
                                        $icon = 'fa-exclamation-triangle';
                                        $bgClass = 'bg-rose-100 text-rose-500 dark:bg-rose-900/30';
                                    } elseif ($n['type'] == 'announcement') {
                                        $icon = 'fa-bullhorn';
                                        $bgClass = 'bg-brandGreen/10 text-brandGreen dark:bg-brandGreen/20';
                                    } elseif ($n['type'] == 'promotion') {
                                        $icon = 'fa-tag';
                                        $bgClass = 'bg-amber-100 text-amber-500 dark:bg-amber-900/30';
                                    }
                                ?>
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 <?php echo $bgClass; ?>">
                                    <i class="fas <?php echo $icon; ?> text-lg"></i>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-black text-lg"><?php echo htmlspecialchars($n['title']); ?></h3>
                                        <?php if (!$n['is_read']): ?>
                                            <span class="px-2 py-0.5 rounded-full bg-brandGreen/10 text-brandGreen text-[8px] font-black uppercase tracking-widest">New</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                                        <?php echo $n['message']; // No htmlspecialchars as we support HTML from admin ?>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight mt-2 flex items-center gap-2">
                                        <i class="far fa-clock"></i> <?php echo date('F d, Y • H:i', strtotime($n['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 ml-14 md:ml-0">
                                <?php if (!$n['is_read']): ?>
                                    <a href="?mark_read=<?php echo $n['id']; ?>" class="p-3 bg-brandGreen/10 text-brandGreen hover:bg-brandGreen hover:text-white rounded-xl transition-all" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $n['id']; ?>" onclick="return confirm('Archive this notification?')" class="p-3 bg-slate-100 dark:bg-slate-800 text-slate-400 hover:bg-rose-500 hover:text-white rounded-xl transition-all" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div id="notificationModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
        <div class="sticky top-0 bg-white dark:bg-slate-900 p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-start">
            <div class="flex items-center gap-4">
                <div id="modalIconContainer" class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0"></div>
                <div>
                    <h2 id="modalTitle" class="text-2xl font-black"></h2>
                    <p id="modalDate" class="text-xs text-slate-500 font-bold uppercase tracking-tight mt-1"></p>
                </div>
            </div>
            <button onclick="closeNotificationModal()" class="w-10 h-10 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="modalMessage" class="text-base leading-relaxed text-slate-700 dark:text-slate-300 font-medium mb-8"></div>
            <div class="flex items-center gap-3">
                <button onclick="closeNotificationModal()" class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl font-bold uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Get notification cards
const notificationCards = document.querySelectorAll('.notification-card');

notificationCards.forEach(card => {
    card.addEventListener('click', function() {
        const notifId = this.dataset.notifId;
        const title = this.dataset.notifTitle;
        const message = this.dataset.notifMessage;
        const type = this.dataset.notifType;
        const date = this.dataset.notifDate;
        const isRead = this.dataset.notifRead;

        // Set modal content
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').innerHTML = message;
        document.getElementById('modalDate').textContent = date;

        // Set icon and color
        let icon = 'fa-info-circle';
        let bgClass = 'bg-blue-100 text-blue-500 dark:bg-blue-900/30';
        
        if (type === 'alert') {
            icon = 'fa-exclamation-triangle';
            bgClass = 'bg-rose-100 text-rose-500 dark:bg-rose-900/30';
        } else if (type === 'announcement') {
            icon = 'fa-bullhorn';
            bgClass = 'bg-brandGreen/10 text-brandGreen dark:bg-brandGreen/20';
        } else if (type === 'promotion') {
            icon = 'fa-tag';
            bgClass = 'bg-amber-100 text-amber-500 dark:bg-amber-900/30';
        }

        const iconContainer = document.getElementById('modalIconContainer');
        iconContainer.className = 'w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 ' + bgClass;
        iconContainer.innerHTML = '<i class="fas ' + icon + ' text-xl"></i>';

        // Show modal
        document.getElementById('notificationModal').classList.remove('hidden');

        // Mark as read if not already read
        if (isRead === '0') {
            markNotificationAsRead(notifId);
        }
    });
});

function closeNotificationModal() {
    document.getElementById('notificationModal').classList.add('hidden');
}

function markNotificationAsRead(notifId) {
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: notifId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the card's visual state
            const card = document.querySelector('[data-notif-id="' + notifId + '"]');
            if (card) {
                card.dataset.notifRead = '1';
                card.classList.remove('border-brandGreen/30', 'shadow-lg', 'shadow-brandGreen/5');
                card.classList.add('border-slate-100', 'dark:border-slate-800', 'opacity-75');
                // Remove the green bar and "New" badge
                const bar = card.querySelector('.absolute.top-0.left-0');
                if (bar) bar.remove();
                const badge = card.querySelector('span.bg-brandGreen');
                if (badge) badge.remove();
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Close modal on outside click
document.getElementById('notificationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNotificationModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNotificationModal();
    }
});
</script>

<?php include "includes/footer.php"; ?>
