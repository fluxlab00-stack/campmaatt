<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/image_utils.php';

// Simple handler for posting an item. Extend with validation and DB insertion as needed.
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL);
    exit;
}

$title = trim($_POST['title'] ?? '');
$price = trim($_POST['price'] ?? '');
$description = trim($_POST['description'] ?? '');

// Basic validation
$errors = [];
if ($title === '') $errors[] = 'Title is required.';
if ($price === '') $errors[] = 'Price is required.';

// Handle file upload
$uploadedFilePath = null;
if (!empty($_FILES['primary_image']) && $_FILES['primary_image']['error'] === UPLOAD_ERR_OK) {
    $tmpPath = $_FILES['primary_image']['tmp_name'];
    $origName = basename($_FILES['primary_image']['name']);
    $mime = mime_content_type($tmpPath);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
        $errors[] = 'Unsupported image type.';
    } else {
        // Ensure upload directory
        $uploadDir = rtrim(UPLOAD_PATH, '/') . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Generate unique filename
        $ext = pathinfo($origName, PATHINFO_EXTENSION);
        $newName = 'listing_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destPath = $uploadDir . $newName;

        // Move uploaded file
        if (!move_uploaded_file($tmpPath, $destPath)) {
            $errors[] = 'Failed to move uploaded file.';
        } else {
            // Compress the image in-place (overwrite)
            $compressedPath = $uploadDir . 'c_' . $newName;
            $ok = compressImage($destPath, $compressedPath);
            if ($ok) {
                // remove original and set uploadedFilePath to compressed
                @unlink($destPath);
                $uploadedFilePath = $compressedPath;
            } else {
                // If compression failed, keep original
                $uploadedFilePath = $destPath;
            }
        }
    }
}

if (!empty($errors)) {
    // Store errors in session and redirect back
    $_SESSION['form_errors'] = $errors;
    header('Location: ' . SITE_URL);
    exit;
}

// TODO: Insert listing into database and associate $uploadedFilePath as primary image
// Example (pseudo):
// $stmt = $db->prepare('INSERT INTO listings (user_id, title, price, description, posted_at) VALUES (?, ?, ?, ?, NOW())', 'isss', [$userId, $title, $price, $description]);
// After insert, save $uploadedFilePath into listing_images table with is_primary=1

// For now redirect to homepage with success message
$_SESSION['flash'] = 'Listing posted successfully (placeholder).';
header('Location: ' . SITE_URL);
exit;
