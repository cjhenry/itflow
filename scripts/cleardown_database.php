#!/usr/bin/env php
<?php

/**
 * ITFlow Database Cleardown Script
 *
 * Clears test/demo data from database while preserving schema
 * Usage: php cleardown_database.php
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// Change to script directory
chdir(__DIR__ . '/..');

// Load config
require_once 'config.php';

if (!isset($mysqli)) {
    die("Database connection failed. Check config.php\n");
}

// Confirmation prompt
echo "================================\n";
echo "ITFlow Database Cleardown\n";
echo "================================\n";
echo "\nThis script will clear test data from the database.\n";
echo "Schema and structure will be preserved.\n\n";
echo "WARNING: This action cannot be undone!\n\n";
echo "Continue? (type 'yes' to proceed): ";

$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if ($input !== 'yes') {
    echo "Cancelled.\n";
    exit(0);
}

echo "\nClearing database...\n";

// Disable foreign key checks during cleanup
mysqli_query($mysqli, "SET FOREIGN_KEY_CHECKS = 0");

$tables_to_clear = [
    'agreement_service_hours',
    'agreement_services',
    'agreement_assets',
    'agreement_hours_history',
    'agreements',
    'client_services',
    'service_catalog',
    'invoice_items',
    'invoices',
    'recurring_invoices',
    'ticket_replies',
    'tickets',
    'recurring_invoices_custom_fields'
];

$cleared = 0;
$errors = 0;

foreach ($tables_to_clear as $table) {
    // Check if table exists first
    $check = mysqli_query($mysqli, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($check) === 0) {
        echo "⊘ Skipped $table (table does not exist)\n";
        continue;
    }

    if (mysqli_query($mysqli, "TRUNCATE TABLE $table")) {
        echo "✓ Cleared $table\n";
        $cleared++;
    } else {
        echo "✗ Error clearing $table: " . mysqli_error($mysqli) . "\n";
        $errors++;
    }
}

// Re-enable foreign key checks
mysqli_query($mysqli, "SET FOREIGN_KEY_CHECKS = 1");

echo "\n================================\n";
echo "Cleardown Complete\n";
echo "================================\n";
echo "Tables cleared: $cleared\n";
echo "Errors: $errors\n";
echo "\nDatabase is ready for fresh data.\n";

exit(0);

?>
