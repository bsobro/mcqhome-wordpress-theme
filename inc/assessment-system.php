<?php
/**
 * Assessment System for MCQHome Theme
 * 
 * Handles MCQ assessment delivery, answer processing, and scoring
 * 
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle MCQ Set assessment requests
 */
function mcqhome_handle_assessment_request() {
    if (!isset($_GET['action']) || !in_array($_GET['action'], ['start', 'continue', 'submit'])) {
        return;
    }
    
    global $post;
    if (!$post || $post->post_type !== 'mcq_set') {
        return;
    }
    
    $action = sanitize_text_field($_GET['action']);
    $mcq_set_id = $post->ID;
    
    switch ($action) {
        case 'start':
            mcqhome_start_assessment($mcq_set_id);
            break;
        case 'continue':
            mcqhome_continue_assessment($mcq_set_id);
            break;
        case 'submit':
            mcqhome_submit_assessment($mcq_set_id);
            break;
    }
}
add_action('template_redirect', 'mcqhome_handle_assessment_request');

/**
 * Start a new assessment
 */
function mcqhome_start_assessment($mcq_set_id) {
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url(get_permalink($mcq_set_id) . '?action=start'));
        exit;
    }
    
    $questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    if (!is_array($questions) || empty($questions)) {
        wp_die(__('This MCQ set has no questions available.', 'mcqhome'));
    }
    
    // Create new attempt record
    global $wpdb;
    $user_id = get_current_user_id();
    
    $wpdb->insert(
        $wpdb->prefix . 'mcq_attempts',
        array(
            'user_id' => $user_id,
            'mcq_set_id' => $mcq_set_id,
            'status' => 'in_progress',
            'started_at' => current_time('mysql'),
            'answers' => json_encode(array())
        ),
        array('%d', '%d', '%s', '%s', '%s')
    );
    
    $attempt_id = $wpdb->insert_id;
    
    // Debug logging
    error_log('MCQHome: Created attempt ID: ' . $attempt_id . ' for user: ' . $user_id . ' on MCQ set: ' . $mcq_set_id);
    
    if (!$attempt_id) {
        error_log('MCQHome: Failed to create attempt. DB Error: ' . $wpdb->last_error);
        wp_die(__('Failed to start assessment. Please try again.', 'mcqhome'));
    }
    
    // Redirect to assessment interface
    wp_redirect(add_query_arg(array('action' => 'take', 'attempt' => $attempt_id), get_permalink($mcq_set_id)));
    exit;
}

/**
 * Continue an existing assessment
 */
function mcqhome_continue_assessment($mcq_set_id) {
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url(get_permalink($mcq_set_id) . '?action=continue'));
        exit;
    }
    
    global $wpdb;
    $user_id = get_current_user_id();
    
    // Find existing in-progress attempt
    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_attempts 
         WHERE user_id = %d AND mcq_set_id = %d AND status = 'in_progress' 
         ORDER BY started_at DESC LIMIT 1",
        $user_id, $mcq_set_id
    ));
    
    if (!$attempt) {
        // No existing attempt, start new one
        mcqhome_start_assessment($mcq_set_id);
        return;
    }
    
    // Redirect to assessment interface
    wp_redirect(add_query_arg(array('action' => 'take', 'attempt' => $attempt->id), get_permalink($mcq_set_id)));
    exit;
}

/**
 * Display assessment interface
 */
function mcqhome_display_assessment_interface($mcq_set_id, $attempt_id) {
    global $wpdb;
    
    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_attempts WHERE id = %d",
        $attempt_id
    ));
    
    // Debug logging
    error_log('MCQHome: Looking for attempt ID: ' . $attempt_id . ' for user: ' . get_current_user_id());
    if (!$attempt) {
        error_log('MCQHome: No attempt found with ID: ' . $attempt_id);
        wp_die(__('Assessment attempt not found. Please start a new assessment.', 'mcqhome'));
    }
    
    if ($attempt->user_id != get_current_user_id()) {
        error_log('MCQHome: Attempt user mismatch. Attempt user: ' . $attempt->user_id . ', Current user: ' . get_current_user_id());
        wp_die(__('This assessment attempt belongs to another user.', 'mcqhome'));
    }
    
    $questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    $settings = get_post_meta($mcq_set_id, '_mcq_set_settings', true);
    $answers = json_decode($attempt->answers, true) ?: array();
    
    $settings = wp_parse_args($settings, array(
        'time_limit' => 0,
        'display_format' => 'single_page'
    ));
    
    // Load assessment template
    get_header();
    
    echo '<div class="assessment-container container mx-auto px-4 py-8">';
    echo '<div class="assessment-header mb-6">';
    echo '<h1 class="text-2xl font-bold">' . get_the_title($mcq_set_id) . '</h1>';
    echo '<div class="assessment-progress">';
    echo '<span class="text-sm text-gray-600">' . sprintf(__('Total Questions: %d', 'mcqhome'), count($questions)) . '</span>';
    echo '</div>';
    echo '</div>';
    
    echo '<form id="assessment-form" method="post" action="' . add_query_arg('action', 'submit') . '">';
    wp_nonce_field('mcqhome_submit_assessment', 'assessment_nonce');
    echo '<input type="hidden" name="attempt_id" value="' . $attempt_id . '">';
    
    if ($settings['display_format'] === 'single_page') {
        mcqhome_render_single_page_format($questions, $answers);
    } else {
        mcqhome_render_next_next_format($questions, $answers);
    }
    
    echo '<div class="assessment-actions mt-8">';
    echo '<button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">';
    echo __('Submit Assessment', 'mcqhome');
    echo '</button>';
    echo '</div>';
    
    echo '</form>';
    echo '</div>';
    
    get_footer();
    exit;
}

/**
 * Render single page format
 */
function mcqhome_render_single_page_format($questions, $answers) {
    echo '<div class="questions-container space-y-8">';
    
    foreach ($questions as $index => $question) {
        $question_num = $index + 1;
        $selected_answer = isset($answers[$index]) ? $answers[$index] : '';
        
        echo '<div class="question-item border rounded-lg p-6">';
        echo '<div class="question-header mb-4">';
        echo '<h3 class="text-lg font-semibold">Question ' . $question_num . '</h3>';
        echo '</div>';
        
        echo '<div class="question-text mb-4">';
        echo '<p>' . wp_kses_post($question['question_text']) . '</p>';
        echo '</div>';
        
        echo '<div class="question-options space-y-2">';
        $options = array('A', 'B', 'C', 'D');
        foreach ($options as $option) {
            $option_key = 'option_' . strtolower($option);
            $option_text = $question[$option_key];
            $checked = ($selected_answer === $option) ? 'checked' : '';
            
            echo '<label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">';
            echo '<input type="radio" name="answers[' . $index . ']" value="' . $option . '" ' . $checked . ' class="mr-3">';
            echo '<span class="font-medium mr-2">' . $option . '.</span>';
            echo '<span>' . esc_html($option_text) . '</span>';
            echo '</label>';
        }
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}

/**
 * Render next-next format (simplified for now)
 */
function mcqhome_render_next_next_format($questions, $answers) {
    // For now, render as single page - can be enhanced later
    mcqhome_render_single_page_format($questions, $answers);
}

/**
 * Submit assessment
 */
function mcqhome_submit_assessment($mcq_set_id) {
    if (!isset($_POST['assessment_nonce']) || !wp_verify_nonce($_POST['assessment_nonce'], 'mcqhome_submit_assessment')) {
        wp_die(__('Security check failed.', 'mcqhome'));
    }
    
    if (!isset($_POST['attempt_id']) || !isset($_POST['answers'])) {
        wp_die(__('Invalid submission.', 'mcqhome'));
    }
    
    $attempt_id = intval($_POST['attempt_id']);
    $submitted_answers = $_POST['answers'];
    
    global $wpdb;
    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_attempts WHERE id = %d AND user_id = %d",
        $attempt_id, get_current_user_id()
    ));
    
    if (!$attempt) {
        wp_die(__('Invalid attempt.', 'mcqhome'));
    }
    
    // Calculate score
    $questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    $settings = get_post_meta($mcq_set_id, '_mcq_set_settings', true);
    $settings = wp_parse_args($settings, array(
        'negative_marking' => 0,
        'passing_marks' => 70
    ));
    
    $total_marks = 0;
    $earned_marks = 0;
    $correct_answers = 0;
    
    foreach ($questions as $index => $question) {
        $question_marks = isset($question['marks']) ? floatval($question['marks']) : 1;
        $total_marks += $question_marks;
        
        $submitted_answer = isset($submitted_answers[$index]) ? $submitted_answers[$index] : '';
        $correct_answer = $question['correct_answer'];
        
        if ($submitted_answer === $correct_answer) {
            $earned_marks += $question_marks;
            $correct_answers++;
        } elseif (!empty($submitted_answer) && $settings['negative_marking'] > 0) {
            $earned_marks -= $settings['negative_marking'];
        }
    }
    
    $score_percentage = $total_marks > 0 ? ($earned_marks / $total_marks) * 100 : 0;
    $score_percentage = max(0, $score_percentage); // Ensure non-negative
    
    // Update attempt record
    $wpdb->update(
        $wpdb->prefix . 'mcq_attempts',
        array(
            'answers' => json_encode($submitted_answers),
            'score_earned' => $earned_marks,
            'score_percentage' => $score_percentage,
            'status' => 'completed',
            'completed_at' => current_time('mysql')
        ),
        array('id' => $attempt_id),
        array('%s', '%f', '%f', '%s', '%s'),
        array('%d')
    );
    
    // Redirect to results page
    wp_redirect(add_query_arg(array('action' => 'results', 'attempt' => $attempt_id), get_permalink($mcq_set_id)));
    exit;
}

/**
 * Display assessment results
 */
function mcqhome_display_assessment_results($mcq_set_id, $attempt_id) {
    global $wpdb;
    
    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_attempts WHERE id = %d AND user_id = %d",
        $attempt_id, get_current_user_id()
    ));
    
    if (!$attempt || $attempt->status !== 'completed') {
        wp_die(__('Invalid or incomplete assessment.', 'mcqhome'));
    }
    
    $questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    $settings = get_post_meta($mcq_set_id, '_mcq_set_settings', true);
    $answers = json_decode($attempt->answers, true);
    
    $settings = wp_parse_args($settings, array('passing_marks' => 70));
    $passed = $attempt->score_percentage >= $settings['passing_marks'];
    
    get_header();
    
    echo '<div class="results-container container mx-auto px-4 py-8">';
    echo '<div class="results-header text-center mb-8">';
    echo '<h1 class="text-3xl font-bold mb-4">' . __('Assessment Results', 'mcqhome') . '</h1>';
    echo '<div class="score-display">';
    echo '<div class="text-6xl font-bold mb-2 ' . ($passed ? 'text-green-600' : 'text-red-600') . '">';
    echo round($attempt->score_percentage, 1) . '%';
    echo '</div>';
    echo '<div class="text-xl ' . ($passed ? 'text-green-600' : 'text-red-600') . '">';
    echo $passed ? __('PASSED', 'mcqhome') : __('FAILED', 'mcqhome');
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="results-summary bg-gray-50 rounded-lg p-6 mb-8">';
    echo '<h3 class="text-lg font-semibold mb-4">' . __('Summary', 'mcqhome') . '</h3>';
    echo '<div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">';
    echo '<div><span class="block text-2xl font-bold">' . count($questions) . '</span><span class="text-sm text-gray-600">' . __('Total Questions', 'mcqhome') . '</span></div>';
    
    $correct_count = 0;
    foreach ($questions as $index => $question) {
        if (isset($answers[$index]) && $answers[$index] === $question['correct_answer']) {
            $correct_count++;
        }
    }
    
    echo '<div><span class="block text-2xl font-bold text-green-600">' . $correct_count . '</span><span class="text-sm text-gray-600">' . __('Correct', 'mcqhome') . '</span></div>';
    echo '<div><span class="block text-2xl font-bold">' . round($attempt->score_earned, 1) . '</span><span class="text-sm text-gray-600">' . __('Marks Earned', 'mcqhome') . '</span></div>';
    echo '<div><span class="block text-2xl font-bold">' . $settings['passing_marks'] . '%</span><span class="text-sm text-gray-600">' . __('Passing Marks', 'mcqhome') . '</span></div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="results-actions text-center">';
    echo '<a href="' . get_permalink($mcq_set_id) . '" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 mr-4">' . __('Back to MCQ Set', 'mcqhome') . '</a>';
    echo '<a href="' . home_url('/mcq-set/') . '" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">' . __('Browse More MCQs', 'mcqhome') . '</a>';
    echo '</div>';
    
    echo '</div>';
    
    get_footer();
    exit;
}

/**
 * Handle assessment interface routing
 */
function mcqhome_assessment_template_redirect() {
    if (!is_singular('mcq_set')) {
        return;
    }
    
    if (isset($_GET['action'])) {
        $action = sanitize_text_field($_GET['action']);
        $mcq_set_id = get_the_ID();
        
        if ($action === 'take' && isset($_GET['attempt'])) {
            $attempt_id = intval($_GET['attempt']);
            mcqhome_display_assessment_interface($mcq_set_id, $attempt_id);
        } elseif ($action === 'results' && isset($_GET['attempt'])) {
            $attempt_id = intval($_GET['attempt']);
            mcqhome_display_assessment_results($mcq_set_id, $attempt_id);
        } elseif ($action === 'submit') {
            mcqhome_submit_assessment($mcq_set_id);
        }
    }
}
add_action('template_redirect', 'mcqhome_assessment_template_redirect', 5);

/**
 * Add assessment styles
 */
function mcqhome_assessment_styles() {
    if (is_singular('mcq_set') && isset($_GET['action'])) {
        echo '<style>
        .assessment-container { max-width: 800px; }
        .question-item { transition: all 0.2s; }
        .question-item:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .question-options label:hover { background-color: #f9fafb; }
        .question-options input[type="radio"]:checked + span { color: #2563eb; }
        .results-container { max-width: 600px; }
        .score-display { padding: 2rem; background: white; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        </style>';
    }
}
add_action('wp_head', 'mcqhome_assessment_styles');