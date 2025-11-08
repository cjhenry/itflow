<?php

/**
 * Service Catalog Enhancement - Database Migration
 * Phase 1: Database Foundation
 *
 * Creates service catalog tables and modifies existing tables
 * Run this migration to set up the service catalog feature
 */

defined('FROM_MIGRATION') || die('Direct access not allowed');

// Track migration status
$migration_name = "001_service_catalog_schema";
$migrations = [];
$errors = [];

// Migration 1: Create service_catalog table
$migrations[] = "
CREATE TABLE IF NOT EXISTS service_catalog (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL UNIQUE,
    service_description VARCHAR(500),
    service_default_rate DECIMAL(15,2) NOT NULL,
    service_category VARCHAR(50),
    service_default_unit VARCHAR(20) DEFAULT 'Hour',
    service_tax_id INT,
    service_minimum_hours DECIMAL(5,2),
    service_sort_order INT DEFAULT 0,
    service_status ENUM('Active', 'Archived') DEFAULT 'Active',
    service_created_by INT,
    service_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    service_updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (service_tax_id) REFERENCES tax_codes(tax_id) ON DELETE SET NULL,
    FOREIGN KEY (service_created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_service_status (service_status),
    INDEX idx_service_category (service_category),
    INDEX idx_service_created_by (service_created_by),
    INDEX idx_service_name (service_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
";

// Migration 2: Create client_services table
$migrations[] = "
CREATE TABLE IF NOT EXISTS client_services (
    client_service_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    service_id INT,
    client_service_custom_name VARCHAR(100),
    client_service_custom_rate DECIMAL(15,2),
    client_service_is_custom BOOLEAN DEFAULT FALSE,
    client_service_custom_description VARCHAR(500),
    client_service_custom_notes TEXT,
    client_service_included BOOLEAN DEFAULT TRUE,
    client_service_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    client_service_updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service_catalog(service_id) ON DELETE SET NULL,
    UNIQUE KEY unique_client_service (client_id, service_id),
    INDEX idx_client_id (client_id),
    INDEX idx_service_id (service_id),
    INDEX idx_included (client_service_included)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
";

// Migration 3: Create agreement_services table
$migrations[] = "
CREATE TABLE IF NOT EXISTS agreement_services (
    agreement_service_id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_id INT NOT NULL,
    service_id INT NOT NULL,
    agreement_service_custom_rate DECIMAL(15,2),
    agreement_service_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service_catalog(service_id) ON DELETE CASCADE,
    UNIQUE KEY unique_agreement_service (agreement_id, service_id),
    INDEX idx_agreement_id (agreement_id),
    INDEX idx_service_id (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
";

// Migration 4: Create agreement_service_hours table
$migrations[] = "
CREATE TABLE IF NOT EXISTS agreement_service_hours (
    agreement_service_hours_id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_id INT NOT NULL,
    service_id INT NOT NULL,
    service_hours_allocated DECIMAL(10,2),
    service_hours_used DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service_catalog(service_id) ON DELETE CASCADE,
    UNIQUE KEY unique_agreement_service_hours (agreement_id, service_id),
    INDEX idx_agreement_id (agreement_id),
    INDEX idx_service_id (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
";

// Migration 5: Modify clients table - add billing contact fields
$migrations[] = "
ALTER TABLE clients ADD COLUMN IF NOT EXISTS client_billing_contact_name VARCHAR(100) AFTER client_rate
";

$migrations[] = "
ALTER TABLE clients ADD COLUMN IF NOT EXISTS client_billing_contact_email VARCHAR(100) AFTER client_billing_contact_name
";

$migrations[] = "
ALTER TABLE clients ADD COLUMN IF NOT EXISTS client_billing_cc_emails VARCHAR(500) AFTER client_billing_contact_email
";

// Migration 6: Modify invoice_items table - add service link
$migrations[] = "
ALTER TABLE invoice_items ADD COLUMN IF NOT EXISTS item_service_id INT AFTER item_product_id
";

// Migration 7: Modify ticket_replies table - add service tracking
$migrations[] = "
ALTER TABLE ticket_replies ADD COLUMN IF NOT EXISTS ticket_reply_service_id INT AFTER ticket_reply_time_worked
";

// Execute migrations
foreach ($migrations as $sql) {
    if (mysqli_query($mysqli, $sql)) {
        // Success
    } else {
        $errors[] = mysqli_error($mysqli) . " | SQL: " . substr($sql, 0, 80) . "...";
    }
}

// Return results
if (empty($errors)) {
    echo json_encode([
        'success' => true,
        'migrations_run' => count($migrations),
        'errors' => 0,
        'message' => 'Service Catalog schema migration completed successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'migrations_run' => count($migrations) - count($errors),
        'errors' => count($errors),
        'error_details' => $errors
    ]);
}

?>
