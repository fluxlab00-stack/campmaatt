<?php
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$user_id = (int)($_GET['id'] ?? 0);

if (!$user_id) {
    echo '<p class="text-red-600">Invalid user ID</p>';
    exit;
}

// Get user details
$sql = "SELECT u.*, 
        CONCAT(u.first_name, ' ', u.last_name) as full_name,
        (SELECT campus_name FROM campuses WHERE campus_id = u.campus_id) as campus,
        (SELECT department_name FROM departments WHERE department_id = u.department_id) as department,
        (SELECT level_name FROM levels WHERE level_id = u.level_id) as level
        FROM users u WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo '<p class="text-red-600">User not found</p>';
    exit;
}

// Get user statistics
$stats = [];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM listings WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['total_listings'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM listings WHERE user_id = ? AND status = 'active'");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['active_listings'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM listings WHERE user_id = ? AND status = 'sold'");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['sold_listings'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['bookmarks'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(DISTINCT m.message_id) as count FROM messages m 
    JOIN chats c ON m.chat_id = c.chat_id 
    WHERE c.buyer_id = ? OR c.seller_id = ?");
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$stats['messages'] = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COALESCE(SUM(price), 0) as revenue FROM listings WHERE user_id = ? AND status = 'sold'");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stats['revenue'] = $stmt->get_result()->fetch_assoc()['revenue'];
?>

<div class="space-y-6">
    <!-- User Info -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-600">Full Name</p>
            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Email</p>
            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Phone</p>
            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Campus</p>
            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['campus']); ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Department</p>
            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['department']); ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Level</p>
            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['level']); ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Status</p>
            <?php if ($user['is_active']): ?>
                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>
            <?php else: ?>
                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Inactive</span>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-sm text-gray-600">Account Type</p>
            <?php if ($user['is_admin']): ?>
                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Admin</span>
            <?php else: ?>
                <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">User</span>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-sm text-gray-600">Joined</p>
            <p class="font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Last Login</p>
            <p class="font-semibold text-gray-900">
                <?php echo $user['last_login_at'] ? date('M d, Y H:i', strtotime($user['last_login_at'])) : 'Never'; ?>
            </p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Statistics</h4>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Total Listings</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_listings']; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Active Listings</p>
                <p class="text-2xl font-bold text-green-700"><?php echo $stats['active_listings']; ?></p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Sold Listings</p>
                <p class="text-2xl font-bold text-blue-700"><?php echo $stats['sold_listings']; ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Total Revenue</p>
                <p class="text-2xl font-bold text-purple-700">₦<?php echo number_format($stats['revenue'], 2); ?></p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Bookmarks</p>
                <p class="text-2xl font-bold text-yellow-700"><?php echo $stats['bookmarks']; ?></p>
            </div>
            <div class="bg-pink-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Messages</p>
                <p class="text-2xl font-bold text-pink-700"><?php echo $stats['messages']; ?></p>
            </div>
        </div>
    </div>

    <!-- Recent Listings -->
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Recent Listings</h4>
        <?php
        $stmt = $conn->prepare("
            SELECT listing_id, title, price, status, created_at 
            FROM listings 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $listings = $stmt->get_result();
        ?>
        
        <?php if ($listings->num_rows > 0): ?>
            <div class="space-y-2">
                <?php while ($listing = $listings->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($listing['title']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($listing['created_at'])); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">₦<?php echo number_format($listing['price'], 2); ?></p>
                            <span class="text-xs px-2 py-1 rounded-full capitalize
                                <?php 
                                echo $listing['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                    ($listing['status'] === 'sold' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                                ?>">
                                <?php echo $listing['status']; ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-sm">No listings yet</p>
        <?php endif; ?>
    </div>
</div>
