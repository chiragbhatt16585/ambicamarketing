<?php
require_once 'api/config.php';

try {
    $db = Database::getInstance();
    
    // Test database connection
    $testQuery = $db->fetch("SELECT COUNT(*) as count FROM settings");
    $settingsCount = $testQuery['count'];
    
    // Fetch sample data
    $companySettings = $db->fetchAll("SELECT setting_key, setting_value FROM settings LIMIT 5");
    $businessCategories = $db->fetchAll("SELECT COUNT(*) as count FROM business_categories WHERE is_active = TRUE");
    $products = $db->fetchAll("SELECT COUNT(*) as count FROM products WHERE is_active = TRUE");
    $contacts = $db->fetchAll("SELECT COUNT(*) as count FROM contacts");
    
    $status = "SUCCESS";
    $message = "Database connection and queries working correctly";
    
} catch (Exception $e) {
    $status = "ERROR";
    $message = $e->getMessage();
    $settingsCount = 0;
    $companySettings = [];
    $businessCategories = [['count' => 0]];
    $products = [['count' => 0]];
    $contacts = [['count' => 0]];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Website Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .data-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .data-section h3 {
            margin-top: 0;
            color: #495057;
        }
        .data-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .data-item:last-child {
            border-bottom: none;
        }
        .api-test {
            margin-top: 30px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .api-test button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        .api-test button:hover {
            background: #0056b3;
        }
        .api-result {
            margin-top: 15px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dynamic Website Test</h1>
        
        <div class="status <?php echo strtolower($status); ?>">
            <?php echo $status; ?>: <?php echo $message; ?>
        </div>
        
        <div class="data-section">
            <h3>Database Statistics</h3>
            <div class="data-item">
                <span>Settings Count:</span>
                <span><?php echo $settingsCount; ?></span>
            </div>
            <div class="data-item">
                <span>Business Categories:</span>
                <span><?php echo $businessCategories[0]['count']; ?></span>
            </div>
            <div class="data-item">
                <span>Products:</span>
                <span><?php echo $products[0]['count']; ?></span>
            </div>
            <div class="data-item">
                <span>Contacts:</span>
                <span><?php echo $contacts[0]['count']; ?></span>
            </div>
        </div>
        
        <?php if (!empty($companySettings)): ?>
        <div class="data-section">
            <h3>Sample Company Settings</h3>
            <?php foreach ($companySettings as $setting): ?>
            <div class="data-item">
                <span><?php echo htmlspecialchars($setting['setting_key']); ?>:</span>
                <span><?php echo htmlspecialchars(substr($setting['setting_value'], 0, 50)) . (strlen($setting['setting_value']) > 50 ? '...' : ''); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="api-test">
            <h3>API Tests</h3>
            <button onclick="testWebsiteDataAPI()">Test Website Data API</button>
            <button onclick="testSettingsAPI()">Test Settings API</button>
            <button onclick="testProductsAPI()">Test Products API</button>
            <div id="apiResult" class="api-result" style="display: none;"></div>
        </div>
        
        <div class="data-section">
            <h3>Quick Links</h3>
            <div class="data-item">
                <span>Dynamic Homepage:</span>
                <span><a href="index.php">index.php</a></span>
            </div>

            <div class="data-item">
                <span>Static Homepage:</span>
                <span><a href="index.html">index.html</a></span>
            </div>
            <div class="data-item">
                <span>Admin Dashboard:</span>
                <span><a href="admin/dashboard.html">admin/dashboard.html</a></span>
            </div>
        </div>
    </div>

    <script>
        async function testWebsiteDataAPI() {
            try {
                const response = await fetch('api/website-data.php');
                const data = await response.json();
                displayAPIResult('Website Data API', data);
            } catch (error) {
                displayAPIResult('Website Data API', { error: error.message });
            }
        }
        
        async function testSettingsAPI() {
            try {
                const response = await fetch('api/settings.php');
                const data = await response.json();
                displayAPIResult('Settings API', data);
            } catch (error) {
                displayAPIResult('Settings API', { error: error.message });
            }
        }
        
        async function testProductsAPI() {
            try {
                const response = await fetch('api/products.php?action=business-categories');
                const data = await response.json();
                displayAPIResult('Products API', data);
            } catch (error) {
                displayAPIResult('Products API', { error: error.message });
            }
        }
        
        function displayAPIResult(apiName, data) {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.style.display = 'block';
            resultDiv.textContent = `${apiName} Response:\n${JSON.stringify(data, null, 2)}`;
        }
    </script>
</body>
</html> 