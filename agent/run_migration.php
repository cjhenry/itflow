<?php
/**
 * Manual Migration Runner for item_discount column
 * Visit: http://localhost:8080/agent/run_migration.php
 */

require_once "../config.php";
require_once "../includes/database_version.php";

echo "<h2>Database Migration - item_discount Column</h2>";
echo "<pre>";

// Get current database version
$result = mysqli_query($mysqli, "SELECT config_current_database_version FROM settings WHERE company_id = 1");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    $CURRENT_DATABASE_VERSION = $row['config_current_database_version'];
    echo "Current DB Version: $CURRENT_DATABASE_VERSION\n";
    echo "Latest DB Version: " . LATEST_DATABASE_VERSION . "\n";
} else {
    echo "Error: Could not get current database version\n";
    exit(1);
}

// First, check if column already exists
echo "\n--- Checking if item_discount column exists ---\n";
$check = mysqli_query($mysqli, "DESCRIBE invoice_items");
$column_exists = false;
if ($check) {
    while ($row = mysqli_fetch_array($check)) {
        if ($row['Field'] == 'item_discount') {
            echo "✓ item_discount column already exists!\n";
            $column_exists = true;
            break;
        }
    }
}

if (!$column_exists) {
    echo "✗ item_discount column NOT FOUND - adding it...\n\n";

    $alter_result = mysqli_query($mysqli, "ALTER TABLE `invoice_items` ADD COLUMN `item_discount` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `item_subtotal`");

    if ($alter_result) {
        echo "✓ Successfully added item_discount column\n";
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

// Now update database version if needed
echo "\n--- Updating database version ---\n";
if (LATEST_DATABASE_VERSION > $CURRENT_DATABASE_VERSION) {
    echo "Updating version from $CURRENT_DATABASE_VERSION to " . LATEST_DATABASE_VERSION . "\n";
    $update_result = mysqli_query($mysqli, "UPDATE `settings` SET `config_current_database_version` = '" . LATEST_DATABASE_VERSION . "' WHERE company_id = 1");
    if ($update_result) {
        echo "✓ Version updated successfully\n";
    } else {
        echo "✗ Failed to update version: " . mysqli_error($mysqli) . "\n";
    }
} else {
    echo "Database version is already up to date\n";
}

// Final verification
echo "\n--- Final Verification ---\n";
$verify = mysqli_query($mysqli, "DESCRIBE invoice_items");
if ($verify) {
    $found = false;
    while ($row = mysqli_fetch_array($verify)) {
        if ($row['Field'] == 'item_discount') {
            echo "✓ CONFIRMED: item_discount column exists\n";
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

echo "\n✓ Migration complete! Products/services should now be addable.\n";
echo "</pre>";
?>
