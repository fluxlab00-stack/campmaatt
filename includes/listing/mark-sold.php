<?php
/**
 * Mark Listing as Sold
 * Updates listing status to sold
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

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

// Update listing status
$stmt = $db->prepare(
    "UPDATE listings SET status = 'sold', sold_at = NOW() WHERE listing_id = ?",
    "i",
    [$listingId]
);

if ($stmt) {
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Listing marked as sold']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update listing']);
}
