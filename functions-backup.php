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
        
        // Enqueue Question Navigation Panel CSS
        if (file_exists(MCQHOME_THEME_DIR . '/assets/css/question-navigation-panel.css')) {
            wp_enqueue_style('mcqhome-question-navigation-panel', MCQHOME_THEME_URL . '/assets/css/question-navigation-panel.css', ['mcqhome-assessment'], MCQHOME_VERSION);
        }
        
        // Enqueue Mobile Assessment Enhancements CSS
        if (file_exists(MCQHOME_THEME_DIR . '/assets/css/mobile-assessment-enhancements.css')) {
            wp_enqueue_style('mcqhome-mobile-assessment', MCQHOME_THEME_URL . '/assets/css/mobile-assessment-enhancements.css', ['mcqhome-assessment', 'mcqhome-question-navigation-panel'], MCQHOME_VERSION);
        }
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
        // Enqueue Question Navigation Panel JavaScript first
        if (file_exists(MCQHOME_THEME_DIR . '/assets/js/question-navigation-panel.js')) {
            wp_enqueue_script('mcqhome-question-navigation-panel', MCQHOME_THEME_URL . '/assets/js/question-navigation-panel.js', ['jquery'], MCQHOME_VERSION, true);
            
            // Localize script for navigation panel
            wp_localize_script('mcqhome-question-navigation-panel', 'mcqhome_l10n', [
                'question_navigation' => __('Questions', 'mcqhome'),
                'attempted' => __('Attempted', 'mcqhome'),
                'skipped' => __('Skipped', 'mcqhome'),
                'remaining' => __('Remaining', 'mcqhome'),
                'current' => __('Current', 'mcqhome'),
                'answered' => __('Answered', 'mcqhome'),
                'unanswered' => __('Not Answered', 'mcqhome'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mcqhome_assessment_nonce')
            ]);
        }
        
        wp_enqueue_script('mcqhome-assessment-security', MCQHOME_THEME_URL . '/assets/js/assessment-security.js', ['jquery'], MCQHOME_VERSION, true);
        wp_enqueue_script('mcqhome-assessment', MCQHOME_THEME_URL . '/assets/js/assessment.js', ['jquery', 'mcqhome-question-navigation-panel', 'mcqhome-assessment-security'], MCQHOME_VERSION, true);
        
        // Enqueue Mobile Assessment Enhancements JavaScript
        if (file_exists(MCQHOME_THEME_DIR . '/assets/js/mobile-assessment-enhancements.js')) {
            wp_enqueue_script('mcqhome-mobile-assessment', MCQHOME_THEME_URL . '/assets/js/mobile-assessment-enhancements.js', ['jquery', 'mcqhome-assessment'], MCQHOME_VERSION, true);
        }
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
    try {
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
        if (function_exists('mcqhome_create_default_pages')) {
            mcqhome_create_default_pages();
        }
        
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
        
    } catch (Exception $e) {
        // Log any activation errors but don't break the site
        error_log('MCQHome: Theme activation error - ' . $e->getMessage());
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
            'content' => '<!-- Dashboard content will be loaded by the template -->',
            'template' => 'page-dashboard.php'
        ],
        'browse' => [
            'title' => 'Browse MCQs',
            'content' => '<!-- Browse content will be loaded by the template -->',
            'template' => 'page-browse.php'
        ],
        'institutions' => [
            'title' => 'Institutions',
            'content' => '<!-- Institutions content will be loaded by the template -->',
            'template' => 'page-institutions.php'
        ],
        'register' => [
            'title' => 'Register',
            'content' => '[mcqhome_registration]',
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
        try {
            $existing_page = get_page_by_path($slug);
            
            if (!$existing_page) {
                $page_id = wp_insert_post([
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $slug,
                ]);
                
                if ($page_id && !is_wp_error($page_id) && isset($page_data['template'])) {
                    update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                }
            }
        } catch (Exception $e) {
            // Log error but continue with other pages
            error_log('MCQHome: Failed to create page ' . $slug . ' - ' . $e->getMessage());
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
        wp_enqueue_script('mcqhome-mcq-set-builder', MCQHOME_THEME_URL . '/assets/js/mcq-set-builder.js', ['jquery', 'jquery-ui-sortable'], MCQHOME_VERSION, true);
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        // Localize script for MCQ Set builder
        wp_localize_script('mcqhome-mcq-set-builder', 'mcqSetBuilderL10n', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mcqhome_nonce'),
            'selectMedia' => __('Select Media', 'mcqhome'),
            'useMedia' => __('Use this media', 'mcqhome'),
            'thumbnailPreview' => __('Thumbnail Preview', 'mcqhome'),
            'uploadThumbnail' => __('Upload Thumbnail', 'mcqhome'),
            'remove' => __('Remove', 'mcqhome'),
            'sectionName' => __('Section Name', 'mcqhome'),
            'sectionDescription' => __('Section Description (optional)', 'mcqhome'),
            'section' => __('Section', 'mcqhome'),
            'edit' => __('Edit', 'mcqhome'),
            'questionAdded' => __('Question added successfully!', 'mcqhome'),
            'questionsAdded' => __('%d questions added successfully!', 'mcqhome'),
            'noQuestions' => __('No questions added yet. Create your first question above.', 'mcqhome'),
            'noExistingQuestions' => __('No existing questions available.', 'mcqhome'),
            'createFirstQuestion' => __('Create your first question using the form above.', 'mcqhome'),
            'errorLoadingQuestions' => __('Error loading existing questions.', 'mcqhome'),
            'loadingQuestions' => __('Loading available questions...', 'mcqhome'),
            'addSelectedQuestions' => __('Add Selected Questions', 'mcqhome'),
            'selectQuestionsFirst' => __('Please select questions to add.', 'mcqhome'),
            'questionsAlreadyAdded' => __('Selected questions are already in this set.', 'mcqhome'),
            'errorNoQuestion' => __('Please enter a question text.', 'mcqhome'),
            'errorEmptyOptions' => __('Please fill in all answer options.', 'mcqhome'),
            'errorNoCorrectAnswer' => __('Please select the correct answer.', 'mcqhome'),
            'errorNoExplanation' => __('Please provide an explanation for the correct answer.', 'mcqhome'),
            'errorGeneric' => __('An error occurred. Please try again.', 'mcqhome'),
            'unsavedChanges' => __('You have unsaved changes. Are you sure you want to leave?', 'mcqhome'),
            'addAnotherPrompt' => __('You can now add another question.', 'mcqhome'),
            'oneQuestion' => __('1 question', 'mcqhome'),
            'multipleQuestions' => __('%d questions', 'mcqhome'),
            'searchQuestions' => __('Search questions...', 'mcqhome'),
            'search' => __('Search', 'mcqhome'),
            'selectAll' => __('Select All', 'mcqhome'),
            'deselectAll' => __('Deselect All', 'mcqhome'),
            'retry' => __('Retry', 'mcqhome'),
            // Question management strings
            'editingQuestion' => __('Editing Question', 'mcqhome'),
            'cancel' => __('Cancel', 'mcqhome'),
            'questionText' => __('Question Text', 'mcqhome'),
            'explanation' => __('Explanation', 'mcqhome'),
            'noSection' => __('No Section', 'mcqhome'),
            'saveChanges' => __('Save Changes', 'mcqhome'),
            'questionUpdated' => __('Question updated successfully!', 'mcqhome'),
            'errorLoadingQuestion' => __('Error loading question for editing.', 'mcqhome'),
            'errorUpdatingQuestion' => __('Error updating question.', 'mcqhome'),
            'confirmDeleteQuestion' => __('Are you sure you want to delete "%s"?', 'mcqhome'),
            'questionDeleted' => __('Question deleted successfully!', 'mcqhome'),
            'errorDeletingQuestion' => __('Error deleting question.', 'mcqhome'),
            'questionsReordered' => __('Questions reordered successfully!', 'mcqhome'),
            // Bulk operations strings
            'bulkActions' => __('Bulk Actions', 'mcqhome'),
            'assignToSection' => __('Assign to Section', 'mcqhome'),
            'removeFromSection' => __('Remove from Section', 'mcqhome'),
            'deleteSelected' => __('Delete Selected', 'mcqhome'),
            'selectSection' => __('Select Section', 'mcqhome'),
            'apply' => __('Apply', 'mcqhome'),
            'selectActionFirst' => __('Please select an action first.', 'mcqhome'),
            'selectSectionFirst' => __('Please select a section first.', 'mcqhome'),
            'confirmBulkDelete' => __('Are you sure you want to delete %d selected questions?', 'mcqhome'),
            'questionsAssignedToSection' => __('Questions assigned to "%s" successfully!', 'mcqhome'),
            'questionsRemovedFromSections' => __('Questions removed from sections successfully!', 'mcqhome'),
            'questionsDeleted' => __('%d questions deleted successfully!', 'mcqhome'),
        ]);
    }
}
add_action('admin_enqueue_scripts', 'mcqhome_admin_scripts');

/**
 * Include additional theme files
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/template-functions.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/template-functions.php';
}

if (file_exists(MCQHOME_THEME_DIR . '/inc/customizer.php')) {
    require_once MCQHOME_THEME_DIR . '/inc/customizer.php';
}

// Include only essential files to prevent critical errors during theme activation
$essential_files = [
    '/inc/user-roles.php',
    '/inc/registration.php',
    '/inc/database-setup.php',
    '/inc/dashboard-functions.php',
    '/inc/role-settings.php',
    '/inc/seo-functions.php',
    '/inc/performance-optimization.php',
    '/inc/asset-minification.php',
    '/inc/semantic-html.php',
    '/inc/legacy-redirect-system.php',
    '/inc/redirect-admin.php',
    '/inc/demo-content-safe.php',
    '/inc/default-institution.php',
    '/inc/browse-search-functions.php'
];

foreach ($essential_files as $file) {
    $file_path = MCQHOME_THEME_DIR . $file;
    if (file_exists($file_path)) {
        try {
            require_once $file_path;
        } catch (Exception $e) {
            error_log('MCQHome: Failed to load ' . $file . ' - ' . $e->getMessage());
        }
    }
}

// Temporarily disable problematic files until they can be fixed
// These files have syntax errors that prevent theme activation:
// - /inc/post-types.php (unmatched braces)
// - /inc/ajax-handlers.php (duplicate functions) 
// - /inc/assessment-controller.php (unmatched braces)
// - /inc/assessment-security.php (duplicate methods)
// - /inc/assessment-functions.php (dependencies on broken files)

// Registration system is now properly handled by inc/registration.php

// Registration system is handled by inc/registration.php

/**
 * Create placeholder shortcodes to prevent errors
 */
function mcqhome_create_placeholder_shortcodes() {
    // Dashboard shortcode
    if (!shortcode_exists('mcqhome_dashboard')) {
        add_shortcode('mcqhome_dashboard', function() {
            return '<p>Dashboard functionality will be available soon.</p>';
        });
    }
    
    // Browse shortcode
    if (!shortcode_exists('mcqhome_browse')) {
        add_shortcode('mcqhome_browse', function() {
            return '<p>Browse functionality will be available soon.</p>';
        });
    }
    
    // Institutions shortcode
    if (!shortcode_exists('mcqhome_institutions')) {
        add_shortcode('mcqhome_institutions', function() {
            return '<p>Institutions functionality will be available soon.</p>';
        });
    }
    
    // Teachers shortcode
    if (!shortcode_exists('mcqhome_teachers')) {
        add_shortcode('mcqhome_teachers', function() {
            return '<p>Teachers functionality will be available soon.</p>';
        });
    }
}
add_action('init', 'mcqhome_create_placeholder_shortcodes', 5);

/**
 * Admin notice for setup issues
 */
function mcqhome_admin_notices() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Show notice about temporarily disabled features
    echo '<div class="notice notice-warning is-dismissible">';
    echo '<p><strong>MCQHome Theme:</strong> Some advanced features (MCQ creation, assessment system) are temporarily disabled due to code issues. Basic functionality (registration, user management) is available.</p>';
    echo '<p>The following features will be restored after code fixes:</p>';
    echo '<ul style="margin-left: 20px;">';
    echo '<li>• MCQ and MCQ Set creation</li>';
    echo '<li>• Assessment taking and results</li>';
    echo '<li>• Advanced AJAX handlers</li>';
    echo '</ul>';
    echo '</div>';
    
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
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>MCQHome Theme:</strong> Some database tables are missing but the theme will work with basic functionality. Missing tables: ' . implode(', ', $missing_tables) . '</p>';
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
/**
 
* Bulk assign questions to section
 */
function mcqhome_bulk_assign_questions_to_section($mcq_set_id, $question_ids, $section_id) {
    // Get current questions order
    $current_order = json_decode(get_post_meta($mcq_set_id, '_mcq_set_questions_order', true), true);
    if (!$current_order) {
        $current_order = ['questions' => []];
    }
    
    $updated_count = 0;
    
    // Update section assignment for each question
    foreach ($current_order['questions'] as &$question) {
        if (in_array($question['mcq_id'], $question_ids)) {
            $question['section_id'] = $section_id;
            
            // Also update the individual MCQ meta
            update_post_meta($question['mcq_id'], '_mcq_section_id', $section_id);
            $updated_count++;
        }
    }
    
    // Save updated order
    update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode($current_order));
    
    return [
        'message' => sprintf(__('%d questions assigned to section successfully.', 'mcqhome'), $updated_count),
        'updated_count' => $updated_count
    ];
}

/**
 * Bulk remove questions from sections
 */
function mcqhome_bulk_remove_questions_from_section($mcq_set_id, $question_ids) {
    // Get current questions order
    $current_order = json_decode(get_post_meta($mcq_set_id, '_mcq_set_questions_order', true), true);
    if (!$current_order) {
        $current_order = ['questions' => []];
    }
    
    $updated_count = 0;
    
    // Remove section assignment for each question
    foreach ($current_order['questions'] as &$question) {
        if (in_array($question['mcq_id'], $question_ids)) {
            $question['section_id'] = '';
            
            // Also update the individual MCQ meta
            delete_post_meta($question['mcq_id'], '_mcq_section_id');
            $updated_count++;
        }
    }
    
    // Save updated order
    update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode($current_order));
    
    return [
        'message' => sprintf(__('%d questions removed from sections successfully.', 'mcqhome'), $updated_count),
        'updated_count' => $updated_count
    ];
}

/**
 * Bulk delete questions
 */
function mcqhome_bulk_delete_questions($mcq_set_id, $question_ids) {
    // Get current questions order
    $current_order = json_decode(get_post_meta($mcq_set_id, '_mcq_set_questions_order', true), true);
    if (!$current_order) {
        $current_order = ['questions' => []];
    }
    
    $deleted_count = 0;
    $failed_deletions = [];
    
    // Check if questions can be deleted (not used in other MCQ sets)
    global $wpdb;
    foreach ($question_ids as $question_id) {
        $mcq_sets_using = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_mcq_set_questions_order' 
             AND meta_value LIKE %s
             AND post_id != %d",
            '%"mcq_id":' . $question_id . '%',
            $mcq_set_id
        ));
        
        if (!empty($mcq_sets_using)) {
            $failed_deletions[] = $question_id;
            continue;
        }
        
        // Delete the MCQ
        $deleted = wp_delete_post($question_id, true);
        if ($deleted) {
            $deleted_count++;
        } else {
            $failed_deletions[] = $question_id;
        }
    }
    
    // Remove deleted questions from the MCQ set order
    $current_order['questions'] = array_filter($current_order['questions'], function($question) use ($question_ids, $failed_deletions) {
        return !in_array($question['mcq_id'], $question_ids) || in_array($question['mcq_id'], $failed_deletions);
    });
    
    // Reindex the array and update order numbers
    $current_order['questions'] = array_values($current_order['questions']);
    foreach ($current_order['questions'] as $index => &$question) {
        $question['order'] = $index + 1;
    }
    
    // Save updated order
    update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode($current_order));
    
    $message = sprintf(__('%d questions deleted successfully.', 'mcqhome'), $deleted_count);
    if (!empty($failed_deletions)) {
        $message .= ' ' . sprintf(__('%d questions could not be deleted (used in other MCQ sets).', 'mcqhome'), count($failed_deletions));
    }
    
    return [
        'message' => $message,
        'deleted_count' => $deleted_count,
        'failed_count' => count($failed_deletions),
        'failed_ids' => $failed_deletions
    ];
}

/**
 * AJAX Handlers for Assessment Progress Tracking
 */

// Initialize assessment controller for AJAX
function mcqhome_init_assessment_ajax() {
    if (class_exists('MCQHome_Assessment_Controller')) {
        $assessment_controller = new MCQHome_Assessment_Controller();
        
        // Save progress AJAX handler
        add_action('wp_ajax_mcqhome_save_progress', [$assessment_controller, 'ajax_save_progress']);
        add_action('wp_ajax_nopriv_mcqhome_save_progress', [$assessment_controller, 'ajax_save_progress']);
        
        // Navigate to question AJAX handler
        add_action('wp_ajax_mcqhome_navigate_question', [$assessment_controller, 'ajax_navigate_to_question']);
        add_action('wp_ajax_nopriv_mcqhome_navigate_question', [$assessment_controller, 'ajax_navigate_to_question']);
    }
}
add_action('init', 'mcqhome_init_assessment_ajax');

/**
 * Enqueue scripts for visual progress tracking
 */
function mcqhome_enqueue_progress_tracking_scripts() {
    if (is_page_template('page-take-assessment.php') || is_singular('mcq_set')) {
        // Localize script for AJAX
        wp_localize_script('mcqhome-assessment', 'mcqhome_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mcqhome_assessment_nonce'),
            'strings' => [
                'progress_saved' => __('Progress saved', 'mcqhome'),
                'progress_save_failed' => __('Failed to save progress', 'mcqhome'),
                'navigation_failed' => __('Navigation failed', 'mcqhome'),
            ]
        ]);
    }
}
add_action('wp_enqueue_scripts', 'mcqhome_enqueue_progress_tracking_scripts', 20);

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