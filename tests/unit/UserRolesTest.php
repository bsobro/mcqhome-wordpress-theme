<?php
/**
 * Test user roles and capabilities
 */

class UserRolesTest extends MCQHome_TestCase {
    
    public function test_custom_roles_exist() {
        $expected_roles = ['student', 'teacher', 'institution'];
        
        foreach ($expected_roles as $role_name) {
            $role = get_role($role_name);
            $this->assertNotNull($role, "Role '{$role_name}' should exist");
        }
    }
    
    public function test_student_capabilities() {
        wp_set_current_user($this->test_student);
        
        // Test capabilities student should have
        $should_have = [
            'read',
            'take_mcq',
            'view_mcq_results',
            'track_progress',
            'follow_institutions',
            'follow_teachers',
            'enroll_in_sets',
            'view_dashboard',
            'browse_content'
        ];
        
        foreach ($should_have as $capability) {
            $this->assertUserCan($capability, $this->test_student);
        }
        
        // Test capabilities student should NOT have
        $should_not_have = [
            'create_mcq',
            'edit_mcq',
            'manage_teachers',
            'manage_institution',
            'delete_others_mcq',
            'manage_options'
        ];
        
        foreach ($should_not_have as $capability) {
            $this->assertUserCannot($capability, $this->test_student);
        }
    }
    
    public function test_teacher_capabilities() {
        wp_set_current_user($this->test_teacher);
        
        // Test capabilities teacher should have
        $should_have = [
            'read',
            'create_mcq',
            'edit_mcq',
            'edit_own_mcq',
            'delete_own_mcq',
            'publish_mcq',
            'create_mcq_set',
            'edit_mcq_set',
            'view_student_progress',
            'manage_enrollments',
            'view_teacher_dashboard'
        ];
        
        foreach ($should_have as $capability) {
            $this->assertUserCan($capability, $this->test_teacher);
        }
        
        // Test capabilities teacher should NOT have
        $should_not_have = [
            'manage_teachers',
            'manage_institution',
            'delete_others_mcq',
            'edit_others_mcq',
            'manage_options'
        ];
        
        foreach ($should_not_have as $capability) {
            $this->assertUserCannot($capability, $this->test_teacher);
        }
    }
    
    public function test_institution_capabilities() {
        wp_set_current_user($this->test_institution);
        
        // Test capabilities institution should have
        $should_have = [
            'read',
            'manage_institution',
            'manage_teachers',
            'add_teachers',
            'remove_teachers',
            'view_all_students',
            'manage_student_enrollments',
            'view_institution_analytics',
            'view_institution_dashboard',
            'create_mcq',
            'edit_mcq',
            'create_mcq_set'
        ];
        
        foreach ($should_have as $capability) {
            $this->assertUserCan($capability, $this->test_institution);
        }
        
        // Test capabilities institution should NOT have
        $should_not_have = [
            'manage_options',
            'delete_others_mcq' // Should only manage their own institution's content
        ];
        
        foreach ($should_not_have as $capability) {
            $this->assertUserCannot($capability, $this->test_institution);
        }
    }
    
    public function test_admin_capabilities() {
        wp_set_current_user($this->test_admin);
        
        // Test that admin has all custom capabilities
        $admin_capabilities = [
            'create_mcq',
            'edit_mcq',
            'edit_others_mcq',
            'delete_mcq',
            'delete_others_mcq',
            'manage_institutions',
            'manage_all_users',
            'view_system_analytics',
            'manage_mcq_settings'
        ];
        
        foreach ($admin_capabilities as $capability) {
            $this->assertUserCan($capability, $this->test_admin);
        }
    }
    
    public function test_user_role_display_names() {
        $expected_names = [
            'student' => 'Student',
            'teacher' => 'Teacher',
            'institution' => 'Institution',
            'administrator' => 'Administrator'
        ];
        
        foreach ($expected_names as $role => $expected_name) {
            $display_name = mcqhome_get_user_role_display_name($role);
            $this->assertEquals($expected_name, $display_name);
        }
    }
    
    public function test_get_user_primary_role() {
        // Test each role
        $test_cases = [
            $this->test_student => 'student',
            $this->test_teacher => 'teacher',
            $this->test_institution => 'institution',
            $this->test_admin => 'administrator'
        ];
        
        foreach ($test_cases as $user_id => $expected_role) {
            $primary_role = mcqhome_get_user_primary_role($user_id);
            $this->assertEquals($expected_role, $primary_role);
        }
    }
    
    public function test_user_belongs_to_institution() {
        // Associate teacher with institution
        update_user_meta($this->test_teacher, 'institution_id', $this->test_institution_post);
        
        // Test association
        $belongs = mcqhome_user_belongs_to_institution($this->test_teacher, $this->test_institution_post);
        $this->assertTrue($belongs);
        
        // Test non-association
        $not_belongs = mcqhome_user_belongs_to_institution($this->test_student, $this->test_institution_post);
        $this->assertFalse($not_belongs);
    }
    
    public function test_content_access_control() {
        // Create test MCQ
        $mcq_id = $this->createTestMCQ([
            'post_author' => $this->test_teacher,
            'post_status' => 'publish'
        ]);
        
        // Test admin can access everything
        $admin_access = mcqhome_can_user_access_content($mcq_id, $this->test_admin);
        $this->assertTrue($admin_access);
        
        // Test author can access their own content
        $author_access = mcqhome_can_user_access_content($mcq_id, $this->test_teacher);
        $this->assertTrue($author_access);
        
        // Test student can access published content
        $student_access = mcqhome_can_user_access_content($mcq_id, $this->test_student);
        $this->assertTrue($student_access);
        
        // Test unpublished content access
        wp_update_post([
            'ID' => $mcq_id,
            'post_status' => 'draft'
        ]);
        
        // Author should still have access
        $author_draft_access = mcqhome_can_user_access_content($mcq_id, $this->test_teacher);
        $this->assertTrue($author_draft_access);
        
        // Student should not have access to draft
        $student_draft_access = mcqhome_can_user_access_content($mcq_id, $this->test_student);
        $this->assertFalse($student_draft_access);
    }
    
    public function test_admin_area_access_restriction() {
        // This test would need to be run in an admin context
        // For now, we'll test the logic function if it exists
        
        if (function_exists('mcqhome_should_restrict_admin_access')) {
            // Student should be restricted
            $student_restricted = mcqhome_should_restrict_admin_access($this->test_student);
            $this->assertTrue($student_restricted);
            
            // Teacher should have access
            $teacher_restricted = mcqhome_should_restrict_admin_access($this->test_teacher);
            $this->assertFalse($teacher_restricted);
            
            // Institution should have access
            $institution_restricted = mcqhome_should_restrict_admin_access($this->test_institution);
            $this->assertFalse($institution_restricted);
            
            // Admin should have access
            $admin_restricted = mcqhome_should_restrict_admin_access($this->test_admin);
            $this->assertFalse($admin_restricted);
        }
    }
    
    public function test_users_by_role_query() {
        // Test getting users by role
        $students = mcqhome_get_users_by_role('student', ['number' => 10]);
        $this->assertIsArray($students);
        
        // Check that all returned users have the correct role
        foreach ($students as $user) {
            $user_roles = $user->roles;
            $this->assertContains('student', $user_roles);
        }
        
        // Test with pagination
        $teachers_page_1 = mcqhome_get_users_by_role('teacher', ['number' => 5, 'paged' => 1]);
        $this->assertIsArray($teachers_page_1);
        $this->assertLessThanOrEqual(5, count($teachers_page_1));
    }
    
    public function test_role_hierarchy() {
        // Test that roles have proper hierarchy in capabilities
        $student_role = get_role('student');
        $teacher_role = get_role('teacher');
        $institution_role = get_role('institution');
        
        // Teacher should have all student capabilities plus more
        $student_caps = array_keys($student_role->capabilities);
        $teacher_caps = array_keys($teacher_role->capabilities);
        
        foreach ($student_caps as $cap) {
            if ($cap !== 'level_0') { // Skip deprecated level capabilities
                $this->assertArrayHasKey($cap, $teacher_role->capabilities, 
                    "Teacher should have student capability: {$cap}");
            }
        }
        
        // Institution should have teacher capabilities plus more
        $institution_caps = array_keys($institution_role->capabilities);
        
        $teacher_specific_caps = ['create_mcq', 'edit_mcq', 'view_student_progress'];
        foreach ($teacher_specific_caps as $cap) {
            $this->assertArrayHasKey($cap, $institution_role->capabilities,
                "Institution should have teacher capability: {$cap}");
        }
    }
    
    public function test_capability_checking_function() {
        // Test the mcqhome_user_can function
        $this->assertTrue(mcqhome_user_can('take_mcq', $this->test_student));
        $this->assertTrue(mcqhome_user_can('create_mcq', $this->test_teacher));
        $this->assertTrue(mcqhome_user_can('manage_institution', $this->test_institution));
        
        $this->assertFalse(mcqhome_user_can('create_mcq', $this->test_student));
        $this->assertFalse(mcqhome_user_can('manage_institution', $this->test_teacher));
    }
}