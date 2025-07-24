<?php
// Ambica Marketing Website Installation Script
// Run this script once to set up the database and initial configuration

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Ambica Marketing Website Installation</h1>";

// Check if already installed
if (file_exists('installed.txt')) {
    echo "<p style='color: red;'>Website is already installed. Delete 'installed.txt' to reinstall.</p>";
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'ambica_marketing';
$username = 'root';
$password = '';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Database connection successful</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    
    echo "<p>✓ Database created/selected</p>";
    
    // Read and execute SQL schema
    $sql = file_get_contents('database/schema.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p>✓ Database tables created</p>";
    echo "<p>✓ Sample data inserted</p>";
    
    // Create installed marker
    file_put_contents('installed.txt', date('Y-m-d H:i:s'));
    
    echo "<h2>Installation Complete!</h2>";
    echo "<p>Your Ambica Marketing website has been successfully installed.</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Access your website: <a href='index.html' target='_blank'>Homepage</a></li>";
    echo "<li>Access admin panel: <a href='admin/login.html' target='_blank'>Admin Login</a></li>";
    echo "<li>Default admin credentials: admin / admin123</li>";
    echo "<li>Update company information in admin settings</li>";
    echo "<li>Add your products through the admin panel</li>";
    echo "</ul>";
    
    echo "<h3>Important Notes:</h3>";
    echo "<ul>";
    echo "<li>Change the default admin password after first login</li>";
    echo "<li>Update contact information in the admin settings</li>";
    echo "<li>Add real product images to assets/images/ directory</li>";
    echo "<li>Configure email settings in api/contacts.php for notifications</li>";
    echo "<li>Set up SSL certificate for production use</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>Installation completed successfully!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

h1 {
    color: #2563eb;
    border-bottom: 2px solid #2563eb;
    padding-bottom: 10px;
}

h2 {
    color: #1f2937;
    margin-top: 30px;
}

h3 {
    color: #374151;
    margin-top: 20px;
}

ul {
    background: #f8fafc;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #2563eb;
}

li {
    margin-bottom: 8px;
}

a {
    color: #2563eb;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

p {
    margin: 10px 0;
}
</style> 