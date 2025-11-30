<?php
$page_title = 'Listing Management';
require_once 'header.php';
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(l.title LIKE ? OR l.description LIKE ? OR u.full_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "l.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($category_filter)) {
    $where_conditions[] = "l.category_id = ?";
    $params[] = (int)$category_filter;
    $types .= 'i';
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM listings l 
              LEFT JOIN users u ON l.user_id = u.user_id 
              $where_sql";
if (!empty($params)) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_listings = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_listings = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_listings / $per_page);

// Get listings
$sql = "SELECT l.*, CONCAT(u.first_name, ' ', u.last_name) as seller_name, u.email as seller_email, u.phone_number as seller_phone,
               c.category_name,
               (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id LIMIT 1) as main_image,
               (SELECT COUNT(*) FROM bookmarks WHERE listing_id = l.listing_id) as bookmark_count
        FROM listings l
        LEFT JOIN users u ON l.user_id = u.user_id
        LEFT JOIN categories c ON l.category_id = c.category_id
        $where_sql
        ORDER BY l.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$listings = $stmt->get_result();

// Get categories for filter
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
?>

<!-- Filters and Search -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Listings</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Title, description, or seller..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="sold" <?php echo $status_filter === 'sold' ? 'selected' : ''; ?>>Sold</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>" 
                                <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="listings.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Listings Grid -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">
                All Listings <span class="text-sm text-gray-500 font-normal">(<?php echo number_format($total_listings); ?> total)</span>
            </h3>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Listing</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($listings->num_rows > 0): ?>
                    <?php while ($listing = $listings->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50" id="listing-row-<?php echo $listing['listing_id']; ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($listing['main_image']): ?>
                                        <img src="../<?php echo htmlspecialchars($listing['main_image']); ?>" 
                                             alt="Listing Image" class="w-16 h-16 object-cover rounded-lg">
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($listing['title']); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-bookmark mr-1"></i><?php echo $listing['bookmark_count']; ?> bookmarks
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($listing['seller_name']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($listing['seller_email']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                ₦<?php echo number_format($listing['price'], 2); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($listing['category_name']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                $status_colors = [
                                    'active' => 'green',
                                    'sold' => 'blue',
                                    'inactive' => 'gray'
                                ];
                                $color = $status_colors[$listing['status']] ?? 'gray';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold text-<?php echo $color; ?>-800 bg-<?php echo $color; ?>-100 rounded-full capitalize">
                                    <?php echo htmlspecialchars($listing['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo number_format($listing['views_count']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($listing['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="viewListing(<?php echo $listing['listing_id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($listing['status'] === 'active'): ?>
                                        <button onclick="markSold(<?php echo $listing['listing_id']; ?>)" 
                                                class="text-green-600 hover:text-green-800" title="Mark as Sold">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteListing(<?php echo $listing['listing_id']; ?>)" 
                                            class="text-red-600 hover:text-red-800" title="Delete Listing">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-box text-4xl mb-2 text-gray-300"></i>
                            <p>No listings found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="p-6 border-t">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_listings); ?> of <?php echo number_format($total_listings); ?> listings
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 <?php echo $i === $page ? 'bg-primary text-white border-primary' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Listing Details Modal -->
<div id="listingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 class="text-xl font-semibold text-gray-800">Listing Details</h3>
            <button onclick="closeListingModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="listingModalContent" class="p-6">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<script>
function viewListing(listingId) {
    document.getElementById('listingModal').classList.remove('hidden');
    document.getElementById('listingModalContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-primary"></i></div>';
    
    fetch(`listing-details.php?id=${listingId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('listingModalContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('listingModalContent').innerHTML = '<p class="text-red-600">Error loading listing details</p>';
        });
}

function closeListingModal() {
    document.getElementById('listingModal').classList.add('hidden');
}

function markSold(listingId) {
    if (!confirm('Mark this listing as sold?')) return;
    
    fetch('listing-actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=mark_sold&listing_id=${listingId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating listing');
        }
    });
}

function deleteListing(listingId) {
    if (!confirm('Are you sure you want to delete this listing? This action cannot be undone.')) return;
    
    fetch('listing-actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&listing_id=${listingId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`listing-row-${listingId}`).remove();
        } else {
            alert(data.message || 'Error deleting listing');
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>
