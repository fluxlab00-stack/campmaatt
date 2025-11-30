<?php
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';
$report_id = (int)($_POST['report_id'] ?? 0);

if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
    exit;
}

switch ($action) {
    case 'update_status':
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['pending', 'reviewed', 'action_taken'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
        $stmt->bind_param('si', $status, $report_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Report status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        break;
        
    case 'delete':
        $stmt = $conn->prepare("DELETE FROM reports WHERE report_id = ?");
        $stmt->bind_param('i', $report_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Report deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete report']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
