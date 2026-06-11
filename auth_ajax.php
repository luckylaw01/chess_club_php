<?php
header('Content-Type: application/json');
session_start();

require_once 'includes/db_connect.php';

function auth_json_response(string $status, string $message, ?array $user = null): void
{
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'user' => $user
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response('error', 'Invalid request method.');
}

$action = strtolower(trim((string)($_POST['action'] ?? '')));

if ($action === 'login') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = trim((string)($_POST['password'] ?? ''));

    if ($email === '' || $password === '') {
        auth_json_response('error', 'Please enter both email and password.');
    }

    $stmt = $conn->prepare('SELECT id, username, email, password, first_name, last_name, role, profile_picture FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        auth_json_response('error', 'Database error. Please try again later.');
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_picture'] = $user['profile_picture'];

        // Prepare safe user info for frontend pre-fill
        $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
        
        // Fetch remaining profile details for form pre-filling
        $profileStmt = $conn->prepare('SELECT phone_number, date_of_birth, gender, club_type, club_name FROM users WHERE id = ? LIMIT 1');
        $phone = ''; $dob = ''; $gender = ''; $clubType = 'chess'; $clubName = '';
        if ($profileStmt) {
            $profileStmt->bind_param('i', $user['id']);
            $profileStmt->execute();
            $profileResult = $profileStmt->get_result();
            if ($profileRow = $profileResult->fetch_assoc()) {
                $phone = $profileRow['phone_number'] ?? '';
                $dob = $profileRow['date_of_birth'] ?? '';
                $gender = $profileRow['gender'] ?? '';
                $clubType = $profileRow['club_type'] ?? 'chess';
                $clubName = $profileRow['club_name'] ?? '';
            }
            $profileStmt->close();
        }

        auth_json_response('success', 'Logged in successfully!', [
            'id' => (int)$user['id'],
            'full_name' => $fullName,
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone_number' => $phone,
            'date_of_birth' => $dob,
            'gender' => $gender,
            'club_type' => $clubType,
            'club_name' => $clubName
        ]);
    } else {
        auth_json_response('error', 'Invalid email or password.');
    }

} elseif ($action === 'register') {
    $firstName = trim((string)($_POST['first_name'] ?? ''));
    $lastName = trim((string)($_POST['last_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone_number'] ?? ''));
    $dob = trim((string)($_POST['date_of_birth'] ?? ''));
    $gender = trim((string)($_POST['gender'] ?? ''));
    $password = trim((string)($_POST['password'] ?? ''));
    $clubType = trim((string)($_POST['club_type'] ?? 'chess'));
    $clubName = trim((string)($_POST['club_name'] ?? 'None'));

    if ($firstName === '' || $lastName === '' || $email === '' || $phone === '' || $dob === '' || $gender === '' || $password === '') {
        auth_json_response('error', 'Please complete all required fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        auth_json_response('error', 'Please enter a valid email address.');
    }

    if (strlen($password) < 6) {
        auth_json_response('error', 'Password must have at least 6 characters.');
    }

    // Check if email already exists
    $checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    if ($checkStmt) {
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            auth_json_response('error', 'This email is already registered.');
        }
        $checkStmt->close();
    }

    // Prepare insert query matching schema
    $username = strtolower($firstName . '.' . $lastName);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $fullName = $firstName . ' ' . $lastName;
    $role = 'user';

    $insertStmt = $conn->prepare('INSERT INTO users (username, email, password, first_name, last_name, full_name, phone_number, date_of_birth, gender, club_type, club_name, elo_rating, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1200, ?)');
    if (!$insertStmt) {
        auth_json_response('error', 'Database insertion setup failed. Please try again.');
    }

    $insertStmt->bind_param('ssssssssssss', $username, $email, $hashedPassword, $firstName, $lastName, $fullName, $phone, $dob, $gender, $clubType, $clubName, $role);
    
    if ($insertStmt->execute()) {
        $newUserId = (int)$insertStmt->insert_id;
        $insertStmt->close();

        // Autologin after registration
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = $newUserId;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['role'] = $role;
        $_SESSION['profile_picture'] = null;

        auth_json_response('success', 'Account created and logged in successfully!', [
            'id' => $newUserId,
            'full_name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone_number' => $phone,
            'date_of_birth' => $dob,
            'gender' => $gender,
            'club_type' => $clubType,
            'club_name' => $clubName
        ]);
    } else {
        $errorMsg = $insertStmt->error;
        $insertStmt->close();
        auth_json_response('error', 'Registration failed: ' . $errorMsg);
    }

} else {
    auth_json_response('error', 'Invalid action specified.');
}
