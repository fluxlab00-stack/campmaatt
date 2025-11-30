<?php
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';
$user_id = (int)($_POST['user_id'] ?? 0);

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

switch ($action) {
    case 'toggle_status':
        $sql = "UPDATE users SET is_active = NOT is_active WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user status']);
        }
        break;
    
    case 'delete':
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete user's listings images
            $stmt = $conn->prepare("SELECT image_path FROM listing_images WHERE listing_id IN (SELECT listing_id FROM listings WHERE user_id = ?)");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $images = $stmt->get_result();
            
            while ($img = $images->fetch_assoc()) {
                $img_path = '../assets/uploads/listings/' . $img['image_path'];
                if (file_exists($img_path)) {
                    unlink($img_path);
                }
            }
            
            // Delete listing images records
            $stmt = $conn->prepare("DELETE FROM listing_images WHERE listing_id IN (SELECT listing_id FROM listings WHERE user_id = ?)");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            // Delete bookmarks
            $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            // Delete messages
            $stmt = $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR recipient_id = ?");
            $stmt->bind_param('ii', $user_id, $user_id);
            $stmt->execute();
            
            // Delete listings
            $stmt = $conn->prepare("DELETE FROM listings WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            // Delete lost & found reports
            $stmt = $conn->prepare("DELETE FROM lost_found WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            // Delete user profile image
            $stmt = $conn->prepare("SELECT profile_image FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && $user['profile_image']) {
                $profile_path = '../assets/uploads/profiles/' . $user['profile_image'];
                if (file_exists($profile_path)) {
                    unlink($profile_path);
                }
            }
            
            // Finally, delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()]);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
