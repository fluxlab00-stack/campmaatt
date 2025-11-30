<?php
require_once 'auth.php';
requireAdmin();
require_once '../includes/db.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get all users with related data
$sql = "SELECT u.user_id, 
               CONCAT(u.first_name, ' ', u.last_name) as full_name,
               u.first_name,
               u.last_name,
               u.email,
               u.phone_number,
               (SELECT campus_name FROM campuses WHERE campus_id = u.campus_id) as campus,
               (SELECT department_name FROM departments WHERE department_id = u.department_id) as department,
               (SELECT level_name FROM levels WHERE level_id = u.level_id) as level,
               u.is_verified,
               u.is_active,
               u.is_admin,
               u.average_rating,
               u.total_ratings,
               u.created_at,
               u.last_login_at,
               (SELECT COUNT(*) FROM listings WHERE user_id = u.user_id) as total_listings,
               (SELECT COUNT(*) FROM listings WHERE user_id = u.user_id AND status = 'active') as active_listings,
               (SELECT COUNT(*) FROM listings WHERE user_id = u.user_id AND status = 'sold') as sold_listings
        FROM users u
        ORDER BY u.created_at DESC";

$result = $conn->query($sql);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=campmart_users_export_' . date('Y-m-d_His') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add column headers
fputcsv($output, [
    'User ID',
    'Full Name',
    'First Name',
    'Last Name',
    'Email',
    'Phone',
    'Campus',
    'Department',
    'Level',
    'Verified',
    'Active',
    'Admin',
    'Average Rating',
    'Total Ratings',
    'Total Listings',
    'Active Listings',
    'Sold Listings',
    'Joined Date',
    'Last Login'
]);

// Add data rows
while ($user = $result->fetch_assoc()) {
    fputcsv($output, [
        $user['user_id'],
        $user['full_name'],
        $user['first_name'],
        $user['last_name'],
        $user['email'],
        $user['phone_number'] ?? 'N/A',
        $user['campus'] ?? 'N/A',
        $user['department'] ?? 'N/A',
        $user['level'] ?? 'N/A',
        $user['is_verified'] ? 'Yes' : 'No',
        $user['is_active'] ? 'Yes' : 'No',
        $user['is_admin'] ? 'Yes' : 'No',
        $user['average_rating'],
        $user['total_ratings'],
        $user['total_listings'],
        $user['active_listings'],
        $user['sold_listings'],
        date('Y-m-d H:i:s', strtotime($user['created_at'])),
        $user['last_login_at'] ? date('Y-m-d H:i:s', strtotime($user['last_login_at'])) : 'Never'
    ]);
}

fclose($output);
exit;
