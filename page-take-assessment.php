<?php
/**
 * Template for taking MCQ assessments
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

// Get MCQ set ID from URL parameter
$mcq_set_id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;

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

// Check if user is enrolled in this MCQ set
$enrollment = mcqhome_check_user_enrollment($user_id, $mcq_set_id);
if (!$enrollment) {
    wp_redirect(home_url());
    exit;
}

// Get MCQ set configuration
$mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
$display_format = get_post_meta($mcq_set_id, '_mcq_set_display_format', true) ?: 'next_next';
$time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
$total_marks = get_post_meta($mcq_set_id, '_mcq_set_total_marks', true);
$passing_marks = get_post_meta($mcq_set_id, '_mcq_set_passing_marks', true);

if (empty($mcq_ids)) {
    wp_redirect(home_url());
    exit;
}

// Get user's current progress
$progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
$current_question = $progress ? $progress->current_question : 0;
$answers_data = $progress ? maybe_unserialize($progress->answers_data) : [];

// Get all MCQ questions
$mcqs = [];
foreach ($mcq_ids as $mcq_id) {
    $mcq = get_post($mcq_id);
    if ($mcq && $mcq->post_type === 'mcq') {
        $mcqs[] = [
            'id' => $mcq_id,
            'question' => get_post_meta($mcq_id, '_mcq_question_text', true),
            'option_a' => get_post_meta($mcq_id, '_mcq_option_a', true),
            'option_b' => get_post_meta($mcq_id, '_mcq_option_b', true),
            'option_c' => get_post_meta($mcq_id, '_mcq_option_c', true),
            'option_d' => get_post_meta($mcq_id, '_mcq_option_d', true),
            'correct_answer' => get_post_meta($mcq_id, '_mcq_correct_answer', true),
            'explanation' => get_post_meta($mcq_id, '_mcq_explanation', true),
            'marks' => get_post_meta($mcq_id, '_mcq_marks', true) ?: 1
        ];
    }
}

$total_questions = count($mcqs);
?>

<div class="mcq-assessment-container">
    <div class="assessment-header">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="assessment-info">
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo esc_html($mcq_set->post_title); ?></h1>
                    <div class="assessment-meta text-sm text-gray-600 mt-1">
                        <span class="total-questions"><?php printf(__('Total Questions: %d', 'mcqhome'), $total_questions); ?></span>
                        <span class="separator mx-2">•</span>
                        <span class="total-marks"><?php printf(__('Total Marks: %d', 'mcqhome'), $total_marks); ?></span>
                        <?php if ($time_limit): ?>
                        <span class="separator mx-2">•</span>
                        <span class="time-limit"><?php printf(__('Time Limit: %d minutes', 'mcqhome'), $time_limit); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="assessment-controls">
                    <?php if ($time_limit): ?>
                    <div class="timer-display bg-red-100 text-red-800 px-4 py-2 rounded-lg font-mono text-lg">
                        <span id="timer-display">--:--</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="assessment-body">
        <div class="container mx-auto px-4 py-6">
            <div class="flex gap-6">
                <!-- Question Navigation Panel -->
                <div class="question-nav-panel bg-white rounded-lg shadow-md p-4 w-64 h-fit sticky top-6">
                    <h3 class="text-lg font-semibold mb-4 cursor-pointer md:cursor-default"><?php _e('Questions', 'mcqhome'); ?></h3>
                    
                    <div class="progress-indicator mb-4">
                        <div class="progress-bar bg-gray-200 rounded-full h-2">
                            <div class="progress-fill bg-blue-500 h-2 rounded-full transition-all duration-300" 
                                 style="width: <?php echo ($current_question / $total_questions) * 100; ?>%"></div>
                        </div>
                        <div class="progress-text text-sm text-gray-600 mt-2">
                            <span id="progress-text"><?php printf(__('%d of %d completed', 'mcqhome'), count($answers_data), $total_questions); ?></span>
                        </div>
                    </div>
                    
                    <div class="question-grid grid grid-cols-5 gap-2">
                        <?php for ($i = 0; $i < $total_questions; $i++): 
                            $question_num = $i + 1;
                            $is_answered = isset($answers_data[$i]);
                            $is_current = ($i === $current_question);
                            
                            $btn_class = 'question-nav-btn w-8 h-8 rounded text-sm font-medium transition-all duration-200 ';
                            if ($is_current) {
                                $btn_class .= 'bg-blue-500 text-white ring-2 ring-blue-300';
                            } elseif ($is_answered) {
                                $btn_class .= 'bg-green-500 text-white hover:bg-green-600';
                            } else {
                                $btn_class .= 'bg-gray-200 text-gray-700 hover:bg-gray-300';
                            }
                        ?>
                        <button type="button" 
                                class="<?php echo $btn_class; ?>" 
                                data-question="<?php echo $i; ?>"
                                onclick="navigateToQuestion(<?php echo $i; ?>)"
                                aria-label="<?php printf(__('Go to question %d', 'mcqhome'), $question_num); ?>">
                            <?php echo $question_num; ?>
                        </button>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="legend mt-4 text-xs">
                        <div class="flex items-center mb-1">
                            <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                            <span><?php _e('Current', 'mcqhome'); ?></span>
                        </div>
                        <div class="flex items-center mb-1">
                            <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                            <span><?php _e('Answered', 'mcqhome'); ?></span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-gray-200 rounded mr-2"></div>
                            <span><?php _e('Not Answered', 'mcqhome'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Question Display Area -->
                <div class="question-display-area flex-1">
                    <?php if ($display_format === 'next_next'): ?>
                        <!-- Next-Next Format: Single Question Per Page -->
                        <div id="next-next-container" class="question-container">
                            <?php foreach ($mcqs as $index => $mcq): ?>
                            <div class="question-slide bg-white rounded-lg shadow-md p-6 <?php echo $index === $current_question ? 'active' : 'hidden'; ?>" 
                                 data-question="<?php echo $index; ?>">
                                
                                <div class="question-header mb-6">
                                    <div class="question-number text-sm text-gray-500 mb-2">
                                        <?php printf(__('Question %d of %d', 'mcqhome'), $index + 1, $total_questions); ?>
                                    </div>
                                    <div class="question-marks text-sm text-blue-600 font-medium">
                                        <?php printf(__('Marks: %s', 'mcqhome'), $mcq['marks']); ?>
                                    </div>
                                </div>
                                
                                <div class="question-content mb-6">
                                    <div class="question-text text-lg leading-relaxed">
                                        <?php echo wp_kses_post($mcq['question']); ?>
                                    </div>
                                </div>
                                
                                <div class="answer-options space-y-3">
                                    <?php 
                                    $options = ['A' => $mcq['option_a'], 'B' => $mcq['option_b'], 'C' => $mcq['option_c'], 'D' => $mcq['option_d']];
                                    $selected_answer = isset($answers_data[$index]) ? $answers_data[$index] : '';
                                    
                                    foreach ($options as $key => $option_text): 
                                        if (empty($option_text)) continue;
                                    ?>
                                    <label class="option-label flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors duration-200 <?php echo $selected_answer === $key ? 'border-blue-500 bg-blue-50' : ''; ?>">
                                        <input type="radio" 
                                               name="question_<?php echo $index; ?>" 
                                               value="<?php echo $key; ?>" 
                                               class="mt-1 mr-3 text-blue-500"
                                               <?php checked($selected_answer, $key); ?>
                                               onchange="saveAnswer(<?php echo $index; ?>, '<?php echo $key; ?>')">
                                        <div class="option-content">
                                            <span class="option-letter font-semibold text-gray-700 mr-2"><?php echo $key; ?>.</span>
                                            <span class="option-text"><?php echo esc_html($option_text); ?></span>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="question-navigation mt-8 flex justify-between">
                                    <button type="button" 
                                            class="prev-btn bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 <?php echo $index === 0 ? 'invisible' : ''; ?>"
                                            onclick="navigateToQuestion(<?php echo $index - 1; ?>)"
                                            aria-label="<?php _e('Go to previous question', 'mcqhome'); ?>">
                                        <span class="hidden sm:inline"><?php _e('Previous', 'mcqhome'); ?></span>
                                        <span class="sm:hidden">←</span>
                                    </button>
                                    
                                    <?php if ($index === $total_questions - 1): ?>
                                    <button type="button" 
                                            class="submit-btn bg-green-600 text-white px-8 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200"
                                            onclick="submitAssessment()"
                                            aria-label="<?php _e('Submit assessment', 'mcqhome'); ?>">
                                        <?php _e('Submit Assessment', 'mcqhome'); ?>
                                    </button>
                                    <?php else: ?>
                                    <button type="button" 
                                            class="next-btn bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200"
                                            onclick="navigateToQuestion(<?php echo $index + 1; ?>)"
                                            aria-label="<?php _e('Go to next question', 'mcqhome'); ?>">
                                        <span class="hidden sm:inline"><?php _e('Next', 'mcqhome'); ?></span>
                                        <span class="sm:hidden">→</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php else: ?>
                        <!-- Single Page Format: All Questions Visible -->
                        <div id="single-page-container" class="questions-list space-y-6">
                            <?php foreach ($mcqs as $index => $mcq): ?>
                            <div class="question-card bg-white rounded-lg shadow-md p-6" data-question="<?php echo $index; ?>">
                                <div class="question-header mb-4">
                                    <div class="flex justify-between items-center">
                                        <div class="question-number text-lg font-semibold text-gray-800">
                                            <?php printf(__('Question %d', 'mcqhome'), $index + 1); ?>
                                        </div>
                                        <div class="question-marks text-sm text-blue-600 font-medium">
                                            <?php printf(__('Marks: %s', 'mcqhome'), $mcq['marks']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="question-content mb-4">
                                    <div class="question-text leading-relaxed">
                                        <?php echo wp_kses_post($mcq['question']); ?>
                                    </div>
                                </div>
                                
                                <div class="answer-options space-y-2">
                                    <?php 
                                    $options = ['A' => $mcq['option_a'], 'B' => $mcq['option_b'], 'C' => $mcq['option_c'], 'D' => $mcq['option_d']];
                                    $selected_answer = isset($answers_data[$index]) ? $answers_data[$index] : '';
                                    
                                    foreach ($options as $key => $option_text): 
                                        if (empty($option_text)) continue;
                                    ?>
                                    <label class="option-label flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors duration-200 <?php echo $selected_answer === $key ? 'border-blue-500 bg-blue-50 selected' : ''; ?>"
                                           data-option="<?php echo $key; ?>">
                                        <input type="radio" 
                                               name="question_<?php echo $index; ?>" 
                                               value="<?php echo $key; ?>" 
                                               class="mt-1 mr-3 text-blue-500"
                                               <?php checked($selected_answer, $key); ?>
                                               onchange="saveAnswer(<?php echo $index; ?>, '<?php echo $key; ?>')"
                                               aria-describedby="option-<?php echo $index; ?>-<?php echo $key; ?>">
                                        <div class="option-content" id="option-<?php echo $index; ?>-<?php echo $key; ?>">
                                            <span class="option-letter font-medium text-gray-700 mr-2"><?php echo $key; ?>.</span>
                                            <span class="option-text"><?php echo esc_html($option_text); ?></span>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="submit-section bg-white rounded-lg shadow-md p-6 text-center">
                                <button type="button" 
                                        class="submit-btn bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-700 transition-colors duration-200"
                                        onclick="submitAssessment()">
                                    <?php _e('Submit Assessment', 'mcqhome'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assessment Data -->
<script type="text/javascript">
window.assessmentData = {
    setId: <?php echo $mcq_set_id; ?>,
    userId: <?php echo $user_id; ?>,
    displayFormat: '<?php echo $display_format; ?>',
    totalQuestions: <?php echo $total_questions; ?>,
    currentQuestion: <?php echo $current_question; ?>,
    timeLimit: <?php echo $time_limit ? $time_limit * 60 : 0; ?>, // Convert to seconds
    answersData: <?php echo json_encode($answers_data); ?>,
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('mcqhome_assessment_nonce'); ?>'
};
</script>

<?php
get_footer();
?>