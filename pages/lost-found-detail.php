<?php
/**
 * Found It Item Detail Page
 * View complete details of a found or reported item
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Get item ID
$itemId = intval($_GET['id'] ?? 0);

if ($itemId <= 0) {
    header("Location: free-corner.php");
    exit();
}

// Get database instance
$db = Database::getInstance();

// Fetch item details
$stmt = $db->prepare(
    "SELECT lf.*, u.user_id, u.first_name, u.last_name, u.email, u.phone_number, 
            u.whatsapp_link, u.profile_picture_url, cp.campus_name
     FROM lost_found lf
     JOIN users u ON lf.user_id = u.user_id
     LEFT JOIN campuses cp ON u.campus_id = cp.campus_id
     WHERE lf.lost_found_id = ?",
    "i",
    [$itemId]
);

if (!$stmt) {
    header("Location: free-corner.php");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    setFlashMessage('error', 'Item not found.');
    header("Location: free-corner.php");
    exit();
}

$pageTitle = htmlspecialchars($item['item_name']) . " - CampMart";
$isOwner = isLoggedIn() && getCurrentUserId() == $item['user_id'];

// Check if current user bookmarked this lost/found item
$isBookmarked = 0;
if (isLoggedIn()) {
    $currentUserId = getCurrentUserId();
    $bmStmt = $db->prepare("SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND item_type = 'lost_found' AND item_id = ? LIMIT 1", "ii", [$currentUserId, $itemId]);
    if ($bmStmt) {
        $bmStmt->execute();
        $bmRes = $bmStmt->get_result();
        if ($bmRes && $bmRes->num_rows > 0) {
            $isBookmarked = 1;
        }
        $bmStmt->close();
    }
}

// Fetch all images
$images = [];
$stmt = $db->prepare(
    "SELECT image_url FROM lost_found_images WHERE lost_found_id = ? ORDER BY image_id ASC",
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

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center mb-4">
            <a href="<?php echo baseUrl('pages/free-corner.php'); ?>" class="text-white hover:text-gray-200 mr-4">
                <i class="fas fa-arrow-left text-2xl"></i>
            </a>
            <h1 class="text-4xl font-bold">
                <?php echo htmlspecialchars($item['item_name']); ?>
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $item['item_type'] === 'lost' ? 'bg-red-500' : 'bg-green-500'; ?>">
                <i class="fas fa-<?php echo $item['item_type'] === 'lost' ? 'search' : 'check-circle'; ?> mr-1"></i>
                <?php echo ucfirst($item['item_type']); ?>
            </span>
            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $item['status'] === 'active' ? 'bg-blue-500' : 'bg-gray-500'; ?>">
                <?php echo ucfirst($item['status']); ?>
            </span>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Images and Details -->
            <div class="lg:col-span-2">
                <!-- Image Gallery -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <?php if (!empty($images)): ?>
                        <div class="relative">
                            <img id="mainImage" 
                                 src="<?php echo baseUrl($images[0]['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                 class="w-full h-96 object-cover">
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                            <div class="p-4 bg-gray-50">
                                <div class="flex gap-2 overflow-x-auto">
                                    <?php foreach ($images as $index => $image): ?>
                                        <img src="<?php echo baseUrl($image['image_url']); ?>" 
                                             alt="Image <?php echo $index + 1; ?>"
                                             class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition <?php echo $index === 0 ? 'ring-2 ring-primary' : ''; ?>"
                                             onclick="changeMainImage('<?php echo baseUrl($image['image_url']); ?>', this)">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-6xl"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Item Details -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Item Details</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600 uppercase mb-1">Description</h3>
                            <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Location
                                </h3>
                                <p class="text-gray-800"><?php echo htmlspecialchars($item['location_lost_found']); ?></p>
                            </div>

                            <?php if ($item['date_lost_found']): ?>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-1">
                                    <i class="fas fa-calendar mr-1"></i> Date
                                </h3>
                                <p class="text-gray-800"><?php echo date('M d, Y', strtotime($item['date_lost_found'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-gray-600 uppercase mb-1">
                                <i class="fas fa-clock mr-1"></i> Posted
                            </h3>
                            <p class="text-gray-800"><?php echo timeAgo($item['posted_at']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Contact Information -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Contact Information</h2>
                    
                    <!-- User Profile -->
                    <div class="flex items-center mb-6 pb-6 border-b">
                        <img src="<?php echo baseUrl($item['profile_picture_url'] ?: 'assets/images/default-avatar.png'); ?>" 
                             alt="<?php echo htmlspecialchars($item['first_name']); ?>" 
                             class="w-16 h-16 rounded-full object-cover mr-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">
                                <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                            </h3>
                            <?php if ($item['campus_name']): ?>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-university mr-1"></i>
                                    <?php echo htmlspecialchars($item['campus_name']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Contact Actions -->
                    <div class="space-y-3">
                        <?php if (isLoggedIn()): ?>
                            <div class="mb-3 text-right">
                                <button data-lostfound-id="<?php echo $itemId; ?>" onclick="toggleBookmark({type:'lost_found', item_id: <?php echo $itemId; ?>}, this)" class="inline-flex items-center px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 transition">
                                    <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark text-primary mr-2"></i>
                                    <span class="text-sm font-semibold"><?php echo $isBookmarked ? 'Saved' : 'Save'; ?></span>
                                </button>
                            </div>
                            <?php if (!$isOwner): ?>
                                <!-- Contact via WhatsApp -->
                                <?php if ($item['whatsapp_link']): ?>
                                    <a href="<?php echo htmlspecialchars($item['whatsapp_link']); ?>" 
                                       target="_blank"
                                       class="block w-full text-center px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                                        <i class="fab fa-whatsapp mr-2"></i> Contact via WhatsApp
                                    </a>
                                <?php endif; ?>

                                <!-- Phone -->
                                <?php if ($item['phone_number']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($item['phone_number']); ?>" 
                                       class="block w-full text-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold">
                                        <i class="fas fa-phone mr-2"></i> Call <?php echo htmlspecialchars($item['phone_number']); ?>
                                    </a>
                                <?php endif; ?>

                                <!-- Email -->
                                <a href="mailto:<?php echo htmlspecialchars($item['email']); ?>" 
                                   class="block w-full text-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold">
                                    <i class="fas fa-envelope mr-2"></i> Send Email
                                </a>
                            <?php else: ?>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                    <i class="fas fa-info-circle text-blue-500 text-2xl mb-2"></i>
                                    <p class="text-blue-700 font-semibold">This is your item</p>
                                </div>

                                <?php if ($item['status'] === 'active'): ?>
                                    <a href="<?php echo baseUrl('pages/edit-lost-found.php?id=' . $itemId); ?>" 
                                       class="block w-full text-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                                        <i class="fas fa-edit mr-2"></i> Edit Item
                                    </a>
                                    <button onclick="markAsResolved(<?php echo $itemId; ?>)" 
                                            class="w-full px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                                        <i class="fas fa-check mr-2"></i> Mark as Resolved
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                <i class="fas fa-lock text-yellow-500 text-2xl mb-2"></i>
                                <p class="text-yellow-700 font-semibold mb-3">Please login to contact</p>
                                <a href="<?php echo baseUrl('index.php'); ?>" 
                                   class="inline-block px-6 py-2 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                                    Login / Register
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Report Item -->
                    <?php if (isLoggedIn() && !$isOwner): ?>
                        <div class="mt-6 pt-6 border-t">
                            <button onclick="openReportModal()" 
                                    class="text-red-600 hover:text-red-700 text-sm font-semibold">
                                <i class="fas fa-flag mr-1"></i> Report this item
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Report Modal -->
<?php if (isLoggedIn() && !$isOwner): ?>
<div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Report Item</h3>
            <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="reportForm" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Reason</label>
                <select name="reason" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select a reason</option>
                    <option value="inappropriate">Inappropriate Content</option>
                    <option value="spam">Spam</option>
                    <option value="fraud">Fraudulent Item</option>
                    <option value="duplicate">Duplicate Posting</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Details</label>
                <textarea name="details" rows="4" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                          placeholder="Please provide more details about your report..."></textarea>
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
// Image gallery
function changeMainImage(imageUrl, thumbnail) {
    document.getElementById('mainImage').src = imageUrl;
    
    // Update active thumbnail
    document.querySelectorAll('.overflow-x-auto img').forEach(img => {
        img.classList.remove('ring-2', 'ring-primary');
    });
    thumbnail.classList.add('ring-2', 'ring-primary');
}

// Mark as resolved
function markAsResolved(itemId) {
    if (confirm('Are you sure you want to mark this item as resolved?')) {
        const formData = new FormData();
        formData.append('item_id', itemId);
        
        fetch('<?php echo baseUrl('includes/lost-found/mark-resolved.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to mark as resolved');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

// Report modal
function openReportModal() {
    document.getElementById('reportModal').classList.remove('hidden');
    document.getElementById('reportModal').classList.add('flex');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.getElementById('reportModal').classList.remove('flex');
}

// Report form submission
<?php if (isLoggedIn() && !$isOwner): ?>
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('item_type', 'lost_found');
    formData.append('item_id', <?php echo $itemId; ?>);
    
    fetch('<?php echo baseUrl('admin/report-actions.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report submitted successfully');
            closeReportModal();
        } else {
            alert(data.message || 'Failed to submit report');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
