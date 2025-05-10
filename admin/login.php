<?php
// Initialize database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn() && isAdmin()) {
    header("Location: /admin/index.php");
    exit();
}

$error = "";

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Attempt login
        if (login($email, $password)) {
            // Check if user is admin
            if (isAdmin()) {
                // Redirect to admin dashboard
                header("Location: /admin/index.php");
                exit();
            } else {
                // Not an admin, log them out
                logout();
                $error = "You don't have permission to access the admin area.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - DriveEasy Rentals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-login-page">
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="login-header">
                <div class="logo">
                    <span class="logo-text">DriveEasy</span>
                    <span class="logo-subtext">Admin</span>
                </div>
                <h1>Welcome Back</h1>
                <p>Sign in to access the admin dashboard</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/admin/login.php" class="admin-login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" required placeholder="Enter your email address">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" required placeholder="Enter your password">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </div>
            </form>
            
            <div class="back-to-website">
                <a href="/index.php">
                    <i class="fas fa-arrow-left"></i> Back to Website
                </a>
            </div>
        </div>
        
        <div class="admin-login-info">
            <div class="login-info-content">
                <h2>DriveEasy Admin Panel</h2>
                <p>Manage your car rental business with our powerful admin dashboard.</p>
                
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-car"></i>
                        <span>Manage car inventory</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Track bookings and reservations</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tags"></i>
                        <span>Organize car categories</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>View business analytics</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
