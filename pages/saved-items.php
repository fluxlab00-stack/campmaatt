<?php
/**
 * Saved Items Page
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

$pageTitle = "Saved Items - CampMart";

// Get database instance
$db = Database::getInstance();

// Get current user
$user = getCurrentUser();
$userId = $user['user_id'];

// Fetch saved/bookmarked listings
// Fetch saved/bookmarked items (both marketplace listings and lost & found)
$savedListings = [];
$sql = "(
    SELECT
        b.bookmark_id,
        'listing' as item_type,
        l.listing_id as item_id,
        l.user_id as owner_id,
        l.title as title,
            l.description as description,
        (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image,
        l.price as price,
        l.condition_status as condition_status,
        c.category_name as category_name,
        u.first_name as first_name,
        u.last_name as last_name,
        NULL as lf_item_type,
        NULL as location_lost_found,
        b.bookmarked_at
    FROM bookmarks b
    JOIN listings l ON (b.item_type = 'listing' AND b.item_id = l.listing_id) OR (b.item_type IS NULL AND b.listing_id = l.listing_id)
    JOIN users u ON l.user_id = u.user_id
    LEFT JOIN categories c ON l.category_id = c.category_id
    WHERE b.user_id = ? AND l.status = 'active'
)
UNION ALL
(
    SELECT
        b.bookmark_id,
        'lost_found' as item_type,
        lf.lost_found_id as item_id,
        lf.user_id as owner_id,
        lf.item_name as title,
            lf.description as description,
        (SELECT image_url FROM lost_found_images WHERE lost_found_id = lf.lost_found_id LIMIT 1) as primary_image,
        NULL as price,
        NULL as condition_status,
        'Lost & Found' as category_name,
        u.first_name as first_name,
        u.last_name as last_name,
        lf.item_type as lf_item_type,
        lf.location_lost_found as location_lost_found,
        b.bookmarked_at
    FROM bookmarks b
    JOIN lost_found lf ON b.item_type = 'lost_found' AND b.item_id = lf.lost_found_id
    JOIN users u ON lf.user_id = u.user_id
    WHERE b.user_id = ? AND lf.status = 'active'
)
ORDER BY bookmarked_at DESC";

$stmt = $db->prepare($sql, "ii", [$userId, $userId]);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $savedListings[] = $row;
    }
    $stmt->close();
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-2">
            <i class="fas fa-bookmark mr-2"></i> Saved Items
        </h1>
        <p class="text-xl text-gray-100">
            Keep track of items you're interested in
        </p>
    </div>
</section>

<!-- Statistics -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
            <div class="text-lg text-gray-700">
                <span class="font-semibold"><?php echo count($savedListings); ?></span> saved items
            </div>
            <a href="marketplace.php" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                <i class="fas fa-search mr-2"></i> Browse More Items
            </a>
        </div>
    </div>
</section>

<!-- Saved Items Grid -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($savedListings)): ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($savedListings as $listing): ?>
                    <?php
                        $itemType = $listing['item_type'];
                        $itemId = $listing['item_id'];
                        $cardPayload = ['id' => $itemId, 'type' => $itemType, 'title' => $listing['title'], 'description' => $listing['description'] ?? ''];
                    ?>
                            <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer group hover:shadow-xl transition" onclick="showCardFeature(event, <?php echo htmlspecialchars(json_encode($cardPayload)); ?>)">
                        <!-- Image -->
                        <div class="relative h-48">
                                    <img src="<?php echo baseUrl(htmlspecialchars($listing['primary_image'] ?? 'assets/images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($listing['title']); ?>" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Badges -->
                            <div class="absolute top-2 left-2 flex gap-1">
                                <!-- Hot badge removed -->
                                <?php if (!empty($listing['is_free'])): ?>
                                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                        Free
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                        
                        <!-- Content -->
                        <div class="p-4">
                        </div>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 truncate">
                                <?php
                                    // For lost_found items show a prefix like "Found at ..." or "Lost at ..."
                                    if ($listing['item_type'] === 'lost_found') {
                                        $lfType = $listing['lf_item_type'] ?? '';
                                        $loc = $listing['location_lost_found'] ?? '';
                                        $prefix = '';
                                        if ($lfType === 'found') {
                                            $prefix = 'Found at ';
                                        } elseif ($lfType === 'lost') {
                                            $prefix = 'Lost at ';
                                        }
                                        if (!empty($loc)) {
                                            echo htmlspecialchars($prefix . $loc);
                                        } else {
                                            echo htmlspecialchars($listing['title']);
                                        }
                                    } else {
                                        echo htmlspecialchars($listing['title']);
                                    }
                                ?>
                            </h3>
                            
                            <div class="flex items-center justify-between mb-2">
                                <?php if ($listing['item_type'] === 'listing'): ?>
                                <span class="text-xl font-bold text-primary">
                                    <?php echo formatPrice($listing['price']); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($listing['condition_status']); ?>
                                </span>
                                <?php else: ?>
                                <span class="text-sm text-gray-600">Lost &amp; Found</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-tag mr-1"></i>
                                <?php echo htmlspecialchars($listing['category_name']); ?>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600 mb-3">
                                <i class="fas fa-user mr-1"></i>
                                <?php echo htmlspecialchars($listing['first_name'] . ' ' . $listing['last_name']); ?>
                            </div>
                            
                            <div class="text-xs text-gray-500 mb-3">
                                Saved <?php echo timeAgo($listing['bookmarked_at']); ?>
                            </div>
                        </div>
                        <!-- Footer actions -->
                        <div class="p-3 border-t bg-white flex items-center justify-between">
                            <div>
                                <button onclick="event.stopPropagation(); removeBookmark(<?php echo htmlspecialchars(json_encode(['type' => $listing['item_type'], 'item_id' => $listing['item_id']])); ?>)" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                                    <i class="fas fa-bookmark mr-2"></i> Remove
                                </button>
                            </div>
                            <div>
                                <?php if ($listing['item_type'] === 'listing' && (int)$listing['owner_id'] !== (int)$userId): ?>
                                    <a href="<?php echo baseUrl('pages/chat.php?listing_id=' . (int)$listing['item_id'] . '&seller_id=' . (int)$listing['owner_id']); ?>" class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-green-600 transition text-sm">
                                        <i class="fas fa-comment mr-2"></i> Message Seller
                                    </a>
                                <?php elseif ($listing['item_type'] === 'lost_found' && (int)$listing['owner_id'] !== (int)$userId): ?>
                                    <a href="<?php echo baseUrl('pages/chat.php?seller_id=' . (int)$listing['owner_id']); ?>" class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-green-600 transition text-sm">
                                        <i class="fas fa-comment mr-2"></i> Message Poster
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-bookmark text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">No Saved Items</h3>
                <p class="text-gray-500 mb-6">
                    Start browsing and bookmark items you're interested in!
                </p>
                <a href="marketplace.php" class="inline-block px-6 py-3 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                    <i class="fas fa-search mr-2"></i> Explore Marketplace
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function removeBookmark(payload) {
        // payload may be a number (listing_id) or an object { type, item_id }
        if (typeof payload === 'number') {
            payload = { type: 'listing', listing_id: payload };
        }

        if (!confirm('Remove this item from your saved items?')) return;

        fetch('<?php echo baseUrl('includes/listing/toggle-bookmark.php'); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
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
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
