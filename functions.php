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

// Clean Registration Fix - Override the broken registration
function mcqhome_registration_form_clean($atts) {
    $atts = shortcode_atts([
        'redirect' => '',
        'show_login_link' => true,
    ], $atts);

    // If user is already logged in, redirect to dashboard
    if (is_user_logged_in()) {
        $user_role = mcqhome_get_user_primary_role();
        $redirect_url = home_url('/dashboard/');
        return '<p>' . sprintf(__('You are already logged in. <a href="%s">Go to Dashboard</a>', 'mcqhome'), $redirect_url) . '</p>';
    }

    ob_start();
    ?>
    <div id="mcqhome-registration-clean" class="max-w-4xl mx-auto px-4 py-8">
        <!-- Messages -->
        <div id="registration-messages" class="mb-6"></div>

        <!-- Step 1: Role Selection -->
        <div id="step-role-selection" class="registration-step active">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php _e('Choose Your Role', 'mcqhome'); ?></h2>
                    <p class="text-gray-600"><?php _e('Select how you want to use MCQHome', 'mcqhome'); ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Student Role -->
                    <div class="role-card p-6 border-2 border-gray-200 rounded-lg hover:border-blue-500 cursor-pointer transition-all" data-role="student">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('Student', 'mcqhome'); ?></h3>
                            <p class="text-sm text-gray-600"><?php _e('Take MCQs and track your progress', 'mcqhome'); ?></p>
                        </div>
                    </div>

                    <!-- Teacher Role -->
                    <div class="role-card p-6 border-2 border-gray-200 rounded-lg hover:border-green-500 cursor-pointer transition-all" data-role="teacher">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('Teacher', 'mcqhome'); ?></h3>
                            <p class="text-sm text-gray-600"><?php _e('Create and manage MCQs', 'mcqhome'); ?></p>
                        </div>
                    </div>

                    <!-- Institution Role -->
                    <div class="role-card p-6 border-2 border-gray-200 rounded-lg hover:border-purple-500 cursor-pointer transition-all" data-role="institution">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('Institution', 'mcqhome'); ?></h3>
                            <p class="text-sm text-gray-600"><?php _e('Manage teachers and students', 'mcqhome'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Registration Form -->
        <div id="step-registration-form" class="registration-step" style="display: none;">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <!-- Back Button -->
                <button id="back-to-roles" class="text-blue-600 hover:text-blue-800 mb-6 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <?php _e('Back to Role Selection', 'mcqhome'); ?>
                </button>

                <!-- Form Header -->
                <div class="text-center mb-8">
                    <h2 id="form-title" class="text-2xl font-bold text-gray-900 mb-2"><?php _e('Create Your Account', 'mcqhome'); ?></h2>
                    <p id="form-subtitle" class="text-gray-600"><?php _e('Join MCQHome and start your journey', 'mcqhome'); ?></p>
                </div>

                <!-- Registration Form -->
                <form id="registration-form" class="space-y-6">
                    <?php wp_nonce_field('mcqhome_register_nonce', 'mcqhome_register_nonce'); ?>
                    <input type="hidden" id="selected-role" name="user_role" value="">

                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php _e('First Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="first_name" name="first_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php _e('Last Name', 'mcqhome'); ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="last_name" name="last_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php _e('Email Address', 'mcqhome'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php _e('Password', 'mcqhome'); ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="password" name="password" required minlength="8"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1"><?php _e('Minimum 8 characters', 'mcqhome'); ?></p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php _e('Confirm Password', 'mcqhome'); ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Role-specific fields placeholder -->
                    <div id="role-specific-fields" style="display: none;">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-blue-700 text-sm">
                                <strong><?php _e('Note:', 'mcqhome'); ?></strong>
                                <?php _e('Role-specific fields will be added here based on your selection.', 'mcqhome'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-start">
                        <input type="checkbox" id="terms_accepted" name="terms_accepted" required
                               class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="terms_accepted" class="ml-2 text-sm text-gray-700">
                            <?php _e('I agree to the', 'mcqhome'); ?> <a href="#" class="text-blue-600 hover:underline"><?php _e('Terms and Conditions', 'mcqhome'); ?></a> 
                            <?php _e('and', 'mcqhome'); ?> <a href="#" class="text-blue-600 hover:underline"><?php _e('Privacy Policy', 'mcqhome'); ?></a>
                            <span class="text-red-500">*</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium">
                            <?php _e('Create Account', 'mcqhome'); ?>
                        </button>
                    </div>
                </form>

                <?php if ($atts['show_login_link']): ?>
                    <div class="text-center mt-6">
                        <p class="text-gray-600">
                            <?php _e('Already have an account?', 'mcqhome'); ?>
                            <a href="<?php echo wp_login_url(); ?>" class="text-blue-600 hover:underline font-medium">
                                <?php _e('Sign In', 'mcqhome'); ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .role-card.selected {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .role-card.selected h3 {
            color: #1d4ed8;
        }
        .role-card.selected p {
            color: #1e40af;
        }
        .message {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .message-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .message-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            console.log('MCQHome: Clean registration script loaded');
            
            var selectedRole = null;
            
            function showMessage(message, type) {
                var messageClass = type === 'error' ? 'message-error' : 'message-success';
                $('#registration-messages').html('<div class="message ' + messageClass + '">' + message + '</div>');
                
                // Auto-hide success messages after 5 seconds
                if (type === 'success') {
                    setTimeout(function() {
                        $('#registration-messages').html('');
                    }, 5000);
                }
            }
            
            function selectRole(role) {
                console.log('MCQHome: Role selected -', role);
                selectedRole = role;
                
                // Clear any previous messages
                $('#registration-messages').html('');
                
                // Update visual selection
                $('.role-card').removeClass('selected');
                $('[data-role="' + role + '"]').addClass('selected');
                
                // Set hidden input
                $('#selected-role').val(role);
                
                // Update form titles
                var titles = {
                    'student': { title: 'Student Registration', subtitle: 'Start your learning journey with MCQHome' },
                    'teacher': { title: 'Teacher Registration', subtitle: 'Join as a teacher and create amazing MCQs' },
                    'institution': { title: 'Institution Registration', subtitle: 'Register your institution and manage your team' }
                };
                
                if (titles[role]) {
                    $('#form-title').text(titles[role].title);
                    $('#form-subtitle').text(titles[role].subtitle);
                }
                
                // Show role-specific note
                $('#role-specific-fields').show();
                
                // Show registration form with smooth transition
                setTimeout(function() {
                    $('#step-role-selection').hide();
                    $('#step-registration-form').show();
                }, 300);
            }
            
            // Bind role card clicks
            $('.role-card').on('click', function(e) {
                e.preventDefault();
                var role = $(this).data('role');
                selectRole(role);
            });
            
            // Add keyboard support
            $('.role-card').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    var role = $(this).data('role');
                    selectRole(role);
                }
            });
            
            // Make role cards focusable
            $('.role-card').attr('tabindex', '0');
            
            // Bind back button
            $('#back-to-roles').on('click', function(e) {
                e.preventDefault();
                $('#registration-messages').html('');
                $('#step-registration-form').hide();
                $('#step-role-selection').show();
            });
            
            // Form validation and submission
            $('#registration-form').on('submit', function(e) {
                e.preventDefault();
                
                if (!selectedRole) {
                    showMessage('Please select a role first.', 'error');
                    $('#step-registration-form').hide();
                    $('#step-role-selection').show();
                    return;
                }
                
                // Basic validation
                var firstName = $('#first_name').val().trim();
                var lastName = $('#last_name').val().trim();
                var email = $('#email').val().trim();
                var password = $('#password').val();
                var confirmPassword = $('#confirm_password').val();
                var termsAccepted = $('#terms_accepted').is(':checked');
                
                if (!firstName) {
                    showMessage('First name is required.', 'error');
                    return;
                }
                
                if (!lastName) {
                    showMessage('Last name is required.', 'error');
                    return;
                }
                
                if (!email || !isValidEmail(email)) {
                    showMessage('Please enter a valid email address.', 'error');
                    return;
                }
                
                if (!password || password.length < 8) {
                    showMessage('Password must be at least 8 characters long.', 'error');
                    return;
                }
                
                if (password !== confirmPassword) {
                    showMessage('Passwords do not match.', 'error');
                    return;
                }
                
                if (!termsAccepted) {
                    showMessage('You must accept the terms and conditions.', 'error');
                    return;
                }
                
                // Show loading state
                var $submitBtn = $(this).find('button[type="submit"]');
                var originalText = $submitBtn.text();
                $submitBtn.text('Creating Account...').prop('disabled', true);
                
                // Simulate form submission (replace with actual AJAX call)
                setTimeout(function() {
                    showMessage('Registration functionality will be connected to the backend. Role: ' + selectedRole, 'success');
                    
                    // Reset form
                    $('#registration-form')[0].reset();
                    selectedRole = null;
                    $('#selected-role').val('');
                    $('.role-card').removeClass('selected');
                    $('#role-specific-fields').hide();
                    
                    // Go back to role selection
                    setTimeout(function() {
                        $('#step-registration-form').hide();
                        $('#step-role-selection').show();
                    }, 2000);
                    
                    // Reset button
                    $submitBtn.text(originalText).prop('disabled', false);
                }, 2000);
            });
            
            // Email validation helper
            function isValidEmail(email) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            // Real-time password confirmation validation
            $('#confirm_password').on('input', function() {
                var password = $('#password').val();
                var confirmPassword = $(this).val();
                
                if (confirmPassword && password && confirmPassword !== password) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            $('#password').on('input', function() {
                var password = $(this).val();
                var confirmPassword = $('#confirm_password').val();
                
                if (confirmPassword && password !== confirmPassword) {
                    document.getElementById('confirm_password').setCustomValidity('Passwords do not match');
                } else {
                    document.getElementById('confirm_password').setCustomValidity('');
                }
            });
            
            console.log('MCQHome: Clean registration initialized successfully');
        });
    </script>
    <?php
    return ob_get_clean();
}

// Replace the broken registration shortcode with the working one
remove_shortcode('mcqhome_registration');
add_shortcode('mcqhome_registration', 'mcqhome_registration_form_clean');

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