<?php
/**
 * MCQHome Theme functions - ULTRA MINIMAL VERSION
 * Only the absolute essentials for WordPress theme activation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('MCQHOME_VERSION', '1.0.0');

/**
 * Theme setup - minimal
 */
function mcqhome_setup() {
    // Add theme support for title tag
    add_theme_support('title-tag');
    
    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');
    
    // Register navigation menus
    register_nav_menus([
        'primary' => 'Primary Menu',
    ]);
}
add_action('after_setup_theme', 'mcqhome_setup');

/**
 * Enqueue styles - minimal
 */
function mcqhome_scripts() {
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri(), [], MCQHOME_VERSION);
}
add_action('wp_enqueue_scripts', 'mcqhome_scripts');

/**
 * Theme activation - minimal
 */
function mcqhome_activation() {
    // Just flush rewrite rules
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'mcqhome_activation');

/**
 * Admin notice
 */
function mcqhome_ultra_minimal_notice() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-success"><p><strong>MCQHome Theme activated successfully!</strong> Running in safe mode with minimal features.</p></div>';
    }
}
add_action('admin_notices', 'mcqhome_ultra_minimal_notice');