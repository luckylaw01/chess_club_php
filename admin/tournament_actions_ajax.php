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
    
    $stmt = $conn->prepare("INSERT INTO tournament_registrations (tournament_id, registration_type, full_name, email, phone, participant_count, payment_status, status) VALUES (?, 'individual', ?, ?, ?, 1, 'paid', 'confirmed')");
    $stmt->bind_param("isss", $tid, $name, $email, $phone);
    
    if ($stmt->execute()) {
        $registrationId = $conn->insert_id;
        $participantStmt = $conn->prepare("INSERT INTO tournament_registration_participants (registration_id, full_name, email, phone, category, is_primary) VALUES (?, ?, ?, ?, ?, 1)");
        if ($participantStmt) {
            $participantStmt->bind_param("issss", $registrationId, $name, $email, $phone, $category);
            $participantStmt->execute();
            $participantStmt->close();
        }
        echo json_encode(['status' => 'success', 'message' => 'Player added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add player: ' . $conn->error]);
    }
    $stmt->close();
} 
elseif ($action === 'update_participant') {
    $participantId = (int)$_POST['participant_id'];
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $clubType = trim($_POST['club_type'] ?? 'chess');
    $clubName = trim($_POST['club_name'] ?? '');
    $category = trim($_POST['category'] ?? 'Open');
    $guardianPhone = trim($_POST['guardian_phone'] ?? '');

    $stmt = $conn->prepare("UPDATE tournament_registration_participants SET full_name = ?, email = ?, phone = ?, date_of_birth = ?, gender = ?, club_type = ?, club_name = ?, category = ?, guardian_phone = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("sssssssssi", $fullName, $email, $phone, $dateOfBirth, $gender, $clubType, $clubName, $category, $guardianPhone, $participantId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Participant updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update participant']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare update']);
    }
}
elseif ($action === 'delete_participant') {
    $participantId = (int)$_POST['participant_id'];
    $stmt = $conn->prepare("DELETE FROM tournament_registration_participants WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $participantId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Participant removed successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove participant']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare delete']);
    }
} 
else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
