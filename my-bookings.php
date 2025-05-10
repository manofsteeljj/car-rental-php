<?php
// Initialize database connection and functions
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Check if user is logged in
requireLogin();

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    
    if ($booking_id > 0) {
        try {
            // Check if booking belongs to the current user
            $checkQuery = "
                SELECT id, status, start_date 
                FROM bookings 
                WHERE id = :id AND (customer_email = :email OR user_id = :user_id)
            ";
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->execute([
                ':id' => $booking_id,
                ':email' => $_SESSION['user_email'],
                ':user_id' => $_SESSION['user_id']
            ]);
            $booking = $checkStmt->fetch();
            
            if ($booking) {
                // Check if booking is eligible for cancellation
                // Can't cancel completed bookings or bookings that have already started
                if ($booking['status'] === 'completed') {
                    $_SESSION['error'] = "Completed bookings cannot be cancelled.";
                } elseif ($booking['status'] === 'cancelled') {
                    $_SESSION['error'] = "This booking is already cancelled.";
                } elseif (strtotime($booking['start_date']) <= time()) {
                    $_SESSION['error'] = "Bookings that have already started cannot be cancelled online. Please contact customer support.";
                } else {
                    // Update booking status to cancelled
                    $updateQuery = "UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = :id";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateStmt->execute([':id' => $booking_id]);
                    
                    $_SESSION['success'] = "Your booking has been successfully cancelled.";
                }
            } else {
                $_SESSION['error'] = "Booking not found or you don't have permission to cancel it.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "An error occurred while cancelling your booking. Please try again.";
            error_log("Booking cancellation error: " . $e->getMessage());
        }
    }
    
    // Redirect to refresh the page
    header("Location: /my-bookings.php");
    exit();
}

// Get user's bookings
$query = "
    SELECT b.*, c.make, c.model, c.image_url
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.customer_email = :email OR b.user_id = :user_id
    ORDER BY b.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':email' => $_SESSION['user_email'],
    ':user_id' => $_SESSION['user_id']
]);
$bookings = $stmt->fetchAll();

// Include header
include_once __DIR__ . '/includes/header.php';
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>My Bookings</h1>
        <p>View and manage your car rental bookings</p>
    </div>
</div>

<!-- Bookings section -->
<section class="my-bookings-section">
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <h3>No Bookings Found</h3>
                <p>You haven't made any car rental bookings yet. Start exploring our cars and make your first booking!</p>
                <a href="/pages/cars.php" class="btn btn-primary">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="bookings-container">
                <div class="booking-tabs">
                    <button class="tab-btn active" data-status="all">All Bookings</button>
                    <button class="tab-btn" data-status="upcoming">Upcoming</button>
                    <button class="tab-btn" data-status="active">Active</button>
                    <button class="tab-btn" data-status="completed">Completed</button>
                    <button class="tab-btn" data-status="cancelled">Cancelled</button>
                </div>
                
                <div class="bookings-list">
                    <?php foreach($bookings as $booking): ?>
                        <?php
                        // Determine booking category for filtering
                        $today = date('Y-m-d');
                        $isActive = ($booking['start_date'] <= $today && $booking['end_date'] >= $today && $booking['status'] === 'confirmed');
                        $isUpcoming = ($booking['start_date'] > $today && ($booking['status'] === 'confirmed' || $booking['status'] === 'pending'));
                        $status = $isActive ? 'active' : ($isUpcoming ? 'upcoming' : strtolower($booking['status']));
                        ?>
                        <div class="booking-card" data-status="<?= $status ?>">
                            <div class="booking-header">
                                <div class="booking-reference">
                                    <span class="label">Booking Reference:</span>
                                    <span class="value"><?= htmlspecialchars($booking['reference_number']) ?></span>
                                </div>
                                <div class="booking-status">
                                    <span class="status-badge <?= strtolower($booking['status']) ?>">
                                        <?= $isActive ? 'Active' : ($isUpcoming ? 'Upcoming' : ucfirst($booking['status'])) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="booking-info">
                                <div class="booking-car">
                                    <div class="booking-car-image">
                                        <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="<?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>">
                                    </div>
                                    <div class="booking-car-details">
                                        <h3><?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?></h3>
                                        <div class="booking-price">
                                            <span class="price"><?= formatPrice($booking['total_price']) ?></span>
                                            <span class="price-note">total</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-dates">
                                    <div class="date-range">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= formatDate($booking['start_date']) ?> <span class="date-separator">to</span> <?= formatDate($booking['end_date']) ?></span>
                                    </div>
                                    <div class="booking-date">
                                        <span class="label">Booked on:</span>
                                        <span class="value"><?= formatDate($booking['created_at']) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <a href="/pages/booking-details.php?ref=<?= urlencode($booking['reference_number']) ?>" class="btn btn-outline">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                
                                <?php if ($isUpcoming): ?>
                                    <form method="POST" action="/my-bookings.php" class="cancel-form" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <button type="submit" name="cancel_booking" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Cancel Booking
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($booking['status'] === 'completed'): ?>
                                    <a href="/pages/write-review.php?booking=<?= $booking['id'] ?>" class="btn btn-secondary">
                                        <i class="fas fa-star"></i> Write Review
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($isUpcoming && $booking['status'] === 'confirmed'): ?>
                                    <a href="/pages/modify-booking.php?ref=<?= urlencode($booking['reference_number']) ?>" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Modify
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .my-bookings-section {
        padding: 3rem 0;
    }
    
    .bookings-container {
        background-color: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    
    .booking-tabs {
        display: flex;
        overflow-x: auto;
        border-bottom: 1px solid var(--border-color);
    }
    
    .tab-btn {
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 500;
        color: var(--text-light);
        position: relative;
        white-space: nowrap;
    }
    
    .tab-btn.active {
        color: var(--primary-color);
    }
    
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--primary-color);
    }
    
    .bookings-list {
        padding: 1.5rem;
    }
    
    .booking-card {
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    
    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid var(--border-color);
    }
    
    .booking-reference .label {
        font-weight: 500;
        margin-right: 0.5rem;
    }
    
    .booking-reference .value {
        font-weight: 600;
    }
    
    .booking-info {
        padding: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
    }
    
    .booking-car {
        display: flex;
        gap: 1.5rem;
        flex: 1;
    }
    
    .booking-car-image {
        width: 120px;
        height: 80px;
        border-radius: var(--radius);
        overflow: hidden;
    }
    
    .booking-car-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .booking-car-details h3 {
        margin-bottom: 0.5rem;
    }
    
    .booking-price {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
    }
    
    .booking-price .price {
        font-weight: 700;
        color: var(--primary-color);
    }
    
    .booking-price .price-note {
        font-size: 0.85rem;
        color: var(--text-light);
    }
    
    .booking-dates {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .date-range {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .date-range i {
        color: var(--primary-color);
    }
    
    .booking-date {
        font-size: 0.9rem;
        color: var(--text-light);
    }
    
    .booking-date .label {
        margin-right: 0.25rem;
    }
    
    .booking-actions {
        display: flex;
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-color);
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .btn-danger {
        background-color: var(--danger-color);
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #c0392b;
    }
    
    @media (max-width: 768px) {
        .booking-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .booking-info {
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .booking-actions {
            flex-direction: column;
        }
        
        .booking-actions .btn {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tabs functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const bookingCards = document.querySelectorAll('.booking-card');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs
                tabBtns.forEach(tab => tab.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Get selected status
                const status = this.getAttribute('data-status');
                
                // Show/hide booking cards based on status
                bookingCards.forEach(card => {
                    if (status === 'all' || card.getAttribute('data-status') === status) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
</script>

<?php
// Include footer
include_once __DIR__ . '/includes/footer.php';
?>
