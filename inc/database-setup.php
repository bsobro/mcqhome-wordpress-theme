<?php
/**
 * Database Setup for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create custom database tables
 */
function mcqhome_create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // MCQ Attempts table
    $table_name = $wpdb->prefix . 'mcq_attempts';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) DEFAULT NULL,
        selected_answer varchar(1) NOT NULL,
        correct_answer varchar(1) NOT NULL,
        is_correct tinyint(1) NOT NULL DEFAULT 0,
        score_points decimal(5,2) NOT NULL DEFAULT 0,
        negative_points decimal(5,2) NOT NULL DEFAULT 0,
        time_taken int(11) DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'in_progress',
        started_at datetime DEFAULT CURRENT_TIMESTAMP,
        completed_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY mcq_id (mcq_id),
        KEY mcq_set_id (mcq_set_id),
        KEY status (status),
        KEY completed_at (completed_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // MCQ Set Attempts table
    $table_name = $wpdb->prefix . 'mcq_set_attempts';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) NOT NULL,
        total_questions int(11) NOT NULL DEFAULT 0,
        answered_questions int(11) NOT NULL DEFAULT 0,
        correct_answers int(11) NOT NULL DEFAULT 0,
        total_score decimal(8,2) NOT NULL DEFAULT 0,
        max_score decimal(8,2) NOT NULL DEFAULT 0,
        score_percentage decimal(5,2) NOT NULL DEFAULT 0,
        passing_score decimal(5,2) NOT NULL DEFAULT 0,
        is_passed tinyint(1) NOT NULL DEFAULT 0,
        time_limit int(11) DEFAULT NULL,
        time_taken int(11) DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'in_progress',
        started_at datetime DEFAULT CURRENT_TIMESTAMP,
        completed_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY mcq_set_id (mcq_set_id),
        KEY status (status),
        KEY completed_at (completed_at),
        UNIQUE KEY user_set_attempt (user_id, mcq_set_id, started_at)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // User Progress table
    $table_name = $wpdb->prefix . 'mcq_user_progress';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) NOT NULL,
        current_question int(11) NOT NULL DEFAULT 0,
        total_questions int(11) NOT NULL DEFAULT 0,
        completed_questions text,
        answers_data longtext,
        progress_percentage decimal(5,2) NOT NULL DEFAULT 0,
        last_activity datetime DEFAULT CURRENT_TIMESTAMP,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY mcq_set_id (mcq_set_id),
        KEY last_activity (last_activity),
        UNIQUE KEY user_set_progress (user_id, mcq_set_id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // User Follows table
    $table_name = $wpdb->prefix . 'mcq_user_follows';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        follower_id bigint(20) NOT NULL,
        followed_id bigint(20) NOT NULL,
        followed_type varchar(20) NOT NULL DEFAULT 'user',
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY follower_id (follower_id),
        KEY followed_id (followed_id),
        KEY followed_type (followed_type),
        KEY status (status),
        UNIQUE KEY unique_follow (follower_id, followed_id, followed_type)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // User Enrollments table
    $table_name = $wpdb->prefix . 'mcq_user_enrollments';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        mcq_set_id bigint(20) NOT NULL,
        enrollment_type varchar(20) NOT NULL DEFAULT 'free',
        payment_status varchar(20) NOT NULL DEFAULT 'completed',
        enrolled_at datetime DEFAULT CURRENT_TIMESTAMP,
        expires_at datetime DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY mcq_set_id (mcq_set_id),
        KEY status (status),
        KEY enrolled_at (enrolled_at),
        UNIQUE KEY user_set_enrollment (user_id, mcq_set_id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // User Notifications table
    $table_name = $wpdb->prefix . 'mcq_user_notifications';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        type varchar(50) NOT NULL,
        title varchar(255) NOT NULL,
        message text NOT NULL,
        data longtext,
        is_read tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        read_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY type (type),
        KEY is_read (is_read),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Activity Stream table
    $table_name = $wpdb->prefix . 'mcq_activity_stream';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        activity_type varchar(50) NOT NULL,
        object_type varchar(50) NOT NULL,
        object_id bigint(20) NOT NULL,
        data longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY activity_type (activity_type),
        KEY object_type (object_type),
        KEY object_id (object_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Update database version
    update_option('mcqhome_db_version', '1.0.0');
}

/**
 * Check if database tables need to be created or updated
 */
function mcqhome_check_database_version() {
    $installed_version = get_option('mcqhome_db_version', '0.0.0');
    $current_version = '1.0.0';
    
    if (version_compare($installed_version, $current_version, '<')) {
        mcqhome_create_database_tables();
    }
}

/**
 * Initialize database on theme activation
 */
function mcqhome_init_database() {
    mcqhome_check_database_version();
}

// Hook into theme activation
add_action('after_switch_theme', 'mcqhome_init_database');

// Check database version on admin init
add_action('admin_init', 'mcqhome_check_database_version');

// Hook into post publishing to log activities
add_action('transition_post_status', 'mcqhome_log_content_activity', 10, 3);

/**
 * Log activity when content is published
 */
function mcqhome_log_content_activity($new_status, $old_status, $post) {
    // Only log when post is published for the first time
    if ($new_status !== 'publish' || $old_status === 'publish') {
        return;
    }
    
    // Only log for MCQ and MCQ Set post types
    if (!in_array($post->post_type, ['mcq', 'mcq_set'])) {
        return;
    }
    
    $activity_type = $post->post_type === 'mcq' ? 'created_mcq' : 'created_mcq_set';
    
    // Log the activity
    mcqhome_log_activity($post->post_author, $activity_type, $post->post_type, $post->ID, [
        'title' => $post->post_title
    ]);
    
    // Notify followers
    mcqhome_notify_followers_of_new_content($post->post_author, $post);
}

/**
 * Notify followers when new content is published
 */
function mcqhome_notify_followers_of_new_content($author_id, $post) {
    global $wpdb;
    
    // Get followers of this user
    $followers = $wpdb->get_results($wpdb->prepare(
        "SELECT follower_id FROM {$wpdb->prefix}mcq_user_follows 
         WHERE followed_id = %d AND followed_type = 'user' AND status = 'active'",
        $author_id
    ));
    
    // Get followers of author's institution
    $institution_id = get_user_meta($author_id, 'institution_id', true);
    if ($institution_id) {
        $institution_followers = $wpdb->get_results($wpdb->prepare(
            "SELECT follower_id FROM {$wpdb->prefix}mcq_user_follows 
             WHERE followed_id = %d AND followed_type = 'institution' AND status = 'active'",
            $institution_id
        ));
        $followers = array_merge($followers, $institution_followers);
    }
    
    if (empty($followers)) {
        return;
    }
    
    $author = get_userdata($author_id);
    $content_type = $post->post_type === 'mcq' ? __('MCQ', 'mcqhome') : __('MCQ Set', 'mcqhome');
    
    // Create notifications for all followers
    foreach ($followers as $follower) {
        // Don't notify the author themselves
        if ($follower->follower_id == $author_id) {
            continue;
        }
        
        mcqhome_create_notification(
            $follower->follower_id,
            'new_content',
            sprintf(__('New %s from %s', 'mcqhome'), $content_type, $author->display_name),
            sprintf(__('%s published a new %s: %s', 'mcqhome'), 
                $author->display_name, strtolower($content_type), $post->post_title),
            [
                'author_id' => $author_id,
                'post_id' => $post->ID,
                'post_type' => $post->post_type,
                'post_url' => get_permalink($post->ID)
            ]
        );
    }
}

/**
 * Helper functions for database operations
 */

/**
 * Record MCQ attempt
 */
function mcqhome_record_mcq_attempt($user_id, $mcq_id, $mcq_set_id, $selected_answer, $correct_answer, $score_points = 0, $negative_points = 0) {
    global $wpdb;
    
    $is_correct = ($selected_answer === $correct_answer) ? 1 : 0;
    $final_score = $is_correct ? $score_points : -$negative_points;
    
    return $wpdb->insert(
        $wpdb->prefix . 'mcq_attempts',
        [
            'user_id' => $user_id,
            'mcq_id' => $mcq_id,
            'mcq_set_id' => $mcq_set_id,
            'selected_answer' => $selected_answer,
            'correct_answer' => $correct_answer,
            'is_correct' => $is_correct,
            'score_points' => $final_score,
            'negative_points' => $negative_points,
            'status' => 'completed',
            'completed_at' => current_time('mysql')
        ],
        ['%d', '%d', '%d', '%s', '%s', '%d', '%f', '%f', '%s', '%s']
    );
}

/**
 * Update MCQ set attempt
 */
function mcqhome_update_set_attempt($user_id, $mcq_set_id, $data) {
    global $wpdb;
    
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_set_attempts 
         WHERE user_id = %d AND mcq_set_id = %d AND status = 'in_progress'
         ORDER BY started_at DESC LIMIT 1",
        $user_id, $mcq_set_id
    ));
    
    if ($existing) {
        return $wpdb->update(
            $wpdb->prefix . 'mcq_set_attempts',
            $data,
            ['id' => $existing->id],
            null,
            ['%d']
        );
    } else {
        $data['user_id'] = $user_id;
        $data['mcq_set_id'] = $mcq_set_id;
        return $wpdb->insert(
            $wpdb->prefix . 'mcq_set_attempts',
            $data
        );
    }
}

/**
 * Update user progress
 */
function mcqhome_update_user_progress($user_id, $mcq_set_id, $progress_data) {
    global $wpdb;
    
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_user_progress 
         WHERE user_id = %d AND mcq_set_id = %d",
        $user_id, $mcq_set_id
    ));
    
    $data = [
        'current_question' => $progress_data['current_question'] ?? 0,
        'total_questions' => $progress_data['total_questions'] ?? 0,
        'completed_questions' => maybe_serialize($progress_data['completed_questions'] ?? []),
        'answers_data' => maybe_serialize($progress_data['answers_data'] ?? []),
        'progress_percentage' => $progress_data['progress_percentage'] ?? 0,
        'last_activity' => current_time('mysql')
    ];
    
    if ($existing) {
        return $wpdb->update(
            $wpdb->prefix . 'mcq_user_progress',
            $data,
            ['id' => $existing->id],
            null,
            ['%d']
        );
    } else {
        $data['user_id'] = $user_id;
        $data['mcq_set_id'] = $mcq_set_id;
        return $wpdb->insert(
            $wpdb->prefix . 'mcq_user_progress',
            $data
        );
    }
}

/**
 * Add user follow relationship
 */
function mcqhome_add_user_follow($follower_id, $followed_id, $followed_type = 'user') {
    global $wpdb;
    
    return $wpdb->replace(
        $wpdb->prefix . 'mcq_user_follows',
        [
            'follower_id' => $follower_id,
            'followed_id' => $followed_id,
            'followed_type' => $followed_type,
            'status' => 'active'
        ],
        ['%d', '%d', '%s', '%s']
    );
}

/**
 * Remove user follow relationship
 */
function mcqhome_remove_user_follow($follower_id, $followed_id, $followed_type = 'user') {
    global $wpdb;
    
    return $wpdb->delete(
        $wpdb->prefix . 'mcq_user_follows',
        [
            'follower_id' => $follower_id,
            'followed_id' => $followed_id,
            'followed_type' => $followed_type
        ],
        ['%d', '%d', '%s']
    );
}

/**
 * Enroll user in MCQ set
 */
function mcqhome_enroll_user($user_id, $mcq_set_id, $enrollment_type = 'free') {
    global $wpdb;
    
    return $wpdb->replace(
        $wpdb->prefix . 'mcq_user_enrollments',
        [
            'user_id' => $user_id,
            'mcq_set_id' => $mcq_set_id,
            'enrollment_type' => $enrollment_type,
            'payment_status' => 'completed',
            'status' => 'active'
        ],
        ['%d', '%d', '%s', '%s', '%s']
    );
}

/**
 * Get user's enrolled MCQ sets
 */
function mcqhome_get_user_enrollments($user_id) {
    global $wpdb;
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT mcq_set_id FROM {$wpdb->prefix}mcq_user_enrollments 
         WHERE user_id = %d AND status = 'active'",
        $user_id
    ));
}

/**
 * Get user's following list
 */
function mcqhome_get_user_following($user_id, $followed_type = null) {
    global $wpdb;
    
    $sql = "SELECT followed_id, followed_type FROM {$wpdb->prefix}mcq_user_follows 
            WHERE follower_id = %d AND status = 'active'";
    $params = [$user_id];
    
    if ($followed_type) {
        $sql .= " AND followed_type = %s";
        $params[] = $followed_type;
    }
    
    return $wpdb->get_results($wpdb->prepare($sql, $params));
}

/**
 * Check if user is enrolled in MCQ set
 */
function mcqhome_check_user_enrollment($user_id, $mcq_set_id) {
    global $wpdb;
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_user_enrollments 
         WHERE user_id = %d AND mcq_set_id = %d AND status = 'active'",
        $user_id, $mcq_set_id
    ));
}

/**
 * Get user progress for MCQ set
 */
function mcqhome_get_user_progress($user_id, $mcq_set_id) {
    global $wpdb;
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mcq_user_progress 
         WHERE user_id = %d AND mcq_set_id = %d",
        $user_id, $mcq_set_id
    ));
}

/**
 * Create notification for user
 */
function mcqhome_create_notification($user_id, $type, $title, $message, $data = []) {
    global $wpdb;
    
    return $wpdb->insert(
        $wpdb->prefix . 'mcq_user_notifications',
        [
            'user_id' => $user_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => maybe_serialize($data),
            'is_read' => 0
        ],
        ['%d', '%s', '%s', '%s', '%s', '%d']
    );
}

/**
 * Get user notifications
 */
function mcqhome_get_user_notifications($user_id, $limit = 10, $unread_only = false) {
    global $wpdb;
    
    $sql = "SELECT * FROM {$wpdb->prefix}mcq_user_notifications 
            WHERE user_id = %d";
    $params = [$user_id];
    
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT %d";
    $params[] = $limit;
    
    $notifications = $wpdb->get_results($wpdb->prepare($sql, $params));
    
    // Unserialize data
    foreach ($notifications as $notification) {
        $notification->data = maybe_unserialize($notification->data);
    }
    
    return $notifications;
}

/**
 * Mark notification as read
 */
function mcqhome_mark_notification_read($notification_id, $user_id = null) {
    global $wpdb;
    
    $where = ['id' => $notification_id];
    if ($user_id) {
        $where['user_id'] = $user_id;
    }
    
    return $wpdb->update(
        $wpdb->prefix . 'mcq_user_notifications',
        [
            'is_read' => 1,
            'read_at' => current_time('mysql')
        ],
        $where,
        ['%d', '%s'],
        ['%d', '%d']
    );
}

/**
 * Log activity to stream
 */
function mcqhome_log_activity($user_id, $activity_type, $object_type, $object_id, $data = []) {
    global $wpdb;
    
    return $wpdb->insert(
        $wpdb->prefix . 'mcq_activity_stream',
        [
            'user_id' => $user_id,
            'activity_type' => $activity_type,
            'object_type' => $object_type,
            'object_id' => $object_id,
            'data' => maybe_serialize($data)
        ],
        ['%d', '%s', '%s', '%d', '%s']
    );
}

/**
 * Get activity stream for followed users
 */
function mcqhome_get_activity_feed($user_id, $limit = 20) {
    global $wpdb;
    
    // Get followed users
    $following = mcqhome_get_user_following($user_id);
    
    if (empty($following)) {
        return [];
    }
    
    $followed_user_ids = [];
    $followed_institution_ids = [];
    
    foreach ($following as $follow) {
        if ($follow->followed_type === 'user') {
            $followed_user_ids[] = $follow->followed_id;
        } elseif ($follow->followed_type === 'institution') {
            $followed_institution_ids[] = $follow->followed_id;
        }
    }
    
    // Get users associated with followed institutions
    if (!empty($followed_institution_ids)) {
        $institution_users = get_users([
            'meta_key' => 'institution_id',
            'meta_value' => $followed_institution_ids,
            'meta_compare' => 'IN',
            'fields' => 'ID'
        ]);
        $followed_user_ids = array_merge($followed_user_ids, $institution_users);
    }
    
    if (empty($followed_user_ids)) {
        return [];
    }
    
    $user_ids_placeholder = implode(',', array_fill(0, count($followed_user_ids), '%d'));
    
    $sql = "SELECT * FROM {$wpdb->prefix}mcq_activity_stream 
            WHERE user_id IN ($user_ids_placeholder)
            ORDER BY created_at DESC 
            LIMIT %d";
    
    $params = array_merge($followed_user_ids, [$limit]);
    
    $activities = $wpdb->get_results($wpdb->prepare($sql, $params));
    
    // Unserialize data and add user info
    foreach ($activities as $activity) {
        $activity->data = maybe_unserialize($activity->data);
        $activity->user = get_userdata($activity->user_id);
    }
    
    return $activities;
}

/**
 * Get unread notifications count
 */
function mcqhome_get_unread_notifications_count($user_id) {
    global $wpdb;
    
    return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mcq_user_notifications 
         WHERE user_id = %d AND is_read = 0",
        $user_id
    ));
}