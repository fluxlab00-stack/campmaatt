<?php
$page_title = 'Dashboard';
require_once 'header.php';
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get statistics
$stats = [];

// Total Users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Active Users (logged in within last 30 days)
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats['active_users'] = $result->fetch_assoc()['count'];

// Total Listings
$result = $conn->query("SELECT COUNT(*) as count FROM listings");
$stats['total_listings'] = $result->fetch_assoc()['count'];

// Active Listings
$result = $conn->query("SELECT COUNT(*) as count FROM listings WHERE status = 'active'");
$stats['active_listings'] = $result->fetch_assoc()['count'];

// Sold Listings
$result = $conn->query("SELECT COUNT(*) as count FROM listings WHERE status = 'sold'");
$stats['sold_listings'] = $result->fetch_assoc()['count'];

// Total Found It Reports
$result = $conn->query("SELECT COUNT(*) as count FROM lost_found");
$stats['lost_found_reports'] = $result->fetch_assoc()['count'];

// Total Revenue (sum of sold listings)
$result = $conn->query("SELECT COALESCE(SUM(price), 0) as revenue FROM listings WHERE status = 'sold'");
$stats['total_revenue'] = $result->fetch_assoc()['revenue'];

// Today's new users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
$stats['today_users'] = $result->fetch_assoc()['count'];

// Today's new listings
$result = $conn->query("SELECT COUNT(*) as count FROM listings WHERE DATE(created_at) = CURDATE()");
$stats['today_listings'] = $result->fetch_assoc()['count'];

// Recent users
$recent_users = $conn->query("
    SELECT user_id, CONCAT(first_name, ' ', last_name) as full_name, email, 
           (SELECT campus_name FROM campuses WHERE campus_id = users.campus_id) as campus, 
           created_at, is_active 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Recent listings
$recent_listings = $conn->query("
    SELECT l.listing_id, l.title, l.price, l.status, l.created_at, 
           CONCAT(u.first_name, ' ', u.last_name) as seller_name, c.category_name
    FROM listings l
    LEFT JOIN users u ON l.user_id = u.user_id
    LEFT JOIN categories c ON l.category_id = c.category_id
    ORDER BY l.created_at DESC
    LIMIT 5
");

// Popular categories
$popular_categories = $conn->query("
    SELECT c.category_name, COUNT(l.listing_id) as listing_count
    FROM categories c
    LEFT JOIN listings l ON c.category_id = l.category_id
    GROUP BY c.category_id, c.category_name
    ORDER BY listing_count DESC
    LIMIT 5
");

// Monthly revenue data for chart (last 6 months)
$monthly_revenue = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           COALESCE(SUM(price), 0) as revenue
    FROM listings
    WHERE status = 'sold' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$revenue_data = [];
while ($row = $monthly_revenue->fetch_assoc()) {
    $revenue_data[] = $row;
}
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Users</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></h3>
                <p class="text-xs text-green-600 mt-2">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['today_users']; ?> today
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Listings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Listings</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_listings']); ?></h3>
                <p class="text-xs text-green-600 mt-2">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['today_listings']; ?> today
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                <h3 class="text-3xl font-bold text-gray-800">₦<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p class="text-xs text-gray-500 mt-2">
                    <?php echo $stats['sold_listings']; ?> items sold
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Found It -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Found It</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['lost_found_reports']); ?></h3>
                <p class="text-xs text-gray-500 mt-2">
                    Total reports
                </p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-search text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Active Listings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-xl text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Active Listings</p>
                <h4 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['active_listings']); ?></h4>
            </div>
        </div>
    </div>

    <!-- Active Users (30 days) -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-check text-xl text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Active Users</p>
                <h4 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['active_users']); ?></h4>
            </div>
        </div>
    </div>

    <!-- Sold Listings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-hand-holding-usd text-xl text-purple-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Sold Listings</p>
                <h4 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['sold_listings']); ?></h4>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Tables Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Revenue Chart -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Trend (Last 6 Months)</h3>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Popular Categories -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Popular Categories</h3>
        <div class="space-y-4">
            <?php while ($cat = $popular_categories->fetch_assoc()): ?>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                        <span class="text-sm font-semibold text-gray-900"><?php echo $cat['listing_count']; ?> listings</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <?php 
                        $percentage = $stats['total_listings'] > 0 ? ($cat['listing_count'] / $stats['total_listings']) * 100 : 0;
                        ?>
                        <div class="bg-primary h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Users -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Recent Users</h3>
                <a href="users.php" class="text-sm text-primary hover:underline">View All</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($user = $recent_users->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($user['campus']); ?></td>
                            <td class="px-6 py-4">
                                <?php if ($user['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Listings -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Recent Listings</h3>
                <a href="listings.php" class="text-sm text-primary hover:underline">View All</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($listing = $recent_listings->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($listing['title']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($listing['seller_name']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                ₦<?php echo number_format($listing['price'], 2); ?>
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
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($listing['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueData = <?php echo json_encode($revenue_data); ?>;
    const months = revenueData.map(d => {
        const [year, month] = d.month.split('-');
        return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    const revenues = revenueData.map(d => parseFloat(d.revenue));

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
                datasets: [{
                label: 'Revenue (₦)',
                data: revenues,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.08)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>

<?php require_once 'footer.php'; ?>
