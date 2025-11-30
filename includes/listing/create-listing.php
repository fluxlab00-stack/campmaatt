<?php
/**
 * Create Listing Process
 * Handles new listing creation
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

// Require login
requireLogin();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('error', 'Invalid request. Please try again.');
    header("Location: ../../index.php");
    exit();
}

// Get and sanitize input
$userId = getCurrentUserId();
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$categoryId = intval($_POST['category_id'] ?? 0);
$condition = sanitize($_POST['condition_status'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$quantity = intval($_POST['quantity_available'] ?? 1);
$location = sanitize($_POST['location_description'] ?? '');
$tags = sanitize($_POST['tags'] ?? '');
$isFree = isset($_POST['is_free']) ? 1 : 0;
$isAvailableToday = isset($_POST['is_available_today']) ? 1 : 0;

// Validation
$errors = [];

if (empty($title)) {
    $errors[] = "Item name is required.";
}

if (empty($description)) {
    $errors[] = "Description is required.";
}

if ($categoryId <= 0) {
    $errors[] = "Please select a category.";
}

if (empty($condition)) {
    $errors[] = "Please select item condition.";
}

if (!$isFree && $price <= 0) {
    $errors[] = "Please enter a valid price or mark the item as free.";
}

if ($isFree) {
    $price = 0;
}

if ($quantity < 1) {
    $errors[] = "Quantity must be at least 1.";
}

if (empty($location)) {
    $errors[] = "Location description is required.";
}

// Validate images
if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    $errors[] = "Please upload at least one image.";
}

if (!empty($errors)) {
    setFlashMessage('error', implode(' ', $errors));
    header("Location: ../../index.php");
    exit();
}

// Get database instance
$db = Database::getInstance();

// Begin transaction
$db->beginTransaction();

try {
    // Insert listing
    $stmt = $db->prepare(
        "INSERT INTO listings (user_id, category_id, title, description, price, is_free, 
         is_available_today, quantity_available, condition_status, location_description, 
         status, posted_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())",
        "iissdiiiss",
        [
            $userId, $categoryId, $title, $description, $price, $isFree,
            $isAvailableToday, $quantity, $condition, $location
        ]
    );
    
    if (!$stmt) {
        throw new Exception("Failed to create listing.");
    }
    
    $stmt->execute();
    $listingId = $db->getLastInsertId();
    $stmt->close();
    
    // Handle image uploads
    $uploadedImages = [];
    $imageCount = count($_FILES['images']['name']);
    
    for ($i = 0; $i < min($imageCount, 8); $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $imageFile = [
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i]
            ];
            
            $uploadResult = uploadImage($imageFile, 'listings');
            
            if ($uploadResult['success']) {
                $uploadedImages[] = [
                    'url' => $uploadResult['filename'],
                    'is_primary' => ($i === 0) ? 1 : 0
                ];
            }
        }
    }
    
    // Insert images
    if (empty($uploadedImages)) {
        throw new Exception("Failed to upload images.");
    }
    
    foreach ($uploadedImages as $image) {
        $stmt = $db->prepare(
            "INSERT INTO listing_images (listing_id, image_url, is_primary, created_at)
             VALUES (?, ?, ?, NOW())",
            "isi",
            [$listingId, $image['url'], $image['is_primary']]
        );
        
        if ($stmt) {
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Handle tags
    if (!empty($tags)) {
        $tagArray = array_map('trim', explode(',', $tags));
        $tagArray = array_filter($tagArray); // Remove empty tags
        
        foreach ($tagArray as $tag) {
            $stmt = $db->prepare(
                "INSERT INTO listing_tags (listing_id, tag_name, created_at)
                 VALUES (?, ?, NOW())",
                "is",
                [$listingId, $tag]
            );
            
            if ($stmt) {
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // Commit transaction
    $db->commit();
    
    setFlashMessage('success', 'Your listing has been posted successfully!');
    header("Location: ../../pages/listing-detail.php?id=" . $listingId);
    exit();
    
} catch (Exception $e) {
    $db->rollback();
    
    // Clean up uploaded images on error
    foreach ($uploadedImages as $image) {
        deleteFile($image['url']);
    }
    
    error_log("Listing creation error: " . $e->getMessage());
    setFlashMessage('error', 'An error occurred while creating your listing. Please try again.');
    header("Location: ../../index.php");
    exit();
}
