<?php
/**
 * Check recent error logs for debugging
 */

echo "<h2>Recent Error Logs</h2>";
echo "<pre>";

// Try common log locations
$log_paths = [
    '/var/log/apache2/error.log',
    '/var/log/php-fpm.log',
    '/var/log/syslog',
    sys_get_temp_dir() . '/php_errors.log',
];

$log_found = false;

foreach ($log_paths as $path) {
    if (file_exists($path) && is_readable($path)) {
        echo "=== $path ===\n";
        $lines = file($path);
        // Get last 100 lines
        $recent = array_slice($lines, -100);

        // Filter for edit_item or EDIT_ITEM_HANDLER
        foreach ($recent as $line) {
            if (stripos($line, 'edit_item') !== false || stripos($line, 'discount') !== false) {
                echo $line;
            }
        }
        $log_found = true;
    }
}

if (!$log_found) {
    echo "No readable log files found at standard locations\n";
    echo "Trying alternative method...\n\n";

    // PHP's internal error log
    $error_log = ini_get('error_log');
    echo "PHP error_log location: " . ($error_log ? $error_log : 'not configured') . "\n";
}

echo "</pre>";

// Also try to read recent Apache logs with tail
echo "\n<h2>Last 50 lines from /var/log/apache2/error.log:</h2>";
echo "<pre>";
if (file_exists('/var/log/apache2/error.log')) {
    $output = shell_exec('tail -50 /var/log/apache2/error.log 2>&1');
    echo $output;
} else {
    echo "File not found";
}
echo "</pre>";

?>
