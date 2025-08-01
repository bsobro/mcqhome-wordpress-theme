<?php
/**
 * AJAX Handlers for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle follow institution AJAX request
 */
function mcqhome_ajax_follow_institution() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to follow institutions.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $institution_id = intval($_POST['institution_id']);
    
    // Verify institution exists
    $institution = get_post($institution_id);
    if (!$institution || $institution->post_type !== 'institution') {
        wp_send_json_error(__('Institution not found.', 'mcqhome'));
    }
    
    // Add to following list
    $following_institutions = get_user_meta($user_id, 'following_institutions', true);
    if (!is_array($following_institutions)) {
        $following_institutions = [];
    }
    
    if (!in_array($institution_id, $following_institutions)) {
        $following_institutions[] = $institution_id;
        update_user_meta($user_id, 'following_institutions', $following_institutions);
        
        // Also add to database table
        mcqhome_add_user_follow($user_id, $institution_id, 'institution');
        
        // Log activity
        mcqhome_log_activity($user_id, 'followed', 'institution', $institution_id, [
            'institution_name' => $institution->post_title
        ]);
        
        // Create notification for institution followers (if they want to be notified)
        $institution_author_id = $institution->post_author;
        if ($institution_author_id && $institution_author_id != $user_id) {
            $follower = get_userdata($user_id);
            mcqhome_create_notification(
                $institution_author_id,
                'new_follower',
                __('New Follower', 'mcqhome'),
                sprintf(__('%s started following your institution %s', 'mcqhome'), 
                    $follower->display_name, $institution->post_title),
                [
                    'follower_id' => $user_id,
                    'institution_id' => $institution_id
                ]
            );
        }
        
        wp_send_json_success([
            'message' => __('Successfully followed institution.', 'mcqhome'),
            'action' => 'followed'
        ]);
    } else {
        wp_send_json_error(__('You are already following this institution.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_follow_institution', 'mcqhome_ajax_follow_institution');

/**
 * Handle unfollow institution AJAX request
 */
function mcqhome_ajax_unfollow_institution() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to unfollow institutions.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $institution_id = intval($_POST['institution_id']);
    
    // Remove from following list
    $following_institutions = get_user_meta($user_id, 'following_institutions', true);
    if (is_array($following_institutions)) {
        $following_institutions = array_diff($following_institutions, [$institution_id]);
        update_user_meta($user_id, 'following_institutions', $following_institutions);
        
        // Also remove from database table
        mcqhome_remove_user_follow($user_id, $institution_id, 'institution');
        
        wp_send_json_success([
            'message' => __('Successfully unfollowed institution.', 'mcqhome'),
            'action' => 'unfollowed'
        ]);
    } else {
        wp_send_json_error(__('You are not following this institution.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_unfollow_institution', 'mcqhome_ajax_unfollow_institution');

/**
 * Handle follow teacher AJAX request
 */
function mcqhome_ajax_follow_teacher() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to follow teachers.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $teacher_id = intval($_POST['teacher_id']);
    
    // Verify teacher exists and has teacher role
    $teacher = get_userdata($teacher_id);
    if (!$teacher || !in_array('teacher', $teacher->roles)) {
        wp_send_json_error(__('Teacher not found.', 'mcqhome'));
    }
    
    // Add to following list
    $following_teachers = get_user_meta($user_id, 'following_teachers', true);
    if (!is_array($following_teachers)) {
        $following_teachers = [];
    }
    
    if (!in_array($teacher_id, $following_teachers)) {
        $following_teachers[] = $teacher_id;
        update_user_meta($user_id, 'following_teachers', $following_teachers);
        
        // Also add to database table
        mcqhome_add_user_follow($user_id, $teacher_id, 'user');
        
        // Log activity
        mcqhome_log_activity($user_id, 'followed', 'user', $teacher_id, [
            'teacher_name' => $teacher->display_name
        ]);
        
        // Create notification for teacher
        if ($teacher_id != $user_id) {
            $follower = get_userdata($user_id);
            mcqhome_create_notification(
                $teacher_id,
                'new_follower',
                __('New Follower', 'mcqhome'),
                sprintf(__('%s started following you', 'mcqhome'), $follower->display_name),
                [
                    'follower_id' => $user_id
                ]
            );
        }
        
        wp_send_json_success([
            'message' => __('Successfully followed teacher.', 'mcqhome'),
            'action' => 'followed'
        ]);
    } else {
        wp_send_json_error(__('You are already following this teacher.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_follow_teacher', 'mcqhome_ajax_follow_teacher');

/**
 * Handle unfollow teacher AJAX request
 */
function mcqhome_ajax_unfollow_teacher() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to unfollow teachers.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $teacher_id = intval($_POST['teacher_id']);
    
    // Remove from following list
    $following_teachers = get_user_meta($user_id, 'following_teachers', true);
    if (is_array($following_teachers)) {
        $following_teachers = array_diff($following_teachers, [$teacher_id]);
        update_user_meta($user_id, 'following_teachers', $following_teachers);
        
        // Also remove from database table
        mcqhome_remove_user_follow($user_id, $teacher_id, 'user');
        
        wp_send_json_success([
            'message' => __('Successfully unfollowed teacher.', 'mcqhome'),
            'action' => 'unfollowed'
        ]);
    } else {
        wp_send_json_error(__('You are not following this teacher.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_unfollow_teacher', 'mcqhome_ajax_unfollow_teacher');

/**
 * Handle enroll in MCQ set AJAX request
 */
function mcqhome_ajax_enroll_mcq_set() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to enroll in MCQ sets.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $mcq_set_id = intval($_POST['mcq_set_id']);
    
    // Verify MCQ set exists
    $mcq_set = get_post($mcq_set_id);
    if (!$mcq_set || $mcq_set->post_type !== 'mcq_set') {
        wp_send_json_error(__('MCQ set not found.', 'mcqhome'));
    }
    
    // Check if already enrolled
    $enrolled_sets = get_user_meta($user_id, 'enrolled_mcq_sets', true);
    if (!is_array($enrolled_sets)) {
        $enrolled_sets = [];
    }
    
    if (!in_array($mcq_set_id, $enrolled_sets)) {
        $enrolled_sets[] = $mcq_set_id;
        update_user_meta($user_id, 'enrolled_mcq_sets', $enrolled_sets);
        
        // Also add to database table
        mcqhome_enroll_user($user_id, $mcq_set_id, 'free');
        
        wp_send_json_success([
            'message' => __('Successfully enrolled in MCQ set.', 'mcqhome'),
            'action' => 'enrolled'
        ]);
    } else {
        wp_send_json_error(__('You are already enrolled in this MCQ set.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_enroll_mcq_set', 'mcqhome_ajax_enroll_mcq_set');

/**
 * Handle get dashboard stats AJAX request
 */
function mcqhome_ajax_get_dashboard_stats() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to view dashboard stats.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $stats = mcqhome_get_student_stats($user_id);
    
    wp_send_json_success($stats);
}
add_action('wp_ajax_mcqhome_get_dashboard_stats', 'mcqhome_ajax_get_dashboard_stats');

/**
 * Handle load more content AJAX request
 */
function mcqhome_ajax_load_more_content() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to load more content.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $content_type = sanitize_text_field($_POST['content_type']);
    $page = intval($_POST['page']);
    $per_page = intval($_POST['per_page']) ?: 5;
    
    $html = '';
    
    switch ($content_type) {
        case 'enrolled_courses':
            $enrolled_sets = get_user_meta($user_id, 'enrolled_mcq_sets', true);
            if (is_array($enrolled_sets) && !empty($enrolled_sets)) {
                $offset = ($page - 1) * $per_page;
                $mcq_sets = get_posts([
                    'post_type' => 'mcq_set',
                    'post__in' => $enrolled_sets,
                    'posts_per_page' => $per_page,
                    'offset' => $offset,
                    'post_status' => 'publish'
                ]);
                
                ob_start();
                foreach ($mcq_sets as $set) {
                    // Render course item (similar to mcqhome_render_enrolled_courses)
                    $progress = mcqhome_get_user_set_progress($user_id, $set->ID);
                    $author = get_userdata($set->post_author);
                    
                    echo '<div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">';
                    echo '<h3 class="text-lg font-medium text-gray-900">';
                    echo '<a href="' . get_permalink($set->ID) . '" class="hover:text-blue-600">' . esc_html($set->post_title) . '</a>';
                    echo '</h3>';
                    echo '<p class="text-sm text-gray-500 mt-1">' . sprintf(__('by %s', 'mcqhome'), esc_html($author->display_name)) . '</p>';
                    echo '<div class="mt-3">';
                    echo '<div class="w-full bg-gray-200 rounded-full h-2">';
                    echo '<div class="bg-blue-600 h-2 rounded-full" style="width: ' . $progress['percentage'] . '%"></div>';
                    echo '</div>';
                    echo '<p class="text-xs text-gray-500 mt-1">' . $progress['percentage'] . '% complete</p>';
                    echo '</div>';
                    echo '</div>';
                }
                $html = ob_get_clean();
            }
            break;
            
        case 'recent_activity':
            global $wpdb;
            $offset = ($page - 1) * $per_page;
            $recent_attempts = $wpdb->get_results($wpdb->prepare(
                "SELECT ma.*, ms.post_title as set_title, m.post_title as mcq_title
                 FROM {$wpdb->prefix}mcq_attempts ma
                 LEFT JOIN {$wpdb->posts} ms ON ma.mcq_set_id = ms.ID
                 LEFT JOIN {$wpdb->posts} m ON ma.mcq_id = m.ID
                 WHERE ma.user_id = %d AND ma.status = 'completed'
                 ORDER BY ma.completed_at DESC
                 LIMIT %d OFFSET %d",
                $user_id, $per_page, $offset
            ));
            
            if (!empty($recent_attempts)) {
                ob_start();
                foreach ($recent_attempts as $attempt) {
                    $score_color = $attempt->score_percentage >= 70 ? 'text-green-600' : ($attempt->score_percentage >= 50 ? 'text-yellow-600' : 'text-red-600');
                    
                    echo '<div class="flex items-center justify-between py-3 border-b border-gray-200">';
                    echo '<div>';
                    echo '<p class="text-sm font-medium text-gray-900">' . esc_html($attempt->set_title ?: $attempt->mcq_title) . '</p>';
                    echo '<p class="text-xs text-gray-500">' . human_time_diff(strtotime($attempt->completed_at)) . ' ' . __('ago', 'mcqhome') . '</p>';
                    echo '</div>';
                    echo '<div class="text-sm ' . $score_color . ' font-medium">' . $attempt->score_percentage . '%</div>';
                    echo '</div>';
                }
                $html = ob_get_clean();
            }
            break;
    }
    
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_mcqhome_load_more_content', 'mcqhome_ajax_load_more_content');

/**
 * Handle MCQ auto-save AJAX request
 */
function mcqhome_ajax_autosave_mcq() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to save MCQs.', 'mcqhome'));
    }
    
    $post_id = intval($_POST['post_id']);
    
    // Check if user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(__('You do not have permission to edit this MCQ.', 'mcqhome'));
    }
    
    // Verify post type
    if (get_post_type($post_id) !== 'mcq') {
        wp_send_json_error(__('Invalid post type.', 'mcqhome'));
    }
    
    // Save the data
    $saved = true;
    
    // Save question text
    if (isset($_POST['question_text'])) {
        $saved = $saved && update_post_meta($post_id, '_mcq_question_text', wp_kses_post($_POST['question_text']));
    }
    
    // Save options
    if (isset($_POST['options']) && is_array($_POST['options'])) {
        foreach (['A', 'B', 'C', 'D'] as $option) {
            if (isset($_POST['options'][$option])) {
                $saved = $saved && update_post_meta($post_id, '_mcq_option_' . strtolower($option), sanitize_text_field($_POST['options'][$option]));
            }
        }
    }
    
    // Save correct answer
    if (isset($_POST['correct_answer']) && in_array($_POST['correct_answer'], ['A', 'B', 'C', 'D'])) {
        $saved = $saved && update_post_meta($post_id, '_mcq_correct_answer', sanitize_text_field($_POST['correct_answer']));
    }
    
    // Save explanation
    if (isset($_POST['explanation'])) {
        $saved = $saved && update_post_meta($post_id, '_mcq_explanation', wp_kses_post($_POST['explanation']));
    }
    
    // Update last modified time
    wp_update_post([
        'ID' => $post_id,
        'post_modified' => current_time('mysql'),
        'post_modified_gmt' => current_time('mysql', 1)
    ]);
    
    if ($saved) {
        wp_send_json_success([
            'message' => __('MCQ auto-saved successfully.', 'mcqhome'),
            'timestamp' => current_time('mysql')
        ]);
    } else {
        wp_send_json_error(__('Failed to auto-save MCQ.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_autosave_mcq', 'mcqhome_ajax_autosave_mcq');

/**
 * Handle media upload AJAX request
 */
function mcqhome_ajax_upload_media() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to upload media.', 'mcqhome'));
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file'])) {
        wp_send_json_error(__('No file uploaded.', 'mcqhome'));
    }
    
    $file = $_FILES['file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(__('File upload error.', 'mcqhome'));
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'audio/mp3', 'audio/wav'];
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error(__('Invalid file type.', 'mcqhome'));
    }
    
    // Check file size (10MB limit)
    if ($file['size'] > 10 * 1024 * 1024) {
        wp_send_json_error(__('File too large. Maximum size is 10MB.', 'mcqhome'));
    }
    
    // Handle the upload
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    $attachment_id = media_handle_upload('file', 0);
    
    if (is_wp_error($attachment_id)) {
        wp_send_json_error($attachment_id->get_error_message());
    }
    
    // Get attachment data
    $attachment = wp_get_attachment_metadata($attachment_id);
    $attachment_url = wp_get_attachment_url($attachment_id);
    $attachment_type = get_post_mime_type($attachment_id);
    
    // Determine media type
    $media_type = 'file';
    if (strpos($attachment_type, 'image/') === 0) {
        $media_type = 'image';
    } elseif (strpos($attachment_type, 'video/') === 0) {
        $media_type = 'video';
    } elseif (strpos($attachment_type, 'audio/') === 0) {
        $media_type = 'audio';
    }
    
    wp_send_json_success([
        'id' => $attachment_id,
        'url' => $attachment_url,
        'type' => $media_type,
        'mime' => $attachment_type,
        'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
    ]);
}
add_action('wp_ajax_mcqhome_upload_media', 'mcqhome_ajax_upload_media');

/**
 * Handle add taxonomy term AJAX request
 */
function mcqhome_ajax_add_taxonomy_term() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to add terms.', 'mcqhome'));
    }
    
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $name = sanitize_text_field($_POST['name']);
    $description = sanitize_textarea_field($_POST['description']);
    
    // Validate taxonomy
    if (!in_array($taxonomy, ['mcq_subject', 'mcq_topic'])) {
        wp_send_json_error(__('Invalid taxonomy.', 'mcqhome'));
    }
    
    // Check if user can manage terms
    if (!current_user_can('manage_categories')) {
        wp_send_json_error(__('You do not have permission to add terms.', 'mcqhome'));
    }
    
    // Check if term already exists
    if (term_exists($name, $taxonomy)) {
        wp_send_json_error(__('Term already exists.', 'mcqhome'));
    }
    
    // Create the term
    $term_data = wp_insert_term($name, $taxonomy, [
        'description' => $description
    ]);
    
    if (is_wp_error($term_data)) {
        wp_send_json_error($term_data->get_error_message());
    }
    
    // Get the created term
    $term = get_term($term_data['term_id'], $taxonomy);
    
    wp_send_json_success([
        'term_id' => $term->term_id,
        'name' => $term->name,
        'slug' => $term->slug,
        'description' => $term->description
    ]);
}
add_action('wp_ajax_mcqhome_add_taxonomy_term', 'mcqhome_ajax_add_taxonomy_term');

/**
 * Handle MCQ Set auto-save AJAX request
 */
function mcqhome_ajax_autosave_mcq_set() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to save MCQ Sets.', 'mcqhome'));
    }
    
    $post_id = intval($_POST['post_ID']);
    
    // Check if user can edit this post
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(__('You do not have permission to edit this MCQ Set.', 'mcqhome'));
    }
    
    // Verify post type
    if (get_post_type($post_id) !== 'mcq_set') {
        wp_send_json_error(__('Invalid post type.', 'mcqhome'));
    }
    
    // Save the data (similar to the save_post hook but for AJAX)
    $saved = true;
    
    // Save selected MCQs
    if (isset($_POST['mcq_set_questions']) && is_array($_POST['mcq_set_questions'])) {
        $selected_mcqs = array_map('intval', $_POST['mcq_set_questions']);
        $saved = $saved && update_post_meta($post_id, '_mcq_set_questions', $selected_mcqs);
    }
    
    // Save scoring configuration
    $scoring_fields = [
        'mcq_set_marks_per_question' => 'floatval',
        'mcq_set_negative_marking' => 'floatval',
        'mcq_set_total_marks' => 'floatval',
        'mcq_set_passing_marks' => 'floatval'
    ];
    
    foreach ($scoring_fields as $field => $sanitize_func) {
        if (isset($_POST[$field])) {
            $saved = $saved && update_post_meta($post_id, '_' . $field, $sanitize_func($_POST[$field]));
        }
    }
    
    // Save individual marks
    if (isset($_POST['mcq_set_individual_marks']) && is_array($_POST['mcq_set_individual_marks'])) {
        $individual_marks = [];
        foreach ($_POST['mcq_set_individual_marks'] as $mcq_id => $marks) {
            $individual_marks[intval($mcq_id)] = floatval($marks);
        }
        $saved = $saved && update_post_meta($post_id, '_mcq_set_individual_marks', $individual_marks);
    }
    
    // Save display format
    if (isset($_POST['mcq_set_display_format']) && in_array($_POST['mcq_set_display_format'], ['next_next', 'single_page'])) {
        $saved = $saved && update_post_meta($post_id, '_mcq_set_display_format', sanitize_text_field($_POST['mcq_set_display_format']));
    }
    
    // Save pricing
    if (isset($_POST['mcq_set_pricing_type']) && in_array($_POST['mcq_set_pricing_type'], ['free', 'paid'])) {
        $saved = $saved && update_post_meta($post_id, '_mcq_set_pricing_type', sanitize_text_field($_POST['mcq_set_pricing_type']));
    }
    
    if (isset($_POST['mcq_set_price'])) {
        $saved = $saved && update_post_meta($post_id, '_mcq_set_price', floatval($_POST['mcq_set_price']));
    }
    
    // Update last modified time
    wp_update_post([
        'ID' => $post_id,
        'post_modified' => current_time('mysql'),
        'post_modified_gmt' => current_time('mysql', 1)
    ]);
    
    if ($saved) {
        wp_send_json_success([
            'message' => __('MCQ Set auto-saved successfully.', 'mcqhome'),
            'timestamp' => current_time('mysql')
        ]);
    } else {
        wp_send_json_error(__('Failed to auto-save MCQ Set.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_autosave_mcq_set', 'mcqhome_ajax_autosave_mcq_set');

/**
 * Handle dashboard shortcode
 */
function mcqhome_dashboard_shortcode($atts) {
    $atts = shortcode_atts([
        'user_id' => get_current_user_id()
    ], $atts, 'mcqhome_dashboard');
    
    if (!is_user_logged_in()) {
        return '<p>' . __('Please log in to view your dashboard.', 'mcqhome') . '</p>';
    }
    
    ob_start();
    
    // This will be handled by the page template
    echo '<div id="mcqhome-dashboard-content">';
    echo __('Dashboard content will be loaded here.', 'mcqhome');
    echo '</div>';
    
    return ob_get_clean();
}
add_shortcode('mcqhome_dashboard', 'mcqhome_dashboard_shortcode');

/**
 * Handle save assessment progress AJAX request
 */
function mcqhome_ajax_save_assessment_progress() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
        wp_send_json_error(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to save progress.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $mcq_set_id = intval($_POST['set_id']);
    $current_question = intval($_POST['current_question']);
    $answers = $_POST['answers'] ?? [];
    $time_taken = intval($_POST['time_taken']);
    
    // Verify MCQ set exists
    $mcq_set = get_post($mcq_set_id);
    if (!$mcq_set || $mcq_set->post_type !== 'mcq_set') {
        wp_send_json_error(__('MCQ set not found.', 'mcqhome'));
    }
    
    // Check if user is enrolled
    $enrollment = mcqhome_check_user_enrollment($user_id, $mcq_set_id);
    if (!$enrollment) {
        wp_send_json_error(__('You are not enrolled in this MCQ set.', 'mcqhome'));
    }
    
    // Get MCQ set questions
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    if (empty($mcq_ids)) {
        wp_send_json_error(__('No questions found in this MCQ set.', 'mcqhome'));
    }
    
    $total_questions = count($mcq_ids);
    $answered_count = count($answers);
    $progress_percentage = ($answered_count / $total_questions) * 100;
    
    // Prepare progress data
    $progress_data = [
        'current_question' => $current_question,
        'total_questions' => $total_questions,
        'completed_questions' => array_keys($answers),
        'answers_data' => $answers,
        'progress_percentage' => $progress_percentage
    ];
    
    // Save progress
    $result = mcqhome_update_user_progress($user_id, $mcq_set_id, $progress_data);
    
    if ($result !== false) {
        wp_send_json_success([
            'message' => __('Progress saved successfully.', 'mcqhome'),
            'progress_percentage' => $progress_percentage,
            'answered_count' => $answered_count,
            'total_questions' => $total_questions
        ]);
    } else {
        wp_send_json_error(__('Failed to save progress.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_save_assessment_progress', 'mcqhome_ajax_save_assessment_progress');

/**
 * Handle submit assessment AJAX request
 */
function mcqhome_ajax_submit_assessment() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
        wp_send_json_error(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to submit assessment.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $mcq_set_id = intval($_POST['set_id']);
    $answers = $_POST['answers'] ?? [];
    $time_taken = intval($_POST['time_taken']);
    $auto_submit = isset($_POST['auto_submit']) && $_POST['auto_submit'];
    
    // Verify MCQ set exists
    $mcq_set = get_post($mcq_set_id);
    if (!$mcq_set || $mcq_set->post_type !== 'mcq_set') {
        wp_send_json_error(__('MCQ set not found.', 'mcqhome'));
    }
    
    // Check if user is enrolled
    $enrollment = mcqhome_check_user_enrollment($user_id, $mcq_set_id);
    if (!$enrollment) {
        wp_send_json_error(__('You are not enrolled in this MCQ set.', 'mcqhome'));
    }
    
    // Validate assessment submission
    $validation = mcqhome_validate_assessment_submission($user_id, $mcq_set_id, $answers);
    if (is_wp_error($validation)) {
        wp_send_json_error($validation->get_error_message());
    }
    
    // Use the new scoring engine to save assessment attempt
    $result = mcqhome_save_assessment_attempt($user_id, $mcq_set_id, $answers, $time_taken);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    // Log assessment activity
    mcqhome_log_assessment_activity($user_id, $mcq_set_id, 'assessment_submitted', [
        'total_score' => $result['total_score'],
        'score_percentage' => $result['score_percentage'],
        'is_passed' => $result['is_passed'],
        'time_taken' => $time_taken,
        'auto_submit' => $auto_submit
    ]);
    
    // Create results URL
    $results_url = add_query_arg([
        'set_id' => $mcq_set_id,
        'attempt_id' => $result['attempt_id']
    ], home_url('/assessment-results/'));
    
    wp_send_json_success([
        'message' => __('Assessment submitted successfully.', 'mcqhome'),
        'total_score' => $result['total_score'],
        'max_score' => $result['max_score'],
        'score_percentage' => $result['score_percentage'],
        'is_passed' => $result['is_passed'],
        'correct_answers' => $result['correct_answers'],
        'total_questions' => $result['total_questions'],
        'answered_questions' => $result['answered_questions'],
        'attempt_id' => $result['attempt_id'],
        'redirect_url' => $results_url
    ]);
}
add_action('wp_ajax_mcqhome_submit_assessment', 'mcqhome_ajax_submit_assessment');

/**
 * Handle get assessment results AJAX request
 */
function mcqhome_ajax_get_assessment_results() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_assessment_nonce')) {
        wp_send_json_error(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to view results.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $mcq_set_id = intval($_POST['set_id']);
    $attempt_id = isset($_POST['attempt_id']) ? intval($_POST['attempt_id']) : null;
    
    // Get detailed results
    $results = mcqhome_get_assessment_results($user_id, $mcq_set_id, $attempt_id);
    
    if (is_wp_error($results)) {
        wp_send_json_error($results->get_error_message());
    }
    
    // Get performance analytics
    $analytics = mcqhome_get_user_performance_analytics($user_id, $mcq_set_id);
    $comparison = mcqhome_get_performance_comparison($user_id, $mcq_set_id);
    
    wp_send_json_success([
        'results' => $results,
        'analytics' => $analytics,
        'comparison' => is_wp_error($comparison) ? null : $comparison
    ]);
}
add_action('wp_ajax_mcqhome_get_assessment_results', 'mcqhome_ajax_get_assessment_results');

/**
 * Handle get notifications AJAX request
 */
function mcqhome_ajax_get_notifications() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to view notifications.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
    $unread_only = isset($_POST['unread_only']) && $_POST['unread_only'];
    
    $notifications = mcqhome_get_user_notifications($user_id, $limit, $unread_only);
    $unread_count = mcqhome_get_unread_notifications_count($user_id);
    
    wp_send_json_success([
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
}
add_action('wp_ajax_mcqhome_get_notifications', 'mcqhome_ajax_get_notifications');

/**
 * Handle mark notification read AJAX request
 */
function mcqhome_ajax_mark_notification_read() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to mark notifications.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $notification_id = intval($_POST['notification_id']);
    
    $result = mcqhome_mark_notification_read($notification_id, $user_id);
    
    if ($result !== false) {
        $unread_count = mcqhome_get_unread_notifications_count($user_id);
        wp_send_json_success([
            'message' => __('Notification marked as read.', 'mcqhome'),
            'unread_count' => $unread_count
        ]);
    } else {
        wp_send_json_error(__('Failed to mark notification as read.', 'mcqhome'));
    }
}
add_action('wp_ajax_mcqhome_mark_notification_read', 'mcqhome_ajax_mark_notification_read');

/**
 * Handle get activity feed AJAX request
 */
function mcqhome_ajax_get_activity_feed() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to view activity feed.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
    
    $activities = mcqhome_get_activity_feed($user_id, $limit);
    
    // Format activities for display
    $formatted_activities = [];
    foreach ($activities as $activity) {
        $formatted_activity = [
            'id' => $activity->id,
            'user' => [
                'id' => $activity->user->ID,
                'name' => $activity->user->display_name,
                'avatar' => get_avatar_url($activity->user->ID, 32)
            ],
            'activity_type' => $activity->activity_type,
            'object_type' => $activity->object_type,
            'object_id' => $activity->object_id,
            'data' => $activity->data,
            'created_at' => $activity->created_at,
            'time_ago' => human_time_diff(strtotime($activity->created_at))
        ];
        
        // Add object details based on type
        if ($activity->object_type === 'mcq_set') {
            $mcq_set = get_post($activity->object_id);
            if ($mcq_set) {
                $formatted_activity['object'] = [
                    'title' => $mcq_set->post_title,
                    'url' => get_permalink($mcq_set->ID)
                ];
            }
        } elseif ($activity->object_type === 'mcq') {
            $mcq = get_post($activity->object_id);
            if ($mcq) {
                $formatted_activity['object'] = [
                    'title' => $mcq->post_title,
                    'url' => get_permalink($mcq->ID)
                ];
            }
        }
        
        $formatted_activities[] = $formatted_activity;
    }
    
    wp_send_json_success([
        'activities' => $formatted_activities
    ]);
}
add_action('wp_ajax_mcqhome_get_activity_feed', 'mcqhome_ajax_get_activity_feed');

/**
 * Handle get performance analytics AJAX request
 */
function mcqhome_ajax_get_performance_analytics() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_send_json_error(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to view analytics.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $mcq_set_id = isset($_POST['set_id']) ? intval($_POST['set_id']) : null;
    
    // Get performance analytics
    $analytics = mcqhome_get_user_performance_analytics($user_id, $mcq_set_id);
    
    wp_send_json_success($analytics);
}
add_action('wp_ajax_mcqhome_get_performance_analytics', 'mcqhome_ajax_get_performance_analytics');

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
 * Get user's current progress for MCQ set
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
 * Handle toggle follow AJAX request (unified for institutions and teachers)
 */
function mcqhome_ajax_toggle_follow() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_send_json_error(__('Security check failed', 'mcqhome'));
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('You must be logged in to follow.', 'mcqhome'));
    }
    
    $user_id = get_current_user_id();
    $followed_id = intval($_POST['followed_id']);
    $followed_type = sanitize_text_field($_POST['followed_type']);
    
    // Validate followed type
    if (!in_array($followed_type, ['institution', 'teacher'])) {
        wp_send_json_error(__('Invalid follow type.', 'mcqhome'));
    }
    
    // Verify the entity exists
    if ($followed_type === 'institution') {
        $entity = get_post($followed_id);
        if (!$entity || $entity->post_type !== 'institution') {
            wp_send_json_error(__('Institution not found.', 'mcqhome'));
        }
    } else {
        $entity = get_userdata($followed_id);
        if (!$entity || !in_array('teacher', $entity->roles)) {
            wp_send_json_error(__('Teacher not found.', 'mcqhome'));
        }
    }
    
    global $wpdb;
    
    // Check if already following
    $is_following = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mcq_user_follows 
         WHERE follower_id = %d AND followed_id = %d AND followed_type = %s",
        $user_id, $followed_id, $followed_type
    ));
    
    if ($is_following) {
        // Unfollow
        $result = $wpdb->delete(
            $wpdb->prefix . 'mcq_user_follows',
            [
                'follower_id' => $user_id,
                'followed_id' => $followed_id,
                'followed_type' => $followed_type
            ],
            ['%d', '%d', '%s']
        );
        
        if ($result !== false) {
            // Also update user meta
            $meta_key = $followed_type === 'institution' ? 'following_institutions' : 'following_teachers';
            $following_list = get_user_meta($user_id, $meta_key, true);
            if (is_array($following_list)) {
                $following_list = array_diff($following_list, [$followed_id]);
                update_user_meta($user_id, $meta_key, $following_list);
            }
            
            wp_send_json_success([
                'action' => 'unfollowed',
                'message' => sprintf(__('Successfully unfollowed %s.', 'mcqhome'), 
                    $followed_type === 'institution' ? __('institution', 'mcqhome') : __('teacher', 'mcqhome'))
            ]);
        } else {
            wp_send_json_error(__('Failed to unfollow. Please try again.', 'mcqhome'));
        }
    } else {
        // Follow
        $result = $wpdb->insert(
            $wpdb->prefix . 'mcq_user_follows',
            [
                'follower_id' => $user_id,
                'followed_id' => $followed_id,
                'followed_type' => $followed_type,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s']
        );
        
        if ($result !== false) {
            // Also update user meta
            $meta_key = $followed_type === 'institution' ? 'following_institutions' : 'following_teachers';
            $following_list = get_user_meta($user_id, $meta_key, true);
            if (!is_array($following_list)) {
                $following_list = [];
            }
            if (!in_array($followed_id, $following_list)) {
                $following_list[] = $followed_id;
                update_user_meta($user_id, $meta_key, $following_list);
            }
            
            wp_send_json_success([
                'action' => 'followed',
                'message' => sprintf(__('Successfully followed %s.', 'mcqhome'), 
                    $followed_type === 'institution' ? __('institution', 'mcqhome') : __('teacher', 'mcqhome'))
            ]);
        } else {
            wp_send_json_error(__('Failed to follow. Please try again.', 'mcqhome'));
        }
    }
}
add_action('wp_ajax_mcqhome_toggle_follow', 'mcqhome_ajax_toggle_follow');

/**
 * Get topics for a specific subject
 */
function mcqhome_ajax_get_subject_topics() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    $subject_slug = sanitize_text_field($_POST['subject_slug']);
    
    // Get the subject term
    $subject = get_term_by('slug', $subject_slug, 'mcq_subject');
    if (!$subject) {
        wp_send_json_error(__('Subject not found.', 'mcqhome'));
    }
    
    // Get topics related to this subject
    $topics = get_terms([
        'taxonomy' => 'mcq_topic',
        'hide_empty' => true,
        'meta_query' => [
            [
                'key' => 'parent_subject',
                'value' => $subject->term_id,
                'compare' => '='
            ]
        ],
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    
    if (is_wp_error($topics)) {
        wp_send_json_error(__('Error loading topics.', 'mcqhome'));
    }
    
    $topics_data = [];
    foreach ($topics as $topic) {
        $topics_data[] = [
            'id' => $topic->term_id,
            'slug' => $topic->slug,
            'name' => $topic->name,
            'count' => $topic->count
        ];
    }
    
    wp_send_json_success($topics_data);
}
add_action('wp_ajax_mcqhome_get_subject_topics', 'mcqhome_ajax_get_subject_topics');
add_action('wp_ajax_nopriv_mcqhome_get_subject_topics', 'mcqhome_ajax_get_subject_topics');

/**
 * Get teachers for a specific institution
 */
function mcqhome_ajax_get_institution_teachers() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    $institution_id = intval($_POST['institution_id']);
    
    // Verify institution exists
    $institution = get_post($institution_id);
    if (!$institution || $institution->post_type !== 'institution') {
        wp_send_json_error(__('Institution not found.', 'mcqhome'));
    }
    
    // Get teachers associated with this institution
    $teachers = get_users([
        'meta_key' => 'institution_id',
        'meta_value' => $institution_id,
        'orderby' => 'display_name',
        'order' => 'ASC'
    ]);
    
    $teachers_data = [];
    foreach ($teachers as $teacher) {
        $teachers_data[] = [
            'id' => $teacher->ID,
            'name' => $teacher->display_name,
            'email' => $teacher->user_email
        ];
    }
    
    wp_send_json_success($teachers_data);
}
add_action('wp_ajax_mcqhome_get_institution_teachers', 'mcqhome_ajax_get_institution_teachers');
add_action('wp_ajax_nopriv_mcqhome_get_institution_teachers', 'mcqhome_ajax_get_institution_teachers');

/**
 * Get content statistics for browse page
 */
function mcqhome_ajax_get_browse_stats() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed', 'mcqhome'));
    }
    
    global $wpdb;
    
    // Get total counts
    $stats = [
        'total_mcq_sets' => wp_count_posts('mcq_set')->publish,
        'total_mcqs' => wp_count_posts('mcq')->publish,
        'total_institutions' => wp_count_posts('institution')->publish,
        'total_subjects' => wp_count_terms(['taxonomy' => 'mcq_subject', 'hide_empty' => true]),
        'total_topics' => wp_count_terms(['taxonomy' => 'mcq_topic', 'hide_empty' => true]),
        'total_teachers' => count(get_users(['role' => 'teacher']))
    ];
    
    // Get popular subjects (top 5)
    $popular_subjects = get_terms([
        'taxonomy' => 'mcq_subject',
        'hide_empty' => true,
        'orderby' => 'count',
        'order' => 'DESC',
        'number' => 5
    ]);
    
    $stats['popular_subjects'] = [];
    foreach ($popular_subjects as $subject) {
        $stats['popular_subjects'][] = [
            'name' => $subject->name,
            'count' => $subject->count,
            'link' => get_term_link($subject)
        ];
    }
    
    wp_send_json_success($stats);
}
add_action('wp_ajax_mcqhome_get_browse_stats', 'mcqhome_ajax_get_browse_stats');
add_action('wp_ajax_nopriv_mcqhome_get_browse_stats', 'mcqhome_ajax_get_browse_stats');