<?php

/**
 * Invoice Services Helper Functions
 * Integrates service catalog with invoice item generation
 * Handles rate hierarchy lookup for invoices
 */

require_once "inc_agreement_services.php";
require_once "inc_client_services.php";
require_once "inc_services.php";

/**
 * Get service rate for invoice (hierarchy: agreement > client > master)
 * Looks up the effective rate for a service based on context
 *
 * @param mysqli $mysqli
 * @param int $service_id - Service ID
 * @param int $client_id - Client ID (required)
 * @param int $agreement_id - Agreement ID (optional)
 * @return float - Effective rate for service
 */
function getInvoiceServiceRate($mysqli, $service_id, $client_id, $agreement_id = 0) {
    $service_id = intval($service_id);
    $client_id = intval($client_id);
    $agreement_id = intval($agreement_id);

    // 1. Check agreement override if provided
    if ($agreement_id > 0) {
        $rate = getAgreementServiceRate($mysqli, $agreement_id, $service_id, $client_id);
        if ($rate > 0) {
            return $rate;
        }
    }

    // 2. Check client override
    $rate = getClientServiceRate($mysqli, $client_id, $service_id);
    if ($rate > 0) {
        return $rate;
    }

    // 3. Fall back to master default rate
    $result = mysqli_query($mysqli,
        "SELECT service_default_rate FROM service_catalog WHERE service_id = $service_id");

    $row = mysqli_fetch_assoc($result);
    return $row ? floatval($row['service_default_rate']) : 0;
}

/**
 * Get all services available for a client
 * Returns both master services and client-specific custom services
 *
 * @param mysqli $mysqli
 * @param int $client_id
 * @return array - Array of service objects
 */
function getServicesForInvoice($mysqli, $client_id) {
    $client_id = intval($client_id);

    $sql = "SELECT
        COALESCE(cs.client_service_id, 0) as client_service_id,
        sc.service_id,
        sc.service_name,
        sc.service_category,
        COALESCE(cs.client_service_custom_rate, sc.service_default_rate) as effective_rate,
        sc.service_default_rate as master_rate,
        COALESCE(cs.client_service_custom_rate, NULL) as client_rate
    FROM service_catalog sc
    LEFT JOIN client_services cs ON sc.service_id = cs.service_id AND cs.client_id = $client_id
    WHERE sc.service_status = 'Active'
    ORDER BY sc.service_category, sc.service_name ASC";

    $result = mysqli_query($mysqli, $sql);
    $services = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    return $services;
}

/**
 * Create invoice item from service
 * Auto-calculates price based on service rate and provided quantity
 *
 * @param mysqli $mysqli
 * @param int $invoice_id
 * @param int $service_id
 * @param float $quantity - Hours or units
 * @param int $tax_id - Tax ID (0 = no tax)
 * @param int $client_id
 * @param int $agreement_id - Optional
 * @return array - success, item_id, error
 */
function createInvoiceItemFromService($mysqli, $invoice_id, $service_id, $quantity, $tax_id = 0, $client_id, $agreement_id = 0) {
    $invoice_id = intval($invoice_id);
    $service_id = intval($service_id);
    $quantity = floatval($quantity);
    $tax_id = intval($tax_id);
    $client_id = intval($client_id);
    $agreement_id = intval($agreement_id);

    // Get service details
    $service = getService($mysqli, $service_id);
    if (!$service) {
        return ['success' => false, 'error' => 'Service not found'];
    }

    // Get effective rate
    $price = getInvoiceServiceRate($mysqli, $service_id, $client_id, $agreement_id);
    if ($price <= 0) {
        return ['success' => false, 'error' => 'Service rate cannot be determined'];
    }

    // Calculate subtotal
    $subtotal = $quantity * $price;

    // Calculate tax
    $tax_amount = 0;
    if ($tax_id > 0) {
        $tax_result = mysqli_query($mysqli,
            "SELECT tax_percent FROM taxes WHERE tax_id = $tax_id");
        $tax_row = mysqli_fetch_assoc($tax_result);
        if ($tax_row) {
            $tax_amount = $subtotal * ($tax_row['tax_percent'] / 100);
        }
    }

    $total = $subtotal + $tax_amount;

    // Get next item order
    $order_result = mysqli_query($mysqli,
        "SELECT MAX(item_order) as max_order FROM invoice_items WHERE item_invoice_id = $invoice_id");
    $order_row = mysqli_fetch_assoc($order_result);
    $item_order = ($order_row['max_order'] ?? 0) + 1;

    // Build item name and description
    $item_name = $service['service_name'];
    $item_description = $service['service_description'] ?? '';

    // Insert invoice item
    $sql = "INSERT INTO invoice_items (
        item_invoice_id,
        item_service_id,
        item_name,
        item_description,
        item_quantity,
        item_price,
        item_subtotal,
        item_tax,
        item_total,
        item_order,
        item_tax_id
    ) VALUES (
        $invoice_id,
        $service_id,
        '" . mysqli_real_escape_string($mysqli, $item_name) . "',
        '" . mysqli_real_escape_string($mysqli, $item_description) . "',
        $quantity,
        $price,
        $subtotal,
        $tax_amount,
        $total,
        $item_order,
        $tax_id
    )";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true, 'item_id' => mysqli_insert_id($mysqli)];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Get service details for invoice item
 * Returns service info and rate hierarchy
 *
 * @param mysqli $mysqli
 * @param int $item_id
 * @return array - Item with service details
 */
function getInvoiceItemService($mysqli, $item_id) {
    $item_id = intval($item_id);

    $result = mysqli_query($mysqli,
        "SELECT ii.*, sc.service_name, sc.service_category
         FROM invoice_items ii
         LEFT JOIN service_catalog sc ON ii.item_service_id = sc.service_id
         WHERE ii.item_id = $item_id");

    return mysqli_fetch_assoc($result);
}

/**
 * Link existing invoice item to service
 * Updates item_service_id for service tracking
 *
 * @param mysqli $mysqli
 * @param int $item_id
 * @param int $service_id
 * @return array - success, error
 */
function linkInvoiceItemToService($mysqli, $item_id, $service_id) {
    $item_id = intval($item_id);
    $service_id = intval($service_id);

    if ($service_id <= 0) {
        $service_id_sql = "NULL";
    } else {
        $service_id_sql = $service_id;
    }

    $sql = "UPDATE invoice_items SET item_service_id = $service_id_sql WHERE item_id = $item_id";

    if (mysqli_query($mysqli, $sql)) {
        return ['success' => true];
    }

    return ['success' => false, 'error' => mysqli_error($mysqli)];
}

/**
 * Get invoice summary by service
 * Groups invoice items by service for reporting
 *
 * @param mysqli $mysqli
 * @param int $invoice_id
 * @return array - Grouped items by service
 */
function getInvoiceSummaryByService($mysqli, $invoice_id) {
    $invoice_id = intval($invoice_id);

    $sql = "SELECT
        COALESCE(ii.item_service_id, 0) as service_id,
        COALESCE(sc.service_name, 'Other') as service_name,
        COUNT(*) as item_count,
        SUM(ii.item_quantity) as total_quantity,
        SUM(ii.item_subtotal) as total_subtotal,
        SUM(ii.item_tax) as total_tax,
        SUM(ii.item_total) as total_amount
    FROM invoice_items ii
    LEFT JOIN service_catalog sc ON ii.item_service_id = sc.service_id
    WHERE ii.item_invoice_id = $invoice_id
    GROUP BY ii.item_service_id, sc.service_name
    ORDER BY sc.service_name ASC";

    $result = mysqli_query($mysqli, $sql);
    $summary = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $summary[] = $row;
    }

    return $summary;
}

?>
