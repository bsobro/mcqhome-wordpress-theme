<?php
/**
 * MCQ Test Helper Class
 */

class MCQTestHelper {
    
    /**
     * Create a sample MCQ with all required fields
     */
    public static function createSampleMCQ($args = []) {
        $defaults = [
            'post_title' => 'Sample MCQ Question',
            'post_type' => 'mcq',
            'post_status' => 'publish',
            'post_author' => 1,
            'question_text' => 'What is the correct answer to this sample question?',
            'option_a' => 'First option',
            'option_b' => 'Second option (correct)',
            'option_c' => 'Third option',
            'option_d' => 'Fourth option',
            'correct_answer' => 'B',
            'explanation' => 'The second option is correct because it is marked as the correct answer.',
            'marks' => 1,
            'negative_marks' => 0.25,
            'difficulty' => 'medium'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Create the post
        $mcq_id = wp_insert_post([
            'post_title' => $args['post_title'],
            'post_type' => $args['post_type'],
            'post_status' => $args['post_status'],
            'post_author' => $args['post_author']
        ]);
        
        if (is_wp_error($mcq_id)) {
            return false;
        }
        
        // Add meta data
        update_post_meta($mcq_id, '_mcq_question_text', $args['question_text']);
        update_post_meta($mcq_id, '_mcq_option_a', $args['option_a']);
        update_post_meta($mcq_id, '_mcq_option_b', $args['option_b']);
        update_post_meta($mcq_id, '_mcq_option_c', $args['option_c']);
        update_post_meta($mcq_id, '_mcq_option_d', $args['option_d']);
        update_post_meta($mcq_id, '_mcq_correct_answer', $args['correct_answer']);
        update_post_meta($mcq_id, '_mcq_explanation', $args['explanation']);
        update_post_meta($mcq_id, '_mcq_marks', $args['marks']);
        update_post_meta($mcq_id, '_mcq_negative_marks', $args['negative_marks']);
        
        // Add taxonomy terms if specified
        if (isset($args['difficulty'])) {
            wp_set_post_terms($mcq_id, $args['difficulty'], 'mcq_difficulty');
        }
        
        if (isset($args['subject'])) {
            wp_set_post_terms($mcq_id, $args['subject'], 'mcq_subject');
        }
        
        if (isset($args['topic'])) {
            wp_set_post_terms($mcq_id, $args['topic'], 'mcq_topic');
        }
        
        return $mcq_id;
    }
    
    /**
     * Create a sample MCQ set
     */
    public static function createSampleMCQSet($args = []) {
        $defaults = [
            'post_title' => 'Sample MCQ Set',
            'post_type' => 'mcq_set',
            'post_status' => 'publish',
            'post_author' => 1,
            'mcq_count' => 5,
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0.25,
            'display_format' => 'next_next',
            'time_limit' => 30
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Create MCQs for the set if not provided
        if (!isset($args['mcq_ids'])) {
            $mcq_ids = [];
            for ($i = 1; $i <= $args['mcq_count']; $i++) {
                $mcq_id = self::createSampleMCQ([
                    'post_title' => "MCQ {$i} for Set",
                    'post_author' => $args['post_author'],
                    'question_text' => "Question {$i}: What is the answer to question {$i}?",
                    'option_a' => "Option A for question {$i}",
                    'option_b' => "Option B for question {$i} (correct)",
                    'option_c' => "Option C for question {$i}",
                    'option_d' => "Option D for question {$i}",
                    'correct_answer' => 'B',
                    'explanation' => "Explanation for question {$i}"
                ]);
                
                if ($mcq_id) {
                    $mcq_ids[] = $mcq_id;
                }
            }
            $args['mcq_ids'] = $mcq_ids;
        }
        
        // Create the MCQ set post
        $set_id = wp_insert_post([
            'post_title' => $args['post_title'],
            'post_type' => $args['post_type'],
            'post_status' => $args['post_status'],
            'post_author' => $args['post_author']
        ]);
        
        if (is_wp_error($set_id)) {
            return false;
        }
        
        // Add meta data
        update_post_meta($set_id, '_mcq_set_questions', $args['mcq_ids']);
        update_post_meta($set_id, '_mcq_set_total_marks', $args['total_marks']);
        update_post_meta($set_id, '_mcq_set_passing_marks', $args['passing_marks']);
        update_post_meta($set_id, '_mcq_set_negative_marking', $args['negative_marking']);
        update_post_meta($set_id, '_mcq_set_display_format', $args['display_format']);
        update_post_meta($set_id, '_mcq_set_time_limit', $args['time_limit']);
        
        // Calculate individual marks if not provided
        if (!isset($args['individual_marks'])) {
            $individual_marks = array_fill(0, count($args['mcq_ids']), 
                $args['total_marks'] / count($args['mcq_ids']));
            update_post_meta($set_id, '_mcq_set_individual_marks', $individual_marks);
        }
        
        return $set_id;
    }
    
    /**
     * Validate MCQ data structure
     */
    public static function validateMCQData($mcq_id) {
        $errors = [];
        
        // Check if post exists and is correct type
        $post = get_post($mcq_id);
        if (!$post || $post->post_type !== 'mcq') {
            $errors[] = 'Invalid MCQ post';
            return $errors;
        }
        
        // Check required meta fields
        $required_fields = [
            '_mcq_question_text' => 'Question text',
            '_mcq_option_a' => 'Option A',
            '_mcq_option_b' => 'Option B',
            '_mcq_option_c' => 'Option C',
            '_mcq_option_d' => 'Option D',
            '_mcq_correct_answer' => 'Correct answer',
            '_mcq_explanation' => 'Explanation'
        ];
        
        foreach ($required_fields as $field => $label) {
            $value = get_post_meta($mcq_id, $field, true);
            if (empty($value)) {
                $errors[] = "Missing {$label}";
            }
        }
        
        // Validate correct answer
        $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
        if (!in_array($correct_answer, ['A', 'B', 'C', 'D'])) {
            $errors[] = 'Invalid correct answer value';
        }
        
        // Check that all options are filled
        $options = [
            get_post_meta($mcq_id, '_mcq_option_a', true),
            get_post_meta($mcq_id, '_mcq_option_b', true),
            get_post_meta($mcq_id, '_mcq_option_c', true),
            get_post_meta($mcq_id, '_mcq_option_d', true)
        ];
        
        foreach ($options as $i => $option) {
            if (empty(trim($option))) {
                $errors[] = 'Option ' . chr(65 + $i) . ' is empty';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate MCQ Set data structure
     */
    public static function validateMCQSetData($set_id) {
        $errors = [];
        
        // Check if post exists and is correct type
        $post = get_post($set_id);
        if (!$post || $post->post_type !== 'mcq_set') {
            $errors[] = 'Invalid MCQ Set post';
            return $errors;
        }
        
        // Check required meta fields
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        if (empty($mcq_ids) || !is_array($mcq_ids)) {
            $errors[] = 'No MCQs assigned to set';
        } else {
            // Validate each MCQ in the set
            foreach ($mcq_ids as $mcq_id) {
                $mcq_errors = self::validateMCQData($mcq_id);
                if (!empty($mcq_errors)) {
                    $errors[] = "MCQ {$mcq_id} has errors: " . implode(', ', $mcq_errors);
                }
            }
        }
        
        // Check scoring configuration
        $total_marks = get_post_meta($set_id, '_mcq_set_total_marks', true);
        $passing_marks = get_post_meta($set_id, '_mcq_set_passing_marks', true);
        
        if (empty($total_marks) || $total_marks <= 0) {
            $errors[] = 'Invalid total marks';
        }
        
        if (empty($passing_marks) || $passing_marks < 0) {
            $errors[] = 'Invalid passing marks';
        }
        
        if ($passing_marks > $total_marks) {
            $errors[] = 'Passing marks cannot be greater than total marks';
        }
        
        return $errors;
    }
    
    /**
     * Generate test answers for an MCQ set
     */
    public static function generateTestAnswers($set_id, $correct_percentage = 70) {
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        if (empty($mcq_ids)) {
            return [];
        }
        
        $answers = [];
        $correct_count = ceil(count($mcq_ids) * ($correct_percentage / 100));
        
        foreach ($mcq_ids as $index => $mcq_id) {
            if ($index < $correct_count) {
                // Give correct answer
                $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
                $answers[$index] = $correct_answer;
            } else {
                // Give random incorrect answer
                $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
                $options = ['A', 'B', 'C', 'D'];
                $incorrect_options = array_diff($options, [$correct_answer]);
                $answers[$index] = $incorrect_options[array_rand($incorrect_options)];
            }
        }
        
        return $answers;
    }
    
    /**
     * Calculate expected score for given answers
     */
    public static function calculateExpectedScore($set_id, $answers) {
        $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
        $individual_marks = get_post_meta($set_id, '_mcq_set_individual_marks', true);
        $negative_marking = get_post_meta($set_id, '_mcq_set_negative_marking', true) ?: 0;
        
        $total_score = 0;
        $correct_count = 0;
        
        foreach ($mcq_ids as $index => $mcq_id) {
            if (!isset($answers[$index])) {
                continue; // Unanswered
            }
            
            $correct_answer = get_post_meta($mcq_id, '_mcq_correct_answer', true);
            $question_marks = isset($individual_marks[$index]) ? $individual_marks[$index] : 1;
            
            if ($answers[$index] === $correct_answer) {
                $total_score += $question_marks;
                $correct_count++;
            } else {
                $total_score -= ($question_marks * $negative_marking);
            }
        }
        
        return [
            'total_score' => max(0, $total_score),
            'correct_count' => $correct_count,
            'total_questions' => count($mcq_ids),
            'answered_questions' => count($answers)
        ];
    }
}