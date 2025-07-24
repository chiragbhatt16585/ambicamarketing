<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Test 1: Check if MySQL is running
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "✅ MySQL server is running<br>";
} catch (PDOException $e) {
    echo "❌ MySQL server error: " . $e->getMessage() . "<br>";
    echo "Make sure MySQL is started in XAMPP Control Panel<br>";
    exit;
}

// Test 2: Check if database exists
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('ambica_marketing', $databases)) {
        echo "✅ Database 'ambica_marketing' exists<br>";
    } else {
        echo "❌ Database 'ambica_marketing' does NOT exist<br>";
        echo "Available databases: " . implode(", ", $databases) . "<br>";
        echo "<br><strong>To create the database:</strong><br>";
        echo "1. Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a><br>";
        echo "2. Click 'New' on the left sidebar<br>";
        echo "3. Enter database name: ambica_marketing<br>";
        echo "4. Click 'Create'<br>";
        echo "5. Then import the schema from database/schema.sql<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error checking databases: " . $e->getMessage() . "<br>";
}

// Test 3: Try to connect to the specific database
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ambica_marketing;charset=utf8mb4",
        "root",
        ""
    );
    echo "✅ Successfully connected to ambica_marketing database<br>";
    
    // Test 4: Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "✅ Tables found: " . implode(", ", $tables) . "<br>";
    } else {
        echo "❌ No tables found in database<br>";
        echo "You need to import the schema from database/schema.sql<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Cannot connect to ambica_marketing database: " . $e->getMessage() . "<br>";
    echo "The database either doesn't exist or has wrong permissions<br>";
}

echo "<br><h3>Next Steps:</h3>";
echo "1. If database doesn't exist: Create it in phpMyAdmin<br>";
echo "2. If database exists but no tables: Import database/schema.sql<br>";
echo "3. If still having issues: Check XAMPP MySQL is running<br>";
?> 