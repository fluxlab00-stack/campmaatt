<?php
/**
 * Delete Listing
 * Removes a listing and its associated data
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$listingId = intval($input['listing_id'] ?? 0);
$userId = getCurrentUserId();

if ($listingId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid listing']);
    exit();
}

$db = Database::getInstance();

// Verify ownership
$stmt = $db->prepare(
    "SELECT user_id FROM listings WHERE listing_id = ?",
    "i",
    [$listingId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$listing = $result->fetch_assoc();
$stmt->close();

if (!$listing || $listing['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get all images to delete
$imagesToDelete = [];
$stmt = $db->prepare(
    "SELECT image_url FROM listing_images WHERE listing_id = ?",
    "i",
    [$listingId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $imagesToDelete[] = $row['image_url'];
    }
    $stmt->close();
}

// Begin transaction
$db->beginTransaction();

try {
    // Delete listing (CASCADE will handle related records)
    $stmt = $db->prepare(
        "DELETE FROM listings WHERE listing_id = ?",
        "i",
        [$listingId]
    );
    
    if (!$stmt) {
        throw new Exception("Failed to delete listing");
    }
    
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $db->commit();
    
    // Delete image files
    foreach ($imagesToDelete as $imageUrl) {
        deleteFile($imageUrl);
    }
    
    echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
    
} catch (Exception $e) {
    $db->rollback();
    error_log("Listing deletion error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to delete listing']);
}
