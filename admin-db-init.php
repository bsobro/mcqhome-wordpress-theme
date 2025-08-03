<?php
/**
 * Simple database initialization script
 * 
 * This file can be accessed directly to initialize the database tables
 * if there are any issues with the automatic initialization.
 */

// Load WordPress
require_once dirname(__FILE__) . '/wp-load.php';

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Load the database setup file
require_once get_template_directory() . '/inc/database-setup.php';

echo '<h1>MCQHome Database Initialization</h1>';

try {
    // Force database initialization
    mcqhome_init_database();
    echo '<p style="color: green;">✓ Database tables initialized successfully!</p>';
    
    // Check if tables exist
    global $wpdb;
    $tables = [
        'mcq_attempts',
        'mcq_set_attempts', 
        'mcq_user_progress',
        'mcq_user_follows',
        'mcq_user_enrollments',
        'mcq_user_notifications',
        'mcq_activity_stream'
    ];
    
    echo '<h2>Table Status:</h2>';
    echo '<ul>';
    foreach ($tables as $table) {
        $full_table_name = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
        $status = $exists ? '✓ Exists' : '✗ Missing';
        $color = $exists ? 'green' : 'red';
        echo "<li style='color: $color;'>$table: $status</li>";
    }
    echo '</ul>';
    
} catch (Exception $e) {
    echo '<p style="color: red;">✗ Error: ' . $e->getMessage() . '</p>';
}

echo '<p><a href="' . admin_url() . '">← Back to Admin</a></p>';
?>