<?php
// Initialize database connection if not already done
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
}

// Get search parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$pickup_date = isset($_GET['pickup_date']) ? sanitize($_GET['pickup_date']) : '';
$return_date = isset($_GET['return_date']) ? sanitize($_GET['return_date']) : '';
$search_term = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Initialize variables
$cars = [];
$errorMessage = "";
$hasValidDates = !empty($pickup_date) && !empty($return_date);

// Validate dates if provided
if ($hasValidDates) {
    $pickup = new DateTime($pickup_date);
    $return = new DateTime($return_date);
    $today = new DateTime();
    
    if ($pickup < $today) {
        $errorMessage = "Pickup date cannot be in the past.";
        $hasValidDates = false;
    }
    
    if ($return <= $pickup) {
        $errorMessage = "Return date must be after pickup date.";
        $hasValidDates = false;
    }
}

// Build query based on search parameters
if (empty($errorMessage)) {
    // Start building the query
    $query = "
        SELECT c.*, ct.name AS category_name
        FROM cars c
        JOIN categories ct ON c.category_id = ct.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Add category filter if provided
    if ($category_id > 0) {
        $query .= " AND c.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    // Add search term filter if provided
    if (!empty($search_term)) {
        $query .= " AND (
            c.make LIKE :search_term OR 
            c.model LIKE :search_term OR 
            ct.name LIKE :search_term OR
            CONCAT(c.make, ' ', c.model) LIKE :search_term
        )";
        $params[':search_term'] = "%$search_term%";
    }
    
    // Add order by clause
    $query .= " ORDER BY c.make, c.model";
    
    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();
    
    // Filter cars based on availability if valid dates are provided
    if ($hasValidDates && !empty($cars)) {
        $available_cars = [];
        
        foreach ($cars as $car) {
            if (isCarAvailable($car['id'], $pickup_date, $return_date)) {
                $available_cars[] = $car;
            }
        }
        
        $cars = $available_cars;
    }
}

// Get categories for filter
$categories = getCategories();

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>Search Results</h1>
        <p>Find the perfect car for your journey</p>
    </div>
</div>

<!-- Search section -->
<section class="search-section">
    <div class="container">
        <!-- Search form -->
        <div class="search-box">
            <form action="/pages/search.php" method="GET" class="search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" placeholder="Search by make, model, or category" 
                               value="<?= htmlspecialchars($search_term) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pickup_date">Pickup Date</label>
                        <input type="date" name="pickup_date" id="pickup_date" min="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($pickup_date) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="return_date">Return Date</label>
                        <input type="date" name="return_date" id="return_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                               value="<?= htmlspecialchars($return_date) ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="/pages/search.php" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Search results -->
        <div class="search-results">
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error">
                    <?= $errorMessage ?>
                </div>
            <?php elseif (empty($cars)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No cars found</h3>
                    <p>No cars match your search criteria. Try adjusting your filters or browse our complete car inventory.</p>
                    <a href="/pages/cars.php" class="btn btn-primary">View All Cars</a>
                </div>
            <?php else: ?>
                <div class="results-heading">
                    <h2>
                        <?= count($cars) ?> 
                        <?= count($cars) == 1 ? 'car' : 'cars' ?> 
                        <?= $hasValidDates ? 'available' : 'found' ?>
                    </h2>
                    
                    <?php if ($hasValidDates): ?>
                        <p>
                            From <?= formatDate($pickup_date) ?> to <?= formatDate($return_date) ?>
                            (<?= (new DateTime($return_date))->diff(new DateTime($pickup_date))->days + 1 ?> days)
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="cars-grid">
                    <?php foreach($cars as $car): ?>
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
                                    <a href="/pages/booking.php?car_id=<?= $car['id'] ?><?= $hasValidDates ? '&pickup_date=' . urlencode($pickup_date) . '&return_date=' . urlencode($return_date) : '' ?>" class="btn btn-primary">Book Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Car categories section -->
<section class="featured-categories">
    <div class="container">
        <div class="section-header">
            <h2>Browse by Category</h2>
            <p>Explore our diverse range of vehicles</p>
        </div>
        
        <div class="categories-grid small">
            <?php foreach(array_slice($categories, 0, 4) as $category): ?>
                <a href="/pages/categories.php?id=<?= $category['id'] ?>" class="category-card">
                    <div class="category-icon">
                        <i class="<?= htmlspecialchars($category['icon'] ?: 'fas fa-car') ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="view-all-link">
            <a href="/pages/categories.php" class="btn btn-secondary">View All Categories</a>
        </div>
    </div>
</section>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>
