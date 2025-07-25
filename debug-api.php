<?php
// Debug script to test API endpoints
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>API Debug Test</h1>";

// Test 1: Check if config loads
echo "<h2>1. Testing Config Load</h2>";
try {
    require_once 'api/config.php';
    echo "✓ Config loaded successfully<br>";
} catch (Exception $e) {
    echo "✗ Config error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check database connection
echo "<h2>2. Testing Database Connection</h2>";
try {
    $db = Database::getInstance();
    echo "✓ Database connection successful<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    echo "Please check your database settings in api/config.php<br>";
    echo "Current settings:<br>";
    echo "- Host: " . DB_HOST . "<br>";
    echo "- Database: " . DB_NAME . "<br>";
    echo "- User: " . DB_USER . "<br>";
    exit;
}

// Test 3: Check if tables exist
echo "<h2>3. Testing Database Tables</h2>";
$tables = ['business_categories', 'product_categories', 'products', 'contacts', 'settings'];
foreach ($tables as $table) {
    try {
        $result = $db->fetch("SHOW TABLES LIKE '$table'");
        if ($result) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' does not exist<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error checking table '$table': " . $e->getMessage() . "<br>";
    }
}

// Test 4: Test stats query
echo "<h2>4. Testing Stats Query</h2>";
try {
    $stats = $db->fetch("CALL GetProductStats()");
    if ($stats) {
        echo "✓ Stats query successful<br>";
        echo "Stats data: " . json_encode($stats) . "<br>";
    } else {
        echo "✗ Stats query returned no data<br>";
    }
} catch (Exception $e) {
    echo "✗ Stats query error: " . $e->getMessage() . "<br>";
    echo "This might be because the stored procedure doesn't exist.<br>";
    
    // Try a simple count query instead
    try {
        $productCount = $db->fetch("SELECT COUNT(*) as total FROM products")['total'];
        $categoryCount = $db->fetch("SELECT COUNT(*) as total FROM business_categories")['total'];
        $contactCount = $db->fetch("SELECT COUNT(*) as total FROM contacts")['total'];
        
        echo "✓ Simple counts successful:<br>";
        echo "- Products: $productCount<br>";
        echo "- Categories: $categoryCount<br>";
        echo "- Contacts: $contactCount<br>";
    } catch (Exception $e2) {
        echo "✗ Simple count error: " . $e2->getMessage() . "<br>";
    }
}

// Test 5: Test API endpoints
echo "<h2>5. Testing API Endpoints</h2>";
$endpoints = [
    'api/products.php?action=stats',
    'api/products.php?action=business-categories',
    'api/products.php?action=product-categories',
    'api/products.php?action=products',
    'api/contacts.php',
    'api/settings.php'
];

foreach ($endpoints as $endpoint) {
    echo "<h3>Testing: $endpoint</h3>";
    $url = "http://localhost/ambicamarketing/$endpoint";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "✗ cURL Error: $error<br>";
    } else {
        echo "HTTP Code: $httpCode<br>";
        if ($httpCode == 200) {
            echo "✓ Success<br>";
            $data = json_decode($response, true);
            if ($data) {
                echo "Response: " . substr(json_encode($data), 0, 200) . "...<br>";
            } else {
                echo "Response (not JSON): " . substr($response, 0, 200) . "...<br>";
            }
        } else {
            echo "✗ Error<br>";
            echo "Response: " . substr($response, 0, 500) . "...<br>";
        }
    }
    echo "<br>";
}

echo "<h2>6. Next Steps</h2>";
echo "If you see database connection errors:<br>";
echo "1. Make sure MySQL is running in XAMPP<br>";
echo "2. Check database credentials in api/config.php<br>";
echo "3. Import the database schema from database/schema.sql<br>";
echo "4. Make sure the database 'ambica_marketing' exists<br>";
?> 