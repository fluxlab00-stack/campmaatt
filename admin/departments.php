<?php
$page_title = 'Department Management';
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $department_name = trim($_POST['department_name']);
        
        if (!empty($department_name)) {
            $stmt = $conn->prepare("INSERT INTO departments (department_name) VALUES (?)");
            $stmt->bind_param('s', $department_name);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Department added successfully';
            } else {
                $_SESSION['error'] = 'Error adding department';
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $department_id = (int)$_POST['department_id'];
        $department_name = trim($_POST['department_name']);
        
        if (!empty($department_name) && $department_id > 0) {
            $stmt = $conn->prepare("UPDATE departments SET department_name = ? WHERE department_id = ?");
            $stmt->bind_param('si', $department_name, $department_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Department updated successfully';
            } else {
                $_SESSION['error'] = 'Error updating department';
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $department_id = (int)$_POST['department_id'];
        
        // Check if department is in use
        $check = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE department = (SELECT department_name FROM departments WHERE department_id = ?)");
        $check->bind_param('i', $department_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete department: it is currently in use by ' . $result['count'] . ' users';
        } else {
            $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
            $stmt->bind_param('i', $department_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Department deleted successfully';
            } else {
                $_SESSION['error'] = 'Error deleting department';
            }
        }
    }
    
    header("Location: departments.php");
    exit;
}

// Get all departments with user count
$departments = $conn->query("
    SELECT d.department_id, d.department_name, d.created_at,
           (SELECT COUNT(*) FROM users WHERE department_id = d.department_id) as user_count
    FROM departments d
    ORDER BY d.department_name ASC
");

require_once 'header.php';
?>

<!-- Add Department Form -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Department</h3>
        <form method="POST" class="flex gap-4">
            <input type="hidden" name="action" value="add">
            <input type="text" name="department_name" placeholder="Department Name" required
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                <i class="fas fa-plus mr-2"></i> Add Department
            </button>
        </form>
    </div>
</div>

<!-- Departments Table -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-800">
            All Departments <span class="text-sm text-gray-500 font-normal">(<?php echo $departments->num_rows; ?> total)</span>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($departments->num_rows > 0): ?>
                    <?php while ($dept = $departments->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dept['department_name']); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo number_format($dept['user_count']); ?> users
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($dept['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="editDepartment(<?php echo $dept['department_id']; ?>, '<?php echo htmlspecialchars($dept['department_name'], ENT_QUOTES); ?>')" 
                                            class="text-blue-600 hover:text-blue-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="department_id" value="<?php echo $dept['department_id']; ?>">
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
                            <i class="fas fa-building text-4xl mb-2 text-gray-300"></i>
                            <p>No departments found</p>
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
            <h3 class="text-xl font-semibold text-gray-800">Edit Department</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="department_id" id="edit_department_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Department Name</label>
                <input type="text" name="department_name" id="edit_department_name" required
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
function editDepartment(id, name) {
    document.getElementById('edit_department_id').value = id;
    document.getElementById('edit_department_name').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php require_once 'footer.php'; ?>
