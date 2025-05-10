<?php
// MariaDB connection settings
$host = 'localhost';      // Typically localhost or 127.0.0.1
$dbname = 'car_rental';   // Your database name
$username = 'root';       // Your MariaDB username
$password = 'mary';           // Your MariaDB password

try {
    // Create a PDO instance for MariaDB
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set character set to utf8mb4
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>