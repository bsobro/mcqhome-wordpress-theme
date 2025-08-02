<?php
/**
 * Debug script to identify theme fatal errors
 * Place this file in your theme root and access it via browser
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define WordPress constants if not defined
if (!defined('ABSPATH')) {
    // You need to update this path to your WordPress installation
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

// Load WordPress
require_once ABSPATH . 'wp-config.php';
require_once ABSPATH . 'wp-load.php';

echo "<h1>MCQHome Theme Debug</h1>";

// Check if theme files exist
$theme_dir = get_template_directory();
$required_files = [
    '/functions.php',
    '/inc/template-functions.php',
    '/inc/customizer.php',
    '/inc/post-types.php',
    '/inc/user-roles.php',
    '/inc/registration.php',
    '/inc/ajax-handlers.php',
    '/inc/database-setup.php',
    '/inc/dashboard-functions.php',
    '/inc/assessment-functions.php'
];

echo "<h2>File Existence Check:</h2>";
foreach ($required_files as $file) {
    $full_path = $theme_dir . $file;
    $exists = file_exists($full_path);
    echo "<p>" . $file . ": " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
    
    if ($exists) {
        // Check for PHP syntax errors
        $output = shell_exec("php -l " . escapeshellarg($full_path) . " 2>&1");
        if (strpos($output, 'No syntax errors') === false) {
            echo "<p style='color: red;'>SYNTAX ERROR in $file: $output</p>";
        }
    }
}

// Test loading each file individually
echo "<h2>Individual File Loading Test:</h2>";
foreach ($required_files as $file) {
    $full_path = $theme_dir . $file;
    if (file_exists($full_path)) {
        echo "<p>Testing $file...</p>";
        try {
            ob_start();
            include_once $full_path;
            $output = ob_get_clean();
            echo "<p style='color: green;'>✅ $file loaded successfully</p>";
            if (!empty($output)) {
                echo "<pre>Output: " . htmlspecialchars($output) . "</pre>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error loading $file: " . $e->getMessage() . "</p>";
        } catch (ParseError $e) {
            echo "<p style='color: red;'>❌ Parse error in $file: " . $e->getMessage() . "</p>";
        } catch (Error $e) {
            echo "<p style='color: red;'>❌ Fatal error in $file: " . $e->getMessage() . "</p>";
        }
    }
}

// Check database tables
echo "<h2>Database Tables Check:</h2>";
global $wpdb;
$required_tables = [
    'mcq_attempts',
    'mcq_user_follows',
    'mcq_user_enrollments',
    'mcq_user_progress',
    'mcq_user_notifications',
    'mcq_activity_stream'
];

foreach ($required_tables as $table) {
    $full_table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
    echo "<p>" . $full_table_name . ": " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

// Check user roles
echo "<h2>User Roles Check:</h2>";
$custom_roles = ['student', 'teacher', 'institution'];
foreach ($custom_roles as $role) {
    $role_obj = get_role($role);
    echo "<p>" . $role . ": " . ($role_obj ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

// Check post types
echo "<h2>Post Types Check:</h2>";
$custom_post_types = ['mcq', 'mcq_set', 'institution'];
foreach ($custom_post_types as $post_type) {
    $exists = post_type_exists($post_type);
    echo "<p>" . $post_type . ": " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

// Check for WordPress errors
echo "<h2>WordPress Error Log (last 20 lines):</h2>";
$error_log = ABSPATH . 'wp-content/debug.log';
if (file_exists($error_log)) {
    $lines = file($error_log);
    $last_lines = array_slice($lines, -20);
    echo "<pre>" . htmlspecialchars(implode('', $last_lines)) . "</pre>";
} else {
    echo "<p>No debug.log file found. Enable WP_DEBUG_LOG in wp-config.php</p>";
}

echo "<h2>PHP Info:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "</p>";

echo "<h2>WordPress Info:</h2>";
echo "<p>WordPress Version: " . get_bloginfo('version') . "</p>";
echo "<p>Active Theme: " . get_template() . "</p>";
echo "<p>Theme Directory: " . get_template_directory() . "</p>";