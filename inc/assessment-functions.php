<?php
/**
 * Assessment Helper Functions for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get user's assessment attempts for a specific MCQ set
 */
function mcqhome_get_user_attempts($user_id, $mcq_set_id = null) {
    global $wpdb;
    
    $sql = "SELECT * FROM {$wpdb->prefix}mcq_set_attempts WHERE user_id = %d";
    $params = [$user_id];
    
    if ($mcq_set_id) {
        $sql .= " AND mcq_set_id = %d";
        $params[] = $mcq_set_id;
    }
    
    $sql .= " ORDER BY completed_at DESC";
    
    return $wpdb->get_results($wpdb->prepare($sql, $params));
}

/**
 * Get user's best attempt for a specific MCQ set
 */
function mcqhome_get_user_best_attempt($user_id, $mcq_set_id) {
    global $wpdb;
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE user_id = %d AND mcq_set_id = %d AND status = 'completed'
         ORDER BY total_score DESC, completed_at ASC LIMIT 1",
        $user_id, $mcq_set_id
    ));
}

/**
 * Check if user has completed an MCQ set
 */
function mcqhome_has_user_completed_set($user_id, $mcq_set_id) {
    global $wpdb;
    
    $attempt = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE user_id = %d AND mcq_set_id = %d AND status = 'completed'",
        $user_id, $mcq_set_id
    ));
    
    return $attempt > 0;
}

/**
 * Check if user has an ongoing assessment
 */
function mcqhome_has_ongoing_assessment($user_id, $mcq_set_id) {
    global $wpdb;
    
    $progress = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_user_progress 
         WHERE user_id = %d AND mcq_set_id = %d",
        $user_id, $mcq_set_id
    ));
    
    return $progress !== null;
}

/**
 * Get assessment statistics for a user
 */
function mcqhome_get_user_assessment_stats($user_id) {
    global $wpdb;
    
    $stats = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COUNT(*) as total_attempts,
            COUNT(CASE WHEN is_passed = 1 THEN 1 END) as passed_attempts,
            AVG(score_percentage) as avg_score,
            MAX(score_percentage) as best_score,
            SUM(time_taken) as total_time
         FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE user_id = %d AND status = 'completed'",
        $user_id
    ));
    
    return $stats;
}

/**
 * Get MCQ set statistics
 */
function mcqhome_get_mcq_set_stats($mcq_set_id) {
    global $wpdb;
    
    $stats = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COUNT(DISTINCT user_id) as total_users,
            COUNT(*) as total_attempts,
            AVG(score_percentage) as avg_score,
            COUNT(CASE WHEN is_passed = 1 THEN 1 END) as passed_attempts,
            AVG(time_taken) as avg_time
         FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE mcq_set_id = %d AND status = 'completed'",
        $mcq_set_id
    ));
    
    return $stats;
}

/**
 * Get leaderboard for an MCQ set
 */
function mcqhome_get_mcq_set_leaderboard($mcq_set_id, $limit = 10) {
    global $wpdb;
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT 
            a.user_id,
            a.total_score,
            a.score_percentage,
            a.time_taken,
            a.completed_at,
            u.display_name
         FROM {$wpdb->prefix}mcq_set_attempts a
         JOIN {$wpdb->users} u ON a.user_id = u.ID
         WHERE a.mcq_set_id = %d AND a.status = 'completed'
         ORDER BY a.total_score DESC, a.time_taken ASC
         LIMIT %d",
        $mcq_set_id, $limit
    ));
}

/**
 * Calculate time remaining for an assessment
 */
function mcqhome_calculate_time_remaining($user_id, $mcq_set_id) {
    $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
    
    if (!$progress) {
        return null;
    }
    
    $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
    
    if (!$time_limit) {
        return null; // No time limit
    }
    
    $start_time = strtotime($progress->created_at);
    $current_time = time();
    $elapsed_time = $current_time - $start_time;
    $time_limit_seconds = $time_limit * 60;
    
    $remaining = $time_limit_seconds - $elapsed_time;
    
    return max(0, $remaining);
}

/**
 * Format assessment duration
 */
function mcqhome_format_duration($seconds) {
    if ($seconds < 60) {
        return sprintf(__('%d seconds', 'mcqhome'), $seconds);
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        $remaining_seconds = $seconds % 60;
        if ($remaining_seconds > 0) {
            return sprintf(__('%d minutes %d seconds', 'mcqhome'), $minutes, $remaining_seconds);
        } else {
            return sprintf(__('%d minutes', 'mcqhome'), $minutes);
        }
    } else {
        $hours = floor($seconds / 3600);
        $remaining_minutes = floor(($seconds % 3600) / 60);
        if ($remaining_minutes > 0) {
            return sprintf(__('%d hours %d minutes', 'mcqhome'), $hours, $remaining_minutes);
        } else {
            return sprintf(__('%d hours', 'mcqhome'), $hours);
        }
    }
}

/**
 * Get assessment difficulty level
 */
function mcqhome_get_assessment_difficulty($mcq_set_id) {
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    
    if (empty($mcq_ids)) {
        return null;
    }
    
    $difficulty_counts = ['easy' => 0, 'medium' => 0, 'hard' => 0];
    
    foreach ($mcq_ids as $mcq_id) {
        $difficulties = wp_get_post_terms($mcq_id, 'mcq_difficulty', ['fields' => 'slugs']);
        
        if (!empty($difficulties)) {
            foreach ($difficulties as $difficulty) {
                if (isset($difficulty_counts[$difficulty])) {
                    $difficulty_counts[$difficulty]++;
                }
            }
        }
    }
    
    // Determine overall difficulty based on majority
    $max_count = max($difficulty_counts);
    $dominant_difficulty = array_search($max_count, $difficulty_counts);
    
    return $dominant_difficulty ?: 'medium';
}

/**
 * Generate assessment certificate data
 */
function mcqhome_generate_certificate_data($user_id, $mcq_set_id, $attempt_id) {
    $user = get_user_by('ID', $user_id);
    $mcq_set = get_post($mcq_set_id);
    
    global $wpdb;
    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE id = %d AND user_id = %d AND mcq_set_id = %d",
        $attempt_id, $user_id, $mcq_set_id
    ));
    
    if (!$user || !$mcq_set || !$attempt || !$attempt->is_passed) {
        return false;
    }
    
    return [
        'user_name' => $user->display_name,
        'mcq_set_title' => $mcq_set->post_title,
        'score' => $attempt->total_score,
        'max_score' => $attempt->max_score,
        'percentage' => $attempt->score_percentage,
        'completion_date' => date('F j, Y', strtotime($attempt->completed_at)),
        'certificate_id' => 'MCQ-' . $mcq_set_id . '-' . $user_id . '-' . $attempt_id
    ];
}

/**
 * Clean up expired assessment sessions
 */
function mcqhome_cleanup_expired_sessions() {
    global $wpdb;
    
    // Remove progress data older than 24 hours with no activity
    $wpdb->query(
        "DELETE FROM {$wpdb->prefix}mcq_user_progress 
         WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
    );
    
    // Remove incomplete attempts older than 7 days
    $wpdb->query(
        "DELETE FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE status = 'in_progress' AND started_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
}

/**
 * Schedule cleanup cron job
 */
function mcqhome_schedule_assessment_cleanup() {
    if (!wp_next_scheduled('mcqhome_assessment_cleanup')) {
        wp_schedule_event(time(), 'daily', 'mcqhome_assessment_cleanup');
    }
}
add_action('init', 'mcqhome_schedule_assessment_cleanup');

/**
 * Handle cleanup cron job
 */
function mcqhome_handle_assessment_cleanup() {
    mcqhome_cleanup_expired_sessions();
}
add_action('mcqhome_assessment_cleanup', 'mcqhome_handle_assessment_cleanup');

/**
 * Validate assessment submission
 */
function mcqhome_validate_assessment_submission($user_id, $mcq_set_id, $answers) {
    // Check if user is enrolled
    $enrollment = mcqhome_check_user_enrollment($user_id, $mcq_set_id);
    if (!$enrollment) {
        return new WP_Error('not_enrolled', __('You are not enrolled in this MCQ set.', 'mcqhome'));
    }
    
    // Check if MCQ set exists and has questions
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    if (empty($mcq_ids)) {
        return new WP_Error('no_questions', __('No questions found in this MCQ set.', 'mcqhome'));
    }
    
    // Check if user has already completed this assessment
    $allow_retakes = get_post_meta($mcq_set_id, '_mcq_set_allow_retakes', true);
    if (!$allow_retakes && mcqhome_has_user_completed_set($user_id, $mcq_set_id)) {
        return new WP_Error('already_completed', __('You have already completed this assessment.', 'mcqhome'));
    }
    
    // Validate answers format
    if (!is_array($answers)) {
        return new WP_Error('invalid_answers', __('Invalid answers format.', 'mcqhome'));
    }
    
    // Validate individual answers
    foreach ($answers as $question_index => $answer) {
        if (!in_array($answer, ['A', 'B', 'C', 'D'])) {
            return new WP_Error('invalid_answer', sprintf(__('Invalid answer for question %d.', 'mcqhome'), $question_index + 1));
        }
    }
    
    return true;
}

/**
 * Get assessment navigation data
 */
function mcqhome_get_assessment_navigation($user_id, $mcq_set_id) {
    $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    
    if (!$mcq_ids) {
        return null;
    }
    
    $total_questions = count($mcq_ids);
    $current_question = $progress ? $progress->current_question : 0;
    $answers = $progress ? maybe_unserialize($progress->answers_data) : [];
    $answered_count = count($answers);
    
    return [
        'total_questions' => $total_questions,
        'current_question' => $current_question,
        'answered_count' => $answered_count,
        'progress_percentage' => ($answered_count / $total_questions) * 100,
        'answers' => $answers
    ];
}

/**
 * Start assessment session
 */
function mcqhome_start_assessment_session($user_id, $mcq_set_id) {
    // Check if there's already an active session
    $existing_progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
    
    if ($existing_progress) {
        // Resume existing session
        return [
            'session_id' => $existing_progress->id,
            'current_question' => $existing_progress->current_question,
            'answers' => maybe_unserialize($existing_progress->answers_data),
            'start_time' => strtotime($existing_progress->created_at)
        ];
    }
    
    // Create new session
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    $total_questions = count($mcq_ids);
    
    $progress_data = [
        'current_question' => 0,
        'total_questions' => $total_questions,
        'completed_questions' => [],
        'answers_data' => [],
        'progress_percentage' => 0
    ];
    
    $session_id = mcqhome_update_user_progress($user_id, $mcq_set_id, $progress_data);
    
    return [
        'session_id' => $session_id,
        'current_question' => 0,
        'answers' => [],
        'start_time' => time()
    ];
}

/**
 * Validate assessment session
 */
function mcqhome_validate_assessment_session($user_id, $mcq_set_id) {
    $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
    
    if (!$progress) {
        return new WP_Error('no_session', __('No active assessment session found.', 'mcqhome'));
    }
    
    // Check if session has expired (24 hours)
    $session_age = time() - strtotime($progress->created_at);
    if ($session_age > 24 * 3600) {
        return new WP_Error('session_expired', __('Assessment session has expired.', 'mcqhome'));
    }
    
    // Check time limit if set
    $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
    if ($time_limit) {
        $time_remaining = mcqhome_calculate_time_remaining($user_id, $mcq_set_id);
        if ($time_remaining <= 0) {
            return new WP_Error('time_expired', __('Assessment time limit has expired.', 'mcqhome'));
        }
    }
    
    return true;
}

/**
 * End assessment session
 */
function mcqhome_end_assessment_session($user_id, $mcq_set_id) {
    global $wpdb;
    
    return $wpdb->delete(
        $wpdb->prefix . 'mcq_user_progress',
        ['user_id' => $user_id, 'mcq_set_id' => $mcq_set_id],
        ['%d', '%d']
    );
}

/**
 * Prevent assessment cheating
 */
function mcqhome_log_assessment_activity($user_id, $mcq_set_id, $activity_type, $data = []) {
    global $wpdb;
    
    $activity_data = [
        'user_id' => $user_id,
        'mcq_set_id' => $mcq_set_id,
        'activity_type' => $activity_type,
        'activity_data' => maybe_serialize($data),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'timestamp' => current_time('mysql')
    ];
    
    // Create activity log table if it doesn't exist
    $table_name = $wpdb->prefix . 'mcq_activity_log';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) NOT NULL,
        activity_type varchar(50) NOT NULL,
        activity_data longtext,
        ip_address varchar(45),
        user_agent text,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY mcq_set_id (mcq_set_id),
        KEY activity_type (activity_type),
        KEY timestamp (timestamp)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    return $wpdb->insert($table_name, $activity_data);
}

/**
 * Detect suspicious assessment behavior
 */
function mcqhome_detect_suspicious_behavior($user_id, $mcq_set_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mcq_activity_log';
    
    // Check for rapid answer changes
    $rapid_changes = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name 
         WHERE user_id = %d AND mcq_set_id = %d 
         AND activity_type = 'answer_changed' 
         AND timestamp > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
        $user_id, $mcq_set_id
    ));
    
    if ($rapid_changes > 10) {
        return ['type' => 'rapid_changes', 'count' => $rapid_changes];
    }
    
    // Check for tab switching
    $tab_switches = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name 
         WHERE user_id = %d AND mcq_set_id = %d 
         AND activity_type = 'tab_switch' 
         AND timestamp > DATE_SUB(NOW(), INTERVAL 10 MINUTE)",
        $user_id, $mcq_set_id
    ));
    
    if ($tab_switches > 5) {
        return ['type' => 'tab_switching', 'count' => $tab_switches];
    }
    
    return false;
}

/**
 * Calculate assessment score with negative marking support
 */
function mcqhome_calculate_assessment_score($mcq_set_id, $answers) {
    // Get MCQ set configuration
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    $individual_marks = get_post_meta($mcq_set_id, '_mcq_set_individual_marks', true);
    $negative_marking = get_post_meta($mcq_set_id, '_mcq_set_negative_marking', true) ?: 0;
    $total_marks = get_post_meta($mcq_set_id, '_mcq_set_total_marks', true);
    $passing_marks = get_post_meta($mcq_set_id, '_mcq_set_passing_marks', true);
    
    if (empty($mcq_ids)) {
        return new WP_Error('no_questions', __('No questions found in this MCQ set.', 'mcqhome'));
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
 * Save assessment attempt with detailed results
 */
function mcqhome_save_assessment_attempt($user_id, $mcq_set_id, $answers, $time_taken = 0) {
    global $wpdb;
    
    // Calculate score
    $score_data = mcqhome_calculate_assessment_score($mcq_set_id, $answers);
    
    if (is_wp_error($score_data)) {
        return $score_data;
    }
    
    // Save individual MCQ attempts
    foreach ($score_data['question_results'] as $result) {
        mcqhome_record_mcq_attempt(
            $user_id,
            $result['mcq_id'],
            $mcq_set_id,
            $result['selected_answer'],
            $result['correct_answer'],
            $result['score_points'],
            $score_data['negative_marking_rate']
        );
    }
    
    // Save MCQ set attempt
    $set_attempt_data = [
        'user_id' => $user_id,
        'mcq_set_id' => $mcq_set_id,
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
        'completed_at' => current_time('mysql')
    ];
    
    $attempt_id = $wpdb->insert(
        $wpdb->prefix . 'mcq_set_attempts',
        $set_attempt_data,
        ['%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%d', '%d', '%s', '%s']
    );
    
    if ($attempt_id) {
        // Clean up progress data
        mcqhome_end_assessment_session($user_id, $mcq_set_id);
        
        // Return complete result data
        return array_merge($score_data, [
            'attempt_id' => $wpdb->insert_id,
            'time_taken' => $time_taken
        ]);
    }
    
    return new WP_Error('save_failed', __('Failed to save assessment attempt.', 'mcqhome'));
}

/**
 * Get detailed assessment results
 */
function mcqhome_get_assessment_results($user_id, $mcq_set_id, $attempt_id = null) {
    global $wpdb;
    
    // Get the attempt record
    if ($attempt_id) {
        $attempt = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mcq_set_attempts 
             WHERE id = %d AND user_id = %d AND mcq_set_id = %d",
            $attempt_id, $user_id, $mcq_set_id
        ));
    } else {
        // Get the latest attempt
        $attempt = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mcq_set_attempts 
             WHERE user_id = %d AND mcq_set_id = %d AND status = 'completed'
             ORDER BY completed_at DESC LIMIT 1",
            $user_id, $mcq_set_id
        ));
    }
    
    if (!$attempt) {
        return new WP_Error('no_attempt', __('No assessment attempt found.', 'mcqhome'));
    }
    
    // Get individual question results
    $question_results = $wpdb->get_results($wpdb->prepare(
        "SELECT ma.*, m.post_title as question_title, m.post_content as question_text
         FROM {$wpdb->prefix}mcq_attempts ma
         LEFT JOIN {$wpdb->posts} m ON ma.mcq_id = m.ID
         WHERE ma.user_id = %d AND ma.mcq_set_id = %d 
         AND ma.completed_at = %s
         ORDER BY ma.mcq_id",
        $user_id, $mcq_set_id, $attempt->completed_at
    ));
    
    // Enhance question results with additional data
    foreach ($question_results as &$result) {
        $result->question_text = get_post_meta($result->mcq_id, '_mcq_question_text', true);
        $result->explanation = get_post_meta($result->mcq_id, '_mcq_explanation', true);
        $result->options = [
            'A' => get_post_meta($result->mcq_id, '_mcq_option_a', true),
            'B' => get_post_meta($result->mcq_id, '_mcq_option_b', true),
            'C' => get_post_meta($result->mcq_id, '_mcq_option_c', true),
            'D' => get_post_meta($result->mcq_id, '_mcq_option_d', true)
        ];
    }
    
    return [
        'attempt' => $attempt,
        'question_results' => $question_results,
        'mcq_set' => get_post($mcq_set_id),
        'summary' => [
            'total_questions' => $attempt->total_questions,
            'answered_questions' => $attempt->answered_questions,
            'correct_answers' => $attempt->correct_answers,
            'total_score' => $attempt->total_score,
            'max_score' => $attempt->max_score,
            'score_percentage' => $attempt->score_percentage,
            'is_passed' => $attempt->is_passed,
            'time_taken' => $attempt->time_taken
        ]
    ];
}

/**
 * Get performance analytics for user
 */
function mcqhome_get_user_performance_analytics($user_id, $mcq_set_id = null) {
    global $wpdb;
    
    $where_clause = "WHERE user_id = %d AND status = 'completed'";
    $params = [$user_id];
    
    if ($mcq_set_id) {
        $where_clause .= " AND mcq_set_id = %d";
        $params[] = $mcq_set_id;
    }
    
    // Overall statistics
    $overall_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COUNT(*) as total_attempts,
            AVG(score_percentage) as avg_score,
            MAX(score_percentage) as best_score,
            MIN(score_percentage) as worst_score,
            COUNT(CASE WHEN is_passed = 1 THEN 1 END) as passed_attempts,
            AVG(time_taken) as avg_time,
            SUM(correct_answers) as total_correct,
            SUM(total_questions) as total_questions_attempted
         FROM {$wpdb->prefix}mcq_set_attempts 
         $where_clause",
        $params
    ));
    
    // Performance over time
    $performance_trend = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            DATE(completed_at) as attempt_date,
            AVG(score_percentage) as avg_score,
            COUNT(*) as attempts_count
         FROM {$wpdb->prefix}mcq_set_attempts 
         $where_clause
         GROUP BY DATE(completed_at)
         ORDER BY attempt_date DESC
         LIMIT 30",
        $params
    ));
    
    // Subject-wise performance (if not specific to one set)
    $subject_performance = [];
    if (!$mcq_set_id) {
        $subject_performance = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                t.name as subject_name,
                AVG(sa.score_percentage) as avg_score,
                COUNT(sa.id) as attempts_count,
                COUNT(CASE WHEN sa.is_passed = 1 THEN 1 END) as passed_count
             FROM {$wpdb->prefix}mcq_set_attempts sa
             JOIN {$wpdb->posts} p ON sa.mcq_set_id = p.ID
             JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
             JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
             WHERE sa.user_id = %d AND sa.status = 'completed'
             AND tt.taxonomy = 'mcq_subject'
             GROUP BY t.term_id
             ORDER BY avg_score DESC",
            $user_id
        ));
    }
    
    // Calculate pass rate
    $pass_rate = $overall_stats->total_attempts > 0 
        ? ($overall_stats->passed_attempts / $overall_stats->total_attempts) * 100 
        : 0;
    
    // Calculate accuracy
    $accuracy = $overall_stats->total_questions_attempted > 0 
        ? ($overall_stats->total_correct / $overall_stats->total_questions_attempted) * 100 
        : 0;
    
    return [
        'overall_stats' => $overall_stats,
        'pass_rate' => round($pass_rate, 2),
        'accuracy' => round($accuracy, 2),
        'performance_trend' => $performance_trend,
        'subject_performance' => $subject_performance,
        'improvement_areas' => mcqhome_identify_improvement_areas($user_id, $mcq_set_id)
    ];
}

/**
 * Identify areas for improvement based on performance
 */
function mcqhome_identify_improvement_areas($user_id, $mcq_set_id = null) {
    global $wpdb;
    
    $where_clause = "WHERE ma.user_id = %d";
    $params = [$user_id];
    
    if ($mcq_set_id) {
        $where_clause .= " AND ma.mcq_set_id = %d";
        $params[] = $mcq_set_id;
    }
    
    // Get topics with low performance
    $weak_topics = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            t.name as topic_name,
            COUNT(ma.id) as total_attempts,
            COUNT(CASE WHEN ma.is_correct = 1 THEN 1 END) as correct_attempts,
            (COUNT(CASE WHEN ma.is_correct = 1 THEN 1 END) / COUNT(ma.id)) * 100 as accuracy
         FROM {$wpdb->prefix}mcq_attempts ma
         JOIN {$wpdb->posts} p ON ma.mcq_id = p.ID
         JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         $where_clause AND tt.taxonomy = 'mcq_topic'
         GROUP BY t.term_id
         HAVING accuracy < 60 AND total_attempts >= 3
         ORDER BY accuracy ASC
         LIMIT 5",
        $params
    ));
    
    // Get difficulty levels with low performance
    $difficulty_performance = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            t.name as difficulty_level,
            COUNT(ma.id) as total_attempts,
            COUNT(CASE WHEN ma.is_correct = 1 THEN 1 END) as correct_attempts,
            (COUNT(CASE WHEN ma.is_correct = 1 THEN 1 END) / COUNT(ma.id)) * 100 as accuracy
         FROM {$wpdb->prefix}mcq_attempts ma
         JOIN {$wpdb->posts} p ON ma.mcq_id = p.ID
         JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
         $where_clause AND tt.taxonomy = 'mcq_difficulty'
         GROUP BY t.term_id
         ORDER BY accuracy ASC",
        $params
    ));
    
    return [
        'weak_topics' => $weak_topics,
        'difficulty_performance' => $difficulty_performance
    ];
}

/**
 * Compare user performance with others
 */
function mcqhome_get_performance_comparison($user_id, $mcq_set_id) {
    global $wpdb;
    
    // Get user's best attempt
    $user_attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE user_id = %d AND mcq_set_id = %d AND status = 'completed'
         ORDER BY total_score DESC, completed_at ASC LIMIT 1",
        $user_id, $mcq_set_id
    ));
    
    if (!$user_attempt) {
        return new WP_Error('no_attempt', __('No completed attempt found.', 'mcqhome'));
    }
    
    // Get overall statistics for this MCQ set
    $overall_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COUNT(*) as total_attempts,
            COUNT(DISTINCT user_id) as unique_users,
            AVG(score_percentage) as avg_score,
            MAX(score_percentage) as highest_score,
            MIN(score_percentage) as lowest_score
         FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE mcq_set_id = %d AND status = 'completed'",
        $mcq_set_id
    ));
    
    // Calculate percentile rank
    $better_scores = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT user_id) 
         FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE mcq_set_id = %d AND status = 'completed' 
         AND score_percentage < %f",
        $mcq_set_id, $user_attempt->score_percentage
    ));
    
    $percentile = $overall_stats->unique_users > 0 
        ? ($better_scores / $overall_stats->unique_users) * 100 
        : 0;
    
    return [
        'user_score' => $user_attempt->score_percentage,
        'average_score' => round($overall_stats->avg_score, 2),
        'highest_score' => $overall_stats->highest_score,
        'lowest_score' => $overall_stats->lowest_score,
        'percentile_rank' => round($percentile, 1),
        'total_participants' => $overall_stats->unique_users,
        'performance_level' => mcqhome_get_performance_level($percentile)
    ];
}

/**
 * Get performance level based on percentile
 */
function mcqhome_get_performance_level($percentile) {
    if ($percentile >= 90) {
        return ['level' => 'excellent', 'label' => __('Excellent', 'mcqhome')];
    } elseif ($percentile >= 75) {
        return ['level' => 'good', 'label' => __('Good', 'mcqhome')];
    } elseif ($percentile >= 50) {
        return ['level' => 'average', 'label' => __('Average', 'mcqhome')];
    } elseif ($percentile >= 25) {
        return ['level' => 'below_average', 'label' => __('Below Average', 'mcqhome')];
    } else {
        return ['level' => 'needs_improvement', 'label' => __('Needs Improvement', 'mcqhome')];
    }
}

/**
 * Generate assessment report
 */
function mcqhome_generate_assessment_report($mcq_set_id, $date_from = null, $date_to = null) {
    global $wpdb;
    
    $date_condition = '';
    $params = [$mcq_set_id];
    
    if ($date_from) {
        $date_condition .= ' AND completed_at >= %s';
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $date_condition .= ' AND completed_at <= %s';
        $params[] = $date_to;
    }
    
    $stats = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COUNT(*) as total_attempts,
            COUNT(DISTINCT user_id) as unique_users,
            AVG(score_percentage) as avg_score,
            MIN(score_percentage) as min_score,
            MAX(score_percentage) as max_score,
            COUNT(CASE WHEN is_passed = 1 THEN 1 END) as passed_count,
            AVG(time_taken) as avg_time,
            MIN(time_taken) as min_time,
            MAX(time_taken) as max_time
         FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE mcq_set_id = %d AND status = 'completed' $date_condition",
        $params
    ));
    
    $score_distribution = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            CASE 
                WHEN score_percentage >= 90 THEN 'A (90-100%)'
                WHEN score_percentage >= 80 THEN 'B (80-89%)'
                WHEN score_percentage >= 70 THEN 'C (70-79%)'
                WHEN score_percentage >= 60 THEN 'D (60-69%)'
                ELSE 'F (Below 60%)'
            END as grade,
            COUNT(*) as count
         FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE mcq_set_id = %d AND status = 'completed' $date_condition
         GROUP BY grade
         ORDER BY MIN(score_percentage) DESC",
        $params
    ));
    
    return [
        'stats' => $stats,
        'score_distribution' => $score_distribution,
        'pass_rate' => $stats->total_attempts > 0 ? ($stats->passed_count / $stats->total_attempts) * 100 : 0
    ];
}