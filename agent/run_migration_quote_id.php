<?php
/**
 * Manual Migration Runner for invoice_quote_id column
 * Visit: http://localhost:8080/agent/run_migration_quote_id.php
 */

require_once "../config.php";

echo "<h2>Database Migration - invoice_quote_id Column</h2>";
echo "<pre>";

// First, check if column already exists
echo "\n--- Checking if invoice_quote_id column exists ---\n";
$check = mysqli_query($mysqli, "DESCRIBE invoices");
$column_exists = false;
if ($check) {
    while ($row = mysqli_fetch_array($check)) {
        if ($row['Field'] == 'invoice_quote_id') {
            echo "✓ invoice_quote_id column already exists!\n";
            $column_exists = true;
            break;
        }
    }
}

if (!$column_exists) {
    echo "✗ invoice_quote_id column NOT FOUND - adding it...\n\n";

    $alter_result = mysqli_query($mysqli, "ALTER TABLE `invoices` ADD COLUMN `invoice_quote_id` int(11) DEFAULT NULL AFTER `invoice_recurring_invoice_id`");

    if ($alter_result) {
        echo "✓ Successfully added invoice_quote_id column\n";
    } else {
        $error = mysqli_error($mysqli);
        echo "✗ Error adding column: " . $error . "\n";
        if (strpos($error, 'Duplicate column') !== false) {
            echo "  (This is OK - column already exists)\n";
        }
    }
} else {
    echo "Column already exists, skipping add.\n";
}

// Final verification
echo "\n--- Final Verification ---\n";
$verify = mysqli_query($mysqli, "DESCRIBE invoices");
if ($verify) {
    $found = false;
    while ($row = mysqli_fetch_array($verify)) {
        if ($row['Field'] == 'invoice_quote_id') {
            echo "✓ CONFIRMED: invoice_quote_id column exists\n";
            echo "  Type: " . $row['Type'] . "\n";
            echo "  Null: " . $row['Null'] . "\n";
            echo "  Default: " . $row['Default'] . "\n";
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "✗ ERROR: Column still not found after migration!\n";
    }
} else {
    echo "✗ Error verifying: " . mysqli_error($mysqli) . "\n";
}

echo "\n✓ Migration complete! Invoices from quotes now locked.\n";
echo "</pre>";
?>
