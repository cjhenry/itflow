<?php

/**
 * Service Catalog POST Handler
 * Handles CRUD operations for services
 */

defined('FROM_POST_HANDLER') || die('Direct access not allowed');

require_once "includes/inc_services.php";

// Permission check - admin only
enforceUserPermission('module_client', 2);

// Create Service
if (isset($_POST['add_service'])) {
    $result = createService($mysqli, [
        'name' => $_POST['service_name'] ?? '',
        'description' => $_POST['service_description'] ?? '',
        'rate' => $_POST['service_default_rate'] ?? 0,
        'category' => $_POST['service_category'] ?? '',
        'unit' => $_POST['service_default_unit'] ?? 'Hour',
        'tax_id' => $_POST['service_tax_id'] ?? 0,
        'minimum_hours' => $_POST['service_minimum_hours'] ?? 0,
        'sort_order' => $_POST['service_sort_order'] ?? 0
    ]);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service created successfully";
        header('Location: services.php');
    } else {
        $_SESSION['error_message'] = $result['error'];
        header('Location: services.php?action=add');
    }
    exit;
}

// Update Service
if (isset($_POST['edit_service'])) {
    $service_id = intval($_POST['service_id'] ?? 0);

    if ($service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: services.php');
        exit;
    }

    $result = updateService($mysqli, $service_id, [
        'name' => $_POST['service_name'] ?? '',
        'description' => $_POST['service_description'] ?? '',
        'rate' => $_POST['service_default_rate'] ?? 0,
        'category' => $_POST['service_category'] ?? '',
        'unit' => $_POST['service_default_unit'] ?? 'Hour',
        'tax_id' => $_POST['service_tax_id'] ?? 0,
        'minimum_hours' => $_POST['service_minimum_hours'] ?? 0,
        'sort_order' => $_POST['service_sort_order'] ?? 0
    ]);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service updated successfully";
        header('Location: services.php?view=detail&service_id=' . $service_id);
    } else {
        $_SESSION['error_message'] = $result['error'];
        header('Location: services.php?action=edit&service_id=' . $service_id);
    }
    exit;
}

// Archive Service
if (isset($_POST['archive_service'])) {
    $service_id = intval($_POST['service_id'] ?? 0);

    if ($service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: services.php');
        exit;
    }

    $result = archiveService($mysqli, $service_id);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service archived successfully";
        header('Location: services.php');
    } else {
        $_SESSION['error_message'] = $result['error'];
        header('Location: services.php');
    }
    exit;
}

// Restore Service
if (isset($_POST['restore_service'])) {
    $service_id = intval($_POST['service_id'] ?? 0);

    if ($service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: services.php');
        exit;
    }

    $result = restoreService($mysqli, $service_id);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service restored successfully";
        header('Location: services.php?view=all');
    } else {
        $_SESSION['error_message'] = $result['error'];
        header('Location: services.php?view=all');
    }
    exit;
}

// Delete Service
if (isset($_POST['delete_service'])) {
    $service_id = intval($_POST['service_id'] ?? 0);

    if ($service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: services.php');
        exit;
    }

    $result = deleteService($mysqli, $service_id);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service deleted successfully";
        header('Location: services.php');
    } else {
        $_SESSION['error_message'] = $result['error'];
        header('Location: services.php');
    }
    exit;
}

// Clone Service
if (isset($_POST['clone_service'])) {
    $service_id = intval($_POST['service_id'] ?? 0);
    $new_name = sanitizeInput($_POST['clone_name'] ?? '');

    if ($service_id <= 0) {
        $_SESSION['error_message'] = "Invalid service ID";
        header('Location: services.php');
        exit;
    }

    $result = cloneService($mysqli, $service_id, $new_name);

    if ($result['success']) {
        $_SESSION['success_message'] = "Service cloned successfully";
        header('Location: services.php');
    } else {
        $_SESSION['error_message'] = $result['error'];
        header('Location: services.php');
    }
    exit;
}

// Default: redirect to services list
header('Location: services.php');
exit;

?>
