<?php
$page_title = 'Analytics';
require_once 'header.php';
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get date range filter
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// User Statistics
$user_stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
$user_stats['new_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
$user_stats['active_users'] = $result->fetch_assoc()['count'];

// Listing Statistics
$listing_stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM listings WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
$listing_stats['new_listings'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM listings WHERE status = 'sold' AND updated_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
$listing_stats['sold_listings'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COALESCE(SUM(price), 0) as revenue FROM listings WHERE status = 'sold' AND updated_at BETWEEN '$start_date' AND '$end_date 23:59:59'");
$listing_stats['revenue'] = $result->fetch_assoc()['revenue'];

// Category Performance
$category_performance = $conn->query("
    SELECT c.category_name, 
           COUNT(l.listing_id) as listing_count,
           COALESCE(SUM(CASE WHEN l.status = 'sold' THEN l.price ELSE 0 END), 0) as revenue,
           COALESCE(SUM(l.views_count), 0) as total_views
    FROM categories c
    LEFT JOIN listings l ON c.category_id = l.category_id 
        AND l.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    GROUP BY c.category_id, c.category_name
    ORDER BY listing_count DESC
    LIMIT 10
");

// Campus Activity
$campus_activity = $conn->query("
    SELECT c.campus_name as campus, COUNT(u.user_id) as user_count,
           (SELECT COUNT(*) FROM listings WHERE user_id IN (SELECT user_id FROM users u2 WHERE u2.campus_id = c.campus_id)) as listing_count
    FROM campuses c
    LEFT JOIN users u ON c.campus_id = u.campus_id 
        AND u.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    GROUP BY c.campus_id, c.campus_name
    ORDER BY user_count DESC
    LIMIT 10
");

// Daily Activity (last 30 days)
$daily_activity = $conn->query("
    SELECT DATE(created_at) as date,
           (SELECT COUNT(*) FROM users WHERE DATE(created_at) = DATE(l.created_at)) as users,
           COUNT(*) as listings
    FROM listings l
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

$activity_data = [];
while ($row = $daily_activity->fetch_assoc()) {
    $activity_data[] = $row;
}

// Top Sellers
$top_sellers = $conn->query("
    SELECT CONCAT(u.first_name, ' ', u.last_name) as full_name, u.email, 
           COUNT(l.listing_id) as total_listings,
           SUM(CASE WHEN l.status = 'sold' THEN 1 ELSE 0 END) as sold_listings,
           COALESCE(SUM(CASE WHEN l.status = 'sold' THEN l.price ELSE 0 END), 0) as revenue
    FROM users u
    LEFT JOIN listings l ON u.user_id = l.user_id 
        AND l.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    GROUP BY u.user_id
    HAVING total_listings > 0
    ORDER BY revenue DESC
    LIMIT 10
");

// Most Viewed Listings
$most_viewed = $conn->query("
    SELECT l.title, l.price, l.views_count, CONCAT(u.first_name, ' ', u.last_name) as full_name, l.status
    FROM listings l
    LEFT JOIN users u ON l.user_id = u.user_id
    WHERE l.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    ORDER BY l.views_count DESC
    LIMIT 10
");
?>

<!-- Date Range Filter -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                <i class="fas fa-filter mr-2"></i> Apply Filter
            </button>
            <a href="analytics.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Reset
            </a>
        </form>
    </div>
</div>

<!-- Overview Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">New Users</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($user_stats['new_users']); ?></h3>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-plus text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Active Users</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($user_stats['active_users']); ?></h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-check text-2xl text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">New Listings</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($listing_stats['new_listings']); ?></h3>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Period Revenue</p>
                <h3 class="text-3xl font-bold text-gray-800">₦<?php echo number_format($listing_stats['revenue'], 2); ?></h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Activity Chart -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Activity (Last 30 Days)</h3>
        <div class="h-64">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <!-- Category Performance -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Performance</h3>
        <div class="space-y-3">
            <?php while ($cat = $category_performance->fetch_assoc()): ?>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                        <span class="text-sm font-semibold text-gray-900">
                            <?php echo $cat['listing_count']; ?> listings | ₦<?php echo number_format($cat['revenue'], 0); ?>
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 mb-1"><?php echo number_format($cat['total_views']); ?> views</div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Top Performers -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Sellers -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Top Sellers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Listings</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($seller = $top_sellers->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($seller['full_name']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($seller['email']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo $seller['sold_listings']; ?>/<?php echo $seller['total_listings']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                ₦<?php echo number_format($seller['revenue'], 2); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Most Viewed Listings -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Most Viewed Listings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Listing</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($listing = $most_viewed->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($listing['title']); ?></p>
                                <p class="text-xs text-gray-500">₦<?php echo number_format($listing['price'], 2); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                <?php echo number_format($listing['views_count']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                                    <?php 
                                    echo $listing['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                        ($listing['status'] === 'sold' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                                    ?>">
                                    <?php echo $listing['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Campus Activity -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Campus Activity</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campus</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Users</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Listings</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($campus = $campus_activity->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($campus['campus']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <?php echo number_format($campus['user_count']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <?php echo number_format($campus['listing_count']); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Activity Chart
    const activityData = <?php echo json_encode($activity_data); ?>;
    const dates = activityData.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    const users = activityData.map(d => parseInt(d.users));
    const listings = activityData.map(d => parseInt(d.listings));

    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'New Users',
                    data: users,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'New Listings',
                    data: listings,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.08)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>

<?php require_once 'footer.php'; ?>
