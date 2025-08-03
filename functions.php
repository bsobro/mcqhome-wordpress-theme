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
    // Enqueue compiled CSS from Tailwind build process first
    if (file_exists(MCQHOME_THEME_DIR . '/assets/css/main.css')) {
        wp_enqueue_style('mcqhome-main', MCQHOME_THEME_URL . '/assets/css/main.css', [], MCQHOME_VERSION);
    }
    
    // Enqueue main stylesheet after Tailwind CSS to allow overrides
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri(), ['mcqhome-main'], MCQHOME_VERSION);
    
    // Enqueue dashboard CSS on dashboard page
    if (is_page('dashboard') && file_exists(MCQHOME_THEME_DIR . '/assets/css/dashboard.css')) {
        wp_enqueue_style('mcqhome-dashboard', MCQHOME_THEME_URL . '/assets/css/dashboard.css', ['mcqhome-main'], MCQHOME_VERSION);
    }
    
    // Enqueue assessment CSS on assessment page
    if (is_page('take-assessment') && file_exists(MCQHOME_THEME_DIR . '/assets/css/assessment.css')) {
        wp_enqueue_style('mcqhome-assessment', MCQHOME_THEME_URL . '/assets/css/assessment.css', ['mcqhome-main'], MCQHOME_VERSION);
    }
    
    // Enqueue browse CSS on browse pages
    if ((is_page('browse') || is_page('institutions') || is_author() || is_singular('institution')) && file_exists(MCQHOME_THEME_DIR . '/assets/css/browse.css')) {
        wp_enqueue_style('mcqhome-browse', MCQHOME_THEME_URL . '/assets/css/browse.css', ['mcqhome-main'], MCQHOME_VERSION);
    }

    // Enqueue main JavaScript file
    if (file_exists(MCQHOME_THEME_DIR . '/assets/js/main.js')) {
        wp_enqueue_script('mcqhome-main', MCQHOME_THEME_URL . '/assets/js/main.js', ['jquery'], MCQHOME_VERSION, true);
    }
    
    // Enqueue dashboard JavaScript on dashboard page
    if (is_page('dashboard') && file_exists(MCQHOME_THEME_DIR . '/assets/js/dashboard.js')) {
        wp_enqueue_script('mcqhome-dashboard', MCQHOME_THEME_URL . '/assets/js/dashboard.js', ['jquery'], MCQHOME_VERSION, true);
    }
    
    // Enqueue assessment JavaScript on assessment page
    if (is_page('take-assessment') && file_exists(MCQHOME_THEME_DIR . '/assets/js/assessment.js')) {
        wp_enqueue_script('mcqhome-assessment', MCQHOME_THEME_URL . '/assets/js/assessment.js', ['jquery'], MCQHOME_VERSION, true);
    }
    
    // Enqueue browse JavaScript on browse pages
    if ((is_page('browse') || is_page('institutions') || is_author() || is_singular('institution')) && file_exists(MCQHOME_THEME_DIR . '/assets/js/browse.js')) {
        wp_enqueue_script('mcqhome-browse', MCQHOME_THEME_URL . '/assets/js/browse.js', ['jquery'], MCQHOME_VERSION, true);
        
        // Localize browse script
        wp_localize_script('mcqhome-browse', 'mcqhome_browse', [
            'follow' => __('Follow', 'mcqhome'),
            'following' => __('Following', 'mcqhome'),
            'unfollow' => __('Unfollow', 'mcqhome'),
            'loading' => __('Loading...', 'mcqhome'),
            'loadMore' => __('Load More', 'mcqhome'),
            'noMore' => __('No More Results', 'mcqhome'),
            'error' => __('An error occurred. Please try again.', 'mcqhome'),
            'followSuccess' => __('Successfully followed!', 'mcqhome'),
            'unfollowSuccess' => __('Successfully unfollowed!', 'mcqhome')
        ]);
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
    
    // Initialize database tables safely
    if (function_exists('mcqhome_init_database')) {
        try {
            mcqhome_init_database();
        } catch (Exception $e) {
            // Log error but don't break the site
            error_log('MCQHome: Database initialization failed - ' . $e->getMessage());
        }
    }
    
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
        ],
        'register' => [
            'title' => 'Register',
            'content' => '<!-- wp:shortcode -->[mcqhome_registration]<!-- /wp:shortcode -->',
            'template' => 'page-register.php'
        ],
        'take-assessment' => [
            'title' => 'Take Assessment',
            'content' => '<!-- Assessment page content is handled by the template -->',
            'template' => 'page-take-assessment.php'
        ],
        'assessment-results' => [
            'title' => 'Assessment Results',
            'content' => '<!-- Assessment results page content is handled by the template -->',
            'template' => 'page-assessment-results.php'
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
 * Enqueue admin scripts and styles
 */
function mcqhome_admin_scripts($hook) {
    global $post_type;
    
    // Load scripts for MCQ post type edit pages
    if ($post_type === 'mcq' && ($hook === 'post.php' || $hook === 'post-new.php')) {
        // Enqueue MCQ builder CSS
        wp_enqueue_style('mcqhome-mcq-builder', MCQHOME_THEME_URL . '/assets/css/mcq-builder.css', [], MCQHOME_VERSION);
        
        // Enqueue MCQ editor CSS
        wp_enqueue_style('mcqhome-mcq-editor', MCQHOME_THEME_URL . '/assets/css/mcq-editor.css', [], MCQHOME_VERSION);
        
        // Enqueue MCQ builder JavaScript
        wp_enqueue_script('mcqhome-mcq-builder', MCQHOME_THEME_URL . '/assets/js/mcq-builder.js', ['jquery', 'wp-tinymce'], MCQHOME_VERSION, true);
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        // Localize script for MCQ builder
        wp_localize_script('mcqhome-mcq-builder', 'mcqBuilderL10n', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mcqhome_nonce'),
            'livePreview' => __('Live Preview', 'mcqhome'),
            'showExplanation' => __('Show Explanation', 'mcqhome'),
            'hideExplanation' => __('Hide Explanation', 'mcqhome'),
            'explanation' => __('Explanation', 'mcqhome'),
            'questionPlaceholder' => __('Question text will appear here...', 'mcqhome'),
            'optionPlaceholder' => __('Option %s', 'mcqhome'),
            'explanationPlaceholder' => __('Explanation will appear here...', 'mcqhome'),
            'correctAnswer' => __('Correct Answer', 'mcqhome'),
            'autoSaveSuccess' => __('MCQ auto-saved successfully.', 'mcqhome'),
            'autoSaveError' => __('Failed to auto-save MCQ.', 'mcqhome'),
            'selectMedia' => __('Select Media', 'mcqhome'),
            'useMedia' => __('Use this media', 'mcqhome'),
            'errorNoQuestion' => __('Please enter a question text.', 'mcqhome'),
            'errorEmptyOptions' => __('Please fill in all %d answer options.', 'mcqhome'),
            'errorNoCorrectAnswer' => __('Please select the correct answer.', 'mcqhome'),
            'errorNoExplanation' => __('Please provide an explanation for the correct answer.', 'mcqhome'),
            'invalidFileType' => __('Invalid file type. Please upload images, videos, or audio files only.', 'mcqhome'),
            'fileTooLarge' => __('File too large. Maximum size is 10MB.', 'mcqhome'),
            'uploadError' => __('Failed to upload file. Please try again.', 'mcqhome'),
            'uploading' => __('Uploading...', 'mcqhome'),
            'addSubject' => __('Add New Subject', 'mcqhome'),
            'addTopic' => __('Add New Topic', 'mcqhome'),
            'addTerm' => __('Add Term', 'mcqhome'),
            'termName' => __('Term Name', 'mcqhome'),
            'description' => __('Description', 'mcqhome'),
            'optional' => __('optional', 'mcqhome'),
            'enterTermName' => __('Enter term name...', 'mcqhome'),
            'enterDescription' => __('Enter description...', 'mcqhome'),
            'cancel' => __('Cancel', 'mcqhome'),
            'termNameRequired' => __('Term name is required.', 'mcqhome'),
            'termAdded' => __('Term "%s" added successfully.', 'mcqhome'),
            'termAddError' => __('Failed to add term. Please try again.', 'mcqhome'),
        ]);
    }
    
    // Load scripts for MCQ Set post type edit pages
    if ($post_type === 'mcq_set' && ($hook === 'post.php' || $hook === 'post-new.php')) {
        // Enqueue MCQ Set builder CSS
        wp_enqueue_style('mcqhome-mcq-set-builder', MCQHOME_THEME_URL . '/assets/css/mcq-set-builder.css', [], MCQHOME_VERSION);
        
        // Enqueue MCQ Set builder JavaScript
        wp_enqueue_script('mcqhome-mcq-set-builder', MCQHOME_THEME_URL . '/assets/js/mcq-set-builder.js', ['jquery'], MCQHOME_VERSION, true);
        
        // Localize script for MCQ Set builder
        wp_localize_script('mcqhome-mcq-set-builder', 'mcqSetBuilderL10n', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mcqhome_nonce'),
            'selectedCount' => __('Selected: %d questions', 'mcqhome'),
            'selectQuestionsFirst' => __('Select questions above to configure individual marks.', 'mcqhome'),
            'question' => __('Question', 'mcqhome'),
            'marks' => __('Marks', 'mcqhome'),
            'autoSaveSuccess' => __('MCQ Set auto-saved successfully.', 'mcqhome'),
            'autoSaveError' => __('Failed to auto-save MCQ Set.', 'mcqhome'),
            'unsavedChanges' => __('You have unsaved changes. Are you sure you want to leave?', 'mcqhome'),
            'errorNoQuestions' => __('Please select at least one question for this MCQ set.', 'mcqhome'),
            'errorNoMarks' => __('Total marks must be greater than 0.', 'mcqhome'),
            'errorPassingMarksHigh' => __('Passing marks cannot be greater than total marks.', 'mcqhome'),
            'errorInvalidPrice' => __('Please enter a valid price for paid MCQ sets.', 'mcqhome'),
        ]);
    }
}
add_action('admin_enqueue_scripts', 'mcqhome_admin_scripts');

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

if (file_exists(MCQHOME_THEME_DIR . '/inc/registration.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/registration.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/ajax-handlers.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/ajax-handlers.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/database-setup.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/database-setup.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/dashboard-functions.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/dashboard-functions.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/assessment-functions.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/assessment-functions.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/role-settings.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/role-settings.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/seo-functions.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/seo-functions.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/performance-optimization.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/performance-optimization.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/asset-minification.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/asset-minification.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/semantic-html.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/semantic-html.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/demo-content-safe.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/demo-content-safe.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/default-institution.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/default-institution.php';
}

// Registration system is now properly handled by inc/registration.php

// Registration system is handled by inc/registration.php

/**

 * Admin notice for setup issues
 */
function mcqhome_admin_notices() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if database tables exist
    global $wpdb;
    $required_tables = ['mcq_attempts', 'mcq_user_follows'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        $full_table_name = $wpdb->prefix . $table;
        if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") !== $full_table_name) {
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>MCQHome Theme:</strong> Some database tables are missing. The theme will work with limited functionality. Missing tables: ' . implode(', ', $missing_tables) . '</p>';
        echo '<p><a href="' . admin_url('themes.php') . '" class="button">Reactivate Theme</a> to create missing tables.</p>';
        echo '</div>';
    }
    
    // Check if custom roles exist
    if (!get_role('student') || !get_role('teacher') || !get_role('institution')) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>MCQHome Theme:</strong> Custom user roles are being initialized. Please refresh the page if you encounter any issues.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'mcqhome_admin_notices');

/**
 * Safe theme initialization
 */
function mcqhome_safe_init() {
    // Initialize database tables if they don't exist
    if (function_exists('mcqhome_check_database_version')) {
        try {
            mcqhome_check_database_version();
        } catch (Exception $e) {
            error_log('MCQHome: Database check failed - ' . $e->getMessage());
        }
    }
    

}
add_action('init', 'mcqhome_safe_init', 5);