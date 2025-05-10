<?php
/**
 * General functions used throughout the application
 */

/**
 * Sanitize input data to prevent XSS attacks
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format price with currency symbol
 * 
 * @param float $price The price to format
 * @return string Formatted price
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Format date to a readable format
 * 
 * @param string $date Date string to format
 * @return string Formatted date
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Check if a car is available for a specific date range
 * 
 * @param int $carId Car ID to check
 * @param string $startDate Booking start date
 * @param string $endDate Booking end date
 * @return bool True if available, false otherwise
 */
function isCarAvailable($carId, $startDate, $endDate) {
    global $pdo;
    
    $query = "SELECT COUNT(*) FROM bookings 
              WHERE car_id = :car_id 
              AND (
                  (start_date <= :start_date AND end_date >= :start_date) OR
                  (start_date <= :end_date AND end_date >= :end_date) OR
                  (start_date >= :start_date AND end_date <= :end_date)
              )
              AND status != 'cancelled'";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':car_id' => $carId,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);
    
    return $stmt->fetchColumn() == 0;
}

/**
 * Calculate the total price for a booking
 * 
 * @param float $dailyPrice Daily rental price
 * @param string $startDate Booking start date
 * @param string $endDate Booking end date
 * @return float Total booking price
 */
function calculateBookingPrice($dailyPrice, $startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $days = $end->diff($start)->days + 1; // Include last day
    
    return $dailyPrice * $days;
}

/**
 * Get all car categories
 * 
 * @return array List of categories
 */
function getCategories() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get featured cars for the homepage
 * 
 * @param int $limit Number of cars to return
 * @return array List of featured cars
 */
function getFeaturedCars($limit = 6) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT c.*, ct.name AS category_name 
        FROM cars c 
        JOIN categories ct ON c.category_id = ct.id
        WHERE c.featured = TRUE
        ORDER BY c.id DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Check if a car is currently available
 * 
 * @param int $carId Car ID to check
 * @return bool True if available now, false otherwise
 */
function isCarAvailableNow($carId) {
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    return isCarAvailable($carId, $today, $tomorrow);
}

/**
 * Generate a unique booking reference number
 * 
 * @return string Booking reference number
 */
function generateBookingReference() {
    return 'BK-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

/**
 * Redirect to another page
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Display error message
 * 
 * @param string $message Error message to display
 * @return string HTML for error message
 */
function showError($message) {
    return '<div class="alert alert-error">' . $message . '</div>';
}

/**
 * Display success message
 * 
 * @param string $message Success message to display
 * @return string HTML for success message
 */
function showSuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Get pagination links
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @return string HTML for pagination links
 */
function getPaginationLinks($currentPage, $totalPages, $baseUrl) {
    $links = '<div class="pagination">';
    
    if ($currentPage > 1) {
        $links .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="page-link">&laquo; Previous</a>';
    }
    
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $links .= '<span class="page-link current">' . $i . '</span>';
        } else {
            $links .= '<a href="' . $baseUrl . '?page=' . $i . '" class="page-link">' . $i . '</a>';
        }
    }
    
    if ($currentPage < $totalPages) {
        $links .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="page-link">Next &raquo;</a>';
    }
    
    $links .= '</div>';
    
    return $links;
}
?>
