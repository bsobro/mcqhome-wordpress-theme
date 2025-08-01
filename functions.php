<?php
/**
 * MCQHome Theme functions and definitions
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

    // Add support for editor styles
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');

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
 * Enqueue scripts and styles
 */
function mcqhome_scripts() {
    // Enqueue main stylesheet (will be compiled from Tailwind CSS)
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri(), [], MCQHOME_VERSION);
    
    // Enqueue compiled CSS from Tailwind build process
    if (file_exists(MCQHOME_THEME_DIR . '/assets/css/main.css')) {
        wp_enqueue_style('mcqhome-main', MCQHOME_THEME_URL . '/assets/css/main.css', [], MCQHOME_VERSION);
    }

    // Enqueue main JavaScript file
    if (file_exists(MCQHOME_THEME_DIR . '/assets/js/main.js')) {
        wp_enqueue_script('mcqhome-main', MCQHOME_THEME_URL . '/assets/js/main.js', [], MCQHOME_VERSION, true);
    }

    // Enqueue comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
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

    register_sidebar([
        'name'          => esc_html__('Footer Widget Area', 'mcqhome'),
        'id'            => 'footer-1',
        'description'   => esc_html__('Add widgets here to appear in your footer.', 'mcqhome'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title text-lg font-semibold mb-4">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'mcqhome_widgets_init');

/**
 * Theme activation hook
 */
function mcqhome_activation() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set default options
    $default_options = [
        'mcqhome_setup_complete' => false,
        'mcqhome_demo_content' => false,
    ];
    
    foreach ($default_options as $option => $value) {
        if (get_option($option) === false) {
            add_option($option, $value);
        }
    }
    
    // Create default pages if they don't exist
    mcqhome_create_default_pages();
    
    // Schedule any necessary cron jobs
    if (!wp_next_scheduled('mcqhome_daily_cleanup')) {
        wp_schedule_event(time(), 'daily', 'mcqhome_daily_cleanup');
    }
}

/**
 * Theme deactivation hook
 */
function mcqhome_deactivation() {
    // Clear scheduled cron jobs
    wp_clear_scheduled_hook('mcqhome_daily_cleanup');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Create default pages required by the theme
 */
function mcqhome_create_default_pages() {
    $default_pages = [
        'dashboard' => [
            'title' => 'Dashboard',
            'content' => '<!-- wp:shortcode -->[mcqhome_dashboard]<!-- /wp:shortcode -->',
            'template' => 'page-dashboard.php'
        ],
        'browse' => [
            'title' => 'Browse MCQs',
            'content' => '<!-- wp:shortcode -->[mcqhome_browse]<!-- /wp:shortcode -->',
            'template' => 'page-browse.php'
        ],
        'institutions' => [
            'title' => 'Institutions',
            'content' => '<!-- wp:shortcode -->[mcqhome_institutions]<!-- /wp:shortcode -->',
            'template' => 'page-institutions.php'
        ],
        'teachers' => [
            'title' => 'Teachers',
            'content' => '<!-- wp:shortcode -->[mcqhome_teachers]<!-- /wp:shortcode -->',
            'template' => 'page-teachers.php'
        ]
    ];

    foreach ($default_pages as $slug => $page_data) {
        $existing_page = get_page_by_path($slug);
        
        if (!$existing_page) {
            $page_id = wp_insert_post([
                'post_title' => $page_data['title'],
                'post_content' => $page_data['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $slug,
            ]);
            
            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
            }
        }
    }
}

/**
 * Add theme activation and deactivation hooks
 */
add_action('after_switch_theme', 'mcqhome_activation');
add_action('switch_theme', 'mcqhome_deactivation');

/**
 * Include additional theme files
 */
require_once MCQHOME_THEME_DIR . '/inc/template-functions.php';
require_once MCQHOME_THEME_DIR . '/inc/customizer.php';

// Include custom post types and user roles (will be created in later tasks)
if (file_exists(MCQHOME_THEME_DIR . '/inc/post-types.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/post-types.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/user-roles.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/user-roles.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/ajax-handlers.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/ajax-handlers.php';
}