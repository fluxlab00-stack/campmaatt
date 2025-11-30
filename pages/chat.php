<?php
/**
 * Chat/Messaging Page
 * Direct messaging between buyer and seller
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: " . baseUrl('index.php'));
    exit();
}

$db = Database::getInstance();
$currentUserId = getCurrentUserId();

$listingId = intval($_GET['listing_id'] ?? 0);
$sellerId = intval($_GET['seller_id'] ?? 0);
$chatId = intval($_GET['chat_id'] ?? 0);

// If seller not provided but listing is, fetch seller from listing
if (empty($sellerId) && $listingId > 0) {
    $stmt = $db->prepare("SELECT user_id FROM listings WHERE listing_id = ?", "i", [$listingId]);
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $r = $res->fetch_assoc();
        $sellerId = $r['user_id'] ?? 0;
        $stmt->close();
    }
}

// Prevent users from messaging themselves
if ($sellerId > 0 && $sellerId === $currentUserId) {
    setFlashMessage('error', 'You cannot message yourself.');
    header('Location: ' . baseUrl('pages/marketplace.php'));
    exit();
}

// If chat_id not provided, try to find or create a chat between current user and other user
if ($chatId <= 0 && $sellerId > 0 && $currentUserId !== $sellerId) {
    // search for existing chat (with or without listing)
    $stmt = $db->prepare(
        "SELECT chat_id FROM chats WHERE ((buyer_id = ? AND seller_id = ?) OR (buyer_id = ? AND seller_id = ?)) LIMIT 1",
        "iiii",
        [$currentUserId, $sellerId, $sellerId, $currentUserId]
    );

    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $chatId = (int)$row['chat_id'];
        }
        $stmt->close();
    }

    // create new chat if none exists
    if ($chatId <= 0) {
        $stmt = $db->prepare(
            "INSERT INTO chats (listing_id, buyer_id, seller_id, created_at) VALUES (?, ?, ?, NOW())", 
            "iii", 
            [$listingId > 0 ? $listingId : null, $currentUserId, $sellerId]
        );
        if ($stmt) {
            $stmt->execute();
            $chatId = $db->insert_id;
            $stmt->close();
        }
    }
}

// Validate chat belongs to user
if ($chatId > 0) {
    $stmt = $db->prepare(
        "SELECT c.*, l.title AS listing_title, l.price AS listing_price, l.status AS listing_status, 
                u.user_id AS other_user_id, u.first_name AS other_first, u.last_name AS other_last, u.profile_picture_url AS other_pic
         FROM chats c
         LEFT JOIN listings l ON c.listing_id = l.listing_id
         LEFT JOIN users u ON (CASE WHEN c.buyer_id = ? THEN c.seller_id WHEN c.seller_id = ? THEN c.buyer_id ELSE NULL END) = u.user_id
         WHERE c.chat_id = ? AND (c.buyer_id = ? OR c.seller_id = ?)",
        "iiiii",
        [$currentUserId, $currentUserId, $chatId, $currentUserId, $currentUserId]
    );

    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $chat = $res->fetch_assoc();
        $stmt->close();
    }
}

if (empty($chat)) {
    setFlashMessage('error', 'Chat not found or you do not have access.');
    header('Location: ' . baseUrl('pages/marketplace.php'));
    exit();
}

$otherUserId = $chat['other_user_id'] ?? 0;
$otherUserName = trim(($chat['other_first'] ?? '') . ' ' . ($chat['other_last'] ?? '')) ?: 'User';
$otherUserPic = $chat['other_pic'] ?? 'assets/images/default-avatar.jpg';

$pageTitle = 'Chat about: ' . ($chat['listing_title'] ?? 'Listing');

include __DIR__ . '/../includes/header.php';
?>

<section class="py-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Chat / Listing Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-4 sticky top-6">
                    <div class="flex items-center gap-4">
                        <button onclick="window.history.back()" class="text-gray-600 hover:text-primary">
                            <i class="fas fa-arrow-left text-lg"></i>
                        </button>
                        <img src="../<?php echo htmlspecialchars($otherUserPic); ?>" alt="<?php echo htmlspecialchars($otherUserName); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-primary">
                        <div>
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($otherUserName); ?></h3>
                            <p class="text-xs text-gray-500">Chat about <a href="../pages/listing-detail.php?id=<?php echo (int)$chat['listing_id']; ?>" class="text-primary hover:underline"><?php echo htmlspecialchars($chat['listing_title'] ?? ''); ?></a></p>
                        </div>
                    </div>

                    <div class="mt-4 border-t pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Price</p>
                                <p class="font-bold text-primary"><?php echo formatPrice($chat['listing_price'] ?? 0); ?></p>
                            </div>
                            <div class="text-right">
                                <?php if (($chat['listing_status'] ?? '') === 'sold'): ?>
                                    <span class="text-sm text-red-600 font-semibold">SOLD</span>
                                <?php else: ?>
                                    <span class="text-sm text-green-600 font-semibold">AVAILABLE</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="../pages/profile.php?id=<?php echo (int)$otherUserId; ?>" class="block w-full text-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-green-700 transition font-semibold">View Profile</a>
                    </div>
                </div>
            </div>

            <!-- Center: Messages -->
            <div class="lg:col-span-2">
                <div class="flex flex-col h-[72vh] bg-transparent">
                    <div class="flex-1 overflow-hidden flex flex-col">
                        <div id="messagesContainer" class="bg-white rounded-lg shadow-lg p-6 mb-4 flex-1 overflow-y-auto" aria-live="polite">
                            <div id="messagesList" class="space-y-4">
                                <p class="text-center text-gray-500">Loading messages…</p>
                            </div>
                        </div>

                        <!-- Composer -->
                        <div class="bg-white rounded-lg shadow p-4">
                            <form id="messageForm" class="flex items-end gap-3">
                                <input type="hidden" name="chat_id" value="<?php echo (int)$chatId; ?>">
                                <button type="button" id="attachBtn" class="p-2 rounded-lg hover:bg-gray-100"><i class="fas fa-paperclip text-gray-600"></i></button>
                                <input type="file" id="fileInput" class="hidden" accept="image/*">
                                <div class="flex-1">
                                    <textarea id="messageInput" name="message" rows="1" placeholder="Write a message..." class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary resize-none" required></textarea>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-green-600 to-primary text-white rounded-lg hover:opacity-95">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                            <div id="composerInfo" class="text-xs text-gray-400 mt-2">Press Enter to send · Shift+Enter for newline</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Modern bubble styles */
    .msg-bubble { border-radius: 16px; padding: 10px 14px; display: inline-block; }
    .msg-own { border-bottom-right-radius: 4px; }
    .msg-their { border-bottom-left-radius: 4px; }
    .msg-meta { font-size: 11px; color: #9CA3AF; margin-top: 6px; }
</style>

<!-- Meetpoint Modal -->
<div id="meetpointModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Set Meetpoint</h2>
            <button onclick="closeMeetpointModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <p class="text-gray-600 mb-4">Save this location to your profile for future meetups</p>
        
        <form id="meetpointForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Location Description</label>
                <textarea id="meetpointInput" name="meetpoint_description" 
                    placeholder="e.g., Library Main Entrance, Behind CU Building, Coffee Shop Entrance" 
                    rows="3" maxlength="200"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                <p class="text-xs text-gray-500 mt-1"><span id="charCount">0</span>/200 characters</p>
            </div>
            
            <div class="flex gap-2">
                <button type="button" onclick="closeMeetpointModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold">
                    <i class="fas fa-check mr-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<style>

<script>
const chatId = <?php echo (int)$chatId; ?>;
const currentUserId = <?php echo (int)$currentUserId; ?>;
let isSending = false;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString();
}

function displayMessages(messages) {
    const messagesList = document.getElementById('messagesList');
    messagesList.innerHTML = '';
    if (!messages || messages.length === 0) {
        messagesList.innerHTML = '<p class="text-center text-gray-500">No messages yet. Say hello 👋</p>';
        return;
    }

    messages.forEach(msg => {
        const isOwn = Number(msg.sender_id) === Number(currentUserId);
        const wrapper = document.createElement('div');
        wrapper.className = isOwn ? 'flex justify-end' : 'flex justify-start items-start';

        const inner = document.createElement('div');
        inner.style.maxWidth = '70%';

        if (!isOwn) {
            const avatar = document.createElement('img');
            avatar.src = '../' + (msg.sender_pic || 'assets/images/default-avatar.jpg');
            avatar.alt = msg.sender_name || '';
            avatar.className = 'w-8 h-8 rounded-full object-cover mr-3 float-left';
            avatar.style.marginTop = '6px';
            inner.appendChild(avatar);
        }

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble ' + (isOwn ? 'bg-primary text-white msg-own' : 'bg-gray-100 text-gray-900 msg-their');
        bubble.innerHTML = `<div>${escapeHtml(msg.message_text || '')}</div>`;
        inner.appendChild(bubble);

        const meta = document.createElement('div');
        meta.className = 'msg-meta ' + (isOwn ? 'text-right' : 'text-left');
        meta.textContent = formatTime(msg.sent_at || new Date().toISOString());
        inner.appendChild(meta);

        wrapper.appendChild(inner);
        messagesList.appendChild(wrapper);
    });

    const container = document.getElementById('messagesContainer');
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
}

async function loadMessages() {
    try {
        const res = await fetch('../includes/chat/get-messages.php?chat_id=' + chatId, {cache: 'no-store'});
        const data = await res.json();
        if (data.success) {
            displayMessages(data.messages);
        }
    } catch (err) {
        console.error('Failed to load messages', err);
    }
}

async function sendMessage(text, file=null) {
    if (!text && !file) return;
    if (isSending) return;
    isSending = true;

    // optimistic UI
    const now = new Date().toISOString();
    const optimistic = { sender_id: currentUserId, message_text: text, sent_at: now };
    const staged = (document.__cachedMessages || []).concat([optimistic]);
    displayMessages(staged);

    try {
        let body; let headers = {};
        if (file) {
            body = new FormData();
            body.append('chat_id', chatId);
            body.append('message', text);
            body.append('attachment', file);
        } else {
            body = JSON.stringify({ chat_id: chatId, message: text });
            headers['Content-Type'] = 'application/json';
        }

        const res = await fetch('../includes/chat/send-message.php', { method: 'POST', body, headers });
        const data = await res.json();
        if (data.success) {
            await loadMessages();
        } else {
            alert(data.message || 'Failed to send message');
            await loadMessages();
        }
    } catch (err) {
        console.error('Send error', err);
        alert('Network error. Try again.');
        await loadMessages();
    }

    isSending = false;
}

document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const ta = document.getElementById('messageInput');
    const text = ta.value.trim();
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0] || null;
    if (!text && !file) return;
    
    sendMessage(text, file);
    
    ta.value = '';
    fileInput.value = '';
    ta.focus();
});

document.getElementById('messageInput').addEventListener('keydown', function(e){
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('messageForm').dispatchEvent(new Event('submit'));
    }
});

document.getElementById('attachBtn').addEventListener('click', () => document.getElementById('fileInput').click());

window.addEventListener('load', async () => {
    await loadMessages();
    setInterval(loadMessages, 3000);
});

// Meetpoint Modal Functions
function openMeetpointModal() {
    const modal = document.getElementById('meetpointModal');
    const input = document.getElementById('meetpointInput');
    input.value = '';
    updateCharCount();
    modal.classList.remove('hidden');
    input.focus();
}

function closeMeetpointModal() {
    document.getElementById('meetpointModal').classList.add('hidden');
    document.getElementById('meetpointInput').value = '';
    updateCharCount();
}

function updateCharCount() {
    const input = document.getElementById('meetpointInput');
    document.getElementById('charCount').textContent = input.value.length;
}

document.getElementById('meetpointForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const description = document.getElementById('meetpointInput').value.trim();
    
    if (!description) {
        showToast('Please enter a location description', 'error');
        return;
    }
    
    if (description.length > 200) {
        showToast('Location description must be 200 characters or less', 'error');
        return;
    }
    
    try {
        const response = await fetch('../includes/chat/save-meetpoint.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>'
            },
            body: new URLSearchParams({
                meetpoint_description: description,
                chat_id: '<?php echo htmlspecialchars($chatId, ENT_QUOTES); ?>'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`✓ Meetpoint saved! You have ${data.total_meetpoints || 0}/3 saved`, 'success');
            closeMeetpointModal();
        } else {
            showToast(data.error || 'Failed to save meetpoint', 'error');
        }
    } catch (error) {
        console.error('Error saving meetpoint:', error);
        showToast('Error saving meetpoint. Please try again.', 'error');
    }
});

// Update character count on input
document.getElementById('meetpointInput').addEventListener('input', updateCharCount);

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !document.getElementById('meetpointModal').classList.contains('hidden')) {
        closeMeetpointModal();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
