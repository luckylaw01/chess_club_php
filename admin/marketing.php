<?php
session_start();
require_once "../includes/db_connect.php";

// Check if user is admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['id'];
$pageTitle = "Marketing Campaigns";
include "admin_header.php";

$message = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject'], $_POST['message'], $_POST['target'])) {
    $subject = $conn->real_escape_string($_POST['subject']);
    $content = $_POST['message']; // keep html
    $target = $_POST['target']; // 'all_users', 'mailing_list', 'both', 'custom'
    $custom_emails = trim($_POST['custom_emails'] ?? '');

    if (empty($subject) || empty($content)) {
        $error = "Subject and message are required.";
    } else {
        $recipient_emails = [];

        // 1. Fetch from Users table
        if ($target === 'all_users' || $target === 'both') {
            $result = $conn->query("SELECT email, first_name as name FROM users WHERE email IS NOT NULL AND email != ''");
            while ($row = $result->fetch_assoc()) {
                $recipient_emails[$row['email']] = $row['name'] ?? 'Chess Player';
            }
        }

        // 2. Fetch from Mailing List table
        if ($target === 'mailing_list' || $target === 'both') {
            $result = $conn->query("SELECT email, name FROM mailing_list");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (!isset($recipient_emails[$row['email']])) {
                        $recipient_emails[$row['email']] = $row['name'] ?? 'Subscriber';
                    }
                }
            }
        }

        // 3. Custom Emails (comma or newline separated)
        if ($target === 'custom' || !empty($custom_emails)) {
            $emails_array = preg_split('/[\s,]+/', $custom_emails);
            foreach ($emails_array as $em) {
                $e = trim($em);
                if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    if (!isset($recipient_emails[$e])) {
                        $recipient_emails[$e] = 'Chess Enthusiast';
                    }
                }
            }
        }

        if (empty($recipient_emails)) {
            $error = "No valid recipient emails found based on your selection.";
        } else {
            $from = "info@ascendingpawnchess.com";
            $headers = "From: ASCENDING PAWN CHESS <$from>\r\n";
            $headers .= "Reply-To: $from\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $sentCount = 0;
            $failCount = 0;

            foreach ($recipient_emails as $email => $name) {
                $emailBody = "
                <html>
                <body style='font-family: sans-serif; background-color: #f7fafc; padding: 40px;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <h1 style='color: #80D200; margin: 0; font-size: 24px;'>ASCENDING PAWN CHESS</h1>
                        </div>
                        <h2 style='color: #1a202c; border-bottom: 2px solid #80D200; padding-bottom: 10px;'>$subject</h2>
                        <div style='color: #4a5568; line-height: 1.8; font-size: 16px;'>
                            <p>Hi $name,</p>
                            $content
                        </div>
                        <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;'>
                            <p style='font-size: 12px; color: #718096;'>
                                You are receiving this marketing email from ASCENDING PAWN CHESS.<br>
                                &copy; " . date('Y') . " ASCENDING PAWN CHESS. All rights reserved.
                            </p>
                        </div>
                    </div>
                </body>
                </html>";

                if (mail($email, $subject, $emailBody, $headers, "-f $from")) {
                    $sentCount++;
                } else {
                    // Fallback to basic mail if envelope-from fails depending on server config
                    if(mail($email, $subject, $emailBody, $headers)) {
                         $sentCount++;
                    } else {
                         $failCount++;
                    }
                }
            }

            if ($failCount > 0 && $sentCount == 0) {
                $error = "Failed to send emails. Please check server email configuration.";
            } else {
                $message = "Marketing campaign sent successfully to $sentCount recipients." . ($failCount > 0 ? " ($failCount failed)" : "");
            }
        }
    }
}

// Handle adding to mailing list manually
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subscriber'])) {
    $sub_email = $conn->real_escape_string(trim($_POST['sub_email']));
    $sub_name = $conn->real_escape_string(trim($_POST['sub_name']));
    
    if (filter_var($sub_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO mailing_list (email, name) VALUES (?, ?)");
        $stmt->bind_param("ss", $sub_email, $sub_name);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Subscriber added successfully.";
        } else {
            $error = "Subscriber already exists or invalid data.";
        }
    } else {
        $error = "Please provide a valid email address.";
    }
}

// Fetch mailing list counts
$listCountResult = $conn->query("SELECT COUNT(*) as count FROM mailing_list");
$listCount = $listCountResult ? $listCountResult->fetch_assoc()['count'] : 0;

$usersCountResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE email IS NOT NULL AND email != ''");
$usersCount = $usersCountResult ? $usersCountResult->fetch_assoc()['count'] : 0;
?>

<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-black">Marketing Campaigns</h1>
            <p class="text-slate-500">Create and push email marketing campaigns to your audiences.</p>
        </div>
        <div class="flex gap-4">
             <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-4 py-2 rounded-2xl">
                 <span class="text-xs text-slate-400 font-bold uppercase block">Mailing List Size</span>
                 <span class="text-xl font-black text-brandOrange"><?php echo $listCount; ?></span>
             </div>
             <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-4 py-2 rounded-2xl">
                 <span class="text-xs text-slate-400 font-bold uppercase block">Platform Users</span>
                 <span class="text-xl font-black text-brandGreen"><?php echo $usersCount; ?></span>
             </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Campaign Form -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden glass p-8">
                <h2 class="text-xl font-bold mb-6 border-b border-slate-200 dark:border-slate-800 pb-4">Create New Campaign</h2>
                
                <form action="marketing.php" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Subject / Title</label>
                        <input type="text" name="subject" required
                            class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all"
                            placeholder="e.g. Summer Chess Bootcamp Registration is Open!">
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Campaign Content (HTML Supported)</label>
                        <textarea name="message" rows="8" required
                            class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all font-mono text-sm"
                            placeholder="<p>Write your amazing marketing copy here...</p>"></textarea>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Audience Target</label>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <input type="radio" name="target" value="both" checked class="form-radio text-brandGreen w-5 h-5">
                                <span class="ml-3 font-semibold text-sm">Everyone<br><span class="text-xs text-slate-500 font-normal">Users & Mailing List</span></span>
                            </label>
                            
                            <label class="flex items-center p-4 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <input type="radio" name="target" value="all_users" class="form-radio text-brandGreen w-5 h-5">
                                <span class="ml-3 font-semibold text-sm">Platform Users Only</span>
                            </label>

                            <label class="flex items-center p-4 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <input type="radio" name="target" value="mailing_list" class="form-radio text-brandGreen w-5 h-5">
                                <span class="ml-3 font-semibold text-sm">Mailing List Only</span>
                            </label>

                            <label class="flex items-center p-4 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <input type="radio" name="target" value="custom" class="form-radio text-brandGreen w-5 h-5">
                                <span class="ml-3 font-semibold text-sm">Custom Emails</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Additional Custom Emails (Optional)</label>
                        <textarea name="custom_emails" rows="3"
                            class="w-full px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all"
                            placeholder="john@example.com, jane@domain.com (comma or newline separated)"></textarea>
                    </div>

                    <button type="submit" class="w-full py-4 bg-brandGreen text-white font-black uppercase tracking-widest rounded-2xl hover:bg-brandOrange transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Send Campaign
                    </button>
                    <p class="text-center text-xs text-slate-500 mt-2">Emails will be sent from <strong>info@ascendingpawnchess.com</strong></p>
                </form>
            </div>
        </div>

        <!-- Right: Mailing List Management -->
        <div class="space-y-8">
            <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden glass p-8">
                <h2 class="text-xl font-bold mb-6 border-b border-slate-200 dark:border-slate-800 pb-4">Add to Mailing List</h2>
                <form action="marketing.php" method="POST" class="space-y-4">
                    <input type="hidden" name="add_subscriber" value="1">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Full Name</label>
                        <input type="text" name="sub_name"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all"
                            placeholder="Jane Doe">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Email Address *</label>
                        <input type="email" name="sub_email" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 dark:bg-slate-800/50 focus:ring-2 focus:ring-brandGreen outline-none transition-all"
                            placeholder="jane@example.com">
                    </div>
                    <button type="submit" class="w-full py-3 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition-colors">
                        Add Subscriber
                    </button>
                </form>
            </div>

            <!-- Previews / Logs could go here -->
            <div class="bg-brandGreen/10 border border-brandGreen/20 rounded-[32px] p-8 text-brandGreen">
                 <div class="flex items-center gap-4 mb-4">
                     <i class="fas fa-lightbulb text-3xl"></i>
                     <h3 class="font-bold text-lg">Marketing Tips</h3>
                 </div>
                 <ul class="space-y-2 text-sm">
                     <li>&bull; Keep subject lines under 60 characters.</li>
                     <li>&bull; Use HTML formatting for bold headlines and links.</li>
                     <li>&bull; Personalize your campaigns for better engagement.</li>
                     <li>&bull; Always test with a 'Custom Email' first!</li>
                 </ul>
            </div>
        </div>
    </div>
</div>

<?php include "admin_footer.php"; ?>
