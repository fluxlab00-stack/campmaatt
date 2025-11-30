<?php
/**
 * Registration Process
 * Handles new user registration
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('error', 'Invalid request. Please try again.');
    header("Location: ../../index.php");
    exit();
}

// Get and sanitize input
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName = sanitize($_POST['last_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$phoneNumber = sanitize($_POST['phone_number'] ?? '');
$campusId = intval($_POST['campus_id'] ?? 0);
$departmentId = intval($_POST['department_id'] ?? 0);
$levelId = intval($_POST['level_id'] ?? 0);

// Validation
$errors = [];

if (empty($firstName)) {
    $errors[] = "First name is required.";
}

if (empty($lastName)) {
    $errors[] = "Last name is required.";
}

if (empty($email) || !validateEmail($email)) {
    $errors[] = "Please enter a valid email address.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if (empty($phoneNumber) || !validatePhone($phoneNumber)) {
    $errors[] = "Please enter a valid Nigerian phone number.";
}

if ($campusId <= 0) {
    $errors[] = "Please select your campus.";
}

if ($departmentId <= 0) {
    $errors[] = "Please select your department.";
}

if ($levelId <= 0) {
    $errors[] = "Please select your level.";
}

if (!isset($_POST['agree_terms'])) {
    $errors[] = "You must agree to the Terms & Policies.";
}

if (!empty($errors)) {
    setFlashMessage('error', implode(' ', $errors));
    header("Location: ../../index.php");
    exit();
}

// Get database instance
$db = Database::getInstance();

// Check if email already exists
$stmt = $db->prepare(
    "SELECT user_id FROM users WHERE email = ?",
    "s",
    [$email]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        setFlashMessage('error', 'An account with this email already exists.');
        $stmt->close();
        header("Location: ../../index.php");
        exit();
    }
    $stmt->close();
}

// Check if phone number already exists
$stmt = $db->prepare(
    "SELECT user_id FROM users WHERE phone_number = ?",
    "s",
    [$phoneNumber]
);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        setFlashMessage('error', 'An account with this phone number already exists.');
        $stmt->close();
        header("Location: ../../index.php");
        exit();
    }
    $stmt->close();
}

// Handle profile picture upload
$profilePicture = 'assets/images/default-avatar.png';
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $uploadResult = uploadImage($_FILES['profile_picture'], 'profiles');
    if ($uploadResult['success']) {
        $profilePicture = $uploadResult['filename'];
    }
}

// Hash password
$passwordHash = password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);

// Generate WhatsApp link
$whatsappLink = generateWhatsAppLink($phoneNumber);

// Begin transaction
$db->beginTransaction();

try {
    // Insert user
    $stmt = $db->prepare(
        "INSERT INTO users (first_name, last_name, email, password_hash, phone_number, whatsapp_link, 
         profile_picture_url, campus_id, department_id, level_id, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        "sssssssiis",
        [
            $firstName, $lastName, $email, $passwordHash, $phoneNumber, $whatsappLink,
            $profilePicture, $campusId, $departmentId, $levelId
        ]
    );
    
    if (!$stmt) {
        throw new Exception("Failed to create account.");
    }
    
    $stmt->execute();
    $userId = $db->getLastInsertId();
    $stmt->close();
    
    // Commit transaction
    $db->commit();
    
    // Get user data for session
    $stmt = $db->prepare(
        "SELECT user_id, first_name, last_name, email, profile_picture_url, is_admin, campus_id
         FROM users WHERE user_id = ?",
        "i",
        [$userId]
    );
    
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();
        
        // Set session
        setUserSession($userData);
        
        setFlashMessage('success', 'Welcome to CampMart, ' . $firstName . '! Your account has been created successfully.');
        header("Location: ../../index.php");
        exit();
    }
    
} catch (Exception $e) {
    $db->rollback();
    error_log("Registration error: " . $e->getMessage());
    setFlashMessage('error', 'An error occurred during registration. Please try again.');
    header("Location: ../../index.php");
    exit();
}
