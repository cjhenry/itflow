<?php

/**
 * Service Catalog Helper Functions
 * Core CRUD and utility functions for service management
 */

/**
 * Create a new service
 */
function createService($mysqli, $data) {
    $service_name = sanitizeInput($data['name'] ?? '');
    $service_description = sanitizeInput($data['description'] ?? '');
    $service_default_rate = floatval($data['rate'] ?? 0);
    $service_category = sanitizeInput($data['category'] ?? '');
    $service_default_unit = sanitizeInput($data['unit'] ?? 'Hour');
    $service_tax_id = intval($data['tax_id'] ?? 0) ?: 'NULL';
    $service_minimum_hours = floatval($data['minimum_hours'] ?? 0);
    $service_sort_order = intval($data['sort_order'] ?? 0);
    $service_created_by = intval($_SESSION['user_id'] ?? 0);

    if (empty($service_name) || $service_default_rate <= 0) {
        return ['success' => false, 'error' => 'Service name and rate are required'];
    }

    // Check if name already exists
    $result = mysqli_query($mysqli, "SELECT service_id FROM service_catalog WHERE service_name = '$service_name'");
    if (mysqli_num_rows($result) > 0) {
        return ['success' => false, 'error' => 'Service name already exists'];
    }

    $sql = "INSERT INTO service_catalog (
        service_name,
        service_description,
        service_default_rate,
        service_category,
        service_default_unit,
        service_tax_id,
        service_minimum_hours,
        service_sort_order,
        service_status,
        service_created_by
    ) VALUES (
        '$service_name',
        '$service_description',
        $service_default_rate,
        '$service_category',
        '$service_default_unit',
        " . ($service_tax_id === 'NULL' ? 'NULL' : $service_tax_id) . ",
        $service_minimum_hours,
        $service_sort_order,
        'Active',
        $service_created_by
    )";

    if (mysqli_query($mysqli, $sql)) {
        $service_id = mysqli_insert_id($mysqli);
        logAction('service_created', 0, "Service created: $service_name ($service_id)");
        return ['success' => true, 'service_id' => $service_id];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Get single service
 */
function getService($mysqli, $service_id) {
    $service_id = intval($service_id);
    $result = mysqli_query($mysqli, "SELECT * FROM service_catalog WHERE service_id = $service_id");
    return mysqli_fetch_assoc($result);
}

/**
 * Get all services (active or all)
 */
function getAllServices($mysqli, $active_only = false, $order_by = 'service_sort_order') {
    $where = '';
    if ($active_only) {
        $where = "WHERE service_status = 'Active'";
    }

    $result = mysqli_query($mysqli,
        "SELECT * FROM service_catalog $where ORDER BY $order_by ASC");

    $services = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    return $services;
}

/**
 * Get services by category
 */
function getServicesByCategory($mysqli, $category) {
    $category = sanitizeInput($category);
    $result = mysqli_query($mysqli,
        "SELECT * FROM service_catalog
         WHERE service_category = '$category'
         AND service_status = 'Active'
         ORDER BY service_sort_order ASC");

    $services = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    return $services;
}

/**
 * Get active services only
 */
function getActiveServices($mysqli) {
    return getAllServices($mysqli, true);
}

/**
 * Update service
 */
function updateService($mysqli, $service_id, $data) {
    $service_id = intval($service_id);
    $service_name = sanitizeInput($data['name'] ?? '');
    $service_description = sanitizeInput($data['description'] ?? '');
    $service_default_rate = floatval($data['rate'] ?? 0);
    $service_category = sanitizeInput($data['category'] ?? '');
    $service_default_unit = sanitizeInput($data['unit'] ?? 'Hour');
    $service_tax_id = intval($data['tax_id'] ?? 0) ?: 'NULL';
    $service_minimum_hours = floatval($data['minimum_hours'] ?? 0);
    $service_sort_order = intval($data['sort_order'] ?? 0);

    if (empty($service_name) || $service_default_rate <= 0) {
        return ['success' => false, 'error' => 'Service name and rate are required'];
    }

    $sql = "UPDATE service_catalog SET
        service_name = '$service_name',
        service_description = '$service_description',
        service_default_rate = $service_default_rate,
        service_category = '$service_category',
        service_default_unit = '$service_default_unit',
        service_tax_id = " . ($service_tax_id === 'NULL' ? 'NULL' : $service_tax_id) . ",
        service_minimum_hours = $service_minimum_hours,
        service_sort_order = $service_sort_order
        WHERE service_id = $service_id";

    if (mysqli_query($mysqli, $sql)) {
        logAction('service_updated', 0, "Service updated: $service_name ($service_id)");
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Archive service (soft delete)
 */
function archiveService($mysqli, $service_id) {
    $service_id = intval($service_id);

    $sql = "UPDATE service_catalog
            SET service_status = 'Archived'
            WHERE service_id = $service_id";

    if (mysqli_query($mysqli, $sql)) {
        $service = getService($mysqli, $service_id);
        logAction('service_archived', 0, "Service archived: " . $service['service_name']);
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Restore archived service
 */
function restoreService($mysqli, $service_id) {
    $service_id = intval($service_id);

    $sql = "UPDATE service_catalog
            SET service_status = 'Active'
            WHERE service_id = $service_id";

    if (mysqli_query($mysqli, $sql)) {
        $service = getService($mysqli, $service_id);
        logAction('service_restored', 0, "Service restored: " . $service['service_name']);
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Delete service (hard delete - only if not referenced)
 */
function deleteService($mysqli, $service_id) {
    $service_id = intval($service_id);
    $service = getService($mysqli, $service_id);

    // Check if service is referenced
    $client_count = mysqli_fetch_row(mysqli_query($mysqli,
        "SELECT COUNT(*) FROM client_services WHERE service_id = $service_id"));
    $agreement_count = mysqli_fetch_row(mysqli_query($mysqli,
        "SELECT COUNT(*) FROM agreement_services WHERE service_id = $service_id"));
    $invoice_count = mysqli_fetch_row(mysqli_query($mysqli,
        "SELECT COUNT(*) FROM invoice_items WHERE item_service_id = $service_id"));

    $total_refs = $client_count[0] + $agreement_count[0] + $invoice_count[0];

    if ($total_refs > 0) {
        return [
            'success' => false,
            'error' => "Service is in use: $client_count[0] clients, $agreement_count[0] agreements, $invoice_count[0] invoices"
        ];
    }

    $sql = "DELETE FROM service_catalog WHERE service_id = $service_id";

    if (mysqli_query($mysqli, $sql)) {
        logAction('service_deleted', 0, "Service deleted: " . $service['service_name']);
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Clone service
 */
function cloneService($mysqli, $service_id, $new_name = '') {
    $service_id = intval($service_id);
    $service = getService($mysqli, $service_id);

    if (!$service) {
        return ['success' => false, 'error' => 'Service not found'];
    }

    if (empty($new_name)) {
        $new_name = $service['service_name'] . ' (Copy)';
    }

    $data = [
        'name' => $new_name,
        'description' => $service['service_description'],
        'rate' => $service['service_default_rate'],
        'category' => $service['service_category'],
        'unit' => $service['service_default_unit'],
        'tax_id' => $service['service_tax_id'],
        'minimum_hours' => $service['service_minimum_hours'],
        'sort_order' => $service['service_sort_order'] + 1
    ];

    $result = createService($mysqli, $data);
    if ($result['success']) {
        logAction('service_cloned', 0, "Service cloned: {$service['service_name']} -> $new_name");
    }

    return $result;
}

/**
 * Get service usage count
 */
function getServiceUsageCount($mysqli, $service_id) {
    $service_id = intval($service_id);

    $client_count = mysqli_fetch_row(mysqli_query($mysqli,
        "SELECT COUNT(DISTINCT client_id) FROM client_services WHERE service_id = $service_id"));
    $agreement_count = mysqli_fetch_row(mysqli_query($mysqli,
        "SELECT COUNT(DISTINCT agreement_id) FROM agreement_services WHERE service_id = $service_id"));

    return [
        'clients' => $client_count[0],
        'agreements' => $agreement_count[0]
    ];
}

/**
 * Get distinct categories
 */
function getServiceCategories($mysqli) {
    $result = mysqli_query($mysqli,
        "SELECT DISTINCT service_category FROM service_catalog
         WHERE service_category IS NOT NULL AND service_category != ''
         ORDER BY service_category ASC");

    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row['service_category'];
    }

    return $categories;
}

/**
 * Search services
 */
function searchServices($mysqli, $keyword, $active_only = false) {
    $keyword = sanitizeInput($keyword);
    $where = "WHERE (service_name LIKE '%$keyword%' OR service_description LIKE '%$keyword%')";

    if ($active_only) {
        $where .= " AND service_status = 'Active'";
    }

    $result = mysqli_query($mysqli,
        "SELECT * FROM service_catalog $where ORDER BY service_sort_order ASC");

    $services = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    return $services;
}

?>
