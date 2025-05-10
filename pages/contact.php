<?php
// Initialize database connection if not already done
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
}

// Initialize variables
$errors = [];
$success = false;
$name = $email = $phone = $subject = $message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? sanitize($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required.";
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        try {
            // In a real application, you would send an email here
            // For this example, we'll just save the message to the database
            
            $query = "
                INSERT INTO contact_messages (name, email, phone, subject, message, created_at)
                VALUES (:name, :email, :phone, :subject, :message, NOW())
            ";
            
            // Check if the contact_messages table exists, create it if not
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS contact_messages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    phone VARCHAR(20),
                    subject VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    is_read BOOLEAN DEFAULT FALSE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':subject' => $subject,
                ':message' => $message
            ]);
            
            $success = true;
            
            // Clear form data after successful submission
            $name = $email = $phone = $subject = $message = '';
        } catch (PDOException $e) {
            $errors[] = "An error occurred while sending your message. Please try again.";
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Get in touch with our team for inquiries and support</p>
    </div>
</div>

<!-- Contact section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <div class="info-card">
                    <h2>How Can We Help You?</h2>
                    <p>Have questions about our rental services or need assistance with your booking? Our team is here to help. Fill out the form or reach out to us using the contact information below.</p>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="method-details">
                                <h3>Visit Us</h3>
                                <p>123 Rental Street, Car City, CC 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="method-details">
                                <h3>Call Us</h3>
                                <p>+1 (555) 123-4567</p>
                                <p>Monday-Friday: 8AM - 8PM</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="method-details">
                                <h3>Email Us</h3>
                                <p>info@driveeasy.com</p>
                                <p>support@driveeasy.com</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-connect">
                        <h3>Connect With Us</h3>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3023.2375441275275!2d-74.0059445!3d40.7127837!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzQ2LjAiTiA3NMKwMDAnMjEuNCJX!5e0!3m2!1sen!2sus!4v1635181410000!5m2!1sen!2sus" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
            
            <div class="contact-form-container">
                <?php if ($success): ?>
                    <div class="success-message">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2>Thank You for Contacting Us!</h2>
                        <p>Your message has been sent successfully. Our team will get back to you as soon as possible.</p>
                        <a href="/index.php" class="btn btn-primary">Back to Home</a>
                    </div>
                <?php else: ?>
                    <form action="/pages/contact.php" method="POST" class="contact-form">
                        <h2>Send Us a Message</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error">
                                <ul>
                                    <?php foreach($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="name">Your Name</label>
                                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($name) ?>" placeholder="Enter your name">
                            </div>
                            
                            <div class="form-group half">
                                <label for="email">Email Address</label>
                                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="phone">Phone Number (Optional)</label>
                                <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Enter your phone number">
                            </div>
                            
                            <div class="form-group half">
                                <label for="subject">Subject</label>
                                <input type="text" name="subject" id="subject" required value="<?= htmlspecialchars($subject) ?>" placeholder="What is this regarding?">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" rows="6" required placeholder="Type your message here..."><?= htmlspecialchars($message) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- FAQ section -->
<section class="faq-section">
    <div class="container">
        <div class="section-header">
            <h2>Frequently Asked Questions</h2>
            <p>Find answers to commonly asked questions about our services</p>
        </div>
        
        <div class="faq-grid">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What documents do I need to rent a car?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>To rent a car with DriveEasy, you'll need a valid driver's license, a credit card in your name, and a proof of identity. International customers may need additional documentation.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What is your cancellation policy?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We offer free cancellation up to 48 hours before your scheduled pickup time. Cancellations within 48 hours may be subject to a fee equivalent to one day's rental.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Is insurance included in the rental price?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Basic insurance is included in all our rental prices. Additional coverage options are available at the time of booking or pickup for enhanced protection.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What is the minimum age to rent a car?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>The minimum age to rent a car is 21 years. Drivers under 25 may be subject to a young driver surcharge. For luxury and specialty vehicles, the minimum age is 25.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I return the car to a different location?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes, one-way rentals are available for most vehicles. Additional fees may apply depending on the distance between locations.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What happens if I return the car late?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We provide a 30-minute grace period for returns. After that, late returns may be charged at an hourly rate up to a maximum of one additional day's rental.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .contact-section {
        padding: 4rem 0;
    }
    
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .info-card {
        background-color: white;
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }
    
    .info-card h2 {
        margin-bottom: 1rem;
    }
    
    .info-card p {
        margin-bottom: 1.5rem;
        color: var(--text-light);
    }
    
    .contact-methods {
        margin: 2rem 0;
    }
    
    .contact-method {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    
    .method-icon {
        width: 40px;
        height: 40px;
        background-color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .method-details h3 {
        margin-bottom: 0.25rem;
        font-size: 1.1rem;
    }
    
    .method-details p {
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }
    
    .social-connect h3 {
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
    
    .social-links {
        display: flex;
        gap: 1rem;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        background-color: var(--light-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        transition: var(--transition);
    }
    
    .social-link:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }
    
    .map-container {
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
    }
    
    .contact-form-container {
        background-color: white;
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }
    
    .contact-form h2 {
        margin-bottom: 1.5rem;
    }
    
    .success-message {
        text-align: center;
        padding: 2rem 0;
    }
    
    .success-icon {
        font-size: 4rem;
        color: var(--success-color);
        margin-bottom: 1.5rem;
    }
    
    .success-message h2 {
        margin-bottom: 1rem;
    }
    
    .success-message p {
        margin-bottom: 2rem;
        color: var(--text-light);
    }
    
    .faq-section {
        background-color: var(--light-color);
        padding: 4rem 0;
    }
    
    .faq-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }
    
    .faq-item {
        background-color: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    
    .faq-question {
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .faq-question h3 {
        margin: 0;
        font-size: 1.1rem;
    }
    
    .faq-question i {
        color: var(--primary-color);
        transition: var(--transition);
    }
    
    .faq-answer {
        padding: 0 1.5rem;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }
    
    .faq-item.active .faq-question {
        background-color: var(--primary-color);
        color: white;
    }
    
    .faq-item.active .faq-question i {
        color: white;
        transform: rotate(180deg);
    }
    
    .faq-item.active .faq-answer {
        padding: 0 1.5rem 1.5rem;
        max-height: 200px;
    }
    
    @media (max-width: 992px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
        
        .faq-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FAQ toggle functionality
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', function() {
                // Close all other FAQs
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current FAQ
                item.classList.toggle('active');
            });
        });
    });
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>
