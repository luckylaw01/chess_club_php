<?php
session_start();
require_once "includes/db_connect.php";

$pageTitle = "Contact Us";

$message_sent = false;
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_msg = "Please fill in all fields.";
    } else {
        // Prepare notification content
        $title = "Contact Form: " . substr($subject, 0, 200);
        $full_message = "<strong>From:</strong> " . htmlspecialchars($name) . " (" . htmlspecialchars($email) . ")<br><br>" . nl2br(htmlspecialchars($message));
        
        $stmt = $conn->prepare("INSERT INTO notification_content (title, message, type) VALUES (?, ?, 'alert')");
        $stmt->bind_param("ss", $title, $full_message);
        
        if ($stmt->execute()) {
            $content_id = $stmt->insert_id;
            
            // Get all admins
            $adminCount = 0;
            $adminQuery = $conn->query("SELECT id FROM users WHERE role = 'admin'");
            if ($adminQuery) {
                $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, content_id) VALUES (?, ?)");
                while ($admin = $adminQuery->fetch_assoc()) {
                    $notifStmt->bind_param("ii", $admin['id'], $content_id);
                    $notifStmt->execute();
                    $adminCount++;
                }
            }
            
            $message_sent = true;
        } else {
            $error_msg = "Something went wrong. Please try again later.";
        }
    }
}

include 'includes/header.php';
?>

<div class="bg-slate-50 dark:bg-darkBg min-h-screen py-32 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white mb-6">Get in Touch</h1>
            <p class="text-lg text-slate-600 dark:text-slate-400">Have a question or want to join the club? Fill out the form below or contact us directly.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-12">
            <!-- Contact Info -->
            <div class="md:col-span-1 space-y-8">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
                    <div class="w-12 h-12 bg-brandGreen/10 text-brandGreen rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Location</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Nyeri, Kenya</p>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
                    <div class="w-12 h-12 bg-brandOrange/10 text-brandOrange rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Email</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">info@ascendingpawnchess.com</p>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
                    <div class="w-12 h-12 bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center text-xl mb-4">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Phone</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">+254721510393</p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 shadow-sm border border-slate-200 dark:border-slate-800 h-full flex flex-col">
                    <?php if ($message_sent): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 relative">
                            <span class="block sm:inline">Thank you! Your message has been sent successfully. We will get back to you shortly.</span>
                        </div>
                    <?php elseif (!empty($error_msg)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 relative">
                            <span class="block sm:inline"><?php echo $error_msg; ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="contact_us.php" method="POST" class="space-y-6 flex-grow flex flex-col">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Your Name</label>
                                <input type="text" name="name" required placeholder="John Doe" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brandGreen focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Address</label>
                                <input type="email" name="email" required placeholder="john@example.com" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brandGreen focus:border-transparent transition-all">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subject</label>
                            <input type="text" name="subject" required placeholder="How can we help?" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brandGreen focus:border-transparent transition-all">
                        </div>

                        <div class="flex-grow flex flex-col">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Message</label>
                            <textarea name="message" required rows="5" placeholder="Your message here..." class="flex-grow w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brandGreen focus:border-transparent transition-all resize-none"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-brandGreen hover:bg-brandGreen/90 text-white font-bold rounded-xl px-6 py-4 transition-all">
                            Send Message <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>