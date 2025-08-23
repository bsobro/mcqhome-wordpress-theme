<?php
/**
 * MCQHome Theme functions - Restored Steps 1-6 (Working Version)
 * Step 7 (Enhanced Features) removed due to critical error
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
        ]);
    }
}
add_action('wp_enqueue_scripts', 'mcqhome_scripts');

/**
 * Step 1: Registration System
 */
$step1_success = false;
if (file_exists(MCQHOME_THEME_DIR . '/inc/registration.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/registration.php';
        $step1_success = true;
        error_log('MCQHome: Step 1 - Registration System loaded successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Step 1 - Registration System failed - ' . $e->getMessage());
        $step1_success = false;
    }
} else {
    $step1_success = false;
}

/**
 * Step 2: User Roles and Capabilities
 */
$step2_success = false;
if (file_exists(MCQHOME_THEME_DIR . '/inc/user-roles.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/user-roles.php';
        $step2_success = true;
        error_log('MCQHome: Step 2 - User Roles loaded successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Step 2 - User Roles failed - ' . $e->getMessage());
        $step2_success = false;
    }
} else {
    $step2_success = false;
}

/**
 * Step 3: Dashboard System
 */
$step3_success = false;
if (file_exists(MCQHOME_THEME_DIR . '/inc/dashboard-functions.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/dashboard-functions.php';
        $step3_success = true;
        error_log('MCQHome: Step 3 - Dashboard System loaded successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Step 3 - Dashboard System failed - ' . $e->getMessage());
        $step3_success = false;
    }
} else {
    $step3_success = false;
}

/**
 * Step 4: Database Setup and Core Functions
 */
$step4_success = false;
if (file_exists(MCQHOME_THEME_DIR . '/inc/database-setup.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/database-setup.php';
        $step4_success = true;
        error_log('MCQHome: Step 4 - Database Setup loaded successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Step 4 - Database Setup failed - ' . $e->getMessage());
        $step4_success = false;
    }
} else {
    $step4_success = false;
}

/**
 * Step 5: Basic Custom Post Types
 */
$step5_success = false;
if (file_exists(MCQHOME_THEME_DIR . '/inc/post-types.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/post-types.php';
        $step5_success = true;
        error_log('MCQHome: Step 5 - Basic Custom Post Types loaded successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Step 5 - Basic Custom Post Types failed - ' . $e->getMessage());
        $step5_success = false;
    }
} else {
    $step5_success = false;
}

/**
 * Step 6: Assessment System
 */
$step6_success = false;
if (file_exists(MCQHOME_THEME_DIR . '/inc/assessment-system.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/assessment-system.php';
        $step6_success = true;
        error_log('MCQHome: Step 6 - Assessment System loaded successfully');
    } catch (Exception $e) {
        error_log('MCQHome: Step 6 - Assessment System failed - ' . $e->getMessage());
        $step6_success = false;
    }
} else {
    $step6_success = false;
}

/**
 * Additional Essential Files (Safe to load)
 */
$additional_files = [
    '/inc/role-settings.php',
    '/inc/demo-content-safe.php',
    '/inc/default-institution.php'
];

$additional_loaded = 0;
foreach ($additional_files as $file) {
    $file_path = MCQHOME_THEME_DIR . $file;
    if (file_exists($file_path)) {
        try {
            require_once $file_path;
            $additional_loaded++;
            error_log('MCQHome: Additional file loaded - ' . $file);
        } catch (Exception $e) {
            error_log('MCQHome: Failed to load ' . $file . ' - ' . $e->getMessage());
        }
    }
}

// Step 7: Enhanced Features - DISABLED due to critical error
// Will be restored carefully one by one after testing each file
$step7_success = false;
$step7_loaded = [];

/**
 * Count completed steps
 */
$completed_steps = 0;
if ($step1_success) $completed_steps++;
if ($step2_success) $completed_steps++;
if ($step3_success) $completed_steps++;
if ($step4_success) $completed_steps++;
if ($step5_success) $completed_steps++;
if ($step6_success) $completed_steps++;

/**
 * Admin notice showing progress
 */
add_action('admin_notices', function() use ($step1_success, $step2_success, $step3_success, $step4_success, $step5_success, $step6_success, $additional_loaded) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $completed_steps = 0;
    if ($step1_success) $completed_steps++;
    if ($step2_success) $completed_steps++;
    if ($step3_success) $completed_steps++;
    if ($step4_success) $completed_steps++;
    if ($step5_success) $completed_steps++;
    if ($step6_success) $completed_steps++;
    
    echo '<div class="notice notice-info is-dismissible" style="padding: 15px; border-left: 4px solid #0073aa;">';
    echo '<h3 style="margin-top: 0;">🚀 MCQHome Theme - System Status</h3>';
    echo '<ul style="margin-left: 20px;">';
    echo '<li>' . ($step1_success ? '✅' : '❌') . ' <strong>Step 1:</strong> Registration System</li>';
    echo '<li>' . ($step2_success ? '✅' : '❌') . ' <strong>Step 2:</strong> User Roles & Capabilities</li>';
    echo '<li>' . ($step3_success ? '✅' : '❌') . ' <strong>Step 3:</strong> Dashboard System</li>';
    echo '<li>' . ($step4_success ? '✅' : '❌') . ' <strong>Step 4:</strong> Database Setup</li>';
    echo '<li>' . ($step5_success ? '✅' : '❌') . ' <strong>Step 5:</strong> Basic Custom Post Types</li>';
    echo '<li>' . ($step6_success ? '✅' : '❌') . ' <strong>Step 6:</strong> Assessment System</li>';
    echo '<li>' . ($additional_loaded > 0 ? '✅' : '❌') . ' <strong>Additional:</strong> Essential Files (' . $additional_loaded . '/3)</li>';
    echo '<li>⏸️ <strong>Step 7:</strong> Enhanced Features (Paused - will restore carefully)</li>';
    echo '</ul>';
    
    if ($completed_steps == 6) {
        echo '<p><strong>🎉 ALL CORE STEPS COMPLETE!</strong> MCQHome theme is fully functional!</p>';
        echo '<p><strong>✨ What you can do now:</strong></p>';
        echo '<ul style="margin-left: 40px; margin-top: 10px;">';
        echo '<li>• Create MCQ Sets with questions</li>';
        echo '<li>• Students can register and take assessments</li>';
        echo '<li>• View results and track progress</li>';
        echo '<li>• Manage institutions and user roles</li>';
        echo '</ul>';
        echo '<p><em>Enhanced features will be restored carefully one by one.</em></p>';
    } else {
        echo '<p><strong>⚠️ Some components failed to load.</strong> Check error logs for details.</p>';
    }
    
    echo '<p><em>Progress: ' . $completed_steps . '/6 core systems restored</em></p>';
    echo '</div>';
});

/**
 * Theme activation hook
 */
function mcqhome_activation() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set default options
    add_option('mcqhome_setup_complete', false);
    add_option('mcqhome_demo_content', false);
}
add_action('after_switch_theme', 'mcqhome_activation');

/**
 * Include safe additional theme files
 */
if (file_exists(MCQHOME_THEME_DIR . '/inc/customizer.php')) {
    try {
        require_once MCQHOME_THEME_DIR . '/inc/customizer.php';
    } catch (Exception $e) {
        error_log('MCQHome: Failed to load customizer - ' . $e->getMessage());
    }
}