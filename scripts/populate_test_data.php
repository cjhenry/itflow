#!/usr/bin/env php
<?php

/**
 * ITFlow Test Data Population Script
 *
 * Populates database with realistic test data for development/testing
 * Includes: Clients, Contacts, Locations, Tickets, Invoices, Agreements, Assets, Vendors
 * Usage: php populate_test_data.php
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

echo "================================\n";
echo "ITFlow Test Data Population\n";
echo "================================\n\n";

// Get first admin user for ownership
$user_result = mysqli_query($mysqli, "SELECT user_id FROM users WHERE user_archived_at IS NULL LIMIT 1");
$user_row = mysqli_fetch_assoc($user_result);
$admin_user_id = $user_row['user_id'] ?? 1;

// Get first company currency (or use USD)
$currency_code = 'USD';

// Get first active service IDs for testing
$services_result = mysqli_query($mysqli, "SELECT service_id, service_name FROM service_catalog WHERE service_status = 'Active' LIMIT 10");
$services = [];
while ($row = mysqli_fetch_assoc($services_result)) {
    $services[] = $row;
}

if (empty($services)) {
    echo "✗ No active services found. Please ensure service_catalog is populated.\n";
    exit(1);
}

echo "Using " . count($services) . " active services for testing\n\n";

// Get first income category
$category_result = mysqli_query($mysqli, "SELECT category_id FROM categories WHERE category_type = 'Income' AND category_archived_at IS NULL LIMIT 1");
$category_row = mysqli_fetch_assoc($category_result);
$income_category_id = $category_row['category_id'] ?? 1;

// Get tax codes
$tax_result = mysqli_query($mysqli, "SELECT tax_id FROM taxes WHERE tax_archived_at IS NULL LIMIT 1");
$tax_row = mysqli_fetch_assoc($tax_result);
$tax_id = $tax_row['tax_id'] ?? 0;

// Test data
$test_clients = [
    [
        'name' => 'Acme Corporation',
        'type' => 'Manufacturing',
        'website' => 'acme.example.com',
        'rate' => 85.00,
        'billing_contact' => 'John Smith',
        'billing_email' => 'billing@acme.example.com'
    ],
    [
        'name' => 'TechStart Inc',
        'type' => 'Software Development',
        'website' => 'techstart.example.com',
        'rate' => 95.00,
        'billing_contact' => 'Sarah Johnson',
        'billing_email' => 'accounts@techstart.example.com'
    ],
    [
        'name' => 'Global Retail Co',
        'type' => 'Retail',
        'website' => 'globalretail.example.com',
        'rate' => 75.00,
        'billing_contact' => 'Mike Davis',
        'billing_email' => 'finance@globalretail.example.com'
    ],
    [
        'name' => 'Healthcare Plus',
        'type' => 'Healthcare',
        'website' => 'healthcareplus.example.com',
        'rate' => 100.00,
        'billing_contact' => 'Dr. Jennifer Lee',
        'billing_email' => 'billing@healthcareplus.example.com'
    ],
];

$contacts = [
    ['name' => 'John Smith', 'title' => 'IT Manager', 'email' => 'john@acme.example.com', 'phone' => '5551234567'],
    ['name' => 'Alice Brown', 'title' => 'System Admin', 'email' => 'alice@acme.example.com', 'phone' => '5551234568'],
    ['name' => 'Sarah Johnson', 'title' => 'CTO', 'email' => 'sarah@techstart.example.com', 'phone' => '5552234567'],
    ['name' => 'Bob Wilson', 'title' => 'Network Admin', 'email' => 'bob@techstart.example.com', 'phone' => '5552234568'],
    ['name' => 'Mike Davis', 'title' => 'IT Director', 'email' => 'mike@globalretail.example.com', 'phone' => '5553234567'],
    ['name' => 'Carol White', 'title' => 'Help Desk', 'email' => 'carol@globalretail.example.com', 'phone' => '5553234568'],
    ['name' => 'Dr. Jennifer Lee', 'title' => 'Medical Director', 'email' => 'jennifer@healthcareplus.example.com', 'phone' => '5554234567'],
    ['name' => 'Tom Garcia', 'title' => 'IT Support', 'email' => 'tom@healthcareplus.example.com', 'phone' => '5554234568'],
];

$vendors = [
    ['name' => 'Tech Solutions Ltd', 'type' => 'Hardware', 'website' => 'techsolutions.example.com'],
    ['name' => 'Cloud Services Inc', 'type' => 'Cloud Provider', 'website' => 'cloudservices.example.com'],
    ['name' => 'Software Licenses Pro', 'type' => 'Software', 'website' => 'softwarelicenses.example.com'],
];

$ticket_subjects = [
    'Network connectivity issues on Floor 3',
    'Email server not responding',
    'Cannot access shared drive',
    'Laptop hardware upgrade needed',
    'VPN connection failing',
    'Database performance degradation',
    'Monitor replacement required',
    'Software license renewal',
];

$agreement_types = ['Fixed Price - Monthly', 'Block Hours - Prepaid', 'Time & Materials'];

echo "Creating test clients and related data...\n";

$client_ids = [];
$contact_counter = 0;

foreach ($test_clients as $client_data) {
    // Create client
    $sql = "INSERT INTO clients SET
        client_name = '" . mysqli_real_escape_string($mysqli, $client_data['name']) . "',
        client_type = '" . mysqli_real_escape_string($mysqli, $client_data['type']) . "',
        client_website = '" . mysqli_real_escape_string($mysqli, $client_data['website']) . "',
        client_rate = " . floatval($client_data['rate']) . ",
        client_currency_code = '$currency_code',
        client_net_terms = 30,
        client_billing_contact_name = '" . mysqli_real_escape_string($mysqli, $client_data['billing_contact']) . "',
        client_billing_contact_email = '" . mysqli_real_escape_string($mysqli, $client_data['billing_email']) . "',
        client_accessed_at = NOW()";

    if (mysqli_query($mysqli, $sql)) {
        $client_id = mysqli_insert_id($mysqli);
        $client_ids[] = $client_id;
        echo "✓ Created client: " . $client_data['name'] . " (ID: $client_id)\n";

        // Create upload directory
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/uploads/clients/$client_id")) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . "/uploads/clients/$client_id", 0755, true);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/uploads/clients/$client_id/index.php", "");
        }

        // Create primary location
        $location_sql = "INSERT INTO locations SET
            location_name = 'Primary Office',
            location_address = '123 Main Street',
            location_city = 'New York',
            location_state = 'NY',
            location_zip = '10001',
            location_country = 'USA',
            location_phone = '555-123-4567',
            location_primary = 1,
            location_client_id = $client_id";

        if (mysqli_query($mysqli, $location_sql)) {
            echo "  → Created primary location\n";
        }

        // Create 2 contacts per client
        for ($i = 0; $i < 2; $i++) {
            if ($contact_counter < count($contacts)) {
                $contact = $contacts[$contact_counter];
                $contact_sql = "INSERT INTO contacts SET
                    contact_name = '" . mysqli_real_escape_string($mysqli, $contact['name']) . "',
                    contact_title = '" . mysqli_real_escape_string($mysqli, $contact['title']) . "',
                    contact_email = '" . mysqli_real_escape_string($mysqli, $contact['email']) . "',
                    contact_phone = '" . mysqli_real_escape_string($mysqli, $contact['phone']) . "',
                    contact_primary = " . ($i === 0 ? 1 : 0) . ",
                    contact_important = 1,
                    contact_client_id = $client_id";

                if (mysqli_query($mysqli, $contact_sql)) {
                    echo "  → Created contact: " . $contact['name'] . "\n";
                    $first_contact_id = $i === 0 ? mysqli_insert_id($mysqli) : null;
                }
                $contact_counter++;
            }
        }

        // Add client services (link to service catalog)
        foreach (array_slice($services, 0, 5) as $service) {
            $client_service_sql = "INSERT INTO client_services SET
                client_id = $client_id,
                service_id = " . $service['service_id'] . ",
                client_service_included = 1";

            if (mysqli_query($mysqli, $client_service_sql)) {
                // Optionally add custom rate for first service
                if ($service === $services[0]) {
                    $custom_rate = floatval($client_data['rate']);
                    mysqli_query($mysqli, "UPDATE client_services SET client_service_custom_rate = $custom_rate WHERE client_id = $client_id AND service_id = " . $service['service_id']);
                }
            }
        }
        echo "  → Added 5 services to client\n";

    } else {
        echo "✗ Error creating client: " . mysqli_error($mysqli) . "\n";
    }
}

echo "\nCreating test agreements...\n";

foreach (array_slice($client_ids, 0, 2) as $client_id) {
    $agreement_type = $agreement_types[rand(0, 2)];

    if ($agreement_type === 'Block Hours - Prepaid') {
        $hours = 40;
    } else {
        $hours = 0;
    }

    $agreement_name = "Standard " . $agreement_type;
    $agreement_notes = "Test agreement for " . $agreement_type;

    $agreement_number = 1000 + rand(1, 999);
    $agreement_prefix = 'AGR-';

    $agreement_sql = "INSERT INTO agreements SET
        agreement_client_id = $client_id,
        agreement_prefix = '$agreement_prefix',
        agreement_number = $agreement_number,
        agreement_type = '" . mysqli_real_escape_string($mysqli, $agreement_type) . "',
        agreement_name = '" . mysqli_real_escape_string($mysqli, $agreement_name) . "',
        agreement_value = " . rand(1000, 5000) . ",
        agreement_hours_included = $hours,
        agreement_start_date = DATE_SUB(NOW(), INTERVAL 30 DAY),
        agreement_end_date = DATE_ADD(NOW(), INTERVAL 330 DAY),
        agreement_status = 'Active',
        agreement_notes = '" . mysqli_real_escape_string($mysqli, $agreement_notes) . "',
        agreement_created_at = NOW()";

    if (mysqli_query($mysqli, $agreement_sql)) {
        $agreement_id = mysqli_insert_id($mysqli);
        echo "✓ Created agreement: $agreement_type (ID: $agreement_id)\n";
    }
}

echo "\nCreating test tickets...\n";

$ticket_counter = 0;
foreach (array_slice($client_ids, 0, 3) as $client_id) {
    for ($i = 0; $i < 2; $i++) {
        if ($ticket_counter < count($ticket_subjects)) {
            $status = rand(1, 4); // Random status
            $service_id = $services[rand(0, count($services) - 1)]['service_id'];

            $ticket_sql = "INSERT INTO tickets SET
                ticket_client_id = $client_id,
                ticket_prefix = 'TKT-',
                ticket_number = " . (1000 + rand(1, 999)) . ",
                ticket_subject = '" . mysqli_real_escape_string($mysqli, $ticket_subjects[$ticket_counter]) . "',
                ticket_details = 'Test ticket created for feature testing',
                ticket_status = $status,
                ticket_priority = " . rand(1, 5) . ",
                ticket_created_by = $admin_user_id,
                ticket_assigned_to = $admin_user_id,
                ticket_created_at = DATE_SUB(NOW(), INTERVAL " . rand(1, 30) . " DAY),
                ticket_updated_at = NOW()";

            if (mysqli_query($mysqli, $ticket_sql)) {
                $ticket_id = mysqli_insert_id($mysqli);
                echo "✓ Created ticket: " . $ticket_subjects[$ticket_counter] . " (ID: $ticket_id)\n";

                // Add ticket replies with time tracking
                for ($j = 0; $j < rand(1, 3); $j++) {
                    $hours = rand(0, 2);
                    $minutes = rand(0, 59);
                    $time_worked = sprintf("%02d:%02d:00", $hours, $minutes);

                    $reply_sql = "INSERT INTO ticket_replies SET
                        ticket_reply_ticket_id = $ticket_id,
                        ticket_reply = 'Work performed on this ticket.',
                        ticket_reply_type = 'Internal',
                        ticket_reply_time_worked = '$time_worked',
                        ticket_reply_by = $admin_user_id,
                        ticket_reply_created_at = DATE_SUB(NOW(), INTERVAL " . rand(1, 25) . " DAY)";

                    if (mysqli_query($mysqli, $reply_sql)) {
                        echo "  → Added reply with time tracking and service\n";
                    }
                }
            }
            $ticket_counter++;
        }
    }
}

echo "\nCreating test invoices...\n";

foreach ($client_ids as $client_id) {
    $invoice_sql = "INSERT INTO invoices SET
        invoice_client_id = $client_id,
        invoice_number = " . (1000 + rand(1, 999)) . ",
        invoice_prefix = 'INV-',
        invoice_scope = 'Monthly IT Services',
        invoice_status = 'Draft',
        invoice_date = DATE_SUB(NOW(), INTERVAL " . rand(0, 30) . " DAY),
        invoice_due = DATE_ADD(NOW(), INTERVAL 30 DAY),
        invoice_category_id = $income_category_id,
        invoice_currency_code = '$currency_code',
        invoice_amount = " . rand(500, 5000) . ",
        invoice_created_at = NOW()";

    if (mysqli_query($mysqli, $invoice_sql)) {
        $invoice_id = mysqli_insert_id($mysqli);
        echo "✓ Created invoice (ID: $invoice_id)\n";

        // Add invoice items from services
        foreach (array_slice($services, 0, 3) as $idx => $service) {
            $qty = rand(1, 20);
            $price = rand(50, 150);
            $subtotal = $qty * $price;
            $tax = ($tax_id > 0) ? round($subtotal * 0.1) : 0;
            $total = $subtotal + $tax;

            $item_sql = "INSERT INTO invoice_items SET
                item_invoice_id = $invoice_id,
                item_name = '" . mysqli_real_escape_string($mysqli, $service['service_name']) . "',
                item_quantity = $qty,
                item_price = $price,
                item_subtotal = $subtotal,
                item_tax = $tax,
                item_total = $total,
                item_tax_id = " . ($tax_id > 0 ? $tax_id : 0) . ",
                item_order = " . ($idx + 1);

            if (mysqli_query($mysqli, $item_sql)) {
                echo "  → Added invoice item: " . $service['service_name'] . "\n";
            }
        }

        // Update invoice total
        $total_sql = "UPDATE invoices SET invoice_amount = (SELECT SUM(item_total) FROM invoice_items WHERE item_invoice_id = $invoice_id) WHERE invoice_id = $invoice_id";
        mysqli_query($mysqli, $total_sql);
    }
}

echo "\nCreating test vendors...\n";

$vendor_ids = [];
foreach ($vendors as $vendor) {
    $vendor_sql = "INSERT INTO vendors SET
        vendor_name = '" . mysqli_real_escape_string($mysqli, $vendor['name']) . "',
        vendor_website = '" . mysqli_real_escape_string($mysqli, $vendor['website']) . "',
        vendor_created_at = NOW()";

    if (mysqli_query($mysqli, $vendor_sql)) {
        $vendor_id = mysqli_insert_id($mysqli);
        $vendor_ids[] = $vendor_id;
        echo "✓ Created vendor: " . $vendor['name'] . " (ID: $vendor_id)\n";
    }
}

echo "\nCreating test assets...\n";

$asset_counter = 0;
$asset_names = ['Server - Dell PowerEdge', 'Firewall - Cisco ASA', 'Switch - Cisco Catalyst', 'Laptop - Dell XPS', 'Monitor - Dell U2720Q'];

foreach (array_slice($client_ids, 0, 2) as $client_id) {
    foreach (array_slice($asset_names, 0, 3) as $asset_name) {
        $vendor_id = $vendor_ids[rand(0, count($vendor_ids) - 1)];
        $serial_num = 'SN-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $asset_notes = "Test asset for client $client_id";

        $asset_sql = "INSERT INTO assets SET
            asset_client_id = $client_id,
            asset_name = '" . mysqli_real_escape_string($mysqli, $asset_name) . "',
            asset_type = 'Hardware',
            asset_make = 'Test Make',
            asset_serial = '" . mysqli_real_escape_string($mysqli, $serial_num) . "',
            asset_notes = '" . mysqli_real_escape_string($mysqli, $asset_notes) . "',
            asset_created_at = NOW()";

        if (mysqli_query($mysqli, $asset_sql)) {
            echo "✓ Created asset: $asset_name (Client: $client_id)\n";
        }
    }
}

echo "\n================================\n";
echo "Test Data Population Complete!\n";
echo "================================\n\n";

echo "Summary:\n";
echo "✓ " . count($client_ids) . " clients created\n";
echo "✓ Multiple contacts per client\n";
echo "✓ Client services linked to service catalog\n";
echo "✓ Test agreements with service allocation\n";
echo "✓ Tickets with time tracking and services\n";
echo "✓ Invoices with service-based line items\n";
echo "✓ Vendors and assets for testing\n\n";

echo "Ready for feature testing!\n";

?>
