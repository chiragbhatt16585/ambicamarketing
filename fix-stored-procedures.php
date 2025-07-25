<?php
// Fix stored procedures script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fixing Stored Procedures</h1>";

try {
    require_once 'api/config.php';
    $db = Database::getInstance();
    echo "✓ Database connection successful<br>";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Drop existing procedures if they exist
echo "<h2>1. Dropping existing procedures</h2>";
try {
    $db->query("DROP PROCEDURE IF EXISTS GetContactStats");
    echo "✓ Dropped GetContactStats procedure<br>";
} catch (Exception $e) {
    echo "⚠ Warning: " . $e->getMessage() . "<br>";
}

try {
    $db->query("DROP PROCEDURE IF EXISTS GetProductStats");
    echo "✓ Dropped GetProductStats procedure<br>";
} catch (Exception $e) {
    echo "⚠ Warning: " . $e->getMessage() . "<br>";
}

// Create GetContactStats procedure
echo "<h2>2. Creating GetContactStats procedure</h2>";
$contactStatsSQL = "
CREATE PROCEDURE GetContactStats()
BEGIN
    SELECT 
        COUNT(*) as total_contacts,
        COUNT(CASE WHEN status = 'new' THEN 1 END) as new_contacts,
        COUNT(CASE WHEN status = 'read' THEN 1 END) as read_contacts,
        COUNT(CASE WHEN status = 'replied' THEN 1 END) as replied_contacts,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_contacts
    FROM contacts;
END
";

try {
    $db->query($contactStatsSQL);
    echo "✓ Created GetContactStats procedure<br>";
} catch (Exception $e) {
    echo "✗ Failed to create GetContactStats: " . $e->getMessage() . "<br>";
}

// Create GetProductStats procedure
echo "<h2>3. Creating GetProductStats procedure</h2>";
$productStatsSQL = "
CREATE PROCEDURE GetProductStats()
BEGIN
    SELECT 
        COUNT(*) as total_products,
        COUNT(CASE WHEN is_active = TRUE THEN 1 END) as active_products,
        COUNT(CASE WHEN is_featured = TRUE THEN 1 END) as featured_products,
        COUNT(DISTINCT business_category_id) as business_categories,
        COUNT(DISTINCT product_category_id) as product_categories
    FROM products;
END
";

try {
    $db->query($productStatsSQL);
    echo "✓ Created GetProductStats procedure<br>";
} catch (Exception $e) {
    echo "✗ Failed to create GetProductStats: " . $e->getMessage() . "<br>";
}

// Test the procedures
echo "<h2>4. Testing procedures</h2>";

try {
    $contactStats = $db->fetch("CALL GetContactStats()");
    echo "✓ GetContactStats works: " . json_encode($contactStats) . "<br>";
} catch (Exception $e) {
    echo "✗ GetContactStats failed: " . $e->getMessage() . "<br>";
}

try {
    $productStats = $db->fetch("CALL GetProductStats()");
    echo "✓ GetProductStats works: " . json_encode($productStats) . "<br>";
} catch (Exception $e) {
    echo "✗ GetProductStats failed: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Testing API endpoints</h2>";

// Test the stats API endpoint
$url = "http://localhost/ambicamarketing/api/products.php?action=stats";
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
        echo "✓ Stats API works!<br>";
        $data = json_decode($response, true);
        if ($data) {
            echo "Response: " . json_encode($data) . "<br>";
        }
    } else {
        echo "✗ Stats API failed<br>";
        echo "Response: " . substr($response, 0, 500) . "<br>";
    }
}

echo "<h2>6. Setup Complete!</h2>";
echo "Now try the dashboard:<br>";
echo "<a href='admin/login.html'>Go to Admin Login</a><br>";
echo "<a href='debug-api.php'>Test All API Endpoints</a><br>";
?> 