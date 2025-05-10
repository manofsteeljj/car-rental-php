<?php
// Initialize database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
requireAdmin();

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $new_status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
    
    if ($booking_id > 0 && in_array($new_status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
        try {
            $query = "UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':status' => $new_status,
                ':id' => $booking_id
            ]);
            
            $_SESSION['success'] = "Booking status updated successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating booking status: " . $e->getMessage();
        }
        
        // Redirect to refresh the page
        header("Location: /admin/bookings.php");
        exit();
    }
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Handle filtering
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query based on filters
$whereClause = [];
$params = [];

if (!empty($status)) {
    if ($status === 'active') {
        $whereClause[] = "CURDATE() BETWEEN b.start_date AND b.end_date AND b.status = 'confirmed'";
    } else {
        $whereClause[] = "b.status = :status";
        $params[':status'] = $status;
    }
}

if (!empty($dateFrom)) {
    $whereClause[] = "b.start_date >= :date_from";
    $params[':date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $whereClause[] = "b.end_date <= :date_to";
    $params[':date_to'] = $dateTo;
}

if (!empty($search)) {
    $whereClause[] = "(b.reference_number LIKE :search OR b.customer_name LIKE :search OR b.customer_email LIKE :search OR CONCAT(c.make, ' ', c.model) LIKE :search)";
    $params[':search'] = "%$search%";
}

$whereClauseStr = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";

// Count total bookings for pagination
$countQuery = "
    SELECT COUNT(*) 
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    $whereClauseStr
";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalBookings = $countStmt->fetchColumn();
$totalPages = ceil($totalBookings / $perPage);

// Get bookings with pagination and filtering
$query = "
    SELECT b.*, c.make, c.model, c.image_url
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    $whereClauseStr
    ORDER BY b.created_at DESC
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
$bookings = $stmt->fetchAll();

// Include admin header
include_once __DIR__ . '/includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Booking Management</h1>
        <div class="header-actions">
            <a href="/admin/bookings.php?status=pending" class="btn btn-warning">
                <i class="fas fa-clock"></i> Pending Bookings
            </a>
            <a href="/admin/bookings.php?status=active" class="btn btn-success">
                <i class="fas fa-car-side"></i> Active Rentals
            </a>
        </div>
    </div>
    
    <!-- Filter section -->
    <div class="filter-card">
        <form action="/admin/bookings.php" method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" placeholder="Reference, name, email, car" value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active Rentals</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                
                <div class="form-group filter-actions">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/admin/bookings.php" class="btn btn-outline">Reset</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Bookings list -->
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
        
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <h3>No bookings found</h3>
                <p>Try adjusting your filters or check back later for new bookings.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reference</th>
                            <th>Car</th>
                            <th>Customer</th>
                            <th>Dates</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td><strong><?= htmlspecialchars($booking['reference_number']) ?></strong></td>
                                <td>
                                    <div class="car-info">
                                        <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="<?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?>" class="car-thumbnail">
                                        <span><?= htmlspecialchars($booking['make'] . ' ' . $booking['model']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div><?= htmlspecialchars($booking['customer_name']) ?></div>
                                        <div class="customer-email"><?= htmlspecialchars($booking['customer_email']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <?= formatDate($booking['start_date']) ?> 
                                    <span class="date-separator">to</span> 
                                    <?= formatDate($booking['end_date']) ?>
                                </td>
                                <td><?= formatPrice($booking['total_price']) ?></td>
                                <td>
                                    <span class="status-badge <?= strtolower($booking['status']) ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td><?= formatDate($booking['created_at']) ?></td>
                                <td class="actions">
                                    <a href="/admin/booking-details.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline status-change-btn" data-booking-id="<?= $booking['id'] ?>" data-current-status="<?= $booking['status'] ?>">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Status change modal for each booking (hidden by default) -->
                            <div class="status-modal" id="status-modal-<?= $booking['id'] ?>" style="display: none;">
                                <div class="status-modal-content">
                                    <h3>Change Booking Status</h3>
                                    <p>Booking Reference: <strong><?= htmlspecialchars($booking['reference_number']) ?></strong></p>
                                    <form action="/admin/bookings.php" method="POST">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <div class="form-group">
                                            <label for="status-<?= $booking['id'] ?>">New Status:</label>
                                            <select name="status" id="status-<?= $booking['id'] ?>" required>
                                                <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="modal-buttons">
                                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                            <button type="button" class="btn btn-outline cancel-status-btn">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $status ? '&status=' . $status : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $dateFrom ? '&date_from=' . $dateFrom : '' ?><?= $dateTo ? '&date_to=' . $dateTo : '' ?>" class="pagination-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-link current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $dateFrom ? '&date_from=' . $dateFrom : '' ?><?= $dateTo ? '&date_to=' . $dateTo : '' ?>" class="pagination-link">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $status ? '&status=' . $status : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $dateFrom ? '&date_from=' . $dateFrom : '' ?><?= $dateTo ? '&date_to=' . $dateTo : '' ?>" class="pagination-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="table-footer">
                <p>Showing <?= count($bookings) ?> of <?= $totalBookings ?> bookings</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status change modal functionality
        const statusChangeBtns = document.querySelectorAll('.status-change-btn');
        const cancelStatusBtns = document.querySelectorAll('.cancel-status-btn');
        
        statusChangeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                const modal = document.getElementById(`status-modal-${bookingId}`);
                modal.style.display = 'flex';
            });
        });
        
        cancelStatusBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.status-modal');
                modal.style.display = 'none';
            });
        });
        
        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('status-modal')) {
                event.target.style.display = 'none';
            }
        });
    });
</script>

<style>
    .car-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .car-thumbnail {
        width: 50px;
        height: 30px;
        object-fit: cover;
        border-radius: 3px;
    }
    
    .customer-info {
        display: flex;
        flex-direction: column;
    }
    
    .customer-email {
        font-size: 0.85rem;
        color: var(--admin-text-light);
    }
    
    .status-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 100;
    }
    
    .status-modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: var(--admin-radius);
        width: 90%;
        max-width: 500px;
    }
    
    .modal-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }
</style>

<?php
// Include admin footer
include_once __DIR__ . '/includes/footer.php';
?>
