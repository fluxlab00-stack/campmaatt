<?php
/**
 * Create Lost/Found Report
 * Process new lost or found item submissions
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Please login to report items.']);
    } else {
        $_SESSION['error'] = "Please login to report items.";
        header('Location: ../../pages/lost-found.php');
    }
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid request. Please try again.']);
    } else {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header('Location: ../../pages/lost-found.php');
    }
    exit;
}

// Validate required fields
$required = ['item_type', 'item_name', 'description', 'location', 'date_lost_found'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        } else {
            $_SESSION['error'] = "All fields are required.";
            header('Location: ../../pages/lost-found.php');
        }
        exit;
    }
}

// Sanitize inputs
$itemType = sanitize($_POST['item_type']);
$itemName = sanitize($_POST['item_name']);
$description = sanitize($_POST['description']);
$location = sanitize($_POST['location']);
$dateLostFound = sanitize($_POST['date_lost_found']);
$userId = $_SESSION['user_id'];

// Validate item type
if (!in_array($itemType, ['lost', 'found'])) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid item type.']);
    } else {
        $_SESSION['error'] = "Invalid item type.";
        header('Location: ../../pages/lost-found.php');
    }
    exit;
}

// Validate date
if (strtotime($dateLostFound) > time()) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Date cannot be in the future.']);
    } else {
        $_SESSION['error'] = "Date cannot be in the future.";
        header('Location: ../../pages/lost-found.php');
    }
    exit;
}

$db = Database::getInstance();

try {
    // Begin transaction
    $db->getConnection()->begin_transaction();
    
    // Insert lost/found item
    $stmt = $db->prepare(
        "INSERT INTO lost_found (user_id, item_type, item_name, description, location_lost_found, date_lost_found, status, posted_at) 
         VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())"
    );
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $db->getConnection()->error);
    }
    
    $stmt->bind_param("isssss", $userId, $itemType, $itemName, $description, $location, $dateLostFound);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert report: " . $stmt->error);
    }
    
    $lostFoundId = $db->getConnection()->insert_id;
    $stmt->close();
    
    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/uploads/lost_found/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        $dbPath = 'assets/uploads/lost_found/' . $fileName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.");
        }
        
        // Validate file size (max 20MB)
        if ($_FILES['image']['size'] > 20 * 1024 * 1024) {
            throw new Exception("File size too large. Maximum 20MB allowed.");
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Insert image record
            // Compress/resize image to reasonable size to save storage (max width/height 2000px)
            $mime = mime_content_type($targetPath);
            compressImage($targetPath, $mime, 2000, 80);

            $stmt = $db->prepare(
                "INSERT INTO lost_found_images (lost_found_id, image_url) 
                 VALUES (?, ?)"
            );

            if (!$stmt) {
                throw new Exception("Failed to prepare image statement: " . $db->getConnection()->error);
            }

            $stmt->bind_param("is", $lostFoundId, $dbPath);

            if (!$stmt->execute()) {
                throw new Exception("Failed to save image record: " . $stmt->error);
            }

            $stmt->close();
        } else {
            throw new Exception("Failed to upload image.");
        }
    }
    
    // Commit transaction
    $db->getConnection()->commit();
    
    // Generate dynamic success message based on report type
    $lost_messages = [
        "Upload successful — I hope this gets back to you soon.",
        "Upload successful — Fingers crossed it finds its way home 🤞.",
        "Upload successful — Your report is live.",
        "Upload successful — someone out there might just spot it..",
        "Upload successful — I hope someone reaches out soon.",
        "Upload successful — Stay hopeful, help is on the way.",
        "Upload successful — the search begins now.",
        "Upload successful — I hope good news comes soon.",
        "Upload successful — the community's eyes are now on it."
    ];
    
    $found_messages = [
        "Upload successful — you've got a good heart ✨.",
        "Upload successful — Someone will be grateful for your kindness.",
        "Upload successful — You just made someone's day brighter 😊.",
        "Upload successful — Thank you for choosing kindness.",
        "Upload successful — you've got a gorgeous soul ✨.",
        "Upload successful — you're making the world a little softer.",
        "Upload successful — you're spreading good energy today.",
        "Upload successful — the owner will be grateful.",
        "Upload successful — you're a blessing to the community."
    ];
    
    $messages = ($itemType === 'lost') ? $lost_messages : $found_messages;
    $success_message = $messages[array_rand($messages)];
    
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode([
            'success' => true,
            'message' => $success_message,
            'item_id' => $lostFoundId,
            'report_type' => $itemType
        ]);
    } else {
        $_SESSION['success'] = $success_message;
        header('Location: ../../pages/lost-found.php');
    }
    exit;
    
} catch (Exception $e) {
    // Rollback on error
    $db->getConnection()->rollback();
    
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../../pages/lost-found.php');
    }
    exit;
}

/**
 * Compress and resize image using GD.
 * Supports JPEG, PNG, GIF, WebP.
 */
function compressImage($filePath, $mime, $maxDim = 2000, $quality = 80) {
    if (!extension_loaded('gd')) return false;
    list($origW, $origH) = getimagesize($filePath);
    if (!$origW || !$origH) return false;

    $ratio = min(1, $maxDim / max($origW, $origH));
    $newW = (int)round($origW * $ratio);
    $newH = (int)round($origH * $ratio);

    if ($newW === $origW && $newH === $origH) {
        // still compress by re-encoding
        $newW = $origW;
        $newH = $origH;
    }

    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            $src = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $src = imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($filePath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $src = imagecreatefromwebp($filePath);
            } else {
                return false;
            }
            break;
        default:
            return false;
    }

    if (!$src) return false;

    $dst = imagecreatetruecolor($newW, $newH);
    // Preserve transparency for PNG/GIF
    if (in_array($mime, ['image/png','image/gif'])) {
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            imagejpeg($dst, $filePath, $quality);
            break;
        case 'image/png':
            // PNG quality: 0 (no compression) - 9
            $pngLevel = (int)round((100 - $quality) / 11.111); // approximate
            imagepng($dst, $filePath, max(0, min(9, $pngLevel)));
            break;
        case 'image/gif':
            imagegif($dst, $filePath);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) imagewebp($dst, $filePath, $quality);
            break;
    }

    imagedestroy($src);
    imagedestroy($dst);
    return true;
}
