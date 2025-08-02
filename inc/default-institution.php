<?php
/**
 * Default Institution Management for MCQHome Theme
 * Handles MCQ Academy as the default institution for independent teachers
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MCQ Academy Default Institution Manager
 */
class MCQHome_Default_Institution {
    
    const DEFAULT_INSTITUTION_SLUG = 'mcq-academy';
    const DEFAULT_INSTITUTION_NAME = 'MCQ Academy';
    
    /**
     * Initialize default institution functionality
     */
    public function __construct() {
        add_action('after_switch_theme', [$this, 'ensure_default_institution_exists']);
        add_action('user_register', [$this, 'assign_default_institution_to_teacher'], 10, 1);
        add_filter('mcqhome_teacher_institution_options', [$this, 'add_default_institution_option'], 10, 1);
        add_action('wp_ajax_mcqhome_get_default_institution', [$this, 'ajax_get_default_institution']);
        add_action('wp_ajax_nopriv_mcqhome_get_default_institution', [$this, 'ajax_get_default_institution']);
    }
    
    /**
     * Ensure MCQ Academy default institution exists
     */
    public function ensure_default_institution_exists() {
        $default_institution = $this->get_default_institution();
        
        if (!$default_institution) {
            $this->create_default_institution();
        }
    }
    
    /**
     * Get the default institution (MCQ Academy)
     */
    public function get_default_institution() {
        // First try to find by meta key
        $institutions = get_posts([
            'post_type' => 'institution',
            'meta_key' => '_is_default_institution',
            'meta_value' => '1',
            'post_status' => 'publish',
            'numberposts' => 1
        ]);
        
        if (!empty($institutions)) {
            return $institutions[0];
        }
        
        // Fallback: try to find by slug
        $institution = get_page_by_path(self::DEFAULT_INSTITUTION_SLUG, OBJECT, 'institution');
        if ($institution) {
            // Mark it as default if found
            update_post_meta($institution->ID, '_is_default_institution', '1');
            return $institution;
        }
        
        return null;
    }
    
    /**
     * Create the default institution
     */
    public function create_default_institution() {
        $institution_data = [
            'post_title' => self::DEFAULT_INSTITUTION_NAME,
            'post_name' => self::DEFAULT_INSTITUTION_SLUG,
            'post_content' => $this->get_default_institution_description(),
            'post_status' => 'publish',
            'post_type' => 'institution',
            'post_author' => 1 // Admin user
        ];
        
        $institution_id = wp_insert_post($institution_data);
        
        if ($institution_id && !is_wp_error($institution_id)) {
            // Mark as default institution
            update_post_meta($institution_id, '_is_default_institution', '1');
            update_post_meta($institution_id, '_institution_type', 'default');
            update_post_meta($institution_id, '_institution_website', home_url());
            update_post_meta($institution_id, '_institution_description', $this->get_default_institution_description());
            update_post_meta($institution_id, '_institution_established', date('Y'));
            update_post_meta($institution_id, '_institution_location', 'Global');
            update_post_meta($institution_id, '_institution_specialization', 'Multi-disciplinary Education');
            update_post_meta($institution_id, '_auto_assign_independent_teachers', '1');
            
            // Set featured image if available
            $this->set_default_institution_featured_image($institution_id);
            
            return $institution_id;
        }
        
        return false;
    }
    
    /**
     * Get default institution description
     */
    private function get_default_institution_description() {
        return sprintf(
            __('MCQ Academy is the default institution for independent teachers and educators who want to share their knowledge and create quality multiple choice questions for students worldwide. As part of %s, MCQ Academy provides a platform for educators to reach students globally without being tied to a specific institution.', 'mcqhome'),
            get_bloginfo('name')
        );
    }
    
    /**
     * Set featured image for default institution
     */
    private function set_default_institution_featured_image($institution_id) {
        // Check if there's a default institution image in the theme
        $default_image_path = get_template_directory() . '/assets/images/mcq-academy-logo.png';
        
        if (file_exists($default_image_path)) {
            // Upload the image to media library
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($default_image_path);
            $filename = 'mcq-academy-logo.png';
            
            if (wp_mkdir_p($upload_dir['path'])) {
                $file = $upload_dir['path'] . '/' . $filename;
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }
            
            file_put_contents($file, $image_data);
            
            $wp_filetype = wp_check_filetype($filename, null);
            $attachment = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            ];
            
            $attach_id = wp_insert_attachment($attachment, $file, $institution_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            set_post_thumbnail($institution_id, $attach_id);
        }
    }
    
    /**
     * Assign default institution to teachers without institution
     */
    public function assign_default_institution_to_teacher($user_id) {
        $user = get_userdata($user_id);
        
        // Only process teachers
        if (!in_array('teacher', $user->roles)) {
            return;
        }
        
        // Check if teacher already has an institution assigned
        $existing_institution = get_user_meta($user_id, 'institution_id', true);
        if (!empty($existing_institution)) {
            return;
        }
        
        // Assign default institution
        $default_institution = $this->get_default_institution();
        if ($default_institution) {
            update_user_meta($user_id, 'institution_id', $default_institution->ID);
            
            // Log this assignment
            $this->log_default_institution_assignment($user_id, $default_institution->ID);
        }
    }
    
    /**
     * Add default institution option to teacher registration
     */
    public function add_default_institution_option($options) {
        $default_institution = $this->get_default_institution();
        
        if ($default_institution) {
            // Add as the first option
            $default_option = [
                'id' => $default_institution->ID,
                'name' => $default_institution->post_title,
                'description' => __('For independent educators not affiliated with a specific institution', 'mcqhome'),
                'is_default' => true
            ];
            
            array_unshift($options, $default_option);
        }
        
        return $options;
    }
    
    /**
     * AJAX handler to get default institution info
     */
    public function ajax_get_default_institution() {
        $default_institution = $this->get_default_institution();
        
        if ($default_institution) {
            wp_send_json_success([
                'id' => $default_institution->ID,
                'name' => $default_institution->post_title,
                'description' => get_post_meta($default_institution->ID, '_institution_description', true),
                'url' => get_permalink($default_institution->ID)
            ]);
        } else {
            wp_send_json_error(__('Default institution not found', 'mcqhome'));
        }
    }
    
    /**
     * Log default institution assignment
     */
    private function log_default_institution_assignment($user_id, $institution_id) {
        $user = get_userdata($user_id);
        
        error_log(sprintf(
            'MCQHome: User %s (ID: %d) automatically assigned to default institution (ID: %d)',
            $user->user_login,
            $user_id,
            $institution_id
        ));
        
        // Store assignment history
        $assignment_history = get_user_meta($user_id, '_institution_assignment_history', true) ?: [];
        $assignment_history[] = [
            'institution_id' => $institution_id,
            'assigned_at' => current_time('mysql'),
            'assignment_type' => 'automatic_default'
        ];
        update_user_meta($user_id, '_institution_assignment_history', $assignment_history);
    }
    
    /**
     * Get teachers assigned to default institution
     */
    public function get_default_institution_teachers() {
        $default_institution = $this->get_default_institution();
        
        if (!$default_institution) {
            return [];
        }
        
        return get_users([
            'role' => 'teacher',
            'meta_key' => 'institution_id',
            'meta_value' => $default_institution->ID,
            'meta_compare' => '='
        ]);
    }
    
    /**
     * Get content created by default institution teachers
     */
    public function get_default_institution_content($post_type = 'mcq', $limit = 10) {
        $teachers = $this->get_default_institution_teachers();
        
        if (empty($teachers)) {
            return [];
        }
        
        $teacher_ids = wp_list_pluck($teachers, 'ID');
        
        return get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'author__in' => $teacher_ids,
            'numberposts' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
    }
    
    /**
     * Check if user belongs to default institution
     */
    public function is_default_institution_member($user_id) {
        $default_institution = $this->get_default_institution();
        
        if (!$default_institution) {
            return false;
        }
        
        $user_institution = get_user_meta($user_id, 'institution_id', true);
        return $user_institution == $default_institution->ID;
    }
    
    /**
     * Get default institution statistics
     */
    public function get_default_institution_stats() {
        $default_institution = $this->get_default_institution();
        
        if (!$default_institution) {
            return null;
        }
        
        $teachers = $this->get_default_institution_teachers();
        $teacher_ids = wp_list_pluck($teachers, 'ID');
        
        $mcqs_count = 0;
        $mcq_sets_count = 0;
        
        if (!empty($teacher_ids)) {
            $mcqs_count = count(get_posts([
                'post_type' => 'mcq',
                'post_status' => 'publish',
                'author__in' => $teacher_ids,
                'numberposts' => -1,
                'fields' => 'ids'
            ]));
            
            $mcq_sets_count = count(get_posts([
                'post_type' => 'mcq_set',
                'post_status' => 'publish',
                'author__in' => $teacher_ids,
                'numberposts' => -1,
                'fields' => 'ids'
            ]));
        }
        
        return [
            'institution_id' => $default_institution->ID,
            'institution_name' => $default_institution->post_title,
            'teachers_count' => count($teachers),
            'mcqs_count' => $mcqs_count,
            'mcq_sets_count' => $mcq_sets_count,
            'established' => get_post_meta($default_institution->ID, '_institution_established', true),
            'url' => get_permalink($default_institution->ID)
        ];
    }
    
    /**
     * Display default institution info in admin
     */
    public function display_admin_info() {
        $stats = $this->get_default_institution_stats();
        
        if (!$stats) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . __('MCQ Academy default institution is not set up. This may cause issues with teacher registration.', 'mcqhome') . '</p>';
            echo '</div>';
            return;
        }
        
        echo '<div class="mcq-academy-info">';
        echo '<h3>' . esc_html($stats['institution_name']) . '</h3>';
        echo '<p>' . __('Default institution for independent teachers', 'mcqhome') . '</p>';
        echo '<ul>';
        echo '<li>' . sprintf(__('Teachers: %d', 'mcqhome'), $stats['teachers_count']) . '</li>';
        echo '<li>' . sprintf(__('MCQs: %d', 'mcqhome'), $stats['mcqs_count']) . '</li>';
        echo '<li>' . sprintf(__('MCQ Sets: %d', 'mcqhome'), $stats['mcq_sets_count']) . '</li>';
        echo '</ul>';
        echo '<p><a href="' . esc_url($stats['url']) . '" class="button">' . __('View Institution Page', 'mcqhome') . '</a></p>';
        echo '</div>';
    }
}

// Initialize default institution manager safely
function mcqhome_init_default_institution() {
    if (class_exists('MCQHome_Default_Institution')) {
        try {
            new MCQHome_Default_Institution();
        } catch (Exception $e) {
            error_log('MCQHome: Failed to initialize default institution - ' . $e->getMessage());
        }
    }
}
add_action('init', 'mcqhome_init_default_institution', 15);

/**
 * Helper functions for default institution
 */

/**
 * Get the default institution
 */
function mcqhome_get_default_institution() {
    static $manager = null;
    if ($manager === null && class_exists('MCQHome_Default_Institution')) {
        try {
            $manager = new MCQHome_Default_Institution();
        } catch (Exception $e) {
            error_log('MCQHome: Error creating default institution manager - ' . $e->getMessage());
            return null;
        }
    }
    return $manager ? $manager->get_default_institution() : null;
}

/**
 * Check if user belongs to default institution
 */
function mcqhome_is_default_institution_member($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    static $manager = null;
    if ($manager === null && class_exists('MCQHome_Default_Institution')) {
        try {
            $manager = new MCQHome_Default_Institution();
        } catch (Exception $e) {
            error_log('MCQHome: Error creating default institution manager - ' . $e->getMessage());
            return false;
        }
    }
    return $manager ? $manager->is_default_institution_member($user_id) : false;
}

/**
 * Get default institution teachers
 */
function mcqhome_get_default_institution_teachers() {
    static $manager = null;
    if ($manager === null && class_exists('MCQHome_Default_Institution')) {
        try {
            $manager = new MCQHome_Default_Institution();
        } catch (Exception $e) {
            error_log('MCQHome: Error creating default institution manager - ' . $e->getMessage());
            return [];
        }
    }
    return $manager ? $manager->get_default_institution_teachers() : [];
}

/**
 * Get default institution content
 */
function mcqhome_get_default_institution_content($post_type = 'mcq', $limit = 10) {
    $manager = new MCQHome_Default_Institution();
    return $manager->get_default_institution_content($post_type, $limit);
}

/**
 * Get default institution statistics
 */
function mcqhome_get_default_institution_stats() {
    $manager = new MCQHome_Default_Institution();
    return $manager->get_default_institution_stats();
}

/**
 * Modify user registration form to show default institution option
 */
function mcqhome_add_default_institution_to_registration_form() {
    $default_institution = mcqhome_get_default_institution();
    
    if ($default_institution) {
        echo '<div class="mcq-academy-option" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<label>';
        echo '<input type="radio" name="institution_choice" value="default" checked> ';
        echo '<strong>' . esc_html($default_institution->post_title) . '</strong>';
        echo '</label>';
        echo '<p style="margin: 5px 0 0 20px; font-size: 0.9em; color: #666;">';
        echo __('Perfect for independent educators not affiliated with a specific institution', 'mcqhome');
        echo '</p>';
        echo '</div>';
    }
}

/**
 * Handle teacher registration with default institution
 */
function mcqhome_handle_teacher_registration_with_default_institution($user_id, $user_data) {
    // Check if this is a teacher registration
    if (!isset($user_data['role']) || $user_data['role'] !== 'teacher') {
        return;
    }
    
    // Check if default institution was selected or no institution specified
    $institution_choice = $_POST['institution_choice'] ?? 'default';
    
    if ($institution_choice === 'default' || empty($_POST['institution_id'])) {
        $default_institution = mcqhome_get_default_institution();
        
        if ($default_institution) {
            update_user_meta($user_id, 'institution_id', $default_institution->ID);
            
            // Send welcome email mentioning MCQ Academy
            mcqhome_send_default_institution_welcome_email($user_id, $default_institution);
        }
    }
}
add_action('mcqhome_teacher_registered', 'mcqhome_handle_teacher_registration_with_default_institution', 10, 2);

/**
 * Send welcome email for default institution members
 */
function mcqhome_send_default_institution_welcome_email($user_id, $institution) {
    $user = get_userdata($user_id);
    
    $subject = sprintf(__('Welcome to %s - %s', 'mcqhome'), get_bloginfo('name'), $institution->post_title);
    
    $message = sprintf(
        __('Hello %s,

Welcome to %s! You have been automatically assigned to %s, our default institution for independent educators.

As a member of %s, you can:
- Create and publish MCQs
- Build MCQ sets for students
- Reach students worldwide
- Track your content performance

Your institution page: %s

Get started by creating your first MCQ!

Best regards,
The %s Team', 'mcqhome'),
        $user->display_name,
        get_bloginfo('name'),
        $institution->post_title,
        $institution->post_title,
        get_permalink($institution->ID),
        get_bloginfo('name')
    );
    
    wp_mail($user->user_email, $subject, $message);
}