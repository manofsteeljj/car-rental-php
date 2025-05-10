<?php
// Initialize database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
requireAdmin();

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no booking ID is provided
if ($booking_id <= 0) {
    header("Location: /admin/bookings.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
    
    if (!empty($new_status) && in_array($new_status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        try {
            $query = "UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':status' => $new_status,
                ':id' => $booking_id
            ]);
            
            $_SESSION['success'] = "Booking status updated successfully.";
            
            // Redirect to refresh the page
            header("Location: /admin/booking-details.php?id=$booking_id");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating booking status: " . $e->getMessage();
        }
    }
}

// Get booking details
$query = "
    SELECT b.*, c.make, c.model, c.image_url, c.daily_rate, ct.name AS category_name
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN categories ct ON c.category_id = ct.id
    WHERE b.id = :id
    LIMIT 1
";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':id', $booking_id, PDO::PARAM_INT);
$stmt->execute();
$booking = $stmt->fetch();

// Redirect if booking not found
if (!$booking) {
    $_SESSION['error'] = "Booking not found.";
    header("Location: /admin/bookings.php");
    exit();
}

// Calculate rental duration
$start = new DateTime($booking['start_date']);
$end = new DateTime($booking['end_date']);
$duration = $end->diff($start)->days + 1; // Include last day

// Include admin header
include_once __DIR__ . '/includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Booking Details</h1>
        <a href="/admin/bookings.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Bookings
        </a>
    </div>
    
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
    
    <div class="booking-details-grid">
        <!-- Booking information card -->
        <div class="booking-detail-card">
            <h3>Booking Information</h3>
            
            <div class="info-group">
                <div class="info-label">Reference Number</div>
                <div class="info-value"><?= htmlspecialchars($booking['reference_number']) ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-badge <?= strtolower($booking['status']) ?>">
                        <?= ucfirst($booking['status']) ?>
                    </span>
                </div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Booking Date</div>
                <div class="info-value"><?= formatDate($booking['created_at']) ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Rental Period</div>
                <div class="info-value">
                    <?= formatDate($booking['start_date']) ?> to <?= formatDate($booking['end_date']) ?>
                    (<?= $duration ?> days)
                </div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Total Price</div>
                <div class="info-value price"><?= formatPrice($booking['total_price']) ?></div>
            </div>
        </div>
        
        <!-- Customer information card -->
        <div class="booking-detail-card">
            <h3>Customer Information</h3>
            
            <div class="info-group">
                <div class="info-label">Name</div>
                <div class="info-value"><?= htmlspecialchars($booking['customer_name']) ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Email</div>
                <div class="info-value"><?= htmlspecialchars($booking['customer_email']) ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Phone</div>
                <div class="info-value"><?= htmlspecialchars($booking['customer_phone']) ?></div>
            </div>
            
            <?php if ($booking['user_id']): ?>
                <div class="info-group">
                    <div class="info-label">User ID</div>
                    <div class="info-value"><?= $booking['user_id'] ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($booking['additional_requirements'])): ?>
                <div class="info-group">
                    <div class="info-label">Additional Requirements</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($booking['additional_requirements'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Car details card -->
    <div class="booking-detail-card">
        <h3>Car Details</h3>
        
        <div class="car-details-flex">
            <div class="car-image">
                <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="<?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>">
            </div>
            
            <div class="car-info-grid">
                <div class="info-group">
                    <div class="info-label">Car</div>
                    <div class="info-value">
                        <?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Category</div>
                    <div class="info-value"><?= htmlspecialchars($booking['category_name']) ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Daily Rate</div>
                    <div class="info-value"><?= formatPrice($booking['daily_rate']) ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">View Car</div>
                    <div class="info-value">
                        <a href="/pages/car-details.php?id=<?= $booking['car_id'] ?>" target="_blank" class="btn btn-sm btn-outline">
                            <i class="fas fa-external-link-alt"></i> Open
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking timeline -->
    <div class="booking-detail-card">
        <h3>Booking Timeline</h3>
        
        <div class="booking-timeline">
            <div class="timeline-item">
                <div class="timeline-date"><?= formatDate($booking['created_at']) ?></div>
                <div class="timeline-title">Booking Created</div>
                <div class="timeline-description">
                    Customer placed a booking for <?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>
                </div>
            </div>
            
            <?php if ($booking['status'] !== 'pending'): ?>
                <div class="timeline-item">
                    <?php
                    // For demonstration, we'll use updated_at as the status change date
                    // In a real application, you would track each status change separately
                    $statusDate = !empty($booking['updated_at']) ? $booking['updated_at'] : $booking['created_at'];
                    ?>
                    <div class="timeline-date"><?= formatDate($statusDate) ?></div>
                    <div class="timeline-title">Status Updated to <?= ucfirst($booking['status']) ?></div>
                    <div class="timeline-description">
                        <?php if ($booking['status'] === 'confirmed'): ?>
                            Booking was confirmed and ready for pickup
                        <?php elseif ($booking['status'] === 'completed'): ?>
                            Rental was completed successfully
                        <?php elseif ($booking['status'] === 'cancelled'): ?>
                            Booking was cancelled
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($booking['status'] === 'confirmed' || $booking['status'] === 'completed'): ?>
                <div class="timeline-item">
                    <div class="timeline-date"><?= formatDate($booking['start_date']) ?></div>
                    <div class="timeline-title">Scheduled Pickup</div>
                    <div class="timeline-description">
                        Customer scheduled to pick up the vehicle
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date"><?= formatDate($booking['end_date']) ?></div>
                    <div class="timeline-title">Scheduled Return</div>
                    <div class="timeline-description">
                        Customer scheduled to return the vehicle
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Actions card -->
    <div class="booking-actions-card">
        <h3>Booking Actions</h3>
        
        <form action="/admin/booking-details.php?id=<?= $booking_id ?>" method="POST" class="status-update-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Update Status</label>
                    <select name="status" id="status" required>
                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </div>
        </form>
        
        <div class="action-buttons">
            <a href="mailto:<?= htmlspecialchars($booking['customer_email']) ?>" class="btn btn-outline">
                <i class="fas fa-envelope"></i> Email Customer
            </a>
            
            <a href="/admin/print-booking.php?id=<?= $booking_id ?>" target="_blank" class="btn btn-outline">
                <i class="fas fa-print"></i> Print Booking
            </a>
        </div>
    </div>
</div>

<style>
    .booking-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .booking-detail-card {
        background-color: white;
        border-radius: var(--admin-radius);
        box-shadow: var(--admin-shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .booking-detail-card h3 {
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--admin-border);
    }
    
    .info-group {
        margin-bottom: 1.25rem;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: var(--admin-text-light);
        margin-bottom: 0.35rem;
    }
    
    .info-value {
        font-weight: 500;
    }
    
    .info-value.price {
        color: var(--admin-primary);
        font-size: 1.1rem;
    }
    
    .car-details-flex {
        display: flex;
        gap: 1.5rem;
    }
    
    .car-image {
        flex: 0 0 200px;
        height: 150px;
        border-radius: var(--admin-radius);
        overflow: hidden;
    }
    
    .car-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .car-info-grid {
        flex: 1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }
    
    @media (max-width: 992px) {
        .booking-details-grid {
            grid-template-columns: 1fr;
        }
        
        .car-details-flex {
            flex-direction: column;
        }
        
        .car-image {
            flex: 0 0 auto;
            height: 200px;
        }
    }
</style>

<?php
// Include admin footer
include_once __DIR__ . '/includes/footer.php';
?>
