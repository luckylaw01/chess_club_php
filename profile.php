<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$pageTitle = "My Profile";
include "includes/header.php";
include "includes/db_connect.php";

$user_id = $_SESSION["id"];
$message = '';
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"] ?? '');
    $last_name = trim($_POST["last_name"] ?? '');
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    
    $full_name = trim($first_name . ' ' . $last_name);

    // Profile picture upload
    $profile_picture = $_SESSION["profile_picture"] ?? '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileInfo = pathinfo($_FILES['profile_picture']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            $newFileName = 'user_' . $user_id . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $profile_picture = $destination;
                $_SESSION["profile_picture"] = $profile_picture;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image format. Allowed: JPG, PNG, GIF, WEBP.";
        }
    }

    if (empty($error)) {
        // Handle password change if provided
        $password_sql = "";
        $types = "ssssssi";
        $params = [$first_name, $last_name, $full_name, $username, $email, $profile_picture, $user_id];
        
        if (!empty($_POST["new_password"])) {
            if ($_POST["new_password"] === $_POST["confirm_password"]) {
                $hashed_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
                $password_sql = ", password = ?";
                $types = "sssssssi";
                $params = [$first_name, $last_name, $full_name, $username, $email, $profile_picture, $hashed_password, $user_id];
            } else {
                $error = "Passwords do not match.";
            }
        }

        if (empty($error)) {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, full_name = ?, username = ?, email = ?, profile_picture = ?" . $password_sql . " WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    $message = "Profile updated successfully.";
                    $_SESSION["first_name"] = $first_name;
                    $_SESSION["last_name"] = $last_name;
                    $_SESSION["username"] = $username;
                } else {
                    $error = "Oops! Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    }
}

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>

<section class="pt-32 pb-20 px-6 font-sans min-h-screen">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-5xl font-black mb-8 text-slate-900 dark:text-white uppercase tracking-tight">Manage <span class="text-brandGreen">Profile</span></h1>

        <?php if (!empty($message)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8 rounded-r">
                <p class="font-bold"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded-r">
                <p class="font-bold"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900/50 p-8 md:p-10 rounded-[40px] border border-slate-200 dark:border-slate-800 shadow-2xl relative">
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                
                <div class="flex flex-col md:flex-row gap-10">
                    <!-- Profile Picture Column -->
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <div class="relative mb-6 group cursor-pointer" onclick="document.getElementById('profile_picture').click()">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img id="preview_image" src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" class="w-48 h-48 rounded-[2rem] object-cover shadow-xl border-4 border-white dark:border-slate-800">
                            <?php else: ?>
                                <div id="preview_placeholder" class="w-48 h-48 rounded-[2rem] bg-gradient-to-br from-brandGreen to-brandGold flex items-center justify-center text-white text-6xl font-black shadow-xl border-4 border-white dark:border-slate-800">
                                    <?php echo strtoupper(substr($user["first_name"], 0, 1)); ?>
                                </div>
                                <img id="preview_image" src="" alt="Profile" class="w-48 h-48 rounded-[2rem] object-cover shadow-xl border-4 border-white dark:border-slate-800 hidden">
                            <?php endif; ?>
                            
                            <div class="absolute inset-0 bg-black/50 rounded-[2rem] flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <i class="fas fa-camera text-3xl mb-2"></i>
                                <span class="text-sm font-bold uppercase tracking-widest">Change</span>
                            </div>
                        </div>
                        <input type="file" id="profile_picture" name="profile_picture" class="hidden" accept="image/*" onchange="previewFile()">
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest text-center mt-2">Allowed: JPG, PNG, GIF<br>Max size: 2MB</p>
                    </div>

                    <!-- Details Column -->
                    <div class="w-full md:w-2/3 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen text-slate-900 dark:text-white font-bold transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen text-slate-900 dark:text-white font-bold transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">Username</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen text-slate-900 dark:text-white font-bold transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen text-slate-900 dark:text-white font-bold transition-all">
                        </div>

                        <div class="pt-6 border-t border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg font-black uppercase tracking-widest mb-6">Change Password</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">New Password</label>
                                    <input type="password" name="new_password" placeholder="Leave blank to keep current" class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen text-slate-900 dark:text-white font-bold transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-2">Confirm Password</label>
                                    <input type="password" name="confirm_password" placeholder="Confirm new password" class="w-full px-6 py-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-none focus:ring-2 focus:ring-brandGreen text-slate-900 dark:text-white font-bold transition-all">
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 flex justify-end">
                            <button type="submit" class="px-10 py-4 bg-brandGreen text-white font-black uppercase text-sm tracking-widest rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-xl shadow-brandGreen/20">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
function previewFile() {
    const preview = document.getElementById('preview_image');
    const placeholder = document.getElementById('preview_placeholder');
    const file = document.querySelector('input[type=file]').files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
        preview.src = reader.result;
        preview.classList.remove('hidden');
        if(placeholder) placeholder.classList.add('hidden');
    }, false);

    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>

<?php include "includes/footer.php"; ?>