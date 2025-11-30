<?php
/**
 * Get or create a chat between current user and another user (seller)
 * Expects JSON body: { seller_id, listing_id (optional) }
 * Returns: { success: true, chat_id, other_user: { user_id, first_name, last_name, profile_picture_url } }
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: [];
$sellerId = intval($input['seller_id'] ?? 0);
$listingId = isset($input['listing_id']) ? intval($input['listing_id']) : 0;

if ($sellerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid seller id']);
    exit;
}

$db = Database::getInstance();
$currentUserId = getCurrentUserId();

if ($sellerId === $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Cannot chat with yourself']);
    exit;
}

// Try to find existing chat between the two users (prefer same listing if provided)
$params = [];
$sql = "SELECT chat_id FROM chats WHERE ((buyer_id = ? AND seller_id = ?) OR (buyer_id = ? AND seller_id = ?))";
$params = [$currentUserId, $sellerId, $sellerId, $currentUserId];

if ($listingId > 0) {
    $sql .= " AND (listing_id = ? OR listing_id IS NULL)"; // allow chat for same listing or generic
    $params[] = $listingId;
}

$types = str_repeat('i', count($params));
$stmt = $db->prepare($sql . ' LIMIT 1', $types, $params);
$chatId = 0;
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $chatId = (int)$row['chat_id'];
    $stmt->close();
}

// If not existing, create a new chat
if ($chatId <= 0) {
    $stmt = $db->prepare("INSERT INTO chats (listing_id, buyer_id, seller_id, created_at) VALUES (?, ?, ?, NOW())", 'iii', [$listingId > 0 ? $listingId : null, $currentUserId, $sellerId]);
    if ($stmt) {
        $stmt->execute();
        $chatId = $db->insert_id;
        $stmt->close();
    }
}

if ($chatId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Failed to create or find chat']);
    exit;
}

// Return basic other user info
$s = $db->prepare("SELECT user_id, first_name, last_name, profile_picture_url FROM users WHERE user_id = ? LIMIT 1", 'i', [$sellerId]);
$other = null;
if ($s) {
    $s->execute();
    $r = $s->get_result();
    if ($row = $r->fetch_assoc()) {
        $other = $row;
    }
    $s->close();
}

echo json_encode(['success' => true, 'chat_id' => (int)$chatId, 'other_user' => $other]);
exit;

?>
