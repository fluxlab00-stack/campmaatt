<?php
/**
 * Marketplace Page
 * Browse and search all listings
 * 
 * This page uses the same shared query function (getMarketplaceListings) 
 * as the homepage to ensure data consistency and automatic sync.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Marketplace - CampMart";

// Get database instance
$db = Database::getInstance();

// Get filter parameters
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$minPrice = floatval($_GET['min_price'] ?? 0);
$maxPrice = floatval($_GET['max_price'] ?? 0);
$condition = sanitize($_GET['condition'] ?? '');
$sortBy = sanitize($_GET['sort'] ?? 'newest');
$isFree = isset($_GET['free']) ? 1 : 0;
$isAvailableToday = isset($_GET['available_today']) ? 1 : 0;
$page = intval($_GET['page'] ?? 1);

// Get current user's campus and state
$userCampusId = null;
$userCurrentState = null;
if (isLoggedIn()) {
    $userCampusId = $_SESSION['campus_id'] ?? null;
    $userCurrentState = $_SESSION['current_state'] ?? null;
}

// Calculate pagination
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Fetch listings using the shared query function
// This ensures homepage and marketplace page use the same data source
$marketplaceData = getMarketplaceListings($db, [
    'userCampusId' => $userCampusId,
    'currentState' => $userCurrentState,
    'limit' => $perPage,
    'offset' => $offset,
    'search' => $search,
    'category' => $category,
    'minPrice' => $minPrice,
    'maxPrice' => $maxPrice,
    'condition' => $condition,
    'isFree' => $isFree,
    'isAvailableToday' => $isAvailableToday,
    'sortBy' => $sortBy,
    'includeBookmarks' => isLoggedIn(),
    'currentUserId' => isLoggedIn() ? getCurrentUserId() : 0
]);

$listings = $marketplaceData['listings'];
$totalRows = $marketplaceData['total'];
$pagination = paginate($totalRows, $page, $perPage);

// Get all categories for filter
$categories = [];
$catResult = $db->query("SELECT category_name FROM categories ORDER BY category_name");
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row['category_name'];
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">
            <?php if ($isFree): ?>
                Free Corner
            <?php elseif ($isAvailableToday): ?>
                ⏰ Available Today
            <?php elseif ($isAvailableToday): ?>
                ⏰ Available Today
            <?php elseif (!empty($search)): ?>
                Search Results for "<?php echo htmlspecialchars($search); ?>"
            <?php elseif (!empty($category)): ?>
                <?php echo htmlspecialchars($category); ?>
            <?php else: ?>
                Marketplace
            <?php endif; ?>
        </h1>
        <p class="text-gray-100">
            <?php echo number_format($totalRows); ?> item<?php echo $totalRows != 1 ? 's' : ''; ?> found
        </p>
    </div>
</section>

<!-- Filters and Content -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                
                <!-- Search -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                        placeholder="Search items..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <!-- Category -->
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
                
                <!-- Price Range -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Price Range</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" 
                            placeholder="Min" class="w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <input type="number" name="max_price" value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>" 
                            placeholder="Max" class="w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                </div>
                
                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sort By</label>
                    <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                </div>
                
                <!-- Checkboxes -->
                <div class="md:col-span-4 flex flex-wrap gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="free" <?php echo $isFree ? 'checked' : ''; ?> class="mr-2">
                        <span class="text-sm text-gray-700">Free Items Only</span>
                    </label>
                    <!-- Hot Deals filter removed -->
                    <label class="flex items-center">
                        <input type="checkbox" name="available_today" <?php echo $isAvailableToday ? 'checked' : ''; ?> class="mr-2">
                        <span class="text-sm text-gray-700">Available Today</span>
                    </label>
                </div>
                
                <!-- Submit -->
                <div class="md:col-span-4 flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                        Apply Filters
                    </button>
                    <a href="marketplace.php" class="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Listings Grid -->
        <?php if (empty($listings)): ?>
            <div class="text-center py-16">
                <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">No items found</h3>
                <p class="text-gray-600">Try adjusting your filters or search terms</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($listings as $item): ?>
                    <div class="bg-white rounded-lg shadow-custom overflow-hidden cursor-pointer group hover:shadow-lg transition flex flex-col"
                         onclick="window.location.href='<?php echo baseUrl('pages/listing-detail.php?id=' . $item['listing_id']); ?>'">
                        <div class="relative">
                            <img src="<?php echo baseUrl(htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                 class="w-full h-48 object-cover">
                            
                            <!-- Hot badge removed -->
                            
                            <?php if ($item['is_free']): ?>
                                <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
                                    FREE
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-semibold text-lg text-gray-900 mb-2 truncate">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h3>
                            <p class="text-primary font-bold text-xl mb-2">
                                <?php echo formatPrice($item['price']); ?>
                            </p>
                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-tag mr-1"></i>
                                <?php echo htmlspecialchars($item['category_name']); ?>
                            </p>
                            <div class="flex items-center justify-between mb-3 flex-grow">
                                <p class="text-xs text-gray-500">
                                    by <a href="<?php echo baseUrl('pages/user-profile.php?id=' . $item['user_id']); ?>" 
                                           onclick="event.stopPropagation()" 
                                           class="text-primary hover:underline font-semibold">
                                        <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                    </a>
                                </p>
                                <p class="text-xs text-gray-400">
                                    <?php echo timeAgo($item['posted_at']); ?>
                                </p>
                            </div>
                            
                            <!-- Bookmark Icon - Bottom Right -->
                            <?php if (isLoggedIn()): ?>
                                <div class="flex justify-end">
                                    <button onclick="event.stopPropagation(); toggleBookmark(<?php echo $item['listing_id']; ?>, this);" 
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php if (isLoggedIn()): ?>
<div class="fixed right-6 bottom-6 z-50">
    <button type="button" onclick="openPostModal()" 
       class="w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg hover:bg-green-600 hover:scale-110 transition transform" 
       title="Post Item">
        <i class="fas fa-plus text-2xl"></i>
    </button>
</div>
<?php endif; ?>
