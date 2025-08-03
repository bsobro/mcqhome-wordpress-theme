<?php
/**
 * Minimal MCQHome Theme functions - for debugging
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('MCQHOME_VERSION', '1.0.0');
define('MCQHOME_THEME_DIR', get_template_directory());
define('MCQHOME_THEME_URL', get_template_directory_uri());

/**
 * MCQHome Theme setup
 */
function mcqhome_setup() {
    // Make theme available for translation
    load_theme_textdomain('mcqhome', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // Add theme support for selective refresh for widgets
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for HTML5 markup
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    // Register navigation menus
    register_nav_menus([
        'primary' => esc_html__('Primary Menu', 'mcqhome'),
        'footer'  => esc_html__('Footer Menu', 'mcqhome'),
    ]);
}
add_action('after_setup_theme', 'mcqhome_setup');

/**
 * Enqueue scripts and styles
 */
function mcqhome_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri(), [], MCQHOME_VERSION);
    
    // Enqueue main JavaScript file if it exists
    if (file_exists(MCQHOME_THEME_DIR . '/assets/js/main.js')) {
        wp_enqueue_script('mcqhome-main', MCQHOME_THEME_URL . '/assets/js/main.js', ['jquery'], MCQHOME_VERSION, true);
    }

    // Enqueue comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'mcqhome_scripts');

/**
 * Register widget areas
 */
function mcqhome_widgets_init() {
    register_sidebar([
        'name'          => esc_html__('Sidebar', 'mcqhome'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'mcqhome'),
        'before_widget' => '<section id="%1$s" class="widget %2$s mb-8">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title text-lg font-semibold mb-4">',
        'after_title'   => '</h2>',
    ]);
}
add_action('widgets_init', 'mcqhome_widgets_init');

/**
 * Simple theme activation hook
 */
function mcqhome_simple_activation() {
    // Just flush rewrite rules
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'mcqhome_simple_activation');

/**
 * Theme deactivation hook
 */
function mcqhome_simple_deactivation() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
add_action('switch_theme', 'mcqhome_simple_deactivation');