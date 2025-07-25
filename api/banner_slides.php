<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    switch ($method) {
        case 'GET':
            handleGetSlides($db);
            break;
        case 'POST':
            requireAdminAuth();
            handleAddOrEditSlide($db);
            break;
        case 'DELETE':
            requireAdminAuth();
            handleDeleteSlide($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendErrorResponse($e->getMessage(), 500);
}

function handleGetSlides($db) {
    $slides = $db->fetchAll("SELECT * FROM banner_slides ORDER BY display_order, id");
    sendSuccessResponse($slides, 'Slides retrieved');
}

function handleAddOrEditSlide($db) {
    $id = intval($_POST['id'] ?? 0);
    $title = sanitizeInput($_POST['title'] ?? '');
    $subtitle = sanitizeInput($_POST['subtitle'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $image_url = uploadImage($_FILES['image'], '../assets/images/banner');
    } else if (!empty($_POST['image_url'])) {
        $image_url = sanitizeInput($_POST['image_url']);
    }
    if ($id) {
        // Edit
        $existing = $db->fetch("SELECT * FROM banner_slides WHERE id=?", [$id]);
        if (!$existing) sendErrorResponse('Slide not found');
        if (!$image_url) $image_url = $existing['image_url'];
        $db->query("UPDATE banner_slides SET image_url=?, title=?, subtitle=?, display_order=?, is_active=? WHERE id=?", [
            $image_url, $title, $subtitle, $display_order, $is_active, $id
        ]);
        sendSuccessResponse(null, 'Slide updated');
    } else {
        // Add
        if (!$image_url) {
            sendErrorResponse('Image is required');
        }
        $db->query("INSERT INTO banner_slides (image_url, title, subtitle, display_order, is_active) VALUES (?, ?, ?, ?, ?)", [
            $image_url, $title, $subtitle, $display_order, $is_active
        ]);
        sendSuccessResponse(null, 'Slide added');
    }
}

function handleDeleteSlide($db) {
    parse_str(file_get_contents('php://input'), $del_vars);
    $id = intval($del_vars['id'] ?? 0);
    if (!$id) sendErrorResponse('Slide ID required');
    $db->query("DELETE FROM banner_slides WHERE id=?", [$id]);
    sendSuccessResponse(null, 'Slide deleted');
} 