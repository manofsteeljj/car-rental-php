<?php
// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include auth functions
require_once __DIR__ . '/includes/auth.php';

// Log the user out
logout();

// Redirect to home page
header("Location: /index.php");
exit();
?>
