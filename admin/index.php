<?php
// Initialize database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
requireAdmin();

// Get dashboard statistics
$stats = [];

// Count total cars
$carQuery = "SELECT COUNT(*) FROM cars";
$carStmt = $pdo->query($carQuery);
$stats['totalCars'] = $carStmt->fetchColumn();

// Count total bookings
$bookingQuery = "SELECT COUNT(*) FROM bookings";
$bookingStmt = $pdo->query($bookingQuery);
$stats['totalBookings'] = $bookingStmt->fetchColumn();

// Count active bookings (current date falls between start and end date)
$activeBookingQuery = "SELECT COUNT(*) FROM bookings WHERE CURDATE() BETWEEN start_date AND end_date AND status = 'confirmed'";
$activeBookingStmt = $pdo->query($activeBookingQuery);
$stats['activeBookings'] = $activeBookingStmt->fetchColumn();

// Count pending bookings
$pendingBookingQuery = "SELECT COUNT(*) FROM bookings WHERE status = 'pending'";
$pendingBookingStmt = $pdo->query($pendingBookingQuery);
$stats['pendingBookings'] = $pendingBookingStmt->fetchColumn();

// Count cars by category
$categoryStatsQuery = "
    SELECT ct.name, COUNT(c.id) as count
    FROM categories ct
    LEFT JOIN cars c ON ct.id = c.category_id
    GROUP BY ct.id
    ORDER BY count DESC
";
$categoryStatsStmt = $pdo->query($categoryStatsQuery);
$stats['carsByCategory'] = $categoryStatsStmt->fetchAll();

// Get recent bookings
$recentBookingsQuery = "
    SELECT b.*, c.make, c.model
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    ORDER BY b.created_at DESC
    LIMIT 5
";
$recentBookingsStmt = $pdo->query($recentBookingsQuery);
$recentBookings = $recentBookingsStmt->fetchAll();

// Include admin header
include_once __DIR__ . '/includes/header.php';
?>

<!-- Dashboard content -->
<div class="dashboard-container">
    <!-- Page header -->
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>! Here's your rental overview.</p>
    </div>
    
    <!-- Stats cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-car"></i>
            </div>
            <div class="stat-content">
                <h3>Total Cars</h3>
                <div class="stat-value"><?= $stats['totalCars'] ?></div>
                <a href="/admin/cars.php" class="stat-link">View All Cars</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3>Total Bookings</h3>
                <div class="stat-value"><?= $stats['totalBookings'] ?></div>
                <a href="/admin/bookings.php" class="stat-link">View All Bookings</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Pending Bookings</h3>
                <div class="stat-value"><?= $stats['pendingBookings'] ?></div>
                <a href="/admin/bookings.php?status=pending" class="stat-link">View Pending</a>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-car-side"></i>
            </div>
            <div class="stat-content">
                <h3>Active Rentals</h3>
                <div class="stat-value"><?= $stats['activeBookings'] ?></div>
                <a href="/admin/bookings.php?status=active" class="stat-link">View Active</a>
            </div>
        </div>
    </div>
    
    <!-- Dashboard widgets -->
    <div class="dashboard-grid">
        <!-- Recent bookings widget -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h2>Recent Bookings</h2>
                <a href="/admin/bookings.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            
            <div class="widget-content">
                <?php if (empty($recentBookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <p>No recent bookings found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Car</th>
                                    <th>Customer</th>
                                    <th>Dates</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($booking['reference_number']) ?></strong></td>
                                        <td><?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?></td>
                                        <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                                        <td>
                                            <?= formatDate($booking['start_date']) ?> 
                                            <span class="date-separator">to</span> 
                                            <?= formatDate($booking['end_date']) ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= strtolower($booking['status']) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/admin/booking-details.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Cars by category widget -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h2>Cars by Category</h2>
                <a href="/admin/categories.php" class="btn btn-sm btn-outline">Manage Categories</a>
            </div>
            
            <div class="widget-content">
                <?php if (empty($stats['carsByCategory'])): ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <p>No categories found.</p>
                    </div>
                <?php else: ?>
                    <div class="category-stats">
                        <?php foreach ($stats['carsByCategory'] as $category): ?>
                            <div class="category-stat-item">
                                <div class="category-name"><?= htmlspecialchars($category['name']) ?></div>
                                <div class="category-progress">
                                    <div class="progress-bar" style="width: <?= ($category['count'] / $stats['totalCars']) * 100 ?>%"></div>
                                </div>
                                <div class="category-count"><?= $category['count'] ?> cars</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick actions -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="/admin/add-car.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Car
            </a>
            <a href="/admin/categories.php" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <a href="/admin/bookings.php?status=pending" class="btn btn-warning">
                <i class="fas fa-clock"></i> Pending Bookings
            </a>
            <a href="/index.php" target="_blank" class="btn btn-outline">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
        </div>
    </div>
</div>

<?php
// Include admin footer
include_once __DIR__ . '/includes/footer.php';
?>
