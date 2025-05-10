<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit();
}

// Get current page for highlighting active menu item
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveEasy Admin - Car Rental Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-page">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>
        
        <div class="admin-content">
            <!-- Top bar -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="topbar-title">
                        <span>DriveEasy Admin Dashboard</span>
                    </div>
                </div>
                
                <div class="topbar-right">
                    <a href="/admin/index.php" class="topbar-icon" title="Dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                    <a href="/admin/bookings.php?status=pending" class="topbar-icon" title="Pending Bookings">
                        <i class="fas fa-clock"></i>
                    </a>
                    <a href="/index.php" target="_blank" class="topbar-icon" title="View Website">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    
                    <div class="admin-user">
                        <div class="admin-user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="admin-user-info">
                            <div class="admin-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                            <div class="admin-user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main admin content will be included here -->
