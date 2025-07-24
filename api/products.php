<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // Initialize database connection
    $db = Database::getInstance();
    
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $db);
            break;
        case 'POST':
            handlePostRequest($action, $db);
            break;
        case 'PUT':
            handlePutRequest($action, $db);
            break;
        case 'DELETE':
            handleDeleteRequest($action, $db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendErrorResponse($e->getMessage(), 500);
}

function handleGetRequest($action, $db) {
    switch ($action) {
        case 'business-categories':
            $categories = $db->fetchAll("SELECT * FROM business_categories WHERE is_active = 1 ORDER BY display_order");
            sendSuccessResponse($categories, 'Business categories retrieved successfully');
            break;
            
        case 'product-categories':
            $businessId = $_GET['business_id'] ?? null;
            if ($businessId) {
                $categories = $db->fetchAll(
                    "SELECT * FROM product_categories WHERE business_category_id = ? AND is_active = 1 ORDER BY display_order",
                    [$businessId]
                );
            } else {
                $categories = $db->fetchAll("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY display_order");
            }
            sendSuccessResponse($categories, 'Product categories retrieved successfully');
            break;
            
        case 'products':
            $businessId = $_GET['business_id'] ?? null;
            $categoryId = $_GET['category_id'] ?? null;
            $featured = $_GET['featured'] ?? null;
            
            $sql = "SELECT p.*, bc.name as business_category_name, pc.name as product_category_name 
                   FROM products p 
                   JOIN business_categories bc ON p.business_category_id = bc.id 
                   LEFT JOIN product_categories pc ON p.product_category_id = pc.id 
                   WHERE p.is_active = 1";
            $params = [];
            
            if ($businessId) {
                $sql .= " AND p.business_category_id = ?";
                $params[] = $businessId;
            }
            
            if ($categoryId) {
                $sql .= " AND p.product_category_id = ?";
                $params[] = $categoryId;
            }
            
            if ($featured === 'true') {
                $sql .= " AND p.is_featured = 1";
            }
            
            $sql .= " ORDER BY p.display_order, p.name";
            
            $products = $db->fetchAll($sql, $params);
            sendSuccessResponse($products, 'Products retrieved successfully');
            break;
            
        case 'product':
            $id = $_GET['id'] ?? null;
            $slug = $_GET['slug'] ?? null;
            
            if (!$id && !$slug) {
                sendErrorResponse('Product ID or slug required');
            }
            
            $sql = "SELECT p.*, bc.name as business_category_name, pc.name as product_category_name 
                   FROM products p 
                   JOIN business_categories bc ON p.business_category_id = bc.id 
                   LEFT JOIN product_categories pc ON p.product_category_id = pc.id 
                   WHERE p.is_active = 1";
            $params = [];
            
            if ($id) {
                $sql .= " AND p.id = ?";
                $params[] = $id;
            } else {
                $sql .= " AND p.slug = ?";
                $params[] = $slug;
            }
            
            $product = $db->fetch($sql, $params);
            if (!$product) {
                sendErrorResponse('Product not found', 404);
            }
            
            // Get product images
            $images = $db->fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order", [$product['id']]);
            $product['images'] = $images;
            
            sendSuccessResponse($product, 'Product retrieved successfully');
            break;
            
        case 'stats':
            $stats = $db->fetch("CALL GetProductStats()");
            sendSuccessResponse($stats, 'Product statistics retrieved successfully');
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
    }
}

function handlePostRequest($action, $db) {
    requireAdminAuth();
    
    switch ($action) {
        case 'business-category':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $name = sanitizeInput($data['name'] ?? '');
            $description = sanitizeInput($data['description'] ?? '');
            $iconClass = sanitizeInput($data['icon_class'] ?? '');
            $displayOrder = intval($data['display_order'] ?? 0);
            
            if (empty($name)) {
                sendErrorResponse('Business category name is required');
            }
            
            $slug = generateSlug($name);
            
            $db->query(
                "INSERT INTO business_categories (name, slug, description, icon_class, display_order) VALUES (?, ?, ?, ?, ?)",
                [$name, $slug, $description, $iconClass, $displayOrder]
            );
            
            $id = $db->lastInsertId();
            sendSuccessResponse(['id' => $id], 'Business category created successfully');
            break;
            
        case 'product-category':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $businessCategoryId = intval($data['business_category_id'] ?? 0);
            $name = sanitizeInput($data['name'] ?? '');
            $description = sanitizeInput($data['description'] ?? '');
            $iconClass = sanitizeInput($data['icon_class'] ?? '');
            $displayOrder = intval($data['display_order'] ?? 0);
            
            if (empty($businessCategoryId) || empty($name)) {
                sendErrorResponse('Business category ID and product category name are required');
            }
            
            $slug = generateSlug($name);
            
            $db->query(
                "INSERT INTO product_categories (business_category_id, name, slug, description, icon_class, display_order) VALUES (?, ?, ?, ?, ?, ?)",
                [$businessCategoryId, $name, $slug, $description, $iconClass, $displayOrder]
            );
            
            $id = $db->lastInsertId();
            sendSuccessResponse(['id' => $id], 'Product category created successfully');
            break;
            
        case 'product':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $businessCategoryId = intval($data['business_category_id'] ?? 0);
            $productCategoryId = intval($data['product_category_id'] ?? 0);
            $name = sanitizeInput($data['name'] ?? '');
            $description = sanitizeInput($data['description'] ?? '');
            $shortDescription = sanitizeInput($data['short_description'] ?? '');
            $price = floatval($data['price'] ?? 0);
            $isFeatured = boolval($data['is_featured'] ?? false);
            $displayOrder = intval($data['display_order'] ?? 0);
            
            if (empty($businessCategoryId) || empty($name)) {
                sendErrorResponse('Business category ID and product name are required');
            }
            
            $slug = generateSlug($name);
            
            // Handle image upload
            $imageUrl = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $businessCategory = $db->fetch("SELECT slug FROM business_categories WHERE id = ?", [$businessCategoryId]);
                $categorySlug = $businessCategory['slug'] ?? 'general';
                $imageUrl = uploadImage($_FILES['image'], $categorySlug);
            }
            
            $db->query(
                "INSERT INTO products (business_category_id, product_category_id, name, slug, description, short_description, image_url, price, is_featured, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$businessCategoryId, $productCategoryId, $name, $slug, $description, $shortDescription, $imageUrl, $price, $isFeatured, $displayOrder]
            );
            
            $id = $db->lastInsertId();
            sendSuccessResponse(['id' => $id], 'Product created successfully');
            break;
            
        case 'product-image':
            $productId = intval($_POST['product_id'] ?? 0);
            $altText = sanitizeInput($_POST['alt_text'] ?? '');
            $isPrimary = boolval($_POST['is_primary'] ?? false);
            $displayOrder = intval($_POST['display_order'] ?? 0);
            
            if (empty($productId) || !isset($_FILES['image'])) {
                sendErrorResponse('Product ID and image are required');
            }
            
            // Get business category for folder structure
            $product = $db->fetch("SELECT bc.slug FROM products p JOIN business_categories bc ON p.business_category_id = bc.id WHERE p.id = ?", [$productId]);
            if (!$product) {
                sendErrorResponse('Product not found');
            }
            
            $imageUrl = uploadImage($_FILES['image'], $product['slug']);
            
            // If this is primary, unset other primary images
            if ($isPrimary) {
                $db->query("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$productId]);
            }
            
            $db->query(
                "INSERT INTO product_images (product_id, image_url, alt_text, is_primary, display_order) VALUES (?, ?, ?, ?, ?)",
                [$productId, $imageUrl, $altText, $isPrimary, $displayOrder]
            );
            
            $id = $db->lastInsertId();
            sendSuccessResponse(['id' => $id], 'Product image uploaded successfully');
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
    }
}

function handlePutRequest($action, $db) {
    requireAdminAuth();
    
    switch ($action) {
        case 'business-category':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                sendErrorResponse('Business category ID is required');
            }
            
            $name = sanitizeInput($data['name'] ?? '');
            $description = sanitizeInput($data['description'] ?? '');
            $iconClass = sanitizeInput($data['icon_class'] ?? '');
            $displayOrder = intval($data['display_order'] ?? 0);
            $isActive = boolval($data['is_active'] ?? true);
            
            if (empty($name)) {
                sendErrorResponse('Business category name is required');
            }
            
            $slug = generateSlug($name);
            
            $db->query(
                "UPDATE business_categories SET name = ?, slug = ?, description = ?, icon_class = ?, display_order = ?, is_active = ? WHERE id = ?",
                [$name, $slug, $description, $iconClass, $displayOrder, $isActive, $id]
            );
            
            sendSuccessResponse(null, 'Business category updated successfully');
            break;
            
        case 'product-category':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                sendErrorResponse('Product category ID is required');
            }
            
            $businessCategoryId = intval($data['business_category_id'] ?? 0);
            $name = sanitizeInput($data['name'] ?? '');
            $description = sanitizeInput($data['description'] ?? '');
            $iconClass = sanitizeInput($data['icon_class'] ?? '');
            $displayOrder = intval($data['display_order'] ?? 0);
            $isActive = boolval($data['is_active'] ?? true);
            
            if (empty($businessCategoryId) || empty($name)) {
                sendErrorResponse('Business category ID and product category name are required');
            }
            
            $slug = generateSlug($name);
            
            $db->query(
                "UPDATE product_categories SET business_category_id = ?, name = ?, slug = ?, description = ?, icon_class = ?, display_order = ?, is_active = ? WHERE id = ?",
                [$businessCategoryId, $name, $slug, $description, $iconClass, $displayOrder, $isActive, $id]
            );
            
            sendSuccessResponse(null, 'Product category updated successfully');
            break;
            
        case 'product':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                sendErrorResponse('Product ID is required');
            }
            
            $businessCategoryId = intval($data['business_category_id'] ?? 0);
            $productCategoryId = intval($data['product_category_id'] ?? 0);
            $name = sanitizeInput($data['name'] ?? '');
            $description = sanitizeInput($data['description'] ?? '');
            $shortDescription = sanitizeInput($data['short_description'] ?? '');
            $price = floatval($data['price'] ?? 0);
            $isFeatured = boolval($data['is_featured'] ?? false);
            $isActive = boolval($data['is_active'] ?? true);
            $displayOrder = intval($data['display_order'] ?? 0);
            
            if (empty($businessCategoryId) || empty($name)) {
                sendErrorResponse('Business category ID and product name are required');
            }
            
            $slug = generateSlug($name);
            
            $db->query(
                "UPDATE products SET business_category_id = ?, product_category_id = ?, name = ?, slug = ?, description = ?, short_description = ?, price = ?, is_featured = ?, is_active = ?, display_order = ? WHERE id = ?",
                [$businessCategoryId, $productCategoryId, $name, $slug, $description, $shortDescription, $price, $isFeatured, $isActive, $displayOrder, $id]
            );
            
            sendSuccessResponse(null, 'Product updated successfully');
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
    }
}

function handleDeleteRequest($action, $db) {
    requireAdminAuth();
    
    switch ($action) {
        case 'business-category':
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                sendErrorResponse('Business category ID is required');
            }
            
            // Check if category has products
            $productCount = $db->fetch("SELECT COUNT(*) as count FROM products WHERE business_category_id = ?", [$id])['count'];
            if ($productCount > 0) {
                sendErrorResponse('Cannot delete business category with existing products');
            }
            
            $db->query("DELETE FROM business_categories WHERE id = ?", [$id]);
            sendSuccessResponse(null, 'Business category deleted successfully');
            break;
            
        case 'product-category':
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                sendErrorResponse('Product category ID is required');
            }
            
            // Check if category has products
            $productCount = $db->fetch("SELECT COUNT(*) as count FROM products WHERE product_category_id = ?", [$id])['count'];
            if ($productCount > 0) {
                sendErrorResponse('Cannot delete product category with existing products');
            }
            
            $db->query("DELETE FROM product_categories WHERE id = ?", [$id]);
            sendSuccessResponse(null, 'Product category deleted successfully');
            break;
            
        case 'product':
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                sendErrorResponse('Product ID is required');
            }
            
            // Get product images to delete
            $images = $db->fetchAll("SELECT image_url FROM product_images WHERE product_id = ?", [$id]);
            foreach ($images as $image) {
                deleteImage($image['image_url']);
            }
            
            // Delete product images first
            $db->query("DELETE FROM product_images WHERE product_id = ?", [$id]);
            
            // Delete product
            $db->query("DELETE FROM products WHERE id = ?", [$id]);
            sendSuccessResponse(null, 'Product deleted successfully');
            break;
            
        case 'product-image':
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                sendErrorResponse('Product image ID is required');
            }
            
            // Get image path before deletion
            $image = $db->fetch("SELECT image_url FROM product_images WHERE id = ?", [$id]);
            if ($image) {
                deleteImage($image['image_url']);
            }
            
            $db->query("DELETE FROM product_images WHERE id = ?", [$id]);
            sendSuccessResponse(null, 'Product image deleted successfully');
            break;
            
        default:
            sendErrorResponse('Invalid action', 400);
    }
}
?> 