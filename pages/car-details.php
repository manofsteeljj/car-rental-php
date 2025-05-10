<?php
// Initialize database connection if not already done
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
}

// Get car ID from URL
$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no car ID is provided
if ($car_id <= 0) {
    header("Location: /pages/cars.php");
    exit();
}

// Get car details
$query = "
    SELECT c.*, ct.name AS category_name, ct.description AS category_description
    FROM cars c
    JOIN categories ct ON c.category_id = ct.id
    WHERE c.id = :car_id
    LIMIT 1
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$stmt->execute();
$car = $stmt->fetch();

// Redirect if car not found
if (!$car) {
    header("Location: /pages/cars.php");
    exit();
}

// Get related cars (same category, excluding current car)
$relatedQuery = "
    SELECT c.*, ct.name AS category_name
    FROM cars c
    JOIN categories ct ON c.category_id = ct.id
    WHERE c.category_id = :category_id AND c.id != :car_id
    ORDER BY RAND()
    LIMIT 3
";

$relatedStmt = $pdo->prepare($relatedQuery);
$relatedStmt->bindValue(':category_id', $car['category_id'], PDO::PARAM_INT);
$relatedStmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
$relatedStmt->execute();
$relatedCars = $relatedStmt->fetchAll();

// Include header if not already included
if (!defined('HEADER_INCLUDED')) {
    include_once __DIR__ . '/../includes/header.php';
    define('HEADER_INCLUDED', true);
}
?>

<!-- Breadcrumb navigation -->
<div class="breadcrumb">
    <div class="container">
        <a href="/index.php">Home</a>
        <i class="fas fa-chevron-right"></i>
        <a href="/pages/cars.php">Cars</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></span>
    </div>
</div>

<!-- Car details section -->
<section class="car-details-section">
    <div class="container">
        <div class="car-details-grid">
            <div class="car-image-gallery">
                <div class="main-image">
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>">
                    <div class="availability-badge <?= isCarAvailableNow($car['id']) ? 'available' : 'unavailable' ?>">
                        <?= isCarAvailableNow($car['id']) ? 'Available Now' : 'Currently Unavailable' ?>
                    </div>
                </div>
            </div>
            
            <div class="car-info">
                <h1><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></h1>
                <div class="car-meta">
                    <span class="car-category"><i class="fas fa-tag"></i> <?= htmlspecialchars($car['category_name']) ?></span>
                    <span class="car-year"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($car['year']) ?></span>
                </div>
                
                <div class="car-price-box">
                    <div class="price-label">Daily Rate</div>
                    <div class="price-amount"><?= formatPrice($car['daily_rate']) ?></div>
                    <div class="price-period">per day</div>
                </div>
                
                <div class="car-description">
                    <p><?= nl2br(htmlspecialchars($car['description'])) ?></p>
                </div>
                
                <div class="car-booking-box">
                    <h3>Ready to Book This Car?</h3>
                    <a href="/pages/booking.php?car_id=<?= $car['id'] ?>" class="btn btn-primary btn-block">Book Now</a>
                </div>
            </div>
        </div>
        
        <!-- Car specifications -->
        <div class="car-specifications">
            <h2>Specifications</h2>
            <div class="specs-grid">
                <div class="spec-item">
                    <i class="fas fa-users"></i>
                    <h4>Passengers</h4>
                    <p><?= htmlspecialchars($car['passengers']) ?> people</p>
                </div>
                
                <div class="spec-item">
                    <i class="fas fa-suitcase"></i>
                    <h4>Luggage</h4>
                    <p><?= htmlspecialchars($car['luggage']) ?> bags</p>
                </div>
                
                <div class="spec-item">
                    <i class="fas fa-door-open"></i>
                    <h4>Doors</h4>
                    <p><?= htmlspecialchars($car['doors']) ?></p>
                </div>
                
                <div class="spec-item">
                    <i class="fas fa-cog"></i>
                    <h4>Transmission</h4>
                    <p><?= htmlspecialchars($car['transmission']) ?></p>
                </div>
                
                <div class="spec-item">
                    <i class="fas fa-gas-pump"></i>
                    <h4>Fuel Type</h4>
                    <p><?= htmlspecialchars($car['fuel_type']) ?></p>
                </div>
                
                <div class="spec-item">
                    <i class="fas fa-snowflake"></i>
                    <h4>Air Conditioning</h4>
                    <p><?= $car['air_conditioning'] ? 'Yes' : 'No' ?></p>
                </div>
            </div>
        </div>
        
        <!-- Car features -->
        <div class="car-features">
            <h2>Features</h2>
            <div class="features-list">
                <?php
                $features = explode(',', $car['features']);
                foreach ($features as $feature):
                    if (trim($feature)):
                ?>
                    <div class="feature-item">
                        <i class="fas fa-check"></i>
                        <span><?= htmlspecialchars(trim($feature)) ?></span>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>
        
        <!-- Booking calendar will go here (simplified for this example) -->
        <div class="booking-calendar">
            <h2>Availability Calendar</h2>
            <p>Please use our booking form to check availability for your specific dates.</p>
            <a href="/pages/booking.php?car_id=<?= $car['id'] ?>" class="btn btn-secondary">Check Availability</a>
        </div>
    </div>
</section>

<!-- Related cars section -->
<?php if (!empty($relatedCars)): ?>
<section class="related-cars">
    <div class="container">
        <div class="section-header">
            <h2>Similar Cars You Might Like</h2>
            <p>Explore other options in the <?= htmlspecialchars($car['category_name']) ?> category</p>
        </div>
        
        <div class="cars-grid">
            <?php foreach($relatedCars as $relatedCar): ?>
                <div class="car-card">
                    <div class="car-image">
                        <div class="availability-badge <?= isCarAvailableNow($relatedCar['id']) ? 'available' : 'unavailable' ?>">
                            <?= isCarAvailableNow($relatedCar['id']) ? 'Available' : 'Unavailable' ?>
                        </div>
                        <img src="<?= htmlspecialchars($relatedCar['image_url']) ?>" alt="<?= htmlspecialchars($relatedCar['make'] . ' ' . $relatedCar['model']) ?>">
                    </div>
                    <div class="car-details">
                        <h3><?= htmlspecialchars($relatedCar['make'] . ' ' . $relatedCar['model']) ?></h3>
                        <p class="car-category"><i class="fas fa-tag"></i> <?= htmlspecialchars($relatedCar['category_name']) ?></p>
                        <div class="car-features">
                            <span><i class="fas fa-user"></i> <?= $relatedCar['passengers'] ?> Seats</span>
                            <span><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($relatedCar['fuel_type']) ?></span>
                            <span><i class="fas fa-cog"></i> <?= htmlspecialchars($relatedCar['transmission']) ?></span>
                        </div>
                        <div class="car-price">
                            <span class="price"><?= formatPrice($relatedCar['daily_rate']) ?></span>
                            <span class="period">per day</span>
                        </div>
                        <div class="car-actions">
                            <a href="/pages/car-details.php?id=<?= $relatedCar['id'] ?>" class="btn btn-outline">View Details</a>
                            <a href="/pages/booking.php?car_id=<?= $relatedCar['id'] ?>" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Ready for Your Journey?</h2>
            <p>Book this <?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?> now and hit the road in style!</p>
            <a href="/pages/booking.php?car_id=<?= $car['id'] ?>" class="btn btn-light">Book Now</a>
        </div>
    </div>
</section>

<?php
// Include footer if this page is loaded directly
if (!defined('FOOTER_INCLUDED')) {
    include_once __DIR__ . '/../includes/footer.php';
    define('FOOTER_INCLUDED', true);
}
?>
