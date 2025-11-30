<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../session.php';

// Check if logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check CSRF token
if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF token missing']);
    exit;
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['profile_picture'];
$user_id = $_SESSION['user_id'];

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
    exit;
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
    exit;
}

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/../../assets/uploads/profiles/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
$file_path = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Delete old profile picture if it exists and is not the default
$db = Database::getInstance();
$stmt = $db->prepare("SELECT profile_picture_url FROM users WHERE user_id = ? LIMIT 1", 'i', [$user_id]);
if ($stmt) {
    $stmt->execute();
    $user_result = $stmt->get_result();
    if ($user_result) {
        $user = $user_result->fetch_assoc();
        if ($user && $user['profile_picture_url'] && $user['profile_picture_url'] !== 'default-avatar.png') {
            $old_file = __DIR__ . '/../../assets/uploads/profiles/' . basename($user['profile_picture_url']);
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
        }
    }
    $stmt->close();
}

// Update database
$relative_path = 'assets/uploads/profiles/' . $filename;
$updateStmt = $db->prepare("UPDATE users SET profile_picture_url = ? WHERE user_id = ?", 'si', [$relative_path, $user_id]);
if ($updateStmt) {
    $updateStmt->execute();
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully',
        'profile_picture_url' => $relative_path
    ]);
    $updateStmt->close();
} else {
    // Delete uploaded file if database update fails
    @unlink($file_path);
    echo json_encode(['success' => false, 'message' => 'Failed to update profile in database']);
}
?>
