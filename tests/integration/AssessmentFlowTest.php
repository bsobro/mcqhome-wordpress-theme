<?php
/**
 * Integration test for complete assessment flow
 */

class AssessmentFlowTest extends MCQHome_TestCase {
    
    private $assessment_scenario;
    
    protected function setUp(): void {
        parent::setUp();
        
        // Create a complete assessment scenario
        $this->assessment_scenario = AssessmentTestHelper::createAssessmentScenario([
            'mcq_count' => 10,
            'total_marks' => 20,
            'passing_marks' => 12,
            'negative_marking' => 0.25,
            'time_limit' => 30
        ]);
    }
    
    protected function tearDown(): void {
        // Clean up assessment data
        AssessmentTestHelper::cleanupAssessmentData($this->assessment_scenario);
        parent::tearDown();
    }
    
    public function test_complete_assessment_flow() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Step 1: Start assessment session
        $session = mcqhome_start_assessment_session($student_id, $set_id);
        
        $this->assertIsArray($session);
        $this->assertTrue($session['success'] ?? false);
        $this->assertArrayHasKey('session_id', $session);
        $this->assertEquals(0, $session['current_question']);
        $this->assertEquals([], $session['answers']);
        
        // Step 2: Simulate answering questions progressively
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        $answers = [];
        
        for ($i = 0; $i < count($mcq_ids); $i++) {
            // Answer 70% correctly
            if ($i < 7) {
                $correct_answer = get_post_meta($mcq_ids[$i], '_mcq_correct_answer', true);
                $answers[$i] = $correct_answer;
            } else {
                // Wrong answers for remaining questions
                $answers[$i] = 'A'; // Assuming A is wrong for test MCQs
            }
            
            // Update progress
            $progress_percentage = (($i + 1) / count($mcq_ids)) * 100;
            $update_result = AssessmentTestHelper::updateAssessmentProgress(
                $student_id, $set_id, $i, array_slice($answers, 0, $i + 1), $progress_percentage
            );
            
            $this->assertTrue($update_result['success']);
            $this->assertEquals($i, $update_result['current_question']);
        }
        
        // Step 3: Submit assessment
        $submission_result = AssessmentTestHelper::submitAssessmentAnswers(
            $student_id, $set_id, $answers, 1200 // 20 minutes
        );
        
        $this->assertTrue($submission_result['success']);
        $this->assertArrayHasKey('attempt_id', $submission_result);
        $this->assertArrayHasKey('score_data', $submission_result);
        
        $score_data = $submission_result['score_data'];
        
        // Step 4: Verify scoring
        $this->assertEquals(10, $score_data['total_questions']);
        $this->assertEquals(10, $score_data['answered_questions']);
        $this->assertEquals(7, $score_data['correct_answers']);
        
        // Expected score: (7 * 2) - (3 * 2 * 0.25) = 14 - 1.5 = 12.5
        $this->assertEquals(12.5, $score_data['total_score']);
        $this->assertTrue($score_data['is_passed']); // 12.5 >= 12
        
        // Step 5: Verify attempt was saved
        $attempt_id = $submission_result['attempt_id'];
        $this->assertIsNumeric($attempt_id);
        $this->assertGreaterThan(0, $attempt_id);
        
        // Step 6: Retrieve and verify results
        $results = mcqhome_get_assessment_results($student_id, $set_id, $attempt_id);
        
        $this->assertFalse(is_wp_error($results));
        $this->assertArrayHasKey('attempt', $results);
        $this->assertArrayHasKey('question_results', $results);
        $this->assertArrayHasKey('summary', $results);
        
        $attempt = $results['attempt'];
        $this->assertEquals($student_id, $attempt->user_id);
        $this->assertEquals($set_id, $attempt->mcq_set_id);
        $this->assertEquals(12.5, $attempt->total_score);
        $this->assertEquals(1, $attempt->is_passed);
        
        // Step 7: Verify progress was cleaned up
        $remaining_progress = mcqhome_get_user_progress($student_id, $set_id);
        $this->assertNull($remaining_progress);
    }
    
    public function test_assessment_with_time_limit() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Start assessment
        $session = mcqhome_start_assessment_session($student_id, $set_id);
        $this->assertTrue($session['success'] ?? false);
        
        // Check time remaining
        $time_remaining = mcqhome_calculate_time_remaining($student_id, $set_id);
        $this->assertIsNumeric($time_remaining);
        $this->assertGreaterThan(0, $time_remaining);
        $this->assertLessThanOrEqual(30 * 60, $time_remaining); // 30 minutes max
        
        // Simulate time passing by updating the session start time
        global $wpdb;
        $past_time = date('Y-m-d H:i:s', time() - (25 * 60)); // 25 minutes ago
        $wpdb->update(
            $wpdb->prefix . 'mcq_user_progress',
            ['created_at' => $past_time],
            ['user_id' => $student_id, 'mcq_set_id' => $set_id],
            ['%s'],
            ['%d', '%d']
        );
        
        // Check remaining time again
        $updated_time_remaining = mcqhome_calculate_time_remaining($student_id, $set_id);
        $this->assertLessThan($time_remaining, $updated_time_remaining);
        $this->assertLessThanOrEqual(5 * 60, $updated_time_remaining); // Should be ~5 minutes left
    }
    
    public function test_assessment_session_validation() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Test validation without session
        $validation = mcqhome_validate_assessment_session($student_id, $set_id);
        $this->assertTrue(is_wp_error($validation));
        $this->assertEquals('no_session', $validation->get_error_code());
        
        // Start session
        mcqhome_start_assessment_session($student_id, $set_id);
        
        // Test validation with active session
        $validation = mcqhome_validate_assessment_session($student_id, $set_id);
        $this->assertTrue($validation === true || !is_wp_error($validation));
        
        // Test expired session
        global $wpdb;
        $expired_time = date('Y-m-d H:i:s', time() - (25 * 3600)); // 25 hours ago
        $wpdb->update(
            $wpdb->prefix . 'mcq_user_progress',
            ['created_at' => $expired_time],
            ['user_id' => $student_id, 'mcq_set_id' => $set_id],
            ['%s'],
            ['%d', '%d']
        );
        
        $expired_validation = mcqhome_validate_assessment_session($student_id, $set_id);
        $this->assertTrue(is_wp_error($expired_validation));
        $this->assertEquals('session_expired', $expired_validation->get_error_code());
    }
    
    public function test_assessment_navigation() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Start assessment
        mcqhome_start_assessment_session($student_id, $set_id);
        
        // Answer some questions
        $partial_answers = ['A', 'B', 'C'];
        AssessmentTestHelper::updateAssessmentProgress($student_id, $set_id, 2, $partial_answers, 30);
        
        // Get navigation data
        $nav_data = mcqhome_get_assessment_navigation($student_id, $set_id);
        
        $this->assertIsArray($nav_data);
        $this->assertEquals(10, $nav_data['total_questions']);
        $this->assertEquals(2, $nav_data['current_question']);
        $this->assertEquals(3, $nav_data['answered_count']);
        $this->assertEquals(30, $nav_data['progress_percentage']);
        $this->assertEquals($partial_answers, $nav_data['answers']);
    }
    
    public function test_multiple_attempts_handling() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Enable retakes
        update_post_meta($set_id, '_mcq_set_allow_retakes', true);
        
        // First attempt - poor score
        $first_answers = ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A']; // All wrong
        $first_result = AssessmentTestHelper::simulateAssessmentAttempt($student_id, $set_id, $first_answers);
        
        $this->assertTrue($first_result['success']);
        $this->assertFalse($first_result['score_data']['is_passed']);
        
        // Second attempt - better score
        $second_answers = ['B', 'C', 'B', 'C', 'B', 'C', 'B', 'C', 'B', 'C']; // All correct
        $second_result = AssessmentTestHelper::simulateAssessmentAttempt($student_id, $set_id, $second_answers);
        
        $this->assertTrue($second_result['success']);
        $this->assertTrue($second_result['score_data']['is_passed']);
        
        // Verify both attempts are recorded
        $user_attempts = mcqhome_get_user_attempts($student_id, $set_id);
        $this->assertCount(2, $user_attempts);
        
        // Get best attempt
        $best_attempt = mcqhome_get_user_best_attempt($student_id, $set_id);
        $this->assertNotNull($best_attempt);
        $this->assertEquals(20, $best_attempt->total_score); // Should be the second attempt
    }
    
    public function test_assessment_without_retakes() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Disable retakes
        update_post_meta($set_id, '_mcq_set_allow_retakes', false);
        
        // First attempt
        $first_answers = ['B', 'C', 'B', 'C', 'B', 'C', 'B', 'C', 'B', 'C'];
        $first_result = AssessmentTestHelper::simulateAssessmentAttempt($student_id, $set_id, $first_answers);
        
        $this->assertTrue($first_result['success']);
        
        // Second attempt should fail validation
        $second_answers = ['A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A'];
        $validation = mcqhome_validate_assessment_submission($student_id, $set_id, $second_answers);
        
        $this->assertTrue(is_wp_error($validation));
        $this->assertEquals('already_completed', $validation->get_error_code());
    }
    
    public function test_partial_assessment_resume() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Start assessment and answer some questions
        mcqhome_start_assessment_session($student_id, $set_id);
        $partial_answers = ['A', 'B', 'C', 'D', 'A'];
        AssessmentTestHelper::updateAssessmentProgress($student_id, $set_id, 4, $partial_answers, 50);
        
        // Simulate resuming session
        $resumed_session = mcqhome_start_assessment_session($student_id, $set_id);
        
        $this->assertTrue($resumed_session['resumed'] ?? false);
        $this->assertEquals(4, $resumed_session['current_question']);
        $this->assertEquals($partial_answers, $resumed_session['answers']);
        
        // Continue and complete assessment
        $complete_answers = ['A', 'B', 'C', 'D', 'A', 'B', 'C', 'D', 'A', 'B'];
        $final_result = AssessmentTestHelper::submitAssessmentAnswers($student_id, $set_id, $complete_answers);
        
        $this->assertTrue($final_result['success']);
        $this->assertEquals(10, $final_result['score_data']['answered_questions']);
    }
    
    public function test_assessment_statistics_accuracy() {
        $student_id = $this->assessment_scenario['student_id'];
        $set_id = $this->assessment_scenario['set_id'];
        
        // Create multiple attempts with known scores
        $test_attempts = [
            ['score' => 16, 'passed' => true, 'time' => 300],
            ['score' => 10, 'passed' => false, 'time' => 450],
            ['score' => 18, 'passed' => true, 'time' => 250]
        ];
        
        foreach ($test_attempts as $attempt_data) {
            // Create attempt directly in database for precise control
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'mcq_set_attempts',
                [
                    'user_id' => $student_id,
                    'mcq_set_id' => $set_id,
                    'total_questions' => 10,
                    'answered_questions' => 10,
                    'correct_answers' => $attempt_data['score'] / 2, // Assuming 2 marks per question
                    'total_score' => $attempt_data['score'],
                    'max_score' => 20,
                    'score_percentage' => ($attempt_data['score'] / 20) * 100,
                    'passing_score' => 12,
                    'is_passed' => $attempt_data['passed'] ? 1 : 0,
                    'time_taken' => $attempt_data['time'],
                    'status' => 'completed',
                    'started_at' => current_time('mysql'),
                    'completed_at' => current_time('mysql')
                ],
                ['%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%d', '%d', '%s', '%s', '%s']
            );
        }
        
        // Test user statistics
        $user_stats = mcqhome_get_user_assessment_stats($student_id);
        
        $this->assertEquals(3, $user_stats->total_attempts);
        $this->assertEquals(2, $user_stats->passed_attempts);
        $this->assertEquals(14.67, round($user_stats->avg_score, 2)); // (16+10+18)/3
        $this->assertEquals(18, $user_stats->best_score);
        $this->assertEquals(1000, $user_stats->total_time); // 300+450+250
        
        // Test MCQ set statistics
        $set_stats = mcqhome_get_mcq_set_stats($set_id);
        
        $this->assertEquals(1, $set_stats->total_users);
        $this->assertEquals(3, $set_stats->total_attempts);
        $this->assertEquals(73.33, round($set_stats->avg_score, 2)); // ((16/20)*100 + (10/20)*100 + (18/20)*100)/3
        $this->assertEquals(2, $set_stats->passed_attempts);
        $this->assertEquals(333.33, round($set_stats->avg_time, 2)); // (300+450+250)/3
    }
    
    public function test_scoring_algorithm_edge_cases() {
        $set_id = $this->assessment_scenario['set_id'];
        
        // Test various scoring scenarios
        $test_cases = AssessmentTestHelper::testScoringAlgorithm($set_id, [
            'all_correct' => array_fill(0, 10, 'B'), // Assuming B is always correct
            'all_wrong' => array_fill(0, 10, 'A'),   // Assuming A is always wrong
            'half_correct' => array_merge(array_fill(0, 5, 'B'), array_fill(0, 5, 'A')),
            'no_answers' => [],
            'partial_answers' => ['B', 'B', 'B'] // Only first 3 answered
        ]);
        
        // Verify all test cases passed
        foreach ($test_cases as $case_name => $result) {
            $this->assertTrue($result['passed'], "Scoring test case '{$case_name}' failed");
            
            if ($result['passed']) {
                $score_data = $result['score_data'];
                
                switch ($case_name) {
                    case 'all_correct':
                        $this->assertEquals(10, $score_data['correct_answers']);
                        $this->assertEquals(20, $score_data['total_score']);
                        $this->assertTrue($score_data['is_passed']);
                        break;
                        
                    case 'all_wrong':
                        $this->assertEquals(0, $score_data['correct_answers']);
                        $this->assertEquals(0, $score_data['total_score']); // Should not go negative
                        $this->assertFalse($score_data['is_passed']);
                        break;
                        
                    case 'half_correct':
                        $this->assertEquals(5, $score_data['correct_answers']);
                        // Expected: (5 * 2) - (5 * 2 * 0.25) = 10 - 2.5 = 7.5
                        $this->assertEquals(7.5, $score_data['total_score']);
                        $this->assertFalse($score_data['is_passed']); // 7.5 < 12
                        break;
                        
                    case 'no_answers':
                        $this->assertEquals(0, $score_data['correct_answers']);
                        $this->assertEquals(0, $score_data['answered_questions']);
                        $this->assertEquals(0, $score_data['total_score']);
                        break;
                        
                    case 'partial_answers':
                        $this->assertEquals(3, $score_data['correct_answers']);
                        $this->assertEquals(3, $score_data['answered_questions']);
                        $this->assertEquals(6, $score_data['total_score']);
                        break;
                }
            }
        }
    }
}