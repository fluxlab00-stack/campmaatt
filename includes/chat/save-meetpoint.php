<?php
/**
 * Save Meetpoint to User Profile
 * Stores a suggested meetpoint location in the user's profile
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance();
$userId = getCurrentUserId();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$meetpointDescription = trim($input['meetpoint_description'] ?? '');
$chatId = intval($input['chat_id'] ?? 0);

if (empty($meetpointDescription)) {
    echo json_encode(['success' => false, 'message' => 'Meetpoint description required']);
    exit;
}

if (strlen($meetpointDescription) > 200) {
    echo json_encode(['success' => false, 'message' => 'Meetpoint too long (max 200 characters)']);
    exit;
}

// Validate chat exists and user is part of it
if ($chatId > 0) {
    $stmt = $db->prepare(
        "SELECT 1 FROM chats WHERE chat_id = ? AND (buyer_id = ? OR seller_id = ?)",
        "iii",
        [$chatId, $userId, $userId]
    );
    
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $stmt->close();
    }
}

// Get current saved meetpoints
$stmt = $db->prepare(
    "SELECT preferred_meetpoints FROM users WHERE user_id = ?",
    "i",
    [$userId]
);

$currentMeetpoints = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['preferred_meetpoints'])) {
            $decoded = json_decode($row['preferred_meetpoints'], true);
            if (is_array($decoded)) {
                $currentMeetpoints = array_values($decoded);
            }
        }
    }
    $stmt->close();
}

// Check if meetpoint already exists (case-insensitive)
$meetpointLower = strtolower($meetpointDescription);
$exists = false;
foreach ($currentMeetpoints as $mp) {
    if (strtolower($mp) === $meetpointLower) {
        $exists = true;
        break;
    }
}

if ($exists) {
    echo json_encode(['success' => false, 'message' => 'This meetpoint already exists']);
    exit;
}

// Limit to 3 meetpoints
if (count($currentMeetpoints) >= 3) {
    echo json_encode(['success' => false, 'message' => 'Maximum 3 meetpoints allowed. Remove one to add another.']);
    exit;
}

// Add new meetpoint
$currentMeetpoints[] = $meetpointDescription;
$meetpointsJson = json_encode(array_values($currentMeetpoints), JSON_UNESCAPED_UNICODE);

// Update user profile
$stmt = $db->prepare(
    "UPDATE users SET preferred_meetpoints = ? WHERE user_id = ?",
    "si",
    [$meetpointsJson, $userId]
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to save meetpoint']);
    exit;
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Meetpoint saved successfully',
        'meetpoint' => $meetpointDescription,
        'total_meetpoints' => count($currentMeetpoints)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save meetpoint']);
}

$stmt->close();
?>
