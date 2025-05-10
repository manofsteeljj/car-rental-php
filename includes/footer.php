</main>
        <!-- Main content ends -->
        
        <!-- Footer section -->
        <footer class="site-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-about">
                        <div class="logo">
                            <span class="logo-text">DriveEasy</span>
                            <span class="logo-subtext">Rentals</span>
                        </div>
                        <p>DriveEasy Rentals offers premium car rental services with a wide selection of vehicles for any occasion. Experience the freedom of the open road with our reliable and affordable rental options.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    
                    <div class="footer-links">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="/index.php">Home</a></li>
                            <li><a href="/pages/cars.php">Our Cars</a></li>
                            <li><a href="/pages/categories.php">Categories</a></li>
                            <li><a href="/pages/about.php">About Us</a></li>
                            <li><a href="/pages/contact.php">Contact</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-categories">
                        <h3>Car Categories</h3>
                        <ul>
                            <?php
                            $categories = getCategories();
                            foreach(array_slice($categories, 0, 5) as $category) {
                                echo '<li><a href="/pages/categories.php?id=' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="footer-contact">
                        <h3>Contact Us</h3>
                        <ul>
                            <li><i class="fas fa-map-marker-alt"></i> 123 Rental Street, Car City, CC 12345</li>
                            <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                            <li><i class="fas fa-envelope"></i> info@driveeasy.com</li>
                            <li><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 8:00 PM</li>
                        </ul>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; <?= date('Y') ?> DriveEasy Rentals. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="/pages/privacy.php">Privacy Policy</a>
                        <a href="/pages/terms.php">Terms & Conditions</a>
                        <a href="/pages/faq.php">FAQ</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- Page wrapper ends -->
    
    <script src="/assets/js/main.js"></script>
</body>
</html>
