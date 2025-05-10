<?php
// Initialize database connection and functions
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: /index.php");
    exit();
}

// Initialize variables
$errors = [];
$email = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    if (empty($errors)) {
        // Attempt login
        if (login($email, $password)) {
            // Set remember me cookie if selected
            if ($remember) {
                // In a real application, you would implement secure remember me functionality here
                // For this example, we'll just set a simple cookie with the email
                setcookie('remember_user', $email, time() + (86400 * 30), "/"); // 30 days
            }
            
            // Redirect to appropriate page
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '/index.php';
            unset($_SESSION['redirect_after_login']);
            
            header("Location: $redirect");
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}

// Include header
include_once __DIR__ . '/includes/header.php';
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>Login to Your Account</h1>
        <p>Sign in to manage your bookings and rental history</p>
    </div>
</div>

<!-- Login section -->
<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Welcome Back</h2>
                    <p>Please enter your credentials to continue</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="/login.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" required placeholder="Enter your password">
                        </div>
                    </div>
                    
                    <div class="form-group remember-me">
                        <div class="checkbox-group">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                </form>
                
                <div class="auth-links">
                    <p>Don't have an account? <a href="/register.php">Register Now</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .auth-section {
        padding: 4rem 0;
    }
    
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .auth-card {
        width: 100%;
        max-width: 450px;
        background-color: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2.5rem;
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .auth-header h2 {
        margin-bottom: 0.5rem;
    }
    
    .auth-header p {
        color: var(--text-light);
    }
    
    .remember-me {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
    }
    
    .checkbox-group input[type="checkbox"] {
        margin-right: 0.5rem;
    }
    
    .forgot-password {
        font-size: 0.9rem;
    }
    
    .auth-links {
        text-align: center;
        margin-top: 1.5rem;
    }
    
    @media (max-width: 576px) {
        .auth-card {
            padding: 1.5rem;
        }
    }
</style>

<?php
// Include footer
include_once __DIR__ . '/includes/footer.php';
?>
