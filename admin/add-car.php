<?php
// Initialize database connection and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
requireAdmin();

// Get categories for dropdown
$categories = getCategories();

// Initialize variables
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $make = isset($_POST['make']) ? sanitize($_POST['make']) : '';
    $model = isset($_POST['model']) ? sanitize($_POST['model']) : '';
    $year = isset($_POST['year']) ? (int)$_POST['year'] : '';
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $daily_rate = isset($_POST['daily_rate']) ? (float)$_POST['daily_rate'] : 0;
    $image_url = isset($_POST['image_url']) ? sanitize($_POST['image_url']) : '';
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $passengers = isset($_POST['passengers']) ? (int)$_POST['passengers'] : 0;
    $luggage = isset($_POST['luggage']) ? (int)$_POST['luggage'] : 0;
    $doors = isset($_POST['doors']) ? (int)$_POST['doors'] : 0;
    $transmission = isset($_POST['transmission']) ? sanitize($_POST['transmission']) : '';
    $fuel_type = isset($_POST['fuel_type']) ? sanitize($_POST['fuel_type']) : '';
    $air_conditioning = isset($_POST['air_conditioning']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $features = isset($_POST['features']) ? sanitize($_POST['features']) : '';
    
    // Validate form data
    if (empty($make)) {
        $errors[] = "Make is required.";
    }
    
    if (empty($model)) {
        $errors[] = "Model is required.";
    }
    
    if (empty($year) || $year < 1900 || $year > date('Y') + 1) {
        $errors[] = "Please enter a valid year.";
    }
    
    if ($category_id <= 0) {
        $errors[] = "Please select a category.";
    }
    
    if (empty($daily_rate) || $daily_rate <= 0) {
        $errors[] = "Please enter a valid daily rate.";
    }
    
    if (empty($image_url)) {
        $errors[] = "Image URL is required.";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    
    if ($passengers <= 0) {
        $errors[] = "Please enter a valid number of passengers.";
    }
    
    if (empty($transmission)) {
        $errors[] = "Transmission type is required.";
    }
    
    if (empty($fuel_type)) {
        $errors[] = "Fuel type is required.";
    }
    
    // If no errors, insert the car
    if (empty($errors)) {
        try {
            $query = "
                INSERT INTO cars (
                    make, model, year, category_id, daily_rate, image_url, description,
                    passengers, luggage, doors, transmission, fuel_type, air_conditioning,
                    featured, features, created_at
                ) VALUES (
                    :make, :model, :year, :category_id, :daily_rate, :image_url, :description,
                    :passengers, :luggage, :doors, :transmission, :fuel_type, :air_conditioning,
                    :featured, :features, NOW()
                )
            ";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':make' => $make,
                ':model' => $model,
                ':year' => $year,
                ':category_id' => $category_id,
                ':daily_rate' => $daily_rate,
                ':image_url' => $image_url,
                ':description' => $description,
                ':passengers' => $passengers,
                ':luggage' => $luggage,
                ':doors' => $doors,
                ':transmission' => $transmission,
                ':fuel_type' => $fuel_type,
                ':air_conditioning' => $air_conditioning,
                ':featured' => $featured,
                ':features' => $features
            ]);
            
            $success = true;
            $_SESSION['success'] = "Car added successfully.";
            
            // Redirect to car list
            header("Location: /admin/cars.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Include admin header
include_once __DIR__ . '/includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Add New Car</h1>
        <a href="/admin/cars.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Cars
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="admin-form-container">
        <form action="/admin/add-car.php" method="POST" class="admin-form">
            <h2>Car Information</h2>
            
            <div class="form-section">
                <div class="form-row">
                    <div class="form-group half">
                        <label for="make">Make</label>
                        <input type="text" name="make" id="make" value="<?= isset($_POST['make']) ? htmlspecialchars($_POST['make']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group half">
                        <label for="model">Model</label>
                        <input type="text" name="model" id="model" value="<?= isset($_POST['model']) ? htmlspecialchars($_POST['model']) : '' ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="year">Year</label>
                        <input type="number" name="year" id="year" min="1900" max="<?= date('Y') + 1 ?>" value="<?= isset($_POST['year']) ? htmlspecialchars($_POST['year']) : date('Y') ?>" required>
                    </div>
                    
                    <div class="form-group half">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="daily_rate">Daily Rate ($)</label>
                        <input type="number" name="daily_rate" id="daily_rate" min="0" step="0.01" value="<?= isset($_POST['daily_rate']) ? htmlspecialchars($_POST['daily_rate']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group half">
                        <label for="image_url">Image URL</label>
                        <input type="url" name="image_url" id="image_url" value="<?= isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : '' ?>" required>
                        <div id="image_preview" class="image-preview" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="form-section-title">Specifications</h3>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="passengers">Passengers</label>
                        <input type="number" name="passengers" id="passengers" min="1" max="20" value="<?= isset($_POST['passengers']) ? htmlspecialchars($_POST['passengers']) : '5' ?>" required>
                    </div>
                    
                    <div class="form-group half">
                        <label for="luggage">Luggage Capacity</label>
                        <input type="number" name="luggage" id="luggage" min="0" max="10" value="<?= isset($_POST['luggage']) ? htmlspecialchars($_POST['luggage']) : '3' ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="doors">Doors</label>
                        <input type="number" name="doors" id="doors" min="1" max="10" value="<?= isset($_POST['doors']) ? htmlspecialchars($_POST['doors']) : '4' ?>">
                    </div>
                    
                    <div class="form-group half">
                        <label for="transmission">Transmission</label>
                        <select name="transmission" id="transmission" required>
                            <option value="">-- Select Transmission --</option>
                            <option value="Automatic" <?= isset($_POST['transmission']) && $_POST['transmission'] == 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                            <option value="Manual" <?= isset($_POST['transmission']) && $_POST['transmission'] == 'Manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="Semi-Automatic" <?= isset($_POST['transmission']) && $_POST['transmission'] == 'Semi-Automatic' ? 'selected' : '' ?>>Semi-Automatic</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="fuel_type">Fuel Type</label>
                        <select name="fuel_type" id="fuel_type" required>
                            <option value="">-- Select Fuel Type --</option>
                            <option value="Gasoline" <?= isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Gasoline' ? 'selected' : '' ?>>Gasoline</option>
                            <option value="Diesel" <?= isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="Hybrid" <?= isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            <option value="Electric" <?= isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Electric' ? 'selected' : '' ?>>Electric</option>
                        </select>
                    </div>
                    
                    <div class="form-group half">
                        <div class="checkbox-group">
                            <input type="checkbox" name="air_conditioning" id="air_conditioning" value="1" <?= isset($_POST['air_conditioning']) ? 'checked' : '' ?>>
                            <label for="air_conditioning">Air Conditioning</label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" name="featured" id="featured" value="1" <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                            <label for="featured">Featured Car</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="form-section-title">Features</h3>
                
                <div class="form-group">
                    <label for="features">Features (comma separated)</label>
                    <input type="hidden" name="features" id="features" value="<?= isset($_POST['features']) ? htmlspecialchars($_POST['features']) : '' ?>">
                    
                    <div id="features_container" class="features-container">
                        <!-- Features will be displayed here -->
                    </div>
                    
                    <div class="form-row" style="margin-top: 1rem;">
                        <div class="form-group half">
                            <input type="text" id="new_feature" placeholder="Enter a feature">
                        </div>
                        <div class="form-group half">
                            <button type="button" id="add_feature" class="btn btn-outline">Add Feature</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Add Car</button>
                <a href="/admin/cars.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
// Include admin footer
include_once __DIR__ . '/includes/footer.php';
?>
