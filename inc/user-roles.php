<?php
/**
 * Custom User Roles and Capabilities
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize custom user roles and capabilities
 */
function mcqhome_init_user_roles() {
    // Remove default subscriber role capabilities that we don't want
    $subscriber = get_role('subscriber');
    if ($subscriber) {
        $subscriber->remove_cap('read');
    }
    
    // Create custom roles
    mcqhome_create_student_role();
    mcqhome_create_teacher_role();
    mcqhome_create_institution_role();
    mcqhome_setup_admin_role();
}

/**
 * Create Student role with specific capabilities
 */
function mcqhome_create_student_role() {
    $capabilities = [
        // Basic WordPress capabilities
        'read' => true,
        
        // MCQ-specific capabilities
        'take_mcq' => true,
        'view_mcq_results' => true,
        'track_progress' => true,
        'follow_institutions' => true,
        'follow_teachers' => true,
        'enroll_in_sets' => true,
        'view_dashboard' => true,
        'browse_content' => true,
        'view_profile' => true,
        'edit_own_profile' => true,
        
        // Social features
        'create_comments' => true,
        'rate_content' => true,
    ];
    
    // Remove existing role if it exists to update capabilities
    if (get_role('student')) {
        remove_role('student');
    }
    
    add_role('student', __('Student', 'mcqhome'), $capabilities);
}

/**
 * Create Teacher role with specific capabilities
 */
function mcqhome_create_teacher_role() {
    $capabilities = [
        // Basic WordPress capabilities
        'read' => true,
        'upload_files' => true,
        
        // MCQ creation and management
        'create_mcq' => true,
        'edit_mcq' => true,
        'edit_own_mcq' => true,
        'delete_own_mcq' => true,
        'publish_mcq' => true,
        'read_private_mcq' => true,
        
        // MCQ Set management
        'create_mcq_set' => true,
        'edit_mcq_set' => true,
        'edit_own_mcq_set' => true,
        'delete_own_mcq_set' => true,
        'publish_mcq_set' => true,
        'read_private_mcq_set' => true,
        
        // Student management
        'view_student_progress' => true,
        'manage_enrollments' => true,
        'view_student_results' => true,
        
        // Content management
        'manage_own_content' => true,
        'view_content_analytics' => true,
        'moderate_comments' => true,
        
        // Dashboard and profile
        'view_dashboard' => true,
        'view_teacher_dashboard' => true,
        'browse_content' => true,
        'view_profile' => true,
        'edit_own_profile' => true,
        
        // Institution association
        'associate_with_institution' => true,
        'view_institution_content' => true,
        
        // All student capabilities
        'take_mcq' => true,
        'view_mcq_results' => true,
        'track_progress' => true,
        'follow_institutions' => true,
        'follow_teachers' => true,
        'enroll_in_sets' => true,
        'create_comments' => true,
        'rate_content' => true,
    ];
    
    // Remove existing role if it exists to update capabilities
    if (get_role('teacher')) {
        remove_role('teacher');
    }
    
    add_role('teacher', __('Teacher', 'mcqhome'), $capabilities);
}

/**
 * Create Institution role with specific capabilities
 */
function mcqhome_create_institution_role() {
    $capabilities = [
        // Basic WordPress capabilities
        'read' => true,
        'upload_files' => true,
        
        // Institution management
        'manage_institution' => true,
        'edit_institution_profile' => true,
        'customize_institution_branding' => true,
        
        // Teacher management
        'manage_teachers' => true,
        'add_teachers' => true,
        'remove_teachers' => true,
        'view_teacher_performance' => true,
        'assign_teacher_permissions' => true,
        
        // Student management
        'view_all_students' => true,
        'manage_student_enrollments' => true,
        'view_student_progress' => true,
        'view_student_results' => true,
        'generate_student_reports' => true,
        
        // Content oversight
        'view_all_institution_content' => true,
        'moderate_institution_content' => true,
        'approve_teacher_content' => true,
        'manage_content_categories' => true,
        
        // Analytics and reporting
        'view_institution_analytics' => true,
        'generate_reports' => true,
        'export_data' => true,
        
        // MCQ management (institutional level)
        'view_all_mcq' => true,
        'edit_institution_mcq' => true,
        'delete_institution_mcq' => true,
        'view_all_mcq_set' => true,
        'edit_institution_mcq_set' => true,
        'delete_institution_mcq_set' => true,
        
        // Dashboard and profile
        'view_dashboard' => true,
        'view_institution_dashboard' => true,
        'browse_content' => true,
        'view_profile' => true,
        'edit_own_profile' => true,
        
        // All teacher capabilities
        'create_mcq' => true,
        'edit_mcq' => true,
        'edit_own_mcq' => true,
        'delete_own_mcq' => true,
        'publish_mcq' => true,
        'read_private_mcq' => true,
        'create_mcq_set' => true,
        'edit_mcq_set' => true,
        'edit_own_mcq_set' => true,
        'delete_own_mcq_set' => true,
        'publish_mcq_set' => true,
        'read_private_mcq_set' => true,
        'manage_own_content' => true,
        'view_content_analytics' => true,
        'moderate_comments' => true,
        'associate_with_institution' => true,
        'view_institution_content' => true,
        
        // All student capabilities
        'take_mcq' => true,
        'view_mcq_results' => true,
        'track_progress' => true,
        'follow_institutions' => true,
        'follow_teachers' => true,
        'enroll_in_sets' => true,
        'create_comments' => true,
        'rate_content' => true,
    ];
    
    // Remove existing role if it exists to update capabilities
    if (get_role('institution')) {
        remove_role('institution');
    }
    
    add_role('institution', __('Institution', 'mcqhome'), $capabilities);
}

/**
 * Setup Admin role with additional MCQ-specific capabilities
 */
function mcqhome_setup_admin_role() {
    $admin = get_role('administrator');
    
    if ($admin) {
        // Add all custom capabilities to admin
        $custom_capabilities = [
            // MCQ management
            'create_mcq',
            'edit_mcq',
            'edit_others_mcq',
            'edit_own_mcq',
            'delete_mcq',
            'delete_others_mcq',
            'delete_own_mcq',
            'publish_mcq',
            'read_private_mcq',
            'manage_mcq_categories',
            
            // MCQ Set management
            'create_mcq_set',
            'edit_mcq_set',
            'edit_others_mcq_set',
            'edit_own_mcq_set',
            'delete_mcq_set',
            'delete_others_mcq_set',
            'delete_own_mcq_set',
            'publish_mcq_set',
            'read_private_mcq_set',
            'manage_mcq_set_categories',
            
            // Institution management
            'create_institution',
            'edit_institution',
            'edit_others_institution',
            'delete_institution',
            'delete_others_institution',
            'publish_institution',
            'read_private_institution',
            'manage_institutions',
            
            // User management
            'manage_all_users',
            'view_all_user_data',
            'modify_user_roles',
            'bulk_user_operations',
            
            // System administration
            'manage_mcq_settings',
            'view_system_analytics',
            'export_all_data',
            'import_content',
            'manage_demo_content',
            'system_maintenance',
            
            // All role-specific capabilities
            'take_mcq',
            'view_mcq_results',
            'track_progress',
            'follow_institutions',
            'follow_teachers',
            'enroll_in_sets',
            'view_dashboard',
            'browse_content',
            'view_profile',
            'edit_own_profile',
            'create_comments',
            'rate_content',
            'manage_teachers',
            'add_teachers',
            'remove_teachers',
            'view_teacher_performance',
            'assign_teacher_permissions',
            'view_all_students',
            'manage_student_enrollments',
            'view_student_progress',
            'view_student_results',
            'generate_student_reports',
            'view_all_institution_content',
            'moderate_institution_content',
            'approve_teacher_content',
            'manage_content_categories',
            'view_institution_analytics',
            'generate_reports',
            'export_data',
            'manage_institution',
            'edit_institution_profile',
            'customize_institution_branding',
            'view_institution_dashboard',
            'view_teacher_dashboard',
            'manage_own_content',
            'view_content_analytics',
            'moderate_comments',
            'associate_with_institution',
            'view_institution_content',
        ];
        
        foreach ($custom_capabilities as $cap) {
            $admin->add_cap($cap);
        }
    }
}

/**
 * Get user role display name
 */
function mcqhome_get_user_role_display_name($role) {
    $role_names = [
        'student' => __('Student', 'mcqhome'),
        'teacher' => __('Teacher', 'mcqhome'),
        'institution' => __('Institution', 'mcqhome'),
        'administrator' => __('Administrator', 'mcqhome'),
    ];
    
    return isset($role_names[$role]) ? $role_names[$role] : ucfirst($role);
}

/**
 * Check if user has specific MCQ capability
 */
function mcqhome_user_can($capability, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    return user_can($user_id, $capability);
}

/**
 * Get user's primary role
 */
function mcqhome_get_user_primary_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    $roles = $user->roles;
    
    // Priority order for roles
    $role_priority = ['administrator', 'institution', 'teacher', 'student'];
    
    foreach ($role_priority as $role) {
        if (in_array($role, $roles)) {
            return $role;
        }
    }
    
    return isset($roles[0]) ? $roles[0] : false;
}

/**
 * Check if user belongs to specific institution
 */
function mcqhome_user_belongs_to_institution($user_id, $institution_id) {
    $user_institution = get_user_meta($user_id, 'institution_id', true);
    return $user_institution == $institution_id;
}

/**
 * Get users by role with pagination
 */
function mcqhome_get_users_by_role($role, $args = []) {
    $default_args = [
        'role' => $role,
        'number' => 20,
        'paged' => 1,
        'orderby' => 'registered',
        'order' => 'DESC',
    ];
    
    $args = wp_parse_args($args, $default_args);
    
    return get_users($args);
}

/**
 * Role-based access control for content
 */
function mcqhome_can_user_access_content($post_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $user_role = mcqhome_get_user_primary_role($user_id);
    $post = get_post($post_id);
    
    if (!$post) {
        return false;
    }
    
    // Admin can access everything
    if ($user_role === 'administrator') {
        return true;
    }
    
    // Check if content is published
    if ($post->post_status !== 'publish') {
        // Only author and institution can access unpublished content
        if ($post->post_author == $user_id) {
            return true;
        }
        
        if ($user_role === 'institution') {
            $content_institution = get_post_meta($post_id, 'institution_id', true);
            $user_institution = get_user_meta($user_id, 'institution_id', true);
            return $content_institution == $user_institution;
        }
        
        return false;
    }
    
    // For published content, check role-specific access
    switch ($user_role) {
        case 'student':
            return mcqhome_user_can('browse_content', $user_id);
            
        case 'teacher':
            return mcqhome_user_can('browse_content', $user_id);
            
        case 'institution':
            return mcqhome_user_can('view_all_institution_content', $user_id);
            
        default:
            return false;
    }
}

/**
 * Initialize user roles on theme activation
 */
function mcqhome_activate_user_roles() {
    mcqhome_init_user_roles();
    
    // Flush rewrite rules to ensure custom post type URLs work
    flush_rewrite_rules();
}

/**
 * Clean up user roles on theme deactivation
 */
function mcqhome_deactivate_user_roles() {
    // Note: We don't remove roles on deactivation to preserve user data
    // Roles can be manually removed if needed
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Initialize user roles
add_action('init', 'mcqhome_init_user_roles');

// Hook into theme activation/deactivation
add_action('after_switch_theme', 'mcqhome_activate_user_roles');
add_action('switch_theme', 'mcqhome_deactivate_user_roles');

/**
 * Prevent users from accessing admin area based on role
 */
function mcqhome_restrict_admin_access() {
    if (is_admin() && !wp_doing_ajax()) {
        $user_role = mcqhome_get_user_primary_role();
        
        // Only allow admin, institution, and teacher roles in admin area
        if (!in_array($user_role, ['administrator', 'institution', 'teacher'])) {
            wp_redirect(home_url('/dashboard/'));
            exit;
        }
    }
}
add_action('admin_init', 'mcqhome_restrict_admin_access');

/**
 * Customize admin bar based on user role
 */
function mcqhome_customize_admin_bar() {
    $user_role = mcqhome_get_user_primary_role();
    
    // Hide admin bar for students
    if ($user_role === 'student') {
        show_admin_bar(false);
    }
}
add_action('wp_loaded', 'mcqhome_customize_admin_bar');

/**
 * Add custom user meta fields for role-specific data
 */
function mcqhome_add_user_meta_fields($user) {
    $user_role = mcqhome_get_user_primary_role($user->ID);
    ?>
    <h3><?php _e('MCQHome Profile Information', 'mcqhome'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="institution_id"><?php _e('Institution', 'mcqhome'); ?></label></th>
            <td>
                <?php
                $institution_id = get_user_meta($user->ID, 'institution_id', true);
                $institutions = get_posts([
                    'post_type' => 'institution',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                ]);
                ?>
                <select name="institution_id" id="institution_id">
                    <option value=""><?php _e('Select Institution', 'mcqhome'); ?></option>
                    <?php foreach ($institutions as $institution): ?>
                        <option value="<?php echo $institution->ID; ?>" <?php selected($institution_id, $institution->ID); ?>>
                            <?php echo esc_html($institution->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Select the institution this user belongs to.', 'mcqhome'); ?></p>
            </td>
        </tr>
        
        <?php if (in_array($user_role, ['teacher', 'institution'])): ?>
        <tr>
            <th><label for="specialization"><?php _e('Specialization', 'mcqhome'); ?></label></th>
            <td>
                <input type="text" name="specialization" id="specialization" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'specialization', true)); ?>" 
                       class="regular-text" />
                <p class="description"><?php _e('Areas of expertise or specialization.', 'mcqhome'); ?></p>
            </td>
        </tr>
        <?php endif; ?>
        
        <tr>
            <th><label for="bio"><?php _e('Bio', 'mcqhome'); ?></label></th>
            <td>
                <textarea name="bio" id="bio" rows="5" cols="30"><?php echo esc_textarea(get_user_meta($user->ID, 'bio', true)); ?></textarea>
                <p class="description"><?php _e('Brief biography or description.', 'mcqhome'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'mcqhome_add_user_meta_fields');
add_action('edit_user_profile', 'mcqhome_add_user_meta_fields');

/**
 * Save custom user meta fields
 */
function mcqhome_save_user_meta_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    $meta_fields = ['institution_id', 'specialization', 'bio'];
    
    foreach ($meta_fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('personal_options_update', 'mcqhome_save_user_meta_fields');
add_action('edit_user_profile_update', 'mcqhome_save_user_meta_fields');