<?php
/**
 * Edit Found It Item Page
 * Allows users to edit their existing Found It items
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$pageTitle = "Edit Found It Item - CampMart";

// Get database instance
$db = Database::getInstance();

// Get current user
$user = getCurrentUser();
$userId = $user['user_id'];

// Get item ID
$itemId = intval($_GET['id'] ?? 0);

if ($itemId <= 0) {
    setFlashMessage('error', 'Invalid item ID.');
    header("Location: my-listings.php?type=lost_found");
    exit();
}

// Fetch item details with ownership verification
$stmt = $db->prepare(
    "SELECT * FROM lost_found WHERE lost_found_id = ? AND user_id = ?",
    "ii",
    [$itemId, $userId]
);

if (!$stmt) {
    setFlashMessage('error', 'Database error occurred.');
    header("Location: my-listings.php?type=lost_found");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    setFlashMessage('error', 'Item not found or you do not have permission to edit it.');
    header("Location: my-listings.php?type=lost_found");
    exit();
}

// Fetch existing images
$images = [];
$stmt = $db->prepare(
    "SELECT image_id, image_url FROM lost_found_images WHERE lost_found_id = ? ORDER BY image_id ASC",
    "i",
    [$itemId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request. Please try again.');
        header("Location: edit-lost-found.php?id=" . $itemId);
        exit();
    }

    // Get form data
    $itemType = sanitize($_POST['item_type']);
    $itemName = sanitize($_POST['item_name']);
    $description = sanitize($_POST['description']);
    $locationLostFound = sanitize($_POST['location_lost_found']);
    $dateLostFound = sanitize($_POST['date_lost_found']);

    // Validate inputs
    if (empty($itemType) || empty($itemName) || empty($description) || empty($locationLostFound)) {
        setFlashMessage('error', 'Please fill in all required fields.');
    } else {
        // Update item
        $stmt = $db->prepare(
            "UPDATE lost_found 
             SET item_type = ?, item_name = ?, description = ?, location_lost_found = ?, date_lost_found = ?, updated_at = NOW()
             WHERE lost_found_id = ? AND user_id = ?",
            "sssssii",
            [$itemType, $itemName, $description, $locationLostFound, $dateLostFound ?: null, $itemId, $userId]
        );

        if ($stmt && $stmt->execute()) {
            // Handle new image uploads
            if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
                $uploadDir = __DIR__ . '/../assets/uploads/lost_found/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $currentImageCount = count($images);
                $maxImages = 8;

                foreach ($_FILES['new_images']['tmp_name'] as $key => $tmpName) {
                    if ($currentImageCount >= $maxImages) {
                        break;
                    }

                    if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = uniqid() . '_' . basename($_FILES['new_images']['name'][$key]);
                        $targetPath = $uploadDir . $fileName;
                        $dbPath = 'assets/uploads/lost_found/' . $fileName;

                        if (move_uploaded_file($tmpName, $targetPath)) {
                            // Insert image into database
                            $imgStmt = $db->prepare(
                                "INSERT INTO lost_found_images (lost_found_id, image_url) VALUES (?, ?)",
                                "is",
                                [$itemId, $dbPath]
                            );
                            if ($imgStmt) {
                                $imgStmt->execute();
                                $imgStmt->close();
                                $currentImageCount++;
                            }
                        }
                    }
                }
            }

            setFlashMessage('success', 'Item updated successfully!');
            header("Location: my-listings.php?type=lost_found");
            exit();
        } else {
            setFlashMessage('error', 'Failed to update item. Please try again.');
        }

        if ($stmt) {
            $stmt->close();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-2">
            <i class="fas fa-edit mr-2"></i> Edit Found It Item
        </h1>
        <p class="text-xl text-gray-100">
            Update your item details
        </p>
    </div>
</section>

<!-- Edit Form -->
<section class="py-12 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <!-- Current Images -->
                <?php if (!empty($images)): ?>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Current Images</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <?php foreach ($images as $image): ?>
                            <div class="relative group">
                                <img src="<?php echo baseUrl($image['image_url']); ?>" 
                                     alt="Item Image" 
                                     class="w-full h-32 object-cover rounded-lg">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all rounded-lg flex items-center justify-center">
                                    <button type="button" 
                                            onclick="deleteImage(<?php echo $image['image_id']; ?>)" 
                                            class="opacity-0 group-hover:opacity-100 transition-opacity px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Add New Images -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Add New Images (Optional)</label>
                    <input type="file" name="new_images[]" id="editLostFoundImageInput" accept="image/*" multiple 
                        onchange="previewNewImages(this)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    <p class="text-xs text-gray-500 mt-1">You can upload additional images (max 8 total)</p>
                    
                    <!-- Image Preview Container -->
                    <div id="newImagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
                </div>
                
                <!-- Item Type and Item Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Type *</label>
                        <select name="item_type" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                            <option value="">Select Type</option>
                            <option value="lost" <?php echo ($item['item_type'] === 'lost') ? 'selected' : ''; ?>>Lost</option>
                            <option value="found" <?php echo ($item['item_type'] === 'found') ? 'selected' : ''; ?>>Found</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Item Name *</label>
                        <input type="text" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Description *</label>
                    <textarea name="description" rows="6" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition" 
                        placeholder="Provide detailed description of the item..."><?php echo htmlspecialchars($item['description']); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Include any distinguishing features, colors, or markings</p>
                </div>
                
                <!-- Location and Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Location (Where Lost/Found) *</label>
                        <input type="text" name="location_lost_found" value="<?php echo htmlspecialchars($item['location_lost_found']); ?>" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition"
                            placeholder="e.g., Library, Cafeteria, Room 101">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Date (When Lost/Found)</label>
                        <input type="date" name="date_lost_found" value="<?php echo htmlspecialchars($item['date_lost_found'] ?? ''); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <button type="submit" 
                        class="flex-1 bg-primary text-white px-6 py-3 rounded-lg hover:bg-pink-700 transition font-semibold">
                        <i class="fas fa-save mr-2"></i> Update Item
                    </button>
                    <a href="<?php echo baseUrl('pages/my-listings.php?type=lost_found'); ?>" 
                        class="flex-1 text-center bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Delete image function
function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        const formData = new FormData();
        formData.append('image_id', imageId);
        formData.append('type', 'lost_found');
        
        fetch('<?php echo baseUrl('includes/lost-found/delete-image.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to delete image');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the image');
        });
    }
}

// Preview new images
function previewNewImages(input) {
    const previewContainer = document.getElementById('newImagePreview');
    previewContainer.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        previewContainer.classList.remove('hidden');
        
        const maxFiles = Math.min(input.files.length, 8);
        for (let i = 0; i < maxFiles; i++) {
            const file = input.files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                    <span class="absolute top-1 right-1 bg-black bg-opacity-50 text-white px-2 py-0.5 rounded text-xs">${i + 1}</span>
                `;
                previewContainer.appendChild(div);
            };
            
            reader.readAsDataURL(file);
        }
        
        if (input.files.length > 8) {
            alert('Maximum 8 images allowed. Only first 8 will be uploaded.');
        }
    } else {
        previewContainer.classList.add('hidden');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
