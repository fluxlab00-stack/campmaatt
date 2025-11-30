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
    case 'resolve':
        $sql = "UPDATE lost_found SET status = 'resolved' WHERE lost_found_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $report_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Report marked as resolved']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating report']);
        }
        break;
    
    case 'delete':
        $conn->begin_transaction();
        
        try {
            // Get and delete images
            $stmt = $conn->prepare("SELECT image_path FROM lost_found_images WHERE lost_found_id = ?");
            $stmt->bind_param('i', $report_id);
            $stmt->execute();
            $images = $stmt->get_result();
            
            while ($img = $images->fetch_assoc()) {
                $img_path = '../assets/uploads/lost_found/' . $img['image_path'];
                if (file_exists($img_path)) {
                    unlink($img_path);
                }
            }
            
            // Delete image records
            $stmt = $conn->prepare("DELETE FROM lost_found_images WHERE lost_found_id = ?");
            $stmt->bind_param('i', $report_id);
            $stmt->execute();
            
            // Delete the report
            $stmt = $conn->prepare("DELETE FROM lost_found WHERE lost_found_id = ?");
            $stmt->bind_param('i', $report_id);
            $stmt->execute();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error deleting report: ' . $e->getMessage()]);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
