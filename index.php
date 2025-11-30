<?php
/**
 * CampMart Homepage
 * Main landing page with hero section and featured listings
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "CampMart - Your Campus Marketplace";

// Get database instance
$db = Database::getInstance();

// Get current user's campus and state
$userCampusId = null;
$userCurrentState = null;
$currentUserId = isLoggedIn() ? getCurrentUserId() : 0;
if (isLoggedIn()) {
    $userCampusId = $_SESSION['campus_id'] ?? null;
    $userCurrentState = $_SESSION['current_state'] ?? null;
}

// Fetch marketplace listings using shared query function
// Homepage shows latest 8 items from the same database query as the marketplace page
$marketplaceData = getMarketplaceListings($db, [
    'userCampusId' => $userCampusId,
    'currentState' => $userCurrentState,
    'limit' => 12, // Fetch 12 to display 8
    'offset' => 0,
    'sortBy' => 'newest',
    'includeBookmarks' => isLoggedIn(),
    'currentUserId' => isLoggedIn() ? getCurrentUserId() : 0
]);

$allListings = $marketplaceData['listings'];

// Ensure free-corner items are reflected on the homepage listings as well.
// Merge free items into the main listing feed while avoiding duplicates and keep newest first.
if (!empty($freeItems)) {
    // Index existing listings by id for quick lookup
    $existingIds = array_column($allListings, 'listing_id');
    $existingIds = array_map('intval', $existingIds);

    foreach ($freeItems as $fi) {
        if (!in_array((int)$fi['listing_id'], $existingIds, true)) {
            array_unshift($allListings, $fi); // prioritize free items at the top
            $existingIds[] = (int)$fi['listing_id'];
        }
    }

    // Keep feed size reasonable (limit to 12)
    if (count($allListings) > 12) {
        $allListings = array_slice($allListings, 0, 12);
    }
}

// Fetch featured free items
$freeItems = [];
if ($userCampusId) {
    $stmt = $db->prepare(
        "SELECT l.*, u.first_name, u.last_name, c.category_name,
                (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
         FROM listings l
         JOIN users u ON l.user_id = u.user_id
         JOIN categories c ON l.category_id = c.category_id
         WHERE l.status = 'active' AND l.is_free = 1 AND u.campus_id = ?
         ORDER BY l.posted_at DESC
         LIMIT 4",
        "i",
        [$userCampusId]
    );
} else {
    $stmt = $db->prepare(
        "SELECT l.*, u.first_name, u.last_name, c.category_name,
                (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
         FROM listings l
         JOIN users u ON l.user_id = u.user_id
         JOIN categories c ON l.category_id = c.category_id
         WHERE l.status = 'active' AND l.is_free = 1
         ORDER BY l.posted_at DESC
         LIMIT 4"
    );
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $freeItems[] = $row;
    }
    $stmt->close();
}

// Hot Deals feature removed

// --- Latest Lost & Found previews (3 latest) ---
$latestLostFound = [];
// Include whether current user bookmarked this lost/found item
$lfQuery = "SELECT lf.*, u.first_name, u.last_name, (SELECT image_url FROM lost_found_images WHERE lost_found_id = lf.lost_found_id LIMIT 1) as primary_image,
            (SELECT bookmark_id FROM bookmarks b WHERE b.user_id = {$currentUserId} AND b.item_type = 'lost_found' AND b.item_id = lf.lost_found_id LIMIT 1) as is_bookmarked
            FROM lost_found lf
            JOIN users u ON lf.user_id = u.user_id
            WHERE lf.status = 'active'";
if ($userCampusId) {
    $lfQuery .= " AND u.campus_id = " . (int)$userCampusId;
}
$lfQuery .= " ORDER BY lf.posted_at DESC LIMIT 5";
$lfRes = $db->query($lfQuery);
if ($lfRes) {
    while ($r = $lfRes->fetch_assoc()) {
        $latestLostFound[] = $r;
    }
}

// --- Trending items for homepage (top 8) using same weighted interactions logic ---
$trendingItems = [];
// Ensure listing_views table exists for scoring
$db->query("CREATE TABLE IF NOT EXISTS listing_views (
    view_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (listing_id),
    INDEX (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Trending mix: 65% top trending by score, 35% broader campus items
$limitTotal = 8;
$topCount = (int)ceil($limitTotal * 0.65);
$restCount = $limitTotal - $topCount;

// Compute score-based top items
$scoreQuery = "SELECT l.listing_id,
            (( (SELECT COUNT(*) FROM listing_views lv WHERE lv.listing_id = l.listing_id AND lv.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) * 0.5 ) +
             ( (SELECT COUNT(*) FROM messages m JOIN chats ch ON m.chat_id = ch.chat_id WHERE ch.listing_id = l.listing_id AND m.sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) * 0.3 ) +
             ( (SELECT COUNT(*) FROM bookmarks b WHERE b.listing_id = l.listing_id AND b.bookmarked_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) * 0.2 )
            ) as score
        FROM listings l
        WHERE l.status = 'active'
        ORDER BY score DESC, l.posted_at DESC
        LIMIT ?";

$stmt = $db->prepare($scoreQuery, "i", [$topCount]);
$topIds = [];
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $topIds[] = (int)$r['listing_id'];
    $stmt->close();
}

// Fetch full records for top IDs
$itemsA = [];
if (!empty($topIds)) {
    $in = implode(',', $topIds);
    $sqlA = "SELECT l.*, u.first_name, u.last_name, c.category_name,
                (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
             FROM listings l
             JOIN users u ON l.user_id = u.user_id
             JOIN categories c ON l.category_id = c.category_id
             WHERE l.listing_id IN ({$in})
             ORDER BY FIELD(l.listing_id, {$in})";
    $resA = $db->query($sqlA);
    if ($resA) {
        while ($r = $resA->fetch_assoc()) $itemsA[] = $r;
    }
}

// Determine campus-based rest items (within user's campus)
$itemsB = [];
if (isLoggedIn() && !empty($userCampusId) && $restCount > 0) {
    $exclude = !empty($topIds) ? ' AND l.listing_id NOT IN (' . implode(',', $topIds) . ')' : '';
    $sqlB = "SELECT l.*, u.first_name, u.last_name, c.category_name,
                (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
             FROM listings l
             JOIN users u ON l.user_id = u.user_id
             JOIN categories c ON l.category_id = c.category_id
             WHERE u.campus_id = {$userCampusId} AND l.status = 'active' {$exclude}
             ORDER BY l.posted_at DESC
             LIMIT ?";
    $stmtB = $db->prepare($sqlB, "i", [$restCount]);
    if ($stmtB) {
        $stmtB->execute();
        $resB = $stmtB->get_result();
        while ($r = $resB->fetch_assoc()) $itemsB[] = $r;
        $stmtB->close();
    }
}

// Merge items (keep trending items priority but allow some shuffle)
$merged = array_merge($itemsA, $itemsB);
// If there's a desire to shuffle slightly while keeping trending prominent, we can interleave
if (!empty($merged)) {
    // If merged is larger than limit, slice after shuffle for a fresher mix
    shuffle($merged);
    $trendingItems = array_slice($merged, 0, $limitTotal);
} else {
    // fallback to original simple query if nothing found
    $fallback = $db->prepare(
        "SELECT l.*, u.first_name, u.last_name, c.category_name,
                (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
         FROM listings l
         JOIN users u ON l.user_id = u.user_id
         JOIN categories c ON l.category_id = c.category_id
         WHERE l.status = 'active'
         ORDER BY l.posted_at DESC
         LIMIT ?",
        "i",
        [$limitTotal]
    );
    if ($fallback) {
        $fallback->execute();
        $rf = $fallback->get_result();
        while ($r = $rf->fetch_assoc()) $trendingItems[] = $r;
        $fallback->close();
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Carousel Section -->
<section class="hero-carousel-wrapper relative h-screen min-h-96 bg-gray-900">
    <!-- Navbar Inside Carousel -->
    <nav class="absolute top-0 left-0 right-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl font-bold">
                        <span class="text-primary">campus Life</span><span class="text-secondary">  Simplified</span>
                    </span>
                </div>
                <div class="flex items-center space-x-6">
                    <?php if (!isLoggedIn()): ?>
                        <div class="flex flex-col sm:flex-row gap-3">
                            
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Carousel Container -->
    <div class="carousel-container relative w-full h-full overflow-hidden">
        <div class="carousel-wrapper flex transition-transform duration-1000 ease-in-out h-full" id="carouselWrapper">
           
           
              <!-- Slide 1 -->
            <div class="carousel-slide min-w-full h-full relative flex items-center justify-center">
                <img src="assets\images\logo.jpg" 
                     alt="Buy and Sell" 
                     class="absolute inset-0 w-full h-full object-cover">
             
                <div class="relative z-10 flex items-center justify-center text-white h-full w-full">
                    <div class="text-center px-4">
                        <h2 class="text-5xl font-bold mb-4" style="text-shadow: 3px 3px 6px rgba(0,0,0,0.8), 0 0 20px rgba(0,0,0,0.5);">Welcome To Campmart</h2>
                        <p class="text-xl" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">Your Campus Marketplace</p>
                    </div>
                </div>
            </div>


            <!-- Slide 2 -->
            <div class="carousel-slide min-w-full h-full relative flex items-center justify-center">
                <img src="assets\images\phone1.jpg" 
                     alt="Buy and Sell" 
                     class="absolute inset-0 w-full h-full object-cover">
             
                <div class="relative z-10 flex items-center justify-center text-white h-full w-full">
                    <div class="text-center px-4">
                        <h2 class="text-5xl font-bold mb-4" style="text-shadow: 3px 3px 6px rgba(0,0,0,0.8), 0 0 20px rgba(0,0,0,0.5);">Buy & Sell with Ease</h2>
                        <p class="text-xl" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">No fees, no stress, just smart trading</p>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-slide min-w-full h-full relative flex items-center justify-center">
                <img src="assets\images\kitchen.webp" 
                     alt="Community" 
                     class="absolute inset-0 w-full h-full object-cover">
             
                <div class="relative z-10 flex items-center justify-center text-white h-full w-full">
                    <div class="text-center px-4">
                        <h2 class="text-5xl font-bold mb-4" style="text-shadow: 3px 3px 6px rgba(0,0,0,0.8), 0 0 20px rgba(0,0,0,0.5);">Campus Community</h2>
                        <p class="text-xl" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">Connect with trusted campus members</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carousel Indicators -->
        <div class="absolute bottom-20 left-1/2 -translate-x-1/2 z-30 flex gap-2">
            <button onclick="goToSlide(0)" class="carousel-indicator w-3 h-3 rounded-full bg-white/50 hover:bg-white transition active" data-slide="0"></button>
            <button onclick="goToSlide(1)" class="carousel-indicator w-3 h-3 rounded-full bg-white/50 hover:bg-white transition" data-slide="1"></button>
            <button onclick="goToSlide(2)" class="carousel-indicator w-3 h-3 rounded-full bg-white/50 hover:bg-white transition" data-slide="2"></button>
        </div>
    </div>
</section>

<!-- Search Bar Section -->
<section class="bg-gray-100 py-6 -mt-12 relative z-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-0">
        <form action="pages/marketplace.php" method="GET" class="flex shadow-xl">
            <input type="text" name="search" placeholder="Search for items, services, accommodation..." 
                class="flex-1 px-4 py-3 text-gray-900 focus:outline-none rounded-l-lg">
            <button type="submit" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white transition rounded-r-lg">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</section>

<!-- Carousel Styles -->
<style>
    .carousel-slide {
        flex-shrink: 0;
        position: relative;
        width: 100%;
        height: 100%;
    }
    
    .carousel-slide img {
        object-fit: cover;
        object-position: center;
    }
    
    .carousel-indicator.active {
        background-color: #FF7A00 !important;
    }
</style>

<!-- Carousel Scripts -->
<script>
let currentSlide = 0;
const totalSlides = 3;
let autoSlideTimer;

function updateCarousel() {
    const wrapper = document.getElementById('carouselWrapper');
    wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    
    // Update indicators
    document.querySelectorAll('.carousel-indicator').forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentSlide);
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel();
    resetAutoSlide();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateCarousel();
    resetAutoSlide();
}

function goToSlide(n) {
    currentSlide = n;
    updateCarousel();
    resetAutoSlide();
}

function resetAutoSlide() {
    clearInterval(autoSlideTimer);
    autoSlideTimer = setInterval(nextSlide, 5000);
}

// Auto-advance carousel every 5 seconds
autoSlideTimer = setInterval(nextSlide, 5000);
</script>



<!-- Hot Deals removed -->

<!-- Free Corner Section -->
<?php if (!empty($freeItems)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-gift text-primary mr-3"></i>
                    Free Corner
                </h2>
                <p class="text-gray-600 mt-2">Everything here costs ₦0 — For real.</p>
            </div>
                        <a href="<?php echo baseUrl('pages/free-corner.php'); ?>" class="text-primary hover:underline font-semibold">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($freeItems as $item): ?>
                <div class="bg-white rounded-lg shadow-custom overflow-hidden border-2 border-primary cursor-pointer group hover:shadow-lg transition flex flex-col" onclick="window.location.href='<?php echo baseUrl('pages/listing-detail.php?id=' . $item['listing_id']); ?>'">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                             class="w-full h-48 object-cover">
                        <span class="absolute top-2 left-2 bg-primary text-white text-xs px-3 py-1 rounded-full font-semibold">
                            FREE
                        </span>
                    </div>
                    <div class="p-4 flex-1 flex flex-col">
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
                        <div class="flex items-center justify-between mb-3 flex-grow">
                            <p class="text-xs text-gray-500">
                                by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?php echo timeAgo($item['posted_at']); ?>
                            </p>
                        </div>
                        <?php if (isLoggedIn()): ?>
                            <?php
                                $isBookmarked = false;
                                if (isLoggedIn()) {
                                    $bstmt = $db->prepare(
                                        "SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND (listing_id = ? OR (item_type = 'listing' AND item_id = ?)) LIMIT 1",
                                        "iii",
                                        [$currentUserId, $item['listing_id'], $item['listing_id']]
                                    );
                                    if ($bstmt) {
                                        $bstmt->execute();
                                        $bres = $bstmt->get_result();
                                        if ($bres && $bres->fetch_assoc()) {
                                            $isBookmarked = true;
                                        }
                                        $bstmt->close();
                                    }
                                }
                            ?>
                            <div class="flex justify-end items-center gap-2">
                                <button data-listing-id="<?php echo (int)$item['listing_id']; ?>" onclick="event.stopPropagation(); toggleBookmark(<?php echo $item['listing_id']; ?>, this);" 
                                    class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Save item">
                                    <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark text-primary"></i>
                                </button>
                                <button onclick="event.stopPropagation(); showCardFeature(event, { id: <?php echo (int)$item['listing_id']; ?>, type: 'listing', title: '<?php echo addslashes(htmlspecialchars($item['title'] ?? '', ENT_QUOTES)); ?>', description: '<?php echo addslashes(htmlspecialchars($item['description'] ?? '', ENT_QUOTES)); ?>' });" class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Comments">
                                    <i class="far fa-comment text-gray-600"></i>
                                </button>
                                <button onclick="event.stopPropagation(); openChatModal(<?php echo (int)($item['user_id'] ?? 0); ?>, <?php echo (int)$item['listing_id']; ?>, '<?php echo addslashes(htmlspecialchars(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''), ENT_QUOTES)); ?>', '<?php echo addslashes(htmlspecialchars($item['primary_image'] ?? 'assets/images/default-avatar.jpg', ENT_QUOTES)); ?>');" class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Message Seller">
                                    <i class="far fa-paper-plane text-gray-600"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- Lost & Found Preview Section moved below Trending -->

<!-- CTA Section -->
<?php if (!isLoggedIn()): ?>
<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="mb-8">
            <span class="text-6xl font-bold text-primary">CampMart</span>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">
            Not everything lost is gone
        </h2>
        <p class="text-lg text-gray-600 mb-4">
            Help recover lost items and find great deals on campus
        </p>
    </div>
</section>
<?php endif; ?>

<!-- Trending Section -->
<!-- Marketplace Preview Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Marketplace</h2>
                <p class="text-gray-600 mt-2">Latest items from your university marketplace</p>
            </div>
            <a href="<?php echo baseUrl('pages/marketplace.php'); ?>" class="text-primary hover:underline font-semibold">
                View Full Marketplace <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach (array_slice($allListings, 0, 8) as $item): ?>
                <div class="bg-white rounded-lg shadow-custom overflow-hidden cursor-pointer group hover:shadow-lg transition flex flex-col"
                     onclick="window.location.href='<?php echo baseUrl('pages/listing-detail.php?id=' . $item['listing_id']); ?>'">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['title'] ?? $item['item_name'] ?? ''); ?>" class="w-full h-48 object-cover">
                    </div>
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="font-semibold text-lg text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($item['title'] ?? $item['item_name'] ?? ''); ?></h3>
                        <p class="text-primary font-bold text-xl mb-2"><?php echo formatPrice($item['price'] ?? 0); ?></p>
                        <p class="text-sm text-gray-600 mb-2"><i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></p>
                        <div class="flex items-center justify-between mb-3 flex-grow">
                            <p class="text-xs text-gray-500">by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></p>
                            <p class="text-xs text-gray-400"><?php echo timeAgo($item['posted_at'] ?? $item['date_posted'] ?? ''); ?></p>
                        </div>
                        <?php if (isLoggedIn()): ?>
                            <?php
                                // Determine bookmark state if not already provided by query
                                $isBookmarked = !empty($item['is_bookmarked']);
                                if (isLoggedIn() && !$isBookmarked) {
                                    $bstmt = $db->prepare(
                                        "SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND (listing_id = ? OR (item_type = 'listing' AND item_id = ?)) LIMIT 1",
                                        "iii",
                                        [$currentUserId, $item['listing_id'], $item['listing_id']]
                                    );
                                    if ($bstmt) {
                                        $bstmt->execute();
                                        $bres = $bstmt->get_result();
                                        if ($bres && $bres->fetch_assoc()) $isBookmarked = true;
                                        $bstmt->close();
                                    }
                                }
                            ?>
                            <div class="flex justify-end items-center gap-2">
                                <button data-listing-id="<?php echo (int)$item['listing_id']; ?>" onclick="event.stopPropagation(); toggleBookmark(<?php echo $item['listing_id']; ?>, this);" 
                                    class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Save item">
                                    <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark text-primary"></i>
                                </button>
                                <button onclick="event.stopPropagation(); showCardFeature(event, { id: <?php echo (int)$item['listing_id']; ?>, type: 'listing', title: '<?php echo addslashes(htmlspecialchars($item['title'] ?? '', ENT_QUOTES)); ?>', description: '<?php echo addslashes(htmlspecialchars($item['description'] ?? '', ENT_QUOTES)); ?>' });" class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Comments">
                                    <i class="far fa-comment text-gray-600"></i>
                                </button>
                                <button onclick="event.stopPropagation(); openChatModal(<?php echo (int)($item['user_id'] ?? 0); ?>, <?php echo (int)$item['listing_id']; ?>, '<?php echo addslashes(htmlspecialchars(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''), ENT_QUOTES)); ?>', '<?php echo addslashes(htmlspecialchars($item['primary_image'] ?? 'assets/images/default-avatar.jpg', ENT_QUOTES)); ?>');" class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Message Seller">
                                    <i class="far fa-paper-plane text-gray-600"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Trending Now</h2>
                <p class="text-gray-600 mt-2">Top items on campus this week.</p>
            </div>
            <a href="<?php echo baseUrl('pages/marketplace.php'); ?>" class="text-primary hover:underline font-semibold">
                View All Listings <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div> 
        <div id="trendingCarousel" class="relative overflow-hidden">
            <div id="trendingWrapper" class="flex transition-transform duration-700 ease-in-out">
                <?php foreach ($trendingItems as $item): ?>
                    <div class="trending-slide w-full lg:w-1/4 px-2 box-border">
                        <div class="bg-white rounded-lg shadow-custom overflow-hidden cursor-pointer group hover:shadow-lg transition flex flex-col" onclick="window.location.href='<?php echo baseUrl('pages/listing-detail.php?id=' . $item['listing_id']); ?>'">
                            <div class="relative">
                                <img src="<?php echo htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="w-full h-48 object-cover">
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
                                        by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        <?php echo timeAgo($item['posted_at']); ?>
                                    </p>
                                </div>
                                <?php if (isLoggedIn()): ?>
                                    <?php
                                        $isBookmarked = !empty($item['is_bookmarked']);
                                        if (isLoggedIn() && !$isBookmarked) {
                                            $bstmt = $db->prepare(
                                                "SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND (listing_id = ? OR (item_type = 'listing' AND item_id = ?)) LIMIT 1",
                                                "iii",
                                                [$currentUserId, $item['listing_id'], $item['listing_id']]
                                            );
                                            if ($bstmt) {
                                                $bstmt->execute();
                                                $bres = $bstmt->get_result();
                                                if ($bres && $bres->fetch_assoc()) $isBookmarked = true;
                                                $bstmt->close();
                                            }
                                        }
                                    ?>
                                    <div class="flex justify-end">
                                        <button onclick="event.stopPropagation(); toggleBookmark(<?php echo $item['listing_id']; ?>, this);" 
                                            class="bg-white rounded-full p-2 hover:bg-gray-100 transition border border-gray-200" title="Save item">
                                            <i class="<?php echo $isBookmarked ? 'fas' : 'far'; ?> fa-bookmark text-primary"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
            (function(){
                // Build a simple auto-advance carousel for trending items
                const wrapper = document.getElementById('trendingWrapper');
                if (!wrapper) return;
                const slides = Array.from(wrapper.children);
                if (slides.length === 0) return;

                // Determine items-per-frame (4 on lg, 2 on sm, 1 on xs)
                function itemsPerFrame(){
                    if (window.matchMedia('(min-width:1024px)').matches) return 4;
                    if (window.matchMedia('(min-width:640px)').matches) return 2;
                    return 1;
                }

                let perFrame = itemsPerFrame();
                let index = 0;

                function updateLayout(){
                    perFrame = itemsPerFrame();
                    // set each slide width to (100 / perFrame)% of container
                    slides.forEach(s => s.style.flex = `0 0 ${100 / perFrame}%`);
                }

                updateLayout();
                window.addEventListener('resize', updateLayout);

                function advance(){
                    const totalFrames = Math.ceil(slides.length / perFrame);
                    index = (index + 1) % totalFrames;
                    const containerWidth = container.clientWidth;
                    const left = index * containerWidth;
                    container.scrollTo({ left: left, behavior: 'smooth' });
                }

                // Auto advance every 4 seconds
                let timer = setInterval(advance, 4000);
                // Pause on hover
                const container = document.getElementById('trendingCarousel');
                container.addEventListener('mouseenter', ()=> clearInterval(timer));
                container.addEventListener('mouseleave', ()=> { clearInterval(timer); timer = setInterval(advance, 4000); });
            })();
        </script>
    </div>
</section>

<!-- Features Section -->
<?php if (!empty($latestLostFound)): ?>
<!-- Lost & Found Section with Tagline and Description -->
<section class="py-12 bg-gradient-to-r from-red-50 to-green-50 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 flex items-center mb-2">
                <i class="fas fa-search text-accent mr-3"></i>
                Found It
            </h2>
            <p class="text-lg text-gray-700 font-semibold mb-2">Because not everything that's lost is gone.</p>
            <p class="text-gray-600">Help someone recover their lost item. Post items you've lost or found around campus.</p>
        </div>

        <div class="flex gap-3 mb-8 flex-wrap">
            <?php if (isLoggedIn()): ?>
                <button onclick="document.getElementById('lostFoundTypeInput').value = 'lost'; openLostFoundModal();" 
                    class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold">
                    <i class="fas fa-exclamation-circle mr-2"></i> Report Lost Item
                </button>
                <button onclick="document.getElementById('lostFoundTypeInput').value = 'found'; openLostFoundModal();" 
                    class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                    <i class="fas fa-check-circle mr-2"></i> Report Found Item
                </button>
            <?php else: ?>
                <button onclick="openLoginModal()" 
                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login to Report
                </button>
            <?php endif; ?>
            <a href="<?php echo baseUrl('pages/lost-found.php'); ?>" 
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                <i class="fas fa-eye mr-2"></i> See All Lost & Found
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach (array_slice($latestLostFound, 0, 8) as $lf): ?>
                <div class="bg-white rounded-lg shadow-custom overflow-hidden cursor-pointer group hover:shadow-lg transition" onclick="window.location.href='<?php echo baseUrl('pages/lost-found-detail.php?id=' . $lf['lost_found_id']); ?>'">
                    <div class="relative h-40">
                        <img src="<?php echo baseUrl(htmlspecialchars($lf['primary_image'] ?? 'assets/images/placeholder.jpg')); ?>" alt="<?php echo htmlspecialchars($lf['item_name']); ?>" class="w-full h-full object-cover">
                        <?php $lfBadgeClass = ($lf['item_type'] === 'lost') ? 'bg-red-500 text-white' : 'bg-primary text-white'; ?>
                        <span class="absolute top-2 left-2 <?php echo $lfBadgeClass; ?> text-xs px-2 py-1 rounded-full font-semibold">
                            <?php echo $lf['item_type'] === 'lost' ? '😢 Lost' : '✓ Found'; ?>
                        </span>
                        <?php if (isLoggedIn()): ?>
                            <div class="absolute top-2 right-2 flex items-center gap-2">
                                <button data-lostfound-id="<?php echo (int)$lf['lost_found_id']; ?>" onclick="event.stopPropagation(); toggleBookmark({type:'lost_found', item_id: <?php echo (int)$lf['lost_found_id']; ?>}, this)" class="bg-white p-2 rounded-full hover:bg-gray-100 transition border border-gray-200" title="Save item">
                                    <i class="<?php echo !empty($lf['is_bookmarked']) ? 'fas' : 'far'; ?> fa-bookmark text-primary"></i>
                                </button>
                                <button onclick="event.stopPropagation(); showCardFeature(event, { id: <?php echo (int)$lf['lost_found_id']; ?>, type: 'lost_found', title: '<?php echo addslashes(htmlspecialchars($lf['item_name'] ?? '', ENT_QUOTES)); ?>', description: '<?php echo addslashes(htmlspecialchars($lf['description'] ?? '', ENT_QUOTES)); ?>' });" class="bg-white p-2 rounded-full hover:bg-gray-100 transition border border-gray-200" title="Comments">
                                    <i class="far fa-comment text-gray-600"></i>
                                </button>
                                <button onclick="event.stopPropagation(); openChatModal(<?php echo (int)$lf['user_id']; ?>, 0, '<?php echo addslashes(htmlspecialchars(($lf['first_name'] ?? '') . ' ' . ($lf['last_name'] ?? ''), ENT_QUOTES)); ?>', '<?php echo addslashes(htmlspecialchars($lf['primary_image'] ?? 'assets/images/default-avatar.jpg', ENT_QUOTES)); ?>');" class="bg-white p-2 rounded-full hover:bg-gray-100 transition border border-gray-200" title="Message Poster">
                                    <i class="far fa-paper-plane text-gray-600"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                        <!-- Edit Button (shown only to item owner) -->
                        <?php if (isLoggedIn() && $_SESSION['user_id'] == $lf['user_id']): ?>
                            <?php if ($lf['status'] === 'active'): ?>
                            <button onclick="event.stopPropagation(); markLostFoundResolved(<?php echo $lf['lost_found_id']; ?>)" 
                                class="absolute top-2 right-12 bg-green-500 text-white p-1.5 rounded-full hover:bg-green-600 transition text-sm shadow-lg" title="Mark as Resolved">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                            <button onclick="event.stopPropagation(); window.location.href='<?php echo baseUrl('pages/edit-lost-found.php?id=' . $lf['lost_found_id']); ?>'" 
                                class="absolute top-2 right-2 bg-blue-500 text-white p-1.5 rounded-full hover:bg-blue-600 transition text-sm shadow-lg">
                                <i class="fas fa-edit"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-sm text-gray-900 truncate"><?php echo htmlspecialchars($lf['item_name']); ?></h3>
                        <p class="text-xs text-gray-600 mb-2 line-clamp-1"><?php echo htmlspecialchars($lf['description']); ?></p>
                        <div class="text-xs text-gray-500 flex justify-between">
                            <span class="truncate"><?php echo htmlspecialchars($lf['location_lost_found'] ?? ''); ?></span>
                            <span><?php echo date('M j', strtotime($lf['posted_at'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose CampMart?</h2>
            <p class="text-gray-600">The smartest way to buy and sell on campus</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-green-600 text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-3xl"></i>
                </div>
                <h3 class="font-semibold text-xl mb-2">Safe & Secure</h3>
                <p class="text-gray-600">Campus-verified users and secure meet points ensure your safety</p>
            </div>
            
            <div class="text-center">
                <div class="bg-green-600 text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-3xl"></i>
                </div>
                <h3 class="font-semibold text-xl mb-2">Fast & Easy</h3>
                <p class="text-gray-600">Post items in seconds, connect instantly with buyers and sellers</p>
            </div>
            
            <div class="text-center">
                <div class="bg-green-600 text-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-3xl"></i>
                </div>
                <h3 class="font-semibold text-xl mb-2">Campus Community</h3>
                <p class="text-gray-600">Buy and sell within your trusted campus community</p>
            </div>
        </div>
    </div>
</section>

<!-- Floating Action Buttons -->
<?php if (isLoggedIn()): ?>
<div class="fixed right-6 bottom-6 flex flex-col gap-4 z-50">
    <!-- Post Item Button (opens listing form) -->
    <button type="button" onclick="openPostModal()" 
       class="w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg hover:bg-green-600 hover:scale-110 transition transform" 
       title="Post Item">
        <i class="fas fa-plus text-2xl"></i>
    </button>
</div>

<!-- Profile Modal -->
<div id="profileModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Profile</h2>
                <button onclick="closeProfileModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Profile Picture -->
                <div class="text-center mb-6">
                    <div class="relative inline-block">
                        <img id="profileModalImage" 
                             src="<?php echo htmlspecialchars($_SESSION['profile_picture'] ?? 'assets/images/default-avatar.jpg'); ?>" 
                             alt="Profile" 
                             class="w-24 h-24 rounded-full object-cover border-4 border-green-600">
                        <label for="profileImageInput" class="absolute bottom-0 right-0 bg-primary text-white rounded-full p-2 cursor-pointer hover:bg-green-600 transition">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="profileImageInput" accept="image/*" class="hidden" onchange="updateProfilePicture(event)">
                    </div>
                </div>

                <!-- User Info -->
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">
                        <?php echo htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')); ?>
                    </h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                </div>

                <!-- Edit Profile Button -->
                <a href="<?php echo baseUrl('pages/profile.php'); ?>" 
                   class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center font-semibold">
                    Edit Profile
                </a>

                <!-- Logout Button -->
                <a href="<?php echo baseUrl('pages/logout.php'); ?>" 
                   class="block w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-center font-semibold">
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function openProfileModal() {
    document.getElementById('profileModal').classList.remove('hidden');
}

function closeProfileModal() {
    document.getElementById('profileModal').classList.add('hidden');
}

function updateProfilePicture(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileModalImage').src = e.target.result;
                // Send to server via AJAX to update profile picture
                const form = new FormData();
                form.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
                form.append('first_name', '<?php echo htmlspecialchars($_SESSION['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>');
                form.append('last_name', '<?php echo htmlspecialchars($_SESSION['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>');
                form.append('phone_number', '<?php echo htmlspecialchars($_SESSION['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>');
                // profile picture file
                form.append('profile_picture', file);

                fetch('<?php echo baseUrl('includes/profile/update-profile.php'); ?>', {
                    method: 'POST',
                    body: form,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        if (data.profile_picture) {
                            document.getElementById('profileModalImage').src = data.profile_picture;
                        }
                        alert(data.message || 'Profile updated');
                    } else {
                        alert(data.message || 'Failed to update profile');
                    }
                }).catch(err => {
                    console.error(err);
                    alert('Failed to update profile picture');
                });
        };
        reader.readAsDataURL(file);
    }
}

// Close modal when clicking outside
document.getElementById('profileModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeProfileModal();
    }
});

// Mark Lost/Found item as resolved from homepage
function markLostFoundResolved(itemId) {
    if (!confirm('Mark this item as resolved?')) return;
    
    fetch('<?php echo baseUrl('includes/lost-found/mark-resolved.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>'
        },
        body: JSON.stringify({ item_id: itemId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✓ Item marked as resolved!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Network error', 'error');
    });
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
