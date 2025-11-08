<?php

/*
 * ITFlow - User GET/POST request handler
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config.php";
require_once "../functions.php";
require_once "../includes/check_login.php";

// Define a variable that we can use to only allow running post files via inclusion (prevents people/bots poking them)
define('FROM_POST_HANDLER', true);

// Log all POST requests for debugging
error_log("POST request to post.php - Module: " . ($_POST['module'] ?? 'unknown') . " - Action: " . (key($_POST) ?? 'none'));


// Determine which files we should load

// Determine module to load - check for explicit module field first, fall back to HTTP_REFERER
$module = isset($_POST['module']) ? sanitizeInput($_POST['module']) : null;

if (!$module && isset($_GET['module'])) {
    $module = sanitizeInput($_GET['module']);
}

if (!$module && isset($_SERVER['HTTP_REFERER'])) {
    // Parse URL & get the path
    $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);

    // Get the base name (the page name)
    $module = explode(".", basename($path))[0];

    // Strip off any _details bits
    $module = str_ireplace('_details', '', $module);
}

// Dynamically load admin-related module POST logic

// Load all module POST logic
//  Loads everything in post
//  Eventually, it would be nice to only specifically load what we need like we do for admins

foreach (glob("post/*.php") as $user_module) {
    if (!preg_match('/_model\.php$/', basename($user_module))) {
        $handler_module = str_replace('.php', '', basename($user_module));
        // Only load handler if it matches the determined module
        // Also handle singular/plural variations (quotes->quote, invoices->invoice, etc)
        $module_singular = rtrim($module, 's');
        $module_match = !$module || $handler_module === $module || $handler_module === $module_singular;

        if ($module_match) {
            try {
                require_once $user_module;
            } catch (Exception $e) {
                error_log("Error loading $user_module: " . $e->getMessage());
                die("Error processing request: " . $e->getMessage());
            }
        }
    }
}


// Logout is the same for user and admin
require_once "../post/logout.php";

// TODO: Find a home for these

require_once "../post/ai.php";
require_once "../post/misc.php";
