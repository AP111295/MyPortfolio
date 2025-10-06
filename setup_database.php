<?php
// Database setup script
// Run this file once to create the database and table

$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP MySQL password is empty

try {
    // First, connect without specifying a database to create the database
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS portfolio_db";
    $pdo->exec($sql);
    echo "Database 'portfolio_db' created successfully.<br>";
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=portfolio_db;charset=utf8", $username, $password);
    
    // Create contact_messages table
    $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE
    )";
    
    $pdo->exec($sql);
    echo "Table 'contact_messages' created successfully.<br>";
    echo "Database setup completed! You can now use the contact form.<br>";
    echo "<a href='index.html'>Go back to portfolio</a> | <a href='admin/index.php'>View Admin Panel</a>";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>