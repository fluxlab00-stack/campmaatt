<?php
/**
 * Delete Image Process
 * Handles deletion of listing images
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit();
}

// Get image ID
$imageId = intval($_POST['image_id'] ?? 0);

if ($imageId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid image ID.']);
    exit();
}

// Get database instance
$db = Database::getInstance();

// Get current user
$user = getCurrentUser();
$userId = $user['user_id'];

// Fetch image details and verify ownership
$stmt = $db->prepare(
    "SELECT li.image_id, li.listing_id, li.image_url, li.is_primary, l.user_id
     FROM listing_images li
     JOIN listings l ON li.listing_id = l.listing_id
     WHERE li.image_id = ?",
    "i",
    [$imageId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();
$stmt->close();

if (!$image) {
    echo json_encode(['success' => false, 'message' => 'Image not found.']);
    exit();
}

// Verify ownership
if ($image['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this image.']);
    exit();
}

// Check if this is the only image
$stmt = $db->prepare(
    "SELECT COUNT(*) as count FROM listing_images WHERE listing_id = ?",
    "i",
    [$image['listing_id']]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] <= 1) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete the only image. A listing must have at least one image.']);
        exit();
    }
}

// Delete the physical file
$filepath = __DIR__ . '/../../' . $image['image_url'];
if (file_exists($filepath)) {
    unlink($filepath);
}

// Delete from database
$stmt = $db->prepare(
    "DELETE FROM listing_images WHERE image_id = ?",
    "i",
    [$imageId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete image.']);
    exit();
}

$stmt->execute();
$stmt->close();

// If deleted image was primary, make another image primary
if ($image['is_primary']) {
    $db->query("UPDATE listing_images SET is_primary = 1 WHERE listing_id = {$image['listing_id']} ORDER BY image_id ASC LIMIT 1");
}

echo json_encode(['success' => true, 'message' => 'Image deleted successfully.']);
exit();
