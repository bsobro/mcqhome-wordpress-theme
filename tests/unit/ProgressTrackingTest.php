<?php
/**
 * Test progress tracking functionality
 */

class ProgressTrackingTest extends MCQHome_TestCase {
    
    public function test_user_progress_creation() {
        // Test creating new progress record
        $progress_data = [
            'current_question' => 0,
            'total_questions' => 5,
            'completed_questions' => [],
            'answers_data' => [],
            'progress_percentage' => 0
        ];
        
        $progress_id = mcqhome_update_user_progress($this->test_student, $this->test_mcq_set, $progress_data);
        
        $this->assertIsNumeric($progress_id);
        $this->assertGreaterThan(0, $progress_id);
        
        // Verify progress was saved
        $saved_progress = mcqhome_get_user_progress($this->test_student, $this->test_mcq_set);
        $this->assertNotNull($saved_progress);
        $this->assertEquals(0, $saved_progress->current_question);
        $this->assertEquals(5, $saved_progress->total_questions);
    }
    
    public function test_user_progress_update() {
        // Create initial progress
        $initial_data = [
            'current_question' => 0,
            'total_questions' => 5,
            'answers_data' => [],
            'progress_percentage' => 0
        ];
        
        mcqhome_update_user_progress($this->test_student, $this->test_mcq_set, $initial_data);
        
        // Update progress
        $updated_data = [
            'current_question' => 2,
            'total_questions' => 5,
            'answers_data' => ['A', 'B'],
            'progress_percentage' => 40
        ];
        
        $result = mcqhome_update_user_progress($this->test_student, $this->test_mcq_set, $updated_data);
        $this->assertTrue($result !== false);
        
        // Verify update
        $progress = mcqhome_get_user_progress($this->test_student, $this->test_mcq_set);
        $this->assertEquals(2, $progress->current_question);
        $this->assertEquals(40, $progress->progress_percentage);
        
        $answers = maybe_unserialize($progress->answers_data);
        $this->assertEquals(['A', 'B'], $answers);
    }
    
    public function test_progress_percentage_calculation() {
        $test_cases = [
            ['answered' => 0, 'total' => 5, 'expected' => 0],
            ['answered' => 1, 'total' => 5, 'expected' => 20],
            ['answered' => 3, 'total' => 5, 'expected' => 60],
            ['answered' => 5, 'total' => 5, 'expected' => 100],
            ['answered' => 2, 'total' => 3, 'expected' => 66.67]
        ];
        
        foreach ($test_cases as $case) {
            $percentage = ($case['answered'] / $case['total']) * 100;
            $rounded_percentage = round($percentage, 2);
            
            $this->assertEquals($case['expected'], $rounded_percentage, 
                "Failed for {$case['answered']}/{$case['total']} questions", 0.01);
        }
    }
    
    public function test_assessment_navigation_data() {
        // Create progress with some answers
        $progress_data = [
            'current_question' => 2,
            'total_questions' => 5,
            'answers_data' => ['A', 'B', 'C'],
            'progress_percentage' => 60
        ];
        
        mcqhome_update_user_progress($this->test_student, $this->test_mcq_set, $progress_data);
        
        // Get navigation data
        $nav_data = mcqhome_get_assessment_navigation($this->test_student, $this->test_mcq_set);
        
        $this->assertIsArray($nav_data);
        $this->assertEquals(5, $nav_data['total_questions']);
        $this->assertEquals(2, $nav_data['current_question']);
        $this->assertEquals(3, $nav_data['answered_count']);
        $this->assertEquals(60, $nav_data['progress_percentage']);
        $this->assertEquals(['A', 'B', 'C'], $nav_data['answers']);
    }
    
    public function test_user_has_ongoing_assessment() {
        // Initially no ongoing assessment
        $has_ongoing = mcqhome_has_ongoing_assessment($this->test_student, $this->test_mcq_set);
        $this->assertFalse($has_ongoing);
        
        // Create progress
        $progress_data = [
            'current_question' => 1,
            'total_questions' => 5,
            'answers_data' => ['A'],
            'progress_percentage' => 20
        ];
        
        mcqhome_update_user_progress($this->test_student, $this->test_mcq_set, $progress_data);
        
        // Now should have ongoing assessment
        $has_ongoing = mcqhome_has_ongoing_assessment($this->test_student, $this->test_mcq_set);
        $this->assertTrue($has_ongoing);
    }
    
    public function test_assessment_session_management() {
        // Start new session
        $session = mcqhome_start_assessment_session($this->test_student, $this->test_mcq_set);
        
        $this->assertIsArray($session);
        $this->assertArrayHasKey('session_id', $session);
        $this->assertArrayHasKey('current_question', $session);
        $this->assertArrayHasKey('answers', $session);
        $this->assertEquals(0, $session['current_question']);
        $this->assertEquals([], $session['answers']);
        
        // Try to start another session (should resume existing)
        $resumed_session = mcqhome_start_assessment_session($this->test_student, $this->test_mcq_set);
        $this->assertEquals($session['session_id'], $resumed_session['session_id']);
    }
    
    public function test_session_validation() {
        // Create a session
        mcqhome_start_assessment_session($this->test_student, $this->test_mcq_set);
        
        // Validate session
        $validation = mcqhome_validate_assessment_session($this->test_student, $this->test_mcq_set);
        $this->assertTrue($validation === true || !is_wp_error($validation));
        
        // Test validation for non-existent session
        $no_session_validation = mcqhome_validate_assessment_session($this->test_student, 99999);
        $this->assertTrue(is_wp_error($no_session_validation));
        $this->assertEquals('no_session', $no_session_validation->get_error_code());
    }
    
    public function test_time_remaining_calculation() {
        // Create MCQ set with time limit
        $set_with_time_limit = $this->createTestMCQSet([
            'time_limit' => 30 // 30 minutes
        ]);
        
        update_post_meta($set_with_time_limit, '_mcq_set_time_limit', 30);
        
        // Start session
        mcqhome_start_assessment_session($this->test_student, $set_with_time_limit);
        
        // Calculate time remaining
        $time_remaining = mcqhome_calculate_time_remaining($this->test_student, $set_with_time_limit);
        
        $this->assertIsNumeric($time_remaining);
        $this->assertGreaterThan(0, $time_remaining);
        $this->assertLessThanOrEqual(30 * 60, $time_remaining); // Should be <= 30 minutes in seconds
    }
    
    public function test_progress_cleanup() {
        // Create progress for multiple users
        $progress_data = [
            'current_question' => 1,
            'total_questions' => 5,
            'answers_data' => ['A'],
            'progress_percentage' => 20
        ];
        
        mcqhome_update_user_progress($this->test_student, $this->test_mcq_set, $progress_data);
        mcqhome_update_user_progress($this->test_teacher, $this->test_mcq_set, $progress_data);
        
        // End session for one user
        $result = mcqhome_end_assessment_session($this->test_student, $this->test_mcq_set);
        $this->assertTrue($result !== false);
        
        // Verify progress was removed for that user
        $progress = mcqhome_get_user_progress($this->test_student, $this->test_mcq_set);
        $this->assertNull($progress);
        
        // Verify other user's progress still exists
        $other_progress = mcqhome_get_user_progress($this->test_teacher, $this->test_mcq_set);
        $this->assertNotNull($other_progress);
    }
    
    public function test_assessment_statistics() {
        // Create some completed attempts
        $this->createTestAttempt($this->test_student, $this->test_mcq_set, [
            'total_score' => 8,
            'max_score' => 10,
            'is_passed' => 1,
            'time_taken' => 300
        ]);
        
        $this->createTestAttempt($this->test_student, $this->test_mcq_set, [
            'total_score' => 6,
            'max_score' => 10,
            'is_passed' => 1,
            'time_taken' => 450
        ]);
        
        // Get user statistics
        $stats = mcqhome_get_user_assessment_stats($this->test_student);
        
        $this->assertIsObject($stats);
        $this->assertEquals(2, $stats->total_attempts);
        $this->assertEquals(2, $stats->passed_attempts);
        $this->assertEquals(7, $stats->avg_score); // (8+6)/2 = 7
        $this->assertEquals(8, $stats->best_score);
        $this->assertEquals(750, $stats->total_time); // 300+450 = 750
    }
    
    public function test_mcq_set_statistics() {
        // Create attempts from different users
        $this->createTestAttempt($this->test_student, $this->test_mcq_set, [
            'total_score' => 8,
            'max_score' => 10,
            'is_passed' => 1,
            'time_taken' => 300
        ]);
        
        $this->createTestAttempt($this->test_teacher, $this->test_mcq_set, [
            'total_score' => 6,
            'max_score' => 10,
            'is_passed' => 1,
            'time_taken' => 400
        ]);
        
        // Get MCQ set statistics
        $stats = mcqhome_get_mcq_set_stats($this->test_mcq_set);
        
        $this->assertIsObject($stats);
        $this->assertEquals(2, $stats->total_users);
        $this->assertEquals(2, $stats->total_attempts);
        $this->assertEquals(70, $stats->avg_score); // ((8/10)*100 + (6/10)*100)/2 = 70
        $this->assertEquals(2, $stats->passed_attempts);
        $this->assertEquals(350, $stats->avg_time); // (300+400)/2 = 350
    }
    
    public function test_progress_data_integrity() {
        // Test with various data types
        $complex_data = [
            'current_question' => 3,
            'total_questions' => 10,
            'answers_data' => ['A', 'B', '', 'D'], // Including empty answer
            'progress_percentage' => 30.5,
            'custom_data' => [
                'start_time' => time(),
                'question_times' => [45, 60, 30],
                'flags' => ['review' => true, 'bookmark' => false]
            ]
        ];
        
        $progress_id = mcqhome_update_user_progress($this->test_student, $this->test_mcq_set, $complex_data);
        $this->assertIsNumeric($progress_id);
        
        // Retrieve and verify data integrity
        $saved_progress = mcqhome_get_user_progress($this->test_student, $this->test_mcq_set);
        $this->assertEquals(3, $saved_progress->current_question);
        $this->assertEquals(30.5, $saved_progress->progress_percentage);
        
        $saved_answers = maybe_unserialize($saved_progress->answers_data);
        $this->assertEquals(['A', 'B', '', 'D'], $saved_answers);
    }
    
    /**
     * Helper method to create test attempt
     */
    private function createTestAttempt($user_id, $set_id, $attempt_data) {
        global $wpdb;
        
        $defaults = [
            'user_id' => $user_id,
            'mcq_set_id' => $set_id,
            'total_questions' => 5,
            'answered_questions' => 5,
            'correct_answers' => 3,
            'total_score' => 6,
            'max_score' => 10,
            'score_percentage' => 60,
            'passing_score' => 6,
            'is_passed' => 1,
            'time_taken' => 300,
            'status' => 'completed',
            'started_at' => current_time('mysql'),
            'completed_at' => current_time('mysql')
        ];
        
        $attempt_data = wp_parse_args($attempt_data, $defaults);
        
        return $wpdb->insert(
            $wpdb->prefix . 'mcq_set_attempts',
            $attempt_data,
            ['%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%d', '%d', '%s', '%s', '%s']
        );
    }
}