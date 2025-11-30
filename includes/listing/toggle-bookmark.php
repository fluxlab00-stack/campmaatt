<?php
/**
 * Toggle Bookmark
 * Add or remove item from saved items
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to save items']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? 'listing';
$type = $type === 'lost_found' ? 'lost_found' : 'listing';
$listingId = intval($input['listing_id'] ?? 0);
$itemId = intval($input['item_id'] ?? 0);
$userId = getCurrentUserId();

if ($type === 'listing') {
    if ($listingId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid listing']);
        exit();
    }
} else {
    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item']);
        exit();
    }
}

$db = Database::getInstance();

// Verify that the target exists
if ($type === 'listing') {
    // Verify listing exists
    $stmt = $db->prepare(
        "SELECT listing_id FROM listings WHERE listing_id = ?",
        "i",
        [$listingId]
    );

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    if (!$exists) {
        echo json_encode(['success' => false, 'message' => 'Listing not found or cannot be bookmarked']);
        exit();
    }
} else {
    // lost_found
    $stmt = $db->prepare(
        "SELECT lost_found_id FROM lost_found WHERE lost_found_id = ?",
        "i",
        [$itemId]
    );

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    if (!$exists) {
        echo json_encode(['success' => false, 'message' => 'Lost/Found item not found']);
        exit();
    }
}

// Check if already saved (by type + id when applicable)
if ($type === 'listing') {
    $stmt = $db->prepare(
        "SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND item_type = 'listing' AND (item_id = ? OR listing_id = ?)",
        "iii",
        [$userId, $listingId, $listingId]
    );
} else {
    $stmt = $db->prepare(
        "SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND item_type = 'lost_found' AND item_id = ?",
        "ii",
        [$userId, $itemId]
    );
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$exists = $result->num_rows > 0;
$stmt->close();

if ($exists) {
    // Remove bookmark
    if ($type === 'listing') {
        $stmt = $db->prepare(
            "DELETE FROM bookmarks WHERE user_id = ? AND (item_type = 'listing' AND (item_id = ? OR listing_id = ?))",
            "iii",
            [$userId, $listingId, $listingId]
        );
    } else {
        $stmt = $db->prepare(
            "DELETE FROM bookmarks WHERE user_id = ? AND item_type = 'lost_found' AND item_id = ?",
            "ii",
            [$userId, $itemId]
        );
    }

    if ($stmt) {
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'saved' => false, 'message' => 'Item removed from saved items']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove bookmark']);
    }
} else {
    // Add bookmark
    if ($type === 'listing') {
        $stmt = $db->prepare(
            "INSERT INTO bookmarks (user_id, listing_id, item_type, item_id, bookmarked_at) VALUES (?, ?, 'listing', ?, NOW())",
            "iii",
            [$userId, $listingId, $listingId]
        );
    } else {
        $stmt = $db->prepare(
            "INSERT INTO bookmarks (user_id, item_type, item_id, bookmarked_at) VALUES (?, 'lost_found', ?, NOW())",
            "ii",
            [$userId, $itemId]
        );
    }

    if ($stmt) {
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'saved' => true, 'message' => 'Item saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save item']);
    }
}
