<?php
// Initialize database connection if not already done
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
}

// Get all categories for filter
$categories = getCategories();

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Handle filtering
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sortBy = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name_asc';

// Build the WHERE clause for filtering
$whereClause = '';
$params = [];

if ($categoryFilter > 0) {
    $whereClause = 'WHERE c.category_id = :category_id';
    $params[':category_id'] = $categoryFilter;
}

// Set up the ORDER BY clause
switch ($sortBy) {
    case 'price_asc':
        $orderBy = 'c.daily_rate ASC';
        break;
    case 'price_desc':
        $orderBy = 'c.daily_rate DESC';
        break;
    case 'name_desc':
        $orderBy = 'c.make DESC, c.model DESC';
        break;
    case 'name_asc':
    default:
        $orderBy = 'c.make ASC, c.model ASC';
        break;
}

// Count total number of cars for pagination
$countQuery = "SELECT COUNT(*) FROM cars c $whereClause";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalCars = $countStmt->fetchColumn();
$totalPages = ceil($totalCars / $perPage);

// Get cars with pagination and filtering
$query = "
    SELECT c.*, ct.name AS category_name
    FROM cars c
    JOIN categories ct ON c.category_id = ct.id
    $whereClause
    ORDER BY $orderBy
    LIMIT :offset, :per_page
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);

// Bind category filter parameter if exists
if ($categoryFilter > 0) {
    $stmt->bindValue(':category_id', $categoryFilter, PDO::PARAM_INT);
}

$stmt->execute();
$cars = $stmt->fetchAll();

// Include header if not already included
if (!defined('HEADER_INCLUDED')) {
    include_once __DIR__ . '/../includes/header.php';
    define('HEADER_INCLUDED', true);
}
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>Our Car Fleet</h1>
        <p>Find the perfect vehicle for your journey</p>
    </div>
</div>

<!-- Car listing section -->
<section class="car-listing">
    <div class="container">
        <div class="filter-section">
            <form action="/pages/cars.php" method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" onchange="this.form.submit()">
                        <option value="0">All Categories</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort">Sort By:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="name_asc" <?= $sortBy == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="name_desc" <?= $sortBy == 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                        <option value="price_asc" <?= $sortBy == 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                        <option value="price_desc" <?= $sortBy == 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-outline">Apply Filters</button>
                <a href="/pages/cars.php" class="btn btn-text">Clear Filters</a>
            </form>
        </div>
        
        <?php if(empty($cars)): ?>
            <div class="empty-state">
                <i class="fas fa-car-side"></i>
                <h3>No cars found</h3>
                <p>Try adjusting your filters or check back later for new additions to our fleet.</p>
            </div>
        <?php else: ?>
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
                                <a href="/pages/booking.php?car_id=<?= $car['id'] ?>" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $sortBy ? '&sort=' . $sortBy : '' ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-link current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $sortBy ? '&sort=' . $sortBy : '' ?>" class="pagination-link">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $sortBy ? '&sort=' . $sortBy : '' ?>" class="pagination-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Not finding what you need section -->
<section class="not-finding">
    <div class="container">
        <div class="not-finding-content">
            <h2>Not Finding What You Need?</h2>
            <p>Contact our customer service team and we'll help you find the perfect vehicle for your requirements.</p>
            <a href="/pages/contact.php" class="btn btn-secondary">Contact Us</a>
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
