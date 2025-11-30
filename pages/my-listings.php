<?php
/**
 * My Listings Page
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

$pageTitle = "My Listings - CampMart";

// Get database instance
$db = Database::getInstance();

// Get current user
$user = getCurrentUser();
$userId = $user['user_id'];

// Get filter parameter
$filter = sanitize($_GET['filter'] ?? 'active');
$itemType = sanitize($_GET['type'] ?? 'marketplace'); // marketplace or lost_found

// Build query based on filter and type
if ($itemType === 'lost_found') {
    // Fetch lost & found items
    $status = $filter === 'resolved' ? 'resolved' : 'active';
    $query = "SELECT lf.*,
                     (SELECT image_url FROM lost_found_images WHERE lost_found_id = lf.lost_found_id LIMIT 1) as primary_image
              FROM lost_found lf
              WHERE lf.user_id = ? AND lf.status = ?
              ORDER BY lf.posted_at DESC";
    
    $listings = [];
    $stmt = $db->prepare($query, 'is', [$userId, $status]);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $listings[] = $row;
            }
        }
        $stmt->close();
    }
} else {
    // Fetch marketplace listings
    $status = $filter === 'sold' ? 'sold' : 'active';
    $query = "SELECT l.*, c.category_name,
                     (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image,
                     (SELECT COUNT(*) FROM bookmarks WHERE listing_id = l.listing_id) as bookmark_count
              FROM listings l
              JOIN categories c ON l.category_id = c.category_id
              WHERE l.user_id = ? AND l.status = ?
              ORDER BY l.posted_at DESC";
    
    $listings = [];
    $stmt = $db->prepare($query, 'is', [$userId, $status]);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $listings[] = $row;
            }
        }
        $stmt->close();
    }
    $result = $db->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $listings[] = $row;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-2">
            <i class="fas fa-list mr-2"></i> My Listings
        </h1>
        <p class="text-xl text-gray-100">
            Manage all your posted items
        </p>
    </div>
</section>

<!-- Filter Tabs -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Item Type Selector -->
        <div class="mb-4 flex gap-4 border-b pb-4">
            <a href="?type=marketplace&filter=active" 
               class="px-6 py-3 rounded-lg font-semibold transition <?php echo $itemType === 'marketplace' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class="fas fa-shopping-bag mr-2"></i> Marketplace Items
            </a>
            <a href="?type=lost_found&filter=active" 
               class="px-6 py-3 rounded-lg font-semibold transition <?php echo $itemType === 'lost_found' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class="fas fa-search mr-2"></i> Found It
            </a>
        </div>
        
        <div class="flex justify-between items-center">
            <div class="flex gap-4">
                <?php if ($itemType === 'lost_found'): ?>
                    <a href="?type=lost_found&filter=active" 
                       class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'active' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-check-circle mr-2"></i> Active
                    </a>
                    <a href="?type=lost_found&filter=resolved" 
                       class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'resolved' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-check-double mr-2"></i> Resolved
                    </a>
                    <a href="?type=lost_found&filter=all" 
                       class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-list mr-2"></i> All
                    </a>
                <?php else: ?>
                    <a href="?type=marketplace&filter=active" 
                       class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'active' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-check-circle mr-2"></i> Active
                    </a>
                    <a href="?type=marketplace&filter=sold" 
                       class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'sold' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-dollar-sign mr-2"></i> Sold
                    </a>
                    <a href="?type=marketplace&filter=all" 
                       class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-list mr-2"></i> All
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($itemType === 'lost_found'): ?>
                <a href="free-corner.php#lost-found" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                    <i class="fas fa-plus mr-2"></i> Report Item
                </a>
            <?php else: ?>
                <!-- <button onclick="openPostModal()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-orange-600 transition font-semibold">
                    <i class="fas fa-plus mr-2"></i> Post New Item
                </button> -->
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Listings Grid -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($listings)): ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($listings as $listing): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <!-- Image -->
                        <div class="relative h-48">
                            <img src="<?php echo baseUrl(htmlspecialchars($listing['primary_image'] ?? 'assets/images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($displayTitle ?? ''); ?>" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Status Badge -->
                            <?php if ($listing['status'] === 'sold'): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-gray-800 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                        <i class="fas fa-check"></i> Sold
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Views and Bookmarks (only for marketplace items) -->
                            <?php if ($itemType === 'marketplace'): ?>
                            <div class="absolute bottom-2 right-2 flex gap-2">
                                <span class="bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                                    <i class="fas fa-eye"></i> <?php echo $listing['views_count'] ?? 0; ?>
                                </span>
                                <span class="bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                                    <i class="fas fa-bookmark"></i> <?php echo $listing['bookmark_count'] ?? 0; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 truncate">
                                <?php 
                                $displayTitle = $itemType === 'lost_found' ? ($listing['item_name'] ?? '') : ($listing['title'] ?? '');
                                echo htmlspecialchars($displayTitle); 
                                ?>
                            </h3>
                            
                            <?php if ($itemType === 'marketplace'): ?>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xl font-bold text-primary">
                                    <?php echo formatPrice($listing['price']); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($listing['condition_status'] ?? ''); ?>
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="flex items-center mb-2">
                                <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $listing['item_type'] === 'lost' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                    <?php echo ucfirst($listing['item_type'] ?? ''); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($listing['category_name'])): ?>
                            <div class="flex items-center text-sm text-gray-600 mb-3">
                                <i class="fas fa-tag mr-1"></i>
                                <?php echo htmlspecialchars($listing['category_name'] ?? ''); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <?php 
                                    $itemId = $itemType === 'lost_found' ? ($listing['lost_found_id'] ?? 0) : ($listing['listing_id'] ?? 0);
                                    $detailPage = $itemType === 'lost_found' ? 'pages/lost-found-detail.php' : 'pages/listing-detail.php';
                                    $editPage = $itemType === 'lost_found' ? 'pages/edit-lost-found.php' : 'pages/edit-listing.php';
                                    ?>
                                    <a href="<?php echo baseUrl($detailPage . '?id=' . $itemId); ?>" 
                                       class="flex-1 text-center px-3 py-2 bg-primary text-white rounded-lg hover:bg-pink-700 transition text-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    
                                    <?php if ($listing['status'] === 'active'): ?>
                                        <a href="<?php echo baseUrl($editPage . '?id=' . $itemId); ?>" 
                                           class="flex-1 text-center px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex gap-2">
                                    <?php if ($listing['status'] === 'active'): ?>
                                        <?php if ($itemType === 'lost_found'): ?>
                                            <button onclick="markAsResolved(<?php echo $itemId; ?>)" 
                                                    class="flex-1 px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm">
                                                <i class="fas fa-check mr-1"></i> Mark resolved
                                            </button>
                                        <?php else: ?>
                                            <button onclick="markAsSold(<?php echo $itemId; ?>)" 
                                                    class="flex-1 px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm">
                                                <i class="fas fa-check mr-1"></i> Mark sold
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <button onclick="confirmDelete(<?php echo $itemId; ?>)" 
                                            class="flex-1 px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                                        <i class="fas fa-trash mr-1"></i> delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-box-open text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">No Listings Found</h3>
                <p class="text-gray-500 mb-6">
                    <?php 
                    if ($itemType === 'lost_found' && $filter === 'resolved') {
                        echo "No resolved items yet. Your resolved items will appear here for history and tracking.";
                    } elseif ($filter === 'sold') {
                        echo "You haven't sold any items yet.";
                    } else {
                        echo "You haven't posted any items yet. Start selling today!";
                    }
                    ?>
                </p>
                <?php if (!($itemType === 'lost_found' && $filter === 'resolved')): ?>
                    <button onclick="openPostModal()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                        <i class="fas fa-plus mr-2"></i> Post Your First Item
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function markAsResolved(itemId) {
        if (confirm('Mark this item as resolved?')) {
            const formData = new FormData();
            formData.append('item_id', itemId);
            
            fetch('<?php echo baseUrl('includes/lost-found/mark-resolved.php'); ?>', {
                method: 'POST',
                body: JSON.stringify({ item_id: itemId }),
                headers: {
                    'Content-Type': 'application/json',
                }
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
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    }
    
    function markAsSold(listingId) {
        if (confirm('Mark this item as sold?')) {
            fetch('<?php echo baseUrl('includes/listing/mark-sold.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `listing_id=${listingId}&csrf_token=<?php echo generateCSRFToken(); ?>`
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
    
    function confirmDelete(listingId) {
        if (confirm('Are you sure you want to delete this listing? This action cannot be undone.')) {
            fetch('<?php echo baseUrl('includes/listing/delete-listing.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `listing_id=${listingId}&csrf_token=<?php echo generateCSRFToken(); ?>`
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
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
