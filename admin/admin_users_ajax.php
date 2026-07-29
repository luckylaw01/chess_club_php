<?php
session_start();
header('Content-Type: application/json');

// Security Check: Only admins allowed
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require_once "../includes/db_connect.php";

$action = $_GET['action'] ?? null;

function generateRandomPassword($length = 12) {
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $special = '!@#$%&*';
    
    $password = '';
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $special[rand(0, strlen($special) - 1)];
    
    $allChars = $lowercase . $uppercase . $numbers . $special;
    for ($i = 0; $i < $length - 4; $i++) {
        $password .= $allChars[rand(0, strlen($allChars) - 1)];
    }
    
    return str_shuffle($password);
}

function sendPasswordEmail($email, $name, $password, $isNewAccount = false) {
    $from = "admin@ascendingpawnchess.com";
    $subject = $isNewAccount ? "Welcome to Ascending Pawn Chess Club!" : "Your New Account Password";
    $headers = "From: Ascending Pawn Chess Club <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $title = $isNewAccount ? "Welcome to the Club!" : "Password Reset";
    $intro = $isNewAccount 
        ? "Your account has been created by the administrator. Welcome to Ascending Pawn Chess Club!" 
        : "The administrator has generated a new password for your account.";

    $emailBody = "
    <html>
    <body style='font-family: sans-serif; background-color: #f7fafc; padding: 40px;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #80D200; margin: 0; font-size: 24px;'>Ascending Pawn</h1>
            </div>
            <h2 style='color: #1a202c; border-bottom: 2px solid #80D200; padding-bottom: 10px;'>$title</h2>
            <div style='color: #4a5568; line-height: 1.8; font-size: 16px;'>
                <p>Hello " . htmlspecialchars($name) . ",</p>
                <p>$intro</p>
                <p>Here are your login details:</p>
                <div style='background-color: #f7fafc; padding: 20px; border-radius: 10px; border: 1px solid #edf2f7; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                    <p style='margin: 5px 0;'><strong>Password:</strong> <code style='font-size: 18px; color: #e53e3e; background-color: #fffaf0; padding: 2px 6px; border-radius: 4px; border: 1px dashed #fbd38d;'>" . htmlspecialchars($password) . "</code></p>
                </div>
                <p>We recommend logging in and changing your password in your settings as soon as possible.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='https://ascendingpawnchess.com/login.php' style='background-color: #80D200; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Login to Your Dashboard</a>
                </div>
            </div>
            <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;'>
                <p style='font-size: 12px; color: #718096;'>
                    &copy; " . date('Y') . " Ascending Pawn Chess Club. All rights reserved.
                </p>
            </div>
        </div>
    </body>
    </html>";

    return mail($email, $subject, $emailBody, $headers, "-f $from");
}

function handleProfilePictureUpload($userId) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profile_pictures/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileInfo = pathinfo($_FILES['profile_picture']['name']);
        $ext = strtolower($fileInfo['extension']);
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowedExts)) {
            $newFileName = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                return 'uploads/profile_pictures/' . $newFileName;
            }
        }
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && $action == "list") {
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $role = $conn->real_escape_string($_GET['role'] ?? '');
    $edit_id = (int)($_GET['edit_id'] ?? 0);

    $sql = "SELECT id, username, email, first_name, last_name, role, elo_rating, created_at, profile_picture FROM users WHERE 1=1";

    if ($edit_id > 0) {
        $sql .= " AND id = $edit_id";
    }

    if (!empty($search)) {
        $sql .= " AND (username LIKE '%$search%' OR email LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%')";
    }

    if (!empty($role)) {
        $sql .= " AND role = '$role'";
    }

    $sql .= " ORDER BY created_at DESC";

    $result = $conn->query($sql);
    $users = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'users' => $users]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == "update") {
    $id = (int)$_POST['id'];
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $role = $conn->real_escape_string($_POST['role']);
    $elo_rating = (int)$_POST['elo_rating'];

    // Check if new username or email is taken by another user
    $check = $conn->query("SELECT id FROM users WHERE (username = '$username' OR email = '$email') AND id != $id");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists for another user.']);
        exit;
    }

    $sql = "UPDATE users SET username = '$username', email = '$email', first_name = '$first_name', last_name = '$last_name', role = '$role', elo_rating = $elo_rating";

    $uploadedPic = handleProfilePictureUpload($id);
    if ($uploadedPic) {
        $sql .= ", profile_picture = '$uploadedPic'";
        if ($id == $_SESSION["id"]) {
            $_SESSION["profile_picture"] = $uploadedPic;
        }
    }
    
    $sql .= " WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == "create") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $role = $conn->real_escape_string($_POST['role']);
    $elo_rating = (int)$_POST['elo_rating'];
    
    // Automate password creation and emailing
    $generatedPassword = generateRandomPassword();
    $password = password_hash($generatedPassword, PASSWORD_DEFAULT);

    // Check if user already exists
    $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists.']);
        exit;
    }

    $sql = "INSERT INTO users (username, email, password, first_name, last_name, role, elo_rating, membership_status) 
            VALUES ('$username', '$email', '$password', '$first_name', '$last_name', '$role', $elo_rating, 'active')";

    if ($conn->query($sql)) {
        $newId = $conn->insert_id;
        $uploadedPic = handleProfilePictureUpload($newId);
        if ($uploadedPic) {
            $conn->query("UPDATE users SET profile_picture = '$uploadedPic' WHERE id = $newId");
        }
        
        $name = trim($first_name . ' ' . $last_name);
        if (empty($name)) {
            $name = $username;
        }
        
        sendPasswordEmail($email, $name, $generatedPassword, true);
        echo json_encode(['status' => 'success', 'message' => 'User created and password emailed successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == "send_password") {
    $id = (int)$_POST['id'];
    
    // Security check: cannot reset own password from user list
    if ($id == $_SESSION['id']) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot reset your own password here.']);
        exit;
    }
    
    // Fetch user details
    $userQuery = $conn->query("SELECT first_name, last_name, email FROM users WHERE id = $id");
    if ($userQuery && $userQuery->num_rows > 0) {
        $user = $userQuery->fetch_assoc();
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        if (empty($name)) {
            $name = 'Chess Member';
        }
        $email = $user['email'];
        
        // Generate new password
        $newPassword = generateRandomPassword();
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user password in DB
        $updateSql = "UPDATE users SET password = '$hashed' WHERE id = $id";
        if ($conn->query($updateSql)) {
            // Send email
            if (sendPasswordEmail($email, $name, $newPassword, false)) {
                echo json_encode(['status' => 'success', 'message' => 'New password sent to user successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Password updated in database, but sending email failed.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == "delete") {
    $id = (int)$_POST['id'];

    // Prevent self-deletion
    if (isset($_SESSION['id']) && $id == $_SESSION['id']) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Unassign user as coach from academy courses
        $conn->query("UPDATE academy_courses SET coach_id = NULL WHERE coach_id = $id");

        // 2. Disassociate user from orders (ensure column allows NULL if needed)
        @$conn->query("ALTER TABLE orders MODIFY user_id INT NULL");
        $conn->query("UPDATE orders SET user_id = NULL WHERE user_id = $id");

        // 3. Disassociate user from tournament registrations & participants
        $conn->query("UPDATE tournament_registrations SET user_id = NULL WHERE user_id = $id");
        $conn->query("UPDATE tournament_registration_participants SET user_id = NULL WHERE user_id = $id");

        // 4. Disassociate user from payments & donations
        $conn->query("UPDATE payments SET user_id = NULL WHERE user_id = $id");
        $conn->query("UPDATE donations SET user_id = NULL WHERE user_id = $id");

        // 5. Disassociate user from notification content created by them
        $conn->query("UPDATE notification_content SET created_by = NULL WHERE created_by = $id");

        // 6. Delete user-specific records that should cascade
        $conn->query("DELETE FROM course_enrollments WHERE user_id = $id");
        $conn->query("DELETE FROM student_assignments WHERE user_id = $id");
        $conn->query("DELETE FROM notifications WHERE user_id = $id");

        // 7. Delete the user record
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit;
?>