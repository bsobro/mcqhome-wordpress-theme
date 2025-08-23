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
 * Step 5: Basic Custom Post Types
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/post-types.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/post-types.php';
        $step5_success = true;
        error_log('MCQHome: Step 5 - Custom post types loaded successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Failed to load custom post types - ' . $e->getMessage());
        $step5_success = false;
    }
} else {
    // Create a minimal post-types.php file if it doesn't exist
    $post_types_content = '<?php
/**
 * Custom Post Types for MCQHome Theme
 * 
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined("ABSPATH")) {
    exit;
}

/**
 * Register MCQ Custom Post Type
 */
function mcqhome_register_mcq_post_type() {
    $labels = array(
        "name" => __("MCQs", "mcqhome"),
        "singular_name" => __("MCQ", "mcqhome"),
        "menu_name" => __("MCQs", "mcqhome"),
        "all_items" => __("All MCQs", "mcqhome"),
        "add_new" => __("Add New", "mcqhome"),
        "add_new_item" => __("Add New MCQ", "mcqhome"),
        "edit_item" => __("Edit MCQ", "mcqhome"),
        "new_item" => __("New MCQ", "mcqhome"),
        "view_item" => __("View MCQ", "mcqhome"),
        "view_items" => __("View MCQs", "mcqhome"),
        "search_items" => __("Search MCQs", "mcqhome"),
    );

    $args = array(
        "label" => __("MCQs", "mcqhome"),
        "labels" => $labels,
        "description" => "Multiple Choice Questions",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "mcq", "with_front" => true),
        "query_var" => true,
        "menu_icon" => "dashicons-editor-help",
        "supports" => array("title", "editor", "thumbnail", "author"),
        "taxonomies" => array("mcq_category", "mcq_difficulty"),
    );

    register_post_type("mcq", $args);
}
add_action("init", "mcqhome_register_mcq_post_type");

/**
 * Register MCQ Set Custom Post Type
 */
function mcqhome_register_mcq_set_post_type() {
    $labels = array(
        "name" => __("MCQ Sets", "mcqhome"),
        "singular_name" => __("MCQ Set", "mcqhome"),
        "menu_name" => __("MCQ Sets", "mcqhome"),
        "all_items" => __("All MCQ Sets", "mcqhome"),
        "add_new" => __("Add New", "mcqhome"),
        "add_new_item" => __("Add New MCQ Set", "mcqhome"),
        "edit_item" => __("Edit MCQ Set", "mcqhome"),
        "new_item" => __("New MCQ Set", "mcqhome"),
        "view_item" => __("View MCQ Set", "mcqhome"),
        "view_items" => __("View MCQ Sets", "mcqhome"),
        "search_items" => __("Search MCQ Sets", "mcqhome"),
    );

    $args = array(
        "label" => __("MCQ Sets", "mcqhome"),
        "labels" => $labels,
        "description" => "Collections of MCQs",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "mcq-set", "with_front" => true),
        "query_var" => true,
        "menu_icon" => "dashicons-portfolio",
        "supports" => array("title", "editor", "thumbnail", "author"),
        "taxonomies" => array("mcq_category"),
    );

    register_post_type("mcq_set", $args);
}
add_action("init", "mcqhome_register_mcq_set_post_type");

/**
 * Register Institution Custom Post Type
 */
function mcqhome_register_institution_post_type() {
    $labels = array(
        "name" => __("Institutions", "mcqhome"),
        "singular_name" => __("Institution", "mcqhome"),
        "menu_name" => __("Institutions", "mcqhome"),
        "all_items" => __("All Institutions", "mcqhome"),
        "add_new" => __("Add New", "mcqhome"),
        "add_new_item" => __("Add New Institution", "mcqhome"),
        "edit_item" => __("Edit Institution", "mcqhome"),
        "new_item" => __("New Institution", "mcqhome"),
        "view_item" => __("View Institution", "mcqhome"),
        "view_items" => __("View Institutions", "mcqhome"),
        "search_items" => __("Search Institutions", "mcqhome"),
    );

    $args = array(
        "label" => __("Institutions", "mcqhome"),
        "labels" => $labels,
        "description" => "Educational Institutions",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "institution", "with_front" => true),
        "query_var" => true,
        "menu_icon" => "dashicons-building",
        "supports" => array("title", "editor", "thumbnail", "author"),
    );

    register_post_type("institution", $args);
}
add_action("init", "mcqhome_register_institution_post_type");

/**
 * Register Custom Taxonomies
 */
function mcqhome_register_taxonomies() {
    // MCQ Category taxonomy
    register_taxonomy("mcq_category", array("mcq", "mcq_set"), array(
        "hierarchical" => true,
        "label" => __("MCQ Categories", "mcqhome"),
        "show_ui" => true,
        "show_admin_column" => true,
        "query_var" => true,
        "rewrite" => array("slug" => "mcq-category"),
    ));

    // MCQ Difficulty taxonomy
    register_taxonomy("mcq_difficulty", array("mcq"), array(
        "hierarchical" => false,
        "label" => __("Difficulty Levels", "mcqhome"),
        "show_ui" => true,
        "show_admin_column" => true,
        "query_var" => true,
        "rewrite" => array("slug" => "difficulty"),
    ));
}
add_action("init", "mcqhome_register_taxonomies");

/**
 * Flush rewrite rules on theme activation
 */
function mcqhome_flush_rewrite_rules() {
    mcqhome_register_mcq_post_type();
    mcqhome_register_mcq_set_post_type();
    mcqhome_register_institution_post_type();
    mcqhome_register_taxonomies();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, "mcqhome_flush_rewrite_rules");
';
    
    try {
        file_put_contents(MCQHOME_THEME_DIR . '/inc/post-types.php', $post_types_content);
        require_once MCQHOME_THEME_DIR . '/inc/post-types.php';
        $step5_success = true;
        error_log('MCQHome: Step 5 - Created and loaded post-types.php successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Step 5 - Failed to create post-types.php - ' . $e->getMessage());
        $step5_success = false;
    }
}

/**
 * Progress Dashboard - Shows current status
 */
add_action('admin_notices', function() use ($step1_success, $step2_success, $step3_success, $step4_success, $step5_success) {
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
    echo '<li>' . ($step5_success ? '✅' : '❌') . ' <strong>Step 5:</strong> Basic Custom Post Types</li>';
    echo '<li>⏳ <strong>Step 6:</strong> Assessment System (Next)</li>';
    echo '</ul>';
    
    $completed_steps = array_sum([$step1_success, $step2_success, $step3_success, $step4_success, $step5_success]);
    
    if ($completed_steps == 5) {
        echo '<p><strong>🎉 Steps 1-5 Complete!</strong> Core system with custom post types is working!</p>';
    } elseif ($completed_steps == 4) {
        echo '<p><strong>✨ Steps 1-4 Complete!</strong> Now testing Step 5 (Custom Post Types)...</p>';
    } elseif ($completed_steps == 3) {
        echo '<p><strong>Steps 1-3 Complete!</strong> Building on success...</p>';
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
        // Register post types first
        if (function_exists('mcqhome_register_mcq_set_post_type')) {
            mcqhome_register_mcq_set_post_type();
        }
        if (function_exists('mcqhome_register_institution_post_type')) {
            mcqhome_register_institution_post_type();
        }
        if (function_exists('mcqhome_register_taxonomies')) {
            mcqhome_register_taxonomies();
        }
        
        // Flush rewrite rules after registering post types
        flush_rewrite_rules();
        
        mcqhome_create_basic_pages();
        
        // Initialize user roles safely
        if (function_exists('mcqhome_safe_init_user_roles')) {
            mcqhome_safe_init_user_roles();
        }
        
        error_log('MCQHome: Theme activation completed successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Theme activation error - ' . $e->getMessage());
    }
}
add_action('after_switch_theme', 'mcqhome_activation');

/**
 * Helper functions will be loaded from user-roles.php
 */

/**
 * Force flush rewrite rules on admin init (temporary fix)
 * Remove this after the permalinks are working
 */
add_action('admin_init', function() {
    if (get_option('mcqhome_flush_rewrite_rules') !== 'done') {
        flush_rewrite_rules();
        update_option('mcqhome_flush_rewrite_rules', 'done');
        error_log('MCQHome: Rewrite rules flushed successfully');
    }
});