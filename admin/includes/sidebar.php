<div class="admin-sidebar">
    <div class="sidebar-header">
        <div class="admin-logo">
            <span class="admin-logo-text">DriveEasy</span>
            <span class="admin-logo-subtext">Admin</span>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <div class="sidebar-title">Main</div>
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="/admin/index.php" class="sidebar-link <?= $current_page === 'index' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/admin/bookings.php" class="sidebar-link <?= $current_page === 'bookings' || $current_page === 'booking-details' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Bookings
                </a>
            </li>
        </ul>
        
        <div class="sidebar-divider"></div>
        
        <div class="sidebar-title">Inventory</div>
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="/admin/cars.php" class="sidebar-link <?= $current_page === 'cars' || $current_page === 'add-car' || $current_page === 'edit-car' ? 'active' : '' ?>">
                    <i class="fas fa-car"></i> Cars
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/admin/categories.php" class="sidebar-link <?= $current_page === 'categories' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> Categories
                </a>
            </li>
        </ul>
        
        <div class="sidebar-divider"></div>
        
        <div class="sidebar-title">Account</div>
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="/index.php" target="_blank" class="sidebar-link">
                    <i class="fas fa-external-link-alt"></i> View Website
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>
