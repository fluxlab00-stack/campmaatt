<?php
/**
 * Report Listing
 * Handle listing report submissions
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to report listings']);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$userId = getCurrentUserId();
$itemType = sanitize($_POST['item_type'] ?? '');
$itemId = intval($_POST['item_id'] ?? 0);
$reason = sanitize($_POST['reason'] ?? '');
$details = sanitize($_POST['details'] ?? '');

// Validate inputs
if (empty($itemType) || $itemId <= 0 || empty($reason) || empty($details)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate item type
if (!in_array($itemType, ['listing', 'lost_found'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid item type']);
    exit();
}

// Validate reason
$validReasons = ['inappropriate', 'spam', 'fraud', 'duplicate', 'sold', 'wrong_category', 'offensive', 'other'];
if (!in_array($reason, $validReasons)) {
    echo json_encode(['success' => false, 'message' => 'Invalid reason']);
    exit();
}

// Validate details length
if (strlen($details) < 10) {
    echo json_encode(['success' => false, 'message' => 'Details must be at least 10 characters']);
    exit();
}

// Map reason to report_type
$reportTypeMap = [
    'inappropriate' => 'offensive',
    'spam' => 'spam',
    'fraud' => 'fraud',
    'duplicate' => 'spam',
    'sold' => 'item_sold',
    'wrong_category' => 'misleading',
    'offensive' => 'offensive',
    'other' => 'other'
];
$reportType = $reportTypeMap[$reason] ?? 'other';

$db = Database::getInstance();

// Check if item exists (only support listings for now)
if ($itemType !== 'listing') {
    echo json_encode(['success' => false, 'message' => 'Only listing reports are supported']);
    exit();
}

$stmt = $db->prepare(
    "SELECT listing_id, user_id FROM listings WHERE listing_id = ?",
    "i",
    [$itemId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Listing not found']);
    exit();
}

// Check if user is trying to report their own item
if ($item['user_id'] == $userId) {
    echo json_encode(['success' => false, 'message' => 'You cannot report your own listing']);
    exit();
}

// Check if user has already reported this listing
$stmt = $db->prepare(
    "SELECT report_id FROM reports WHERE reporter_id = ? AND listing_id = ? AND status != 'action_taken'",
    "ii",
    [$userId, $itemId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'You have already reported this listing']);
        exit();
    }
    $stmt->close();
}

// Insert report
$stmt = $db->prepare(
    "INSERT INTO reports (reporter_id, listing_id, reported_user_id, report_type, details, status, reported_at) 
     VALUES (?, ?, ?, ?, ?, 'pending', NOW())",
    "iiiss",
    [$userId, $itemId, $item['user_id'], $reportType, $details]
);

if ($stmt && $stmt->execute()) {
    $stmt->close();
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your report. We will review it shortly.'
    ]);
} else {
    if ($stmt) {
        $stmt->close();
    }
    echo json_encode(['success' => false, 'message' => 'Failed to submit report. Please try again.']);
}
