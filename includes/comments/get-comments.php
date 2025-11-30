<?php
/**
 * Get comments for an item (listing or lost_found)
 * GET params: type (listing|lost_found), item_id, page (optional), per_page (optional)
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'listing';
$itemId = intval($_GET['item_id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = max(5, intval($_GET['per_page'] ?? 20));
$offset = ($page - 1) * $perPage;

if (!in_array($type, ['listing', 'lost_found'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit();
}

if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item id']);
    exit();
}

$db = Database::getInstance();

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM comments WHERE item_type = ? AND item_id = ?", 'si', [$type, $itemId]);
if (!$countStmt) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
    exit();
}
$countStmt->execute();
$cres = $countStmt->get_result();
$total = 0;
if ($cres) {
    $crow = $cres->fetch_assoc();
    $total = (int)($crow['total'] ?? 0);
}
$countStmt->close();

// Fetch comments with user info
$stmt = $db->prepare(
    "SELECT c.comment_id, c.comment_text, c.user_id, c.created_at, u.first_name, u.last_name, u.profile_picture_url
     FROM comments c JOIN users u ON c.user_id = u.user_id
     WHERE c.item_type = ? AND c.item_id = ?
     ORDER BY c.created_at DESC LIMIT ? OFFSET ?",
    'siii', [$type, $itemId, $perPage, $offset]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch comments']);
    exit();
}

$stmt->execute();
$res = $stmt->get_result();
$comments = [];
while ($r = $res->fetch_assoc()) {
    $comments[] = [
        'comment_id' => (int)$r['comment_id'],
        'text' => $r['comment_text'],
        'user_id' => (int)$r['user_id'],
        'user_name' => trim($r['first_name'] . ' ' . $r['last_name']),
        'profile_picture' => $r['profile_picture_url'] ?? 'assets/images/default-avatar.png',
        'created_at' => $r['created_at']
    ];
}
$stmt->close();

echo json_encode(['success' => true, 'comments' => $comments, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
