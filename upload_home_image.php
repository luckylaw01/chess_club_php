<?php
// Endpoint to accept admin image uploads for the homepage
require_once __DIR__ . '/includes/home_images.php';

header('Content-Type: application/json');

if (!is_admin_user()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$allowedKeys = array_keys(default_home_images());
$key = $_POST['key'] ?? '';
if (!in_array($key, $allowedKeys, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image key']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = ['image/jpeg' => '.jpeg', 'image/jpg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp'];
if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

$ext = $allowed[$mime];
$uploadDir = __DIR__ . '/uploads/homepage';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$safeName = preg_replace('/[^a-z0-9_-]/i', '_', $key) . '_' . date('YmdHis') . $ext;
$dest = $uploadDir . '/' . $safeName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Save relative path
$relativePath = 'uploads/homepage/' . $safeName;
$images = load_home_images();
$images[$key] = $relativePath;
if (!save_home_images($images)) {
    // cleanup
    @unlink($dest);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update config']);
    exit;
}

echo json_encode(['success' => true, 'path' => $relativePath]);
exit;
?>
