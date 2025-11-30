<?php
/**
 * User Profile Page
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

$pageTitle = "My Profile - CampMart";

// Get database instance
$db = Database::getInstance();

// Get current user
$user = getCurrentUser();
$userId = $user['user_id'];

// Fetch user details with campus, department, and level info
$stmt = $db->prepare(
    "SELECT u.*, c.campus_name, d.department_name, l.level_name
     FROM users u
     LEFT JOIN campuses c ON u.campus_id = c.campus_id
     LEFT JOIN departments d ON u.department_id = d.department_id
     LEFT JOIN levels l ON u.level_id = l.level_id
     WHERE u.user_id = ?",
    "i",
    [$userId]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $userDetails = $result->fetch_assoc();
    $stmt->close();
}

// Get user statistics
$stats = [
    'listings' => 0,
    'sold' => 0,
    'bookmarks' => 0,
    'views' => 0
];

$result = $db->query("SELECT COUNT(*) as count FROM listings WHERE user_id = {$userId} AND status = 'active'");
if ($result && $row = $result->fetch_assoc()) {
    $stats['listings'] = $row['count'];
}

$result = $db->query("SELECT COUNT(*) as count FROM listings WHERE user_id = {$userId} AND status = 'sold'");
if ($result && $row = $result->fetch_assoc()) {
    $stats['sold'] = $row['count'];
}

$result = $db->query("SELECT COUNT(*) as count FROM bookmarks WHERE user_id = {$userId}");
if ($result && $row = $result->fetch_assoc()) {
    $stats['bookmarks'] = $row['count'];
}

$result = $db->query("SELECT SUM(views_count) as total FROM listings WHERE user_id = {$userId}");
if ($result && $row = $result->fetch_assoc()) {
    $stats['views'] = $row['total'] ?? 0;
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Profile Header -->
<section class="hero-gradient text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <div class="relative">
                <img src="<?php echo htmlspecialchars($userDetails['profile_picture_url'] ? baseUrl($userDetails['profile_picture_url']) : baseUrl('assets/images/default-avatar.png')); ?>" 
                     alt="Profile Picture" 
                     class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                <button onclick="openEditPhotoModal()" class="absolute bottom-0 right-0 bg-white text-primary p-2 rounded-full shadow-lg hover:bg-gray-100 transition">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            
            <div class="text-center md:text-left flex-1">
                <h1 class="text-3xl font-bold mb-2">
                    <?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?>
                </h1>
                <p class="text-gray-100 mb-2">
                    <i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($userDetails['email']); ?>
                </p>
                <p class="text-gray-100 mb-2">
                    <i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($userDetails['phone_number']); ?>
                </p>
                <p class="text-gray-100">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    <?php echo htmlspecialchars($userDetails['campus_name']); ?> • 
                    <?php echo htmlspecialchars($userDetails['department_name']); ?> • 
                    <?php echo htmlspecialchars($userDetails['level_name']); ?>
                </p>
            </div>
            
            <button onclick="openEditProfileModal()" class="px-6 py-3 bg-white text-primary rounded-lg hover:bg-gray-100 transition font-semibold">
                <i class="fas fa-edit mr-2"></i> Edit Profile
            </button>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="p-4">
                <div class="text-3xl font-bold text-primary"><?php echo $stats['listings']; ?></div>
                <div class="text-gray-600 text-sm">Active Listings</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-green-500"><?php echo $stats['sold']; ?></div>
                <div class="text-gray-600 text-sm">Items Sold</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-accent"><?php echo $stats['bookmarks']; ?></div>
                <div class="text-gray-600 text-sm">Saved Items</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-blue-500"><?php echo number_format($stats['views']); ?></div>
                <div class="text-gray-600 text-sm">Total Views</div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Actions -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Quick Actions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="my-listings.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-list text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">My Listings</h3>
                </div>
                <p class="text-gray-600">Manage your active and sold items</p>
            </a>
            
            <a href="saved-items.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-accent bg-opacity-10 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-bookmark text-2xl text-accent"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Saved Items</h3>
                </div>
                <p class="text-gray-600">View items you've bookmarked</p>
            </a>
            
            <a href="messages.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-500 bg-opacity-10 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-envelope text-2xl text-blue-500"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
                </div>
                <p class="text-gray-600">Chat with buyers and sellers</p>
            </a>
        </div>
    </div>
</section>

<!-- Account Settings -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Account Settings</h2>
        
        <div class="bg-gray-50 rounded-lg p-6 space-y-4">
            <div class="flex justify-between items-center pb-4 border-b">
                <div>
                    <h3 class="font-semibold text-gray-900">Change Password</h3>
                    <p class="text-sm text-gray-600">Update your password regularly for security</p>
                </div>
                <button onclick="openChangePasswordModal()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-pink-700 transition">
                    Change
                </button>
            </div>
            
            <div class="flex justify-between items-center pb-4 border-b">
                <div>
                    <h3 class="font-semibold text-gray-900">Email Notifications</h3>
                    <p class="text-sm text-gray-600">Receive updates about your listings and messages</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
            
            <div class="flex justify-between items-center pb-4 border-b">
                <div>
                    <h3 class="font-semibold text-gray-900">Two-Factor Authentication</h3>
                    <p class="text-sm text-gray-600">Add an extra layer of security</p>
                </div>
                <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Enable
                </button>
            </div>
            
            <div class="flex justify-between items-center pt-4">
                <div>
                    <h3 class="font-semibold text-red-600">Delete Account</h3>
                    <p class="text-sm text-gray-600">Permanently delete your account and all data</p>
                </div>
                <button onclick="confirmDeleteAccount()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                    Delete
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6 relative max-h-[90vh] overflow-y-auto">
        <button onclick="closeEditProfileModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <h2 class="text-2xl font-bold text-primary mb-6">Edit Profile</h2>
        
        <form id="editProfileForm" method="POST" action="<?php echo baseUrl('includes/profile/update-profile.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($userDetails['first_name']); ?>" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($userDetails['last_name']); ?>" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Phone Number</label>
                <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($userDetails['phone_number']); ?>" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Profile Picture (Optional)</label>
                <input id="profilePictureInput" type="file" name="profile_picture" accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <?php
                $userMeetpoints = [];
                if (!empty($userDetails['preferred_meetpoints'])) {
                    $decoded = @json_decode($userDetails['preferred_meetpoints'], true);
                    if (is_array($decoded)) $userMeetpoints = $decoded;
                }
            ?>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Preferred Meetpoints (Optional)</label>

                <div id="meetpointsList" class="space-y-3 max-h-56 overflow-y-auto p-1">
                    <?php if (!empty($userMeetpoints)): ?>
                        <?php foreach ($userMeetpoints as $mp): ?>
                            <div class="meetpoint-entry p-3 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="w-full">
                                        <textarea name="meetpoint_desc[]" required rows="2" class="w-full px-3 py-2 border rounded" placeholder="Description / Address"><?php echo htmlspecialchars(is_array($mp) ? ($mp['description'] ?? $mp[0] ?? '') : $mp); ?></textarea>
                                    </div>
                                    <button type="button" class="ml-2 text-red-600 removeMeetpointBtn" title="Remove">&times;</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-3">
                    <button type="button" id="addMeetpointBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-green-700 transition">Add Meetpoint</button>
                    <p class="text-sm text-gray-500 mt-2">You can save up to 3 meetpoints. Title and Description are required. Locked meetpoints cannot be changed once accepted in a chat.</p>
                </div>

                <template id="meetpointTemplate">
                    <div class="meetpoint-entry p-3 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div class="w-full">
                                <textarea name="meetpoint_desc[]" required rows="2" class="w-full px-3 py-2 border rounded" placeholder="Description / Address"></textarea>
                            </div>
                            <button type="button" class="ml-2 text-red-600 removeMeetpointBtn" title="Remove">&times;</button>
                        </div>
                    </div>
                </template>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-pink-700 transition font-semibold">
                Save Changes
            </button>
        </form>
    </div>
</div>

<script>
    function openEditProfileModal() {
        document.getElementById('editProfileModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeEditProfileModal() {
        document.getElementById('editProfileModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function openChangePasswordModal() {
        alert('Change password functionality coming soon!');
    }
    
    function openEditPhotoModal() {
        // Open the edit profile modal and focus the file input
        openEditProfileModal();
        setTimeout(function() {
            var input = document.getElementById('profilePictureInput');
            if (input) input.scrollIntoView({behavior: 'smooth', block: 'center'});
        }, 200);
    }
    
    function confirmDeleteAccount() {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            window.location.href = '../includes/profile/delete-account.php';
        }
    }

    // Form submission handler
    document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('✓ Profile updated successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.message || 'Failed to update profile', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Network error. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });
</script>

<script>
// Meetpoints UI handlers
function updateRemoveButtons() {
    document.querySelectorAll('.removeMeetpointBtn').forEach(btn => {
        btn.onclick = function() {
            const entry = this.closest('.meetpoint-entry');
            if (entry) entry.remove();
            updateAddButtonState();
        };
    });
}

function updateAddButtonState() {
    const list = document.getElementById('meetpointsList');
    const addBtn = document.getElementById('addMeetpointBtn');
    const count = list.querySelectorAll('.meetpoint-entry').length;
    addBtn.disabled = count >= 3;
    addBtn.classList.toggle('opacity-50', count >= 3);
}

document.getElementById('addMeetpointBtn').addEventListener('click', function() {
    const list = document.getElementById('meetpointsList');
    const tmpl = document.getElementById('meetpointTemplate');
    const clone = tmpl.content.cloneNode(true);
    list.appendChild(clone);
    updateRemoveButtons();
    updateAddButtonState();
});

// initialize existing remove buttons
updateRemoveButtons();
updateAddButtonState();

// Inline edit support: enable quick edit of name and phone in header
const csrfToken = '<?php echo generateCSRFToken(); ?>';
function enableInlineEdit() {
    const header = document.querySelector('.hero-gradient .flex-1');
    if (!header) return;
    const nameEl = header.querySelector('h1');
    const phoneEl = header.querySelector('p:nth-of-type(2)');

    // replace name and phone with inputs
    const nameParts = nameEl.textContent.trim().split(' ');
    const first = nameParts.shift() || '';
    const last = nameParts.join(' ') || '';

    nameEl.innerHTML = `<input id="inlineFirst" class="px-3 py-2 rounded border" value="${first}"> <input id="inlineLast" class="px-3 py-2 rounded border" value="${last}">`;
    phoneEl.innerHTML = `<i class="fas fa-phone mr-2"></i><input id="inlinePhone" class="px-3 py-2 rounded border" value="${phoneEl.textContent.trim()}">`;

    // change edit button into Save/Cancel
    const btn = document.querySelector('button[onclick="openEditProfileModal()"]');
    if (btn) {
        btn.outerHTML = `<div class="flex gap-2"><button id="saveInlineBtn" class="px-6 py-3 bg-white text-primary rounded-lg hover:bg-gray-100 transition font-semibold">Save</button><button id="cancelInlineBtn" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg">Cancel</button></div>`;
        document.getElementById('saveInlineBtn').addEventListener('click', saveInlineEdits);
        document.getElementById('cancelInlineBtn').addEventListener('click', cancelInlineEdits);
    }
}

function cancelInlineEdits() { 
    window.location.reload(); 
}

async function saveInlineEdits() {
    const first = document.getElementById('inlineFirst').value.trim();
    const last = document.getElementById('inlineLast').value.trim();
    const phone = document.getElementById('inlinePhone').value.trim();
    
    if (!first || !last || !phone) { 
        showToast('First name, last name and phone are required', 'error');
        return; 
    }

    const saveBtn = document.getElementById('saveInlineBtn');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const form = new FormData();
    form.append('csrf_token', csrfToken);
    form.append('first_name', first);
    form.append('last_name', last);
    form.append('phone_number', phone);

    try {
        const res = await fetch('<?php echo baseUrl('includes/profile/update-profile.php'); ?>', { 
            method: 'POST', 
            body: form, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' } 
        });
        const data = await res.json();
        if (data.success) {
            showToast('✓ Profile updated successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to save profile', 'error');
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    } catch (err) { 
        console.error(err); 
        showToast('Network error. Please try again.', 'error');
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    }
}

// fetch locked meetpoints and apply UI locks
async function applyLockedMeetpoints() {
    try {
        const res = await fetch('../includes/profile/get-locked-meetpoints.php', {cache: 'no-store'});
        const data = await res.json();
        if (!data.success) return;
        const locked = data.locked || [];
        if (locked.length === 0) return;

        const entries = document.querySelectorAll('.meetpoint-entry');
        entries.forEach(entry => {
            const ta = entry.querySelector('textarea[name="meetpoint_desc[]"]');
            const btn = entry.querySelector('.removeMeetpointBtn');
            if (!ta) return;
            const val = ta.value.trim();
            if (locked.includes(val)) {
                // mark readonly and disable remove
                ta.readOnly = true;
                ta.classList.add('bg-gray-100');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                // show lock badge
                const badge = document.createElement('div');
                badge.className = 'text-xs text-red-600 mt-2 font-semibold';
                badge.textContent = 'Locked (accepted in chat)';
                entry.appendChild(badge);
            }
        });
    } catch (err) {
        console.error('Failed to load locked meetpoints', err);
    }
}

// run after init
applyLockedMeetpoints();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/modals.php'; ?>
