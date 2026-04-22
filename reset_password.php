<?php
session_start();
require_once "includes/db_connect.php";
$pageTitle = 'RESET PASSWORD';
include 'includes/header.php';

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

$message = '';
$messageType = '';
$showForm = false;

// Token validation (Simulated for this demo using session)
if (isset($_SESSION['reset_token']) && 
    $_SESSION['reset_token']['token'] === $token && 
    $_SESSION['reset_token']['email'] === $email && 
    $_SESSION['reset_token']['expiry'] > time()) {
    $showForm = true;
} else {
    $message = "Your reset token is invalid or has expired.";
    $messageType = 'error';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $showForm) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = 'error';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email_escaped = mysqli_real_escape_string($conn, $email);
        
        $query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email_escaped'";
        if (mysqli_query($conn, $query)) {
            $message = "Password updated successfully!";
            $messageType = 'success';
            $showForm = false; // Hide form after success
            unset($_SESSION['reset_token']); // Invalidate session token
        } else {
            $message = "Database error. Please try again.";
            $messageType = 'error';
        }
    }
}
?>

<div class="pt-32 pb-24 px-6 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full animate-slide-up">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-10 rounded-[40px] shadow-2xl">
            
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-brandGreen/10 rounded-[28px] flex items-center justify-center mx-auto mb-6 shadow-inner ring-1 ring-brandGreen/20">
                    <i class="fas fa-lock text-brandGreen text-3xl"></i>
                </div>
                <h1 class="text-3xl font-black uppercase tracking-tight mb-3">Reset <span class="text-brandGreen">Password</span></h1>
                <p class="text-slate-500 text-sm font-bold uppercase tracking-widest">Update your strategic access</p>
            </div>

            <?php if ($message): ?>
                <div class="mb-8 p-6 <?php echo $messageType === 'success' ? 'bg-brandGreen/10 border-brandGreen/20 text-brandGreen' : 'bg-red-500/10 border-red-500/20 text-red-500'; ?> border rounded-3xl text-sm font-bold flex items-center gap-4">
                    <i class="fas <?php echo $messageType === 'success' ? 'fa-check' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
                <?php if ($messageType === 'success'): ?>
                    <a href="login.php" class="w-full py-5 bg-brandGreen text-white font-black uppercase tracking-widest rounded-[28px] text-center block shadow-xl shadow-brandGreen/20">Login Now</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($showForm): ?>
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">New Password</label>
                        <div class="relative">
                            <i class="fas fa-shield-alt absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full pl-12 pr-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Confirm New Password</label>
                        <div class="relative">
                            <i class="fas fa-check-double absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="confirm_password" required placeholder="••••••••"
                                class="w-full pl-12 pr-6 py-4 rounded-3xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen outline-none transition-all font-bold">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-5 bg-slate-900 dark:bg-brandGreen text-white font-black uppercase tracking-widest rounded-[28px] hover:bg-brandNeonGreen hover:scale-[1.02] active:scale-95 transition-all shadow-xl shadow-brandGreen/20 flex items-center justify-center gap-3">
                        Update Password
                        <i class="fas fa-save text-xs opacity-50"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>