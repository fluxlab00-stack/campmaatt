<?php
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';
$listing_id = (int)($_POST['listing_id'] ?? 0);

if (!$listing_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid listing ID']);
    exit;
}

switch ($action) {
    case 'mark_sold':
        $sql = "UPDATE listings SET status = 'sold' WHERE listing_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $listing_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Listing marked as sold']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating listing']);
        }
        break;
    
    case 'delete':
        $conn->begin_transaction();
        
        try {
            // Get and delete listing images
            $stmt = $conn->prepare("SELECT image_path FROM listing_images WHERE listing_id = ?");
            $stmt->bind_param('i', $listing_id);
            $stmt->execute();
            $images = $stmt->get_result();
            
            while ($img = $images->fetch_assoc()) {
                $img_path = '../assets/uploads/listings/' . $img['image_path'];
                if (file_exists($img_path)) {
                    unlink($img_path);
                }
            }
            
            // Delete image records
            $stmt = $conn->prepare("DELETE FROM listing_images WHERE listing_id = ?");
            $stmt->bind_param('i', $listing_id);
            $stmt->execute();
            
            // Delete bookmarks
            $stmt = $conn->prepare("DELETE FROM bookmarks WHERE listing_id = ?");
            $stmt->bind_param('i', $listing_id);
            $stmt->execute();
            
            // Delete the listing
            $stmt = $conn->prepare("DELETE FROM listings WHERE listing_id = ?");
            $stmt->bind_param('i', $listing_id);
            $stmt->execute();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error deleting listing: ' . $e->getMessage()]);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
