<?php
/**
 * Create a comment for an item
 * Expects JSON body: { item_type, item_id, comment_text, csrf_token }
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to comment']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['item_type'] ?? '';
$itemId = intval($input['item_id'] ?? 0);
$text = trim($input['comment_text'] ?? '');
$csrf = $input['csrf_token'] ?? '';

if (!in_array($type, ['listing', 'lost_found'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid item type']);
    exit();
}

if ($itemId <= 0 || $text === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

if (!verifyCSRFToken($csrf)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$db = Database::getInstance();
$userId = getCurrentUserId();

$sanitized = sanitize($text);

$stmt = $db->prepare("INSERT INTO comments (item_type, item_id, user_id, comment_text, created_at) VALUES (?, ?, ?, ?, NOW())", 'siis', [$type, $itemId, $userId, $sanitized]);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to save comment']);
    exit();
}

$stmt->execute();
$stmt->close();

$commentId = $db->getLastInsertId();

// Fetch inserted comment with user info to return
$s2 = $db->prepare(
    "SELECT c.comment_id, c.comment_text, c.user_id, c.created_at, u.first_name, u.last_name, u.profile_picture_url
     FROM comments c JOIN users u ON c.user_id = u.user_id
     WHERE c.comment_id = ? LIMIT 1",
    'i', [$commentId]
);

if ($s2) {
    $s2->execute();
    $res = $s2->get_result();
    if ($row = $res->fetch_assoc()) {
        $payload = [
            'comment_id' => (int)$row['comment_id'],
            'text' => $row['comment_text'],
            'user_id' => (int)$row['user_id'],
            'user_name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'profile_picture' => $row['profile_picture_url'] ?? 'assets/images/default-avatar.png',
            'created_at' => $row['created_at']
        ];
        echo json_encode(['success' => true, 'comment' => $payload]);
        $s2->close();
        exit();
    }
    $s2->close();
}

echo json_encode(['success' => true, 'comment' => ['comment_id' => $commentId, 'text' => $sanitized, 'user_id' => $userId, 'user_name' => 'You', 'profile_picture' => $_SESSION['profile_picture'] ?? 'assets/images/default-avatar.png', 'created_at' => date('Y-m-d H:i:s')]]);
