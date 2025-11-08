<?php

/**
 * Client Services Helper Functions
 * Manages client-level service overrides and custom services
 */

require_once "inc_services.php";

/**
 * Get all services for a client (master + overrides + custom)
 */
function getClientServices($mysqli, $client_id, $included_only = true) {
    $client_id = intval($client_id);

    $sql = "SELECT
        cs.client_service_id,
        cs.service_id,
        COALESCE(cs.client_service_custom_name, sc.service_name) as service_name,
        sc.service_description,
        COALESCE(cs.client_service_custom_rate, sc.service_default_rate) as service_rate,
        sc.service_default_rate,
        cs.client_service_custom_rate,
        sc.service_default_unit,
        cs.client_service_is_custom,
        cs.client_service_included,
        cs.client_service_custom_notes,
        sc.service_category,
        sc.service_status
    FROM client_services cs
    LEFT JOIN service_catalog sc ON cs.service_id = sc.service_id
    WHERE cs.client_id = $client_id";

    if ($included_only) {
        $sql .= " AND cs.client_service_included = TRUE";
    }

    $sql .= " ORDER BY COALESCE(cs.client_service_custom_name, sc.service_name) ASC";

    $result = mysqli_query($mysqli, $sql);
    $services = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    return $services;
}

/**
 * Get single client service
 */
function getClientService($mysqli, $client_service_id) {
    $client_service_id = intval($client_service_id);

    $result = mysqli_query($mysqli,
        "SELECT cs.*, sc.service_name, sc.service_description, sc.service_default_rate, sc.service_default_unit
         FROM client_services cs
         LEFT JOIN service_catalog sc ON cs.service_id = sc.service_id
         WHERE cs.client_service_id = $client_service_id");

    return mysqli_fetch_assoc($result);
}

/**
 * Get client service rate (hierarchy: custom > master)
 */
function getClientServiceRate($mysqli, $client_id, $service_id) {
    $client_id = intval($client_id);
    $service_id = intval($service_id);

    // Check for client custom rate
    $result = mysqli_query($mysqli,
        "SELECT client_service_custom_rate FROM client_services
         WHERE client_id = $client_id AND service_id = $service_id");

    $row = mysqli_fetch_assoc($result);
    if ($row && $row['client_service_custom_rate'] > 0) {
        return $row['client_service_custom_rate'];
    }

    // Fall back to master default rate
    $result = mysqli_query($mysqli,
        "SELECT service_default_rate FROM service_catalog WHERE service_id = $service_id");

    $row = mysqli_fetch_assoc($result);
    return $row ? $row['service_default_rate'] : 0;
}

/**
 * Add master service to client (default or with override)
 */
function addClientService($mysqli, $client_id, $service_id, $custom_rate = null) {
    $client_id = intval($client_id);
    $service_id = intval($service_id);
    $custom_rate = $custom_rate ? floatval($custom_rate) : null;

    // Check if already exists
    $result = mysqli_query($mysqli,
        "SELECT client_service_id FROM client_services
         WHERE client_id = $client_id AND service_id = $service_id");

    if (mysqli_num_rows($result) > 0) {
        return ['success' => false, 'error' => 'Service already added to client'];
    }

    $rate_sql = $custom_rate ? $custom_rate : 'NULL';

    $sql = "INSERT INTO client_services (
        client_id,
        service_id,
        client_service_custom_rate,
        client_service_included
    ) VALUES ($client_id, $service_id, $rate_sql, TRUE)";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true, 'client_service_id' => mysqli_insert_id($mysqli)];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Add custom client service (not in master catalog)
 */
function addCustomClientService($mysqli, $client_id, $data) {
    $client_id = intval($client_id);
    $service_name = sanitizeInput($data['name'] ?? '');
    $service_description = sanitizeInput($data['description'] ?? '');
    $service_rate = floatval($data['rate'] ?? 0);
    $service_category = sanitizeInput($data['category'] ?? '');

    if (empty($service_name) || $service_rate <= 0) {
        return ['success' => false, 'error' => 'Service name and rate are required'];
    }

    $sql = "INSERT INTO client_services (
        client_id,
        service_id,
        client_service_custom_name,
        client_service_custom_description,
        client_service_custom_rate,
        client_service_is_custom,
        client_service_included
    ) VALUES (
        $client_id,
        NULL,
        '$service_name',
        '$service_description',
        $service_rate,
        TRUE,
        TRUE
    )";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true, 'client_service_id' => mysqli_insert_id($mysqli)];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Update client service override
 */
function updateClientService($mysqli, $client_service_id, $data) {
    $client_service_id = intval($client_service_id);
    $custom_rate = isset($data['custom_rate']) && $data['custom_rate'] ? floatval($data['custom_rate']) : null;
    $custom_name = sanitizeInput($data['custom_name'] ?? '');
    $custom_notes = sanitizeInput($data['custom_notes'] ?? '');
    $included = isset($data['included']) ? (bool)$data['included'] : true;

    $updates = [];

    if ($custom_rate !== null) {
        $updates[] = "client_service_custom_rate = $custom_rate";
    }

    if (!empty($custom_name)) {
        $updates[] = "client_service_custom_name = '$custom_name'";
    }

    if (!empty($custom_notes)) {
        $updates[] = "client_service_custom_notes = '$custom_notes'";
    }

    $updates[] = "client_service_included = " . ($included ? 1 : 0);

    if (empty($updates)) {
        return ['success' => false, 'error' => 'No updates provided'];
    }

    $sql = "UPDATE client_services SET " . implode(', ', $updates) . " WHERE client_service_id = $client_service_id";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Remove service from client
 */
function removeClientService($mysqli, $client_service_id) {
    $client_service_id = intval($client_service_id);

    $sql = "DELETE FROM client_services WHERE client_service_id = $client_service_id";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Populate client with all active master services (default)
 */
function populateClientWithDefaultServices($mysqli, $client_id) {
    $client_id = intval($client_id);

    // Get all active master services
    $services = getActiveServices($mysqli);

    foreach ($services as $service) {
        // Check if not already added
        $check = mysqli_query($mysqli,
            "SELECT client_service_id FROM client_services
             WHERE client_id = $client_id AND service_id = " . $service['service_id']);

        if (mysqli_num_rows($check) === 0) {
            addClientService($mysqli, $client_id, $service['service_id']);
        }
    }

    return ['success' => true, 'message' => count($services) . ' services added'];
}

/**
 * Get excluded services for client
 */
function getClientExcludedServices($mysqli, $client_id) {
    $client_id = intval($client_id);

    $result = mysqli_query($mysqli,
        "SELECT * FROM client_services
         WHERE client_id = $client_id AND client_service_included = FALSE");

    $services = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    return $services;
}

?>
