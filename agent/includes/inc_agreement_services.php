<?php

/**
 * Agreement Services Helper Functions
 * Manages services assigned to agreements with rate overrides and hour allocation
 */

require_once "inc_services.php";
require_once "inc_client_services.php";

/**
 * Get all services for an agreement
 */
function getAgreementServices($mysqli, $agreement_id) {
    $agreement_id = intval($agreement_id);

    $sql = "SELECT
        ags.agreement_service_id,
        ags.service_id,
        ags.agreement_service_custom_rate,
        sc.service_name,
        sc.service_description,
        sc.service_default_rate,
        sc.service_default_unit,
        ash.service_hours_allocated,
        ash.service_hours_used,
        (ash.service_hours_allocated - ash.service_hours_used) as service_hours_remaining
    FROM agreement_services ags
    LEFT JOIN service_catalog sc ON ags.service_id = sc.service_id
    LEFT JOIN agreement_service_hours ash ON ags.agreement_id = ash.agreement_id
        AND ags.service_id = ash.service_id
    WHERE ags.agreement_id = $agreement_id
    ORDER BY sc.service_name ASC";

    $result = mysqli_query($mysqli, $sql);
    $services = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    return $services;
}

/**
 * Get single agreement service
 */
function getAgreementService($mysqli, $agreement_service_id) {
    $agreement_service_id = intval($agreement_service_id);

    $result = mysqli_query($mysqli,
        "SELECT ags.*, sc.service_name, sc.service_default_rate, sc.service_default_unit
         FROM agreement_services ags
         LEFT JOIN service_catalog sc ON ags.service_id = sc.service_id
         WHERE ags.agreement_service_id = $agreement_service_id");

    return mysqli_fetch_assoc($result);
}

/**
 * Add service to agreement (with optional rate override)
 */
function addAgreementService($mysqli, $agreement_id, $service_id, $custom_rate = null) {
    $agreement_id = intval($agreement_id);
    $service_id = intval($service_id);
    $custom_rate = $custom_rate ? floatval($custom_rate) : null;

    // Check if not already added
    $check = mysqli_query($mysqli,
        "SELECT agreement_service_id FROM agreement_services
         WHERE agreement_id = $agreement_id AND service_id = $service_id");

    if (mysqli_num_rows($check) > 0) {
        return ['success' => false, 'error' => 'Service already added to agreement'];
    }

    $rate_sql = $custom_rate ? $custom_rate : 'NULL';

    $sql = "INSERT INTO agreement_services (
        agreement_id,
        service_id,
        agreement_service_custom_rate
    ) VALUES ($agreement_id, $service_id, $rate_sql)";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true, 'agreement_service_id' => mysqli_insert_id($mysqli)];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Update agreement service rate override
 */
function updateAgreementServiceRate($mysqli, $agreement_service_id, $custom_rate) {
    $agreement_service_id = intval($agreement_service_id);
    $custom_rate = $custom_rate ? floatval($custom_rate) : 'NULL';

    $sql = "UPDATE agreement_services
            SET agreement_service_custom_rate = $custom_rate
            WHERE agreement_service_id = $agreement_service_id";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Remove service from agreement
 */
function removeAgreementService($mysqli, $agreement_service_id) {
    $agreement_service_id = intval($agreement_service_id);

    // Also delete associated hour allocation
    $service = getAgreementService($mysqli, $agreement_service_id);
    if ($service) {
        mysqli_query($mysqli,
            "DELETE FROM agreement_service_hours
             WHERE agreement_id = " . $service['agreement_id'] . "
             AND service_id = " . $service['service_id']);
    }

    $sql = "DELETE FROM agreement_services WHERE agreement_service_id = $agreement_service_id";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Get service rate for agreement (hierarchy: agreement > client > master)
 */
function getAgreementServiceRate($mysqli, $agreement_id, $service_id, $client_id = null) {
    $agreement_id = intval($agreement_id);
    $service_id = intval($service_id);

    // Check agreement override rate
    $result = mysqli_query($mysqli,
        "SELECT agreement_service_custom_rate FROM agreement_services
         WHERE agreement_id = $agreement_id AND service_id = $service_id");

    $row = mysqli_fetch_assoc($result);
    if ($row && $row['agreement_service_custom_rate'] > 0) {
        return $row['agreement_service_custom_rate'];
    }

    // Fall back to client rate if provided
    if ($client_id) {
        return getClientServiceRate($mysqli, intval($client_id), $service_id);
    }

    // Fall back to master default rate
    $result = mysqli_query($mysqli,
        "SELECT service_default_rate FROM service_catalog WHERE service_id = $service_id");

    $row = mysqli_fetch_assoc($result);
    return $row ? $row['service_default_rate'] : 0;
}

/**
 * Allocate hours to service within agreement (Block Hours only)
 */
function allocateAgreementServiceHours($mysqli, $agreement_id, $service_id, $hours_allocated) {
    $agreement_id = intval($agreement_id);
    $service_id = intval($service_id);
    $hours_allocated = floatval($hours_allocated);

    // Check if allocation exists
    $check = mysqli_query($mysqli,
        "SELECT agreement_service_hours_id FROM agreement_service_hours
         WHERE agreement_id = $agreement_id AND service_id = $service_id");

    if (mysqli_num_rows($check) > 0) {
        // Update existing
        $sql = "UPDATE agreement_service_hours
                SET service_hours_allocated = $hours_allocated
                WHERE agreement_id = $agreement_id AND service_id = $service_id";
    } else {
        // Create new
        $sql = "INSERT INTO agreement_service_hours
                (agreement_id, service_id, service_hours_allocated)
                VALUES ($agreement_id, $service_id, $hours_allocated)";
    }

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Get total allocated hours for agreement
 */
function getTotalAllocatedHours($mysqli, $agreement_id) {
    $agreement_id = intval($agreement_id);

    $result = mysqli_query($mysqli,
        "SELECT SUM(service_hours_allocated) as total FROM agreement_service_hours
         WHERE agreement_id = $agreement_id");

    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?: 0;
}

/**
 * Deduct hours from service allocation when time is logged
 */
function deductServiceHours($mysqli, $agreement_id, $service_id, $hours_worked) {
    $agreement_id = intval($agreement_id);
    $service_id = intval($service_id);
    $hours_worked = floatval($hours_worked);

    $sql = "UPDATE agreement_service_hours
            SET service_hours_used = service_hours_used + $hours_worked
            WHERE agreement_id = $agreement_id AND service_id = $service_id";

    return mysqli_query($mysqli, $sql);
}

/**
 * Validate service hour allocation matches total agreement hours
 */
function validateServiceHourAllocation($mysqli, $agreement_id, $total_agreement_hours) {
    $agreement_id = intval($agreement_id);
    $total_agreement_hours = floatval($total_agreement_hours);

    $total_allocated = getTotalAllocatedHours($mysqli, $agreement_id);

    // Allow small floating point variance
    $difference = abs($total_allocated - $total_agreement_hours);

    if ($difference > 0.01) {
        return [
            'valid' => false,
            'error' => "Total allocated hours ($total_allocated) does not match agreement hours ($total_agreement_hours)",
            'allocated' => $total_allocated,
            'expected' => $total_agreement_hours
        ];
    }

    return ['valid' => true];
}

?>
