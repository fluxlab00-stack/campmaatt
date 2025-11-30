<?php
/**
 * Messages Page
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../index.php");
    exit();
}

$pageTitle = "Messages - CampMart";

// Get current user
$user = getCurrentUser();
$userId = getCurrentUserId();

// Get database instance
$db = Database::getInstance();

// Fetch all chats for current user
$stmt = $db->prepare(
    "SELECT c.*, 
            l.title as listing_title, 
            l.price,
            l.status as listing_status,
            (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as listing_image,
            CONCAT(buyer.first_name, ' ', buyer.last_name) as buyer_name,
            buyer.profile_picture_url as buyer_pic,
            CONCAT(seller.first_name, ' ', seller.last_name) as seller_name,
            seller.profile_picture_url as seller_pic,
            (SELECT message_text FROM messages WHERE chat_id = c.chat_id ORDER BY sent_at DESC LIMIT 1) as last_message,
            (SELECT COUNT(*) FROM messages WHERE chat_id = c.chat_id AND sender_id != ? AND is_read = 0) as unread_count
     FROM chats c
     LEFT JOIN listings l ON c.listing_id = l.listing_id
     LEFT JOIN users buyer ON c.buyer_id = buyer.user_id
     LEFT JOIN users seller ON c.seller_id = seller.user_id
     WHERE c.buyer_id = ? OR c.seller_id = ?
     ORDER BY c.last_message_at DESC",
    "iii",
    [$userId, $userId, $userId]
);

$chats = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $chats[] = $row;
    }
    $stmt->close();
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-2">
            <i class="fas fa-envelope mr-2"></i> Messages
        </h1>
        <p class="text-xl text-gray-100">
            Chat with buyers and sellers
        </p>
    </div>
</section>

<!-- Messages Content -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if (empty($chats)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-comments text-gray-300 text-6xl mb-6"></i>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">No Messages Yet</h2>
                <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                    Start a conversation by contacting sellers from their listings, or wait for buyers to message you about your items.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 text-left">
                    <div class="bg-green-50 rounded-lg p-6">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-comment text-2xl text-white"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Direct Chat</h3>
                        </div>
                        <p class="text-sm text-gray-600">
                            Message sellers directly about items you're interested in
                        </p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-6">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-bell text-2xl text-white"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Real-time</h3>
                        </div>
                        <p class="text-sm text-gray-600">
                            Get instant notifications when buyers contact you
                        </p>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-6">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-shield-alt text-2xl text-white"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Secure</h3>
                        </div>
                        <p class="text-sm text-gray-600">
                            Safe and secure in-platform messaging
                        </p>
                    </div>
                </div>
                
                <a href="marketplace.php" class="inline-block mt-8 px-6 py-3 bg-primary text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                    <i class="fas fa-shopping-bag mr-2"></i> Browse Marketplace
                </a>
            </div>
        <?php else: ?>
            <!-- Chat List -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chat Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-primary text-white p-4">
                            <h2 class="text-xl font-bold">
                                <i class="fas fa-inbox mr-2"></i> Conversations
                            </h2>
                            <p class="text-sm text-gray-100 mt-1"><?php echo count($chats); ?> active chats</p>
                        </div>
                        
                        <div class="divide-y divide-gray-200 max-h-[600px] overflow-y-auto">
                            <?php foreach ($chats as $chat): ?>
                                <?php
                                $isBuyer = ($chat['buyer_id'] == $userId);
                                $otherUserName = $isBuyer ? $chat['seller_name'] : $chat['buyer_name'];
                                $otherUserPic = $isBuyer ? $chat['seller_pic'] : $chat['buyer_pic'];
                                $role = $isBuyer ? 'Buyer' : 'Seller';
                                ?>
                                <a href="chat.php?chat_id=<?php echo $chat['chat_id']; ?>" 
                                   class="block p-4 hover:bg-gray-50 transition <?php echo $chat['unread_count'] > 0 ? 'bg-blue-50' : ''; ?>">
                                    <div class="flex items-start gap-3">
                                        <img src="../<?php echo htmlspecialchars($otherUserPic); ?>" 
                                             alt="<?php echo htmlspecialchars($otherUserName); ?>" 
                                             class="w-12 h-12 rounded-full object-cover border-2 border-primary flex-shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h3 class="font-semibold text-gray-900 truncate">
                                                    <?php echo htmlspecialchars($otherUserName); ?>
                                                </h3>
                                                <?php if ($chat['unread_count'] > 0): ?>
                                                    <span class="bg-primary text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                                        <?php echo $chat['unread_count']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs text-gray-500 mb-1">
                                                <span class="px-2 py-0.5 bg-gray-200 rounded"><?php echo $role; ?></span>
                                            </p>
                                            <p class="text-sm text-gray-900 font-medium truncate mb-1">
                                                <?php echo htmlspecialchars($chat['listing_title']); ?>
                                            </p>
                                            <?php if ($chat['last_message']): ?>
                                                <p class="text-xs text-gray-600 truncate">
                                                    <?php echo htmlspecialchars(substr($chat['last_message'], 0, 40)) . (strlen($chat['last_message']) > 40 ? '...' : ''); ?>
                                                </p>
                                            <?php endif; ?>
                                            <p class="text-xs text-gray-400 mt-1">
                                                <?php echo timeAgo($chat['last_message_at']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Chat Preview/Instructions -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-8 text-center h-full flex flex-col items-center justify-center">
                        <i class="fas fa-comment-dots text-gray-300 text-6xl mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Select a Conversation</h2>
                        <p class="text-gray-600 mb-6">
                            Choose a chat from the list to view and respond to messages
                        </p>
                        
                        <div class="bg-blue-50 rounded-lg p-6 max-w-md text-left">
                            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                                Messaging Tips
                            </h3>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                    <span>Respond promptly to buyer inquiries</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                    <span>Be clear about item condition and price</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                    <span>Arrange safe meetup locations on campus</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                    <span>Be professional and courteous</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- Recent Activity -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Activity</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="my-listings.php" class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition">
                <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-list text-2xl text-primary"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">My Listings</h3>
                        <p class="text-sm text-gray-600">View messages from interested buyers</p>
                    </div>
                </div>
            </a>
            
            <a href="saved-items.php" class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition">
                <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-accent bg-opacity-10 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-bookmark text-2xl text-accent"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Saved Items</h3>
                        <p class="text-sm text-gray-600">Contact sellers of items you're interested in</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
