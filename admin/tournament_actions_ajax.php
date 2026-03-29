<?php
session_start();
include "../includes/db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'kick') {
    $regId = (int)$_POST['registration_id'];
    
    $stmt = $conn->prepare("DELETE FROM tournament_registrations WHERE id = ?");
    $stmt->bind_param("i", $regId);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Player removed successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove player']);
    }
    $stmt->close();
} 
elseif ($action === 'add') {
    $tid = (int)$_POST['tournament_id'];
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $category = $_POST['category'];
    
    $stmt = $conn->prepare("INSERT INTO tournament_registrations (tournament_id, full_name, email, phone, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $tid, $name, $email, $phone, $category);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Player added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add player: ' . $conn->error]);
    }
    $stmt->close();
} 
else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
