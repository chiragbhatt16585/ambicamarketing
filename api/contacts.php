<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Initialize database connection
    $db = Database::getInstance();
    
    switch ($method) {
        case 'GET':
            handleGetContacts($db);
            break;
        case 'POST':
            handleCreateContact($db);
            break;
        case 'PUT':
            handleUpdateContact($db);
            break;
        case 'DELETE':
            handleDeleteContact($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendErrorResponse($e->getMessage(), 500);
}

function handleGetContacts($db) {
    // Get all contacts or specific contact
    if (isset($_GET['id'])) {
        $contact = $db->fetch("SELECT * FROM contacts WHERE id = ?", [$_GET['id']]);
        
        if ($contact) {
            sendSuccessResponse($contact, 'Contact retrieved successfully');
        } else {
            sendErrorResponse('Contact not found', 404);
        }
    } else {
        $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC");
        sendSuccessResponse($contacts, 'Contacts retrieved successfully');
    }
}

function handleCreateContact($db) {
    // Handle form data (from contact form)
    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        // Handle form data
        $data = [
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'email' => isset($_POST['email']) ? $_POST['email'] : '',
            'phone' => isset($_POST['phone']) ? $_POST['phone'] : '',
            'message' => isset($_POST['message']) ? $_POST['message'] : '',
            'product_interest' => isset($_POST['product_interest']) ? $_POST['product_interest'] : ''
        ];
    }
    
    if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
        sendErrorResponse('Missing required fields: name, email, and message are required');
    }
    
    // Validate email
    if (!validateEmail($data['email'])) {
        sendErrorResponse('Invalid email address');
    }
    
    try {
        $db->query(
            "INSERT INTO contacts (name, email, phone, message, product_interest, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [
                sanitizeInput($data['name']),
                sanitizeInput($data['email']),
                sanitizeInput($data['phone'] ?? ''),
                sanitizeInput($data['message']),
                sanitizeInput($data['product_interest'] ?? '')
            ]
        );
        
        $contactId = $db->lastInsertId();
        
        // Send email notification (optional)
        sendEmailNotification($data);
        
        sendSuccessResponse(['id' => $contactId], 'Contact submitted successfully');
    } catch (Exception $e) {
        sendErrorResponse('Error creating contact: ' . $e->getMessage(), 500);
    }
}

function handleUpdateContact($db) {
    requireAdminAuth();
    
    $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
    if (empty($id)) {
        sendErrorResponse('Contact ID is required');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        sendErrorResponse('Invalid data provided');
    }
    
    $allowedFields = ['status', 'name', 'email', 'phone', 'message'];
    $updates = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = sanitizeInput($data[$field]);
        }
    }
    
    if (empty($updates)) {
        sendErrorResponse('No valid fields to update');
    }
    
    $params[] = $id;
    
    try {
        $db->query(
            "UPDATE contacts SET " . implode(', ', $updates) . " WHERE id = ?",
            $params
        );
        
        sendSuccessResponse(null, 'Contact updated successfully');
    } catch (Exception $e) {
        sendErrorResponse('Error updating contact: ' . $e->getMessage(), 500);
    }
}

function handleDeleteContact($db) {
    requireAdminAuth();
    
    $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
    if (empty($id)) {
        sendErrorResponse('Contact ID is required');
    }
    
    try {
        $db->query("DELETE FROM contacts WHERE id = ?", [$id]);
        sendSuccessResponse(null, 'Contact deleted successfully');
    } catch (Exception $e) {
        sendErrorResponse('Error deleting contact: ' . $e->getMessage(), 500);
    }
}

// Email notification function
function sendEmailNotification($contactData) {
    $to = 'automation.ambica@gmail.com'; // Ambica Marketing email
    $subject = 'New Contact Form Submission - Ambica Marketing';
    
    $message = "
    New contact form submission received:
    
    Name: {$contactData['name']}
    Email: {$contactData['email']}
    Phone: " . (isset($contactData['phone']) ? $contactData['phone'] : 'Not provided') . "
    Product Interest: " . (isset($contactData['product_interest']) ? $contactData['product_interest'] : 'Not specified') . "
    
    Message:
    {$contactData['message']}
    
    Submitted on: " . date('Y-m-d H:i:s') . "
    ";
    
    $headers = 'From: noreply@ambicamarketing.com' . "\r\n" .
               'Reply-To: ' . $contactData['email'] . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    // Uncomment to enable email notifications
    // mail($to, $subject, $message, $headers);
}
?> 