<?php
$page_title = 'User Management';
require_once 'header.php';
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle search and filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$campus_filter = $_GET['campus'] ?? '';
$per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($status_filter !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = (int)$status_filter;
    $types .= 'i';
}

if (!empty($campus_filter)) {
    $where_conditions[] = "campus_id = ?";
    $params[] = (int)$campus_filter;
    $types .= 'i';
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
if (!empty($params)) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_users = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_users / $per_page);

// Get users
$sql = "SELECT user_id, CONCAT(first_name, ' ', last_name) as full_name, email, phone_number, 
               (SELECT campus_name FROM campuses WHERE campus_id = users.campus_id) as campus,
               (SELECT department_name FROM departments WHERE department_id = users.department_id) as department,
               (SELECT level_name FROM levels WHERE level_id = users.level_id) as level,
               is_active, is_admin, created_at, last_login_at,
               (SELECT COUNT(*) FROM listings WHERE user_id = users.user_id) as total_listings,
               (SELECT COUNT(*) FROM listings WHERE user_id = users.user_id AND status = 'active') as active_listings
        FROM users 
        $where_sql
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();

// Get campuses for filter
$campuses = $conn->query("SELECT campus_id, campus_name FROM campuses ORDER BY campus_name");
?>

<!-- Filters and Search -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Name, email, or matric number..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All</option>
                    <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <!-- Campus Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                <select name="campus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Campuses</option>
                    <?php while ($campus = $campuses->fetch_assoc()): ?>
                        <option value="<?php echo $campus['campus_id']; ?>" 
                                <?php echo $campus_filter == $campus['campus_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($campus['campus_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="users.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">
                All Users <span class="text-sm text-gray-500 font-normal">(<?php echo number_format($total_users); ?> total)</span>
            </h3>
            <button onclick="exportUsers()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-download mr-2"></i> Export CSV
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campus</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Listings</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($users->num_rows > 0): ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50" id="user-row-<?php echo $user['user_id']; ?>">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded">Admin</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-700"><?php echo htmlspecialchars($user['campus']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['department']); ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-700"><?php echo $user['active_listings']; ?> active</p>
                                <p class="text-xs text-gray-500"><?php echo $user['total_listings']; ?> total</p>
                            </td>
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
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="viewUser(<?php echo $user['user_id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="toggleUserStatus(<?php echo $user['user_id']; ?>, <?php echo $user['is_active']; ?>)" 
                                            class="text-yellow-600 hover:text-yellow-800" 
                                            title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check-circle'; ?>"></i>
                                    </button>
                                    <button onclick="deleteUser(<?php echo $user['user_id']; ?>)" 
                                            class="text-red-600 hover:text-red-800" title="Delete User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-2 text-gray-300"></i>
                            <p>No users found</p>
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
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_users); ?> of <?php echo number_format($total_users); ?> users
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&campus=<?php echo urlencode($campus_filter); ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&campus=<?php echo urlencode($campus_filter); ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 <?php echo $i === $page ? 'bg-primary text-white border-primary' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&campus=<?php echo urlencode($campus_filter); ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- User Details Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-800">User Details</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="userModalContent" class="p-6">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<script>
function viewUser(userId) {
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('userModalContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-primary"></i></div>';
    
    fetch(`user-details.php?id=${userId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('userModalContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('userModalContent').innerHTML = '<p class="text-red-600">Error loading user details</p>';
        });
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}

function toggleUserStatus(userId, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    if (!confirm(`Are you sure you want to ${action} this user?`)) return;
    
    fetch('user-actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=toggle_status&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating user status');
        }
    })
    .catch(error => {
        alert('Error updating user status');
    });
}

function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    
    fetch('user-actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`user-row-${userId}`).remove();
        } else {
            alert(data.message || 'Error deleting user');
        }
    })
    .catch(error => {
        alert('Error deleting user');
    });
}

function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = 'export-users.php?' + params.toString();
}
</script>

<?php require_once 'footer.php'; ?>
