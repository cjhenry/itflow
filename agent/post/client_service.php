<?php

/**
 * Client Services POST Handler
 * Handles service management for clients
 */

defined('FROM_POST_HANDLER') || die('Direct access not allowed');

require_once "includes/inc_client_services.php";

// Permission check
enforceUserPermission('module_client', 2);

// Add service to client
if (isset($_POST['add_client_service'])) {
    $client_id = intval($_POST['client_id'] ?? 0);
    $service_id = intval($_POST['service_id'] ?? 0);
    $custom_rate = $_POST['custom_rate'] ?? null;

    if ($client_id <= 0 || $service_id <= 0) {
        $_SESSION['error_message'] = "Invalid client or service ID";
        header('Location: client_overview.php?client_id=' . $client_id);
        exit;
    }

    $result = addClientService($mysqli, $client_id, $service_id, $custom_rate);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service added to client";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: client_overview.php?client_id=' . $client_id . '&tab=services');
    exit;
}

// Add custom service
if (isset($_POST['add_custom_client_service'])) {
    $client_id = intval($_POST['client_id'] ?? 0);

    if ($client_id <= 0) {
        $_SESSION['error_message'] = "Invalid client ID";
        header('Location: client_overview.php?client_id=' . $client_id);
        exit;
    }

    $result = addCustomClientService($mysqli, $client_id, [
        'name' => $_POST['service_name'] ?? '',
        'description' => $_POST['service_description'] ?? '',
        'rate' => $_POST['service_rate'] ?? 0,
        'category' => $_POST['service_category'] ?? ''
    ]);

    if ($result['success']) {
        $_SESSION['success_message'] = "Custom service added";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: client_overview.php?client_id=' . $client_id . '&tab=services');
    exit;
}

// Update client service
if (isset($_POST['update_client_service'])) {
    $client_service_id = intval($_POST['client_service_id'] ?? 0);
    $client_id = intval($_POST['client_id'] ?? 0);

    if ($client_service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: client_overview.php?client_id=' . $client_id);
        exit;
    }

    $result = updateClientService($mysqli, $client_service_id, [
        'custom_rate' => $_POST['custom_rate'] ?? null,
        'custom_name' => $_POST['custom_name'] ?? '',
        'custom_notes' => $_POST['custom_notes'] ?? '',
        'included' => isset($_POST['included'])
    ]);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service updated";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: client_overview.php?client_id=' . $client_id . '&tab=services');
    exit;
}

// Remove service from client
if (isset($_POST['remove_client_service'])) {
    $client_service_id = intval($_POST['client_service_id'] ?? 0);
    $client_id = intval($_POST['client_id'] ?? 0);

    if ($client_service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: client_overview.php?client_id=' . $client_id);
        exit;
    }

    $result = removeClientService($mysqli, $client_service_id);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service removed";
    } else {
        $_SESSION['error_message'] = $result['error'];
    }

    header('Location: client_overview.php?client_id=' . $client_id . '&tab=services');
    exit;
}

// Default redirect
header('Location: clients.php');
exit;

?>
