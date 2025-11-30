<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST;
$lat = isset($input['lat']) ? floatval($input['lat']) : null;
$lon = isset($input['lon']) ? floatval($input['lon']) : null;

if (!$lat || !$lon) {
    echo json_encode(['success' => false, 'message' => 'Missing coordinates']);
    exit;
}

$db = Database::getInstance();
$userId = getCurrentUserId();

// Try reverse geocoding using Nominatim (OpenStreetMap)
$state = null;
$city = null;

$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: CampMart/1.0 (+http://localhost)\r\n"
    ]
];
$context = stream_context_create($opts);
$url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lon}&zoom=10&addressdetails=1";

try {
    $resp = @file_get_contents($url, false, $context);
    if ($resp) {
        $data = json_decode($resp, true);
        if (!empty($data['address'])) {
            $addr = $data['address'];
            // Nominatim may provide state, county, region, city, town, village
            $state = $addr['state'] ?? $addr['region'] ?? $addr['county'] ?? null;
            $city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['county'] ?? null;
        }
    }
} catch (Exception $e) {
    // ignore network errors and fallback
}

// If reverse geocoding failed, leave state/city null (server will fallback to registered campus)
if ($state === null || $city === null) {
    // respond but still update last seen coordinates optionally
    // For now, don't overwrite current_state/current_city if we couldn't resolve
    echo json_encode(['success' => true, 'gps' => true, 'message' => 'Coordinates received but could not resolve address']);
    exit;
}

// Update user's current_state and current_city in DB and session
$updateStmt = $db->prepare("UPDATE users SET current_state = ?, current_city = ?, last_login_at = NOW() WHERE user_id = ?", "ssi", [$state, $city, $userId]);
if ($updateStmt && $updateStmt->execute()) {
    $_SESSION['current_state'] = $state;
    $_SESSION['current_city'] = $city;
    echo json_encode(['success' => true, 'gps' => true, 'current_state' => $state, 'current_city' => $city]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update location']);
    exit;
}
