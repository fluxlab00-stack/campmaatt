<?php
/**
 * Update Profile Handler
 * Handles updating user profile info and profile picture uploads.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(403);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'Please login to update your profile.']);
    } else {
        $_SESSION['error'] = 'Please login to update your profile.';
        header('Location: ../../pages/profile.php');
    }
    exit;
}

// Detect AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Set JSON header for AJAX responses
if ($isAjax) {
    header('Content-Type: application/json');
}

// Accept POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    if ($isAjax) echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    } else {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: ../../pages/profile.php');
    }
    exit;
}

$userId = (int)$_SESSION['user_id'];
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName = sanitize($_POST['last_name'] ?? '');
$phone = sanitize($_POST['phone_number'] ?? '');
$preferredMeetpoints = '';

// Accept structured meetpoints (arrays) or legacy textarea
// Accept description-only meetpoints from meetpoint_desc[]; limit to 3
$mpDescs = $_POST['meetpoint_desc'] ?? [];
$meetpointsArr = [];
if (!empty($mpDescs) && is_array($mpDescs)) {
    for ($i = 0; $i < min(3, count($mpDescs)); $i++) {
        $d = trim($mpDescs[$i]);
        $d = sanitize($d);
        if ($d === '') continue;
        $meetpointsArr[] = $d;
    }
    if (!empty($meetpointsArr)) {
        // store as JSON array of strings (addresses)
        $preferredMeetpoints = json_encode(array_values($meetpointsArr), JSON_UNESCAPED_UNICODE);
    }
} else {
    // legacy fallback (plain text)
    $preferredMeetpoints = sanitize($_POST['preferred_meetpoints'] ?? '');
}

if (empty($firstName) || empty($lastName) || empty($phone)) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'First name, last name and phone number are required.']);
    } else {
        $_SESSION['error'] = 'First name, last name and phone number are required.';
        header('Location: ../../pages/profile.php');
    }
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    $conn->begin_transaction();

    // Load existing saved meetpoints for this user
    $existingMeetpoints = [];
    $sstmt = $conn->prepare("SELECT preferred_meetpoints FROM users WHERE user_id = ?");
    if ($sstmt) {
        $sstmt->bind_param('i', $userId);
        $sstmt->execute();
        $sr = $sstmt->get_result()->fetch_assoc();
        $sstmt->close();
        if (!empty($sr['preferred_meetpoints'])) {
            $decoded = @json_decode($sr['preferred_meetpoints'], true);
            if (is_array($decoded)) {
                // normalize to array of strings
                foreach ($decoded as $it) {
                    if (is_string($it)) $existingMeetpoints[] = $it;
                    elseif (is_array($it) && isset($it['description'])) $existingMeetpoints[] = $it['description'];
                    elseif (is_array($it) && isset($it[0])) $existingMeetpoints[] = $it[0];
                }
            }
        }
    }

    // Determine locked meetpoints (accepted suggestions made by this user)
    $locked = [];
    if (!empty($existingMeetpoints)) {
        // prepare IN clause
        $placeholders = implode(',', array_fill(0, count($existingMeetpoints), '?'));
        $types = str_repeat('s', count($existingMeetpoints));
        $params = $existingMeetpoints;

        // build query to find accepted suggestions matching any existing description
        $sql = "SELECT DISTINCT description FROM meetpoint_suggestions WHERE sender_id = ? AND status = 'accepted' AND description IN ({$placeholders})";
        $stmtLock = $conn->prepare($sql);
        if ($stmtLock) {
            // bind params dynamically: first sender_id then descriptions
            $bindTypes = 'i' . $types;
            $bindParams = array_merge([$userId], $params);
            $refs = [];
            $refs[] = & $bindTypes;
            foreach ($bindParams as $k => $v) {
                $refs[] = & $bindParams[$k];
            }
            call_user_func_array(array($stmtLock, 'bind_param'), $refs);
            $stmtLock->execute();
            $resLock = $stmtLock->get_result();
            while ($r = $resLock->fetch_assoc()) $locked[] = $r['description'];
            $stmtLock->close();
        }
    }

    // If user attempts to remove or edit a locked meetpoint, block the update
    if (!empty($locked)) {
        foreach ($locked as $ld) {
            if (!in_array($ld, $meetpointsArr)) {
                throw new Exception('One or more meetpoints are locked because they were accepted in a chat. You cannot remove or modify them.');
            }
        }
    }

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, preferred_meetpoints = ?, updated_at = NOW() WHERE user_id = ?");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('ssssi', $firstName, $lastName, $phone, $preferredMeetpoints, $userId);
    if (!$stmt->execute()) throw new Exception('Failed to update profile: ' . $stmt->error);
    $stmt->close();

    // Handle profile picture upload if present
    if (!empty($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
        $type = $_FILES['profile_picture']['type'];
        if (!in_array($type, $allowed)) throw new Exception('Invalid image type.');
        if ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) throw new Exception('Image too large. Max 5MB.');

        $uploadDir = __DIR__ . '/../../assets/uploads/profiles/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = time() . '_' . uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $target = $uploadDir . $fileName;
        $dbPath = 'assets/uploads/profiles/' . $fileName;

        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target)) throw new Exception('Failed to move uploaded file.');

        $stmt = $conn->prepare("UPDATE users SET profile_picture_url = ? WHERE user_id = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('si', $dbPath, $userId);
        if (!$stmt->execute()) throw new Exception('Failed to save profile picture: ' . $stmt->error);
        $stmt->close();

        // Update session picture path
        $_SESSION['profile_picture'] = $dbPath;

    }

    // Update preferred meetpoints in session (store JSON string)
    $_SESSION['preferred_meetpoints'] = $preferredMeetpoints;

    $conn->commit();

    if ($isAjax) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.', 'profile_picture' => $_SESSION['profile_picture'] ?? null]);
    } else {
        $_SESSION['success'] = 'Profile updated successfully.';
        header('Location: ../../pages/profile.php');
    }
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../../pages/profile.php');
    }
    exit;
}
```