<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance();
$userId = getCurrentUserId();

$chatId = intval($_POST['chat_id'] ?? 0);
$index = isset($_POST['index']) ? intval($_POST['index']) : -1;
$title = trim($_POST['title'] ?? '');
$desc = trim($_POST['description'] ?? '');

if ($chatId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid chat']);
    exit;
}

// verify chat and participants
$stmt = $db->prepare("SELECT buyer_id, seller_id FROM chats WHERE chat_id = ?", 'i', [$chatId]);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Chat not found']); exit; }
$chat = $res->fetch_assoc();
$stmt->close();

$otherUserId = ($chat['buyer_id'] == $userId) ? $chat['seller_id'] : $chat['buyer_id'];

// If index provided, pull from user's saved meetpoints
if ($index >= 0) {
    $stmt = $db->prepare("SELECT preferred_meetpoints FROM users WHERE user_id = ?", 'i', [$userId]);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    $mpArr = [];
    if (!empty($row['preferred_meetpoints'])) {
        $decoded = @json_decode($row['preferred_meetpoints'], true);
        if (is_array($decoded)) {
            // normalize to array of descriptions
            foreach ($decoded as $it) {
                if (is_string($it)) $mpArr[] = $it;
                elseif (is_array($it) && isset($it['description'])) $mpArr[] = $it['description'];
                elseif (is_array($it) && isset($it[0])) $mpArr[] = $it[0];
                if (count($mpArr) >= 3) break;
            }
        }
    }
    if (!isset($mpArr[$index])) {
        echo json_encode(['success'=>false,'message'=>'Invalid meetpoint selection']);
        exit;
    }
    $desc = $mpArr[$index];
    $title = mb_substr($desc, 0, 60); // use snippet as name
}

if ($desc === '') {
    echo json_encode(['success'=>false,'message'=>'Description required']);
    exit;
}

// insert suggestion
$stmt = $db->prepare("INSERT INTO meetpoint_suggestions (chat_id, sender_id, meet_point_name, description, status, suggested_at) VALUES (?, ?, ?, ?, 'pending', NOW())", 'iiss', [$chatId, $userId, $title, $desc]);
if (!$stmt) { echo json_encode(['success'=>false,'message'=>'DB insert failed']); exit; }
$stmt->execute();
$suggestionId = $db->getConnection()->insert_id;
$stmt->close();

// create a message linking to this suggestion so receivers see it in chat
$msgText = '[MEETPOINT_SUGGESTION] ' . $title;
$stmt = $db->prepare("INSERT INTO messages (chat_id, sender_id, message_text, meetpoint_suggestion_id, sent_at) VALUES (?, ?, ?, ?, NOW())", 'iisi', [$chatId, $userId, $msgText, $suggestionId]);
if ($stmt) { $stmt->execute(); $stmt->close(); }

echo json_encode(['success'=>true, 'message'=>'Meetpoint suggested', 'suggestion_id'=>$suggestionId]);
