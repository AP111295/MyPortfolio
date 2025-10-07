<?php
echo "<h2>🔧 Portfolio System Status Check</h2>";

// Check PHP version
echo "<p> PHP Version: " . phpversion() . "</p>";

// Check if config file exists
if (file_exists('config/database.php')) {
    echo "<p>Database config file exists</p>";
    
    // Try to include and test database connection
    try {
        require_once 'config/database.php';
        echo "<p>Database connection successful</p>";
        
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
        if ($stmt->rowCount() > 0) {
            echo "<p>Contact messages table exists</p>";
            
            // Count messages
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
            $result = $stmt->fetch();
            echo "<p>Total messages in database: " . $result['count'] . "</p>";
        } else {
            echo "<p>Contact messages table does not exist. Please run setup_database.php first.</p>";
        }
        
    } catch(Exception $e) {
        echo "<p>Database connection failed: " . $e->getMessage() . "</p>";
        echo "<p>Make sure MySQL is running in XAMPP and run setup_database.php</p>";
    }
} else {
    echo "<p>Database config file missing</p>";
}

// Check if admin file exists
if (file_exists('admin/index.php')) {
    echo "<p>Admin panel file exists</p>";
} else {
    echo "<p>Admin panel file missing</p>";
}

// Check if contact form processor exists
if (file_exists('process_contact.php')) {
    echo "<p>Contact form processor exists</p>";
} else {
    echo "<p>Contact form processor missing</p>";
}

echo "<hr>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<p><a href='index.html'>🏠 Portfolio Homepage</a></p>";
echo "<p><a href='setup_database.php'>🗄️ Database Setup</a></p>";
echo "<p><a href='admin/index.php'>👨‍💻 Admin Panel</a></p>";
echo "<p><a href='test_system.php'>🔧 This Status Page</a></p>";

echo "<hr>";
echo "<h3>📋 Next Steps:</h3>";
echo "<ol>";
echo "<li>If database table doesn't exist, click 'Database Setup' above</li>";
echo "<li>Test the contact form on your portfolio homepage</li>";
echo "<li>Check the admin panel to see submitted messages</li>";
echo "</ol>";
?>