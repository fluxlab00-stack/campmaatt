<?php
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$listing_id = (int)($_GET['id'] ?? 0);

if (!$listing_id) {
    echo '<p class="text-red-600">Invalid listing ID</p>';
    exit;
}

// Get listing details with all images
$sql = "SELECT l.*, CONCAT(u.first_name, ' ', u.last_name) as full_name, u.email, u.phone_number as phone, 
               (SELECT campus_name FROM campuses WHERE campus_id = u.campus_id) as campus,
               c.category_name
        FROM listings l
        LEFT JOIN users u ON l.user_id = u.user_id
        LEFT JOIN categories c ON l.category_id = c.category_id
        WHERE l.listing_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    echo '<p class="text-red-600">Listing not found</p>';
    exit;
}

// Get all images
$stmt = $conn->prepare("SELECT image_url FROM listing_images WHERE listing_id = ? ORDER BY is_primary DESC, image_id ASC");
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$images = $stmt->get_result();
?>

<div class="space-y-6">
    <!-- Images -->
    <?php if ($images->num_rows > 0): ?>
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-3">Images</h4>
            <div class="grid grid-cols-3 gap-4">
                <?php while ($img = $images->fetch_assoc()): ?>
                    <img src="../<?php echo htmlspecialchars($img['image_url']); ?>" 
                         alt="Listing Image" class="w-full h-40 object-cover rounded-lg">
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Basic Info -->
    <div>
        <h4 class="text-lg font-semibold text-gray-800 mb-3">Listing Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Title</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($listing['title']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Price</p>
                <p class="font-semibold text-primary text-xl">₦<?php echo number_format($listing['price'], 2); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Category</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($listing['category_name']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Condition</p>
                <p class="font-semibold text-gray-900 capitalize"><?php echo htmlspecialchars($listing['condition_status']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <?php 
                $status_colors = [
                    'active' => 'green',
                    'sold' => 'blue',
                    'inactive' => 'gray'
                ];
                $color = $status_colors[$listing['status']] ?? 'gray';
                ?>
                <span class="px-2 py-1 text-xs font-semibold text-<?php echo $color; ?>-800 bg-<?php echo $color; ?>-100 rounded-full capitalize">
                    <?php echo htmlspecialchars($listing['status']); ?>
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-600">Views</p>
                <p class="font-semibold text-gray-900"><?php echo number_format($listing['views_count']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Posted</p>
                <p class="font-semibold text-gray-900"><?php echo date('M d, Y H:i', strtotime($listing['created_at'])); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Updated</p>
                <p class="font-semibold text-gray-900"><?php echo date('M d, Y H:i', strtotime($listing['updated_at'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div>
        <h4 class="text-lg font-semibold text-gray-800 mb-3">Description</h4>
        <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($listing['description']); ?></p>
    </div>

    <!-- Seller Info -->
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-3">Seller Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Name</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($listing['full_name']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Email</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($listing['email']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Phone</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($listing['phone'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Campus</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($listing['campus']); ?></p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="border-t pt-6 flex gap-3">
        <a href="../pages/listing-detail.php?id=<?php echo $listing_id; ?>" target="_blank" 
           class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-external-link-alt mr-2"></i> View on Site
        </a>
        
        <?php if ($listing['status'] === 'active'): ?>
            <button onclick="markSold(<?php echo $listing_id; ?>); closeListingModal();" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-check-circle mr-2"></i> Mark as Sold
            </button>
        <?php endif; ?>
        
        <button onclick="if(confirm('Delete this listing?')) { deleteListing(<?php echo $listing_id; ?>); closeListingModal(); }" 
                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="fas fa-trash mr-2"></i> Delete
        </button>
    </div>
</div>
