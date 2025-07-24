<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Initialize database connection
    $db = Database::getInstance();
    
    switch ($method) {
        case 'GET':
            handleGetSettings($db);
            break;
        case 'POST':
            handleUpdateSettings($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendErrorResponse($e->getMessage(), 500);
}

function handleGetSettings($db) {
    try {
        $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
        
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['setting_key']] = $setting['setting_value'];
        }
        
        sendSuccessResponse($settingsArray, 'Settings retrieved successfully');
    } catch (Exception $e) {
        sendErrorResponse('Error retrieving settings: ' . $e->getMessage(), 500);
    }
}

function handleUpdateSettings($db) {
    requireAdminAuth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        sendErrorResponse('Invalid data provided');
    }
    
    $allowedSettings = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'company_description',
        'whatsapp_number',
        'working_hours',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url'
    ];
    
    try {
        foreach ($allowedSettings as $settingKey) {
            if (isset($data[$settingKey])) {
                $value = sanitizeInput($data[$settingKey]);
                
                // Check if setting exists
                $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$settingKey]);
                
                if ($existing) {
                    // Update existing setting
                    $db->query(
                        "UPDATE settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?",
                        [$value, $settingKey]
                    );
                } else {
                    // Insert new setting
                    $db->query(
                        "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)",
                        [$settingKey, $value]
                    );
                }
            }
        }
        
        sendSuccessResponse(null, 'Settings updated successfully');
    } catch (Exception $e) {
        sendErrorResponse('Error updating settings: ' . $e->getMessage(), 500);
    }
}
?> 