<?php
/**
 * Test scoring algorithm functionality
 */

class ScoringAlgorithmTest extends MCQHome_TestCase {
    
    public function test_basic_scoring_calculation() {
        // Create test MCQ set
        $set_id = $this->createTestMCQSet([
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0.25
        ]);
        
        // Test all correct answers
        $all_correct_answers = ['B', 'C', 'B', 'C', 'B']; // Assuming these are correct
        $score_data = mcqhome_calculate_assessment_score($set_id, $all_correct_answers);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertEquals(5, $score_data['correct_answers']);
        $this->assertEquals(10, $score_data['total_score']);
        $this->assertTrue($score_data['is_passed']);
    }
    
    public function test_negative_marking_calculation() {
        $set_id = $this->createTestMCQSet([
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0.25
        ]);
        
        // Test mixed answers (some correct, some wrong)
        $mixed_answers = ['B', 'A', 'B', 'A', 'B']; // 3 correct, 2 wrong
        $score_data = mcqhome_calculate_assessment_score($set_id, $mixed_answers);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertEquals(3, $score_data['correct_answers']);
        
        // Expected score: (3 * 2) - (2 * 2 * 0.25) = 6 - 1 = 5
        $expected_score = 5.0;
        $this->assertEquals($expected_score, $score_data['total_score']);
    }
    
    public function test_minimum_score_is_zero() {
        $set_id = $this->createTestMCQSet([
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 1.0 // High negative marking
        ]);
        
        // Test all wrong answers
        $all_wrong_answers = ['A', 'A', 'A', 'A', 'A'];
        $score_data = mcqhome_calculate_assessment_score($set_id, $all_wrong_answers);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertEquals(0, $score_data['correct_answers']);
        $this->assertEquals(0, $score_data['total_score']); // Should not go below 0
        $this->assertFalse($score_data['is_passed']);
    }
    
    public function test_individual_marks_calculation() {
        $set_id = $this->createTestMCQSet([
            'total_marks' => 15,
            'passing_marks' => 9,
            'negative_marking' => 0.25
        ]);
        
        // Set individual marks: [1, 2, 3, 4, 5]
        $individual_marks = [1, 2, 3, 4, 5];
        update_post_meta($set_id, '_mcq_set_individual_marks', $individual_marks);
        
        // Test all correct answers
        $all_correct_answers = ['B', 'C', 'B', 'C', 'B'];
        $score_data = mcqhome_calculate_assessment_score($set_id, $all_correct_answers);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertEquals(15, $score_data['total_score']); // 1+2+3+4+5 = 15
        $this->assertTrue($score_data['is_passed']);
    }
    
    public function test_partial_answers() {
        $set_id = $this->createTestMCQSet([
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0.25
        ]);
        
        // Test partial answers (only answer first 3 questions)
        $partial_answers = ['B', 'C', 'B']; // 3 correct, 2 unanswered
        $score_data = mcqhome_calculate_assessment_score($set_id, $partial_answers);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertEquals(3, $score_data['correct_answers']);
        $this->assertEquals(3, $score_data['answered_questions']);
        $this->assertEquals(6, $score_data['total_score']); // 3 * 2 = 6
        $this->assertTrue($score_data['is_passed']);
    }
    
    public function test_score_percentage_calculation() {
        $set_id = $this->createTestMCQSet([
            'total_marks' => 20,
            'passing_marks' => 12,
            'negative_marking' => 0
        ]);
        
        // Test 75% correct answers
        $answers = ['B', 'C', 'B', 'A', 'B']; // 4 correct, 1 wrong
        $score_data = mcqhome_calculate_assessment_score($set_id, $answers);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertEquals(16, $score_data['total_score']); // 4 * 4 = 16
        $this->assertEquals(80.0, $score_data['score_percentage']); // (16/20) * 100 = 80%
    }
    
    public function test_pass_fail_determination() {
        $set_id = $this->createTestMCQSet([
            'total_marks' => 10,
            'passing_marks' => 6,
            'negative_marking' => 0
        ]);
        
        // Test passing score
        $passing_answers = ['B', 'C', 'B', 'A', 'A']; // 3 correct = 6 points
        $score_data = mcqhome_calculate_assessment_score($set_id, $passing_answers);
        $this->assertTrue($score_data['is_passed']);
        
        // Test failing score
        $failing_answers = ['B', 'C', 'A', 'A', 'A']; // 2 correct = 4 points
        $score_data = mcqhome_calculate_assessment_score($set_id, $failing_answers);
        $this->assertFalse($score_data['is_passed']);
    }
    
    public function test_question_results_structure() {
        $set_id = $this->createTestMCQSet();
        $answers = ['B', 'A', 'B', 'C', 'B'];
        
        $score_data = mcqhome_calculate_assessment_score($set_id, $answers);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertArrayHasKey('question_results', $score_data);
        $this->assertCount(5, $score_data['question_results']);
        
        // Check structure of first question result
        $first_result = $score_data['question_results'][0];
        $this->assertArrayHasKey('mcq_id', $first_result);
        $this->assertArrayHasKey('selected_answer', $first_result);
        $this->assertArrayHasKey('correct_answer', $first_result);
        $this->assertArrayHasKey('is_correct', $first_result);
        $this->assertArrayHasKey('score_points', $first_result);
    }
    
    public function test_invalid_mcq_set() {
        // Test with non-existent MCQ set
        $score_data = mcqhome_calculate_assessment_score(99999, ['A', 'B', 'C']);
        
        $this->assertTrue(is_wp_error($score_data));
        $this->assertEquals('no_questions', $score_data->get_error_code());
    }
    
    public function test_empty_answers() {
        $set_id = $this->createTestMCQSet();
        
        // Test with empty answers array
        $score_data = mcqhome_calculate_assessment_score($set_id, []);
        
        $this->assertFalse(is_wp_error($score_data));
        $this->assertEquals(0, $score_data['correct_answers']);
        $this->assertEquals(0, $score_data['answered_questions']);
        $this->assertEquals(0, $score_data['total_score']);
    }
    
    public function test_scoring_with_different_negative_marking_rates() {
        $test_cases = [
            0 => 6, // No negative marking: 3 correct * 2 = 6
            0.25 => 5, // 25% negative: 6 - (2 * 2 * 0.25) = 5
            0.5 => 4, // 50% negative: 6 - (2 * 2 * 0.5) = 4
            1.0 => 2 // 100% negative: 6 - (2 * 2 * 1.0) = 2
        ];
        
        foreach ($test_cases as $negative_rate => $expected_score) {
            $set_id = $this->createTestMCQSet([
                'total_marks' => 10,
                'passing_marks' => 6,
                'negative_marking' => $negative_rate
            ]);
            
            // 3 correct, 2 wrong answers
            $answers = ['B', 'C', 'B', 'A', 'A'];
            $score_data = mcqhome_calculate_assessment_score($set_id, $answers);
            
            $this->assertEquals($expected_score, $score_data['total_score'], 
                "Failed for negative marking rate: {$negative_rate}");
        }
    }
}