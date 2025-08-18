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
 * Step 3: Add Dashboard Functions
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
 * Progress Dashboard - Shows current status
 */
add_action('admin_notices', function() use ($step1_success, $step2_success, $step3_success) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="notice notice-info">';
    echo '<h3>🚀 MCQHome Theme Restoration Progress</h3>';
    echo '<ul style="margin-left: 20px;">';
    echo '<li>' . ($step1_success ? '✅' : '❌') . ' <strong>Step 1:</strong> User Registration System</li>';
    echo '<li>' . ($step2_success ? '✅' : '❌') . ' <strong>Step 2:</strong> User Roles System</li>';
    echo '<li>' . ($step3_success ? '✅' : '❌') . ' <strong>Step 3:</strong> Dashboard Functions & User Interface</li>';
    echo '<li>⏳ <strong>Step 4:</strong> Database Setup (Next)</li>';
    echo '<li>⏳ <strong>Step 5:</strong> Basic Custom Post Types (Next)</li>';
    echo '<li>⏳ <strong>Step 6:</strong> Assessment System (Later)</li>';
    echo '</ul>';
    
    $completed_steps = array_sum([$step1_success, $step2_success, $step3_success]);
    
    if ($completed_steps == 3) {
        echo '<p><strong>🎉 Steps 1-3 Complete!</strong> Core user system is working. Dashboard functionality added!</p>';
    } elseif ($completed_steps == 2) {
        echo '<p><strong>✨ Steps 1-2 Complete!</strong> Now testing Step 3 (Dashboard Functions)...</p>';
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