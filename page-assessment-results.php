<?php
/**
 * Template for displaying MCQ assessment results
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Get parameters
$mcq_set_id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

if (!$mcq_set_id) {
    wp_redirect(home_url());
    exit;
}

// Get MCQ set data
$mcq_set = get_post($mcq_set_id);
if (!$mcq_set || $mcq_set->post_type !== 'mcq_set') {
    wp_redirect(home_url());
    exit;
}

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get detailed assessment results using the new scoring engine
$results_data = mcqhome_get_assessment_results($user_id, $mcq_set_id, $attempt_id);

if (is_wp_error($results_data)) {
    wp_redirect(home_url());
    exit;
}

$attempt = $results_data['attempt'];
$question_results = $results_data['question_results'];
$summary = $results_data['summary'];

// Get performance analytics
$performance_analytics = mcqhome_get_user_performance_analytics($user_id, $mcq_set_id);
$performance_comparison = mcqhome_get_performance_comparison($user_id, $mcq_set_id);

// Get MCQ set configuration
$show_detailed_results = get_post_meta($mcq_set_id, '_mcq_set_show_detailed_results', true) !== 'no';
$negative_marking = get_post_meta($mcq_set_id, '_mcq_set_negative_marking', true) ?: 0;
?>

<div class="assessment-results-container bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- Results Header -->
        <div class="results-header bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo esc_html($mcq_set->post_title); ?></h1>
                <p class="text-gray-600 mb-6"><?php _e('Assessment Results', 'mcqhome'); ?></p>
                
                <!-- Pass/Fail Status -->
                <div class="status-indicator mb-6">
                    <?php if ($attempt->is_passed): ?>
                    <div class="pass-status bg-green-100 border-2 border-green-500 rounded-lg p-4 inline-block">
                        <div class="flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="text-2xl font-bold text-green-700"><?php _e('PASSED', 'mcqhome'); ?></div>
                                <div class="text-sm text-green-600"><?php _e('Congratulations!', 'mcqhome'); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="fail-status bg-red-100 border-2 border-red-500 rounded-lg p-4 inline-block">
                        <div class="flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="text-2xl font-bold text-red-700"><?php _e('NOT PASSED', 'mcqhome'); ?></div>
                                <div class="text-sm text-red-600"><?php _e('Keep practicing!', 'mcqhome'); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Score Display -->
                <div class="score-display">
                    <div class="text-6xl font-bold text-blue-600 mb-2">
                        <?php echo number_format($attempt->total_score, 1); ?>
                    </div>
                    <div class="text-xl text-gray-600 mb-4">
                        <?php printf(__('out of %s marks', 'mcqhome'), number_format($attempt->max_score, 1)); ?>
                    </div>
                    <div class="text-lg text-gray-700">
                        <?php printf(__('Score: %s%%', 'mcqhome'), number_format($attempt->score_percentage, 1)); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary Statistics -->
        <div class="results-summary bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Summary', 'mcqhome'); ?></h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="stat-item text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $summary['total_questions']; ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Total Questions', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-item text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600"><?php echo $summary['correct_answers']; ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Correct Answers', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-item text-center p-4 bg-red-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600"><?php echo $summary['answered_questions'] - $summary['correct_answers']; ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Incorrect Answers', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-item text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-600"><?php echo $summary['total_questions'] - $summary['answered_questions']; ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Unanswered', 'mcqhome'); ?></div>
                </div>
            </div>
            
            <div class="additional-stats mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat-item text-center p-3 border rounded-lg">
                    <div class="text-lg font-semibold text-gray-700"><?php echo mcqhome_format_duration($summary['time_taken']); ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Time Taken', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-item text-center p-3 border rounded-lg">
                    <div class="text-lg font-semibold text-gray-700"><?php echo number_format($summary['passing_score'], 1); ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Passing Score', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-item text-center p-3 border rounded-lg">
                    <div class="text-lg font-semibold text-gray-700"><?php echo date('M j, Y g:i A', strtotime($attempt->completed_at)); ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Completed At', 'mcqhome'); ?></div>
                </div>
            </div>
            
            <?php if ($negative_marking > 0): ?>
            <div class="negative-marking-info mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-yellow-800">
                        <?php printf(__('Negative marking applied: -%s points for incorrect answers', 'mcqhome'), number_format($negative_marking * 100, 0) . '%'); ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Performance Analytics -->
        <?php if (!is_wp_error($performance_comparison)): ?>
        <div class="performance-analytics bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Performance Analysis', 'mcqhome'); ?></h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Performance Comparison -->
                <div class="comparison-stats">
                    <h3 class="text-lg font-medium text-gray-700 mb-3"><?php _e('How You Compare', 'mcqhome'); ?></h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600"><?php _e('Your Score', 'mcqhome'); ?></span>
                            <span class="font-semibold text-blue-600"><?php echo number_format($performance_comparison['user_score'], 1); ?>%</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600"><?php _e('Average Score', 'mcqhome'); ?></span>
                            <span class="font-semibold text-gray-700"><?php echo number_format($performance_comparison['average_score'], 1); ?>%</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600"><?php _e('Highest Score', 'mcqhome'); ?></span>
                            <span class="font-semibold text-green-600"><?php echo number_format($performance_comparison['highest_score'], 1); ?>%</span>
                        </div>
                        
                        <div class="performance-level mt-4 p-3 border-2 <?php 
                            $level = $performance_comparison['performance_level']['level'];
                            echo $level === 'excellent' ? 'border-green-500 bg-green-50' : 
                                ($level === 'good' ? 'border-blue-500 bg-blue-50' : 
                                ($level === 'average' ? 'border-yellow-500 bg-yellow-50' : 'border-red-500 bg-red-50'));
                        ?> rounded-lg text-center">
                            <div class="text-lg font-semibold <?php 
                                echo $level === 'excellent' ? 'text-green-700' : 
                                    ($level === 'good' ? 'text-blue-700' : 
                                    ($level === 'average' ? 'text-yellow-700' : 'text-red-700'));
                            ?>">
                                <?php echo esc_html($performance_comparison['performance_level']['label']); ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                <?php printf(__('Better than %s%% of participants', 'mcqhome'), number_format($performance_comparison['percentile_rank'], 1)); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Overall Statistics -->
                <div class="overall-stats">
                    <h3 class="text-lg font-medium text-gray-700 mb-3"><?php _e('Your Overall Performance', 'mcqhome'); ?></h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600"><?php _e('Total Attempts', 'mcqhome'); ?></span>
                            <span class="font-semibold text-gray-700"><?php echo $performance_analytics['overall_stats']->total_attempts; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600"><?php _e('Pass Rate', 'mcqhome'); ?></span>
                            <span class="font-semibold text-green-600"><?php echo number_format($performance_analytics['pass_rate'], 1); ?>%</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600"><?php _e('Average Score', 'mcqhome'); ?></span>
                            <span class="font-semibold text-blue-600"><?php echo number_format($performance_analytics['overall_stats']->avg_score, 1); ?>%</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600"><?php _e('Best Score', 'mcqhome'); ?></span>
                            <span class="font-semibold text-green-600"><?php echo number_format($performance_analytics['overall_stats']->best_score, 1); ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($show_detailed_results): ?>
        <!-- Detailed Results Toggle -->
        <div class="detailed-results-toggle bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800"><?php _e('Detailed Review', 'mcqhome'); ?></h2>
                <button type="button" 
                        id="toggle-detailed-results" 
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200">
                    <?php _e('Show Detailed Results', 'mcqhome'); ?>
                </button>
            </div>
            <p class="text-gray-600 mt-2"><?php _e('Review each question with correct answers and explanations.', 'mcqhome'); ?></p>
        </div>
        
        <!-- Detailed Results Section -->
        <div id="detailed-results-section" class="detailed-results hidden">
            <?php foreach ($question_results as $index => $result): 
                $is_correct = $result->is_correct;
                $selected_answer = $result->selected_answer;
                $correct_answer = $result->correct_answer;
                $question_number = $index + 1;
            ?>
            <div class="question-result bg-white rounded-lg shadow-md p-6 mb-4 <?php echo $is_correct ? 'border-l-4 border-green-500' : ($selected_answer ? 'border-l-4 border-red-500' : 'border-l-4 border-gray-400'); ?>">
                <div class="question-header flex justify-between items-start mb-4">
                    <div class="question-info">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <?php printf(__('Question %d', 'mcqhome'), $question_number); ?>
                        </h3>
                        <div class="question-status mt-1">
                            <?php if ($is_correct): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <?php _e('Correct', 'mcqhome'); ?>
                            </span>
                            <?php elseif ($selected_answer): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <?php _e('Incorrect', 'mcqhome'); ?>
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <?php _e('Not Answered', 'mcqhome'); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="score-info text-right">
                        <div class="text-lg font-semibold <?php echo $result->score_points > 0 ? 'text-green-600' : ($result->score_points < 0 ? 'text-red-600' : 'text-gray-600'); ?>">
                            <?php echo $result->score_points > 0 ? '+' : ''; ?><?php echo number_format($result->score_points, 1); ?>
                        </div>
                        <div class="text-sm text-gray-600"><?php _e('points', 'mcqhome'); ?></div>
                    </div>
                </div>
                
                <div class="question-content mb-4">
                    <div class="question-text text-gray-800 leading-relaxed">
                        <?php echo wp_kses_post($result->question_text); ?>
                    </div>
                </div>
                
                <div class="answer-options space-y-2 mb-4">
                    <?php 
                    foreach ($result->options as $key => $option_text): 
                        if (empty($option_text)) continue;
                        
                        $is_selected = ($selected_answer === $key);
                        $is_correct_option = ($correct_answer === $key);
                        
                        $option_class = 'option-display flex items-start p-3 border rounded-lg ';
                        if ($is_correct_option) {
                            $option_class .= 'border-green-500 bg-green-50';
                        } elseif ($is_selected && !$is_correct_option) {
                            $option_class .= 'border-red-500 bg-red-50';
                        } else {
                            $option_class .= 'border-gray-200 bg-gray-50';
                        }
                    ?>
                    <div class="<?php echo $option_class; ?>">
                        <div class="option-indicator mr-3 mt-1">
                            <?php if ($is_correct_option): ?>
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <?php elseif ($is_selected): ?>
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <?php else: ?>
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full"></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-content">
                            <span class="option-letter font-semibold text-gray-700 mr-2"><?php echo $key; ?>.</span>
                            <span class="option-text"><?php echo esc_html($option_text); ?></span>
                            
                            <?php if ($is_selected): ?>
                            <span class="ml-2 text-sm font-medium <?php echo $is_correct_option ? 'text-green-600' : 'text-red-600'; ?>">
                                (<?php echo $is_correct_option ? __('Your answer - Correct', 'mcqhome') : __('Your answer', 'mcqhome'); ?>)
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($is_correct_option && !$is_selected): ?>
                            <span class="ml-2 text-sm font-medium text-green-600">
                                (<?php _e('Correct answer', 'mcqhome'); ?>)
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!empty($result->explanation)): ?>
                <div class="explanation bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-800 mb-2"><?php _e('Explanation:', 'mcqhome'); ?></h4>
                    <div class="text-blue-700 leading-relaxed">
                        <?php echo wp_kses_post($result->explanation); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="action-buttons bg-white rounded-lg shadow-md p-6 text-center">
            <div class="space-x-4">
                <a href="<?php echo home_url('/dashboard/'); ?>" 
                   class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors duration-200">
                    <?php _e('Back to Dashboard', 'mcqhome'); ?>
                </a>
                
                <a href="<?php echo get_permalink($mcq_set_id); ?>" 
                   class="inline-block bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    <?php _e('View MCQ Set', 'mcqhome'); ?>
                </a>
                
                <?php if ($attempt->is_passed): ?>
                <button type="button" 
                        onclick="window.print()" 
                        class="inline-block bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                    <?php _e('Print Certificate', 'mcqhome'); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-detailed-results');
    const detailedSection = document.getElementById('detailed-results-section');
    
    if (toggleButton && detailedSection) {
        toggleButton.addEventListener('click', function() {
            if (detailedSection.classList.contains('hidden')) {
                detailedSection.classList.remove('hidden');
                toggleButton.textContent = '<?php _e('Hide Detailed Results', 'mcqhome'); ?>';
            } else {
                detailedSection.classList.add('hidden');
                toggleButton.textContent = '<?php _e('Show Detailed Results', 'mcqhome'); ?>';
            }
        });
    }
});
</script>

<?php
get_footer();
?>