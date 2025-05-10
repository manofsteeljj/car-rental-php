<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Include functions
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Get current page
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveEasy Rentals - Premium Car Rental Service</title>
    <meta name="description" content="DriveEasy offers premium car rentals with a wide selection of vehicles for any occasion. Book your perfect ride today!">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Header section -->
        <header class="site-header">
            <div class="container">
                <div class="logo">
                    <a href="/index.php">
                        <span class="logo-text">DriveEasy</span>
                        <span class="logo-subtext">Rentals</span>
                    </a>
                </div>
                
                <?php include_once __DIR__ . '/navbar.php'; ?>
                
                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span class="user-name">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            <div class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <a href="/admin/index.php">Admin Dashboard</a>
                                <?php endif; ?>
                                <a href="/my-bookings.php">My Bookings</a>
                                <a href="/profile.php">Profile</a>
                                <a href="/logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-outline">Login</a>
                        <a href="/register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-toggle">
                    <span class="sr-only">Menu</span>
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </header>
        
        <!-- Display success or error message if set -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success container">
                <?= $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error container">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Main content starts -->
        <main class="site-main">
