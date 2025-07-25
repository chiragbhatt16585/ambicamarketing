<?php
// Test script to verify dashboard functionality
require_once 'api/config.php';

echo "<h1>Dashboard API Test</h1>";

try {
    $db = Database::getInstance();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test business categories
    $businessCategories = $db->fetchAll("SELECT * FROM business_categories ORDER BY display_order");
    echo "<h2>Business Categories (" . count($businessCategories) . ")</h2>";
    foreach ($businessCategories as $category) {
        echo "<p>• {$category['name']} (ID: {$category['id']})</p>";
    }
    
    // Test product categories
    $productCategories = $db->fetchAll("SELECT * FROM product_categories ORDER BY display_order");
    echo "<h2>Product Categories (" . count($productCategories) . ")</h2>";
    foreach ($productCategories as $category) {
        echo "<p>• {$category['name']} (ID: {$category['id']})</p>";
    }
    
    // Test products
    $products = $db->fetchAll("SELECT * FROM products ORDER BY display_order");
    echo "<h2>Products (" . count($products) . ")</h2>";
    foreach ($products as $product) {
        echo "<p>• {$product['name']} (ID: {$product['id']}) - ₹{$product['price']}</p>";
    }
    
    // Test contacts
    $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
    echo "<h2>Recent Contacts (" . count($contacts) . ")</h2>";
    foreach ($contacts as $contact) {
        echo "<p>• {$contact['name']} ({$contact['email']}) - {$contact['status']}</p>";
    }
    
    // Test settings
    $settings = $db->fetchAll("SELECT * FROM settings");
    echo "<h2>Settings (" . count($settings) . ")</h2>";
    foreach ($settings as $setting) {
        echo "<p>• {$setting['setting_key']}: {$setting['setting_value']}</p>";
    }
    
    echo "<h2>API Endpoints Test</h2>";
    echo "<p><a href='api/products.php?action=stats' target='_blank'>Test Stats API</a></p>";
    echo "<p><a href='api/products.php?action=business-categories' target='_blank'>Test Business Categories API</a></p>";
    echo "<p><a href='api/products.php?action=product-categories' target='_blank'>Test Product Categories API</a></p>";
    echo "<p><a href='api/products.php?action=products' target='_blank'>Test Products API</a></p>";
    echo "<p><a href='api/contacts.php' target='_blank'>Test Contacts API</a></p>";
    echo "<p><a href='api/settings.php' target='_blank'>Test Settings API</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?> 