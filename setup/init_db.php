<?php
// Initialize the database with schema and sample data

// Include database connection
require_once __DIR__ . '/../config/database.php';

echo "Starting database initialization...\n";

try {
    // Read the database.sql file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Execute the SQL commands
    $pdo->exec($sql);
    
    echo "Database schema and sample data have been initialized successfully!\n";
    echo "You can now log in as admin with:\n";
    echo "Email: admin@driveeasy.com\n";
    echo "Password: admin123\n";
    
} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
    exit(1);
}