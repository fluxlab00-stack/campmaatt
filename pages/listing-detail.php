<?php
/**
 * Listing Detail Page
 * View complete details of a listing
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Get listing ID
$listingId = intval($_GET['id'] ?? 0);

if ($listingId <= 0) {
    header("Location: marketplace.php");
    exit();
}

// Get database instance
$db = Database::getInstance();

// Increment view count
$db->query("UPDATE listings SET views_count = views_count + 1 WHERE listing_id = {$listingId}");

// Ensure listing_views table exists (lightweight view logger) and record this view (for 7-day trending)
$db->query("CREATE TABLE IF NOT EXISTS listing_views (
    view_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (listing_id),
    INDEX (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$insertViewStmt = $db->prepare("INSERT INTO listing_views (listing_id) VALUES (?)", "i", [$listingId]);
if ($insertViewStmt) {
    $insertViewStmt->execute();
    $insertViewStmt->close();
}

// Fetch listing details
$stmt = $db->prepare(
    "SELECT l.*, u.user_id, u.first_name, u.last_name, u.email, u.phone_number, 
            u.whatsapp_link, u.profile_picture_url, u.average_rating, u.total_ratings,
            u.department_id, u.level_id, c.category_name,
            d.department_name, lv.level_name, cp.campus_name
     FROM listings l
     JOIN users u ON l.user_id = u.user_id
     JOIN categories c ON l.category_id = c.category_id
     LEFT JOIN departments d ON u.department_id = d.department_id
     LEFT JOIN levels lv ON u.level_id = lv.level_id
     LEFT JOIN campuses cp ON u.campus_id = cp.campus_id
     WHERE l.listing_id = ?",
    "i",
    [$listingId]
);

if (!$stmt) {
    header("Location: marketplace.php");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$listing = $result->fetch_assoc();
$stmt->close();

if (!$listing || $listing['status'] === 'deleted') {
    setFlashMessage('error', 'Listing not found.');
    header("Location: marketplace.php");
    exit();
}

$pageTitle = htmlspecialchars($listing['title']) . " - CampMart";
$isOwner = isLoggedIn() && getCurrentUserId() == $listing['user_id'];

// Fetch all images
$images = [];
$stmt = $db->prepare(
    "SELECT image_url, is_primary FROM listing_images WHERE listing_id = ? ORDER BY is_primary DESC, image_id ASC",
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

// Fetch tags
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

// Check if saved by current user
$isSaved = false;
if (isLoggedIn()) {
    $stmt = $db->prepare(
        "SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND listing_id = ?",
        "ii",
        [getCurrentUserId(), $listingId]
    );
    
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $isSaved = $result->num_rows > 0;
        $stmt->close();
    }
}

// Fetch similar listings
$similarListings = [];
$stmt = $db->prepare(
    "SELECT l.*, u.first_name, u.last_name,
            (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
     FROM listings l
     JOIN users u ON l.user_id = u.user_id
     WHERE l.category_id = ? AND l.listing_id != ? AND l.status = 'active'
     ORDER BY l.posted_at DESC
     LIMIT 4",
    "ii",
    [$listing['category_id'], $listingId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $similarListings[] = $row;
    }
    $stmt->close();
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center text-sm text-gray-600">
                        <a href="<?php echo baseUrl('index.php'); ?>" class="hover:text-primary">Home</a>
            <i class="fas fa-chevron-right text-xs text-gray-400 mx-2"></i>
            <a href="marketplace.php" class="hover:text-primary">Marketplace</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="marketplace.php?category=<?php echo urlencode($listing['category_name']); ?>" class="hover:text-primary">
                <?php echo htmlspecialchars($listing['category_name']); ?>
            </a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-900"><?php echo truncate($listing['title'], 50); ?></span>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Images and Details -->
            <div class="lg:col-span-2">
                
                <!-- Image Gallery -->
                <div class="mb-6">
                    <?php if (!empty($images)): ?>
                        <!-- Main Image -->
                        <div class="mb-4">
                            <img id="mainImage" src="../<?php echo htmlspecialchars($images[0]['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($listing['title']); ?>" 
                                 class="w-full h-96 object-cover rounded-lg shadow-lg">
                        </div>
                        
                        <!-- Thumbnails - Horizontal Scroll -->
                        <?php if (count($images) > 1): ?>
                            <div class="overflow-x-auto pb-2">
                                <div class="flex gap-2 min-w-max">
                                    <?php foreach ($images as $index => $image): ?>
                                        <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" 
                                             alt="Thumbnail <?php echo $index + 1; ?>" 
                                             onclick="changeMainImage(this, '../<?php echo htmlspecialchars($image['image_url']); ?>')"
                                             class="w-24 h-24 object-cover rounded-lg cursor-pointer border-2 transition <?php echo $index === 0 ? 'border-primary' : 'border-gray-300 hover:border-primary'; ?>"
                                             id="thumb-<?php echo $index; ?>">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <img src="<?php echo baseUrl('assets/images/placeholder.svg'); ?>" alt="No Image" class="w-full h-96 object-cover rounded-lg">
                    <?php endif; ?>
                </div>
                
                <!-- Status Badges -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <?php if ($listing['is_free']): ?>
                        <span class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            FREE
                        </span>
                    <?php endif; ?>
                    
                    <!-- Hot Deal badge removed -->
                    
                    <?php if ($listing['is_available_today']): ?>
                        <span class="bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            ⏰ Available Today
                        </span>
                    <?php endif; ?>
                    
                    <span class="bg-gray-200 text-gray-700 px-4 py-2 rounded-full text-sm font-semibold">
                        <?php echo htmlspecialchars($listing['condition_status']); ?>
                    </span>
                    
                    <?php if ($listing['status'] === 'sold'): ?>
                        <span class="bg-red-500 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            SOLD
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Title and Price -->
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    <?php echo htmlspecialchars($listing['title']); ?>
                </h1>
                
                <div class="flex items-center justify-between mb-6">
                    <div class="text-4xl font-bold text-primary">
                        <?php echo formatPrice($listing['price']); ?>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <?php if (isLoggedIn() && !$isOwner): ?>
                            <button onclick="toggleBookmark(<?php echo $listingId; ?>, this)" 
                                class="px-4 py-2 border-2 border-primary rounded-lg hover:bg-primary hover:text-white transition <?php echo $isSaved ? 'bg-primary text-white' : 'text-primary'; ?>">
                                <i class="<?php echo $isSaved ? 'fas' : 'far'; ?> fa-bookmark mr-2"></i>
                                <?php echo $isSaved ? 'Saved' : 'Save'; ?>
                            </button>
                        <?php endif; ?>
                        
                        <button onclick="shareItem()" class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-share-alt mr-2"></i> Share
                        </button>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                </div>
                
                <!-- Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Item Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600">Category:</span>
                            <span class="font-semibold text-gray-900 ml-2"><?php echo htmlspecialchars($listing['category_name']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Condition:</span>
                            <span class="font-semibold text-gray-900 ml-2"><?php echo htmlspecialchars($listing['condition_status']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Quantity:</span>
                            <span class="font-semibold text-gray-900 ml-2"><?php echo $listing['quantity_available']; ?> available</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Location:</span>
                            <span class="font-semibold text-gray-900 ml-2"><?php echo htmlspecialchars($listing['location_description']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Posted:</span>
                            <span class="font-semibold text-gray-900 ml-2"><?php echo timeAgo($listing['posted_at']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Views:</span>
                            <span class="font-semibold text-gray-900 ml-2"><?php echo number_format($listing['views_count']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                                <a href="marketplace.php?search=<?php echo urlencode($tag); ?>" 
                                   class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-primary hover:text-white transition">
                                    #<?php echo htmlspecialchars($tag); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Right Column - Seller Info and Actions -->
            <div class="lg:col-span-1">
                
                <!-- Seller Card -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Seller Information</h3>
                    
                    <div class="flex items-center mb-4">
                        <img src="../<?php echo htmlspecialchars($listing['profile_picture_url']); ?>" 
                             alt="<?php echo htmlspecialchars($listing['first_name']); ?>" 
                             class="w-16 h-16 rounded-full object-cover border-2 border-primary mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">
                                <?php echo htmlspecialchars($listing['first_name'] . ' ' . $listing['last_name']); ?>
                            </h4>
                            <?php if ($listing['average_rating'] > 0): ?>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-star text-yellow-500 mr-1"></i>
                                    <?php echo number_format($listing['average_rating'], 1); ?>
                                    <span class="ml-1">(<?php echo $listing['total_ratings']; ?> reviews)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4 text-sm">
                        <?php if ($listing['department_name']): ?>
                            <p class="text-gray-600">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                <?php echo htmlspecialchars($listing['department_name']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($listing['level_name']): ?>
                            <p class="text-gray-600">
                                <i class="fas fa-layer-group mr-2"></i>
                                <?php echo htmlspecialchars($listing['level_name']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($listing['campus_name']): ?>
                            <p class="text-gray-600">
                                <i class="fas fa-university mr-2"></i>
                                <?php echo htmlspecialchars($listing['campus_name']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$isOwner && $listing['status'] === 'active'): ?>
                        <?php if (isLoggedIn()): ?>
                            <a href="chat.php?listing_id=<?php echo $listingId; ?>&seller_id=<?php echo $listing['user_id']; ?>" 
                            class="w-full block text-center px-4 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold mb-3">
                                <i class="fas fa-comment mr-2"></i> Message Seller
                            </a>
                            
                            <?php if ($listing['whatsapp_link']): ?>
                                <a href="<?php echo htmlspecialchars($listing['whatsapp_link']); ?>" target="_blank"
                                   class="w-full block text-center px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold mb-3">
                                    <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button onclick="openLoginModal()" 
                                class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold mb-3">
                                <i class="fas fa-comment mr-2"></i> Login to Message
                            </button>
                        <?php endif; ?>
                        
                        <a href="user-profile.php?id=<?php echo $listing['user_id']; ?>" 
                           class="w-full block text-center px-4 py-3 border-2 border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition font-semibold">
                            View Profile
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($isOwner): ?>
                        <div class="border-t pt-4">
                            <p class="text-sm text-gray-600 mb-3">This is your listing</p>
                            
                            <?php if ($listing['status'] === 'active'): ?>
                                <a href="edit-listing.php?id=<?php echo $listingId; ?>" 
                                   class="w-full block text-center px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold mb-2">
                                    <i class="fas fa-edit mr-2"></i> Edit Listing
                                </a>
                                
                                <button onclick="markAsSold(<?php echo $listingId; ?>)" 
                                    class="w-full px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold mb-2">
                                    <i class="fas fa-check-circle mr-2"></i> Mark as Sold
                                </button>
                            <?php endif; ?>
                            
                            <button onclick="deleteListing(<?php echo $listingId; ?>)" 
                                class="w-full px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold">
                                <i class="fas fa-trash mr-2"></i> Delete Listing
                            </button>
                        </div>
                    <?php else: ?>
                        <button onclick="openReportModal(<?php echo $listingId; ?>)" 
                            class="w-full mt-4 text-sm text-red-600 hover:text-red-700 transition">
                            <i class="fas fa-flag mr-1"></i> Report this listing
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Safety Tips -->
                <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                        Safety Tips
                    </h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Meet in public places</li>
                        <li>• Inspect item before paying</li>
                        <li>• Don't share bank details</li>
                        <li>• Report suspicious activity</li>
                    </ul>
                    <a href="how-it-works.php#safety" class="text-sm text-primary hover:underline mt-2 block">
                        Read more safety tips →
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Similar Listings -->
        <?php if (!empty($similarListings)): ?>
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Similar Items</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($similarListings as $item): ?>
                        <div class="bg-white rounded-lg shadow-custom hover-scale overflow-hidden">
                            <a href="listing-detail.php?id=<?php echo $item['listing_id']; ?>">
                                <img src="../<?php echo htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.svg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h3 class="font-semibold text-lg text-gray-900 mb-2 truncate">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h3>
                                    <p class="text-primary font-bold text-xl mb-2">
                                        <?php echo formatPrice($item['price']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                    </p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- Report Modal -->
<?php if (isLoggedIn() && !$isOwner): ?>
<div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Report Listing</h3>
            <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="reportForm" onsubmit="submitReport(event)">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Reason for Report *</label>
                <select id="reportReason" name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select a reason</option>
                    <option value="inappropriate">Inappropriate Content</option>
                    <option value="spam">Spam or Misleading</option>
                    <option value="fraud">Fraudulent Listing</option>
                    <option value="duplicate">Duplicate Posting</option>
                    <option value="sold">Item Already Sold</option>
                    <option value="wrong_category">Wrong Category</option>
                    <option value="offensive">Offensive Language</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Additional Details *</label>
                <textarea id="reportDetails" name="details" rows="4" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                          placeholder="Please provide more details about why you're reporting this listing..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeReportModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                    Submit Report
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function changeMainImage(thumbnail, imageUrl) {
    // Update main image
    document.getElementById('mainImage').src = imageUrl;
    
    // Update thumbnail borders
    document.querySelectorAll('[id^="thumb-"]').forEach(thumb => {
        thumb.classList.remove('border-primary');
        thumb.classList.add('border-gray-300');
    });
    thumbnail.classList.remove('border-gray-300');
    thumbnail.classList.add('border-primary');
}

function shareItem() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($listing['title']); ?>',
            text: '<?php echo addslashes(truncate($listing['description'], 100)); ?>',
            url: window.location.href
        }).catch(console.error);
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        showToast('Link copied to clipboard!', 'success');
    }
}

function markAsSold(listingId) {
    if (!confirm('Mark this item as sold? This cannot be undone.')) {
        return;
    }
    
    fetch('<?php echo baseUrl('includes/listing/mark-sold.php'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({listing_id: listingId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'An error occurred', 'error');
        }
    })
    .catch(() => showToast('An error occurred', 'error'));
}

function openReportModal(listingId) {
    document.getElementById('reportModal').classList.remove('hidden');
    document.getElementById('reportModal').classList.add('flex');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.getElementById('reportModal').classList.remove('flex');
    document.getElementById('reportForm').reset();
}

function submitReport(event) {
    event.preventDefault();
    
    const reason = document.getElementById('reportReason').value;
    const details = document.getElementById('reportDetails').value;
    
    // Validate minimum length
    if (details.length < 10) {
        alert('Please provide at least 10 characters in the details');
        return;
    }
    
    const formData = new FormData();
    formData.append('item_type', 'listing');
    formData.append('item_id', <?php echo $listingId; ?>);
    formData.append('reason', reason);
    formData.append('details', details);
    
    fetch('<?php echo baseUrl('includes/listing/report-listing.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report submitted successfully. We will review it shortly.');
            closeReportModal();
        } else {
            alert(data.message || 'Failed to submit report');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the report');
    });
}

</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
