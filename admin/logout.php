<?php
/**
 * Admin Logout
 */

session_start();

// Clear all admin session variables
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

// Destroy session
session_destroy();

// Redirect to login
header("Location: login.php");
exit;
