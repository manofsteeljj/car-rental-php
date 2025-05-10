<?php
// Initialize database connection if not already done
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
    require_once __DIR__ . '/../includes/auth.php';
}

// Get car ID from URL
$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
$car = null;

// If car ID is provided, get car details
if ($car_id > 0) {
    $query = "
        SELECT c.*, ct.name AS category_name
        FROM cars c
        JOIN categories ct ON c.category_id = ct.id
        WHERE c.id = :car_id
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
    $stmt->execute();
    $car = $stmt->fetch();
}

// Handle booking form submission
$formSubmitted = false;
$bookingSuccessful = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    
    // Get form data
    $car_id = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
    $pickup_date = isset($_POST['pickup_date']) ? sanitize($_POST['pickup_date']) : '';
    $return_date = isset($_POST['return_date']) ? sanitize($_POST['return_date']) : '';
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $additional_requirements = isset($_POST['additional_requirements']) ? sanitize($_POST['additional_requirements']) : '';
    
    // Validate car ID
    if ($car_id <= 0) {
        $errors[] = "Please select a car to book.";
    }
    
    // Validate dates
    if (empty($pickup_date)) {
        $errors[] = "Pickup date is required.";
    }
    
    if (empty($return_date)) {
        $errors[] = "Return date is required.";
    }
    
    if (!empty($pickup_date) && !empty($return_date)) {
        $pickup = new DateTime($pickup_date);
        $return = new DateTime($return_date);
        $today = new DateTime();
        
        if ($pickup < $today) {
            $errors[] = "Pickup date cannot be in the past.";
        }
        
        if ($return <= $pickup) {
            $errors[] = "Return date must be after pickup date.";
        }
    }
    
    // Validate personal information
    if (empty($name)) {
        $errors[] = "Your name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    
    // If no errors, check car availability
    if (empty($errors)) {
        $available = isCarAvailable($car_id, $pickup_date, $return_date);
        
        if (!$available) {
            $errors[] = "Sorry, this car is not available for the selected dates. Please choose different dates or another vehicle.";
        }
    }
    
    // If still no errors, create the booking
    if (empty($errors)) {
        try {
            // Get car details for price calculation
            $carQuery = "SELECT daily_rate FROM cars WHERE id = :car_id LIMIT 1";
            $carStmt = $pdo->prepare($carQuery);
            $carStmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
            $carStmt->execute();
            $carDetails = $carStmt->fetch();
            
            if ($carDetails) {
                // Calculate booking price
                $totalPrice = calculateBookingPrice($carDetails['daily_rate'], $pickup_date, $return_date);
                
                // Generate booking reference
                $bookingReference = generateBookingReference();
                
                // Insert booking into database
                $bookingQuery = "
                    INSERT INTO bookings (
                        car_id, customer_name, customer_email, customer_phone, 
                        start_date, end_date, total_price, reference_number, 
                        additional_requirements, status, created_at
                    ) VALUES (
                        :car_id, :customer_name, :customer_email, :customer_phone,
                        :start_date, :end_date, :total_price, :reference_number,
                        :additional_requirements, 'pending', NOW()
                    )
                ";
                
                $bookingStmt = $pdo->prepare($bookingQuery);
                $bookingStmt->execute([
                    ':car_id' => $car_id,
                    ':customer_name' => $name,
                    ':customer_email' => $email,
                    ':customer_phone' => $phone,
                    ':start_date' => $pickup_date,
                    ':end_date' => $return_date,
                    ':total_price' => $totalPrice,
                    ':reference_number' => $bookingReference,
                    ':additional_requirements' => $additional_requirements
                ]);
                
                $bookingSuccessful = true;
                
                // Store booking reference in session for confirmation page
                $_SESSION['booking_reference'] = $bookingReference;
                $_SESSION['booking_email'] = $email;
                
                // Redirect to confirmation page
                header("Location: /pages/booking-confirmation.php");
                exit();
            } else {
                $errors[] = "Selected car not found. Please try again.";
            }
        } catch (PDOException $e) {
            $errors[] = "An error occurred while processing your booking. Please try again.";
            error_log("Booking error: " . $e->getMessage());
        }
    }
}

// Get all cars for select dropdown
$allCarsQuery = "
    SELECT c.id, c.make, c.model, ct.name AS category_name
    FROM cars c
    JOIN categories ct ON c.category_id = ct.id
    ORDER BY c.make, c.model
";
$allCarsStmt = $pdo->query($allCarsQuery);
$allCars = $allCarsStmt->fetchAll();

// Include header if not already included
if (!defined('HEADER_INCLUDED')) {
    include_once __DIR__ . '/../includes/header.php';
    define('HEADER_INCLUDED', true);
}
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>Book Your Car</h1>
        <p>Select your dates and complete the booking form</p>
    </div>
</div>

<!-- Booking section -->
<section class="booking-section">
    <div class="container">
        <div class="booking-grid">
            <div class="booking-form-wrapper">
                <form method="POST" action="/pages/booking.php" class="booking-form">
                    <h2>Reservation Details</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <ul>
                                <?php foreach($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="car_id">Select Car</label>
                        <select name="car_id" id="car_id" required>
                            <option value="">-- Select a Car --</option>
                            <?php foreach($allCars as $availableCar): ?>
                                <option value="<?= $availableCar['id'] ?>" <?= ($car && $car['id'] == $availableCar['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($availableCar['make'] . ' ' . $availableCar['model'] . ' (' . $availableCar['category_name'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="pickup_date">Pickup Date</label>
                            <input type="date" name="pickup_date" id="pickup_date" min="<?= date('Y-m-d') ?>" required
                                   value="<?= isset($_POST['pickup_date']) ? $_POST['pickup_date'] : '' ?>">
                        </div>
                        
                        <div class="form-group half">
                            <label for="return_date">Return Date</label>
                            <input type="date" name="return_date" id="return_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required
                                   value="<?= isset($_POST['return_date']) ? $_POST['return_date'] : '' ?>">
                        </div>
                    </div>
                    
                    <h2>Personal Information</h2>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" required placeholder="Enter your full name"
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" required placeholder="Enter your email address"
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                        
                        <div class="form-group half">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" required placeholder="Enter your phone number"
                                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_requirements">Additional Requirements (Optional)</label>
                        <textarea name="additional_requirements" id="additional_requirements" rows="4" placeholder="Any special requests or requirements?"><?= isset($_POST['additional_requirements']) ? htmlspecialchars($_POST['additional_requirements']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Complete Booking</button>
                    </div>
                </form>
            </div>
            
            <div class="booking-info">
                <div class="booking-info-card">
                    <h2>Booking Information</h2>
                    
                    <?php if ($car): ?>
                        <div class="selected-car-info">
                            <div class="car-image">
                                <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>">
                            </div>
                            <h3><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></h3>
                            <div class="car-meta">
                                <span><i class="fas fa-tag"></i> <?= htmlspecialchars($car['category_name']) ?></span>
                                <span><i class="fas fa-user"></i> <?= $car['passengers'] ?> Seats</span>
                                <span><i class="fas fa-cog"></i> <?= htmlspecialchars($car['transmission']) ?></span>
                            </div>
                            <div class="daily-rate">
                                <span class="rate-label">Daily Rate:</span>
                                <span class="rate-amount"><?= formatPrice($car['daily_rate']) ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="select-car-prompt">
                            <i class="fas fa-car"></i>
                            <p>Please select a car to see details.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="booking-notes">
                        <h3>Important Notes</h3>
                        <ul>
                            <li><i class="fas fa-info-circle"></i> A valid credit card will be required for final payment.</li>
                            <li><i class="fas fa-info-circle"></i> A valid driver's license is required at pickup.</li>
                            <li><i class="fas fa-info-circle"></i> The primary driver must be 21 years or older.</li>
                            <li><i class="fas fa-info-circle"></i> Free cancellation up to 48 hours before pickup.</li>
                        </ul>
                    </div>
                    
                    <div class="need-help">
                        <h3>Need Help?</h3>
                        <p>Contact our customer support team for assistance.</p>
                        <div class="contact-info">
                            <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                            <p><i class="fas fa-envelope"></i> bookings@driveeasy.com</p>
                        </div>
                    </div>
                </div>
            </div>
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
