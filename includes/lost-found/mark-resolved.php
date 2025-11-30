<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$itemId = intval($input['item_id'] ?? 0);

if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

$db = Database::getInstance();
$userId = getCurrentUserId();

// Verify ownership
$stmt = $db->prepare(
    "SELECT 1 FROM lost_found WHERE lost_found_id = ? AND user_id = ?",
    "ii",
    [$itemId, $userId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$stmt->close();

// Update status
$stmt = $db->prepare(
    "UPDATE lost_found SET status = 'resolved' WHERE lost_found_id = ?",
    "i",
    [$itemId]
);

if ($stmt && $stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item marked as resolved']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
