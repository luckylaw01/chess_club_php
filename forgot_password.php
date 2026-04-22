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
        
        // Simulating a token generation
        $token = bin2hex(random_bytes(16));
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token&email=" . urlencode($email);
        
        // Storing simulated token in session for this demo
        $_SESSION['reset_token'] = [
            'token' => $token,
            'email' => $email,
            'expiry' => time() + 3600 // 1 hour
        ];
        
        $message = "A reset link has been (simulated) sent to your email: <strong>" . htmlspecialchars($email) . "</strong>. <br><br><a href='$reset_link' class='text-brandGreen font-bold underline'>Click here to reset your password (Demo Link)</a>";
        $messageType = 'success';
    } else {
        $message = "We couldn't find an account with that email address.";
        $messageType = 'error';
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