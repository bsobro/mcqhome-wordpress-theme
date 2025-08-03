<?php
/**
 * Integration test for user registration flow
 */

class UserRegistrationFlowTest extends MCQHome_TestCase {
    
    public function test_complete_student_registration_flow() {
        // Test student registration
        $registration_data = [
            'user_login' => 'test_student_' . uniqid(),
            'user_email' => 'student_' . uniqid() . '@test.com',
            'user_pass' => 'test_password_123',
            'role' => 'student',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'display_name' => 'Test Student'
        ];
        
        // Simulate registration process
        $result = UserTestHelper::testUserRegistration('student', $registration_data);
        
        $this->assertTrue($result['success'], 'Student registration should succeed');
        $this->assertIsNumeric($result['user_id']);
        
        $user_id = $result['user_id'];
        
        // Verify user was created with correct role
        $user = get_userdata($user_id);
        $this->assertContains('student', $user->roles);
        
        // Test login
        $login_result = UserTestHelper::testUserLogin($registration_data['user_login'], $registration_data['user_pass']);
        $this->assertTrue($login_result['success'], 'Student should be able to login');
        $this->assertEquals('student', $login_result['user_role']);
        
        // Test capabilities
        $expected_capabilities = UserTestHelper::getExpectedCapabilities('student');
        $capability_results = UserTestHelper::testUserCapabilities($user_id, $expected_capabilities);
        
        foreach ($capability_results as $capability => $result) {
            $this->assertTrue($result['passed'], 
                "Student capability test failed for '{$capability}': expected {$result['expected']}, got {$result['actual']}");
        }
        
        // Cleanup
        wp_delete_user($user_id);
    }
    
    public function test_complete_teacher_registration_flow() {
        // Create institution first
        $institution_id = $this->factory->post->create([
            'post_type' => 'institution',
            'post_title' => 'Test Institution for Teacher',
            'post_status' => 'publish'
        ]);
        
        // Test teacher registration with institution
        $registration_data = [
            'user_login' => 'test_teacher_' . uniqid(),
            'user_email' => 'teacher_' . uniqid() . '@test.com',
            'user_pass' => 'test_password_123',
            'role' => 'teacher',
            'first_name' => 'Test',
            'last_name' => 'Teacher',
            'display_name' => 'Test Teacher',
            'institution_id' => $institution_id,
            'specialization' => 'Mathematics'
        ];
        
        $result = UserTestHelper::testUserRegistration('teacher', $registration_data);
        
        $this->assertTrue($result['success'], 'Teacher registration should succeed');
        $user_id = $result['user_id'];
        
        // Verify user was created with correct role and meta
        $user = get_userdata($user_id);
        $this->assertContains('teacher', $user->roles);
        
        // Verify institution association
        $user_institution = get_user_meta($user_id, 'institution_id', true);
        $this->assertEquals($institution_id, $user_institution);
        
        // Verify specialization
        $specialization = get_user_meta($user_id, 'specialization', true);
        $this->assertEquals('Mathematics', $specialization);
        
        // Test capabilities
        $expected_capabilities = UserTestHelper::getExpectedCapabilities('teacher');
        $capability_results = UserTestHelper::testUserCapabilities($user_id, $expected_capabilities);
        
        foreach ($capability_results as $capability => $result) {
            $this->assertTrue($result['passed'], 
                "Teacher capability test failed for '{$capability}'");
        }
        
        // Test content creation capability
        wp_set_current_user($user_id);
        
        // Teacher should be able to create MCQ
        $mcq_id = $this->createTestMCQ([
            'post_author' => $user_id,
            'post_title' => 'Teacher Created MCQ'
        ]);
        
        $this->assertIsNumeric($mcq_id);
        $this->assertGreaterThan(0, $mcq_id);
        
        // Verify MCQ was created
        $mcq = get_post($mcq_id);
        $this->assertEquals('mcq', $mcq->post_type);
        $this->assertEquals($user_id, $mcq->post_author);
        
        // Cleanup
        wp_delete_post($mcq_id, true);
        wp_delete_post($institution_id, true);
        wp_delete_user($user_id);
    }
    
    public function test_complete_institution_registration_flow() {
        $registration_data = [
            'user_login' => 'test_institution_' . uniqid(),
            'user_email' => 'institution_' . uniqid() . '@test.com',
            'user_pass' => 'test_password_123',
            'role' => 'institution',
            'first_name' => 'Test',
            'last_name' => 'Institution',
            'display_name' => 'Test Institution',
            'institution_type' => 'University'
        ];
        
        $result = UserTestHelper::testUserRegistration('institution', $registration_data);
        
        $this->assertTrue($result['success'], 'Institution registration should succeed');
        $user_id = $result['user_id'];
        
        // Verify user was created with correct role
        $user = get_userdata($user_id);
        $this->assertContains('institution', $user->roles);
        
        // Test capabilities
        $expected_capabilities = UserTestHelper::getExpectedCapabilities('institution');
        $capability_results = UserTestHelper::testUserCapabilities($user_id, $expected_capabilities);
        
        foreach ($capability_results as $capability => $result) {
            $this->assertTrue($result['passed'], 
                "Institution capability test failed for '{$capability}'");
        }
        
        // Test institution management capabilities
        wp_set_current_user($user_id);
        
        // Institution should be able to create institution post
        $institution_post_id = $this->factory->post->create([
            'post_type' => 'institution',
            'post_title' => 'Institution Profile',
            'post_author' => $user_id,
            'post_status' => 'publish'
        ]);
        
        $this->assertIsNumeric($institution_post_id);
        
        // Institution should be able to manage teachers
        $teacher_id = $this->factory->user->create(['role' => 'teacher']);
        
        // Associate teacher with institution
        update_user_meta($teacher_id, 'institution_id', $institution_post_id);
        
        // Verify association
        $teacher_institution = get_user_meta($teacher_id, 'institution_id', true);
        $this->assertEquals($institution_post_id, $teacher_institution);
        
        // Cleanup
        wp_delete_post($institution_post_id, true);
        wp_delete_user($teacher_id);
        wp_delete_user($user_id);
    }
    
    public function test_registration_validation() {
        // Test invalid email
        $invalid_email_data = [
            'user_login' => 'test_user',
            'user_email' => 'invalid-email',
            'user_pass' => 'password',
            'role' => 'student'
        ];
        
        $result = UserTestHelper::testUserRegistration('student', $invalid_email_data);
        $this->assertFalse($result['success'], 'Registration with invalid email should fail');
        
        // Test duplicate username
        $first_user = UserTestHelper::testUserRegistration('student', [
            'user_login' => 'duplicate_test',
            'user_email' => 'first@test.com',
            'user_pass' => 'password',
            'role' => 'student'
        ]);
        
        $this->assertTrue($first_user['success']);
        
        $duplicate_user = UserTestHelper::testUserRegistration('student', [
            'user_login' => 'duplicate_test', // Same username
            'user_email' => 'second@test.com',
            'user_pass' => 'password',
            'role' => 'student'
        ]);
        
        $this->assertFalse($duplicate_user['success'], 'Registration with duplicate username should fail');
        
        // Cleanup
        wp_delete_user($first_user['user_id']);
    }
    
    public function test_role_switching_flow() {
        // Create a student user
        $student_result = UserTestHelper::testUserRegistration('student');
        $this->assertTrue($student_result['success']);
        $user_id = $student_result['user_id'];
        
        // Test switching to teacher role
        $switch_result = UserTestHelper::testUserRoleSwitch($user_id, 'teacher');
        $this->assertTrue($switch_result['success']);
        $this->assertTrue($switch_result['role_changed']);
        $this->assertContains('teacher', $switch_result['new_roles']);
        $this->assertNotContains('student', $switch_result['new_roles']);
        
        // Verify capabilities changed
        $teacher_capabilities = UserTestHelper::getExpectedCapabilities('teacher');
        $capability_results = UserTestHelper::testUserCapabilities($user_id, $teacher_capabilities);
        
        // Check a few key capabilities
        $this->assertTrue($capability_results['create_mcq']['passed'], 'Should have create_mcq capability as teacher');
        $this->assertTrue($capability_results['view_teacher_dashboard']['passed'], 'Should have teacher dashboard access');
        
        // Cleanup
        wp_delete_user($user_id);
    }
    
    public function test_profile_update_flow() {
        // Create a teacher user
        $teacher_result = UserTestHelper::testUserRegistration('teacher', [
            'first_name' => 'Original',
            'last_name' => 'Name',
            'specialization' => 'Math'
        ]);
        
        $this->assertTrue($teacher_result['success']);
        $user_id = $teacher_result['user_id'];
        
        // Test profile update
        $update_data = [
            'first_name' => 'Updated',
            'last_name' => 'Teacher',
            'display_name' => 'Updated Teacher',
            'meta' => [
                'specialization' => 'Physics',
                'bio' => 'Updated bio information'
            ]
        ];
        
        $update_result = UserTestHelper::testUserProfileUpdate($user_id, $update_data);
        $this->assertTrue($update_result['success']);
        
        // Verify updates
        $updated_user = get_userdata($user_id);
        $this->assertEquals('Updated', $updated_user->first_name);
        $this->assertEquals('Teacher', $updated_user->last_name);
        $this->assertEquals('Updated Teacher', $updated_user->display_name);
        
        // Verify meta updates
        $specialization = get_user_meta($user_id, 'specialization', true);
        $this->assertEquals('Physics', $specialization);
        
        $bio = get_user_meta($user_id, 'bio', true);
        $this->assertEquals('Updated bio information', $bio);
        
        // Cleanup
        wp_delete_user($user_id);
    }
    
    public function test_default_institution_assignment() {
        // Test that teachers without institution get assigned to default
        $teacher_data = [
            'user_login' => 'independent_teacher_' . uniqid(),
            'user_email' => 'independent_' . uniqid() . '@test.com',
            'user_pass' => 'password',
            'role' => 'teacher'
            // No institution_id specified
        ];
        
        $result = UserTestHelper::testUserRegistration('teacher', $teacher_data);
        $this->assertTrue($result['success']);
        $user_id = $result['user_id'];
        
        // Check if default institution assignment logic exists and works
        if (function_exists('mcqhome_assign_default_institution')) {
            mcqhome_assign_default_institution($user_id);
            
            $institution_id = get_user_meta($user_id, 'institution_id', true);
            $this->assertNotEmpty($institution_id, 'Teacher should be assigned to default institution');
            
            // Verify it's the MCQ Academy default institution
            $institution = get_post($institution_id);
            $this->assertEquals('MCQ Academy', $institution->post_title);
        }
        
        // Cleanup
        wp_delete_user($user_id);
    }
    
    public function test_bulk_user_operations() {
        // Create multiple users
        $user_ids = [];
        
        for ($i = 0; $i < 5; $i++) {
            $result = UserTestHelper::testUserRegistration('student', [
                'user_login' => 'bulk_student_' . $i . '_' . uniqid(),
                'user_email' => 'bulk_' . $i . '_' . uniqid() . '@test.com'
            ]);
            
            $this->assertTrue($result['success']);
            $user_ids[] = $result['user_id'];
        }
        
        // Test bulk cleanup
        $cleanup_results = UserTestHelper::cleanupTestUsers($user_ids);
        
        foreach ($cleanup_results as $user_id => $result) {
            $this->assertTrue($result, "Failed to delete user {$user_id}");
            
            // Verify user was deleted
            $user = get_userdata($user_id);
            $this->assertFalse($user, "User {$user_id} should be deleted");
        }
    }
}