<?php
/**
 * Delete Lost & Found Image
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$userId = getCurrentUserId();
$imageId = intval($_POST['image_id'] ?? 0);
$type = sanitize($_POST['type'] ?? '');

if ($imageId <= 0 || $type !== 'lost_found') {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$db = Database::getInstance();

// Get image details and verify ownership
$stmt = $db->prepare(
    "SELECT lfi.image_url, lf.user_id 
     FROM lost_found_images lfi
     JOIN lost_found lf ON lfi.lost_found_id = lf.lost_found_id
     WHERE lfi.image_id = ?",
    "i",
    [$imageId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();
$stmt->close();

if (!$image) {
    echo json_encode(['success' => false, 'message' => 'Image not found']);
    exit();
}

// Verify ownership
if ($image['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Delete from database
$stmt = $db->prepare("DELETE FROM lost_found_images WHERE image_id = ?", "i", [$imageId]);

if ($stmt && $stmt->execute()) {
    $stmt->close();
    
    // Delete physical file
    $filePath = __DIR__ . '/../../' . $image['image_url'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
} else {
    if ($stmt) {
        $stmt->close();
    }
    echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
}
