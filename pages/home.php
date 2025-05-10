<?php
// Get featured cars for the homepage
$featured_cars = getFeaturedCars(6);

// Get all categories
$categories = getCategories();
?>

<!-- Hero section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Find Your Perfect Drive</h1>
            <p>Explore our premium selection of vehicles for any occasion</p>
            <div class="hero-buttons">
                <a href="/pages/cars.php" class="btn btn-primary">View All Cars</a>
                <a href="/pages/booking.php" class="btn btn-secondary">Book Now</a>
            </div>
        </div>
        
        <!-- Quick search form -->
        <div class="search-box">
            <h2>Quick Search</h2>
            <form action="/pages/search.php" method="GET" class="search-form">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="pickup_date">Pickup Date</label>
                    <input type="date" name="pickup_date" id="pickup_date" min="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="return_date">Return Date</label>
                    <input type="date" name="return_date" id="return_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Search Available Cars</button>
            </form>
        </div>
    </div>
</section>

<!-- Featured cars section -->
<section class="featured-cars">
    <div class="container">
        <div class="section-header">
            <h2>Featured Cars</h2>
            <p>Discover our most popular rental options</p>
        </div>
        
        <div class="cars-grid">
            <?php if(empty($featured_cars)): ?>
                <div class="empty-state">
                    <i class="fas fa-car"></i>
                    <p>No featured cars available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach($featured_cars as $car): ?>
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
            <?php endif; ?>
        </div>
        
        <div class="section-footer">
            <a href="/pages/cars.php" class="btn btn-secondary">View All Cars</a>
        </div>
    </div>
</section>

<!-- Categories section -->
<section class="categories">
    <div class="container">
        <div class="section-header">
            <h2>Browse by Category</h2>
            <p>Find the perfect vehicle for your needs</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach($categories as $category): ?>
                <a href="/pages/categories.php?id=<?= $category['id'] ?>" class="category-card">
                    <div class="category-icon">
                        <i class="<?= htmlspecialchars($category['icon'] ?: 'fas fa-car') ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How it works section -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Renting a car with us is quick and easy</p>
        </div>
        
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Choose Your Car</h3>
                <p>Browse our wide selection of vehicles and find the perfect match for your needs.</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Book Your Dates</h3>
                <p>Select your pickup and return dates and complete the booking form.</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3>Confirm Reservation</h3>
                <p>Review your booking details and confirm your reservation.</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3>Enjoy Your Ride</h3>
                <p>Pick up your car on the scheduled date and enjoy your journey!</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials section -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2>What Our Customers Say</h2>
            <p>Hear from our satisfied clients</p>
        </div>
        
        <div class="testimonials-slider">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"The service was excellent! The car was clean, well-maintained, and exactly what I needed for my trip. Will definitely rent from DriveEasy again."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="testimonial-info">
                        <h4>John Smith</h4>
                        <span>Business Traveler</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <p class="testimonial-text">"Easy booking process and great customer service. The staff was friendly and helpful. The car was in perfect condition. Highly recommended!"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="testimonial-info">
                        <h4>Emily Johnson</h4>
                        <span>Family Vacation</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Fantastic experience from start to finish. The pickup and return process was seamless, and the car was exactly as advertised. Will be my go-to car rental service!"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="testimonial-info">
                        <h4>Michael Brown</h4>
                        <span>Road Trip Enthusiast</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Hit the Road?</h2>
            <p>Experience the freedom of driving with our premium rental cars.</p>
            <a href="/pages/cars.php" class="btn btn-light">Explore Our Fleet</a>
        </div>
    </div>
</section>
