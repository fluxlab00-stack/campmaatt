<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance();
$currentUserId = getCurrentUserId();

// Support JSON body or multipart/form-data (for future attachments)
$chatId = 0;
$message = '';
$attachmentPath = '';

// If POST with form data (browser FormData or form submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!empty($_POST) || !empty($_FILES))) {
    $chatId = intval($_POST['chat_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload = $_FILES['attachment'];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
        if (!in_array($upload['type'], $allowed)) {
            // skip attachment if not allowed
        } else {
            $uploadsDir = __DIR__ . '/../../assets/uploads/chat/';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            $ext = pathinfo($upload['name'], PATHINFO_EXTENSION);
            $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadsDir . $newName;
            if (move_uploaded_file($upload['tmp_name'], $dest)) {
                $attachmentPath = 'assets/uploads/chat/' . $newName;
            }
        }
    }
} else {
    // Try JSON body
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    $chatId = intval($input['chat_id'] ?? 0);
    $message = trim($input['message'] ?? '');
}

// Require either message text or an attachment
if ($chatId <= 0 || (empty($message) && empty($attachmentPath))) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

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

// Build final message text with attachment if present
$finalMessage = $message;
if (!empty($attachmentPath)) {
    if (!empty($message)) {
        $finalMessage .= "\n[attachment]{$attachmentPath}";
    } else {
        $finalMessage = "[attachment]{$attachmentPath}";
    }
}

// Insert message
$stmt = $db->prepare(
    "INSERT INTO messages (chat_id, sender_id, message_text, sent_at) VALUES (?, ?, ?, NOW())",
    "iis",
    [$chatId, $currentUserId, $finalMessage]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    exit;
}

if ($stmt->execute()) {
    // Update chat's last_message_at
    $db->query("UPDATE chats SET last_message_at = NOW() WHERE chat_id = {$chatId}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'attachment' => $attachmentPath
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}

$stmt->close();
?>
