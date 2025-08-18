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
 * Step 1: Add User Registration System
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/registration.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/registration.php';
        
        // Add success notice for registration system
        add_action('admin_notices', function() {
            if (current_user_can('manage_options')) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>MCQHome Step 1:</strong> User registration system loaded successfully!</p>';
                echo '</div>';
            }
        });
        
    } catch (Exception $e) {
        error_log('MCQHome: Failed to load registration system - ' . $e->getMessage());
        
        // Add error notice
        add_action('admin_notices', function() use ($e) {
            if (current_user_can('manage_options')) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>MCQHome Error:</strong> Registration system failed to load - ' . esc_html($e->getMessage()) . '</p>';
                echo '</div>';
            }
        });
    }
}

/**
 * Create basic pages needed for registration
 */
function mcqhome_create_registration_page() {
    $page = get_page_by_path('register');
    
    if (!$page) {
        wp_insert_post([
            'post_title' => 'Register',
            'post_content' => '[mcqhome_registration]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'register',
        ]);
    }
}

/**
 * Theme activation hook
 */
function mcqhome_activation() {
    try {
        flush_rewrite_rules();
        mcqhome_create_registration_page();
    } catch (Exception $e) {
        error_log('MCQHome: Theme activation error - ' . $e->getMessage());
    }
}
add_action('after_switch_theme', 'mcqhome_activation');

/**
 * Helper function for user roles
 */
function mcqhome_get_user_primary_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $user = get_userdata($user_id);
    if (!$user || empty($user->roles)) {
        return false;
    }
    
    return $user->roles[0];
}