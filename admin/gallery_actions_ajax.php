<?php
session_start();
require_once __DIR__ . '/../includes/home_images.php';

header('Content-Type: application/json');

// Security Check: Only admins allowed
if (!is_admin_user()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        $gallery = get_gallery_images();
        echo json_encode(['status' => 'success', 'gallery' => $gallery]);
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }

        $caption = trim($_POST['caption'] ?? '');
        $alt = trim($_POST['alt'] ?? '');

        if (empty($caption)) {
            $caption = 'Chess Club';
        }
        if (empty($alt)) {
            $alt = $caption;
        }

        if (!isset($_FILES['gallery_image']) || $_FILES['gallery_image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
            exit;
        }

        $file = $_FILES['gallery_image'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => '.jpeg',
            'image/jpg' => '.jpg',
            'image/png' => '.png',
            'image/webp' => '.webp'
        ];

        if (!isset($allowed[$mime])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and WEBP allowed.']);
            exit;
        }

        $ext = $allowed[$mime];
        $uploadDir = __DIR__ . '/../uploads/gallery';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uniqueName = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . $ext;
        $destPath = $uploadDir . '/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded image.']);
            exit;
        }

        $relativePath = 'uploads/gallery/' . $uniqueName;
        $gallery = get_gallery_images();
        $gallery[] = [
            'image' => $relativePath,
            'caption' => $caption,
            'alt' => $alt
        ];

        if (save_gallery_images($gallery)) {
            echo json_encode(['status' => 'success', 'message' => 'Image added successfully.']);
        } else {
            @unlink($destPath);
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save configuration.']);
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }

        $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
        $caption = trim($_POST['caption'] ?? '');
        $alt = trim($_POST['alt'] ?? '');

        $gallery = get_gallery_images();
        if ($index < 0 || $index >= count($gallery)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid index.']);
            exit;
        }

        if (empty($caption)) {
            $caption = 'Chess Club';
        }
        if (empty($alt)) {
            $alt = $caption;
        }

        $gallery[$index]['caption'] = $caption;
        $gallery[$index]['alt'] = $alt;

        if (save_gallery_images($gallery)) {
            echo json_encode(['status' => 'success', 'message' => 'Image updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update configuration.']);
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }

        $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
        $gallery = get_gallery_images();

        if ($index < 0 || $index >= count($gallery)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid index.']);
            exit;
        }

        $item = $gallery[$index];
        $imagePath = $item['image'];

        // If it's an uploaded file in uploads/gallery/, delete it from disk
        if (strpos($imagePath, 'uploads/gallery/') === 0) {
            $fullPath = __DIR__ . '/../' . $imagePath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        array_splice($gallery, $index, 1);
        $gallery = array_values($gallery); // reindex keys

        if (save_gallery_images($gallery)) {
            echo json_encode(['status' => 'success', 'message' => 'Image deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update configuration.']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
exit;
?>
