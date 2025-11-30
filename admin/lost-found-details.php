<?php
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$report_id = (int)($_GET['id'] ?? 0);

if (!$report_id) {
    echo '<p class="text-red-600">Invalid report ID</p>';
    exit;
}

// Get report details
$sql = "SELECT lf.*, u.full_name, u.email, u.phone
        FROM lost_found lf
        LEFT JOIN users u ON lf.user_id = u.user_id
        WHERE lf.lost_found_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    echo '<p class="text-red-600">Report not found</p>';
    exit;
}

// Get all images
$stmt = $conn->prepare("SELECT image_path FROM lost_found_images WHERE lost_found_id = ?");
$stmt->bind_param('i', $report_id);
$stmt->execute();
$images = $stmt->get_result();
?>

<div class="space-y-6">
    <!-- Images -->
    <?php if ($images->num_rows > 0): ?>
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-3">Images</h4>
            <div class="grid grid-cols-3 gap-4">
                <?php while ($img = $images->fetch_assoc()): ?>
                    <img src="../assets/uploads/lost_found/<?php echo htmlspecialchars($img['image_path']); ?>" 
                         alt="Item Image" class="w-full h-40 object-cover rounded-lg">
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Basic Info -->
    <div>
        <h4 class="text-lg font-semibold text-gray-800 mb-3">Report Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Item Name</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['item_name']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Report Type</p>
                <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                    <?php echo $report['report_type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                    <?php echo htmlspecialchars($report['report_type']); ?>
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-600">Location</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['location']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Date <?php echo $report['report_type'] === 'lost' ? 'Lost' : 'Found'; ?></p>
                <p class="font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($report['date_lost_found'])); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                    <?php echo $report['status'] === 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                    <?php echo htmlspecialchars($report['status']); ?>
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-600">Reported</p>
                <p class="font-semibold text-gray-900"><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div>
        <h4 class="text-lg font-semibold text-gray-800 mb-3">Description</h4>
        <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($report['description']); ?></p>
    </div>

    <!-- Reporter Info -->
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-3">Reporter Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Name</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['full_name']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Email</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['email']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Phone</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['phone'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="border-t pt-6 flex gap-3">
        <?php if ($report['status'] === 'open'): ?>
            <button onclick="markResolved(<?php echo $report_id; ?>); closeReportModal();" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-check-circle mr-2"></i> Mark as Resolved
            </button>
        <?php endif; ?>
        
        <button onclick="if(confirm('Delete this report?')) { deleteReport(<?php echo $report_id; ?>); closeReportModal(); }" 
                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="fas fa-trash mr-2"></i> Delete
        </button>
    </div>
</div>
