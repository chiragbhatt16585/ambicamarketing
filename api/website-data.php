<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Initialize database connection
    $db = Database::getInstance();
    
    switch ($method) {
        case 'GET':
            handleGetWebsiteData($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendErrorResponse($e->getMessage(), 500);
}

function handleGetWebsiteData($db) {
    try {
        // Fetch company settings
        $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
        $companyData = [];
        foreach ($settings as $setting) {
            $companyData[$setting['setting_key']] = $setting['setting_value'];
        }
        
        // Fetch business categories
        $businessCategories = $db->fetchAll("SELECT * FROM business_categories WHERE is_active = TRUE ORDER BY display_order");
        
        // Fetch featured products
        $featuredProducts = $db->fetchAll("
            SELECT p.*, bc.name as business_category_name, pc.name as product_category_name 
            FROM products p 
            JOIN business_categories bc ON p.business_category_id = bc.id 
            LEFT JOIN product_categories pc ON p.product_category_id = pc.id 
            WHERE p.is_featured = TRUE AND p.is_active = TRUE 
            ORDER BY p.display_order 
            LIMIT 6
        ");
        
        // Fetch contact statistics
        $recentContacts = $db->fetch("SELECT COUNT(*) as count FROM contacts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $totalContacts = $db->fetch("SELECT COUNT(*) as count FROM contacts");
        
        // Fetch all products for product listing
        $allProducts = $db->fetchAll("
            SELECT p.*, bc.name as business_category_name, pc.name as product_category_name 
            FROM products p 
            JOIN business_categories bc ON p.business_category_id = bc.id 
            LEFT JOIN product_categories pc ON p.product_category_id = pc.id 
            WHERE p.is_active = TRUE 
            ORDER BY p.display_order
        ");
        
        // Fetch product categories
        $productCategories = $db->fetchAll("
            SELECT pc.*, bc.name as business_category_name 
            FROM product_categories pc 
            JOIN business_categories bc ON pc.business_category_id = bc.id 
            WHERE pc.is_active = TRUE 
            ORDER BY bc.display_order, pc.display_order
        ");
        
        // Fetch banner slides
        $bannerSlides = $db->fetchAll("SELECT image_url, title, subtitle FROM banner_slides WHERE is_active = TRUE ORDER BY display_order, id");
        
        // Prepare response data
        $responseData = [
            'company' => $companyData,
            'business_categories' => $businessCategories,
            'featured_products' => $featuredProducts,
            'all_products' => $allProducts,
            'product_categories' => $productCategories,
            'banner_slides' => $bannerSlides,
            'statistics' => [
                'total_contacts' => $totalContacts['count'],
                'recent_contacts' => $recentContacts['count']
            ]
        ];
        
        sendSuccessResponse($responseData, 'Website data retrieved successfully');
    } catch (Exception $e) {
        sendErrorResponse('Error retrieving website data: ' . $e->getMessage(), 500);
    }
}
?> 