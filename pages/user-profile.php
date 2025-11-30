<?php
/**
 * Public User Profile Page
 * Displays user's public profile with their marketplace listings
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Get user ID from URL parameter
$userId = intval($_GET['id'] ?? 0);

if ($userId <= 0) {
    header("Location: " . baseUrl('index.php'));
    exit();
}

// Get database instance
$db = Database::getInstance();

// Fetch user details
$stmt = $db->prepare(
    "SELECT u.*, c.campus_name, d.department_name, lv.level_name
     FROM users u
     LEFT JOIN campuses c ON u.campus_id = c.campus_id
     LEFT JOIN departments d ON u.department_id = d.department_id
     LEFT JOIN levels lv ON u.level_id = lv.level_id
     WHERE u.user_id = ?",
    "i",
    [$userId]
);

if (!$stmt) {
    header("Location: " . baseUrl('index.php'));
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: " . baseUrl('index.php'));
    exit();
}

$userDetails = $result->fetch_assoc();
$stmt->close();

$pageTitle = htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']) . " - CampMart";

// Get user's active listings
$listings = [];
$stmt = $db->prepare(
    "SELECT l.*, c.category_name,
            (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
     FROM listings l
     JOIN categories c ON l.category_id = c.category_id
     WHERE l.user_id = ? AND l.status = 'active'
     ORDER BY l.posted_at DESC",
    "i",
    [$userId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
    $stmt->close();
}

// Get user statistics
$totalListings = count($listings);
$totalSold = 0;
$totalViews = 0;

$statsStmt = $db->prepare(
    "SELECT COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_count,
            SUM(views_count) as total_views
     FROM listings WHERE user_id = ?",
    "i",
    [$userId]
);

if ($statsStmt) {
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    if ($statsRow = $statsResult->fetch_assoc()) {
        $totalSold = $statsRow['sold_count'] ?? 0;
        $totalViews = $statsRow['total_views'] ?? 0;
    }
    $statsStmt->close();
}

// Get current user's bookmark status for these listings
$userBookmarks = [];
if (isLoggedIn()) {
    $currentUserId = getCurrentUserId();
    $bookmarkStmt = $db->prepare(
        "SELECT listing_id FROM bookmarks WHERE user_id = ? AND listing_id IN (SELECT listing_id FROM listings WHERE user_id = ?)",
        "ii",
        [$currentUserId, $userId]
    );
    if ($bookmarkStmt) {
        $bookmarkStmt->execute();
        $bookmarkResult = $bookmarkStmt->get_result();
        while ($row = $bookmarkResult->fetch_assoc()) {
            $userBookmarks[$row['listing_id']] = true;
        }
        $bookmarkStmt->close();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Profile Header -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6 mb-6">
            <button onclick="window.history.back()" class="text-white hover:text-gray-200 text-xl transition">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-6">
            <!-- Profile Picture -->
            <div class="relative">
                <img src="<?php echo baseUrl(htmlspecialchars($userDetails['profile_picture_url'] ?? 'assets/images/default-avatar.png')); ?>"
                     alt="<?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?>"
                     class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
            </div>

            <!-- User Info -->
            <div class="text-center md:text-left flex-1">
                <h1 class="text-4xl font-bold mb-2">
                    <?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?>
                </h1>

                <div class="text-gray-100 space-y-1 mb-4">
                    <?php if ($userDetails['campus_name']): ?>
                        <p class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-university mr-2"></i>
                            <?php echo htmlspecialchars($userDetails['campus_name']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($userDetails['department_name']): ?>
                        <p class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-book mr-2"></i>
                            <?php echo htmlspecialchars($userDetails['department_name']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($userDetails['level_name']): ?>
                        <p class="flex items-center justify-center md:justify-start">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <?php echo htmlspecialchars($userDetails['level_name']); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Ratings -->
                <?php if ($userDetails['total_ratings'] > 0): ?>
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-4">
                        <span class="text-yellow-300">
                            <?php
                            $rating = $userDetails['average_rating'] ?? 0;
                            $fullStars = floor($rating);
                            for ($i = 0; $i < 5; $i++) {
                                echo '<i class="fas fa-star' . ($i < $fullStars ? '' : ' far') . ' text-yellow-300"></i>';
                            }
                            ?>
                        </span>
                        <span class="text-gray-100">
                            <?php echo number_format($rating, 1); ?> (<?php echo $userDetails['total_ratings']; ?> reviews)
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- User Statistics -->
<section class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-3xl font-bold text-primary"><?php echo $totalListings; ?></p>
                <p class="text-gray-600 text-sm mt-1">Active Listings</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-primary"><?php echo $totalSold; ?></p>
                <p class="text-gray-600 text-sm mt-1">Items Sold</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-primary"><?php echo number_format($totalViews); ?></p>
                <p class="text-gray-600 text-sm mt-1">Total Views</p>
            </div>
        </div>
    </div>
</section>

<!-- User Listings -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">
            <i class="fas fa-store text-primary mr-3"></i>
            Marketplace Listings
        </h2>

        <?php if (!empty($listings)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($listings as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer group"
                         onclick="window.location.href='<?php echo baseUrl('pages/listing-detail.php?id=' . $item['listing_id']); ?>'">
                        
                        <!-- Image -->
                        <div class="relative h-48 overflow-hidden bg-gray-200">
                            <img src="<?php echo baseUrl(htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.jpg')); ?>"
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">

                            <!-- Condition Badge -->
                            <?php if ($item['condition']): ?>
                                <span class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
                                    <?php echo htmlspecialchars(ucfirst($item['condition'])); ?>
                                </span>
                            <?php endif; ?>

                            <!-- Bookmark Button -->
                            <?php if (isLoggedIn() && getCurrentUserId() !== $userId): ?>
                                <button onclick="event.stopPropagation(); toggleBookmark(<?php echo $item['listing_id']; ?>, this);"
                                    class="absolute bottom-2 right-2 bg-white rounded-full p-2 hover:bg-gray-100 transition shadow-lg border border-gray-200"
                                    title="Save item">
                                    <i class="<?php echo isset($userBookmarks[$item['listing_id']]) ? 'fas' : 'far'; ?> fa-bookmark text-primary"></i>
                                </button>
                            <?php endif; ?>

                            <!-- Free Badge -->
                            <?php if ($item['is_free']): ?>
                                <span class="absolute top-2 right-2 bg-green-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
                                    FREE
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h3>

                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                <?php echo htmlspecialchars($item['description'] ?? ''); ?>
                            </p>

                            <!-- Category -->
                            <p class="text-xs text-gray-500 mb-3">
                                <i class="fas fa-tag mr-1"></i>
                                <?php echo htmlspecialchars($item['category_name']); ?>
                            </p>

                            <!-- Price -->
                            <div class="flex justify-between items-center pt-3 border-t">
                                <span class="text-lg font-bold text-primary">
                                    <?php echo $item['is_free'] ? 'FREE' : formatPrice($item['price']); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-eye mr-1"></i>
                                    <?php echo $item['views_count'] ?? 0; ?> views
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="inline-block mb-4">
                    <i class="fas fa-inbox text-6xl text-gray-300"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">No Active Listings</h3>
                <p class="text-gray-500">This user doesn't have any active marketplace listings at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Contact Section -->
<section class="bg-white py-12 border-t">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Interested in an Item?</h2>
        <p class="text-gray-600 mb-6">
            Click on any item above to view details and start a conversation with the seller.
        </p>

        <?php if (isLoggedIn() && getCurrentUserId() !== $userId): ?>
            <a href="<?php echo baseUrl('pages/messages.php'); ?>"
                class="inline-block px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                <i class="fas fa-envelope mr-2"></i> View Messages
            </a>
        <?php elseif (!isLoggedIn()): ?>
            <button onclick="openLoginModal()"
                class="inline-block px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                <i class="fas fa-sign-in-alt mr-2"></i> Login to Message
            </button>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
