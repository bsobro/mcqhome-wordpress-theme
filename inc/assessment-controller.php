<?php
/**
 * Assessment Delivery Engine Controller for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Assessment Controller Class
 */
class MCQHome_Assessment_Controller {

    /**
     * Start or resume an assessment session
     */
    public function start_assessment($mcq_set_id, $user_id) {
        // Validate access and enrollment
        $validation = $this->validate_access($mcq_set_id, $user_id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Get MCQ set configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Initialize or resume progress
        $progress = $this->initialize_progress($mcq_set_id, $user_id);
        if (is_wp_error($progress)) {
            return $progress;
        }

        // Return assessment data for rendering
        return [
            'config' => $config,
            'progress' => $progress,
            'session_data' => $this->get_session_data($mcq_set_id, $user_id)
        ];
    }

    /**
     * Validate user access to assessment
     */
    private function validate_access($mcq_set_id, $user_id) {
        // Check if MCQ set exists
        $mcq_set = get_post($mcq_set_id);
        if (!$mcq_set || $mcq_set->post_type !== 'mcq_set') {
            return new WP_Error('invalid_set', __('MCQ set not found.', 'mcqhome'));
        }

        // Check if user is logged in
        if (!$user_id) {
            return new WP_Error('not_logged_in', __('You must be logged in to take assessments.', 'mcqhome'));
        }

        // Check enrollment
        if (function_exists('mcqhome_check_user_enrollment')) {
            try {
                $enrollment = mcqhome_check_user_enrollment($user_id, $mcq_set_id);
                if (!$enrollment) {
                    return new WP_Error('not_enrolled', __('You are not enrolled in this assessment.', 'mcqhome'));
                }
            } catch (Exception $e) {
                // Allow access if enrollment system isn't ready
                error_log('MCQHome: Enrollment check failed - ' . $e->getMessage());
            }
        }

        // Check if retakes are allowed
        $allow_retakes = get_post_meta($mcq_set_id, '_mcq_set_allow_retakes', true);
        if (!$allow_retakes && function_exists('mcqhome_has_user_completed_set')) {
            if (mcqhome_has_user_completed_set($user_id, $mcq_set_id)) {
                return new WP_Error('retakes_not_allowed', __('You have already completed this assessment and retakes are not allowed.', 'mcqhome'));
            }
        }

        return true;
    }

    /**
     * Get assessment configuration including sections and questions
     */
    public function get_assessment_config($mcq_set_id) {
        // Get basic configuration
        $display_format = get_post_meta($mcq_set_id, '_mcq_set_display_format', true) ?: 'next_next';
        $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
        $sections_enabled = get_post_meta($mcq_set_id, '_mcq_set_sections_enabled', true);
        $pass_marks = get_post_meta($mcq_set_id, '_mcq_set_passing_marks', true);

        // Get sections (if enabled)
        $sections = [];
        if ($sections_enabled) {
            $sections_data = get_post_meta($mcq_set_id, '_mcq_set_sections', true);
            $sections = $sections_data ? json_decode($sections_data, true) : [];
        }

        // Get organized questions
        $questions = $this->get_organized_questions($mcq_set_id, $sections_enabled);
        if (is_wp_error($questions)) {
            return $questions;
        }

        return [
            'mcq_set_id' => $mcq_set_id,
            'display_format' => $display_format,
            'time_limit' => $time_limit,
            'sections_enabled' => $sections_enabled,
            'sections' => $sections,
            'questions' => $questions,
            'pass_marks' => $pass_marks,
            'total_questions' => $this->count_total_questions($questions)
        ];
    }

    /**
     * Get questions organized by sections or as a single group
     */
    private function get_organized_questions($mcq_set_id, $sections_enabled) {
        // Get question order configuration
        $questions_order_data = get_post_meta($mcq_set_id, '_mcq_set_questions_order', true);
        $questions_order = $questions_order_data ? json_decode($questions_order_data, true) : null;

        // Fallback to legacy question list if new format not available
        if (!$questions_order || !isset($questions_order['questions'])) {
            $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
            if (empty($mcq_ids)) {
                return new WP_Error('no_questions', __('No questions found in this MCQ set.', 'mcqhome'));
            }

            // Convert legacy format to new format
            $questions_order = ['questions' => []];
            foreach ($mcq_ids as $index => $mcq_id) {
                $questions_order['questions'][] = [
                    'mcq_id' => $mcq_id,
                    'section_id' => $sections_enabled ? 'default' : null,
                    'order' => $index + 1
                ];
            }
        }

        $organized = [];

        foreach ($questions_order['questions'] as $question_data) {
            $mcq_id = $question_data['mcq_id'];
            $section_id = $sections_enabled ? ($question_data['section_id'] ?? 'default') : 'default';

            // Initialize section if not exists
            if (!isset($organized[$section_id])) {
                $organized[$section_id] = [];
            }

            // Get MCQ data
            $mcq_data = $this->get_mcq_data($mcq_id);
            if ($mcq_data) {
                $organized[$section_id][] = $mcq_data;
            }
        }

        return $organized;
    }

    /**
     * Get MCQ data for assessment
     */
    private function get_mcq_data($mcq_id) {
        $mcq = get_post($mcq_id);
        if (!$mcq || $mcq->post_type !== 'mcq') {
            return null;
        }

        return [
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

    /**
     * Count total questions across all sections
     */
    private function count_total_questions($questions) {
        $total = 0;
        foreach ($questions as $section_questions) {
            $total += count($section_questions);
        }
        return $total;
    }

    /**
     * Initialize or resume assessment progress
     */
    private function initialize_progress($mcq_set_id, $user_id) {
        // Check for existing progress
        if (function_exists('mcqhome_get_user_progress')) {
            try {
                $existing_progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
                if ($existing_progress) {
                    return [
                        'current_question' => $existing_progress->current_question,
                        'answers' => maybe_unserialize($existing_progress->answers_data) ?: [],
                        'start_time' => strtotime($existing_progress->created_at),
                        'is_resume' => true
                    ];
                }
            } catch (Exception $e) {
                error_log('MCQHome: Progress check failed - ' . $e->getMessage());
            }
        }

        // Create new progress
        $progress_data = [
            'current_question' => 0,
            'answers_data' => [],
            'progress_percentage' => 0
        ];

        if (function_exists('mcqhome_update_user_progress')) {
            try {
                mcqhome_update_user_progress($user_id, $mcq_set_id, $progress_data);
            } catch (Exception $e) {
                error_log('MCQHome: Progress creation failed - ' . $e->getMessage());
            }
        }

        return [
            'current_question' => 0,
            'answers' => [],
            'start_time' => time(),
            'is_resume' => false
        ];
    }

    /**
     * Get session data for JavaScript
     */
    private function get_session_data($mcq_set_id, $user_id) {
        return [
            'mcq_set_id' => $mcq_set_id,
            'user_id' => $user_id,
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mcqhome_assessment_nonce'),
            'start_time' => time()
        ];
    }

    /**
     * Render assessment interface based on format
     */
    public function render_assessment($config, $progress) {
        if ($config['display_format'] === 'single_page') {
            return $this->render_single_page_assessment($config, $progress);
        } else {
            return $this->render_next_next_assessment($config, $progress);
        }
    }

    /**
     * Render Next-Next format assessment
     */
    private function render_next_next_assessment($config, $progress) {
        ob_start();
        ?>
        <div class="assessment-container next-next-format" role="application" aria-label="Assessment Interface">
            <!-- Question Navigation Panel -->
            <div class="question-navigation-panel" role="navigation" aria-label="Question Navigation">
                <?php $this->render_question_navigation($config, $progress); ?>
            </div>

            <!-- Main Assessment Area -->
            <div class="assessment-main" role="region" aria-label="Question Content">
                <!-- Current Question Display -->
                <div class="current-question-container" aria-live="polite" aria-atomic="true">
                    <?php $this->render_current_question($config, $progress); ?>
                </div>

                <!-- Navigation Controls -->
                <div class="question-controls" role="group" aria-label="Question Navigation Controls">
                    <button class="btn-previous prev-btn" 
                            onclick="navigateToPrevious()" 
                            aria-label="Go to previous question"
                            aria-keyshortcuts="Ctrl+ArrowLeft"><?php _e('Previous', 'mcqhome'); ?></button>
                    <button class="btn-skip" 
                            onclick="skipQuestion()"
                            aria-label="Skip current question"><?php _e('Skip', 'mcqhome'); ?></button>
                    <button class="btn-next next-btn" 
                            onclick="navigateToNext()"
                            aria-label="Go to next question"
                            aria-keyshortcuts="Ctrl+ArrowRight"><?php _e('Next', 'mcqhome'); ?></button>
                    <button class="btn-submit submit-btn hidden" 
                            onclick="submitAssessment()"
                            aria-label="Submit assessment"
                            aria-keyshortcuts="Ctrl+Enter"
                            aria-describedby="submit-warning"><?php _e('Submit Assessment', 'mcqhome'); ?></button>
                    <div id="submit-warning" class="sr-only">Warning: Submitting will end the assessment and cannot be undone.</div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Single Page format assessment
     */
    private function render_single_page_assessment($config, $progress) {
        ob_start();
        ?>
        <div class="assessment-container single-page-format" role="application" aria-label="Assessment Interface">
            <!-- Question Navigation Panel -->
            <div class="question-navigation-panel sticky" role="navigation" aria-label="Question Navigation">
                <?php $this->render_question_navigation($config, $progress); ?>
            </div>

            <!-- All Questions Display -->
            <div class="assessment-main question-display-area" role="region" aria-label="All Questions">
                <?php if ($config['sections_enabled'] && !empty($config['sections'])): ?>
                    <?php foreach ($config['sections'] as $section): ?>
                        <?php if (isset($config['questions'][$section['id']])): ?>
                            <div class="section-container" data-section="<?php echo esc_attr($section['id']); ?>">
                                <h2 class="section-title"><?php echo esc_html($section['name']); ?></h2>
                                <?php if (!empty($section['description'])): ?>
                                    <p class="section-description"><?php echo esc_html($section['description']); ?></p>
                                <?php endif; ?>

                                <div class="section-questions">
                                    <?php foreach ($config['questions'][$section['id']] as $question_index => $question): ?>
                                        <?php $this->render_question_card($question, $question_index, $progress, $section['id']); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Non-sectioned questions -->
                    <div class="section-container" data-section="default">
                        <div class="section-questions">
                            <?php foreach ($config['questions']['default'] as $question_index => $question): ?>
                                <?php $this->render_question_card($question, $question_index, $progress); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="submit-section">
                    <button type="button" class="btn-submit" onclick="submitAssessment()">
                        <?php _e('Submit Assessment', 'mcqhome'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render question navigation panel with enhanced section-aware functionality
     */
    private function render_question_navigation($config, $progress) {
        // The navigation panel will be rendered by JavaScript
        // This provides the container and initial data
        ?>
        <div class="navigation-panel-container" 
             data-config="<?php echo esc_attr(json_encode($config)); ?>"
             data-progress="<?php echo esc_attr(json_encode($progress)); ?>">
            
            <!-- Initial server-side render for SEO and no-JS fallback -->
            <div class="navigation-header">
                <h3><?php _e('Questions', 'mcqhome'); ?></h3>
                <div class="progress-summary">
                    <span class="attempted"><?php echo count($progress['answers']); ?> <?php _e('Attempted', 'mcqhome'); ?></span>
                    <span class="remaining"><?php echo $config['total_questions'] - count($progress['answers']); ?> <?php _e('Remaining', 'mcqhome'); ?></span>
                </div>
            </div>

            <div class="question-navigation-content">
                <?php if ($config['sections_enabled'] && !empty($config['sections'])): ?>
                    <!-- Sectioned Navigation -->
                    <?php foreach ($config['sections'] as $section): ?>
                        <?php if (isset($config['questions'][$section['id']])): ?>
                            <div class="section-nav" data-section="<?php echo esc_attr($section['id']); ?>">
                                <h4 class="section-nav-title"><?php echo esc_html($section['name']); ?></h4>
                                <?php if (!empty($section['description'])): ?>
                                    <p class="section-nav-description"><?php echo esc_html($section['description']); ?></p>
                                <?php endif; ?>
                                <div class="question-numbers">
                                    <?php 
                                    $section_questions = $config['questions'][$section['id']];
                                    $global_index = $this->get_section_start_index($config['questions'], $section['id']);
                                    ?>
                                    <?php foreach ($section_questions as $local_index => $question): ?>
                                        <?php 
                                        $question_number = $global_index + $local_index;
                                        $status = $this->get_question_status($question_number, $progress);
                                        $is_current = ($question_number === $progress['current_question']);
                                        ?>
                                        <button class="question-number <?php echo $status; ?> <?php echo $is_current ? 'current' : ''; ?>" 
                                                data-question="<?php echo $question_number; ?>"
                                                data-section="<?php echo esc_attr($section['id']); ?>"
                                                title="<?php echo esc_attr($this->get_question_tooltip($question_number, $status)); ?>"
                                                aria-label="<?php printf(__('Question %d of %d', 'mcqhome'), $question_number + 1, $config['total_questions']); ?>"
                                                <?php if ($is_current): ?>aria-current="true"<?php endif; ?>>
                                            <?php echo $question_number + 1; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Non-sectioned Navigation -->
                    <div class="question-numbers simple-grid">
                        <?php for ($i = 0; $i < $config['total_questions']; $i++): ?>
                            <?php 
                            $status = $this->get_question_status($i, $progress);
                            $is_current = ($i === $progress['current_question']);
                            ?>
                            <button class="question-number <?php echo $status; ?> <?php echo $is_current ? 'current' : ''; ?>" 
                                    data-question="<?php echo $i; ?>"
                                    title="<?php echo esc_attr($this->get_question_tooltip($i, $status)); ?>"
                                    aria-label="<?php printf(__('Question %d of %d', 'mcqhome'), $i + 1, $config['total_questions']); ?>"
                                    <?php if ($is_current): ?>aria-current="true"<?php endif; ?>>
                                <?php echo $i + 1; ?>
                            </button>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="navigation-legend">
                <div class="legend-item">
                    <div class="legend-color current"></div>
                    <span><?php _e('Current', 'mcqhome'); ?></span>
                </div>
                <div class="legend-item">
                    <div class="legend-color attempted"></div>
                    <span><?php _e('Answered', 'mcqhome'); ?></span>
                </div>
                <?php if ($this->get_skipped_count($progress) > 0): ?>
                <div class="legend-item">
                    <div class="legend-color skipped"></div>
                    <span><?php _e('Skipped', 'mcqhome'); ?></span>
                </div>
                <?php endif; ?>
                <div class="legend-item">
                    <div class="legend-color unanswered"></div>
                    <span><?php _e('Not Answered', 'mcqhome'); ?></span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get section start index for global question numbering
     */
    private function get_section_start_index($questions, $target_section_id) {
        $index = 0;
        foreach ($questions as $section_id => $section_questions) {
            if ($section_id === $target_section_id) {
                break;
            }
            $index += count($section_questions);
        }
        return $index;
    }

    /**
     * Get question status for navigation
     */
    private function get_question_status($question_number, $progress) {
        if (isset($progress['answers'][$question_number])) {
            return 'attempted';
        } else if (isset($progress['skipped']) && in_array($question_number, $progress['skipped'])) {
            return 'skipped';
        }
        return 'unanswered';
    }

    /**
     * Get question tooltip text
     */
    private function get_question_tooltip($question_number, $status) {
        $question_num = $question_number + 1;
        
        switch ($status) {
            case 'attempted':
                return sprintf(__('Question %d - Answered', 'mcqhome'), $question_num);
            case 'skipped':
                return sprintf(__('Question %d - Skipped', 'mcqhome'), $question_num);
            case 'current':
                return sprintf(__('Question %d - Current', 'mcqhome'), $question_num);
            default:
                return sprintf(__('Question %d - Not answered', 'mcqhome'), $question_num);
        }
    }

    /**
     * Get skipped questions count
     */
    private function get_skipped_count($progress) {
        return isset($progress['skipped']) ? count($progress['skipped']) : 0;
    }

    /**
     * Render current question for Next-Next format
     */
    public function render_current_question($config, $progress) {
        $current_index = $progress['current_question'];
        $question = $this->get_question_by_global_index($config['questions'], $current_index);
        
        if (!$question) {
            echo '<p>' . __('Question not found.', 'mcqhome') . '</p>';
            return;
        }

        $selected_answer = isset($progress['answers'][$current_index]) ? $progress['answers'][$current_index] : '';
        
        ?>
        <div class="question-slide" data-question="<?php echo $current_index; ?>">
            <div class="question-header">
                <div class="question-number">
                    <?php printf(__('Question %d of %d', 'mcqhome'), $current_index + 1, $config['total_questions']); ?>
                </div>
                <div class="question-marks">
                    <?php printf(__('Marks: %s', 'mcqhome'), $question['marks']); ?>
                </div>
            </div>
            
            <div class="question-content">
                <div class="question-text">
                    <?php echo wp_kses_post($question['question']); ?>
                </div>
            </div>
            
            <div class="answer-options">
                <?php 
                $options = ['A' => $question['option_a'], 'B' => $question['option_b'], 'C' => $question['option_c'], 'D' => $question['option_d']];
                foreach ($options as $key => $option_text): 
                    if (empty($option_text)) continue;
                ?>
                <label class="option-label <?php echo $selected_answer === $key ? 'selected' : ''; ?>">
                    <input type="radio" 
                           name="question_<?php echo $current_index; ?>" 
                           value="<?php echo $key; ?>" 
                           <?php checked($selected_answer, $key); ?>
                           onchange="saveAnswer(<?php echo $current_index; ?>, '<?php echo $key; ?>')">
                    <div class="option-content">
                        <span class="option-letter"><?php echo $key; ?>.</span>
                        <span class="option-text"><?php echo esc_html($option_text); ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render question card for Single Page format
     */
    private function render_question_card($question, $question_index, $progress, $section_id = 'default') {
        $global_index = $this->get_global_question_index($question_index, $section_id);
        $selected_answer = isset($progress['answers'][$global_index]) ? $progress['answers'][$global_index] : '';
        
        ?>
        <div class="question-card" 
             data-question="<?php echo $global_index; ?>" 
             data-question-number="<?php echo $global_index; ?>"
             role="group" 
             aria-labelledby="question-<?php echo $global_index; ?>-title">
            <div class="question-header">
                <div class="question-number" id="question-<?php echo $global_index; ?>-title">
                    <?php printf(__('Question %d', 'mcqhome'), $global_index + 1); ?>
                </div>
                <div class="question-marks" aria-label="<?php printf(__('Worth %s marks', 'mcqhome'), $question['marks']); ?>">
                    <?php printf(__('Marks: %s', 'mcqhome'), $question['marks']); ?>
                </div>
            </div>
            
            <div class="question-content">
                <div class="question-text" id="question-<?php echo $global_index; ?>-text" role="heading" aria-level="3">
                    <?php echo wp_kses_post($question['question']); ?>
                </div>
            </div>
            
            <div class="answer-options" 
                 role="radiogroup" 
                 aria-labelledby="question-<?php echo $global_index; ?>-text"
                 aria-describedby="question-<?php echo $global_index; ?>-instructions">
                <div id="question-<?php echo $global_index; ?>-instructions" class="sr-only">
                    Select one answer from the options below. Use Alt+1, Alt+2, Alt+3, or Alt+4 to select options A, B, C, or D respectively.
                </div>
                <?php 
                $options = ['A' => $question['option_a'], 'B' => $question['option_b'], 'C' => $question['option_c'], 'D' => $question['option_d']];
                foreach ($options as $key => $option_text): 
                    if (empty($option_text)) continue;
                    $option_id = "question-{$global_index}-option-{$key}";
                ?>
                <label class="option-label <?php echo $selected_answer === $key ? 'selected' : ''; ?>" 
                       for="<?php echo $option_id; ?>"
                       aria-label="Option <?php echo $key; ?>: <?php echo esc_attr(wp_strip_all_tags($option_text)); ?>">
                    <input type="radio" 
                           id="<?php echo $option_id; ?>"
                           name="question_<?php echo $global_index; ?>" 
                           value="<?php echo $key; ?>" 
                           <?php checked($selected_answer, $key); ?>
                           onchange="saveAnswer(<?php echo $global_index; ?>, '<?php echo $key; ?>')"
                           aria-describedby="<?php echo $option_id; ?>-text">
                    <div class="option-content">
                        <span class="option-letter" aria-hidden="true"><?php echo $key; ?>.</span>
                        <span class="option-text" id="<?php echo $option_id; ?>-text"><?php echo wp_kses_post($option_text); ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get question by global index
     */
    private function get_question_by_global_index($questions, $global_index) {
        $current_index = 0;
        foreach ($questions as $section_questions) {
            foreach ($section_questions as $question) {
                if ($current_index === $global_index) {
                    return $question;
                }
                $current_index++;
            }
        }
        return null;
    }

    /**
     * Get global question index from section and local index
     */
    private function get_global_question_index($local_index, $section_id) {
        // This would need to be calculated based on the section order
        // For now, return the local index (works for single section)
        return $local_index;
    }

    /**
     * Validate assessment session
     */
    public function validate_session($mcq_set_id, $user_id) {
        // Check time limits
        $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
        if ($time_limit && function_exists('mcqhome_calculate_time_remaining')) {
            $time_remaining = mcqhome_calculate_time_remaining($user_id, $mcq_set_id);
            if ($time_remaining <= 0) {
                return new WP_Error('time_expired', __('Assessment time limit has expired.', 'mcqhome'));
            }
        }

        return true;
    }

    /**
     * Handle assessment submission
     */
    public function submit_assessment($mcq_set_id, $user_id, $answers, $time_taken = 0) {
        // Validate submission
        if (function_exists('mcqhome_validate_assessment_submission')) {
            $validation = mcqhome_validate_assessment_submission($user_id, $mcq_set_id, $answers);
            if (is_wp_error($validation)) {
                return $validation;
            }
        }

        // Save assessment attempt
        if (function_exists('mcqhome_save_assessment_attempt')) {
            $result = mcqhome_save_assessment_attempt($user_id, $mcq_set_id, $answers, $time_taken);
            if (is_wp_error($result)) {
                return $result;
            }

            // Log assessment activity
            if (function_exists('mcqhome_log_assessment_activity')) {
                mcqhome_log_assessment_activity($user_id, $mcq_set_id, 'assessment_submitted', [
                    'total_score' => $result['total_score'],
                    'score_percentage' => $result['score_percentage'],
                    'is_passed' => $result['is_passed'],
                    'time_taken' => $time_taken
                ]);
            }

            return $result;
        }

        return new WP_Error('save_failed', __('Failed to save assessment.', 'mcqhome'));
    }
}

    /**
     * AJAX handler for saving progress (legacy - use secure version)
     */
    public function ajax_save_progress() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mcqhome')]);
        }

        $mcq_set_id = intval($_POST['mcq_set_id']);
        $user_id = get_current_user_id();
        
        // Use the secure progress saving method
        $security_manager = mcqhome_get_assessment_security();
        
        $progress_data = [
            'current_question' => intval($_POST['current_question']),
            'answers_data' => $_POST['answers'] ?? [],
            'skipped_questions' => $_POST['skipped'] ?? [],
            'progress_percentage' => floatval($_POST['progress_percentage'])
        ];

        $result = $security_manager->save_secure_progress($user_id, $mcq_set_id, $progress_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'message' => __('Progress saved successfully.', 'mcqhome'),
            'progress_percentage' => $progress_data['progress_percentage'],
            'timestamp' => current_time('timestamp')
        ]);
    }

    /**
     * AJAX handler for question navigation
     */
    public function ajax_navigate_to_question() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_die(__('Security check failed.', 'mcqhome'));
        }

        $mcq_set_id = intval($_POST['set_id']);
        $question_number = intval($_POST['question_number']);
        $user_id = get_current_user_id();

        // Validate session
        $validation = $this->validate_session($mcq_set_id, $user_id);
        if (is_wp_error($validation)) {
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }

        // Update current question in progress
        if (function_exists('mcqhome_update_current_question')) {
            try {
                mcqhome_update_current_question($user_id, $mcq_set_id, $question_number);
                wp_send_json_success([
                    'message' => __('Navigation successful.', 'mcqhome'),
                    'current_question' => $question_number
                ]);
            } catch (Exception $e) {
                error_log('MCQHome: Navigation failed - ' . $e->getMessage());
                wp_send_json_error(['message' => __('Navigation failed.', 'mcqhome')]);
            }
        } else {
            wp_send_json_error(['message' => __('Navigation system not available.', 'mcqhome')]);
        }tion)) {
            wp_send_json_error($validation->get_error_message());
        }

        // Get assessment config and progress
        $config = $this->get_assessment_config($mcq_set_id);
        $progress = $this->get_current_progress($mcq_set_id, $user_id);

        if (is_wp_error($config) || is_wp_error($progress)) {
            wp_send_json_error(__('Failed to load assessment data.', 'mcqhome'));
        }

        // Update current question in progress
        $progress['current_question'] = $question_number;
        $this->update_progress($mcq_set_id, $user_id, $progress);

        // For Next-Next format, return the question HTML
        if ($config['display_format'] === 'next_next') {
            ob_start();
            $this->render_current_question($config, $progress);
            $question_html = ob_get_clean();

            wp_send_json_success([
                'html' => $question_html,
                'question_number' => $question_number,
                'total_questions' => $config['total_questions']
            ]);
        } else {
            // For single page format, just confirm navigation
            wp_send_json_success([
                'question_number' => $question_number,
                'scroll_target' => "question-{$question_number}"
            ]);
        }
    }

    /**
     * AJAX handler for updating question status
     */
    public function ajax_update_question_status() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_die(__('Security check failed.', 'mcqhome'));
        }

        $mcq_set_id = intval($_POST['set_id']);
        $question_number = intval($_POST['question_number']);
        $status = sanitize_text_field($_POST['status']);
        $answer = isset($_POST['answer']) ? sanitize_text_field($_POST['answer']) : null;
        $user_id = get_current_user_id();

        // Get current progress
        $progress = $this->get_current_progress($mcq_set_id, $user_id);
        if (is_wp_error($progress)) {
            wp_send_json_error(__('Failed to load progress.', 'mcqhome'));
        }

        // Update progress based on status
        switch ($status) {
            case 'attempted':
                if ($answer) {
                    $progress['answers'][$question_number] = $answer;
                    // Remove from skipped if it was skipped before
                    if (isset($progress['skipped'])) {
                        $progress['skipped'] = array_diff($progress['skipped'], [$question_number]);
                    }
                }
                break;
                
            case 'skipped':
                if (!isset($progress['skipped'])) {
                    $progress['skipped'] = [];
                }
                if (!in_array($question_number, $progress['skipped'])) {
                    $progress['skipped'][] = $question_number;
                }
                // Remove answer if it exists
                if (isset($progress['answers'][$question_number])) {
                    unset($progress['answers'][$question_number]);
                }
                break;
        }

        // Save updated progress
        $this->update_progress($mcq_set_id, $user_id, $progress);

        wp_send_json_success([
            'status' => $status,
            'question_number' => $question_number,
            'attempted_count' => count($progress['answers']),
            'skipped_count' => isset($progress['skipped']) ? count($progress['skipped']) : 0,
            'remaining_count' => $this->count_total_questions($this->get_assessment_config($mcq_set_id)['questions']) - count($progress['answers'])
        ]);
    }

    /**
     * Get current progress for user
     */
    private function get_current_progress($mcq_set_id, $user_id) {
        if (function_exists('mcqhome_get_user_progress')) {
            try {
                $existing_progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
                if ($existing_progress) {
                    return [
                        'current_question' => $existing_progress->current_question,
                        'answers' => maybe_unserialize($existing_progress->answers_data) ?: [],
                        'skipped' => maybe_unserialize($existing_progress->skipped_questions) ?: [],
                        'start_time' => strtotime($existing_progress->created_at)
                    ];
                }
            } catch (Exception $e) {
                error_log('MCQHome: Progress retrieval failed - ' . $e->getMessage());
            }
        }

        return [
            'current_question' => 0,
            'answers' => [],
            'skipped' => [],
            'start_time' => time()
        ];
    }

    /**
     * Update progress for user
     */
    private function update_progress($mcq_set_id, $user_id, $progress) {
        if (function_exists('mcqhome_update_user_progress')) {
            try {
                $progress_data = [
                    'current_question' => $progress['current_question'],
                    'answers_data' => serialize($progress['answers']),
                    'skipped_questions' => serialize($progress['skipped']),
                    'progress_percentage' => $this->calculate_progress_percentage($progress)
                ];
                
                mcqhome_update_user_progress($user_id, $mcq_set_id, $progress_data);
            } catch (Exception $e) {
                error_log('MCQHome: Progress update failed - ' . $e->getMessage());
            }
        }
    }

    /**
     * Calculate progress percentage
     */
    private function calculate_progress_percentage($progress) {
        $total_questions = $this->count_total_questions($this->get_assessment_config($mcq_set_id)['questions']);
        $answered_questions = count($progress['answers']);
        return $total_questions > 0 ? round(($answered_questions / $total_questions) * 100, 2) : 0;
    }

    /**
     * AJAX handler for secure assessment submission
     */
    public function ajax_submit_assessment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mcqhome')]);
        }

        $mcq_set_id = intval($_POST['mcq_set_id']);
        $user_id = get_current_user_id();
        $answers = $_POST['answers'] ?? [];
        
        // Use the secure submission method
        $security_manager = mcqhome_get_assessment_security();
        
        $submission_data = [
            'submission_method' => 'manual',
            'client_time' => $_POST['client_time'] ?? '',
            'time_taken' => intval($_POST['time_taken'] ?? 0),
            'security_data' => $_POST['security_data'] ?? []
        ];

        $result = $security_manager->submit_assessment($user_id, $mcq_set_id, $answers, $submission_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        // Generate redirect URL
        $results_page = get_page_by_path('assessment-results');
        $redirect_url = $results_page ? 
            add_query_arg(['attempt_id' => $result['attempt_id']], get_permalink($results_page)) :
            home_url('/dashboard/');

        wp_send_json_success([
            'message' => __('Assessment submitted successfully.', 'mcqhome'),
            'result' => $result,
            'redirect_url' => $redirect_url
        ]);
    }
}

// Initialize the assessment controller
global $mcqhome_assessment_controller;
$mcqhome_assessment_controller = new MCQHome_Assessment_Controller();

/**
 * Helper function to get assessment controller instance
 */
function mcqhome_get_assessment_controller() {
    global $mcqhome_assessment_controller;
    return $mcqhome_assessment_controller;
}

// Register AJAX handlers
add_action('wp_ajax_mcqhome_navigate_to_question', array($mcqhome_assessment_controller, 'ajax_navigate_to_question'));
add_action('wp_ajax_mcqhome_update_question_status', array($mcqhome_assessment_controller, 'ajax_update_question_status'));
add_action('wp_ajax_mcqhome_save_progress', array($mcqhome_assessment_controller, 'ajax_save_progress'));
add_action('wp_ajax_mcqhome_submit_assessment', array($mcqhome_assessment_controller, 'ajax_submit_assessment'));