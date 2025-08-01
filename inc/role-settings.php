<?php
/**
 * Role Management Settings Page
 * Provides a safe way to manage custom roles without theme activation conflicts
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add settings page to admin menu
 */
function mcqhome_add_role_settings_page() {
    add_submenu_page(
        'tools.php',
        __('MCQ Roles Management', 'mcqhome'),
        __('MCQ Roles', 'mcqhome'),
        'manage_options',
        'mcqhome-roles',
        'mcqhome_render_role_settings_page'
    );
}
add_action('admin_menu', 'mcqhome_add_role_settings_page');

/**
 * Render the role settings page
 */
function mcqhome_render_role_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Handle form submissions
    if (isset($_POST['mcqhome_create_roles'])) {
        check_admin_referer('mcqhome_role_management');
        
        mcqhome_create_all_roles();
        echo '<div class="updated notice"><p>' . __('Roles created successfully!', 'mcqhome') . '</p></div>';
    }
    
    if (isset($_POST['mcqhome_update_roles'])) {
        check_admin_referer('mcqhome_role_management');
        
        mcqhome_update_existing_roles();
        echo '<div class="updated notice"><p>' . __('Roles updated successfully!', 'mcqhome') . '</p></div>';
    }
    
    if (isset($_POST['mcqhome_remove_roles'])) {
        check_admin_referer('mcqhome_role_management');
        
        mcqhome_remove_custom_roles();
        echo '<div class="updated notice"><p>' . __('Custom roles removed successfully!', 'mcqhome') . '</p></div>';
    }
    
    // Display current roles status
    $roles = wp_roles()->roles;
    $custom_roles = ['student', 'teacher', 'institution'];
    ?>
    
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="mcqhome-roles-info">
            <h2><?php _e('Current Role Status', 'mcqhome'); ?></h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Role', 'mcqhome'); ?></th>
                        <th><?php _e('Display Name', 'mcqhome'); ?></th>
                        <th><?php _e('Status', 'mcqhome'); ?></th>
                        <th><?php _e('Users', 'mcqhome'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($custom_roles as $role_key): ?>
                        <tr>
                            <td><code><?php echo esc_html($role_key); ?></code></td>
                            <td>
                                <?php 
                                $role = get_role($role_key);
                                echo $role ? esc_html($role->name) : '—';
                                ?>
                            </td>
                            <td>
                                <?php if ($role): ?>
                                    <span style="color: green;">✓ <?php _e('Active', 'mcqhome'); ?></span>
                                <?php else: ?>
                                    <span style="color: red;">✗ <?php _e('Not Found', 'mcqhome'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $user_count = $role ? count(get_users(['role' => $role_key])) : 0;
                                echo esc_html($user_count);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mcqhome-roles-actions" style="margin-top: 20px;">
            <form method="post">
                <?php wp_nonce_field('mcqhome_role_management'); ?>
                
                <h2><?php _e('Role Management Actions', 'mcqhome'); ?></h2>
                
                <div class="mcqhome-action-section" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9;">
                    <h3><?php _e('Create Roles', 'mcqhome'); ?></h3>
                    <p><?php _e('Create custom roles if they don\'t already exist. This is safe and won\'t affect existing users.', 'mcqhome'); ?></p>
                    <button type="submit" name="mcqhome_create_roles" class="button button-primary">
                        <?php _e('Create All Custom Roles', 'mcqhome'); ?>
                    </button>
                </div>
                
                <div class="mcqhome-action-section" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9;">
                    <h3><?php _e('Update Roles', 'mcqhome'); ?></h3>
                    <p><?php _e('Update existing custom roles with the latest capabilities. Use with caution if users already have these roles.', 'mcqhome'); ?></p>
                    <button type="submit" name="mcqhome_update_roles" class="button button-secondary" 
                            onclick="return confirm('<?php _e('This will update existing roles. Continue?', 'mcqhome'); ?>')">
                        <?php _e('Update All Custom Roles', 'mcqhome'); ?>
                    </button>
                </div>
                
                <div class="mcqhome-action-section" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9;">
                    <h3><?php _e('Remove Roles', 'mcqhome'); ?></h3>
                    <p><?php _e('Remove custom roles. Users with these roles will be assigned the default Subscriber role.', 'mcqhome'); ?></p>
                    <button type="submit" name="mcqhome_remove_roles" class="button button-secondary" 
                            onclick="return confirm('<?php _e('This will remove custom roles and affect users. Continue?', 'mcqhome'); ?>')">
                        <?php _e('Remove All Custom Roles', 'mcqhome'); ?>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mcqhome-roles-help" style="margin-top: 30px;">
            <h2><?php _e('Help & Information', 'mcqhome'); ?></h2>
            <ul>
                <li><strong><?php _e('Student Role:', 'mcqhome'); ?></strong> <?php _e('Can take MCQs, track progress, and follow teachers/institutions.', 'mcqhome'); ?></li>
                <li><strong><?php _e('Teacher Role:', 'mcqhome'); ?></strong> <?php _e('Can create and manage MCQs, view student progress, and manage enrollments.', 'mcqhome'); ?></li>
                <li><strong><?php _e('Institution Role:', 'mcqhome'); ?></strong> <?php _e('Can manage teachers, view analytics, and oversee all institutional content.', 'mcqhome'); ?></li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Create all custom roles safely
 */
function mcqhome_create_all_roles() {
    mcqhome_create_student_role();
    mcqhome_create_teacher_role();
    mcqhome_create_institution_role();
}

/**
 * Update existing custom roles
 */
function mcqhome_update_existing_roles() {
    // Remove and recreate roles to update capabilities
    $roles_to_update = ['student', 'teacher', 'institution'];
    
    foreach ($roles_to_update as $role_key) {
        if (get_role($role_key)) {
            remove_role($role_key);
        }
    }
    
    mcqhome_create_all_roles();
}

/**
 * Remove all custom roles
 */
function mcqhome_remove_custom_roles() {
    $roles_to_remove = ['student', 'teacher', 'institution'];
    
    // Reassign users to subscriber role before removing
    foreach ($roles_to_remove as $role_key) {
        $users = get_users(['role' => $role_key]);
        foreach ($users as $user) {
            $user->set_role('subscriber');
        }
        
        if (get_role($role_key)) {
            remove_role($role_key);
        }
    }
}

/**
 * Add settings link to plugin/theme
 */
function mcqhome_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('tools.php?page=mcqhome-roles') . '">' . __('Roles', 'mcqhome') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}