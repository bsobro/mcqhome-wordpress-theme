<?php
/**
 * Assessment Security and Validation System for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assessment Security Manager Class
 */
class MCQHome_Assessment_Security {

    /**
     * Initialize security hooks and filters
     */
    public function __construct() {
        add_action('wp_ajax_mcqhome_validate_session', [$this, 'ajax_validate_session']);
        add_action('wp_ajax_mcqhome_submit_assessment', [$this, 'ajax_submit_assessment']);
        add_action('wp_ajax_mcqhome_save_secure_progress', [$this, 'ajax_save_secure_progress']);
        add_action('wp_ajax_mcqhome_log_activity', [$this, 'ajax_log_activity']);
        
        // Add security headers for assessment pages
        add_action('wp_head', [$this, 'add_security_headers']);
        
        // Schedule cleanup of expired sessions
        add_action('mcqhome_cleanup_expired_sessions', [$this, 'cleanup_expired_sessions']);
        if (!wp_next_scheduled('mcqhome_cleanup_expired_sessions')) {
            wp_schedule_event(time(), 'hourly', 'mcqhome_cleanup_expired_sessions');
        }
    }

    /**
     * Validate assessment session with enhanced security
     */
    public function validate_session($mcq_set_id, $user_id, $check_time_limit = true) {
        // Basic validation
        if (!$user_id || !$mcq_set_id) {
            return new WP_Error('invalid_params', __('Invalid session parameters.', 'mcqhome'));
        }

        // Check if MCQ set exists
        $mcq_set = get_post($mcq_set_id);
        if (!$mcq_set || $mcq_set->post_type !== 'mcq_set') {
            return new WP_Error('invalid_set', __('MCQ set not found.', 'mcqhome'));
        }

        // Check user enrollment
        if (function_exists('mcqhome_check_user_enrollment')) {
            $enrollment = mcqhome_check_user_enrollment($user_id, $mcq_set_id);
            if (!$enrollment) {
                return new WP_Error('not_enrolled', __('User not enrolled in this assessment.', 'mcqhome'));
            }
        }

        // Get user progress
        $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        if (!$progress) {
            return new WP_Error('no_session', __('No active assessment session found.', 'mcqhome'));
        }

        // Check session age (24 hours max)
        $session_age = time() - strtotime($progress->created_at);
        if ($session_age > 24 * 3600) {
            $this->cleanup_user_session($user_id, $mcq_set_id);
            return new WP_Error('session_expired', __('Assessment session has expired.', 'mcqhome'));
        }

        // Check time limit if enabled and requested
        if ($check_time_limit) {
            $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
            if ($time_limit) {
                $time_remaining = mcqhome_calculate_time_remaining($user_id, $mcq_set_id);
                if ($time_remaining <= 0) {
                    $this->auto_submit_assessment($user_id, $mcq_set_id, 'time_expired');
                    return new WP_Error('time_expired', __('Assessment time limit has expired.', 'mcqhome'));
                }
            }
        }

        // Check for suspicious activity
        $suspicious_activity = $this->detect_suspicious_activity($user_id, $mcq_set_id);
        if ($suspicious_activity) {
            $this->log_security_event($user_id, $mcq_set_id, 'suspicious_activity', $suspicious_activity);
            
            // For now, just log - could implement stricter measures
            if ($suspicious_activity['severity'] === 'high') {
                return new WP_Error('suspicious_activity', __('Suspicious activity detected. Assessment may be terminated.', 'mcqhome'));
            }
        }

        return true;
    }

    /**
     * Save progress with section awareness and security validation
     */
    public function save_secure_progress($user_id, $mcq_set_id, $progress_data) {
        // Validate session first
        $validation = $this->validate_session($mcq_set_id, $user_id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Validate progress data structure
        $validation_result = $this->validate_progress_data($mcq_set_id, $progress_data);
        if (is_wp_error($validation_result)) {
            return $validation_result;
        }

        // Get MCQ set configuration for section validation
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Validate section-aware progress
        if ($config['sections_enabled'] && !empty($config['sections'])) {
            $section_validation = $this->validate_section_progress($config, $progress_data);
            if (is_wp_error($section_validation)) {
                return $section_validation;
            }
        }

        // Add security metadata
        $progress_data['last_activity'] = current_time('mysql');
        $progress_data['ip_address'] = $this->get_client_ip();
        $progress_data['user_agent_hash'] = md5($_SERVER['HTTP_USER_AGENT'] ?? '');
        $progress_data['session_token'] = $this->generate_session_token($user_id, $mcq_set_id);

        // Calculate section-wise progress if sections are enabled
        if ($config['sections_enabled']) {
            $progress_data['section_progress'] = $this->calculate_section_progress($config, $progress_data);
        }

        // Save progress with enhanced security
        try {
            $result = mcqhome_update_user_progress($user_id, $mcq_set_id, $progress_data);
            
            // Log progress save activity
            $this->log_assessment_activity($user_id, $mcq_set_id, 'progress_saved', [
                'current_question' => $progress_data['current_question'] ?? 0,
                'answered_count' => count($progress_data['answers_data'] ?? []),
                'progress_percentage' => $progress_data['progress_percentage'] ?? 0
            ]);

            return $result;
        } catch (Exception $e) {
            error_log('MCQHome Security: Progress save failed - ' . $e->getMessage());
            return new WP_Error('save_failed', __('Failed to save progress securely.', 'mcqhome'));
        }
    }

    /**
     * Validate assessment submission with comprehensive checks
     */
    public function validate_submission($user_id, $mcq_set_id, $answers, $submission_data = []) {
        // Validate session
        $session_validation = $this->validate_session($mcq_set_id, $user_id);
        if (is_wp_error($session_validation)) {
            return $session_validation;
        }

        // Check if already submitted
        if (mcqhome_has_user_completed_set($user_id, $mcq_set_id)) {
            $allow_retakes = get_post_meta($mcq_set_id, '_mcq_set_allow_retakes', true);
            if (!$allow_retakes) {
                return new WP_Error('already_submitted', __('Assessment already submitted and retakes not allowed.', 'mcqhome'));
            }
        }

        // Get assessment configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Validate answers structure
        $answer_validation = $this->validate_answers_structure($config, $answers);
        if (is_wp_error($answer_validation)) {
            return $answer_validation;
        }

        // Validate submission timing
        $timing_validation = $this->validate_submission_timing($user_id, $mcq_set_id, $submission_data);
        if (is_wp_error($timing_validation)) {
            return $timing_validation;
        }

        // Check for cheating patterns
        $cheating_check = $this->detect_cheating_patterns($user_id, $mcq_set_id, $answers, $submission_data);
        if (is_wp_error($cheating_check)) {
            return $cheating_check;
        }

        // Validate section completion if sections are enabled
        if ($config['sections_enabled'] && !empty($config['sections'])) {
            $section_validation = $this->validate_section_completion($config, $answers);
            if (is_wp_error($section_validation)) {
                return $section_validation;
            }
        }

        return true;
    }

    /**
     * Submit assessment with security validation
     */
    public function submit_assessment($user_id, $mcq_set_id, $answers, $submission_data = []) {
        // Validate submission
        $validation = $this->validate_submission($user_id, $mcq_set_id, $answers, $submission_data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Add security metadata to submission
        $submission_data['ip_address'] = $this->get_client_ip();
        $submission_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $submission_data['submission_time'] = current_time('mysql');
        $submission_data['session_duration'] = $this->calculate_session_duration($user_id, $mcq_set_id);

        // Process submission through assessment functions
        if (function_exists('mcqhome_save_assessment_attempt')) {
            try {
                $result = mcqhome_save_assessment_attempt(
                    $user_id, 
                    $mcq_set_id, 
                    $answers, 
                    $submission_data['session_duration'] ?? 0
                );

                if (is_wp_error($result)) {
                    return $result;
                }

                // Log successful submission
                $this->log_assessment_activity($user_id, $mcq_set_id, 'assessment_submitted', [
                    'total_score' => $result['total_score'],
                    'score_percentage' => $result['score_percentage'],
                    'is_passed' => $result['is_passed'],
                    'submission_method' => $submission_data['submission_method'] ?? 'manual'
                ]);

                // Clean up session
                $this->cleanup_user_session($user_id, $mcq_set_id);

                return $result;
            } catch (Exception $e) {
                error_log('MCQHome Security: Assessment submission failed - ' . $e->getMessage());
                return new WP_Error('submission_failed', __('Assessment submission failed.', 'mcqhome'));
            }
        }

        return new WP_Error('function_missing', __('Assessment submission system not available.', 'mcqhome'));
    }

    /**
     * Detect suspicious activity patterns
     */
    private function detect_suspicious_activity($user_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        $current_time = current_time('mysql');
        
        $suspicious_patterns = [];

        // Check for rapid answer changes (more than 20 in 2 minutes)
        $rapid_changes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'answer_changed' 
             AND timestamp > DATE_SUB(%s, INTERVAL 2 MINUTE)",
            $user_id, $mcq_set_id, $current_time
        ));

        if ($rapid_changes > 20) {
            $suspicious_patterns[] = [
                'type' => 'rapid_answer_changes',
                'count' => $rapid_changes,
                'severity' => 'medium'
            ];
        }

        // Check for excessive tab switching (more than 10 in 5 minutes)
        $tab_switches = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type IN ('tab_blur', 'tab_focus', 'window_blur', 'window_focus')
             AND timestamp > DATE_SUB(%s, INTERVAL 5 MINUTE)",
            $user_id, $mcq_set_id, $current_time
        ));

        if ($tab_switches > 10) {
            $suspicious_patterns[] = [
                'type' => 'excessive_tab_switching',
                'count' => $tab_switches,
                'severity' => 'high'
            ];
        }

        // Check for copy/paste activities
        $copy_paste = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type IN ('copy_detected', 'paste_detected')
             AND timestamp > DATE_SUB(%s, INTERVAL 10 MINUTE)",
            $user_id, $mcq_set_id, $current_time
        ));

        if ($copy_paste > 5) {
            $suspicious_patterns[] = [
                'type' => 'copy_paste_activity',
                'count' => $copy_paste,
                'severity' => 'medium'
            ];
        }

        // Check for unusual timing patterns (too fast or too slow)
        $timing_analysis = $this->analyze_answer_timing($user_id, $mcq_set_id);
        if ($timing_analysis['suspicious']) {
            $suspicious_patterns[] = [
                'type' => 'unusual_timing',
                'details' => $timing_analysis,
                'severity' => 'medium'
            ];
        }

        if (!empty($suspicious_patterns)) {
            $max_severity = 'low';
            foreach ($suspicious_patterns as $pattern) {
                if ($pattern['severity'] === 'high') {
                    $max_severity = 'high';
                    break;
                } elseif ($pattern['severity'] === 'medium' && $max_severity !== 'high') {
                    $max_severity = 'medium';
                }
            }

            return [
                'patterns' => $suspicious_patterns,
                'severity' => $max_severity,
                'timestamp' => $current_time
            ];
        }

        return false;
    }

    /**
     * Analyze answer timing patterns
     */
    private function analyze_answer_timing($user_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        
        // Get answer timing data
        $answer_times = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_data, timestamp FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'answer_selected'
             ORDER BY timestamp ASC",
            $user_id, $mcq_set_id
        ));

        if (count($answer_times) < 3) {
            return ['suspicious' => false];
        }

        $intervals = [];
        $previous_time = null;

        foreach ($answer_times as $answer_time) {
            $current_time = strtotime($answer_time->timestamp);
            if ($previous_time) {
                $intervals[] = $current_time - $previous_time;
            }
            $previous_time = $current_time;
        }

        // Calculate statistics
        $avg_interval = array_sum($intervals) / count($intervals);
        $very_fast_count = count(array_filter($intervals, function($interval) {
            return $interval < 3; // Less than 3 seconds
        }));
        
        $very_slow_count = count(array_filter($intervals, function($interval) {
            return $interval > 300; // More than 5 minutes
        }));

        // Determine if suspicious
        $suspicious = false;
        $reasons = [];

        if ($very_fast_count > count($intervals) * 0.5) {
            $suspicious = true;
            $reasons[] = 'too_many_fast_answers';
        }

        if ($avg_interval < 5) {
            $suspicious = true;
            $reasons[] = 'overall_too_fast';
        }

        return [
            'suspicious' => $suspicious,
            'avg_interval' => $avg_interval,
            'very_fast_count' => $very_fast_count,
            'very_slow_count' => $very_slow_count,
            'reasons' => $reasons
        ];
    }

    /**
     * Validate progress data structure
     */
    private function validate_progress_data($mcq_set_id, $progress_data) {
        // Check required fields
        $required_fields = ['current_question', 'answers_data', 'progress_percentage'];
        foreach ($required_fields as $field) {
            if (!isset($progress_data[$field])) {
                return new WP_Error('missing_field', sprintf(__('Missing required field: %s', 'mcqhome'), $field));
            }
        }

        // Validate current question
        $current_question = intval($progress_data['current_question']);
        if ($current_question < 0) {
            return new WP_Error('invalid_question', __('Invalid current question number.', 'mcqhome'));
        }

        // Validate answers data
        if (!is_array($progress_data['answers_data'])) {
            return new WP_Error('invalid_answers', __('Answers data must be an array.', 'mcqhome'));
        }

        // Validate individual answers
        foreach ($progress_data['answers_data'] as $question_index => $answer) {
            if (!is_numeric($question_index) || !in_array($answer, ['A', 'B', 'C', 'D'])) {
                return new WP_Error('invalid_answer_format', __('Invalid answer format detected.', 'mcqhome'));
            }
        }

        // Validate progress percentage
        $progress_percentage = floatval($progress_data['progress_percentage']);
        if ($progress_percentage < 0 || $progress_percentage > 100) {
            return new WP_Error('invalid_progress', __('Invalid progress percentage.', 'mcqhome'));
        }

        return true;
    }

    /**
     * Validate section-aware progress
     */
    private function validate_section_progress($config, $progress_data) {
        if (!$config['sections_enabled'] || empty($config['sections'])) {
            return true;
        }

        // Validate section progress if provided
        if (isset($progress_data['section_progress'])) {
            foreach ($progress_data['section_progress'] as $section_id => $section_data) {
                // Check if section exists
                $section_exists = false;
                foreach ($config['sections'] as $section) {
                    if ($section['id'] === $section_id) {
                        $section_exists = true;
                        break;
                    }
                }

                if (!$section_exists) {
                    return new WP_Error('invalid_section', sprintf(__('Invalid section ID: %s', 'mcqhome'), $section_id));
                }

                // Validate section data structure
                if (!isset($section_data['attempted']) || !isset($section_data['total'])) {
                    return new WP_Error('invalid_section_data', __('Invalid section progress data structure.', 'mcqhome'));
                }
            }
        }

        return true;
    }

    /**
     * Calculate section-wise progress
     */
    private function calculate_section_progress($config, $progress_data) {
        if (!$config['sections_enabled'] || empty($config['sections'])) {
            return [];
        }

        $section_progress = [];
        $answers = $progress_data['answers_data'] ?? [];

        foreach ($config['sections'] as $section) {
            $section_id = $section['id'];
            $section_questions = $config['questions'][$section_id] ?? [];
            $total_questions = count($section_questions);
            
            // Count answered questions in this section
            $answered_count = 0;
            $section_start_index = $this->get_section_start_index($config['questions'], $section_id);
            
            for ($i = 0; $i < $total_questions; $i++) {
                $global_index = $section_start_index + $i;
                if (isset($answers[$global_index])) {
                    $answered_count++;
                }
            }

            $section_progress[$section_id] = [
                'attempted' => $answered_count,
                'total' => $total_questions,
                'percentage' => $total_questions > 0 ? round(($answered_count / $total_questions) * 100, 2) : 0
            ];
        }

        return $section_progress;
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
     * Validate answers structure
     */
    private function validate_answers_structure($config, $answers) {
        if (!is_array($answers)) {
            return new WP_Error('invalid_format', __('Answers must be in array format.', 'mcqhome'));
        }

        $total_questions = 0;
        foreach ($config['questions'] as $section_questions) {
            $total_questions += count($section_questions);
        }

        // Check for invalid question indices
        foreach ($answers as $question_index => $answer) {
            if (!is_numeric($question_index) || $question_index < 0 || $question_index >= $total_questions) {
                return new WP_Error('invalid_question_index', sprintf(__('Invalid question index: %s', 'mcqhome'), $question_index));
            }

            if (!in_array($answer, ['A', 'B', 'C', 'D'])) {
                return new WP_Error('invalid_answer_value', sprintf(__('Invalid answer value for question %d: %s', 'mcqhome'), $question_index + 1, $answer));
            }
        }

        return true;
    }

    /**
     * Validate submission timing
     */
    private function validate_submission_timing($user_id, $mcq_set_id, $submission_data) {
        $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        if (!$progress) {
            return new WP_Error('no_progress', __('No progress data found.', 'mcqhome'));
        }

        $session_duration = time() - strtotime($progress->created_at);
        
        // Check minimum time (prevent too fast submissions)
        $min_time = 30; // 30 seconds minimum
        if ($session_duration < $min_time) {
            return new WP_Error('too_fast', __('Submission too fast. Please take more time to review.', 'mcqhome'));
        }

        // Check maximum time (session timeout)
        $max_time = 24 * 3600; // 24 hours
        if ($session_duration > $max_time) {
            return new WP_Error('session_timeout', __('Session has timed out.', 'mcqhome'));
        }

        return true;
    }

    /**
     * Detect cheating patterns in submission
     */
    private function detect_cheating_patterns($user_id, $mcq_set_id, $answers, $submission_data) {
        // Check for sequential answer patterns (all A, all B, etc.)
        $answer_counts = array_count_values($answers);
        $total_answers = count($answers);
        
        if ($total_answers > 0) {
            foreach ($answer_counts as $answer => $count) {
                if ($count / $total_answers > 0.8) { // More than 80% same answer
                    $this->log_security_event($user_id, $mcq_set_id, 'sequential_answers', [
                        'answer' => $answer,
                        'percentage' => ($count / $total_answers) * 100
                    ]);
                }
            }
        }

        // Check for impossible timing (too many answers in too short time)
        $session_duration = $this->calculate_session_duration($user_id, $mcq_set_id);
        if ($total_answers > 0 && $session_duration > 0) {
            $avg_time_per_question = $session_duration / $total_answers;
            if ($avg_time_per_question < 5) { // Less than 5 seconds per question
                return new WP_Error('impossible_timing', __('Submission timing appears suspicious.', 'mcqhome'));
            }
        }

        return true;
    }

    /**
     * Validate section completion
     */
    private function validate_section_completion($config, $answers) {
        // This is optional - we don't require all sections to be completed
        // But we can validate that answered questions belong to valid sections
        
        $total_questions = 0;
        foreach ($config['questions'] as $section_questions) {
            $total_questions += count($section_questions);
        }

        foreach ($answers as $question_index => $answer) {
            if ($question_index >= $total_questions) {
                return new WP_Error('invalid_question', sprintf(__('Question index %d exceeds total questions.', 'mcqhome'), $question_index));
            }
        }

        return true;
    }

    /**
     * Auto-submit assessment when time expires
     */
    private function auto_submit_assessment($user_id, $mcq_set_id, $reason = 'time_expired') {
        $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        if (!$progress) {
            return false;
        }

        $answers = maybe_unserialize($progress->answers_data) ?: [];
        
        $submission_data = [
            'submission_method' => 'auto_submit',
            'reason' => $reason,
            'auto_submitted_at' => current_time('mysql')
        ];

        return $this->submit_assessment($user_id, $mcq_set_id, $answers, $submission_data);
    }

    /**
     * Calculate session duration
     */
    private function calculate_session_duration($user_id, $mcq_set_id) {
        $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        if (!$progress) {
            return 0;
        }

        return time() - strtotime($progress->created_at);
    }

    /**
     * Get assessment configuration
     */
    private function get_assessment_config($mcq_set_id) {
        $controller = mcqhome_get_assessment_controller();
        return $controller->get_assessment_config($mcq_set_id);
    }

    /**
     * Generate secure session token
     */
    private function generate_session_token($user_id, $mcq_set_id) {
        $data = $user_id . '|' . $mcq_set_id . '|' . time() . '|' . wp_get_session_token();
        return hash('sha256', $data . AUTH_SALT);
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Log assessment activity for security monitoring
     */
    private function log_assessment_activity($user_id, $mcq_set_id, $activity_type, $data = []) {
        if (function_exists('mcqhome_log_assessment_activity')) {
            mcqhome_log_assessment_activity($user_id, $mcq_set_id, $activity_type, $data);
        }
    }

    /**
     * Log security events
     */
    private function log_security_event($user_id, $mcq_set_id, $event_type, $data = []) {
        $this->log_assessment_activity($user_id, $mcq_set_id, 'security_event', [
            'event_type' => $event_type,
            'event_data' => $data,
            'timestamp' => current_time('mysql'),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    /**
     * Clean up expired sessions
     */
    public function cleanup_expired_sessions() {
        global $wpdb;
        
        // Remove progress data older than 24 hours
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}mcq_user_progress 
             WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // Remove activity logs older than 30 days
        $activity_table = $wpdb->prefix . 'mcq_activity_log';
        if ($wpdb->get_var("SHOW TABLES LIKE '$activity_table'") === $activity_table) {
            $wpdb->query(
                "DELETE FROM $activity_table 
                 WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
        }
    }

    /**
     * Clean up specific user session
     */
    private function cleanup_user_session($user_id, $mcq_set_id) {
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->prefix . 'mcq_user_progress',
            ['user_id' => $user_id, 'mcq_set_id' => $mcq_set_id],
            ['%d', '%d']
        );
    }

    /**
     * Enhanced session validation for sectioned assessments
     */
    public function validate_sectioned_session($mcq_set_id, $user_id, $current_section = '') {
        // First run standard validation
        $basic_validation = $this->validate_session($mcq_set_id, $user_id);
        if (is_wp_error($basic_validation)) {
            return $basic_validation;
        }

        // Get assessment configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Additional validation for sectioned assessments
        if ($config['sections_enabled'] && !empty($config['sections'])) {
            // Validate current section if provided
            if ($current_section) {
                $section_exists = false;
                foreach ($config['sections'] as $section) {
                    if ($section['id'] === $current_section) {
                        $section_exists = true;
                        break;
                    }
                }

                if (!$section_exists) {
                    return new WP_Error('invalid_section', __('Invalid section specified.', 'mcqhome'));
                }
            }

            // Check section-specific time limits if implemented
            if (isset($config['section_time_limits'][$current_section])) {
                $section_time_validation = $this->validate_section_time_limit($user_id, $mcq_set_id, $current_section);
                if (is_wp_error($section_time_validation)) {
                    return $section_time_validation;
                }
            }
        }

        return true;
    }

    /**
     * Enhanced progress validation for sectioned assessments
     */
    public function validate_sectioned_progress($mcq_set_id, $progress_data, $current_section = '') {
        // First run standard progress validation
        $basic_validation = $this->validate_progress_data($mcq_set_id, $progress_data);
        if (is_wp_error($basic_validation)) {
            return $basic_validation;
        }

        // Get assessment configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Enhanced validation for sectioned assessments
        if ($config['sections_enabled'] && !empty($config['sections'])) {
            // Validate section progress consistency
            if (isset($progress_data['section_progress'])) {
                $section_validation = $this->validate_section_progress_consistency($config, $progress_data);
                if (is_wp_error($section_validation)) {
                    return $section_validation;
                }
            }

            // Validate current section context
            if ($current_section) {
                $current_question = $progress_data['current_question'] ?? 0;
                $section_validation = $this->validate_question_section_context($config, $current_question, $current_section);
                if (is_wp_error($section_validation)) {
                    return $section_validation;
                }
            }
        }

        return true;
    }

    /**
     * Validate section progress consistency
     */
    private function validate_section_progress_consistency($config, $progress_data) {
        $answers = $progress_data['answers_data'] ?? [];
        $section_progress = $progress_data['section_progress'] ?? [];

        foreach ($section_progress as $section_id => $section_data) {
            // Verify section exists
            $section_exists = false;
            foreach ($config['sections'] as $section) {
                if ($section['id'] === $section_id) {
                    $section_exists = true;
                    break;
                }
            }

            if (!$section_exists) {
                return new WP_Error('invalid_section_progress', sprintf(__('Invalid section in progress data: %s', 'mcqhome'), $section_id));
            }

            // Validate section progress data structure
            if (!isset($section_data['attempted']) || !isset($section_data['total'])) {
                return new WP_Error('invalid_section_data', __('Invalid section progress data structure.', 'mcqhome'));
            }

            // Validate attempted count doesn't exceed total
            if ($section_data['attempted'] > $section_data['total']) {
                return new WP_Error('invalid_section_count', sprintf(__('Section attempted count exceeds total for section: %s', 'mcqhome'), $section_id));
            }
        }

        return true;
    }

    /**
     * Validate question is in correct section context
     */
    private function validate_question_section_context($config, $current_question, $current_section) {
        if (!isset($config['questions'][$current_section])) {
            return new WP_Error('section_not_found', __('Section not found in configuration.', 'mcqhome'));
        }

        // Calculate question range for this section
        $section_start_index = $this->get_section_start_index($config['questions'], $current_section);
        $section_question_count = count($config['questions'][$current_section]);
        $section_end_index = $section_start_index + $section_question_count - 1;

        if ($current_question < $section_start_index || $current_question > $section_end_index) {
            return new WP_Error('question_section_mismatch', __('Current question does not belong to specified section.', 'mcqhome'));
        }

        return true;
    }

    /**
     * Validate section time limit
     */
    private function validate_section_time_limit($user_id, $mcq_set_id, $section_id) {
        // Get section start time from progress data
        $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        if (!$progress) {
            return new WP_Error('no_progress', __('No progress data found.', 'mcqhome'));
        }

        $section_times = maybe_unserialize($progress->section_times) ?: [];
        if (!isset($section_times[$section_id])) {
            // First time in this section, record start time
            $section_times[$section_id] = ['start_time' => current_time('mysql')];
            
            // Update progress with section time
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'mcq_user_progress',
                ['section_times' => serialize($section_times)],
                ['user_id' => $user_id, 'mcq_set_id' => $mcq_set_id]
            );
            
            return true;
        }

        // Check if section time limit is exceeded (if implemented)
        // This would require section-specific time limits in the configuration
        return true;
    }

    /**
     * Enhanced anti-cheating measures for different assessment formats
     */
    public function detect_format_specific_cheating($user_id, $mcq_set_id, $display_format, $activity_data = []) {
        $cheating_patterns = [];

        if ($display_format === 'next_next') {
            // Next-Next format specific checks
            $cheating_patterns = array_merge($cheating_patterns, $this->detect_next_next_cheating($user_id, $mcq_set_id, $activity_data));
        } elseif ($display_format === 'single_page') {
            // Single page format specific checks
            $cheating_patterns = array_merge($cheating_patterns, $this->detect_single_page_cheating($user_id, $mcq_set_id, $activity_data));
        }

        return $cheating_patterns;
    }

    /**
     * Detect cheating patterns specific to Next-Next format
     */
    private function detect_next_next_cheating($user_id, $mcq_set_id, $activity_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        $patterns = [];

        // Check for rapid navigation without reading
        $navigation_activities = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_data, timestamp FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'question_navigation'
             AND timestamp > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
             ORDER BY timestamp ASC",
            $user_id, $mcq_set_id
        ));

        if (count($navigation_activities) > 20) {
            $patterns[] = [
                'type' => 'excessive_navigation',
                'count' => count($navigation_activities),
                'severity' => 'medium'
            ];
        }

        // Check for answers without sufficient time on question
        $quick_answers = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'answer_selected'
             AND JSON_EXTRACT(activity_data, '$.time_on_question') < 3
             AND timestamp > DATE_SUB(NOW(), INTERVAL 10 MINUTE)",
            $user_id, $mcq_set_id
        ));

        if ($quick_answers > 5) {
            $patterns[] = [
                'type' => 'rapid_answers_next_next',
                'count' => $quick_answers,
                'severity' => 'high'
            ];
        }

        return $patterns;
    }

    /**
     * Detect cheating patterns specific to Single Page format
     */
    private function detect_single_page_cheating($user_id, $mcq_set_id, $activity_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        $patterns = [];

        // Check for rapid scrolling without reading
        $scroll_activities = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'page_scroll'
             AND timestamp > DATE_SUB(NOW(), INTERVAL 2 MINUTE)",
            $user_id, $mcq_set_id
        ));

        if ($scroll_activities > 50) {
            $patterns[] = [
                'type' => 'excessive_scrolling',
                'count' => $scroll_activities,
                'severity' => 'medium'
            ];
        }

        // Check for answers in unrealistic sequence (all at once)
        $answer_timestamps = $wpdb->get_results($wpdb->prepare(
            "SELECT timestamp FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'answer_selected'
             AND timestamp > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
             ORDER BY timestamp ASC",
            $user_id, $mcq_set_id
        ));

        if (count($answer_timestamps) > 10) {
            // Check if all answers were within 30 seconds
            $first_answer = strtotime($answer_timestamps[0]->timestamp);
            $last_answer = strtotime(end($answer_timestamps)->timestamp);
            
            if (($last_answer - $first_answer) < 30) {
                $patterns[] = [
                    'type' => 'bulk_answers_single_page',
                    'count' => count($answer_timestamps),
                    'time_span' => $last_answer - $first_answer,
                    'severity' => 'high'
                ];
            }
        }

        return $patterns;
    }

    /**
     * Get time remaining for assessment
     */
    private function get_time_remaining($user_id, $mcq_set_id) {
        $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
        if (!$time_limit) {
            return null;
        }

        $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        if (!$progress) {
            return $time_limit * 60; // Convert minutes to seconds
        }

        $elapsed = time() - strtotime($progress->created_at);
        $remaining = ($time_limit * 60) - $elapsed;
        
        return max(0, $remaining);
    }

    /**
     * Add security headers for assessment pages
     */
    public function add_security_headers() {
        if (is_page_template('page-take-assessment.php') || 
            (is_page() && get_post_meta(get_the_ID(), '_mcq_assessment_page', true))) {
            
            // Prevent caching of assessment pages
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Content Security Policy for assessment pages
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");
            
            // Prevent framing (clickjacking protection)
            header('X-Frame-Options: DENY');
            
            // XSS Protection
            header('X-XSS-Protection: 1; mode=block');
            
            // Content type sniffing protection
            header('X-Content-Type-Options: nosniff');
            
            // Referrer policy
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }

    /**
     * Enhanced submission validation for sectioned assessments
     */
    public function validate_sectioned_submission($user_id, $mcq_set_id, $answers, $submission_data = []) {
        // Run standard submission validation first
        $basic_validation = $this->validate_submission($user_id, $mcq_set_id, $answers, $submission_data);
        if (is_wp_error($basic_validation)) {
            return $basic_validation;
        }

        // Get assessment configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Enhanced validation for sectioned assessments
        if ($config['sections_enabled'] && !empty($config['sections'])) {
            // Validate section completion patterns
            $section_validation = $this->validate_section_submission_patterns($config, $answers, $submission_data);
            if (is_wp_error($section_validation)) {
                return $section_validation;
            }

            // Check for format-specific cheating patterns
            $display_format = $config['display_format'] ?? 'next_next';
            $cheating_patterns = $this->detect_format_specific_cheating($user_id, $mcq_set_id, $display_format, $submission_data);
            
            if (!empty($cheating_patterns)) {
                $high_severity_patterns = array_filter($cheating_patterns, function($pattern) {
                    return $pattern['severity'] === 'high';
                });

                if (!empty($high_severity_patterns)) {
                    $this->log_security_event($user_id, $mcq_set_id, 'high_risk_submission', [
                        'patterns' => $high_severity_patterns,
                        'display_format' => $display_format
                    ]);

                    // For now, log but don't block - could be made stricter
                    // return new WP_Error('suspicious_submission', __('Submission contains suspicious patterns.', 'mcqhome'));
                }
            }
        }

        return true;
    }

    /**
     * Validate section submission patterns
     */
    private function validate_section_submission_patterns($config, $answers, $submission_data) {
        // Check for unrealistic section completion times
        if (isset($submission_data['section_times'])) {
            foreach ($submission_data['section_times'] as $section_id => $section_time_data) {
                $section_questions = $config['questions'][$section_id] ?? [];
                $question_count = count($section_questions);
                
                if ($question_count > 0 && isset($section_time_data['duration'])) {
                    $avg_time_per_question = $section_time_data['duration'] / $question_count;
                    
                    // Flag if less than 5 seconds per question on average
                    if ($avg_time_per_question < 5) {
                        return new WP_Error('unrealistic_section_time', 
                            sprintf(__('Unrealistic completion time for section: %s', 'mcqhome'), $section_id));
                    }
                }
            }
        }

        // Validate answer distribution across sections
        $section_answer_counts = [];
        $total_questions = 0;
        
        foreach ($config['questions'] as $section_id => $section_questions) {
            $section_start = $this->get_section_start_index($config['questions'], $section_id);
            $section_count = count($section_questions);
            $section_answers = 0;
            
            for ($i = $section_start; $i < $section_start + $section_count; $i++) {
                if (isset($answers[$i])) {
                    $section_answers++;
                }
            }
            
            $section_answer_counts[$section_id] = $section_answers;
            $total_questions += $section_count;
        }

        // Check for suspicious patterns (e.g., only answering one section)
        $answered_sections = array_filter($section_answer_counts, function($count) {
            return $count > 0;
        });

        if (count($answered_sections) === 1 && count($config['sections']) > 1) {
            // Only one section answered - could be suspicious but might be legitimate
            $this->log_security_event(0, 0, 'single_section_submission', [
                'section_counts' => $section_answer_counts,
                'answered_sections' => count($answered_sections)
            ]);
        }

        return true;
    }

    /**
     * Enhanced progress restoration for sectioned assessments
     */
    public function restore_sectioned_progress($user_id, $mcq_set_id) {
        $progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        if (!$progress) {
            return new WP_Error('no_progress', __('No saved progress found.', 'mcqhome'));
        }

        // Validate session before restoration
        $session_validation = $this->validate_sectioned_session($mcq_set_id, $user_id);
        if (is_wp_error($session_validation)) {
            return $session_validation;
        }

        // Get assessment configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        $progress_data = [
            'current_question' => $progress->current_question,
            'answers_data' => maybe_unserialize($progress->answers_data) ?: [],
            'skipped_questions' => maybe_unserialize($progress->skipped_questions) ?: [],
            'progress_percentage' => $progress->progress_percentage,
            'last_activity' => $progress->last_activity
        ];

        // Add section-specific progress data
        if ($config['sections_enabled']) {
            $progress_data['section_progress'] = $this->calculate_section_progress($config, $progress_data);
            $progress_data['section_times'] = maybe_unserialize($progress->section_times) ?: [];
            
            // Determine current section based on current question
            $current_section = $this->determine_current_section($config, $progress->current_question);
            $progress_data['current_section'] = $current_section;
        }

        // Log progress restoration
        $this->log_assessment_activity($user_id, $mcq_set_id, 'progress_restored', [
            'current_question' => $progress->current_question,
            'progress_percentage' => $progress->progress_percentage,
            'sections_enabled' => $config['sections_enabled']
        ]);

        return $progress_data;
    }

    /**
     * Determine current section based on question number
     */
    private function determine_current_section($config, $current_question) {
        if (!$config['sections_enabled'] || empty($config['sections'])) {
            return null;
        }

        $question_index = 0;
        foreach ($config['questions'] as $section_id => $section_questions) {
            $section_end = $question_index + count($section_questions);
            
            if ($current_question >= $question_index && $current_question < $section_end) {
                return $section_id;
            }
            
            $question_index = $section_end;
        }

        return null;
    }

    /**
     * Calculate remaining time for assessment
     */
    private function calculate_remaining_time($start_time, $time_limit) {
        $elapsed = time() - $start_time;
        $remaining = $time_limit - $elapsed;
        
        return max(0, $remaining);
    }



    /**
     * Validate section-specific time limits
     */
    private function validate_section_time_limits($user_id, $mcq_set_id, $config) {
        // This can be extended to support per-section time limits
        // For now, just validate overall time limit
        $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
        if ($time_limit) {
            $time_remaining = $this->get_time_remaining($user_id, $mcq_set_id);
            if ($time_remaining <= 0) {
                return new WP_Error('time_expired', __('Assessment time limit has expired.', 'mcqhome'));
            }
        }

        return true;
    }

    /**
     * Enhanced progress validation for sectioned assessments
     */
    public function validate_sectioned_progress($mcq_set_id, $progress_data, $section_id = null) {
        // Run standard progress validation
        $validation = $this->validate_progress_data($mcq_set_id, $progress_data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Get assessment configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Additional validation for sectioned assessments
        if ($config['sections_enabled'] && !empty($config['sections'])) {
            // Validate section-specific progress
            if (isset($progress_data['section_progress'])) {
                foreach ($progress_data['section_progress'] as $sec_id => $sec_progress) {
                    // Validate section exists
                    $section_exists = false;
                    foreach ($config['sections'] as $section) {
                        if ($section['id'] === $sec_id) {
                            $section_exists = true;
                            break;
                        }
                    }

                    if (!$section_exists) {
                        return new WP_Error('invalid_section_progress', sprintf(__('Invalid section in progress data: %s', 'mcqhome'), $sec_id));
                    }

                    // Validate section progress structure
                    if (!isset($sec_progress['attempted']) || !isset($sec_progress['total'])) {
                        return new WP_Error('invalid_section_structure', __('Invalid section progress structure.', 'mcqhome'));
                    }

                    // Validate section progress values
                    if ($sec_progress['attempted'] > $sec_progress['total'] || $sec_progress['attempted'] < 0) {
                        return new WP_Error('invalid_section_values', __('Invalid section progress values.', 'mcqhome'));
                    }
                }
            }

            // Validate current question is within valid range for sections
            if (isset($progress_data['current_question'])) {
                $total_questions = 0;
                foreach ($config['questions'] as $section_questions) {
                    $total_questions += count($section_questions);
                }

                if ($progress_data['current_question'] >= $total_questions) {
                    return new WP_Error('invalid_question_number', __('Current question number exceeds total questions.', 'mcqhome'));
                }
            }
        }

        return true;
    }

    /**
     * Enhanced anti-cheating measures for different assessment formats
     */
    public function detect_format_specific_cheating($user_id, $mcq_set_id, $answers, $format) {
        $cheating_indicators = [];

        // Get assessment timing data
        $timing_analysis = $this->analyze_detailed_timing($user_id, $mcq_set_id);

        if ($format === 'next_next') {
            // Next-Next format specific checks
            
            // Check for too rapid navigation between questions
            if (isset($timing_analysis['avg_question_time']) && $timing_analysis['avg_question_time'] < 2) {
                $cheating_indicators[] = [
                    'type' => 'rapid_navigation',
                    'severity' => 'high',
                    'data' => ['avg_time' => $timing_analysis['avg_question_time']]
                ];
            }

            // Check for suspicious back-and-forth navigation patterns
            $navigation_pattern = $this->analyze_navigation_pattern($user_id, $mcq_set_id);
            if ($navigation_pattern['suspicious']) {
                $cheating_indicators[] = [
                    'type' => 'suspicious_navigation',
                    'severity' => 'medium',
                    'data' => $navigation_pattern
                ];
            }

        } elseif ($format === 'single_page') {
            // Single page format specific checks
            
            // Check for rapid answer selection across multiple questions
            $rapid_selections = $this->detect_rapid_selections($user_id, $mcq_set_id);
            if ($rapid_selections['count'] > 5) {
                $cheating_indicators[] = [
                    'type' => 'rapid_selections',
                    'severity' => 'medium',
                    'data' => $rapid_selections
                ];
            }

            // Check for systematic answer patterns (filling all A's, then all B's, etc.)
            $pattern_analysis = $this->analyze_answer_patterns($answers);
            if ($pattern_analysis['systematic']) {
                $cheating_indicators[] = [
                    'type' => 'systematic_patterns',
                    'severity' => 'medium',
                    'data' => $pattern_analysis
                ];
            }
        }

        // Common checks for both formats
        
        // Check for excessive copy/paste activity
        $copy_paste_count = $this->get_copy_paste_activity($user_id, $mcq_set_id);
        if ($copy_paste_count > 10) {
            $cheating_indicators[] = [
                'type' => 'excessive_copy_paste',
                'severity' => 'high',
                'data' => ['count' => $copy_paste_count]
            ];
        }

        // Check for tab switching during assessment
        $tab_switches = $this->get_tab_switch_count($user_id, $mcq_set_id);
        if ($tab_switches > 15) {
            $cheating_indicators[] = [
                'type' => 'excessive_tab_switching',
                'severity' => 'high',
                'data' => ['count' => $tab_switches]
            ];
        }

        return $cheating_indicators;
    }

    /**
     * Analyze detailed timing patterns
     */
    private function analyze_detailed_timing($user_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        
        // Get answer timing data
        $answer_times = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_data, timestamp FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'answer_selected'
             ORDER BY timestamp ASC",
            $user_id, $mcq_set_id
        ));

        if (count($answer_times) < 2) {
            return ['avg_question_time' => 0, 'suspicious' => false];
        }

        $intervals = [];
        $previous_time = null;

        foreach ($answer_times as $answer_time) {
            $current_time = strtotime($answer_time->timestamp);
            if ($previous_time) {
                $intervals[] = $current_time - $previous_time;
            }
            $previous_time = $current_time;
        }

        $avg_interval = array_sum($intervals) / count($intervals);
        
        return [
            'avg_question_time' => $avg_interval,
            'intervals' => $intervals,
            'suspicious' => $avg_interval < 3 || count(array_filter($intervals, function($i) { return $i < 1; })) > count($intervals) * 0.3
        ];
    }

    /**
     * Analyze navigation patterns for Next-Next format
     */
    private function analyze_navigation_pattern($user_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        
        // Get navigation events
        $navigation_events = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_data, timestamp FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type IN ('question_navigation', 'next_question', 'previous_question')
             ORDER BY timestamp ASC",
            $user_id, $mcq_set_id
        ));

        $back_forth_count = 0;
        $previous_question = null;

        foreach ($navigation_events as $event) {
            $data = maybe_unserialize($event->activity_data);
            $current_question = $data['question'] ?? null;
            
            if ($previous_question !== null && $current_question !== null) {
                if (abs($current_question - $previous_question) > 3) {
                    $back_forth_count++;
                }
            }
            
            $previous_question = $current_question;
        }

        return [
            'back_forth_count' => $back_forth_count,
            'suspicious' => $back_forth_count > 20,
            'total_navigations' => count($navigation_events)
        ];
    }

    /**
     * Detect rapid selections in single page format
     */
    private function detect_rapid_selections($user_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        
        // Get answer selection events in last 30 seconds
        $rapid_selections = $wpdb->get_results($wpdb->prepare(
            "SELECT timestamp FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type = 'answer_selected'
             AND timestamp > DATE_SUB(NOW(), INTERVAL 30 SECOND)
             ORDER BY timestamp ASC",
            $user_id, $mcq_set_id
        ));

        return [
            'count' => count($rapid_selections),
            'suspicious' => count($rapid_selections) > 5
        ];
    }

    /**
     * Analyze answer patterns for systematic cheating
     */
    private function analyze_answer_patterns($answers) {
        if (empty($answers)) {
            return ['systematic' => false];
        }

        $pattern_analysis = [
            'systematic' => false,
            'patterns' => []
        ];

        // Check for runs of same answers
        $current_answer = null;
        $current_run = 0;
        $max_run = 0;
        $runs = [];

        foreach ($answers as $answer) {
            if ($answer === $current_answer) {
                $current_run++;
            } else {
                if ($current_run > 0) {
                    $runs[] = ['answer' => $current_answer, 'length' => $current_run];
                    $max_run = max($max_run, $current_run);
                }
                $current_answer = $answer;
                $current_run = 1;
            }
        }

        // Add final run
        if ($current_run > 0) {
            $runs[] = ['answer' => $current_answer, 'length' => $current_run];
            $max_run = max($max_run, $current_run);
        }

        // Check for systematic patterns
        if ($max_run > 8) { // More than 8 consecutive same answers
            $pattern_analysis['systematic'] = true;
            $pattern_analysis['patterns'][] = 'long_runs';
        }

        // Check for alternating patterns (A,B,A,B,A,B...)
        $alternating_count = 0;
        $answer_array = array_values($answers);
        for ($i = 2; $i < count($answer_array); $i++) {
            if ($answer_array[$i] === $answer_array[$i-2] && $answer_array[$i] !== $answer_array[$i-1]) {
                $alternating_count++;
            }
        }

        if ($alternating_count > count($answers) * 0.6) {
            $pattern_analysis['systematic'] = true;
            $pattern_analysis['patterns'][] = 'alternating';
        }

        return $pattern_analysis;
    }

    /**
     * Get copy/paste activity count
     */
    private function get_copy_paste_activity($user_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type IN ('copy_detected', 'paste_detected')
             AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $user_id, $mcq_set_id
        ));
    }

    /**
     * Get tab switch count
     */
    private function get_tab_switch_count($user_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_activity_log';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND mcq_set_id = %d 
             AND activity_type IN ('tab_blur', 'tab_focus', 'window_blur', 'window_focus')
             AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $user_id, $mcq_set_id
        ));
    }

    /**
     * Enhanced submission validation for sectioned assessments
     */
    public function validate_sectioned_submission($user_id, $mcq_set_id, $answers, $submission_data = []) {
        // Run standard submission validation
        $validation = $this->validate_submission($user_id, $mcq_set_id, $answers, $submission_data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Get assessment configuration
        $config = $this->get_assessment_config($mcq_set_id);
        if (is_wp_error($config)) {
            return $config;
        }

        // Additional validation for sectioned assessments
        if ($config['sections_enabled'] && !empty($config['sections'])) {
            // Validate section completion requirements if any
            $section_validation = $this->validate_section_completion_requirements($config, $answers);
            if (is_wp_error($section_validation)) {
                return $section_validation;
            }

            // Check for section-specific cheating patterns
            $format = $config['display_format'] ?? 'next_next';
            $cheating_indicators = $this->detect_format_specific_cheating($user_id, $mcq_set_id, $answers, $format);
            
            if (!empty($cheating_indicators)) {
                // Log cheating indicators
                $this->log_security_event($user_id, $mcq_set_id, 'cheating_indicators_detected', $cheating_indicators);
                
                // Check if any high severity indicators should block submission
                $high_severity_count = count(array_filter($cheating_indicators, function($indicator) {
                    return $indicator['severity'] === 'high';
                }));

                if ($high_severity_count >= 2) {
                    return new WP_Error('cheating_detected', __('Multiple security violations detected. Submission blocked.', 'mcqhome'));
                }
            }
        }

        return true;
    }

    /**
     * Validate section completion requirements
     */
    private function validate_section_completion_requirements($config, $answers) {
        // Check if minimum questions per section are answered (if required)
        $min_questions_per_section = get_post_meta($config['mcq_set_id'] ?? 0, '_mcq_set_min_questions_per_section', true);
        
        if ($min_questions_per_section) {
            foreach ($config['sections'] as $section) {
                $section_id = $section['id'];
                $section_questions = $config['questions'][$section_id] ?? [];
                $section_start_index = $this->get_section_start_index($config['questions'], $section_id);
                
                $answered_in_section = 0;
                for ($i = 0; $i < count($section_questions); $i++) {
                    $global_index = $section_start_index + $i;
                    if (isset($answers[$global_index])) {
                        $answered_in_section++;
                    }
                }

                if ($answered_in_section < $min_questions_per_section) {
                    return new WP_Error('insufficient_section_answers', 
                        sprintf(__('Section "%s" requires at least %d answered questions.', 'mcqhome'), 
                        $section['name'], $min_questions_per_section));
                }
            }
        }

        return true;
    }

    /**
     * Add security headers for assessment pages
     */
    public function add_security_headers() {
        if (is_page_template('page-take-assessment.php') || is_singular('mcq_set')) {
            // Prevent right-click context menu
            echo '<script>document.addEventListener("contextmenu", function(e) { e.preventDefault(); });</script>';
            
            // Prevent text selection
            echo '<style>body { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }</style>';
            
            // Disable F12, Ctrl+Shift+I, Ctrl+U
            echo '<script>
                document.addEventListener("keydown", function(e) {
                    if (e.key === "F12" || 
                        (e.ctrlKey && e.shiftKey && e.key === "I") ||
                        (e.ctrlKey && e.key === "u")) {
                        e.preventDefault();
                        return false;
                    }
                });
            </script>';
        }
    }

    /**
     * AJAX handler for session validation
     */
    public function ajax_validate_session() {
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mcqhome')]);
        }

        $mcq_set_id = intval($_POST['mcq_set_id']);
        $user_id = get_current_user_id();
        $current_section = sanitize_text_field($_POST['current_section'] ?? '');

        // Use enhanced sectioned validation
        $validation = $this->validate_sectioned_session($mcq_set_id, $user_id, $current_section);
        
        if (is_wp_error($validation)) {
            wp_send_json_error([
                'message' => $validation->get_error_message(),
                'code' => $validation->get_error_code()
            ]);
        }

        // Get additional session info for sectioned assessments
        $config = $this->get_assessment_config($mcq_set_id);
        $session_info = [
            'message' => __('Session valid.', 'mcqhome'),
            'sections_enabled' => $config['sections_enabled'] ?? false,
            'time_remaining' => $this->get_time_remaining($user_id, $mcq_set_id),
            'display_format' => $config['display_format'] ?? 'next_next'
        ];

        wp_send_json_success($session_info);
    }

    /**
     * AJAX handler for secure progress saving
     */
    public function ajax_save_secure_progress() {
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mcqhome')]);
        }

        $mcq_set_id = intval($_POST['mcq_set_id']);
        $user_id = get_current_user_id();
        $current_section = sanitize_text_field($_POST['current_section'] ?? '');
        
        $progress_data = [
            'current_question' => intval($_POST['current_question']),
            'answers_data' => $_POST['answers'] ?? [],
            'skipped_questions' => $_POST['skipped'] ?? [],
            'progress_percentage' => floatval($_POST['progress_percentage']),
            'current_section' => $current_section
        ];

        // Add section progress if provided
        if (isset($_POST['section_progress'])) {
            $progress_data['section_progress'] = $_POST['section_progress'];
        }

        // Use enhanced sectioned progress validation
        $validation = $this->validate_sectioned_progress($mcq_set_id, $progress_data, $current_section);
        if (is_wp_error($validation)) {
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }

        $result = $this->save_secure_progress($user_id, $mcq_set_id, $progress_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'message' => __('Progress saved securely.', 'mcqhome'),
            'timestamp' => current_time('timestamp'),
            'time_remaining' => $this->get_time_remaining($user_id, $mcq_set_id)
        ]);
    }

    /**
     * AJAX handler for assessment submission
     */
    public function ajax_submit_assessment() {
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mcqhome')]);
        }

        $mcq_set_id = intval($_POST['mcq_set_id']);
        $user_id = get_current_user_id();
        $answers = $_POST['answers'] ?? [];
        
        $submission_data = [
            'submission_method' => 'manual',
            'client_time' => $_POST['client_time'] ?? '',
            'time_taken' => intval($_POST['time_taken'] ?? 0)
        ];

        $result = $this->submit_assessment($user_id, $mcq_set_id, $answers, $submission_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'message' => __('Assessment submitted successfully.', 'mcqhome'),
            'result' => $result,
            'redirect_url' => add_query_arg(['attempt_id' => $result['attempt_id']], get_permalink(get_page_by_path('assessment-results')))
        ]);
    }

    /**
     * AJAX handler for logging user activity
     */
    public function ajax_log_activity() {
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'mcqhome')]);
        }

        $mcq_set_id = intval($_POST['mcq_set_id']);
        $user_id = get_current_user_id();
        $activity_type = sanitize_text_field($_POST['activity_type']);
        $activity_data = $_POST['activity_data'] ?? [];

        $this->log_assessment_activity($user_id, $mcq_set_id, $activity_type, $activity_data);
        
        wp_send_json_success(['message' => __('Activity logged.', 'mcqhome')]);
    }
}

// Initialize the security system
global $mcqhome_assessment_security;
$mcqhome_assessment_security = new MCQHome_Assessment_Security();

/**
 * Helper function to get security manager instance
 */
function mcqhome_get_assessment_security() {
    global $mcqhome_assessment_security;
    return $mcqhome_assessment_security;
}