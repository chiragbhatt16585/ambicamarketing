<?php
// Database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Setup Script</h1>";

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'ambica_marketing';

echo "<h2>1. Testing MySQL Connection</h2>";

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ MySQL connection successful<br>";
} catch (PDOException $e) {
    echo "✗ MySQL connection failed: " . $e->getMessage() . "<br>";
    echo "Please make sure:<br>";
    echo "1. XAMPP is running<br>";
    echo "2. MySQL service is started<br>";
    echo "3. Credentials are correct<br>";
    exit;
}

echo "<h2>2. Creating Database</h2>";

try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$dbname' created successfully<br>";
} catch (PDOException $e) {
    echo "✗ Database creation failed: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>3. Selecting Database</h2>";

try {
    $pdo->exec("USE `$dbname`");
    echo "✓ Database selected successfully<br>";
} catch (PDOException $e) {
    echo "✗ Database selection failed: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>4. Importing Schema</h2>";

$schemaFile = 'database/schema.sql';
if (!file_exists($schemaFile)) {
    echo "✗ Schema file not found: $schemaFile<br>";
    exit;
}

try {
    $schema = file_get_contents($schemaFile);
    
    // Split the schema into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(USE|CREATE DATABASE)/i', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...<br>";
            } catch (PDOException $e) {
                // Ignore errors for existing tables/procedures
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "⚠ Warning: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "✓ Schema import completed<br>";
} catch (Exception $e) {
    echo "✗ Schema import failed: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>5. Verifying Tables</h2>";

$tables = ['business_categories', 'product_categories', 'products', 'contacts', 'settings', 'admin_users'];
foreach ($tables as $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($result) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "✓ Table '$table' exists with $count records<br>";
        } else {
            echo "✗ Table '$table' does not exist<br>";
        }
    } catch (PDOException $e) {
        echo "✗ Error checking table '$table': " . $e->getMessage() . "<br>";
    }
}

echo "<h2>6. Testing API Connection</h2>";

try {
    require_once 'api/config.php';
    $db = Database::getInstance();
    echo "✓ API database connection successful<br>";
} catch (Exception $e) {
    echo "✗ API database connection failed: " . $e->getMessage() . "<br>";
}

echo "<h2>7. Setup Complete!</h2>";
echo "Your database is now ready. You can:<br>";
echo "1. <a href='admin/login.html'>Go to Admin Login</a><br>";
echo "2. <a href='debug-api.php'>Test API Endpoints</a><br>";
echo "3. <a href='test-dashboard.php'>View Dashboard Test</a><br>";
echo "<br>";
echo "Admin Login Credentials:<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
?> 