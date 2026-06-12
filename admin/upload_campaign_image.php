<?php
session_start();
require_once "../includes/db_connect.php";

// Check if user is admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    header('Content-Type: application/json');
    $file = $_FILES['image'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Upload error code: ' . $file['error']]);
        exit();
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['error' => 'Only JPG, PNG, GIF, and WEBP images are allowed.']);
        exit();
    }

    $uploadDir = __DIR__ . '/../uploads/campaign_images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Build absolute URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        
        // Resolve directory path
        $dir = str_replace('\\', '/', dirname($_SERVER['REQUEST_URI'])); // e.g. /chess_club/admin or /admin
        $base = ($dir === '/') ? '' : str_replace('\\', '/', dirname($dir));
        if ($base === '/') {
            $base = '';
        }
        
        $imageUrl = $protocol . $host . $base . '/uploads/campaign_images/' . $filename;

        echo json_encode(['url' => $imageUrl]);
    } else {
        echo json_encode(['error' => 'Failed to move uploaded file.']);
    }
    exit();
} else {
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No image uploaded.']);
    exit();
}
