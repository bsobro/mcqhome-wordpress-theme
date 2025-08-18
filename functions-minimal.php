<?php
/**
 * MCQHome Theme functions and definitions - MINIMAL VERSION
 * This is a minimal version to ensure theme activation works
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

    // Add support for core custom logo
    add_theme_support('custom-logo', [
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    ]);

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

    // Add support for custom background
    add_theme_support('custom-background', [
        'default-color' => 'ffffff',
        'default-image' => '',
    ]);

    // Register navigation menus
    register_nav_menus([
        'primary' => esc_html__('Primary Menu', 'mcqhome'),
        'footer'  => esc_html__('Footer Menu', 'mcqhome'),
    ]);
}
add_action('after_setup_theme', 'mcqhome_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet
 */
function mcqhome_content_width() {
    $GLOBALS['content_width'] = apply_filters('mcqhome_content_width', 1200);
}
add_action('after_setup_theme', 'mcqhome_content_width', 0);

/**
 * Enqueue scripts and styles - MINIMAL VERSION
 */
function mcqhome_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri(), [], MCQHOME_VERSION);
    
    // Enqueue main JavaScript file
    wp_enqueue_script('mcqhome-main', MCQHOME_THEME_URL . '/assets/js/main.js', ['jquery'], MCQHOME_VERSION, true);
    
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
                'validation_error' => __('Please fill in all required fields correctly.', 'mcqhome')
            ]
        ]);
    }

    // Localize script for AJAX
    wp_localize_script('mcqhome-main', 'mcqhome_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('mcqhome_nonce'),
    ]);
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
 * Theme activation hook - MINIMAL VERSION
 */
function mcqhome_activation() {
    try {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        add_option('mcqhome_setup_complete', false);
        add_option('mcqhome_demo_content', false);
        
        // Create basic pages
        mcqhome_create_basic_pages();
        
    } catch (Exception $e) {
        // Log any activation errors but don't break the site
        error_log('MCQHome: Theme activation error - ' . $e->getMessage());
    }
}

/**
 * Create basic pages required by the theme
 */
function mcqhome_create_basic_pages() {
    $basic_pages = [
        'register' => [
            'title' => 'Register',
            'content' => '[mcqhome_registration]',
        ],
        'dashboard' => [
            'title' => 'Dashboard',
            'content' => 'Welcome to your dashboard!',
        ]
    ];

    foreach ($basic_pages as $slug => $page_data) {
        try {
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
        } catch (Exception $e) {
            error_log('MCQHome: Failed to create page ' . $slug . ' - ' . $e->getMessage());
        }
    }
}

/**
 * Add theme activation hook
 */
add_action('after_switch_theme', 'mcqhome_activation');

/**
 * Create placeholder shortcodes to prevent errors
 */
function mcqhome_create_placeholder_shortcodes() {
    // Registration shortcode placeholder
    if (!shortcode_exists('mcqhome_registration')) {
        add_shortcode('mcqhome_registration', function() {
            return '<p>Registration system is being set up. Please check back soon.</p>';
        });
    }
}
add_action('init', 'mcqhome_create_placeholder_shortcodes', 5);

/**
 * Get user's primary role
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

/**
 * Admin notice for minimal theme
 */
function mcqhome_minimal_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="notice notice-info is-dismissible">';
    echo '<p><strong>MCQHome Theme:</strong> Running in minimal mode. Advanced features are temporarily disabled to ensure stability.</p>';
    echo '<p>Available features: Basic pages, user registration (when properly configured), and standard WordPress functionality.</p>';
    echo '</div>';
}
add_action('admin_notices', 'mcqhome_minimal_admin_notice');

/**
 * Include only the registration system if it exists and is working
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/registration.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/registration.php';
    } catch (Exception $e) {
        error_log('MCQHome: Failed to load registration system - ' . $e->getMessage());
    }
}