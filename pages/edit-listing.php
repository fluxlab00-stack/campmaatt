<?php
/**
 * Edit Listing Page
 * Allows users to edit their existing listings
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

$pageTitle = "Edit Listing - CampMart";

// Get database instance
$db = Database::getInstance();

// Get current user
$user = getCurrentUser();
$userId = $user['user_id'];

// Get listing ID
$listingId = intval($_GET['id'] ?? 0);

if ($listingId <= 0) {
    setFlashMessage('error', 'Invalid listing ID.');
    header("Location: my-listings.php");
    exit();
}

// Fetch listing details with ownership verification
$stmt = $db->prepare(
    "SELECT l.*, c.category_name
     FROM listings l
     JOIN categories c ON l.category_id = c.category_id
     WHERE l.listing_id = ? AND l.user_id = ?",
    "ii",
    [$listingId, $userId]
);

if (!$stmt) {
    setFlashMessage('error', 'Database error occurred.');
    header("Location: my-listings.php");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$listing = $result->fetch_assoc();
$stmt->close();

if (!$listing) {
    setFlashMessage('error', 'Listing not found or you do not have permission to edit it.');
    header("Location: my-listings.php");
    exit();
}

// Fetch existing images
$images = [];
$stmt = $db->prepare(
    "SELECT image_id, image_url, is_primary 
     FROM listing_images 
     WHERE listing_id = ? 
     ORDER BY is_primary DESC, image_id ASC",
    "i",
    [$listingId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $stmt->close();
}

// Fetch existing tags
$tags = [];
$stmt = $db->prepare(
    "SELECT tag_name FROM listing_tags WHERE listing_id = ?",
    "i",
    [$listingId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag_name'];
    }
    $stmt->close();
}

$tagsString = implode(', ', $tags);

// Fetch all categories for dropdown
$categories = [];
$result = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-2">
            <i class="fas fa-edit mr-2"></i> Edit Listing
        </h1>
        <p class="text-xl text-gray-100">
            Update your listing details
        </p>
    </div>
</section>

<!-- Edit Form -->
<section class="py-12 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-8">
            
            <form method="POST" action="<?php echo baseUrl('includes/listing/update-listing.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
                
                <!-- Current Images -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Current Images</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <?php foreach ($images as $image): ?>
                            <div class="relative group">
                                <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" 
                                     alt="Listing Image" 
                                     class="w-full h-32 object-cover rounded-lg">
                                <?php if ($image['is_primary']): ?>
                                    <span class="absolute top-2 left-2 bg-primary text-white px-2 py-1 rounded text-xs font-semibold">
                                        Primary
                                    </span>
                                <?php endif; ?>
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
                    
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Add New Images (Optional)</label>
                    <input type="file" name="new_images[]" id="editImageInput" accept="image/*" multiple 
                        onchange="previewEditImages(this)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    <p class="text-xs text-gray-500 mt-1">You can upload additional images (max 8 total)</p>
                    
                    <!-- Image Preview Container -->
                    <div id="editImagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
                </div>
                
                <!-- Title -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Item Name *</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                
                <!-- Category and Condition -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Category *</label>
                        <select name="category_id" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($category['category_id'] == $listing['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Condition *</label>
                        <select name="condition_status" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                            <option value="">Select Condition</option>
                            <?php 
                            $conditions = ['New', 'Like New', 'Used - Good', 'Used - Fair', 'For Parts/Repair'];
                            foreach ($conditions as $condition): 
                            ?>
                                <option value="<?php echo $condition; ?>" 
                                    <?php echo ($condition == $listing['condition_status']) ? 'selected' : ''; ?>>
                                    <?php echo $condition; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Description *</label>
                    <textarea name="description" rows="6" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition"><?php echo htmlspecialchars($listing['description']); ?></textarea>
                </div>
                
                <!-- Price, Quantity, Location -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Price (₦) *</label>
                        <input type="number" name="price" id="priceInput" 
                            value="<?php echo $listing['price']; ?>" 
                            step="0.01" min="0" 
                            <?php echo $listing['is_free'] ? 'disabled' : ''; ?>
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Quantity *</label>
                        <input type="number" name="quantity_available" 
                            value="<?php echo $listing['quantity_available']; ?>" 
                            min="1" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Location *</label>
                        <input type="text" name="location_description" 
                            value="<?php echo htmlspecialchars($listing['location_description']); ?>" 
                            placeholder="e.g., Block C Hostel 5" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    </div>
                </div>
                
                <!-- Tags -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Tags (comma-separated)</label>
                    <input type="text" name="tags" 
                        value="<?php echo htmlspecialchars($tagsString); ?>" 
                        placeholder="e.g., gaming, laptop, core i7" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                
                <!-- Checkboxes -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_free" id="isFreeCheckbox" 
                            <?php echo $listing['is_free'] ? 'checked' : ''; ?> 
                            onchange="togglePrice()" class="mr-2">
                        <span class="text-sm text-gray-700">Free Item</span>
                    </label>
                    
                        <!-- Hot Deal option removed -->
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="is_available_today" 
                            <?php echo $listing['is_available_today'] ? 'checked' : ''; ?> 
                            class="mr-2">
                        <span class="text-sm text-gray-700">Available Today </span>
                    </label>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-primary text-white py-3 rounded-lg hover:bg-pink-700 transition font-semibold">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    
                    <a href="my-listings.php" class="flex-1 text-center px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
            
        </div>
    </div>
</section>

<script>
    // Toggle price field based on "Free Item" checkbox
    function togglePrice() {
        const isFree = document.getElementById('isFreeCheckbox').checked;
        const priceInput = document.getElementById('priceInput');
        
        if (isFree) {
            priceInput.value = '0';
            priceInput.disabled = true;
            priceInput.required = false;
        } else {
            priceInput.disabled = false;
            priceInput.required = true;
        }
    }
    
    // Delete image
    function deleteImage(imageId) {
        if (confirm('Are you sure you want to delete this image?')) {
            fetch('<?php echo baseUrl('includes/listing/delete-image.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `image_id=${imageId}&csrf_token=<?php echo generateCSRFToken(); ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            });
        }
    }
    
    // Preview new images
    function previewEditImages(input) {
        const previewContainer = document.getElementById('editImagePreview');
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
<?php include __DIR__ . '/../includes/modals.php'; ?>
