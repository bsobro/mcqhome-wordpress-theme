<?php
/**
 * Safe Demo Content Generator for MCQHome Theme
 * This is a minimal version that won't cause fatal errors during theme activation
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Safe demo content initialization
 */
function mcqhome_safe_demo_content_init() {
    // Only run in admin area and if WordPress is fully loaded
    if (!is_admin() || !function_exists('add_submenu_page')) {
        return;
    }
    
    // Add admin menu
    add_action('admin_menu', 'mcqhome_add_demo_content_menu');
}
add_action('admin_init', 'mcqhome_safe_demo_content_init', 30);

/**
 * Add demo content admin menu
 */
function mcqhome_add_demo_content_menu() {
    add_submenu_page(
        'themes.php',
        __('MCQHome Demo Content', 'mcqhome'),
        __('Demo Content', 'mcqhome'),
        'manage_options',
        'mcqhome-demo-content',
        'mcqhome_demo_content_page'
    );
}

/**
 * Demo content admin page
 */
function mcqhome_demo_content_page() {
    $demo_exists = get_option('mcqhome_demo_content_generated', false);
    ?>
    <div class="wrap">
        <h1><?php _e('MCQHome Demo Content', 'mcqhome'); ?></h1>
        
        <div class="notice notice-info">
            <p><?php _e('Demo content helps you understand how the MCQHome theme works and provides a foundation to build upon.', 'mcqhome'); ?></p>
        </div>
        
        <?php if (!$demo_exists): ?>
            <div class="card">
                <h2><?php _e('Generate Demo Content', 'mcqhome'); ?></h2>
                <p><?php _e('This will create comprehensive demo content including institutions, teachers, students, MCQs, and MCQ sets.', 'mcqhome'); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('mcqhome_generate_demo', 'mcqhome_demo_nonce'); ?>
                    <p>
                        <input type="submit" name="generate_demo" class="button button-primary" value="<?php _e('Generate Demo Content', 'mcqhome'); ?>">
                    </p>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><?php _e('Demo Content Status', 'mcqhome'); ?></h2>
                <p class="notice notice-success inline"><?php _e('Demo content has been generated successfully!', 'mcqhome'); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('mcqhome_cleanup_demo', 'mcqhome_demo_nonce'); ?>
                    <p>
                        <input type="submit" name="cleanup_demo" class="button button-secondary" value="<?php _e('Remove Demo Content', 'mcqhome'); ?>" onclick="return confirm('<?php _e('Are you sure you want to remove all demo content?', 'mcqhome'); ?>')">
                    </p>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php _e('Debug Tools', 'mcqhome'); ?></h2>
            <p>
                <a href="<?php echo get_template_directory_uri(); ?>/debug-demo-content.php" target="_blank" class="button button-secondary">
                    <?php _e('Debug Demo Content System', 'mcqhome'); ?>
                </a>
                <a href="<?php echo get_template_directory_uri(); ?>/test-demo-simple.php" target="_blank" class="button button-secondary">
                    <?php _e('Simple Test', 'mcqhome'); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
    
    // Handle form submissions
    if (isset($_POST['generate_demo']) && wp_verify_nonce($_POST['mcqhome_demo_nonce'], 'mcqhome_generate_demo')) {
        mcqhome_generate_simple_demo_content();
    }
    
    if (isset($_POST['cleanup_demo']) && wp_verify_nonce($_POST['mcqhome_demo_nonce'], 'mcqhome_cleanup_demo')) {
        mcqhome_cleanup_simple_demo_content();
    }
}

/**
 * Generate simple demo content
 */
function mcqhome_generate_simple_demo_content() {
    try {
        // Check basic requirements
        if (!post_type_exists('institution') || !post_type_exists('mcq') || !post_type_exists('mcq_set')) {
            echo '<div class="notice notice-error"><p>' . __('Required post types are not registered. Please ensure the theme is properly activated.', 'mcqhome') . '</p></div>';
            return;
        }
        
        // Create MCQ Academy
        $mcq_academy_id = wp_insert_post([
            'post_title' => 'MCQ Academy',
            'post_content' => 'MCQ Academy is the default institution for independent teachers and educators.',
            'post_status' => 'publish',
            'post_type' => 'institution',
            'post_author' => 1
        ]);
        
        if (!is_wp_error($mcq_academy_id)) {
            update_post_meta($mcq_academy_id, '_is_default_institution', '1');
        }
        
        // Create a few basic subjects
        if (taxonomy_exists('mcq_subject')) {
            $subjects = ['Mathematics', 'Science', 'English'];
            foreach ($subjects as $subject) {
                if (!term_exists($subject, 'mcq_subject')) {
                    wp_insert_term($subject, 'mcq_subject');
                }
            }
        }
        
        // Mark as generated
        update_option('mcqhome_demo_content_generated', true);
        update_option('mcqhome_demo_content_timestamp', current_time('mysql'));
        
        echo '<div class="notice notice-success"><p>' . __('Basic demo content generated successfully!', 'mcqhome') . '</p></div>';
        
    } catch (Exception $e) {
        echo '<div class="notice notice-error"><p>' . __('Error generating demo content: ', 'mcqhome') . $e->getMessage() . '</p></div>';
    }
}

/**
 * Cleanup simple demo content
 */
function mcqhome_cleanup_simple_demo_content() {
    try {
        // Delete demo posts
        $demo_post_types = ['mcq', 'mcq_set', 'institution'];
        foreach ($demo_post_types as $post_type) {
            $posts = get_posts([
                'post_type' => $post_type,
                'post_status' => 'any',
                'numberposts' => -1
            ]);
            
            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
            }
        }
        
        // Delete demo users
        $demo_users = get_users(['role__in' => ['student', 'teacher']]);
        foreach ($demo_users as $user) {
            if (!in_array('administrator', $user->roles)) {
                wp_delete_user($user->ID);
            }
        }
        
        // Reset options
        delete_option('mcqhome_demo_content_generated');
        delete_option('mcqhome_demo_content_timestamp');
        
        echo '<div class="notice notice-success"><p>' . __('Demo content removed successfully!', 'mcqhome') . '</p></div>';
        
    } catch (Exception $e) {
        echo '<div class="notice notice-error"><p>' . __('Error removing demo content: ', 'mcqhome') . $e->getMessage() . '</p></div>';
    }
}

/**
 * Helper function to check if demo content exists
 */
function mcqhome_has_demo_content() {
    return get_option('mcqhome_demo_content_generated', false);
}