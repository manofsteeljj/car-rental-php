<?php
// Initialize database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
requireAdmin();

// Handle car deletion
if (isset($_POST['delete_car']) && isset($_POST['car_id'])) {
    $car_id = (int)$_POST['car_id'];
    
    try {
        // Check if car exists
        $checkStmt = $pdo->prepare("SELECT id FROM cars WHERE id = :id");
        $checkStmt->execute([':id' => $car_id]);
        
        if ($checkStmt->rowCount() > 0) {
            // Check if car has any bookings
            $bookingCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE car_id = :car_id");
            $bookingCheckStmt->execute([':car_id' => $car_id]);
            $hasBookings = $bookingCheckStmt->fetchColumn() > 0;
            
            if ($hasBookings) {
                $_SESSION['error'] = "Cannot delete car as it has associated bookings.";
            } else {
                // Delete the car
                $deleteStmt = $pdo->prepare("DELETE FROM cars WHERE id = :id");
                $deleteStmt->execute([':id' => $car_id]);
                
                $_SESSION['success'] = "Car deleted successfully.";
            }
        } else {
            $_SESSION['error'] = "Car not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting car: " . $e->getMessage();
    }
    
    // Redirect to refresh the page
    header("Location: /admin/cars.php");
    exit();
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Handle filtering
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$availabilityFilter = isset($_GET['availability']) ? sanitize($_GET['availability']) : '';

// Build query based on filters
$whereClause = [];
$params = [];

if ($categoryFilter > 0) {
    $whereClause[] = "c.category_id = :category_id";
    $params[':category_id'] = $categoryFilter;
}

if (!empty($searchTerm)) {
    $whereClause[] = "(c.make LIKE :search OR c.model LIKE :search OR CONCAT(c.make, ' ', c.model) LIKE :search)";
    $params[':search'] = "%$searchTerm%";
}

// Availability filter - this is simplified as a real system would need more complex logic
if ($availabilityFilter === 'available') {
    $whereClause[] = "c.id NOT IN (SELECT car_id FROM bookings WHERE CURDATE() BETWEEN start_date AND end_date AND status = 'confirmed')";
} elseif ($availabilityFilter === 'unavailable') {
    $whereClause[] = "c.id IN (SELECT car_id FROM bookings WHERE CURDATE() BETWEEN start_date AND end_date AND status = 'confirmed')";
}

$whereClauseStr = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";

// Count total cars for pagination
$countQuery = "
    SELECT COUNT(*) 
    FROM cars c
    $whereClauseStr
";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalCars = $countStmt->fetchColumn();
$totalPages = ceil($totalCars / $perPage);

// Get cars with pagination and filtering
$query = "
    SELECT c.*, ct.name AS category_name
    FROM cars c
    JOIN categories ct ON c.category_id = ct.id
    $whereClauseStr
    ORDER BY c.id DESC
    LIMIT :offset, :per_page
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);

// Bind other parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$cars = $stmt->fetchAll();

// Get all categories for filter
$categories = getCategories();

// Include admin header
include_once __DIR__ . '/includes/header.php';
?>

<!-- Cars management page content -->
<div class="admin-container">
    <div class="admin-header">
        <h1>Car Management</h1>
        <a href="/admin/add-car.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Car
        </a>
    </div>
    
    <!-- Filter section -->
    <div class="filter-card">
        <form action="/admin/cars.php" method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" placeholder="Search by make or model" value="<?= htmlspecialchars($searchTerm) ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category">
                        <option value="0">All Categories</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="availability">Availability</label>
                    <select name="availability" id="availability">
                        <option value="">All</option>
                        <option value="available" <?= $availabilityFilter === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="unavailable" <?= $availabilityFilter === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>
                
                <div class="form-group filter-actions">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/admin/cars.php" class="btn btn-outline">Reset</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Cars list -->
    <div class="data-card">
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
        
        <?php if (empty($cars)): ?>
            <div class="empty-state">
                <i class="fas fa-car"></i>
                <h3>No cars found</h3>
                <p>Try adjusting your filters or add a new car.</p>
                <a href="/admin/add-car.php" class="btn btn-primary">Add New Car</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Make & Model</th>
                            <th>Category</th>
                            <th>Year</th>
                            <th>Price</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= $car['id'] ?></td>
                                <td class="car-thumbnail">
                                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($car['category_name']) ?></td>
                                <td><?= htmlspecialchars($car['year']) ?></td>
                                <td><?= formatPrice($car['daily_rate']) ?>/day</td>
                                <td>
                                    <span class="badge <?= $car['featured'] ? 'badge-success' : 'badge-secondary' ?>">
                                        <?= $car['featured'] ? 'Yes' : 'No' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?= isCarAvailableNow($car['id']) ? 'available' : 'unavailable' ?>">
                                        <?= isCarAvailableNow($car['id']) ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="/admin/edit-car.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="/admin/cars.php" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this car?');">
                                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                        <button type="submit" name="delete_car" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <a href="/pages/car-details.php?id=<?= $car['id'] ?>" target="_blank" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $searchTerm ? '&search=' . urlencode($searchTerm) : '' ?><?= $availabilityFilter ? '&availability=' . $availabilityFilter : '' ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-link current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $searchTerm ? '&search=' . urlencode($searchTerm) : '' ?><?= $availabilityFilter ? '&availability=' . $availabilityFilter : '' ?>" class="pagination-link">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?><?= $searchTerm ? '&search=' . urlencode($searchTerm) : '' ?><?= $availabilityFilter ? '&availability=' . $availabilityFilter : '' ?>" class="pagination-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="table-footer">
                <p>Showing <?= count($cars) ?> of <?= $totalCars ?> cars</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include admin footer
include_once __DIR__ . '/includes/footer.php';
?>
