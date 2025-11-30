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

$suggestionId = intval($_POST['suggestion_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($suggestionId <= 0 || !in_array($action, ['accept','decline'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid parameters']);
    exit;
}

// fetch suggestion
$stmt = $db->prepare("SELECT * FROM meetpoint_suggestions WHERE suggestion_id = ?", 'i', [$suggestionId]);
if (!$stmt) { echo json_encode(['success'=>false,'message'=>'DB error']); exit; }
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo json_encode(['success'=>false,'message'=>'Suggestion not found']); exit; }
$suggest = $res->fetch_assoc();
$stmt->close();

// verify that current user is a participant in the chat and is NOT the sender (receiver must respond)
$stmt = $db->prepare("SELECT buyer_id, seller_id FROM chats WHERE chat_id = ?", 'i', [$suggest['chat_id']]);
$stmt->execute();
$c = $stmt->get_result()->fetch_assoc();
$stmt->close();
$buyer = $c['buyer_id']; $seller = $c['seller_id'];
$receiverId = ($suggest['sender_id'] == $buyer) ? $seller : $buyer;
if ($userId != $receiverId) {
    echo json_encode(['success'=>false,'message'=>'Only the receiver can respond']);
    exit;
}

if ($action === 'accept') {
    $u = $db->getConnection();
    $u->begin_transaction();
    try {
        $stmt = $u->prepare("UPDATE meetpoint_suggestions SET status = 'accepted', accepted_at = NOW() WHERE suggestion_id = ?");
        $stmt->bind_param('i', $suggestionId);
        $stmt->execute();
        $stmt->close();

        // add a message recording acceptance
        $msg = '[MEETPOINT_ACCEPTED] ' . $suggest['meet_point_name'];
        $stmt = $u->prepare("INSERT INTO messages (chat_id, sender_id, message_text, meetpoint_suggestion_id, sent_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('iisi', $suggest['chat_id'], $userId, $msg, $suggestionId);
        $stmt->execute();
        $stmt->close();

        // Auto-reject other pending suggestions for this chat
        $stmt = $u->prepare("SELECT suggestion_id, sender_id, meet_point_name FROM meetpoint_suggestions WHERE chat_id = ? AND suggestion_id != ? AND status = 'pending'");
        $stmt->bind_param('ii', $suggest['chat_id'], $suggestionId);
        $stmt->execute();
        $resOther = $stmt->get_result();
        $otherIds = [];
        while ($orow = $resOther->fetch_assoc()) {
            $otherIds[] = $orow;
        }
        $stmt->close();
        foreach ($otherIds as $o) {
            $stmt = $u->prepare("UPDATE meetpoint_suggestions SET status = 'rejected' WHERE suggestion_id = ?");
            $stmt->bind_param('i', $o['suggestion_id']);
            $stmt->execute();
            $stmt->close();

            // add message noting auto-reject
            $rejMsg = '[MEETPOINT_AUTO_REJECTED] ' . $o['meet_point_name'];
            $stmt = $u->prepare("INSERT INTO messages (chat_id, sender_id, message_text, meetpoint_suggestion_id, sent_at) VALUES (?, ?, ?, ?, NOW())");
            $sid = $o['suggestion_id'];
            $stmt->bind_param('iisi', $suggest['chat_id'], $userId, $rejMsg, $sid);
            $stmt->execute();
            $stmt->close();
        }

        $u->commit();
        echo json_encode(['success'=>true,'message'=>'Meetpoint accepted']);
        exit;
    } catch (Exception $e) {
        $u->rollback();
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        exit;
    }
} else {
    // decline
    $stmt = $db->prepare("UPDATE meetpoint_suggestions SET status = 'rejected' WHERE suggestion_id = ?", 'i', [$suggestionId]);
    if ($stmt) { $stmt->execute(); $stmt->close(); }
    // create a message noting the decline
    $msg = '[MEETPOINT_DECLINED] ' . $suggest['meet_point_name'];
    $stmt = $db->prepare("INSERT INTO messages (chat_id, sender_id, message_text, meetpoint_suggestion_id, sent_at) VALUES (?, ?, ?, ?, NOW())", 'iisi', [$suggest['chat_id'], $userId, $msg, $suggestionId]);
    if ($stmt) { $stmt->execute(); $stmt->close(); }

    echo json_encode(['success'=>true,'message'=>'Meetpoint declined']);
    exit;
}
