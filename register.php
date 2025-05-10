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
$name = $email = $phone = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email address already exists. Please login or use a different email.";
        }
    }
    
    // If no errors, register the user
    if (empty($errors)) {
        if (registerUser($name, $email, $password)) {
            // Auto-login after registration
            login($email, $password);
            
            // Set success message
            $_SESSION['success'] = "Registration successful! Welcome to DriveEasy Rentals.";
            
            // Redirect to home page
            header("Location: /index.php");
            exit();
        } else {
            $errors[] = "An error occurred during registration. Please try again.";
        }
    }
}

// Include header
include_once __DIR__ . '/includes/header.php';
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>Create an Account</h1>
        <p>Join DriveEasy to book cars and manage your rentals</p>
    </div>
</div>

<!-- Registration section -->
<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card register-card">
                <div class="auth-header">
                    <h2>Sign Up</h2>
                    <p>Fill in your details to create an account</p>
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
                
                <form action="/register.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" id="name" required value="<?= htmlspecialchars($name) ?>" placeholder="Enter your full name">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Enter your phone number">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" required placeholder="Create a password">
                        </div>
                        <p class="form-help">Password must be at least 6 characters long</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm your password">
                        </div>
                    </div>
                    
                    <div class="form-group terms-agreement">
                        <div class="checkbox-group">
                            <input type="checkbox" name="terms" id="terms" required>
                            <label for="terms">I agree to the <a href="/pages/terms.php">Terms & Conditions</a> and <a href="/pages/privacy.php">Privacy Policy</a></label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </div>
                </form>
                
                <div class="auth-links">
                    <p>Already have an account? <a href="/login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .register-card {
        max-width: 500px;
    }
    
    .form-help {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-top: 0.35rem;
    }
    
    .terms-agreement {
        margin-bottom: 1.5rem;
    }
    
    .terms-agreement a {
        text-decoration: underline;
    }
</style>

<?php
// Include footer
include_once __DIR__ . '/includes/footer.php';
?>
