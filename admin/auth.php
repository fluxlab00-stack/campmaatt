<?php
/**
 * Admin Authentication Check
 */

session_start();

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['error'] = "Please login to access the admin panel.";
        header("Location: login.php");
        exit;
    }
}

function getAdminName() {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function getAdminEmail() {
    return $_SESSION['admin_email'] ?? '';
}

function getAdminId() {
    return $_SESSION['admin_id'] ?? 0;
}
