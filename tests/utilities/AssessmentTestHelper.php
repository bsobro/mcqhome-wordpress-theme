<?php
/**
 * Assessment Test Helper Class
 */

class AssessmentTestHelper {
    
    /**
     * Create a complete assessment scenario for testing
     */
    public static function createAssessmentScenario($args = []) {
        $defaults = [
            'mcq_count' => 5,
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0.25,
            'time_limit' => 30
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Create test users
        $teacher_id = wp_insert_user([
            'user_login' => 'test_teacher_' . uniqid(),
            'user_email' => 'teacher_' . uniqid() . '@test.com',
            'user_pass' => 'test_password',
            'role' => 'teacher'
        ]);
        
        $student_id = wp_insert_user([
            'user_login' => 'test_student_' . uniqid(),
            'user_email' => 'student_' . uniqid() . '@test.com',
            'user_pass' => 'test_password',
            'role' => 'student'
        ]);
        
        // Create MCQs
        $mcq_ids = [];
        for ($i = 1; $i <= $args['mcq_count']; $i++) {
            $mcq_id = MCQTestHelper::createSampleMCQ([
                'post_title' => "Assessment MCQ {$i}",
                'post_author' => $teacher_id,
                'question_text' => "Assessment question {$i}: What is the correct answer?",
                'option_a' => "Option A for question {$i}",
                'option_b' => "Option B for question {$i} (correct)",
                'option_c' => "Option C for question {$i}",
                'option_d' => "Option D for question {$i}",
                'correct_answer' => 'B',
                'explanation' => "Explanation for assessment question {$i}"
            ]);
            
            if ($mcq_id) {
                $mcq_ids[] = $mcq_id;
            }
        }
        
        // Create MCQ Set
        $set_id = MCQTestHelper::createSampleMCQSet([
            'post_title' => 'Assessment Test Set',
            'post_author' => $teacher_id,
            'mcq_ids' => $mcq_ids,
            'total_marks' => $args['total_marks'],
            'passing_marks' => $args['passing_marks'],
            'negative_marking' => $args['negative_marking'],
            'time_limit' => $args['time_limit']
        ]);
        
        return [
            'teacher_id' => $teacher_id,
            'student_id' => $student_id,
            'mcq_ids' => $mcq_ids,
            'set_id' => $set_id,
            'scenario_data' => $args
        ];
    }
    
    /**
     * Simulate taking an assessment
     */
    public static function simulateAssessmentAttempt($user_id, $set_id, $answers = null, $time_taken = null) {
        // Get MCQ set data
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        if (empty($mcq_ids)) {
            return [
                'success' => false,
                'error' => 'No MCQs found in set'
            ];
        }
        
        // Generate answers if not provided
        if ($answers === null) {
            $answers = [];
            foreach ($mcq_ids as $index => $mcq_id) {
                // 70% chance of correct answer
                if (rand(1, 100) <= 70) {
                    $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
                    $answers[$index] = $correct_answer;
                } else {
                    $options = ['A', 'B', 'C', 'D'];
                    $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
                    $incorrect_options = array_diff($options, [$correct_answer]);
                    $answers[$index] = $incorrect_options[array_rand($incorrect_options)];
                }
            }
        }
        
        // Calculate time taken if not provided
        if ($time_taken === null) {
            $time_limit = get_post_meta($set_id, '_mcq_set_time_limit', true);
            $time_taken = $time_limit ? rand(60, $time_limit * 60) : rand(300, 1800);
        }
        
        // Start assessment session
        $session_data = self::startAssessmentSession($user_id, $set_id);
        
        // Submit answers
        $result = self::submitAssessmentAnswers($user_id, $set_id, $answers, $time_taken);
        
        return $result;
    }
    
    /**
     * Start an assessment session
     */
    public static function startAssessmentSession($user_id, $set_id) {
        global $wpdb;
        
        // Check if session already exists
        $existing_progress = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mcq_user_progress 
             WHERE user_id = %d AND mcq_set_id = %d",
            $user_id, $set_id
        ));
        
        if ($existing_progress) {
            return [
                'success' => true,
                'session_id' => $existing_progress->id,
                'resumed' => true
            ];
        }
        
        // Create new session
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        $progress_data = [
            'user_id' => $user_id,
            'mcq_set_id' => $set_id,
            'current_question' => 0,
            'total_questions' => count($mcq_ids),
            'answers_data' => serialize([]),
            'progress_percentage' => 0,
            'created_at' => current_time('mysql'),
            'last_activity' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'mcq_user_progress',
            $progress_data,
            ['%d', '%d', '%d', '%d', '%s', '%f', '%s', '%s']
        );
        
        if ($result === false) {
            return [
                'success' => false,
                'error' => 'Failed to create assessment session'
            ];
        }
        
        return [
            'success' => true,
            'session_id' => $wpdb->insert_id,
            'resumed' => false
        ];
    }
    
    /**
     * Submit assessment answers
     */
    public static function submitAssessmentAnswers($user_id, $set_id, $answers, $time_taken = 0) {
        // Validate answers
        $validation_result = self::validateAssessmentAnswers($set_id, $answers);
        if (!$validation_result['valid']) {
            return [
                'success' => false,
                'error' => $validation_result['error']
            ];
        }
        
        // Calculate score
        $score_result = self::calculateAssessmentScore($set_id, $answers);
        if (!$score_result['success']) {
            return $score_result;
        }
        
        // Save attempt to database
        $attempt_result = self::saveAssessmentAttempt($user_id, $set_id, $answers, $score_result, $time_taken);
        
        return $attempt_result;
    }
    
    /**
     * Validate assessment answers
     */
    public static function validateAssessmentAnswers($set_id, $answers) {
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        
        if (empty($mcq_ids)) {
            return [
                'valid' => false,
                'error' => 'No MCQs found in set'
            ];
        }
        
        // Check answer format
        if (!is_array($answers)) {
            return [
                'valid' => false,
                'error' => 'Answers must be an array'
            ];
        }
        
        // Validate individual answers
        foreach ($answers as $index => $answer) {
            if (!in_array($answer, ['A', 'B', 'C', 'D'])) {
                return [
                    'valid' => false,
                    'error' => "Invalid answer '{$answer}' for question " . ($index + 1)
                ];
            }
        }
        
        return ['valid' => true];
    }
    
    /**
     * Calculate assessment score
     */
    public static function calculateAssessmentScore($set_id, $answers) {
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        $individual_marks = get_post_meta($set_id, '_mcq_set_individual_marks', true);
        $negative_marking = get_post_meta($set_id, '_mcq_set_negative_marking', true) ?: 0;
        $total_marks = get_post_meta($set_id, '_mcq_set_total_marks', true);
        $passing_marks = get_post_meta($set_id, '_mcq_set_passing_marks', true);
        
        if (empty($mcq_ids)) {
            return [
                'success' => false,
                'error' => 'No MCQs found in set'
            ];
        }
        
        $total_questions = count($mcq_ids);
        $correct_answers = 0;
        $total_score = 0;
        $question_results = [];
        $answered_count = 0;
        
        foreach ($mcq_ids as $index => $mcq_id) {
            $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
            $selected_answer = isset($answers[$index]) ? $answers[$index] : '';
            $question_marks = isset($individual_marks[$index]) ? floatval($individual_marks[$index]) : 1;
            
            $is_correct = false;
            $score_points = 0;
            
            if ($selected_answer) {
                $answered_count++;
                $is_correct = ($selected_answer === $correct_answer);
                
                if ($is_correct) {
                    $correct_answers++;
                    $score_points = $question_marks;
                } else {
                    $score_points = -($question_marks * $negative_marking);
                }
            }
            
            $total_score += $score_points;
            
            $question_results[] = [
                'mcq_id' => $mcq_id,
                'question_index' => $index,
                'selected_answer' => $selected_answer,
                'correct_answer' => $correct_answer,
                'is_correct' => $is_correct,
                'score_points' => $score_points,
                'marks' => $question_marks,
                'answered' => !empty($selected_answer)
            ];
        }
        
        // Ensure minimum score is 0
        $total_score = max(0, $total_score);
        
        // Calculate percentage and pass/fail
        $score_percentage = $total_marks > 0 ? ($total_score / $total_marks) * 100 : 0;
        $is_passed = ($total_score >= $passing_marks);
        
        return [
            'success' => true,
            'total_questions' => $total_questions,
            'answered_questions' => $answered_count,
            'correct_answers' => $correct_answers,
            'total_score' => $total_score,
            'max_score' => $total_marks,
            'score_percentage' => round($score_percentage, 2),
            'passing_score' => $passing_marks,
            'is_passed' => $is_passed,
            'question_results' => $question_results,
            'negative_marking_rate' => $negative_marking
        ];
    }
    
    /**
     * Save assessment attempt
     */
    public static function saveAssessmentAttempt($user_id, $set_id, $answers, $score_data, $time_taken) {
        global $wpdb;
        
        // Save MCQ set attempt
        $attempt_data = [
            'user_id' => $user_id,
            'mcq_set_id' => $set_id,
            'total_questions' => $score_data['total_questions'],
            'answered_questions' => $score_data['answered_questions'],
            'correct_answers' => $score_data['correct_answers'],
            'total_score' => $score_data['total_score'],
            'max_score' => $score_data['max_score'],
            'score_percentage' => $score_data['score_percentage'],
            'passing_score' => $score_data['passing_score'],
            'is_passed' => $score_data['is_passed'] ? 1 : 0,
            'time_taken' => $time_taken,
            'status' => 'completed',
            'started_at' => current_time('mysql'),
            'completed_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'mcq_set_attempts',
            $attempt_data,
            ['%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%d', '%d', '%s', '%s', '%s']
        );
        
        if ($result === false) {
            return [
                'success' => false,
                'error' => 'Failed to save assessment attempt'
            ];
        }
        
        $attempt_id = $wpdb->insert_id;
        
        // Save individual MCQ attempts
        foreach ($score_data['question_results'] as $result) {
            $mcq_attempt_data = [
                'user_id' => $user_id,
                'mcq_id' => $result['mcq_id'],
                'mcq_set_id' => $set_id,
                'selected_answer' => $result['selected_answer'],
                'correct_answer' => $result['correct_answer'],
                'is_correct' => $result['is_correct'] ? 1 : 0,
                'score_points' => $result['score_points'],
                'negative_marking_applied' => $score_data['negative_marking_rate'],
                'completed_at' => current_time('mysql')
            ];
            
            $wpdb->insert(
                $wpdb->prefix . 'mcq_attempts',
                $mcq_attempt_data,
                ['%d', '%d', '%d', '%s', '%s', '%d', '%f', '%f', '%s']
            );
        }
        
        // Clean up progress data
        $wpdb->delete(
            $wpdb->prefix . 'mcq_user_progress',
            ['user_id' => $user_id, 'mcq_set_id' => $set_id],
            ['%d', '%d']
        );
        
        return [
            'success' => true,
            'attempt_id' => $attempt_id,
            'score_data' => $score_data,
            'time_taken' => $time_taken
        ];
    }
    
    /**
     * Test scoring algorithm accuracy
     */
    public static function testScoringAlgorithm($set_id, $test_cases = []) {
        $results = [];
        
        // Default test cases if none provided
        if (empty($test_cases)) {
            $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
            $question_count = count($mcq_ids);
            
            $test_cases = [
                'all_correct' => array_fill(0, $question_count, 'B'), // Assuming B is always correct in test data
                'all_wrong' => array_fill(0, $question_count, 'A'), // Assuming A is always wrong
                'half_correct' => array_merge(
                    array_fill(0, floor($question_count / 2), 'B'),
                    array_fill(0, ceil($question_count / 2), 'A')
                ),
                'no_answers' => []
            ];
        }
        
        foreach ($test_cases as $case_name => $answers) {
            $score_result = self::calculateAssessmentScore($set_id, $answers);
            
            if ($score_result['success']) {
                $results[$case_name] = [
                    'answers' => $answers,
                    'score_data' => $score_result,
                    'passed' => true
                ];
            } else {
                $results[$case_name] = [
                    'answers' => $answers,
                    'error' => $score_result['error'],
                    'passed' => false
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Test progress tracking accuracy
     */
    public static function testProgressTracking($user_id, $set_id) {
        // Start session
        $session_result = self::startAssessmentSession($user_id, $set_id);
        if (!$session_result['success']) {
            return [
                'success' => false,
                'error' => 'Failed to start session: ' . $session_result['error']
            ];
        }
        
        // Simulate progress updates
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        $progress_updates = [];
        
        for ($i = 0; $i < count($mcq_ids); $i++) {
            $answers = array_slice(['A', 'B', 'C', 'D'], 0, $i + 1);
            $progress_percentage = (($i + 1) / count($mcq_ids)) * 100;
            
            // Update progress
            $update_result = self::updateAssessmentProgress($user_id, $set_id, $i, $answers, $progress_percentage);
            $progress_updates[] = $update_result;
        }
        
        return [
            'success' => true,
            'session_id' => $session_result['session_id'],
            'progress_updates' => $progress_updates,
            'final_progress' => self::getAssessmentProgress($user_id, $set_id)
        ];
    }
    
    /**
     * Update assessment progress
     */
    public static function updateAssessmentProgress($user_id, $set_id, $current_question, $answers, $progress_percentage) {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mcq_user_progress',
            [
                'current_question' => $current_question,
                'answers_data' => serialize($answers),
                'progress_percentage' => $progress_percentage,
                'last_activity' => current_time('mysql')
            ],
            [
                'user_id' => $user_id,
                'mcq_set_id' => $set_id
            ],
            ['%d', '%s', '%f', '%s'],
            ['%d', '%d']
        );
        
        return [
            'success' => $result !== false,
            'current_question' => $current_question,
            'progress_percentage' => $progress_percentage,
            'answers_count' => count($answers)
        ];
    }
    
    /**
     * Get assessment progress
     */
    public static function getAssessmentProgress($user_id, $set_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mcq_user_progress 
             WHERE user_id = %d AND mcq_set_id = %d",
            $user_id, $set_id
        ));
    }
    
    /**
     * Clean up assessment test data
     */
    public static function cleanupAssessmentData($scenario_data) {
        global $wpdb;
        
        // Delete attempts
        if (isset($scenario_data['set_id'])) {
            $wpdb->delete(
                $wpdb->prefix . 'mcq_set_attempts',
                ['mcq_set_id' => $scenario_data['set_id']],
                ['%d']
            );
            
            $wpdb->delete(
                $wpdb->prefix . 'mcq_attempts',
                ['mcq_set_id' => $scenario_data['set_id']],
                ['%d']
            );
            
            $wpdb->delete(
                $wpdb->prefix . 'mcq_user_progress',
                ['mcq_set_id' => $scenario_data['set_id']],
                ['%d']
            );
        }
        
        // Delete posts
        if (isset($scenario_data['mcq_ids'])) {
            foreach ($scenario_data['mcq_ids'] as $mcq_id) {
                wp_delete_post($mcq_id, true);
            }
        }
        
        if (isset($scenario_data['set_id'])) {
            wp_delete_post($scenario_data['set_id'], true);
        }
        
        // Delete users
        if (isset($scenario_data['teacher_id'])) {
            wp_delete_user($scenario_data['teacher_id']);
        }
        
        if (isset($scenario_data['student_id'])) {
            wp_delete_user($scenario_data['student_id']);
        }
        
        return true;
    }
}