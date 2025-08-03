<?php
/**
 * Base test case for MCQHome Theme tests
 */

use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class MCQHome_TestCase extends WP_UnitTestCase {
    
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        
        // Set up WordPress environment
        $this->setupWordPressEnvironment();
        
        // Create test data
        $this->createTestData();
    }
    
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
        
        // Clean up test data
        $this->cleanupTestData();
    }
    
    /**
     * Set up WordPress environment for testing
     */
    protected function setupWordPressEnvironment() {
        // Initialize custom post types
        if (function_exists('mcqhome_register_mcq_post_type')) {
            mcqhome_register_mcq_post_type();
        }
        
        if (function_exists('mcqhome_register_mcq_set_post_type')) {
            mcqhome_register_mcq_set_post_type();
        }
        
        if (function_exists('mcqhome_register_institution_post_type')) {
            mcqhome_register_institution_post_type();
        }
        
        // Initialize taxonomies
        if (function_exists('mcqhome_register_mcq_taxonomies')) {
            mcqhome_register_mcq_taxonomies();
        }
        
        // Initialize user roles
        if (function_exists('mcqhome_init_user_roles')) {
            mcqhome_init_user_roles();
        }
        
        // Initialize database tables
        if (function_exists('mcqhome_init_database')) {
            mcqhome_init_database();
        }
    }
    
    /**
     * Create test data
     */
    protected function createTestData() {
        // Create test users with different roles
        $this->test_admin = $this->factory->user->create([
            'role' => 'administrator',
            'user_login' => 'test_admin',
            'user_email' => 'admin@test.com'
        ]);
        
        $this->test_institution = $this->factory->user->create([
            'role' => 'institution',
            'user_login' => 'test_institution',
            'user_email' => 'institution@test.com'
        ]);
        
        $this->test_teacher = $this->factory->user->create([
            'role' => 'teacher',
            'user_login' => 'test_teacher',
            'user_email' => 'teacher@test.com'
        ]);
        
        $this->test_student = $this->factory->user->create([
            'role' => 'student',
            'user_login' => 'test_student',
            'user_email' => 'student@test.com'
        ]);
        
        // Create test institution post
        $this->test_institution_post = $this->factory->post->create([
            'post_type' => 'institution',
            'post_title' => 'Test Institution',
            'post_status' => 'publish',
            'post_author' => $this->test_institution
        ]);
        
        // Associate teacher with institution
        update_user_meta($this->test_teacher, 'institution_id', $this->test_institution_post);
        
        // Create test MCQs
        $this->test_mcq_1 = $this->createTestMCQ([
            'post_title' => 'Test MCQ 1',
            'post_author' => $this->test_teacher,
            'question_text' => 'What is 2 + 2?',
            'option_a' => '3',
            'option_b' => '4',
            'option_c' => '5',
            'option_d' => '6',
            'correct_answer' => 'B',
            'explanation' => '2 + 2 equals 4'
        ]);
        
        $this->test_mcq_2 = $this->createTestMCQ([
            'post_title' => 'Test MCQ 2',
            'post_author' => $this->test_teacher,
            'question_text' => 'What is the capital of France?',
            'option_a' => 'London',
            'option_b' => 'Berlin',
            'option_c' => 'Paris',
            'option_d' => 'Madrid',
            'correct_answer' => 'C',
            'explanation' => 'Paris is the capital of France'
        ]);
        
        // Create test MCQ set
        $this->test_mcq_set = $this->createTestMCQSet([
            'post_title' => 'Test MCQ Set',
            'post_author' => $this->test_teacher,
            'mcq_ids' => [$this->test_mcq_1, $this->test_mcq_2],
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0.25
        ]);
    }
    
    /**
     * Clean up test data
     */
    protected function cleanupTestData() {
        // Clean up is handled by WordPress test framework
    }
    
    /**
     * Create a test MCQ
     */
    protected function createTestMCQ($args = []) {
        $defaults = [
            'post_type' => 'mcq',
            'post_status' => 'publish',
            'question_text' => 'Test question?',
            'option_a' => 'Option A',
            'option_b' => 'Option B',
            'option_c' => 'Option C',
            'option_d' => 'Option D',
            'correct_answer' => 'A',
            'explanation' => 'Test explanation'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $mcq_id = $this->factory->post->create([
            'post_type' => $args['post_type'],
            'post_title' => $args['post_title'] ?? 'Test MCQ',
            'post_status' => $args['post_status'],
            'post_author' => $args['post_author'] ?? $this->test_teacher
        ]);
        
        // Add meta data
        update_post_meta($mcq_id, '_mcq_question_text', $args['question_text']);
        update_post_meta($mcq_id, '_mcq_option_a', $args['option_a']);
        update_post_meta($mcq_id, '_mcq_option_b', $args['option_b']);
        update_post_meta($mcq_id, '_mcq_option_c', $args['option_c']);
        update_post_meta($mcq_id, '_mcq_option_d', $args['option_d']);
        update_post_meta($mcq_id, '_mcq_correct_answer', $args['correct_answer']);
        update_post_meta($mcq_id, '_mcq_explanation', $args['explanation']);
        
        return $mcq_id;
    }
    
    /**
     * Create a test MCQ set
     */
    protected function createTestMCQSet($args = []) {
        $defaults = [
            'post_type' => 'mcq_set',
            'post_status' => 'publish',
            'mcq_ids' => [],
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0,
            'display_format' => 'next_next'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $set_id = $this->factory->post->create([
            'post_type' => $args['post_type'],
            'post_title' => $args['post_title'] ?? 'Test MCQ Set',
            'post_status' => $args['post_status'],
            'post_author' => $args['post_author'] ?? $this->test_teacher
        ]);
        
        // Add meta data
        update_post_meta($set_id, '_mcq_set_questions', $args['mcq_ids']);
        update_post_meta($set_id, '_mcq_set_total_marks', $args['total_marks']);
        update_post_meta($set_id, '_mcq_set_passing_marks', $args['passing_marks']);
        update_post_meta($set_id, '_mcq_set_negative_marking', $args['negative_marking']);
        update_post_meta($set_id, '_mcq_set_display_format', $args['display_format']);
        
        return $set_id;
    }
    
    /**
     * Assert that a user has a specific capability
     */
    protected function assertUserCan($capability, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $this->assertTrue(user_can($user_id, $capability), 
            "User {$user_id} should have capability '{$capability}'");
    }
    
    /**
     * Assert that a user does not have a specific capability
     */
    protected function assertUserCannot($capability, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $this->assertFalse(user_can($user_id, $capability), 
            "User {$user_id} should not have capability '{$capability}'");
    }
    
    /**
     * Assert that a post meta value equals expected value
     */
    protected function assertPostMetaEquals($expected, $post_id, $meta_key) {
        $actual = get_post_meta($post_id, $meta_key, true);
        $this->assertEquals($expected, $actual, 
            "Post meta '{$meta_key}' for post {$post_id} should equal '{$expected}'");
    }
    
    /**
     * Assert that a user meta value equals expected value
     */
    protected function assertUserMetaEquals($expected, $user_id, $meta_key) {
        $actual = get_user_meta($user_id, $meta_key, true);
        $this->assertEquals($expected, $actual, 
            "User meta '{$meta_key}' for user {$user_id} should equal '{$expected}'");
    }
}