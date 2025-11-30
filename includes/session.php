<?php
/**
 * Session Management Functions
 * Handles user sessions and authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'first_name' => $_SESSION['first_name'] ?? '',
        'last_name' => $_SESSION['last_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'profile_picture_url' => $_SESSION['profile_picture'] ?? 'assets/images/default-avatar.png',
        'is_admin' => $_SESSION['is_admin'] ?? false
    ];
}

/**
 * Set user session data
 * @param array $userData User data from database
 */
function setUserSession($userData) {
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['first_name'] = $userData['first_name'];
    $_SESSION['last_name'] = $userData['last_name'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['profile_picture'] = $userData['profile_picture_url'] ?? 'assets/images/default-avatar.png';
    $_SESSION['is_admin'] = $userData['is_admin'] ?? false;
    $_SESSION['campus_id'] = $userData['campus_id'] ?? null;
    
    // Update last login
    require_once __DIR__ . '/db.php';
    $db = Database::getInstance();
    $stmt = $db->prepare(
        "UPDATE users SET last_login_at = NOW() WHERE user_id = ?",
        "i",
        [$userData['user_id']]
    );
    if ($stmt) {
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Destroy user session (logout)
 */
function destroyUserSession() {
    session_unset();
    session_destroy();
    session_start(); // Restart for flash messages
}

/**
 * Require login - redirect to home if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login to access this page.";
        header("Location: /campmart/index.php");
        exit();
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        header("Location: /campmart/index.php");
        exit();
    }
}

/**
 * Set flash message
 * @param string $type Type of message (success, error, warning, info)
 * @param string $message Message content
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Get and clear flash message
 * @return array|null ['type' => 'success', 'message' => '...']
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'type' => $_SESSION['flash_type'] ?? 'info',
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
