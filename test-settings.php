<?php
// Test settings API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Settings API Test</h1>";

// Test 1: Direct database query
echo "<h2>1. Direct Database Query</h2>";
try {
    require_once 'api/config.php';
    $db = Database::getInstance();
    
    $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
    echo "✓ Database query successful<br>";
    echo "Settings found: " . count($settings) . "<br>";
    
    foreach ($settings as $setting) {
        echo "- {$setting['setting_key']}: {$setting['setting_value']}<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: API endpoint test
echo "<h2>2. API Endpoint Test</h2>";
$url = "http://localhost/ambicamarketing/api/settings.php";

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
        echo "✓ API call successful<br>";
        $data = json_decode($response, true);
        if ($data) {
            echo "Response structure: " . json_encode($data) . "<br>";
            if (isset($data['data'])) {
                echo "Settings data:<br>";
                foreach ($data['data'] as $key => $value) {
                    echo "- $key: $value<br>";
                }
            }
        } else {
            echo "Response (not JSON): " . substr($response, 0, 500) . "<br>";
        }
    } else {
        echo "✗ API call failed<br>";
        echo "Response: " . substr($response, 0, 500) . "<br>";
    }
}

// Test 3: Form element mapping
echo "<h2>3. Form Element Mapping Test</h2>";
echo "Expected element IDs:<br>";
echo "- companyName (for company_name)<br>";
echo "- companyEmail (for company_email)<br>";
echo "- companyPhone (for company_phone)<br>";
echo "- companyAddress (for company_address)<br>";
echo "- companyDescription (for company_description)<br>";
echo "- whatsappNumber (for whatsapp_number)<br>";
echo "- workingHours (for working_hours)<br>";

echo "<h2>4. Next Steps</h2>";
echo "1. Check the browser console for debug messages<br>";
echo "2. Verify the settings data is being loaded<br>";
echo "3. Check if the form elements exist with correct IDs<br>";
?> 