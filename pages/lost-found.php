<?php
/**
 * Found It Page
 * Help students find their lost items and return found items
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Found It - CampMart";

// Get database instance
$db = Database::getInstance();

// Get filter parameter
$filter = sanitize($_GET['filter'] ?? 'all');

// Get current user's campus
$userCampusId = null;
if (isLoggedIn()) {
    $userCampusId = $_SESSION['campus_id'] ?? null;
}
$currentUserId = isLoggedIn() ? getCurrentUserId() : 0;

// Build query based on filter
$whereCondition = "1=1";
if ($filter === 'all' || $filter === 'lost' || $filter === 'found') {
    $whereCondition = "lf.status = 'active'";
    if ($filter === 'lost') {
        $whereCondition .= " AND lf.item_type = 'lost'";
    } elseif ($filter === 'found') {
        $whereCondition .= " AND lf.item_type = 'found'";
    }
} elseif ($filter === 'resolved') {
    $whereCondition = "lf.status = 'resolved'";
}

// Get resolved count
$resolvedCount = 0;
$resolvedResult = $db->query("SELECT COUNT(*) as count FROM lost_found WHERE status = 'resolved'");
if ($resolvedResult) {
    $resolvedCount = $resolvedResult->fetch_assoc()['count'];
}

// Add campus filter if user is logged in
if ($userCampusId) {
    $whereCondition .= " AND u.campus_id = {$userCampusId}";
}

// Fetch lost and found items
$items = [];
$query = "SELECT lf.*, u.first_name, u.last_name, u.phone_number,
                 (SELECT image_url FROM lost_found_images WHERE lost_found_id = lf.lost_found_id LIMIT 1) as primary_image,
                 (SELECT bookmark_id FROM bookmarks b WHERE b.user_id = {$currentUserId} AND b.item_type = 'lost_found' AND b.item_id = lf.lost_found_id LIMIT 1) as is_bookmarked
          FROM lost_found lf
          JOIN users u ON lf.user_id = u.user_id
          WHERE {$whereCondition}
          ORDER BY lf.posted_at DESC";

$result = $db->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                <i class="fas fa-search text-yellow-300"></i> Found It
            </h1>
            <p class="text-xl text-gray-100">
                Help your fellow students return items or report what you've found
            </p>
        </div>
    </div>
</section>

<!-- Info Section -->
<section class="bg-white py-8 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="p-6 bg-red-50 rounded-lg">
                <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-3"></i>
                <h3 class="font-semibold text-gray-900 mb-2">Lost Something?</h3>
                <p class="text-sm text-gray-600">Post details about your lost item and connect with finders</p>
            </div>
            <div class="p-6 bg-green-50 rounded-lg">
                <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                <h3 class="font-semibold text-gray-900 mb-2">Found Something?</h3>
                <p class="text-sm text-gray-600">Help return found items to their rightful owners</p>
            </div>
            <div class="p-6 bg-blue-50 rounded-lg">
                <i class="fas fa-handshake text-4xl text-blue-500 mb-3"></i>
                <h3 class="font-semibold text-gray-900 mb-2">Connect Safely</h3>
                <p class="text-sm text-gray-600">Use our secure platform to coordinate returns</p>
            </div>
        </div>
    </div>
</section>

<!-- Claiming Guidelines -->
<section class="bg-yellow-50 py-6 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-lg p-4 bg-yellow-50 border border-yellow-200">
            <h4 class="font-semibold text-gray-900 mb-2">Quick Tip — Confirming a Claim</h4>
            <p class="text-sm text-gray-700 mb-2">Before handing over an item, ask the claimant for a unique proof of ownership and meet in a safe public place.</p>
            <ul class="text-sm text-gray-700 list-disc list-inside mb-2">
                <li>Request a photo of the item showing a unique mark or receipt.</li>
                <li>Ask for a clear description matching the post (color, serial, distinguishing marks).</li>
                <li>Coordinate using CampMart messaging or WhatsApp and meet at a public, well-lit location.</li>
                <li>Bring a campus ID — do not share bank details or personal information.</li>
            </ul>
            <p class="text-sm">Read more: <a href="<?php echo baseUrl('pages/how-it-works.php'); ?>#safety" class="text-primary hover:underline">How It Works</a> • <a href="<?php echo baseUrl('pages/faq.php'); ?>" class="text-primary hover:underline">FAQ</a></p>
        </div>
    </div>
</section>

<!-- Resolved Count Banner -->
<?php if ($resolvedCount > 0): ?>
<section class="bg-green-50 py-3 border-b border-green-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-green-700 font-semibold">
            <i class="fas fa-check-circle mr-2"></i>✔ <?php echo $resolvedCount; ?> item<?php echo $resolvedCount !== 1 ? 's have' : ' has'; ?> been resolved on CampMart
        </p>
    </div>
</section>
<?php else: ?>
<section class="bg-blue-50 py-3 border-b border-blue-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-blue-700 font-semibold">
            <i class="fas fa-lightbulb mr-2"></i>No resolved items yet — help someone recover their lost item.
        </p>
    </div>
</section>
<?php endif; ?>

<!-- Filter Tabs -->
<section class="bg-gray-50 py-6 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center gap-4 flex-wrap">
            <a href="?filter=all" 
               class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'all' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-list mr-2"></i> All Items
            </a>
                <a href="?filter=lost" 
               class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'lost' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-exclamation-circle mr-2"></i> Lost
            </a>
            <a href="?filter=found" 
               class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'found' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-check-circle mr-2"></i> Found
            </a>
            <a href="?filter=resolved" 
               class="px-6 py-3 rounded-lg font-semibold transition <?php echo $filter === 'resolved' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-archive mr-2"></i> Resolved
            </a>
        </div>
        
        <?php if (isLoggedIn()): ?>
                <div class="text-center mt-4">
                <button onclick="openLostFoundModal()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                    <i class="fas fa-plus mr-2"></i> Report Item
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Items Grid -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($items)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
                <?php foreach ($items as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer group">
                        <a href="<?php echo baseUrl('pages/lost-found-detail.php?id=' . $item['lost_found_id']); ?>" class="block">
                        <!-- Image -->
                        <div class="relative h-56">
                            <img src="<?php echo baseUrl(htmlspecialchars($item['primary_image'] ?? 'assets/images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Type Badge -->
                            <div class="absolute top-2 left-2">
                                <?php if ($item['item_type'] === 'lost'): ?>
                                    <span class="bg-red-500 text-white px-3 py-1 rounded-lg text-sm font-semibold">
                                        Help me find this 😢
                                    </span>
                                <?php else: ?>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-lg text-sm font-semibold">
                                      I found it
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        </a>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <p class="text-sm text-gray-600 mb-2 truncate"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="text-xs text-gray-500 flex justify-between">
                                <span class="truncate"><?php echo htmlspecialchars($item['location_lost_found'] ?? ''); ?></span>
                                <span><?php echo date('M j', strtotime($item['posted_at'])); ?></span>
                            </div>
                        </div>
                        <!-- Footer Actions -->
                        <div class="p-3 border-t bg-white flex items-center justify-between">
                            <div>
                                <?php if (isLoggedIn() && $_SESSION['user_id'] == $item['user_id']): ?>
                                    <?php if ($item['status'] === 'active'): ?>
                                    <button onclick="event.stopPropagation(); markLostFoundResolved(<?php echo $item['lost_found_id']; ?>)" 
                                        class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm" title="Mark as Resolved">
                                        <i class="fas fa-check mr-1"></i> Resolve
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?php echo baseUrl('pages/edit-lost-found.php?id=' . $item['lost_found_id']); ?>" class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if (isLoggedIn()): ?>
                                    <a href="<?php echo baseUrl('pages/chat.php?seller_id=' . (int)$item['user_id']); ?>" class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-green-600 transition text-sm">
                                        <i class="fas fa-comment mr-1"></i> Message Poster
                                    </a>
                                    <button onclick="event.stopPropagation(); toggleBookmark({type:'lost_found', item_id: <?php echo (int)$item['lost_found_id']; ?>}, this)" class="px-3 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 transition text-sm">
                                        <i class="<?php echo !empty($item['is_bookmarked']) ? 'fas' : 'far'; ?> fa-bookmark text-primary"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">No Items Found</h3>
                <p class="text-gray-500 mb-6">Be the first to report a lost or found item!</p>
                <?php if (isLoggedIn()): ?>
                    <button onclick="openLostFoundModal()" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                        <i class="fas fa-plus mr-2"></i> Report Item
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

<script>
// Mark Lost/Found item as resolved
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
<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
