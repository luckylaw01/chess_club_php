<?php
header('Content-Type: application/json');
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_id = isset($_POST['tournament_id']) ? (int)$_POST['tournament_id'] : 0;
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : 'Open';
    $user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

    if (empty($tournament_id) || empty($full_name) || empty($email) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit;
    }

    // Check if tournament exists and is upcoming
    $stmt = $conn->prepare("SELECT status FROM tournaments WHERE id = ?");
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tournament = $result->fetch_assoc();

    if (!$tournament) {
        echo json_encode(['status' => 'error', 'message' => 'Tournament not found.']);
        exit;
    }

    if ($tournament['status'] !== 'upcoming') {
        echo json_encode(['status' => 'error', 'message' => 'Registration is closed for this tournament.']);
        exit;
    }

    // Check for existing registration
    $checkStmt = $conn->prepare("SELECT id FROM tournament_registrations WHERE tournament_id = ? AND (email = ? OR phone = ?)");
    $checkStmt->bind_param("iss", $tournament_id, $email, $phone);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already registered for this tournament.']);
        exit;
    }

    // Insert registration
    $insertStmt = $conn->prepare("INSERT INTO tournament_registrations (tournament_id, user_id, full_name, email, phone, category) VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("iissss", $tournament_id, $user_id, $full_name, $email, $phone, $category);

    if ($insertStmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! See you at the tournament.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred during registration. Please try again.']);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>