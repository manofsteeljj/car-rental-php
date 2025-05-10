-- Drop existing tables if they exist
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS cars;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL DEFAULT 'customer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CHECK (role IN ('admin', 'customer'))
);

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-car',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
);

-- Create cars table
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    category_id INT NOT NULL,
    daily_rate DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    passengers INT NOT NULL DEFAULT 5,
    luggage INT DEFAULT 3,
    doors INT DEFAULT 4,
    transmission VARCHAR(20) NOT NULL,
    fuel_type VARCHAR(20) NOT NULL,
    air_conditioning TINYINT(1) DEFAULT 1,
    featured TINYINT(1) DEFAULT 0,
    features TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Create bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    reference_number VARCHAR(20) NOT NULL UNIQUE,
    additional_requirements TEXT,
    status VARCHAR(10) NOT NULL DEFAULT 'pending',
    user_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled')),
    FOREIGN KEY (car_id) REFERENCES cars(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default admin user (password is hashed)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@driveeasy.com', '$2y$10$aQ70mBO6JGRAgc2UPlT8meGRljHtXgDJaGZlSKUBPzKa.wgXXE1XG', 'admin');

-- Insert sample categories
INSERT INTO categories (name, description, icon) VALUES
('Economy', 'Budget-friendly cars with great fuel efficiency', 'fas fa-car'),
('SUV', 'Spacious vehicles perfect for family trips', 'fas fa-truck'),
('Luxury', 'Premium vehicles with top-tier features', 'fas fa-car-side'),
('Sports', 'High-performance cars for an exciting drive', 'fas fa-tachometer-alt'),
('Van', 'Larger vehicles for groups or extra cargo', 'fas fa-shuttle-van');

-- Insert sample cars
INSERT INTO cars (make, model, year, category_id, daily_rate, image_url, description, passengers, luggage, doors, transmission, fuel_type, air_conditioning, featured, features) VALUES
('Toyota', 'Corolla', 2022, 1, 45.00, 'https://images.unsplash.com/photo-1623869675781-80aa31012c78?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80', 'The Toyota Corolla is a reliable and fuel-efficient compact car, perfect for city driving and short trips. It offers a comfortable ride with modern amenities.', 5, 2, 4, 'Automatic', 'Gasoline', 1, 1, 'Bluetooth,USB Port,Backup Camera,Cruise Control,Keyless Entry'),
('Honda', 'Civic', 2021, 1, 48.00, 'https://images.unsplash.com/photo-1606152421802-db97b9c7a11b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80', 'A stylish and economical choice for any journey. The Honda Civic offers excellent fuel efficiency without sacrificing comfort or performance.', 5, 2, 4, 'Automatic', 'Gasoline', 1, 0, 'Apple CarPlay,Android Auto,Bluetooth,Backup Camera,USB Charging'),
('Jeep', 'Grand Cherokee', 2021, 2, 89.00, 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80', 'The Jeep Grand Cherokee combines luxury with off-road capability. Perfect for adventurous road trips and outdoor excursions.', 5, 4, 5, 'Automatic', 'Diesel', 1, 1, '4WD,Leather Seats,Navigation System,Panoramic Sunroof,Premium Sound System'),
('BMW', '5 Series', 2022, 3, 125.00, 'https://images.unsplash.com/photo-1523983388277-336a66bf9bcd?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80', 'Experience luxury and performance with the BMW 5 Series. This premium sedan offers sophisticated styling, advanced technology, and a smooth, powerful ride.', 5, 3, 4, 'Automatic', 'Gasoline', 1, 1, 'Leather Seats,Navigation System,Heated Seats,Premium Sound System,Bluetooth,Parking Sensors'),
('Chevrolet', 'Camaro', 2022, 4, 110.00, 'https://images.unsplash.com/photo-1552519507-88aa2dfa9fdb?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80', 'Feel the thrill of driving with the Chevrolet Camaro. This iconic sports car delivers exhilarating performance and head-turning style.', 4, 2, 2, 'Manual', 'Gasoline', 1, 0, 'Sports Mode,Premium Sound System,Leather Seats,Bluetooth,Backup Camera'),
('Mercedes-Benz', 'E-Class', 2021, 3, 135.00, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80', 'The epitome of luxury and refinement. The Mercedes-Benz E-Class offers a sophisticated driving experience with cutting-edge technology and premium comfort.', 5, 3, 4, 'Automatic', 'Hybrid', 1, 1, 'Leather Seats,Navigation System,Heated & Cooled Seats,Premium Sound System,Advanced Driver Assistance,Panoramic Sunroof'),
('Ford', 'Transit', 2021, 5, 95.00, 'https://images.unsplash.com/photo-1616455152601-64710d650ded?ixlib=rb-1.2.1&auto=format&fit=crop&w=1050&q=80', 'Need space for a group or extra luggage? The Ford Transit van offers ample room for passengers and cargo, making it perfect for group trips or moving days.', 12, 8, 4, 'Automatic', 'Diesel', 1, 0, 'High Roof,Backup Camera,Bluetooth,USB Charging,Cruise Control');