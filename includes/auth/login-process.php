<?php
/**
 * Login Process
 * Handles user authentication
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
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
$errors = [];

if (empty($email) || !validateEmail($email)) {
    $errors[] = "Please enter a valid email address.";
}

if (empty($password)) {
    $errors[] = "Please enter your password.";
}

if (!empty($errors)) {
    setFlashMessage('error', implode(' ', $errors));
    header("Location: ../../index.php");
    exit();
}

// Get database instance
$db = Database::getInstance();

// Check if user exists and is active
$stmt = $db->prepare(
    "SELECT user_id, first_name, last_name, email, password_hash, profile_picture_url, is_admin, is_active, campus_id
     FROM users
     WHERE email = ?",
    "s",
    [$email]
);

if (!$stmt) {
    setFlashMessage('error', 'An error occurred. Please try again.');
    header("Location: ../../index.php");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Verify user and password
if (!$user) {
    setFlashMessage('error', 'Invalid email or password.');
    header("Location: ../../index.php");
    exit();
}

if (!$user['is_active']) {
    setFlashMessage('error', 'Your account has been suspended. Please contact support.');
    header("Location: ../../index.php");
    exit();
}

if (!password_verify($password, $user['password_hash'])) {
    setFlashMessage('error', 'Invalid email or password.');
    header("Location: ../../index.php");
    exit();
}

// Login successful - set session
setUserSession($user);

// Redirect to appropriate page
if (isset($_SESSION['redirect_after_login'])) {
    $redirect = $_SESSION['redirect_after_login'];
    unset($_SESSION['redirect_after_login']);
    header("Location: " . $redirect);
} else {
    setFlashMessage('success', 'Welcome back, ' . $user['first_name'] . '!');
    header("Location: ../../index.php");
}
exit();
