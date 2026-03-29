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

if ($_SERVER["REQUEST_METHOD"] == "GET" && $action == "list") {
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $role = $conn->real_escape_string($_GET['role'] ?? '');
    $edit_id = (int)($_GET['edit_id'] ?? 0);

    $sql = "SELECT id, username, email, first_name, last_name, role, elo_rating, created_at FROM users WHERE 1=1";

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
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $role = $conn->real_escape_string($_POST['role']);
    $elo_rating = (int)$_POST['elo_rating'];
    $password = $_POST['password'] ?? '';

    $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', role = '$role', elo_rating = $elo_rating";
    
    // Only update password if provided
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = '$hashed'";
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
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if user already exists
    $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists.']);
        exit;
    }

    $sql = "INSERT INTO users (username, email, password, first_name, last_name, role, elo_rating, membership_status) 
            VALUES ('$username', '$email', '$password', '$first_name', '$last_name', '$role', $elo_rating, 'active')";

    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'User created successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == "delete") {
    $id = (int)$_POST['id'];

    // Prevent self-deletion
    if ($id == $_SESSION['id']) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account.']);
        exit;
    }

    $sql = "DELETE FROM users WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit;
?>