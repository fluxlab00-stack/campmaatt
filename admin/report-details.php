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
$sql = "SELECT r.*, 
               CONCAT(reporter.first_name, ' ', reporter.last_name) as reporter_name, 
               reporter.email as reporter_email,
               reporter.phone_number as reporter_phone,
               CONCAT(reported_user.first_name, ' ', reported_user.last_name) as reported_user_name,
               reported_user.email as reported_user_email,
               l.title as listing_title,
               l.listing_id as listing_id
        FROM reports r
        LEFT JOIN users reporter ON r.reporter_id = reporter.user_id
        LEFT JOIN users reported_user ON r.reported_user_id = reported_user.user_id
        LEFT JOIN listings l ON r.listing_id = l.listing_id
        WHERE r.report_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    echo '<p class="text-red-600">Report not found</p>';
    exit;
}
?>

<div class="space-y-6">
    <!-- Report Info -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-600">Report ID</p>
            <p class="font-semibold text-gray-900">#<?php echo $report['report_id']; ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Report Type</p>
            <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                <?php 
                $color = 'bg-gray-100 text-gray-800';
                if ($report['report_type'] === 'fraud') $color = 'bg-red-100 text-red-800';
                elseif ($report['report_type'] === 'offensive') $color = 'bg-orange-100 text-orange-800';
                elseif ($report['report_type'] === 'spam') $color = 'bg-yellow-100 text-yellow-800';
                echo $color;
                ?>">
                <?php echo htmlspecialchars(str_replace('_', ' ', $report['report_type'])); ?>
            </span>
        </div>
        <div>
            <p class="text-sm text-gray-600">Status</p>
            <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize
                <?php 
                echo $report['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                    ($report['status'] === 'reviewed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); 
                ?>">
                <?php echo htmlspecialchars(str_replace('_', ' ', $report['status'])); ?>
            </span>
        </div>
        <div>
            <p class="text-sm text-gray-600">Reported On</p>
            <p class="font-semibold text-gray-900"><?php echo date('M d, Y H:i', strtotime($report['reported_at'])); ?></p>
        </div>
    </div>

    <!-- Reporter Info -->
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Reporter Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Name</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['reporter_name']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Email</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['reporter_email']); ?></p>
            </div>
            <?php if ($report['reporter_phone']): ?>
            <div>
                <p class="text-sm text-gray-600">Phone</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['reporter_phone']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reported Subject -->
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Reported Subject</h4>
        <?php if ($report['listing_id']): ?>
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Listing</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['listing_title']); ?></p>
                <a href="../pages/listing-detail.php?id=<?php echo $report['listing_id']; ?>" 
                   target="_blank" 
                   class="text-sm text-primary hover:underline mt-2 inline-block">
                    View Listing <i class="fas fa-external-link-alt text-xs"></i>
                </a>
            </div>
        <?php elseif ($report['reported_user_id']): ?>
            <div class="bg-purple-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">User</p>
                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($report['reported_user_name']); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($report['reported_user_email']); ?></p>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-sm">No specific subject</p>
        <?php endif; ?>
    </div>

    <!-- Report Details -->
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Report Details</h4>
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($report['details'] ?? 'No details provided')); ?></p>
        </div>
    </div>

    <!-- Admin Notes -->
    <?php if ($report['admin_notes']): ?>
    <div class="border-t pt-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Admin Notes</h4>
        <div class="bg-yellow-50 p-4 rounded-lg">
            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($report['admin_notes'])); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="border-t pt-6">
        <div class="flex gap-3">
            <?php if ($report['status'] === 'pending'): ?>
                <button onclick="updateStatus(<?php echo $report['report_id']; ?>, 'reviewed'); closeReportModal();" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-check mr-2"></i> Mark as Reviewed
                </button>
                <button onclick="updateStatus(<?php echo $report['report_id']; ?>, 'action_taken'); closeReportModal();" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check-double mr-2"></i> Action Taken
                </button>
            <?php endif; ?>
            <button onclick="if(confirm('Delete this report?')) { deleteReport(<?php echo $report['report_id']; ?>); closeReportModal(); }" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </div>
    </div>
</div>
