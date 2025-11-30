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

$stmt = $db->prepare("SELECT preferred_meetpoints FROM users WHERE user_id = ?", 'i', [$userId]);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

// Normalize stored meetpoints to an array of description strings
$mp = [];
if (!empty($row['preferred_meetpoints'])) {
    $decoded = @json_decode($row['preferred_meetpoints'], true);
    if (is_array($decoded)) {
        // decoded might be array of objects ({description}) or array of strings
        foreach ($decoded as $item) {
            if (is_string($item)) $mp[] = $item;
            elseif (is_array($item) && isset($item['description'])) $mp[] = $item['description'];
            elseif (is_array($item) && isset($item[0])) $mp[] = $item[0];
            // else skip
            if (count($mp) >= 3) break;
        }
    }
}

echo json_encode(['success' => true, 'meetpoints' => array_slice($mp, 0, 3)]); 
