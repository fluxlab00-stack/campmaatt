<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-md w-full p-8 relative my-4">
        <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold mb-2 text-primary">Welcome Back!</h2>
            <p class="text-gray-600">Join thousands of students on CampMart</p>
        </div>
        
        <form id="loginForm" method="POST" action="<?php echo baseUrl('includes/auth/login-process.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" placeholder="Your Campus Email" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                    <button type="button" onclick="togglePasswordVisibility('loginPassword')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-6 text-right">
                <a href="<?php echo baseUrl('pages/forgot-password.php'); ?>" class="text-sm text-primary hover:underline">Forgot Password?</a>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-green-600 transition font-semibold">
                Login
            </button>
        </form>
        
        <div class="mt-6">
            <p class="text-center text-gray-600 text-sm mb-4">Don't have an account? 
                <a href="#" onclick="closeLoginModal(); openRegisterModal();" class="text-primary hover:underline font-semibold">Register</a>
            </p>
            
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>
            
            <button class="w-full bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition font-semibold flex items-center justify-center">
                <img src="https://www.google.com/favicon.ico" alt="Google" class="w-5 h-5 mr-2">
                Continue with Google
            </button>
        </div>
        
        <p class="text-xs text-gray-500 text-center mt-6">
            By continuing, you agree to our <a href="<?php echo baseUrl('pages/terms.php'); ?>" class="text-primary hover:underline">Terms & Policies</a>
        </p>
    </div>
</div>

<!-- Register Modal -->

<div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-2xl w-full p-8 relative max-h-[90vh] overflow-y-auto my-4">
        <button onclick="closeRegisterModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold mb-2"><span class="text-primary">Join</span> <span class="text-3xl font-bold"><span class="text-primary">camp</span><span class="text-secondary">mart</span></span></h2>
            <p class="text-gray-600">Start buying and selling on campus today!</p>
        </div>
        
        <form id="registerForm" method="POST" action="<?php echo baseUrl('includes/auth/register-process.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">First Name</label>
                    <input type="text" name="first_name" placeholder="Abubakar" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Last Name</label>
                    <input type="text" name="last_name" placeholder="ibezim" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" placeholder="Abubakaribezim@gmail.com" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="registerPassword" name="password" placeholder="Create a strong password" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                        <button type="button" onclick="togglePasswordVisibility('registerPassword')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter password" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                        <button type="button" onclick="togglePasswordVisibility('confirmPassword')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Phone Number</label>
                <input type="tel" name="phone_number" placeholder="e.g. 08012345678" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">University/Campus</label>
                    <select name="campus_id" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                        <option value="">Select University</option>
                        <?php
                        // Ensure campuses table is populated with a comprehensive list of Nigerian universities
                        require_once __DIR__ . '/db.php';
                        $db = Database::getInstance();

                        $cntRes = $db->query("SELECT COUNT(*) AS cnt FROM campuses");
                        $cnt = 0;
                        if ($cntRes) {
                            $cnt = (int)($cntRes->fetch_assoc()['cnt'] ?? 0);
                        }

                        if ($cnt < 100) {
                            $unis = require __DIR__ . '/data/nigerian_universities.php';
                            foreach ($unis as $u) {
                                $name = $db->escape($u['name']);
                                $city = $db->escape($u['city'] ?? '');
                                $state = $db->escape($u['state'] ?? '');
                                $exists = $db->query("SELECT campus_id FROM campuses WHERE campus_name = '{$name}' LIMIT 1");
                                if ($exists && $exists->num_rows == 0) {
                                    $db->query("INSERT INTO campuses (campus_name, city, state, created_at, updated_at) VALUES ('{$name}','{$city}','{$state}', NOW(), NOW())");
                                }
                            }
                        }

                        $result = $db->query("SELECT campus_id, campus_name FROM campuses ORDER BY campus_name");
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . (int)$row['campus_id'] . "'>" . htmlspecialchars($row['campus_name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Course/Department</label>
                    <select name="department_id" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                        <option value="">Select Course</option>
                        <?php
                        // Load courses from data file
                        $courses = require __DIR__ . '/data/nigerian_courses.php';
                        sort($courses); // Sort alphabetically
                        
                        // Check if we need to populate the departments table with courses
                        $allCoursesExist = true;
                        foreach ($courses as $course) {
                            $courseName = $db->escape($course);
                            $exists = $db->query("SELECT department_id FROM departments WHERE department_name = '{$courseName}' LIMIT 1");
                            if (!$exists || $exists->num_rows == 0) {
                                $allCoursesExist = false;
                                break;
                            }
                        }
                        
                        // If not all courses exist, populate them
                        if (!$allCoursesExist) {
                            foreach ($courses as $course) {
                                $courseName = $db->escape($course);
                                $exists = $db->query("SELECT department_id FROM departments WHERE department_name = '{$courseName}' LIMIT 1");
                                if (!$exists || $exists->num_rows == 0) {
                                    $db->query("INSERT INTO departments (department_name, created_at, updated_at) VALUES ('{$courseName}', NOW(), NOW())");
                                }
                            }
                        }
                        
                        // Fetch and display all courses from departments table, alphabetically
                        $coursesInDB = array_map(function($c) use ($db) { return $db->escape($c); }, $courses);
                        $coursesSQL = "'" . implode("','", $coursesInDB) . "'";
                        $result = $db->query("SELECT department_id, department_name FROM departments WHERE department_name IN ({$coursesSQL}) ORDER BY department_name ASC");
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['department_id']}'>{$row['department_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Level</label>
                    <select name="level_id" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                        <option value="">Select Level</option>
                        <?php
                        $result = $db->query("SELECT level_id, level_name FROM levels WHERE level_name IN ('100L', '200L', '300L', '400L', '500L') ORDER BY CAST(REPLACE(level_name, 'L', '') AS UNSIGNED) ASC");
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['level_id']}'>{$row['level_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Profile Picture (Optional)</label>
                <input type="file" name="profile_picture" accept="image/*" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <div class="mt-6">
                <label class="flex items-start">
                    <input type="checkbox" name="agree_terms" required class="mt-1 mr-2">
                    <span class="text-sm text-gray-600">
                        I agree to the <a href="<?php echo baseUrl('pages/terms.php'); ?>" target="_blank" class="text-primary hover:underline">Terms & Policies</a> 
                        and <a href="<?php echo baseUrl('pages/privacy.php'); ?>" target="_blank" class="text-primary hover:underline">Privacy Policy</a>
                    </span>
                </label>
            </div>
            
            <button type="submit" class="w-full mt-6 bg-primary text-white py-3 rounded-lg hover:bg-green-600 transition font-semibold">
                Register
            </button>
                
        </form>
        
        <p class="text-center text-gray-600 text-sm mt-6">
            Already have an account? 
            <a href="#" onclick="closeRegisterModal(); openLoginModal();" class="text-primary hover:underline font-semibold">Login</a>
        </p>
        
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>
            
            <button class="w-full bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition font-semibold flex items-center justify-center">
                <img src="https://www.google.com/favicon.ico" alt="Google" class="w-5 h-5 mr-2">
                Continue with Google
            </button>
        </div>
    </div>
</div>

<!-- Post Item Modal (Only shown when logged in) -->
<?php if (isLoggedIn()): ?>
<div id="postModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-start justify-center p-4 pt-6 pb-6 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-3xl w-full p-8 relative my-2">
                <button onclick="closePostModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold mb-2 text-primary">Post Your Item</h2>
            <p class="text-gray-600">List your item and connect with buyers instantly</p>
        
        <form id="postItemForm" method="POST" action="<?php echo baseUrl('includes/listing/create-listing.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Upload Images (Max 8)</label>
                <input type="file" name="images[]" id="postImageInput" accept="image/*" multiple required 
                    onchange="previewPostImages(this)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                <p class="text-xs text-gray-500 mt-1">First image will be the primary image</p>
                
                <!-- Image Preview Container -->
                <div id="postImagePreview" class="mt-4 grid grid-cols-4 gap-2 hidden"></div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Item Name</label>
                <input type="text" name="title" placeholder="e.g., Brand New Dell Laptop" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Category</label>
                    <select name="category_id" id="categorySelect" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                        <option value="">Select Category</option>
                        <?php
                        $result = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Condition</label>
                    <select name="condition_status" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                        <option value="">Select Condition</option>
                        <option value="New">New</option>
                        <option value="Like New"> Raw </option>
                        <option value="Used - Good"> Good condition </option>
                        <option value="Used - Fair"> Fresh </option>
                        <option value="For Parts/Repair">Need Repair</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Description</label>
                <textarea name="description" rows="4" placeholder="Describe your item, its condition, features, and any flaws..." required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Price (₦)</label>
                    <input type="number" name="price" id="priceInput" placeholder="0.00" step="0.01" min="0" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Quantity</label>
                    <input type="number" name="quantity_available" value="1" min="1" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Location</label>
                    <input type="text" name="location_description" placeholder="e.g., Block C Hostel 5" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Tags (comma-separated)</label>
                <input type="text" name="tags" placeholder="e.g., gaming, laptop, core i7" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <div class="flex flex-wrap gap-4 mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_free" id="isFreeCheckbox" onchange="togglePrice()" class="mr-2">
                    <span class="text-sm text-gray-700">Free Item</span>
                </label>
                
                <label class="flex items-center">
                    <input type="checkbox" name="is_available_today" class="mr-2">
                    <span class="text-sm text-gray-700">Available Today ⏰</span>
                </label>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-pink-700 transition font-semibold">
                Post Listing
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Edit Profile Modal -->
<?php if (isLoggedIn()): ?>
<div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 pt-20 overflow-y-auto">
    
    <div class="bg-white rounded-lg max-w-md w-full p-8 relative my-8">
        <button onclick="closeEditProfileModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold mb-2 text-primary">Edit Profile Picture</h2>
            <p class="text-gray-600 text-sm">Upload a new profile picture</p>
        </div>
        
        <form id="editProfileForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-3">Choose Image</label>
                <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-primary transition cursor-pointer"
                    onclick="document.getElementById('profilePictureInput').click()">
                    <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" 
                        onchange="previewProfileImage(this)"
                        class="hidden">
                    <div id="profileImagePlaceholder" class="text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600 text-sm">Click to upload or drag and drop</p>
                        <p class="text-gray-500 text-xs mt-1">PNG, JPG, GIF up to 5MB</p>
                    </div>
                </div>
                
                <!-- Image Preview -->
                <div id="profileImagePreview" class="mt-4 hidden">
                    <img id="previewImage" src="" alt="Preview" class="w-full h-48 object-cover rounded-lg">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeEditProfileModal()" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-primary text-white rounded-lg hover:bg-green-600 transition font-semibold">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    // Modal Functions
    function openLoginModal() {
        document.getElementById('loginModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeLoginModal() {
        document.getElementById('loginModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function openRegisterModal() {
        document.getElementById('registerModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeRegisterModal() {
        document.getElementById('registerModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    <?php if (isLoggedIn()): ?>
    function openPostModal() {
        document.getElementById('postModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closePostModal() {
        document.getElementById('postModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    <?php else: ?>
    function openPostModal() {
        openLoginModal();
    }
    <?php endif; ?>
    
    <?php if (isLoggedIn()): ?>
    function openEditProfileModal() {
        document.getElementById('editProfileModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeEditProfileModal() {
        document.getElementById('editProfileModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset form
        document.getElementById('editProfileForm').reset();
        document.getElementById('profileImagePreview').classList.add('hidden');
        document.getElementById('profileImagePlaceholder').classList.remove('hidden');
    }
    
    function previewProfileImage(input) {
        const previewDiv = document.getElementById('profileImagePreview');
        const previewImg = document.getElementById('previewImage');
        const placeholder = document.getElementById('profileImagePlaceholder');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                input.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewDiv.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Handle profile picture form submission
    document.addEventListener('DOMContentLoaded', function() {
        const editForm = document.getElementById('editProfileForm');
        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const fileInput = document.getElementById('profilePictureInput');
                if (!fileInput.files || !fileInput.files[0]) {
                    alert('Please select an image');
                    return;
                }
                
                const formData = new FormData();
                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                formData.append('profile_picture', fileInput.files[0]);
                
                try {
                    const response = await fetch('<?php echo baseUrl('includes/profile/upload-profile-picture.php'); ?>', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Profile picture updated successfully!');
                        closeEditProfileModal();
                        // Reload to show updated picture
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        alert(data.message || 'Failed to upload picture');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Network error: ' + err.message);
                }
            });
        }
    });
    <?php endif; ?>
    
    // Toggle password visibility
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Toggle price field based on "Free Item" checkbox
    function togglePrice() {
        const isFree = document.getElementById('isFreeCheckbox').checked;
        const priceInput = document.getElementById('priceInput');
        
        if (isFree) {
            priceInput.value = '0';
            priceInput.disabled = true;
            priceInput.required = false;
        } else {
            priceInput.disabled = false;
            priceInput.required = true;
        }
    }
    
    // Preview images for post modal
    function previewPostImages(input) {
        const previewContainer = document.getElementById('postImagePreview');
        previewContainer.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            previewContainer.classList.remove('hidden');
            
            const maxFiles = Math.min(input.files.length, 8);
            for (let i = 0; i < maxFiles; i++) {
                const file = input.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg">
                        ${i === 0 ? '<span class="absolute top-1 left-1 bg-primary text-white px-2 py-0.5 rounded text-xs font-semibold">Primary</span>' : ''}
                        <span class="absolute top-1 right-1 bg-black bg-opacity-50 text-white px-2 py-0.5 rounded text-xs">${i + 1}</span>
                    `;
                    previewContainer.appendChild(div);
                };
                
                reader.readAsDataURL(file);
            }
            
            if (input.files.length > 8) {
                alert('Maximum 8 images allowed. Only first 8 will be uploaded.');
            }
        } else {
            previewContainer.classList.add('hidden');
        }
    }
    
    // Preview images for edit listing
    function previewEditImages(input) {
        const previewContainer = document.getElementById('editImagePreview');
        previewContainer.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            previewContainer.classList.remove('hidden');
            
            const maxFiles = Math.min(input.files.length, 8);
            for (let i = 0; i < maxFiles; i++) {
                const file = input.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                        <span class="absolute top-1 right-1 bg-black bg-opacity-50 text-white px-2 py-0.5 rounded text-xs">${i + 1}</span>
                    `;
                    previewContainer.appendChild(div);
                };
                
                reader.readAsDataURL(file);
            }
            
            if (input.files.length > 8) {
                alert('Maximum 8 images allowed. Only first 8 will be uploaded.');
            }
        } else {
            previewContainer.classList.add('hidden');
        }
    }
    
    // Close modals on outside click
    document.addEventListener('click', function(event) {
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        <?php if (isLoggedIn()): ?>
        const postModal = document.getElementById('postModal');
        const editProfileModal = document.getElementById('editProfileModal');
        <?php endif; ?>
        
        if (event.target === loginModal) closeLoginModal();
        if (event.target === registerModal) closeRegisterModal();
        <?php if (isLoggedIn()): ?>
        if (event.target === postModal) closePostModal();
        if (event.target === editProfileModal) closeEditProfileModal();
        <?php endif; ?>
    });
</script>

<?php if (isLoggedIn()): ?>
<!-- Lost & Found Modal (moved here so it's available site-wide) -->
<div id="lostFoundModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-start justify-center p-4 pt-6 pb-6 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-2xl w-full p-8 relative my-2">
        <button onclick="closeLostFoundModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        
            <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-primary mb-2">Found It - Report Item</h2>
            <p class="text-gray-600">Help reunite items with their owners</p>
        </div>
        
        <form id="lostFoundForm" method="POST" action="<?php echo baseUrl('includes/lost-found/create-report.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" id="lostFoundTypeInput" name="item_type" value="">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Item Name</label>
                <input type="text" name="item_name" placeholder="e.g., Blue iPhone 13" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Description</label>
                <textarea name="description" rows="4" placeholder="Describe the item and any identifying features..." required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Location</label>
                    <input type="text" name="location" placeholder="Where it was lost/found" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Date</label>
                    <input type="date" name="date_lost_found" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Upload Image (Optional)</label>
                <input type="file" name="image" accept="image/*" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary transition">
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-green-600 transition font-semibold">
                Submit Report
            </button>
        </form>
    </div>
</div>

<!-- Success Toast Notification for Lost/Found -->
<div id="lfSuccessToast" class="fixed bottom-6 right-6 bg-white rounded-lg shadow-2xl p-6 max-w-sm z-50 hidden transform transition-all duration-300 ease-out opacity-0 translate-y-8">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 pt-0.5">
            <i class="fas fa-check-circle text-primary text-2xl"></i>
        </div>
        <div class="flex-1">
            <p id="lfToastMessage" class="text-gray-800 font-medium text-sm leading-relaxed"></p>
        </div>
        <button onclick="closeLfSuccessToast()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 ml-2">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 rounded-b-lg overflow-hidden">
        <div id="lfToastProgressBar" class="h-full bg-primary transition-all duration-3000"></div>
    </div>
</div>

<script>
    function openLostFoundModal() {
        // If a type is already set in hidden input, keep it; otherwise default blank
        document.getElementById('lostFoundModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeLostFoundModal() {
        document.getElementById('lostFoundModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function showLfSuccessToast(message) {
        const toast = document.getElementById('lfSuccessToast');
        const toastMessage = document.getElementById('lfToastMessage');
        const progressBar = document.getElementById('lfToastProgressBar');
        toastMessage.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => { toast.classList.remove('opacity-0', 'translate-y-8'); toast.classList.add('opacity-100', 'translate-y-0'); }, 10);
        progressBar.style.width = '100%';
        progressBar.offsetHeight; // reflow
        progressBar.style.width = '0%';
        setTimeout(() => { closeLfSuccessToast(); }, 3000);
    }

    function closeLfSuccessToast() {
        const toast = document.getElementById('lfSuccessToast');
        toast.classList.add('opacity-0', 'translate-y-8');
        setTimeout(() => { toast.classList.add('hidden'); }, 300);
    }

    // AJAX submit for the Lost/Found form with custom success toast
    (function() {
        const form = document.getElementById('lostFoundForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const fd = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showLfSuccessToast(data.message);
                    form.reset();
                    setTimeout(() => { closeLostFoundModal(); }, 500);
                    setTimeout(() => { location.reload(); }, 3500);
                } else {
                    // Use global toast helper
                    showToast(data.message || 'Failed to submit report', 'error');
                }
            }).catch(err => {
                console.error(err);
                showToast('Upload failed. Please try again.', 'error');
            }).finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    })();
</script>
<?php endif; ?>
