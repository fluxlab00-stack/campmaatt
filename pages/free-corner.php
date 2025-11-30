<?php
/**
 * Free Corner Page
 * Browse all free items
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Free Corner - CampMart";

// Get database instance
$db = Database::getInstance();

// Get filter parameters
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$page = intval($_GET['page'] ?? 1);

// Get current user's campus
$userCampusId = null;
if (isLoggedIn()) {
    $userCampusId = $_SESSION['campus_id'] ?? null;
}

// Build query
$whereConditions = ["l.status = 'active'", "l.is_free = 1"];
$params = [];
$types = "";

// By default show free items across all campuses

if (!empty($search)) {
    $whereConditions[] = "(l.title LIKE ? OR l.description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($category)) {
    $whereConditions[] = "c.category_name = ?";
    $params[] = $category;
    $types .= "s";
}

$whereClause = implode(' AND ', $whereConditions);

// Count total results
$countQuery = "SELECT COUNT(*) as total FROM listings l 
               JOIN categories c ON l.category_id = c.category_id 
               JOIN users u ON l.user_id = u.user_id
               WHERE {$whereClause}";

if (!empty($params)) {
    $stmt = $db->prepare($countQuery, $types, $params);
} else {
    $stmt = $db->getConnection()->prepare($countQuery);
}

$stmt->execute();
$totalResult = $stmt->get_result();
$totalRows = $totalResult->fetch_assoc()['total'];
$stmt->close();

// Pagination
$pagination = paginate($totalRows, $page, ITEMS_PER_PAGE);

// Fetch listings
$query = "SELECT l.*, u.first_name, u.last_name, c.category_name,
          (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
          FROM listings l
          JOIN users u ON l.user_id = u.user_id
          JOIN categories c ON l.category_id = c.category_id
          WHERE {$whereClause}
          ORDER BY l.posted_at DESC
          LIMIT ? OFFSET ?";

$allParams = array_merge($params, [$pagination['items_per_page'], $pagination['offset']]);
$allTypes = $types . "ii";

$stmt = $db->prepare($query, $allTypes, $allParams);
$stmt->execute();
$result = $stmt->get_result();

$listings = [];
while ($row = $result->fetch_assoc()) {
    $listings[] = $row;
}
$stmt->close();

// Get all categories
$categories = [];
$catResult = $db->query("SELECT category_name FROM categories ORDER BY category_name");
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row['category_name'];
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold mb-4 flex items-center justify-center">
            <i class="fas fa-gift text-4xl mr-4"></i>
            Free Corner
        </h1>
        <p class="text-2xl text-gray-100 mb-2">Everything Here Costs ₦0 For Real</p>
        <p class="text-lg text-gray-200">Your chance to grab essentials or give back to the campus community</p>
        <div class="mt-6 text-3xl font-bold">
            <?php echo number_format($totalRows); ?> Free Items Available
        </div>
    </div>
</section>



<!-- Filters and Content -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search Free Items</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                        placeholder="Search..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-end gap-4">
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                            Apply Filters
                    </button>
                    <a href="free-corner.php" class="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                        Clear
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Listings Grid -->
        <?php if (empty($listings)): ?>
            <div class="text-center py-16">
                <i class="fas fa-gift text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">No free items found</h3>
                <p class="text-gray-600 mb-6">Be the first to share something with the community!</p>
                <?php if (isLoggedIn()): ?>
                    <button onclick="openPostModal()" class="px-8 py-3 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                        Post a Free Item
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($listings as $item): ?>
                        <div class="bg-white rounded-lg shadow-custom overflow-hidden border-2 border-primary cursor-pointer group hover:shadow-lg transition" data-listing-id="<?php echo (int)$item['listing_id']; ?>" onclick="showCardFeature(event, <?php echo htmlspecialchars(json_encode(['id' => $item['listing_id'], 'title' => $item['title'], 'description' => $item['description'], 'available_today' => $item['is_available_today'] ?? false])); ?>)">
                            <div class="relative">
                                <img src="<?php echo baseUrl(htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.svg')); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="w-full h-48 object-cover">
                                <span class="absolute top-2 right-2 bg-primary text-white text-xs px-3 py-1 rounded-full font-semibold">
                                    FREE
                                </span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg text-gray-900 mb-2 truncate">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <p class="text-primary font-bold text-xl mb-2">
                                    Free
                                </p>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-tag mr-1"></i>
                                    <?php echo htmlspecialchars($item['category_name']); ?>
                                </p>
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs text-gray-500">
                                        by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        <?php echo timeAgo($item['posted_at']); ?>
                                    </p>
                                </div>
                                <?php if (isLoggedIn()): ?>
                                    <div class="flex justify-end mt-2">
                                        <button data-listing-id="<?php echo (int)$item['listing_id']; ?>" onclick="event.stopPropagation(); toggleBookmark(<?php echo (int)$item['listing_id']; ?>, this);" 
                                            class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Save item">
                                            <i class="far fa-bookmark text-primary"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="mt-12 flex justify-center">
                    <nav class="flex space-x-2">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <?php if ($i == $pagination['current_page']): ?>
                                <span class="px-4 py-2 bg-primary text-white rounded-lg font-semibold">
                                    <?php echo $i; ?>
                                </span>
                            <?php elseif ($i == 1 || $i == $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif (abs($i - $pagination['current_page']) == 3): ?>
                                <span class="px-4 py-2">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Next
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
    </div>
</section>
<!-- Info Section -->
<section class="py-8 bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="bg-pink-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-hand-holding-heart text-3xl text-primary"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Give & Receive</h3>
                <p class="text-gray-600 text-sm">Share items you no longer need or find what you're looking for</p>
            </div>
            <div>
                <div class="bg-pink-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-recycle text-3xl text-primary"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Reduce Waste</h3>
                <p class="text-gray-600 text-sm">Help reduce waste by giving items a second life</p>
            </div>
            <div>
                <div class="bg-pink-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-3xl text-primary"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Build Community</h3>
                <p class="text-gray-600 text-sm">Connect with fellow students and support each other</p>
            </div>
        </div>
    </div>
</section>
<!-- Call to Action -->
<?php if (isLoggedIn()): ?>
<section class="py-16 bg-primary text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Have Something to Share?</h2>
        <p class="text-xl mb-8">Post your free items and help a fellow student today!</p>
        <button onclick="openPostModal()" class="px-8 py-3 bg-white text-primary rounded-lg hover:bg-gray-100 transition font-semibold text-lg">
            <i class="fas fa-plus mr-2"></i> Post Free Item
        </button>
    </div>
</section>
<?php endif; ?>



<?php include __DIR__ . '/../includes/footer.php'; ?>
