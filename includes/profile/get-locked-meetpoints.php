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

// return descriptions of meetpoints that were suggested by this user and accepted
$stmt = $db->prepare("SELECT DISTINCT description FROM meetpoint_suggestions WHERE sender_id = ? AND status = 'accepted'", 'i', [$userId]);
$locked = [];
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $locked[] = $r['description'];
    $stmt->close();
}

echo json_encode(['success' => true, 'locked' => $locked]);
