<?php
/**
 * Update Listing Process
 * Handles editing existing listings
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to continue.');
    header("Location: ../../index.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../pages/my-listings.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('error', 'Invalid request. Please try again.');
    header("Location: ../../pages/my-listings.php");
    exit();
}

// Get and sanitize input
$listingId = intval($_POST['listing_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$categoryId = intval($_POST['category_id'] ?? 0);
$conditionStatus = sanitize($_POST['condition_status'] ?? '');
$quantityAvailable = intval($_POST['quantity_available'] ?? 1);
$locationDescription = sanitize($_POST['location_description'] ?? '');
$tags = sanitize($_POST['tags'] ?? '');
$isFree = isset($_POST['is_free']) ? 1 : 0;
$isAvailableToday = isset($_POST['is_available_today']) ? 1 : 0;

// Get current user
$user = getCurrentUser();
$userId = $user['user_id'];

// Validation
$errors = [];

if ($listingId <= 0) {
    $errors[] = "Invalid listing ID.";
}

if (empty($title)) {
    $errors[] = "Item name is required.";
}

if (empty($description)) {
    $errors[] = "Description is required.";
}

if ($categoryId <= 0) {
    $errors[] = "Please select a category.";
}

if (empty($conditionStatus)) {
    $errors[] = "Please select item condition.";
}

if ($quantityAvailable < 1) {
    $errors[] = "Quantity must be at least 1.";
}

if (empty($locationDescription)) {
    $errors[] = "Location is required.";
}

if ($isFree) {
    $price = 0;
} elseif ($price < 0) {
    $errors[] = "Price cannot be negative.";
}

if (!empty($errors)) {
    setFlashMessage('error', implode(' ', $errors));
    header("Location: ../../pages/edit-listing.php?id=" . $listingId);
    exit();
}

// Get database instance
$db = Database::getInstance();

// Verify listing ownership
$stmt = $db->prepare(
    "SELECT user_id FROM listings WHERE listing_id = ?",
    "i",
    [$listingId]
);

if (!$stmt) {
    setFlashMessage('error', 'Database error occurred.');
    header("Location: ../../pages/my-listings.php");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$listing = $result->fetch_assoc();
$stmt->close();

if (!$listing || $listing['user_id'] != $userId) {
    setFlashMessage('error', 'You do not have permission to edit this listing.');
    header("Location: ../../pages/my-listings.php");
    exit();
}

// Update listing
$stmt = $db->prepare(
    "UPDATE listings SET 
        title = ?,
        description = ?,
        price = ?,
        category_id = ?,
        condition_status = ?,
        quantity_available = ?,
        location_description = ?,
        is_free = ?,
        is_available_today = ?,
        updated_at = NOW()
     WHERE listing_id = ? AND user_id = ?",
    "ssdisisiiiii",
    [
        $title,
        $description,
        $price,
        $categoryId,
        $conditionStatus,
        $quantityAvailable,
        $locationDescription,
        $isFree,
        $isAvailableToday,
        $listingId,
        $userId
    ]
);

if (!$stmt) {
    setFlashMessage('error', 'Failed to update listing.');
    header("Location: ../../pages/edit-listing.php?id=" . $listingId);
    exit();
}

$stmt->execute();
$stmt->close();

// Update tags
// First, delete existing tags
$db->query("DELETE FROM listing_tags WHERE listing_id = {$listingId}");

// Then insert new tags
if (!empty($tags)) {
    $tagArray = array_map('trim', explode(',', $tags));
    $tagArray = array_filter($tagArray); // Remove empty values
    
    foreach ($tagArray as $tag) {
        $stmt = $db->prepare(
            "INSERT INTO listing_tags (listing_id, tag_name) VALUES (?, ?)",
            "is",
            [$listingId, $tag]
        );
        
        if ($stmt) {
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Handle new image uploads
if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
    // Count existing images
    $result = $db->query("SELECT COUNT(*) as count FROM listing_images WHERE listing_id = {$listingId}");
    $row = $result->fetch_assoc();
    $existingImageCount = $row['count'];
    
    $uploadedCount = 0;
    $maxImages = 8;
    
    foreach ($_FILES['new_images']['name'] as $key => $filename) {
        if ($existingImageCount + $uploadedCount >= $maxImages) {
            break;
        }
        
        if (!empty($_FILES['new_images']['tmp_name'][$key])) {
            $file = [
                'name' => $_FILES['new_images']['name'][$key],
                'type' => $_FILES['new_images']['type'][$key],
                'tmp_name' => $_FILES['new_images']['tmp_name'][$key],
                'error' => $_FILES['new_images']['error'][$key],
                'size' => $_FILES['new_images']['size'][$key]
            ];
            
            $uploadResult = uploadImage($file, 'listings');
            
            if ($uploadResult['success']) {
                // Insert image record
                $isPrimary = ($existingImageCount == 0 && $uploadedCount == 0) ? 1 : 0;
                
                $stmt = $db->prepare(
                    "INSERT INTO listing_images (listing_id, image_url, is_primary) VALUES (?, ?, ?)",
                    "isi",
                    [$listingId, $uploadResult['filename'], $isPrimary]
                );
                
                if ($stmt) {
                    $stmt->execute();
                    $stmt->close();
                    $uploadedCount++;
                }
            }
        }
    }
}

setFlashMessage('success', 'Listing updated successfully!');
header("Location: ../../pages/listing-detail.php?id=" . $listingId);
exit();
