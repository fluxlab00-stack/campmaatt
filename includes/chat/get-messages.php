<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$chatId = intval($_GET['chat_id'] ?? 0);

if ($chatId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid chat ID']);
    exit;
}

$db = Database::getInstance();
$currentUserId = getCurrentUserId();

// Verify user is part of this chat
$stmt = $db->prepare(
    "SELECT 1 FROM chats WHERE chat_id = ? AND (buyer_id = ? OR seller_id = ?)",
    "iii",
    [$chatId, $currentUserId, $currentUserId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Chat not found']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}
$stmt->close();

// Get messages
$stmt = $db->prepare(
    "SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as sender_name, u.profile_picture_url as sender_pic
     FROM messages m
     LEFT JOIN users u ON m.sender_id = u.user_id
     WHERE m.chat_id = ?
     ORDER BY m.sent_at ASC",
    "i",
    [$chatId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch messages']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

// attach any meetpoint suggestion details referenced by messages
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

$suggestionIds = [];
foreach ($messages as $m) {
    if (!empty($m['meetpoint_suggestion_id'])) $suggestionIds[] = (int)$m['meetpoint_suggestion_id'];
}
$suggestionMap = [];
if (!empty($suggestionIds)) {
    $in = implode(',', array_map('intval', array_unique($suggestionIds)));
    $sql = "SELECT * FROM meetpoint_suggestions WHERE suggestion_id IN ({$in})";
    $res = $db->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $suggestionMap[$r['suggestion_id']] = $r;
        }
    }
}

// attach suggestion details into messages where applicable
foreach ($messages as &$m) {
    if (!empty($m['meetpoint_suggestion_id']) && isset($suggestionMap[$m['meetpoint_suggestion_id']])) {
        $m['meetpoint_suggestion'] = $suggestionMap[$m['meetpoint_suggestion_id']];
    }
}

echo json_encode([
    'success' => true,
    'messages' => $messages
]);
