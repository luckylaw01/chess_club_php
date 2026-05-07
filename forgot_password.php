<?php
session_start();
require_once "includes/db_connect.php";
$pageTitle = 'FORGOT PASSWORD';
include 'includes/header.php';

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if user exists
    $query = "SELECT id, full_name FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate a real token and expiry
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 hour
        $reset_link = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token&email=" . urlencode($email);
        
        // Store the token in the database
        $updateQuery = "UPDATE users SET reset_token = '$token', reset_expires = '$expiry' WHERE email = '$email'";
        mysqli_query($conn, $updateQuery);
        
        // Email headers and body
        $from = "admin@ascendingpawnchess.com";
        $subject = "Password Reset Request";
        $headers = "From: Ascending Pawn Chess Club <$from>\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $emailBody = "
        <html>
        <body style='font-family: sans-serif; background-color: #f7fafc; padding: 40px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #80D200; margin: 0; font-size: 24px;'>Ascending Pawn</h1>
                </div>
                <h2 style='color: #1a202c; border-bottom: 2px solid #80D200; padding-bottom: 10px;'>Password Reset</h2>
                <div style='color: #4a5568; line-height: 1.8; font-size: 16px;'>
                    <p>Hello,</p>
                    <p>We received a request to reset the password for the account associated with this email. If you did not make this request, please ignore this email.</p>
                    <p>To reset your password, please click the button below:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$reset_link' style='background-color: #80D200; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Reset Password</a>
                    </div>
                    <p>Or paste this link into your browser:<br>
                    <a href='$reset_link' style='color: #80D200;'>$reset_link</a></p>
                    <p>This link will expire in 1 hour.</p>
                </div>
                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;'>
                    <p style='font-size: 12px; color: #718096;'>
                        &copy; " . date('Y') . " Ascending Pawn Chess Club. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>";

        // Send the email
        mail($email, $subject, $emailBody, $headers, "-f $from");
        
        $message = "If an account with that email exists, a password reset link has been sent to it. Please check your inbox and spam folder.";
        $messageType = 'success';
    } else {
        // Prevent user enumeration by displaying a generic success message
        $message = "If an account with that email exists, a password reset link has been sent to it. Please check your inbox and spam folder.";
        $messageType = 'success';
    }
}
?>

<div class="pt-32 pb-24 px-6 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full animate-slide-up">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-10 rounded-[40px] shadow-2xl relative overflow-hidden">
            <!-- Decorative Element -->
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-brandGreen/5 rounded-full blur-3xl"></div>
            
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-[28px] flex items-center justify-center mx-auto mb-6 shadow-inner ring-1 ring-slate-200 dark:ring-slate-700">
                    <i class="fas fa-key text-brandGreen text-3xl"></i>
                </div>
                <h1 class="text-3xl font-black uppercase tracking-tight mb-3">Forgot <span class="text-brandGreen">Password?</span></h1>
                <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">Master your recovery process</p>
            </div>

            <?php if ($message): ?>
                <div class="mb-8 p-6 <?php echo $messageType === 'success' ? 'bg-brandGreen/10 border-brandGreen/20 text-brandGreen' : 'bg-red-500/10 border-red-500/20 text-red-500'; ?> border rounded-3xl text-sm font-bold leading-relaxed px-8">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Registered Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="email" name="email" required placeholder="grandmaster@chess.com"
                            class="w-full pl-12 pr-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-brandGreen text-white font-black uppercase tracking-widest rounded-[28px] hover:bg-brandNeonGreen hover:scale-[1.02] active:scale-95 transition-all shadow-xl shadow-brandGreen/20 flex items-center justify-center gap-3">
                    Send Reset Link
                    <i class="fas fa-paper-plane text-xs opacity-50"></i>
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="login.php" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-brandGreen transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>