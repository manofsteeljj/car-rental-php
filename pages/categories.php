<?php
// Initialize database connection if not already done
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
}

// Check if a specific category was requested
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get all categories
$categories = getCategories();

// If a specific category is requested, get its details and cars
$categoryDetails = null;
$categoryCars = [];

if ($category_id > 0) {
    // Get category details
    $categoryQuery = "SELECT * FROM categories WHERE id = :category_id LIMIT 1";
    $categoryStmt = $pdo->prepare($categoryQuery);
    $categoryStmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
    $categoryStmt->execute();
    $categoryDetails = $categoryStmt->fetch();
    
    if ($categoryDetails) {
        // Get cars for this category
        $carsQuery = "
            SELECT c.*, ct.name AS category_name
            FROM cars c
            JOIN categories ct ON c.category_id = ct.id
            WHERE c.category_id = :category_id
            ORDER BY c.make, c.model
        ";
        $carsStmt = $pdo->prepare($carsQuery);
        $carsStmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $carsStmt->execute();
        $categoryCars = $carsStmt->fetchAll();
    }
}

// Include header if not already included
if (!defined('HEADER_INCLUDED')) {
    include_once __DIR__ . '/../includes/header.php';
    define('HEADER_INCLUDED', true);
}
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <?php if ($categoryDetails): ?>
            <h1><?= htmlspecialchars($categoryDetails['name']) ?> Cars</h1>
            <p><?= htmlspecialchars($categoryDetails['description']) ?></p>
        <?php else: ?>
            <h1>Car Categories</h1>
            <p>Browse our vehicles by category to find your perfect match</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($categoryDetails): ?>
<!-- Category details view -->
<section class="category-details">
    <div class="container">
        <div class="breadcrumb">
            <a href="/index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="/pages/categories.php">Categories</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($categoryDetails['name']) ?></span>
        </div>
        
        <?php if (empty($categoryCars)): ?>
            <div class="empty-state">
                <i class="fas fa-car-side"></i>
                <h3>No cars found in this category</h3>
                <p>We're currently updating our inventory. Please check back later or explore other categories.</p>
                <a href="/pages/categories.php" class="btn btn-primary">View All Categories</a>
            </div>
        <?php else: ?>
            <div class="cars-grid">
                <?php foreach($categoryCars as $car): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <div class="availability-badge <?= isCarAvailableNow($car['id']) ? 'available' : 'unavailable' ?>">
                                <?= isCarAvailableNow($car['id']) ? 'Available' : 'Unavailable' ?>
                            </div>
                            <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>">
                        </div>
                        <div class="car-details">
                            <h3><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></h3>
                            <p class="car-category"><i class="fas fa-tag"></i> <?= htmlspecialchars($car['category_name']) ?></p>
                            <div class="car-features">
                                <span><i class="fas fa-user"></i> <?= $car['passengers'] ?> Seats</span>
                                <span><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($car['fuel_type']) ?></span>
                                <span><i class="fas fa-cog"></i> <?= htmlspecialchars($car['transmission']) ?></span>
                            </div>
                            <div class="car-price">
                                <span class="price"><?= formatPrice($car['daily_rate']) ?></span>
                                <span class="period">per day</span>
                            </div>
                            <div class="car-actions">
                                <a href="/pages/car-details.php?id=<?= $car['id'] ?>" class="btn btn-outline">View Details</a>
                                <a href="/pages/booking.php?car_id=<?= $car['id'] ?>" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="back-to-categories">
                <a href="/pages/categories.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to All Categories
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php else: ?>
<!-- Categories list view -->
<section class="categories-list">
    <div class="container">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>No categories available</h3>
                <p>We're currently updating our categories. Please check back later.</p>
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="<?= htmlspecialchars($category['icon'] ?: 'fas fa-car') ?>"></i>
                        </div>
                        <div class="category-content">
                            <h2><?= htmlspecialchars($category['name']) ?></h2>
                            <p><?= htmlspecialchars($category['description']) ?></p>
                            
                            <?php
                            // Get count of cars in this category
                            $countQuery = "SELECT COUNT(*) FROM cars WHERE category_id = :category_id";
                            $countStmt = $pdo->prepare($countQuery);
                            $countStmt->bindValue(':category_id', $category['id'], PDO::PARAM_INT);
                            $countStmt->execute();
                            $carCount = $countStmt->fetchColumn();
                            ?>
                            
                            <div class="car-count">
                                <span><?= $carCount ?> <?= $carCount == 1 ? 'car' : 'cars' ?> available</span>
                            </div>
                            
                            <a href="/pages/categories.php?id=<?= $category['id'] ?>" class="btn btn-outline">
                                View Cars <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- CTA section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Find Your Perfect Ride</h2>
            <p>Browse our complete selection of vehicles and book your next adventure today.</p>
            <a href="/pages/cars.php" class="btn btn-light">View All Cars</a>
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
