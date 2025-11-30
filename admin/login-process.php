<?php
/**
 * Admin Login Process
 */

session_start();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: login.php");
    exit;
}

$db = Database::getInstance();

// Check if user exists and is admin
$stmt = $db->prepare("SELECT user_id, first_name, last_name, email, password_hash, is_admin FROM users WHERE email = ? AND is_active = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid credentials.";
    header("Location: login.php");
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['error'] = "Invalid credentials.";
    header("Location: login.php");
    exit;
}

// Check if user is admin
if (!$user['is_admin']) {
    $_SESSION['error'] = "Unauthorized access. Admin privileges required.";
    header("Location: login.php");
    exit;
}

// Set admin session
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = $user['user_id'];
$_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['admin_email'] = $user['email'];

// Update last login
$updateStmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE user_id = ?");
$updateStmt->bind_param("i", $user['user_id']);
$updateStmt->execute();

$_SESSION['success'] = "Welcome back, " . $user['first_name'] . "!";
header("Location: index.php");
exit;
