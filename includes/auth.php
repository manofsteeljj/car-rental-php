<?php
// Only start session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Authentication functions for the application
 */

/**
 * Check if a user is logged in
 * 
 * @return bool True if the user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user is an admin
 * 
 * @return bool True if the user is an admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Authenticate a user
 * 
 * @param string $email User's email
 * @param string $password User's password
 * @return bool True if authentication is successful, false otherwise
 */
function login($email, $password) {
    global $pdo;
    
    $email = sanitize($email);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // User found and password matches
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            return true;
        }
    } catch (PDOException $e) {
        // Log error
        error_log("Login error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Log out the current user
 */
function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page
    redirect('/index.php');
}

/**
 * Check if the current user is authorized to access admin pages
 * Redirects to login page if not authorized
 */
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "You must be an administrator to access this page.";
        redirect('/admin/login.php');
    }
}

/**
 * Check if a user is logged in, redirect to login page if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "You must be logged in to access this page.";
        redirect('/login.php');
    }
}

/**
 * Register a new user
 * 
 * @param string $name User's name
 * @param string $email User's email
 * @param string $password User's password
 * @return bool True if registration is successful, false otherwise
 */
function registerUser($name, $email, $password) {
    global $pdo;
    
    $name = sanitize($name);
    $email = sanitize($email);
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        return false; // Email already exists
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, created_at) 
            VALUES (:name, :email, :password, 'customer', CURRENT_TIMESTAMP)
        ");
        
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}
?>
