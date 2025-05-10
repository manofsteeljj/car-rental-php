<?php
// Initialize database connection if not already done
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
}

// Check if booking reference is in session
if (!isset($_SESSION['booking_reference']) || !isset($_SESSION['booking_email'])) {
    header("Location: /pages/booking.php");
    exit();
}

$reference = $_SESSION['booking_reference'];
$email = $_SESSION['booking_email'];

// Get booking details
$query = "
    SELECT b.*, c.make, c.model, c.image_url, c.daily_rate
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.reference_number = :reference AND b.customer_email = :email
    LIMIT 1
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':reference' => $reference,
    ':email' => $email
]);
$booking = $stmt->fetch();

// If booking not found, redirect to booking page
if (!$booking) {
    header("Location: /pages/booking.php");
    exit();
}

// Calculate rental duration
$start = new DateTime($booking['start_date']);
$end = new DateTime($booking['end_date']);
$duration = $end->diff($start)->days + 1; // Include last day

// Clear booking session data after retrieving it
unset($_SESSION['booking_reference']);
unset($_SESSION['booking_email']);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>Booking Confirmation</h1>
        <p>Your car rental has been successfully booked</p>
    </div>
</div>

<!-- Confirmation section -->
<section class="confirmation-section">
    <div class="container">
        <div class="confirmation-card">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Thank You for Your Booking!</h2>
                <p>Your reservation has been received and is now being processed.</p>
            </div>
            
            <div class="confirmation-details">
                <div class="confirmation-info">
                    <div class="info-group">
                        <span class="info-label">Booking Reference:</span>
                        <span class="info-value"><?= htmlspecialchars($booking['reference_number']) ?></span>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Status:</span>
                        <span class="info-value status-badge <?= strtolower($booking['status']) ?>"><?= ucfirst($booking['status']) ?></span>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="car-booking-details">
                    <div class="car-image">
                        <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="<?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>">
                    </div>
                    <div class="car-info">
                        <h3><?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?></h3>
                        <div class="rental-period">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= formatDate($booking['start_date']) ?> to <?= formatDate($booking['end_date']) ?> (<?= $duration ?> days)</span>
                        </div>
                        <div class="price-breakdown">
                            <div class="price-item">
                                <span>Daily Rate:</span>
                                <span><?= formatPrice($booking['daily_rate']) ?></span>
                            </div>
                            <div class="price-item">
                                <span>Duration:</span>
                                <span><?= $duration ?> days</span>
                            </div>
                            <div class="price-item total">
                                <span>Total Price:</span>
                                <span><?= formatPrice($booking['total_price']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="customer-details">
                    <h3>Customer Information</h3>
                    <div class="info-row">
                        <div class="info-group">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['customer_name']) ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['customer_email']) ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-group">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['customer_phone']) ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">Booking Date:</span>
                            <span class="info-value"><?= formatDate($booking['created_at']) ?></span>
                        </div>
                    </div>
                    <?php if (!empty($booking['additional_requirements'])): ?>
                        <div class="info-group full">
                            <span class="info-label">Additional Requirements:</span>
                            <span class="info-value"><?= nl2br(htmlspecialchars($booking['additional_requirements'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="confirmation-footer">
                <h3>What's Next?</h3>
                <ul class="next-steps">
                    <li>
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Check Your Email</h4>
                            <p>We've sent a confirmation email to <?= htmlspecialchars($booking['customer_email']) ?> with all your booking details.</p>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>Booking Confirmation</h4>
                            <p>Our team will review your booking and confirm it shortly. You'll receive an email notification once confirmed.</p>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-car"></i>
                        <div>
                            <h4>Pickup Information</h4>
                            <p>Once confirmed, you'll receive pickup instructions and location details. Don't forget to bring your driver's license and credit card.</p>
                        </div>
                    </li>
                </ul>
                
                <div class="confirmation-actions">
                    <a href="/index.php" class="btn btn-primary">Back to Home</a>
                    <a href="/pages/cars.php" class="btn btn-outline">Browse More Cars</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .confirmation-section {
        padding: 3rem 0;
    }
    
    .confirmation-card {
        background-color: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .confirmation-header {
        background-color: #f8f9fa;
        padding: 2rem;
        text-align: center;
        border-bottom: 1px solid var(--border-color);
    }
    
    .confirmation-icon {
        font-size: 3rem;
        color: var(--success-color);
        margin-bottom: 1rem;
    }
    
    .confirmation-details {
        padding: 2rem;
    }
    
    .confirmation-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    
    .info-group {
        margin-bottom: 1rem;
    }
    
    .info-label {
        font-weight: 500;
        color: var(--text-light);
        display: block;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-weight: 600;
    }
    
    .divider {
        height: 1px;
        background-color: var(--border-color);
        margin: 1.5rem 0;
    }
    
    .car-booking-details {
        display: flex;
        gap: 1.5rem;
    }
    
    .car-image {
        flex: 0 0 250px;
        height: 150px;
        border-radius: var(--radius);
        overflow: hidden;
    }
    
    .car-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .car-info {
        flex: 1;
    }
    
    .car-info h3 {
        margin-bottom: 0.75rem;
    }
    
    .rental-period {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        color: var(--text-light);
    }
    
    .price-breakdown {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: var(--radius);
    }
    
    .price-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    
    .price-item.total {
        font-weight: 700;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px solid var(--border-color);
    }
    
    .customer-details h3 {
        margin-bottom: 1rem;
    }
    
    .info-row {
        display: flex;
        gap: 2rem;
    }
    
    .info-group.full {
        margin-top: 1rem;
    }
    
    .confirmation-footer {
        background-color: #f8f9fa;
        padding: 2rem;
        border-top: 1px solid var(--border-color);
    }
    
    .next-steps {
        margin: 1.5rem 0;
    }
    
    .next-steps li {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    
    .next-steps li i {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin-top: 0.25rem;
    }
    
    .next-steps h4 {
        margin-bottom: 0.25rem;
    }
    
    .next-steps p {
        color: var(--text-light);
        font-size: 0.95rem;
    }
    
    .confirmation-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
        .confirmation-info,
        .info-row {
            flex-direction: column;
        }
        
        .car-booking-details {
            flex-direction: column;
        }
        
        .car-image {
            flex: 0 0 auto;
            height: 200px;
        }
    }
</style>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>
