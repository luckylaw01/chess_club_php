<?php
session_start();
require_once "includes/db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get the notification ID from request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'No notification ID provided']);
    exit();
}

$notifId = intval($data['id']);
$userId = $_SESSION['id'];

// Mark notification as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notifId, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
}

$stmt->close();
$conn->close();
?>
