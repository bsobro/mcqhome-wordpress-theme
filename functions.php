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
    // Enqueue compiled CSS from Tailwind build process first
    if (file_exists(MCQHOME_THEME_DIR . '/assets/css/main.css')) {
        wp_enqueue_style('mcqhome-main', MCQHOME_THEME_URL . '/assets/css/main.css', [], MCQHOME_VERSION);
    }
    
    // Enqueue main stylesheet after Tailwind CSS to allow overrides
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri(), ['mcqhome-main'], MCQHOME_VERSION);

    // Enqueue main JavaScript file
    if (file_exists(MCQHOME_THEME_DIR . '/assets/js/main.js')) {
        wp_enqueue_script('mcqhome-main', MCQHOME_THEME_URL . '/assets/js/main.js', ['jquery'], MCQHOME_VERSION, true);
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
 * Essential functions that templates need
 */

/**
 * Get post views count
 */
function mcqhome_get_post_views($post_id) {
    $views = get_post_meta($post_id, '_post_views', true);
    return $views ? intval($views) : 0;
}

/**
 * Track post views
 */
function mcqhome_track_post_views($post_id) {
    // Don't track views for logged-in users who can edit posts
    if (is_user_logged_in() && current_user_can('edit_posts')) {
        return;
    }
    
    $views = mcqhome_get_post_views($post_id);
    update_post_meta($post_id, '_post_views', $views + 1);
}

/**
 * Get MCQ success rate
 */
function mcqhome_get_mcq_success_rate($mcq_id) {
    global $wpdb;
    
    // Check if table exists
    $table_name = $wpdb->prefix . 'mcq_attempts';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return 0; // Table doesn't exist yet
    }
    
    $total_attempts = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mcq_attempts WHERE mcq_id = %d",
        $mcq_id
    ));
    
    if (!$total_attempts) {
        return 0;
    }
    
    $correct_attempts = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mcq_attempts WHERE mcq_id = %d AND is_correct = 1",
        $mcq_id
    ));
    
    return round(($correct_attempts / $total_attempts) * 100);
}

/**
 * Get related MCQs
 */
function mcqhome_get_related_mcqs($mcq_id, $limit = 4) {
    // Get current MCQ's subjects and topics
    $subjects = wp_get_post_terms($mcq_id, 'mcq_subject', ['fields' => 'ids']);
    $topics = wp_get_post_terms($mcq_id, 'mcq_topic', ['fields' => 'ids']);
    
    $args = [
        'post_type' => 'mcq',
        'posts_per_page' => $limit,
        'post__not_in' => [$mcq_id],
        'post_status' => 'publish'
    ];
    
    // Add tax query if we have subjects or topics
    if (!empty($subjects) || !empty($topics)) {
        $tax_query = ['relation' => 'OR'];
        
        if (!empty($subjects)) {
            $tax_query[] = [
                'taxonomy' => 'mcq_subject',
                'field' => 'term_id',
                'terms' => $subjects
            ];
        }
        
        if (!empty($topics)) {
            $tax_query[] = [
                'taxonomy' => 'mcq_topic',
                'field' => 'term_id',
                'terms' => $topics
            ];
        }
        
        $args['tax_query'] = $tax_query;
    }
    
    return new WP_Query($args);
}

// Get MCQ set question count
function mcqhome_get_mcq_set_question_count($set_id) {
    $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
    return is_array($mcq_ids) ? count($mcq_ids) : 0;
}

// Get MCQ set rating
function mcqhome_get_mcq_set_rating($set_id) {
    $rating = get_post_meta($set_id, '_average_rating', true);
    return $rating ? floatval($rating) : 0;
}

// Get user role
function mcqhome_get_user_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $user = get_userdata($user_id);
    return $user && !empty($user->roles) ? $user->roles[0] : 'subscriber';
}

// Get user primary role
function mcqhome_get_user_primary_role($user_id = null) {
    return mcqhome_get_user_role($user_id);
}

// Get user role display name
function mcqhome_get_user_role_display_name($role) {
    $role_names = array(
        'student' => __('Student', 'mcqhome'),
        'teacher' => __('Teacher', 'mcqhome'),
        'institution' => __('Institution', 'mcqhome'),
        'administrator' => __('Administrator', 'mcqhome'),
    );
    return isset($role_names[$role]) ? $role_names[$role] : ucfirst($role);
}

// Get institution stats
function mcqhome_get_institution_stats($institution_id) {
    return array(
        'teachers' => 0,
        'students' => 0,
        'mcq_sets' => 0,
        'total_questions' => 0
    );
}

// Get institution teachers
function mcqhome_get_institution_teachers($institution_id) {
    return array();
}

// Get institution MCQ sets
function mcqhome_get_institution_mcq_sets($institution_id, $limit = 6) {
    $args = array(
        'post_type' => 'mcq_set',
        'posts_per_page' => $limit,
        'post_status' => 'publish'
    );
    return new WP_Query($args);
}

// Get institution subjects
function mcqhome_get_institution_subjects($institution_id) {
    return array();
}

// Get user progress
function mcqhome_get_user_progress($user_id, $mcq_set_id) {
    return null;
}

// Get activity feed
function mcqhome_get_activity_feed($user_id, $limit = 10) {
    return array();
}

// Get unread notifications count
function mcqhome_get_unread_notifications_count($user_id) {
    return 0;
}

// Get user notifications
function mcqhome_get_user_notifications($user_id, $limit = 3) {
    return array();
}

// Get assessment results
function mcqhome_get_assessment_results($user_id, $mcq_set_id, $attempt_id) {
    return array();
}

// Get user performance analytics
function mcqhome_get_user_performance_analytics($user_id, $mcq_set_id) {
    return array();
}

// Get performance comparison
function mcqhome_get_performance_comparison($user_id, $mcq_set_id) {
    return array();
}

/**
 * Track post views on single post pages
 */
function mcqhome_track_single_post_views() {
    if (is_single() && (get_post_type() === 'mcq' || get_post_type() === 'mcq_set')) {
        mcqhome_track_post_views(get_the_ID());
    }
}
add_action('wp_head', 'mcqhome_track_single_post_views');

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
 * Add theme activation and deactivation hooks
 */
add_action('after_switch_theme', 'mcqhome_activation');
add_action('switch_theme', 'mcqhome_deactivation');

/**
 * Include additional theme files safely
 */
$include_files = [
    '/inc/template-functions.php',
    '/inc/customizer.php'
];

foreach ($include_files as $file) {
    $file_path = MCQHOME_THEME_DIR . $file;
    if (file_exists($file_path)) {
        try {
            require_once $file_path;
        } catch (Exception $e) {
            error_log('MCQHome: Failed to include ' . $file . ' - ' . $e->getMessage());
        }
    }
}