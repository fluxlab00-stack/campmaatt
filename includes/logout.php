<?php
/**
 * Logout Process
 * Ends user session and redirects to homepage
 */

require_once __DIR__ . '/session.php';

// Destroy session
destroyUserSession();

// Set flash message
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to homepage
header("Location: ../index.php");
exit();
