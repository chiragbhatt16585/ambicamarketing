<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ambica_marketing');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/ambicamarketing');
define('UPLOAD_PATH', '../assets/images/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// Utility functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateSlug($text) {
    // Convert to lowercase and replace spaces with hyphens
    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

function uploadImage($file, $category = 'general') {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception('No file uploaded');
    }
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        throw new Exception('Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS));
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File too large. Maximum size: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
    }
    // If $category is a path (contains '/'), use as is. Otherwise, treat as subfolder of UPLOAD_PATH
    if (strpos($category, '/') !== false) {
        $uploadDir = rtrim($category, '/') . '/';
        $relativeUrl = ltrim(str_replace(['../', './'], '', $uploadDir), '/') . '';
    } else {
        $uploadDir = UPLOAD_PATH . $category . '/';
        $relativeUrl = 'assets/images/products/' . $category . '/';
    }
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file');
    }
    return $relativeUrl . $filename;
}

function deleteImage($imagePath) {
    $fullPath = '../' . $imagePath;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

// Response helper functions
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse(['error' => $message], $statusCode);
}

function sendSuccessResponse($data = null, $message = 'Success') {
    sendJsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

// Authentication helper
function isAdminLoggedIn() {
    // Check for session-based auth first
    if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
        return true;
    }
    
    // Check for demo token in headers (for API calls from admin dashboard)
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
        return $token === 'demo-token';
    }
    
    // Check for demo token in query string (fallback)
    $token = $_GET['token'] ?? '';
    return $token === 'demo-token';
}

function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        sendErrorResponse('Unauthorized access', 401);
    }
}

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
?> 