<?php
// Get current page for highlighting active menu item
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="site-nav">
    <ul class="main-menu">
        <li class="<?= $current_page === 'index' ? 'active' : '' ?>">
            <a href="/index.php">Home</a>
        </li>
        <li class="<?= $current_page === 'cars' ? 'active' : '' ?>">
            <a href="/pages/cars.php">Cars</a>
        </li>
        <li class="<?= $current_page === 'categories' ? 'active' : '' ?>">
            <a href="/pages/categories.php">Categories</a>
        </li>
        <li class="<?= in_array($current_page, ['booking', 'reservation']) ? 'active' : '' ?>">
            <a href="/pages/booking.php">Book Now</a>
        </li>
        <li class="<?= $current_page === 'about' ? 'active' : '' ?>">
            <a href="/pages/about.php">About</a>
        </li>
        <li class="<?= $current_page === 'contact' ? 'active' : '' ?>">
            <a href="/pages/contact.php">Contact</a>
        </li>
    </ul>
</nav>

<div class="mobile-nav">
    <ul class="mobile-menu">
        <li class="<?= $current_page === 'index' ? 'active' : '' ?>">
            <a href="/index.php"><i class="fas fa-home"></i> Home</a>
        </li>
        <li class="<?= $current_page === 'cars' ? 'active' : '' ?>">
            <a href="/pages/cars.php"><i class="fas fa-car"></i> Cars</a>
        </li>
        <li class="<?= $current_page === 'categories' ? 'active' : '' ?>">
            <a href="/pages/categories.php"><i class="fas fa-tags"></i> Categories</a>
        </li>
        <li class="<?= in_array($current_page, ['booking', 'reservation']) ? 'active' : '' ?>">
            <a href="/pages/booking.php"><i class="fas fa-calendar-alt"></i> Book Now</a>
        </li>
        <li class="<?= $current_page === 'about' ? 'active' : '' ?>">
            <a href="/pages/about.php"><i class="fas fa-info-circle"></i> About</a>
        </li>
        <li class="<?= $current_page === 'contact' ? 'active' : '' ?>">
            <a href="/pages/contact.php"><i class="fas fa-envelope"></i> Contact</a>
        </li>
        
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <li>
                    <a href="/admin/index.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
                </li>
            <?php endif; ?>
            <li>
                <a href="/my-bookings.php"><i class="fas fa-list"></i> My Bookings</a>
            </li>
            <li>
                <a href="/profile.php"><i class="fas fa-user"></i> Profile</a>
            </li>
            <li>
                <a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        <?php else: ?>
            <li>
                <a href="/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            </li>
            <li>
                <a href="/register.php"><i class="fas fa-user-plus"></i> Register</a>
            </li>
        <?php endif; ?>
    </ul>
</div>
