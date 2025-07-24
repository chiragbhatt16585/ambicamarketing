<?php
// Test file to check API responses
header('Content-Type: text/plain');

echo "=== Testing API Responses ===\n\n";

// Test 1: Settings API
echo "1. Testing Settings API:\n";
$url = 'http://localhost/ambicamarketing/api/settings.php';
$response = file_get_contents($url);
echo "Response: " . substr($response, 0, 500) . "\n\n";

// Test 2: Products API
echo "2. Testing Products API:\n";
$url = 'http://localhost/ambicamarketing/api/products.php?action=business-categories';
$response = file_get_contents($url);
echo "Response: " . substr($response, 0, 500) . "\n\n";

// Test 3: Contacts API
echo "3. Testing Contacts API:\n";
$url = 'http://localhost/ambicamarketing/api/contacts.php';
$response = file_get_contents($url);
echo "Response: " . substr($response, 0, 500) . "\n\n";

echo "=== End Test ===\n";
?> 