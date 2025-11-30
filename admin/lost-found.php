<?php
$page_title = 'Found It Management';
require_once 'header.php';
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle filters
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(lf.item_name LIKE ? OR lf.description LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($type_filter)) {
    $where_conditions[] = "lf.item_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

if (!empty($status_filter)) {
    $where_conditions[] = "lf.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM lost_found lf 
              LEFT JOIN users u ON lf.user_id = u.user_id 
              $where_sql";
if (!empty($params)) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_reports = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_reports = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_reports / $per_page);

// Get reports
$sql = "SELECT lf.*, CONCAT(u.first_name, ' ', u.last_name) as reporter_name, u.email as reporter_email, u.phone_number as reporter_phone,
               (SELECT image_url FROM lost_found_images WHERE lost_found_id = lf.lost_found_id LIMIT 1) as main_image
        FROM lost_found lf
        LEFT JOIN users u ON lf.user_id = u.user_id
        $where_sql
        ORDER BY lf.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reports = $stmt->get_result();
?>

<!-- Filters -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Item name, description, or reporter..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All</option>
                    <option value="lost" <?php echo $type_filter === 'lost' ? 'selected' : ''; ?>>Lost</option>
                    <option value="found" <?php echo $type_filter === 'found' ? 'selected' : ''; ?>>Found</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="lost-found.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Reports Table -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-800">
            All Reports <span class="text-sm text-gray-500 font-normal">(<?php echo number_format($total_reports); ?> total)</span>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reporter</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($reports->num_rows > 0): ?>
                    <?php while ($report = $reports->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50" id="report-row-<?php echo $report['lost_found_id']; ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($report['main_image']): ?>
                                        <img src="../assets/uploads/lost_found/<?php echo htmlspecialchars($report['main_image']); ?>" 
                                             alt="Item Image" class="w-16 h-16 object-cover rounded-lg">
                                    <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($report['item_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars(substr($report['description'], 0, 50)) . '...'; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($report['reporter_name']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($report['reporter_email']); ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                                    <?php echo $report['item_type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo htmlspecialchars($report['item_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($report['location_lost_found']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                                    <?php echo $report['status'] === 'active' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo htmlspecialchars($report['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="viewReport(<?php echo $report['lost_found_id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($report['status'] === 'active'): ?>
                                        <button onclick="markResolved(<?php echo $report['lost_found_id']; ?>)" 
                                                class="text-green-600 hover:text-green-800" title="Mark as Resolved">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteReport(<?php echo $report['lost_found_id']; ?>)" 
                                            class="text-red-600 hover:text-red-800" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-search text-4xl mb-2 text-gray-300"></i>
                            <p>No reports found</p>
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
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_reports); ?> of <?php echo number_format($total_reports); ?> reports
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type_filter; ?>&status=<?php echo $status_filter; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type_filter; ?>&status=<?php echo $status_filter; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 <?php echo $i === $page ? 'bg-primary text-white border-primary' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type_filter; ?>&status=<?php echo $status_filter; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Report Details Modal -->
<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 class="text-xl font-semibold text-gray-800">Report Details</h3>
            <button onclick="closeReportModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="reportModalContent" class="p-6">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
            </div>
        </div>
    </div>
</div>

<script>
function viewReport(reportId) {
    document.getElementById('reportModal').classList.remove('hidden');
    document.getElementById('reportModalContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-primary"></i></div>';
    
    fetch(`lost-found-details.php?id=${reportId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('reportModalContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('reportModalContent').innerHTML = '<p class="text-red-600">Error loading report details</p>';
        });
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
}

function markResolved(reportId) {
    if (!confirm('Mark this report as resolved?')) return;
    
    fetch('lost-found-actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=resolve&report_id=${reportId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating report');
        }
    });
}

function deleteReport(reportId) {
    if (!confirm('Are you sure you want to delete this report?')) return;
    
    fetch('lost-found-actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&report_id=${reportId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`report-row-${reportId}`).remove();
        } else {
            alert(data.message || 'Error deleting report');
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>
