<?php
/**
 * Trending Page
 * Shows the most popular and trending items
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Trending - CampMart";

// Get database instance
$db = Database::getInstance();

// Fetch trending listings using weighted interactions over the last 7 days:
// weights: views 50%, comments 30%, saves/bookmarks 20%
$trendingListings = [];

// Ensure listing_views exists (if not already created elsewhere)
$db->query("CREATE TABLE IF NOT EXISTS listing_views (
    view_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (listing_id),
    INDEX (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $db->prepare(
    "SELECT l.*, u.first_name, u.last_name, c.category_name,
            (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image,
            (SELECT COUNT(*) FROM listing_views lv WHERE lv.listing_id = l.listing_id AND lv.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_views,
            (SELECT COUNT(*) FROM messages m JOIN chats ch ON m.chat_id = ch.chat_id WHERE ch.listing_id = l.listing_id AND m.sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_comments,
            (SELECT COUNT(*) FROM bookmarks b WHERE b.listing_id = l.listing_id AND b.bookmarked_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_saves,
            (( (SELECT COUNT(*) FROM listing_views lv WHERE lv.listing_id = l.listing_id AND lv.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) * 0.5 ) +
             ( (SELECT COUNT(*) FROM messages m JOIN chats ch ON m.chat_id = ch.chat_id WHERE ch.listing_id = l.listing_id AND m.sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) * 0.3 ) +
             ( (SELECT COUNT(*) FROM bookmarks b WHERE b.listing_id = l.listing_id AND b.bookmarked_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) * 0.2 )
            ) as score
     FROM listings l
     JOIN users u ON l.user_id = u.user_id
     JOIN categories c ON l.category_id = c.category_id
     WHERE l.status = 'active'
     ORDER BY score DESC, l.posted_at DESC
     LIMIT 24"
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $trendingListings[] = $row;
    }
    $stmt->close();
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                <i class="fas fa-fire text-orange-400"></i> Trending Items
            </h1>
            <p class="text-xl text-gray-100">
                Discover what's popular on campus right now!
            </p>
        </div>
    </div>
</section>

<!-- Trending Stats -->
<section class="bg-white py-8 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="p-4">
                <div class="text-3xl font-bold text-primary"><?php echo count($trendingListings); ?></div>
                <div class="text-gray-600 text-sm">Trending Items</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-primary">
                    <i class="fas fa-arrow-up text-green-500"></i> Popular
                </div>
                <div class="text-gray-600 text-sm">This Week</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-primary">24/7</div>
                <div class="text-gray-600 text-sm">Updated</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="text-gray-600 text-sm">Active Buyers</div>
            </div>
        </div>
    </div>
</section>

<!-- Trending Listings -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($trendingListings)): ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($trendingListings as $listing): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer group hover:shadow-xl transition" onclick="showCardFeature(event, <?php echo htmlspecialchars(json_encode(['id' => $listing['listing_id'], 'title' => $listing['title'], 'description' => $listing['description'], 'available_today' => $listing['is_available_today'] ?? false])); ?>)">
                        <!-- Image -->
                        <div class="relative h-48">
                            <img src="<?php echo baseUrl(htmlspecialchars($listing['primary_image'] ?? 'assets/images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($listing['title']); ?>" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Bookmark Icon -->
                            <?php if (isLoggedIn()): ?>
                                <button onclick="event.stopPropagation(); toggleBookmark(<?php echo $listing['listing_id']; ?>, this);" 
                                    class="absolute top-2 right-2 bg-white rounded-full p-2 hover:bg-gray-100 transition shadow-lg">
                                    <i class="far fa-bookmark text-gray-700"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 truncate">
                                <?php echo htmlspecialchars($listing['title']); ?>
                            </h3>
                            
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xl font-bold text-primary">
                                    <?php echo formatPrice($listing['price']); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($listing['condition_status']); ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-tag mr-1"></i>
                                <?php echo htmlspecialchars($listing['category_name']); ?>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600 mb-3">
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo timeAgo($listing['posted_at']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-fire text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">No Trending Items Yet</h3>
                <p class="text-gray-500">Check back later for trending items!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-primary text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Want Your Item to Trend?</h2>
        <p class="text-xl mb-8">Post high-quality items and engage with buyers to get trending!</p>
        <?php if (isLoggedIn()): ?>
            <button onclick="openPostModal()" class="px-8 py-4 bg-white text-primary rounded-lg hover:bg-gray-100 transition font-semibold text-lg shadow-lg">
                <i class="fas fa-plus mr-2"></i> Post Your Item
            </button>
        <?php else: ?>
            <button onclick="openRegisterModal()" class="px-8 py-4 bg-white text-primary rounded-lg hover:bg-gray-100 transition font-semibold text-lg shadow-lg">
                Join CampMart
            </button>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
