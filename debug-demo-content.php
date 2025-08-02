<?php
/**
 * Debug script for MCQHome Demo Content
 * 
 * This script helps debug issues with demo content generation
 */

// Only run if accessed directly and user is admin
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../wp-config.php');
}

if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo '<h1>MCQHome Demo Content Debug</h1>';

// Check if post types are registered
echo '<h2>Post Types Check</h2>';
$required_post_types = ['mcq', 'mcq_set', 'institution'];
foreach ($required_post_types as $post_type) {
    $exists = post_type_exists($post_type);
    echo '<p>' . $post_type . ': ' . ($exists ? '✅ Registered' : '❌ Not registered') . '</p>';
}

// Check if taxonomies are registered
echo '<h2>Taxonomies Check</h2>';
$required_taxonomies = ['mcq_subject', 'mcq_topic', 'mcq_difficulty'];
foreach ($required_taxonomies as $taxonomy) {
    $exists = taxonomy_exists($taxonomy);
    echo '<p>' . $taxonomy . ': ' . ($exists ? '✅ Registered' : '❌ Not registered') . '</p>';
}

// Check if user roles exist
echo '<h2>User Roles Check</h2>';
$required_roles = ['student', 'teacher', 'institution'];
foreach ($required_roles as $role) {
    $exists = get_role($role);
    echo '<p>' . $role . ': ' . ($exists ? '✅ Exists' : '❌ Does not exist') . '</p>';
}

// Check if database tables exist
echo '<h2>Database Tables Check</h2>';
global $wpdb;
$required_tables = ['mcq_attempts', 'mcq_user_follows', 'mcq_user_enrollments'];
foreach ($required_tables as $table) {
    $full_table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
    echo '<p>' . $full_table_name . ': ' . ($exists ? '✅ Exists' : '❌ Does not exist') . '</p>';
}

// Check if required functions exist
echo '<h2>Required Functions Check</h2>';
$required_functions = ['mcqhome_add_user_follow', 'mcqhome_enroll_user', 'mcqhome_record_mcq_attempt', 'mcqhome_update_user_progress'];
foreach ($required_functions as $function) {
    $exists = function_exists($function);
    echo '<p>' . $function . ': ' . ($exists ? '✅ Exists' : '❌ Does not exist') . '</p>';
}

// Check if demo content class exists
echo '<h2>Demo Content Class Check</h2>';
$class_exists = class_exists('MCQHome_Demo_Content');
echo '<p>MCQHome_Demo_Content class: ' . ($class_exists ? '✅ Exists' : '❌ Does not exist') . '</p>';

// Test basic demo content creation
if ($class_exists) {
    echo '<h2>Basic Test</h2>';
    try {
        $demo = new MCQHome_Demo_Content();
        echo '<p>✅ Demo content class instantiated successfully</p>';
        
        // Test dependency check
        $reflection = new ReflectionClass($demo);
        $method = $reflection->getMethod('check_dependencies');
        $method->setAccessible(true);
        $dependencies_ok = $method->invoke($demo);
        
        echo '<p>Dependencies check: ' . ($dependencies_ok ? '✅ All dependencies OK' : '❌ Missing dependencies') . '</p>';
        
    } catch (Exception $e) {
        echo '<p>❌ Error: ' . $e->getMessage() . '</p>';
    }
}

// Show recent error log entries
echo '<h2>Recent Error Log Entries</h2>';
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $lines = file($error_log);
    $recent_lines = array_slice($lines, -20); // Last 20 lines
    echo '<pre style="background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: scroll;">';
    foreach ($recent_lines as $line) {
        if (strpos($line, 'MCQHome') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo '</pre>';
} else {
    echo '<p>Error log not found or not accessible</p>';
}

echo '<p><a href="' . admin_url('themes.php?page=mcqhome-demo-content') . '">← Back to Demo Content Page</a></p>';
?>