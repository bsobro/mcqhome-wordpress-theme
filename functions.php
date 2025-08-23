<?php
/**
 * MCQHome Theme functions - Step 1: Add Registration System
 * Gradually restoring functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('MCQHOME_VERSION', '1.0.0');
define('MCQHOME_THEME_DIR', get_template_directory());
define('MCQHOME_THEME_URL', get_template_directory_uri());

/**
 * Basic theme setup
 */
function mcqhome_setup() {
    // Add theme support for title tag
    add_theme_support('title-tag');
    
    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');
    
    // Register navigation menus
    register_nav_menus([
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu',
    ]);
    
    // Add theme support for HTML5
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);
}
add_action('after_setup_theme', 'mcqhome_setup');

/**
 * Enqueue styles and scripts
 */
function mcqhome_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri(), [], MCQHOME_VERSION);
    
    // Enqueue registration JavaScript on registration pages
    if (is_page('register') || is_page('registration')) {
        wp_enqueue_script('mcqhome-registration', MCQHOME_THEME_URL . '/assets/js/registration.js', ['jquery'], MCQHOME_VERSION, true);
        
        wp_localize_script('mcqhome-registration', 'mcqhome_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mcqhome_registration_nonce'),
            'messages' => [
                'processing' => __('Creating Account...', 'mcqhome'),
                'success' => __('Account created successfully!', 'mcqhome'),
                'error' => __('Registration failed. Please try again.', 'mcqhome'),
            ]
        ]);
    }
}
add_action('wp_enqueue_scripts', 'mcqhome_scripts');

/**
 * Step 1: Add User Registration System ✅
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/registration.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/registration.php';
        $step1_success = true;
    } catch (Exception $e) {
        error_log('MCQHome: Failed to load registration system - ' . $e->getMessage());
        $step1_success = false;
    }
} else {
    $step1_success = false;
}

/**
 * Step 2: Add User Roles System ✅
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/user-roles.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/user-roles.php';
        $step2_success = true;
    } catch (Exception $e) {
        error_log('MCQHome: Failed to load user roles system - ' . $e->getMessage());
        $step2_success = false;
    }
} else {
    $step2_success = false;
}

/**
 * Step 3: Add Dashboard Functions ✅
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/dashboard-functions.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/dashboard-functions.php';
        $step3_success = true;
    } catch (Exception $e) {
        error_log('MCQHome: Failed to load dashboard functions - ' . $e->getMessage());
        $step3_success = false;
    }
} else {
    $step3_success = false;
}

/**
 * Step 4: Database Setup
 */
function mcqhome_create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table 1: MCQ Attempts
    $table_name = $wpdb->prefix . 'mcq_attempts';
    $sql1 = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) DEFAULT NULL,
        selected_answer varchar(1) DEFAULT NULL,
        is_correct tinyint(1) DEFAULT 0,
        score_earned decimal(5,2) DEFAULT 0.00,
        score_percentage decimal(5,2) DEFAULT 0.00,
        time_taken int(11) DEFAULT 0,
        status varchar(20) DEFAULT 'in_progress',
        started_at datetime DEFAULT CURRENT_TIMESTAMP,
        completed_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY mcq_id (mcq_id),
        KEY mcq_set_id (mcq_set_id),
        KEY status (status)
    ) $charset_collate;";
    
    // Table 2: User Follows
    $table_name = $wpdb->prefix . 'mcq_user_follows';
    $sql2 = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        follower_id bigint(20) NOT NULL,
        followed_id bigint(20) NOT NULL,
        followed_type varchar(20) NOT NULL DEFAULT 'user',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_follow (follower_id, followed_id, followed_type),
        KEY follower_id (follower_id),
        KEY followed_id (followed_id),
        KEY followed_type (followed_type)
    ) $charset_collate;";
    
    // Table 3: User Enrollments
    $table_name = $wpdb->prefix . 'mcq_user_enrollments';
    $sql3 = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) NOT NULL,
        enrolled_at datetime DEFAULT CURRENT_TIMESTAMP,
        status varchar(20) DEFAULT 'active',
        progress_percentage decimal(5,2) DEFAULT 0.00,
        last_accessed datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_enrollment (user_id, mcq_set_id),
        KEY user_id (user_id),
        KEY mcq_set_id (mcq_set_id),
        KEY status (status)
    ) $charset_collate;";
    
    // Table 4: User Progress
    $table_name = $wpdb->prefix . 'mcq_user_progress';
    $sql4 = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) NOT NULL,
        total_questions int(11) DEFAULT 0,
        completed_questions int(11) DEFAULT 0,
        correct_answers int(11) DEFAULT 0,
        total_score decimal(8,2) DEFAULT 0.00,
        best_score decimal(5,2) DEFAULT 0.00,
        average_score decimal(5,2) DEFAULT 0.00,
        time_spent int(11) DEFAULT 0,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_progress (user_id, mcq_set_id),
        KEY user_id (user_id),
        KEY mcq_set_id (mcq_set_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    try {
        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);
        dbDelta($sql4);
        
        // Log successful table creation
        error_log('MCQHome: Database tables created successfully');
        return true;
        
    } catch (Exception $e) {
        error_log('MCQHome: Database table creation failed - ' . $e->getMessage());
        return false;
    }
}

// Initialize database setup
$step4_success = false;
try {
    // Check if all required tables exist
    global $wpdb;
    $required_tables = ['mcq_attempts', 'mcq_user_follows', 'mcq_user_enrollments', 'mcq_user_progress'];
    $tables_exist = 0;
    
    foreach ($required_tables as $table) {
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
        if ($table_exists) {
            $tables_exist++;
        }
    }
    
    if ($tables_exist < 4) {
        // Create missing tables
        $step4_success = mcqhome_create_database_tables();
        error_log('MCQHome: Step 4 - Created database tables. Tables found: ' . $tables_exist . '/4');
    } else {
        $step4_success = true; // All tables exist
        error_log('MCQHome: Step 4 - All database tables exist. Success!');
    }
} catch (Exception $e) {
    error_log('MCQHome: Step 4 database setup failed - ' . $e->getMessage());
    $step4_success = false;
}

/**
 * Progress Dashboard - Shows current status
 */
add_action('admin_notices', function() use ($step1_success, $step2_success, $step3_success, $step4_success) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="notice notice-info">';
    echo '<h3>🚀 MCQHome Theme Restoration Progress</h3>';
    echo '<ul style="margin-left: 20px;">';
    echo '<li>' . ($step1_success ? '✅' : '❌') . ' <strong>Step 1:</strong> User Registration System</li>';
    echo '<li>' . ($step2_success ? '✅' : '❌') . ' <strong>Step 2:</strong> User Roles System</li>';
    echo '<li>' . ($step3_success ? '✅' : '❌') . ' <strong>Step 3:</strong> Dashboard Functions & User Interface</li>';
    echo '<li>' . ($step4_success ? '✅' : '❌') . ' <strong>Step 4:</strong> Database Setup</li>';
    echo '<li>⏳ <strong>Step 5:</strong> Basic Custom Post Types (Next)</li>';
    echo '<li>⏳ <strong>Step 6:</strong> Assessment System (Later)</li>';
    echo '</ul>';
    
    $completed_steps = array_sum([$step1_success, $step2_success, $step3_success, $step4_success]);
    
    if ($completed_steps == 4) {
        echo '<p><strong>🎉 Steps 1-4 Complete!</strong> Core user system and database are working!</p>';
    } elseif ($completed_steps == 3) {
        echo '<p><strong>✨ Steps 1-3 Complete!</strong> Now testing Step 4 (Database Setup)...</p>';
    } elseif ($completed_steps == 2) {
        echo '<p><strong>Steps 1-2 Complete!</strong> Building on success...</p>';
    } elseif ($completed_steps == 1) {
        echo '<p><strong>Step 1 Complete!</strong> Building on success...</p>';
    }
    
    echo '<p><em>Progress: ' . $completed_steps . '/6 core systems restored</em></p>';
    echo '</div>';
});

/**
 * Create basic pages needed for the theme
 */
function mcqhome_create_basic_pages() {
    $pages = [
        'register' => [
            'title' => 'Register',
            'content' => '[mcqhome_registration]',
        ],
        'dashboard' => [
            'title' => 'Dashboard',
            'content' => 'Welcome to your MCQHome dashboard!',
        ]
    ];
    
    foreach ($pages as $slug => $page_data) {
        $existing_page = get_page_by_path($slug);
        
        if (!$existing_page) {
            wp_insert_post([
                'post_title' => $page_data['title'],
                'post_content' => $page_data['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $slug,
            ]);
        }
    }
}

/**
 * Theme activation hook
 */
function mcqhome_activation() {
    try {
        flush_rewrite_rules();
        mcqhome_create_basic_pages();
        
        // Initialize user roles safely
        if (function_exists('mcqhome_safe_init_user_roles')) {
            mcqhome_safe_init_user_roles();
        }
    } catch (Exception $e) {
        error_log('MCQHome: Theme activation error - ' . $e->getMessage());
    }
}
add_action('after_switch_theme', 'mcqhome_activation');

/**
 * Helper functions will be loaded from user-roles.php
 */