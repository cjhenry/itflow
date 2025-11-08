<?php

/**
 * Service Catalog API
 * Provides JSON endpoints for service operations
 */

require_once "../config.php";
require_once "../includes/inc_all.php";
require_once "../agent/includes/inc_services.php";

header('Content-Type: application/json');

// Check permission
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = sanitizeInput($_GET['action'] ?? '');

if ($action == 'get' && isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    $service = getService($mysqli, $service_id);

    if ($service) {
        echo json_encode($service);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Service not found']);
    }
    exit;
}

if ($action == 'list') {
    $active_only = isset($_GET['active']) ? true : false;
    $services = getAllServices($mysqli, $active_only);
    echo json_encode($services);
    exit;
}

if ($action == 'search' && isset($_GET['q'])) {
    $keyword = sanitizeInput($_GET['q']);
    $services = searchServices($mysqli, $keyword, true);
    echo json_encode($services);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);

?>
