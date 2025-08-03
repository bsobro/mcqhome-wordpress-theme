<?php
/**
 * User Test Helper Class
 */

class UserTestHelper {
    
    /**
     * Create test users for different roles
     */
    public static function createTestUsers() {
        $users = [];
        
        // Create admin user
        $users['admin'] = wp_insert_user([
            'user_login' => 'test_admin_' . uniqid(),
            'user_email' => 'admin_' . uniqid() . '@test.com',
            'user_pass' => 'test_password',
            'role' => 'administrator',
            'display_name' => 'Test Administrator'
        ]);
        
        // Create institution user
        $users['institution'] = wp_insert_user([
            'user_login' => 'test_institution_' . uniqid(),
            'user_email' => 'institution_' . uniqid() . '@test.com',
            'user_pass' => 'test_password',
            'role' => 'institution',
            'display_name' => 'Test Institution'
        ]);
        
        // Create teacher user
        $users['teacher'] = wp_insert_user([
            'user_login' => 'test_teacher_' . uniqid(),
            'user_email' => 'teacher_' . uniqid() . '@test.com',
            'user_pass' => 'test_password',
            'role' => 'teacher',
            'display_name' => 'Test Teacher'
        ]);
        
        // Create student user
        $users['student'] = wp_insert_user([
            'user_login' => 'test_student_' . uniqid(),
            'user_email' => 'student_' . uniqid() . '@test.com',
            'user_pass' => 'test_password',
            'role' => 'student',
            'display_name' => 'Test Student'
        ]);
        
        return $users;
    }
    
    /**
     * Test user role capabilities
     */
    public static function testUserCapabilities($user_id, $expected_capabilities) {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return ['error' => 'User not found'];
        }
        
        $results = [];
        
        foreach ($expected_capabilities as $capability => $should_have) {
            $has_capability = user_can($user_id, $capability);
            $results[$capability] = [
                'expected' => $should_have,
                'actual' => $has_capability,
                'passed' => ($has_capability === $should_have)
            ];
        }
        
        return $results;
    }
    
    /**
     * Get expected capabilities for each role
     */
    public static function getExpectedCapabilities($role) {
        $capabilities = [];
        
        switch ($role) {
            case 'student':
                $capabilities = [
                    'read' => true,
                    'take_mcq' => true,
                    'view_mcq_results' => true,
                    'track_progress' => true,
                    'follow_institutions' => true,
                    'follow_teachers' => true,
                    'enroll_in_sets' => true,
                    'view_dashboard' => true,
                    'browse_content' => true,
                    'create_mcq' => false,
                    'edit_mcq' => false,
                    'manage_teachers' => false,
                    'manage_institution' => false
                ];
                break;
                
            case 'teacher':
                $capabilities = [
                    'read' => true,
                    'create_mcq' => true,
                    'edit_mcq' => true,
                    'edit_own_mcq' => true,
                    'delete_own_mcq' => true,
                    'publish_mcq' => true,
                    'create_mcq_set' => true,
                    'edit_mcq_set' => true,
                    'view_student_progress' => true,
                    'manage_enrollments' => true,
                    'view_teacher_dashboard' => true,
                    'manage_teachers' => false,
                    'manage_institution' => false,
                    'delete_others_mcq' => false
                ];
                break;
                
            case 'institution':
                $capabilities = [
                    'read' => true,
                    'manage_institution' => true,
                    'manage_teachers' => true,
                    'add_teachers' => true,
                    'remove_teachers' => true,
                    'view_all_students' => true,
                    'manage_student_enrollments' => true,
                    'view_institution_analytics' => true,
                    'view_institution_dashboard' => true,
                    'create_mcq' => true,
                    'edit_mcq' => true,
                    'create_mcq_set' => true,
                    'edit_mcq_set' => true
                ];
                break;
                
            case 'administrator':
                $capabilities = [
                    'read' => true,
                    'manage_options' => true,
                    'create_mcq' => true,
                    'edit_mcq' => true,
                    'edit_others_mcq' => true,
                    'delete_mcq' => true,
                    'delete_others_mcq' => true,
                    'manage_institutions' => true,
                    'manage_all_users' => true,
                    'view_system_analytics' => true,
                    'manage_mcq_settings' => true
                ];
                break;
        }
        
        return $capabilities;
    }
    
    /**
     * Test user registration process
     */
    public static function testUserRegistration($role, $additional_data = []) {
        $unique_id = uniqid();
        
        $user_data = wp_parse_args($additional_data, [
            'user_login' => "test_{$role}_{$unique_id}",
            'user_email' => "{$role}_{$unique_id}@test.com",
            'user_pass' => 'test_password_123',
            'role' => $role,
            'display_name' => "Test {$role}",
            'first_name' => 'Test',
            'last_name' => ucfirst($role)
        ]);
        
        // Simulate registration process
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            return [
                'success' => false,
                'error' => $user_id->get_error_message(),
                'user_id' => null
            ];
        }
        
        // Add role-specific meta data
        switch ($role) {
            case 'teacher':
                if (isset($additional_data['institution_id'])) {
                    update_user_meta($user_id, 'institution_id', $additional_data['institution_id']);
                }
                if (isset($additional_data['specialization'])) {
                    update_user_meta($user_id, 'specialization', $additional_data['specialization']);
                }
                break;
                
            case 'student':
                if (isset($additional_data['grade_level'])) {
                    update_user_meta($user_id, 'grade_level', $additional_data['grade_level']);
                }
                break;
                
            case 'institution':
                if (isset($additional_data['institution_type'])) {
                    update_user_meta($user_id, 'institution_type', $additional_data['institution_type']);
                }
                break;
        }
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'user_data' => get_userdata($user_id)
        ];
    }
    
    /**
     * Test user login process
     */
    public static function testUserLogin($username, $password) {
        $user = wp_authenticate($username, $password);
        
        if (is_wp_error($user)) {
            return [
                'success' => false,
                'error' => $user->get_error_message()
            ];
        }
        
        return [
            'success' => true,
            'user_id' => $user->ID,
            'user_login' => $user->user_login,
            'user_role' => $user->roles[0] ?? 'none'
        ];
    }
    
    /**
     * Test user profile update
     */
    public static function testUserProfileUpdate($user_id, $update_data) {
        $original_user = get_userdata($user_id);
        if (!$original_user) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }
        
        // Update user data
        $update_data['ID'] = $user_id;
        $result = wp_update_user($update_data);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'error' => $result->get_error_message()
            ];
        }
        
        // Update meta data if provided
        if (isset($update_data['meta'])) {
            foreach ($update_data['meta'] as $key => $value) {
                update_user_meta($user_id, $key, $value);
            }
        }
        
        $updated_user = get_userdata($user_id);
        
        return [
            'success' => true,
            'original_user' => $original_user,
            'updated_user' => $updated_user,
            'changes' => array_diff_assoc($updated_user->to_array(), $original_user->to_array())
        ];
    }
    
    /**
     * Test user role switching
     */
    public static function testUserRoleSwitch($user_id, $new_role) {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }
        
        $old_roles = $user->roles;
        
        // Remove all existing roles and add new role
        foreach ($old_roles as $role) {
            $user->remove_role($role);
        }
        $user->add_role($new_role);
        
        // Refresh user data
        $updated_user = get_user_by('ID', $user_id);
        
        return [
            'success' => true,
            'old_roles' => $old_roles,
            'new_roles' => $updated_user->roles,
            'role_changed' => in_array($new_role, $updated_user->roles)
        ];
    }
    
    /**
     * Clean up test users
     */
    public static function cleanupTestUsers($user_ids) {
        $results = [];
        
        foreach ($user_ids as $user_id) {
            if (is_numeric($user_id)) {
                $result = wp_delete_user($user_id);
                $results[$user_id] = $result;
            }
        }
        
        return $results;
    }
}