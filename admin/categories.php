<?php
$page_title = 'Category Management';
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $category_name = trim($_POST['category_name']);
        $icon_url = trim($_POST['icon'] ?? '');
        
        if (!empty($category_name)) {
            $stmt = $conn->prepare("INSERT INTO categories (category_name, icon_url) VALUES (?, ?)");
            $stmt->bind_param('ss', $category_name, $icon_url);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Category added successfully';
            } else {
                $_SESSION['error'] = 'Error adding category';
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $category_id = (int)$_POST['category_id'];
        $category_name = trim($_POST['category_name']);
        $icon_url = trim($_POST['icon'] ?? '');
        
        if (!empty($category_name) && $category_id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET category_name = ?, icon_url = ? WHERE category_id = ?");
            $stmt->bind_param('ssi', $category_name, $icon_url, $category_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Category updated successfully';
            } else {
                $_SESSION['error'] = 'Error updating category';
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $category_id = (int)$_POST['category_id'];
        
        // Check if category is in use
        $check = $conn->prepare("SELECT COUNT(*) as count FROM listings WHERE category_id = ?");
        $check->bind_param('i', $category_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete category: it has ' . $result['count'] . ' listings';
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->bind_param('i', $category_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Category deleted successfully';
            } else {
                $_SESSION['error'] = 'Error deleting category';
            }
        }
    }
    
    header("Location: categories.php");
    exit;
}

// Get all categories with listing count
$categories = $conn->query("
    SELECT c.category_id, c.category_name, c.icon_url, c.created_at,
           (SELECT COUNT(*) FROM listings WHERE category_id = c.category_id) as listing_count
    FROM categories c
    ORDER BY c.category_name ASC
");

require_once 'header.php';
?>

<!-- Add Category Form -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Category</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="action" value="add">
            <div class="md:col-span-2">
                <input type="text" name="category_name" placeholder="Category Name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <input type="text" name="icon" placeholder="Icon (e.g., fa-laptop)"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                    <i class="fas fa-plus mr-2"></i> Add Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-800">
            All Categories <span class="text-sm text-gray-500 font-normal">(<?php echo $categories->num_rows; ?> total)</span>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Listings</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($categories->num_rows > 0): ?>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50" id="category-row-<?php echo $category['category_id']; ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if ($category['icon_url']): ?>
                                        <div class="w-10 h-10 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center">
                                            <i class="fas <?php echo htmlspecialchars($category['icon_url']); ?> text-primary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($category['category_name']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo number_format($category['listing_count']); ?> listings
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="editCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($category['icon_url'] ?? '', ENT_QUOTES); ?>')" 
                                            class="text-blue-600 hover:text-blue-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-tags text-4xl mb-2 text-gray-300"></i>
                            <p>No categories found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6 border-b flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-800">Edit Category</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="category_id" id="edit_category_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                <input type="text" name="category_name" id="edit_category_name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Icon (Font Awesome class)</label>
                <input type="text" name="icon" id="edit_icon" placeholder="e.g., fa-laptop"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                    Save Changes
                </button>
                <button type="button" onclick="closeEditModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name, icon) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_icon').value = icon;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php require_once 'footer.php'; ?>
