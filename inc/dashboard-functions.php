<?php
/**
 * Dashboard Functions for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render Student Dashboard
 */
function mcqhome_render_student_dashboard($user_id) {
    ?>
    <div class="student-dashboard">
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php
            $stats = mcqhome_get_student_stats($user_id);
            ?>
            
            <!-- Enrolled Courses -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Enrolled Courses', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['enrolled_courses']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Completed MCQs -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Completed MCQs', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['completed_mcqs']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Average Score -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Average Score', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['average_score']; ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Following -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Following', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['following_count']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Enrolled Courses Overview -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('My Enrolled Courses', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_enrolled_courses($user_id); ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Recent Activity', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_recent_activity($user_id); ?>
                    </div>
                </div>

                <!-- Progress Tracking -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Progress Overview', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_progress_tracking($user_id); ?>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                
                <!-- Personalized Content Feed -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Latest from Following', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_personalized_feed($user_id); ?>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Notifications', 'mcqhome'); ?></h2>
                        <span class="notification-count bg-red-500 text-white text-xs rounded-full px-2 py-1 hidden">0</span>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_notifications($user_id); ?>
                    </div>
                </div>

                <!-- Following Management -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Following', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_following_management($user_id); ?>
                    </div>
                </div>

                <!-- Performance Analytics -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Performance Analytics', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_performance_analytics($user_id); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php
}

/**
 * Get student statistics
 */
function mcqhome_get_student_stats($user_id) {
    global $wpdb;
    
    // Get enrolled courses count
    $enrolled_courses = get_user_meta($user_id, 'enrolled_mcq_sets', true);
    $enrolled_courses = is_array($enrolled_courses) ? count($enrolled_courses) : 0;
    
    // Get completed MCQs count
    $completed_mcqs = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mcq_attempts WHERE user_id = %d AND status = 'completed'",
        $user_id
    ));
    
    // Get average score
    $average_score = $wpdb->get_var($wpdb->prepare(
        "SELECT AVG(score_percentage) FROM {$wpdb->prefix}mcq_attempts WHERE user_id = %d AND status = 'completed'",
        $user_id
    ));
    
    // Get following count
    $following_institutions = get_user_meta($user_id, 'following_institutions', true);
    $following_teachers = get_user_meta($user_id, 'following_teachers', true);
    $following_count = 0;
    if (is_array($following_institutions)) $following_count += count($following_institutions);
    if (is_array($following_teachers)) $following_count += count($following_teachers);
    
    return [
        'enrolled_courses' => intval($enrolled_courses),
        'completed_mcqs' => intval($completed_mcqs ?: 0),
        'average_score' => round(floatval($average_score ?: 0), 1),
        'following_count' => intval($following_count)
    ];
}

/**
 * Render enrolled courses section
 */
function mcqhome_render_enrolled_courses($user_id) {
    $enrolled_sets = get_user_meta($user_id, 'enrolled_mcq_sets', true);
    
    if (!is_array($enrolled_sets) || empty($enrolled_sets)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No enrolled courses', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Start by browsing and enrolling in MCQ sets.', 'mcqhome') . '</p>';
        echo '<div class="mt-6">';
        echo '<a href="' . home_url('/browse/') . '" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">';
        echo __('Browse MCQ Sets', 'mcqhome');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        return;
    }
    
    $mcq_sets = get_posts([
        'post_type' => 'mcq_set',
        'post__in' => $enrolled_sets,
        'posts_per_page' => 5,
        'post_status' => 'publish'
    ]);
    
    if (empty($mcq_sets)) {
        echo '<p class="text-gray-500">' . __('No active enrolled courses found.', 'mcqhome') . '</p>';
        return;
    }
    
    echo '<div class="space-y-4">';
    foreach ($mcq_sets as $set) {
        $progress = mcqhome_get_user_set_progress($user_id, $set->ID);
        $author = get_userdata($set->post_author);
        $institution_id = get_post_meta($set->ID, 'institution_id', true);
        $institution = $institution_id ? get_post($institution_id) : null;
        
        echo '<div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">';
        echo '<div class="flex items-start justify-between">';
        echo '<div class="flex-1">';
        echo '<h3 class="text-lg font-medium text-gray-900">';
        echo '<a href="' . get_permalink($set->ID) . '" class="hover:text-blue-600">' . esc_html($set->post_title) . '</a>';
        echo '</h3>';
        
        echo '<div class="mt-1 text-sm text-gray-500">';
        if ($institution) {
            echo '<span class="inline-flex items-center">';
            echo '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>';
            echo '</svg>';
            echo esc_html($institution->post_title);
            echo '</span>';
            echo ' • ';
        }
        echo '<span>' . sprintf(__('by %s', 'mcqhome'), esc_html($author->display_name)) . '</span>';
        echo '</div>';
        
        // Progress bar
        echo '<div class="mt-3">';
        echo '<div class="flex items-center justify-between text-sm">';
        echo '<span class="text-gray-600">' . __('Progress', 'mcqhome') . '</span>';
        echo '<span class="font-medium">' . $progress['percentage'] . '%</span>';
        echo '</div>';
        echo '<div class="mt-1 w-full bg-gray-200 rounded-full h-2">';
        echo '<div class="bg-blue-600 h-2 rounded-full" style="width: ' . $progress['percentage'] . '%"></div>';
        echo '</div>';
        echo '<div class="mt-1 text-xs text-gray-500">';
        echo sprintf(__('%d of %d questions completed', 'mcqhome'), $progress['completed'], $progress['total']);
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Action buttons
        echo '<div class="flex-shrink-0 ml-4">';
        if ($progress['percentage'] > 0) {
            echo '<a href="' . get_permalink($set->ID) . '?action=continue" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">';
            echo __('Continue', 'mcqhome');
            echo '</a>';
        } else {
            echo '<a href="' . get_permalink($set->ID) . '?action=start" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-600 bg-blue-100 hover:bg-blue-200">';
            echo __('Start', 'mcqhome');
            echo '</a>';
        }
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    
    // View all link
    if (count($enrolled_sets) > 5) {
        echo '<div class="mt-4 text-center">';
        echo '<a href="' . home_url('/dashboard/?tab=courses') . '" class="text-blue-600 hover:text-blue-500 text-sm font-medium">';
        echo __('View all enrolled courses', 'mcqhome');
        echo '</a>';
        echo '</div>';
    }
}

/**
 * Get user progress for a specific MCQ set
 */
function mcqhome_get_user_set_progress($user_id, $set_id) {
    global $wpdb;
    
    // Get total questions in set
    $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
    $total_questions = is_array($mcq_ids) ? count($mcq_ids) : 0;
    
    if ($total_questions === 0) {
        return ['completed' => 0, 'total' => 0, 'percentage' => 0];
    }
    
    // Get completed questions
    $completed = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}mcq_attempts 
         WHERE user_id = %d AND mcq_set_id = %d AND status = 'completed'",
        $user_id, $set_id
    ));
    
    $completed = intval($completed ?: 0);
    $percentage = $total_questions > 0 ? round(($completed / $total_questions) * 100, 1) : 0;
    
    return [
        'completed' => $completed,
        'total' => $total_questions,
        'percentage' => $percentage
    ];
}

/**
 * Render recent activity section
 */
function mcqhome_render_recent_activity($user_id) {
    global $wpdb;
    
    // Get recent attempts
    $recent_attempts = $wpdb->get_results($wpdb->prepare(
        "SELECT ma.*, ms.post_title as set_title, m.post_title as mcq_title
         FROM {$wpdb->prefix}mcq_attempts ma
         LEFT JOIN {$wpdb->posts} ms ON ma.mcq_set_id = ms.ID
         LEFT JOIN {$wpdb->posts} m ON ma.mcq_id = m.ID
         WHERE ma.user_id = %d AND ma.status = 'completed'
         ORDER BY ma.completed_at DESC
         LIMIT 10",
        $user_id
    ));
    
    if (empty($recent_attempts)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No recent activity', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Start taking MCQs to see your activity here.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="flow-root">';
    echo '<ul class="-mb-8">';
    
    foreach ($recent_attempts as $index => $attempt) {
        $is_last = ($index === count($recent_attempts) - 1);
        $score_color = $attempt->score_percentage >= 70 ? 'text-green-600' : ($attempt->score_percentage >= 50 ? 'text-yellow-600' : 'text-red-600');
        
        echo '<li>';
        echo '<div class="relative pb-8">';
        
        if (!$is_last) {
            echo '<span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>';
        }
        
        echo '<div class="relative flex space-x-3">';
        echo '<div>';
        echo '<span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">';
        echo '<svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">';
        echo '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>';
        echo '</svg>';
        echo '</span>';
        echo '</div>';
        
        echo '<div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">';
        echo '<div>';
        echo '<p class="text-sm text-gray-500">';
        echo sprintf(__('Completed %s', 'mcqhome'), '<span class="font-medium text-gray-900">' . esc_html($attempt->set_title ?: $attempt->mcq_title) . '</span>');
        echo '</p>';
        echo '<p class="text-sm ' . $score_color . ' font-medium">';
        echo sprintf(__('Score: %s%%', 'mcqhome'), $attempt->score_percentage);
        echo '</p>';
        echo '</div>';
        echo '<div class="text-right text-sm whitespace-nowrap text-gray-500">';
        echo '<time datetime="' . $attempt->completed_at . '">' . human_time_diff(strtotime($attempt->completed_at)) . ' ' . __('ago', 'mcqhome') . '</time>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</li>';
    }
    
    echo '</ul>';
    echo '</div>';
}

/**
 * Render progress tracking section
 */
function mcqhome_render_progress_tracking($user_id) {
    global $wpdb;
    
    // Get progress data for the last 30 days
    $progress_data = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE(completed_at) as date, AVG(score_percentage) as avg_score, COUNT(*) as attempts
         FROM {$wpdb->prefix}mcq_attempts 
         WHERE user_id = %d AND status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY DATE(completed_at)
         ORDER BY date DESC
         LIMIT 10",
        $user_id
    ));
    
    if (empty($progress_data)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No progress data', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Complete some MCQs to see your progress tracking.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    // Simple progress visualization
    echo '<div class="space-y-4">';
    
    foreach (array_reverse($progress_data) as $data) {
        $score_color = $data->avg_score >= 70 ? 'bg-green-500' : ($data->avg_score >= 50 ? 'bg-yellow-500' : 'bg-red-500');
        
        echo '<div class="flex items-center justify-between">';
        echo '<div class="flex items-center space-x-3">';
        echo '<div class="text-sm font-medium text-gray-900">' . date_i18n('M j', strtotime($data->date)) . '</div>';
        echo '<div class="flex-1 bg-gray-200 rounded-full h-2 w-32">';
        echo '<div class="' . $score_color . ' h-2 rounded-full" style="width: ' . $data->avg_score . '%"></div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="text-sm text-gray-500">';
        echo sprintf(__('%d attempts, %s%% avg', 'mcqhome'), $data->attempts, round($data->avg_score, 1));
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Overall stats
    $overall_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) as total_attempts, AVG(score_percentage) as overall_avg, MAX(score_percentage) as best_score
         FROM {$wpdb->prefix}mcq_attempts 
         WHERE user_id = %d AND status = 'completed'",
        $user_id
    ));
    
    if ($overall_stats && $overall_stats->total_attempts > 0) {
        echo '<div class="mt-6 pt-6 border-t border-gray-200">';
        echo '<h4 class="text-sm font-medium text-gray-900 mb-3">' . __('Overall Statistics', 'mcqhome') . '</h4>';
        echo '<div class="grid grid-cols-3 gap-4 text-center">';
        
        echo '<div>';
        echo '<div class="text-2xl font-semibold text-gray-900">' . $overall_stats->total_attempts . '</div>';
        echo '<div class="text-xs text-gray-500">' . __('Total Attempts', 'mcqhome') . '</div>';
        echo '</div>';
        
        echo '<div>';
        echo '<div class="text-2xl font-semibold text-gray-900">' . round($overall_stats->overall_avg, 1) . '%</div>';
        echo '<div class="text-xs text-gray-500">' . __('Average Score', 'mcqhome') . '</div>';
        echo '</div>';
        
        echo '<div>';
        echo '<div class="text-2xl font-semibold text-gray-900">' . round($overall_stats->best_score, 1) . '%</div>';
        echo '<div class="text-xs text-gray-500">' . __('Best Score', 'mcqhome') . '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Render personalized content feed
 */
function mcqhome_render_personalized_feed($user_id) {
    // Get activity feed using the new function
    $activities = mcqhome_get_activity_feed($user_id, 5);
    
    if (empty($activities)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No activity from followed accounts', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Follow institutions and teachers to see their latest activity here.', 'mcqhome') . '</p>';
        echo '<div class="mt-6">';
        echo '<a href="' . home_url('/institutions/') . '" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">';
        echo __('Browse Institutions', 'mcqhome');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-4" id="activity-feed-container">';
    foreach ($activities as $activity) {
        mcqhome_render_activity_item($activity);
    }
    echo '</div>';
    
    echo '<div class="mt-4 text-center">';
    echo '<button class="load-more-activity-btn text-blue-600 hover:text-blue-500 text-sm font-medium" data-user-id="' . $user_id . '" data-page="1">';
    echo __('Load more activity', 'mcqhome');
    echo '</button>';
    echo '</div>';
}

/**
 * Render individual activity item
 */
function mcqhome_render_activity_item($activity) {
    $activity_icon = '';
    $activity_text = '';
    $activity_color = 'border-blue-500';
    
    switch ($activity->activity_type) {
        case 'created_mcq':
            $activity_icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
            $activity_text = sprintf(__('created a new MCQ', 'mcqhome'));
            $activity_color = 'border-green-500';
            break;
        case 'created_mcq_set':
            $activity_icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>';
            $activity_text = sprintf(__('created a new MCQ Set', 'mcqhome'));
            $activity_color = 'border-purple-500';
            break;
        case 'followed':
            $activity_icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>';
            $follow_target = $activity->object_type === 'institution' ? 
                (isset($activity->data['institution_name']) ? $activity->data['institution_name'] : __('an institution', 'mcqhome')) :
                (isset($activity->data['teacher_name']) ? $activity->data['teacher_name'] : __('a teacher', 'mcqhome'));
            $activity_text = sprintf(__('started following %s', 'mcqhome'), $follow_target);
            $activity_color = 'border-blue-500';
            break;
        default:
            $activity_icon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            $activity_text = $activity->activity_type;
            break;
    }
    
    echo '<div class="border-l-4 ' . $activity_color . ' pl-4 py-2">';
    echo '<div class="flex items-start space-x-3">';
    
    // User avatar
    echo '<div class="flex-shrink-0">';
    echo '<img class="h-8 w-8 rounded-full" src="' . get_avatar_url($activity->user->ID, 32) . '" alt="' . esc_attr($activity->user->display_name) . '">';
    echo '</div>';
    
    echo '<div class="flex-1 min-w-0">';
    echo '<div class="flex items-center space-x-2">';
    echo '<span class="text-gray-600">' . $activity_icon . '</span>';
    echo '<p class="text-sm text-gray-900">';
    echo '<span class="font-medium">' . esc_html($activity->user->display_name) . '</span> ';
    echo $activity_text;
    echo '</p>';
    echo '</div>';
    
    // Show object title if available
    if (in_array($activity->activity_type, ['created_mcq', 'created_mcq_set']) && isset($activity->data['title'])) {
        $object = get_post($activity->object_id);
        if ($object) {
            echo '<p class="text-sm text-gray-600 mt-1">';
            echo '<a href="' . get_permalink($object->ID) . '" class="hover:text-blue-600 font-medium">';
            echo esc_html($activity->data['title']);
            echo '</a>';
            echo '</p>';
        }
    }
    
    echo '<p class="text-xs text-gray-500 mt-1">';
    echo human_time_diff(strtotime($activity->created_at)) . ' ' . __('ago', 'mcqhome');
    echo '</p>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

/**
 * Render notifications section
 */
function mcqhome_render_notifications($user_id) {
    $notifications = mcqhome_get_user_notifications($user_id, 5);
    $unread_count = mcqhome_get_unread_notifications_count($user_id);
    
    if (empty($notifications)) {
        echo '<div class="text-center py-6">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 17H7l4 4v-4zM13 3h4l-2 2-2-2zM3 13v4l2-2-2-2z"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No notifications', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('You\'ll see notifications here when you have new activity.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-3" id="notifications-container">';
    foreach ($notifications as $notification) {
        mcqhome_render_notification_item($notification);
    }
    echo '</div>';
    
    if (count($notifications) >= 5) {
        echo '<div class="mt-4 text-center">';
        echo '<button class="load-more-notifications-btn text-blue-600 hover:text-blue-500 text-sm font-medium" data-user-id="' . $user_id . '" data-page="1">';
        echo __('Load more notifications', 'mcqhome');
        echo '</button>';
        echo '</div>';
    }
    
    // Update notification count in header
    if ($unread_count > 0) {
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo '  $(".notification-count").text("' . $unread_count . '").removeClass("hidden");';
        echo '});';
        echo '</script>';
    }
}

/**
 * Render individual notification item
 */
function mcqhome_render_notification_item($notification) {
    $is_unread = !$notification->is_read;
    $notification_class = $is_unread ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200';
    
    echo '<div class="notification-item border rounded-lg p-3 ' . $notification_class . '" data-notification-id="' . $notification->id . '">';
    echo '<div class="flex items-start justify-between">';
    echo '<div class="flex-1">';
    
    // Notification icon based on type
    $icon = '';
    switch ($notification->type) {
        case 'new_follower':
            $icon = '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>';
            break;
        case 'new_content':
            $icon = '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>';
            break;
        default:
            $icon = '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            break;
    }
    
    echo '<div class="flex items-start space-x-3">';
    echo '<div class="flex-shrink-0 mt-1">' . $icon . '</div>';
    echo '<div class="flex-1">';
    echo '<h4 class="text-sm font-medium text-gray-900">' . esc_html($notification->title) . '</h4>';
    echo '<p class="text-sm text-gray-600 mt-1">' . esc_html($notification->message) . '</p>';
    echo '<p class="text-xs text-gray-500 mt-2">' . human_time_diff(strtotime($notification->created_at)) . ' ' . __('ago', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    
    // Mark as read button for unread notifications
    if ($is_unread) {
        echo '<div class="flex-shrink-0 ml-2">';
        echo '<button class="mark-notification-read-btn text-blue-600 hover:text-blue-800 text-xs" data-notification-id="' . $notification->id . '">';
        echo __('Mark as read', 'mcqhome');
        echo '</button>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Add action link if available
    if (isset($notification->data['post_url'])) {
        echo '<div class="mt-2 pt-2 border-t border-gray-200">';
        echo '<a href="' . esc_url($notification->data['post_url']) . '" class="text-blue-600 hover:text-blue-800 text-xs font-medium">';
        echo __('View content', 'mcqhome');
        echo '</a>';
        echo '</div>';
    }
    
    echo '</div>';
}

/**
 * Render following management section
 */
function mcqhome_render_following_management($user_id) {
    $following_institutions = get_user_meta($user_id, 'following_institutions', true);
    $following_teachers = get_user_meta($user_id, 'following_teachers', true);
    
    if (empty($following_institutions) && empty($following_teachers)) {
        echo '<div class="text-center py-6">';
        echo '<p class="text-gray-500 text-sm">' . __('You are not following anyone yet.', 'mcqhome') . '</p>';
        echo '<div class="mt-4 space-x-2">';
        echo '<a href="' . home_url('/institutions/') . '" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">';
        echo __('Find Institutions', 'mcqhome');
        echo '</a>';
        echo '<a href="' . home_url('/teachers/') . '" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">';
        echo __('Find Teachers', 'mcqhome');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-3">';
    
    // Show followed institutions
    if (is_array($following_institutions) && !empty($following_institutions)) {
        $institutions = get_posts([
            'post_type' => 'institution',
            'post__in' => $following_institutions,
            'posts_per_page' => 3,
            'post_status' => 'publish'
        ]);
        
        foreach ($institutions as $institution) {
            echo '<div class="flex items-center justify-between">';
            echo '<div class="flex items-center space-x-2">';
            echo '<div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">';
            echo '<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>';
            echo '</svg>';
            echo '</div>';
            echo '<div>';
            echo '<p class="text-sm font-medium text-gray-900">' . esc_html($institution->post_title) . '</p>';
            echo '<p class="text-xs text-gray-500">' . __('Institution', 'mcqhome') . '</p>';
            echo '</div>';
            echo '</div>';
            echo '<button type="button" class="text-xs text-red-600 hover:text-red-500" onclick="mcqhome_unfollow_institution(' . $institution->ID . ')">';
            echo __('Unfollow', 'mcqhome');
            echo '</button>';
            echo '</div>';
        }
    }
    
    // Show followed teachers
    if (is_array($following_teachers) && !empty($following_teachers)) {
        $teachers = get_users([
            'include' => array_slice($following_teachers, 0, 3),
            'fields' => ['ID', 'display_name']
        ]);
        
        foreach ($teachers as $teacher) {
            echo '<div class="flex items-center justify-between">';
            echo '<div class="flex items-center space-x-2">';
            echo '<div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">';
            echo '<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
            echo '</svg>';
            echo '</div>';
            echo '<div>';
            echo '<p class="text-sm font-medium text-gray-900">' . esc_html($teacher->display_name) . '</p>';
            echo '<p class="text-xs text-gray-500">' . __('Teacher', 'mcqhome') . '</p>';
            echo '</div>';
            echo '</div>';
            echo '<button type="button" class="text-xs text-red-600 hover:text-red-500" onclick="mcqhome_unfollow_teacher(' . $teacher->ID . ')">';
            echo __('Unfollow', 'mcqhome');
            echo '</button>';
            echo '</div>';
        }
    }
    
    echo '</div>';
    
    $total_following = count($following_institutions ?: []) + count($following_teachers ?: []);
    if ($total_following > 6) {
        echo '<div class="mt-4 text-center">';
        echo '<a href="' . home_url('/dashboard/?tab=following') . '" class="text-blue-600 hover:text-blue-500 text-sm font-medium">';
        echo sprintf(__('View all %d following', 'mcqhome'), $total_following);
        echo '</a>';
        echo '</div>';
    }
}

/**
 * Render performance analytics section
 */
function mcqhome_render_performance_analytics($user_id) {
    global $wpdb;
    
    // Get subject-wise performance
    $subject_performance = $wpdb->get_results($wpdb->prepare(
        "SELECT tt.name as subject_name, AVG(ma.score_percentage) as avg_score, COUNT(*) as attempts
         FROM {$wpdb->prefix}mcq_attempts ma
         JOIN {$wpdb->posts} p ON ma.mcq_id = p.ID
         JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
         JOIN {$wpdb->term_taxonomy} tt_tax ON tr.term_taxonomy_id = tt_tax.term_taxonomy_id
         JOIN {$wpdb->terms} tt ON tt_tax.term_id = tt.term_id
         WHERE ma.user_id = %d AND ma.status = 'completed' 
         AND tt_tax.taxonomy = 'mcq_subject'
         GROUP BY tt.term_id
         ORDER BY avg_score DESC
         LIMIT 5",
        $user_id
    ));
    
    if (empty($subject_performance)) {
        echo '<div class="text-center py-6">';
        echo '<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>';
        echo '</svg>';
        echo '<p class="mt-2 text-sm text-gray-500">' . __('No performance data available yet.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-3">';
    echo '<h4 class="text-sm font-medium text-gray-900">' . __('Performance by Subject', 'mcqhome') . '</h4>';
    
    foreach ($subject_performance as $subject) {
        $score_color = $subject->avg_score >= 70 ? 'text-green-600' : ($subject->avg_score >= 50 ? 'text-yellow-600' : 'text-red-600');
        $bar_color = $subject->avg_score >= 70 ? 'bg-green-500' : ($subject->avg_score >= 50 ? 'bg-yellow-500' : 'bg-red-500');
        
        echo '<div>';
        echo '<div class="flex items-center justify-between text-sm">';
        echo '<span class="font-medium text-gray-900">' . esc_html($subject->subject_name) . '</span>';
        echo '<span class="' . $score_color . ' font-medium">' . round($subject->avg_score, 1) . '%</span>';
        echo '</div>';
        echo '<div class="mt-1 flex items-center space-x-2">';
        echo '<div class="flex-1 bg-gray-200 rounded-full h-2">';
        echo '<div class="' . $bar_color . ' h-2 rounded-full" style="width: ' . $subject->avg_score . '%"></div>';
        echo '</div>';
        echo '<span class="text-xs text-gray-500">' . sprintf(__('%d attempts', 'mcqhome'), $subject->attempts) . '</span>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}

// Include this file in functions.php
add_action('init', function() {
    if (!function_exists('mcqhome_render_student_dashboard')) {
        require_once get_template_directory() . '/inc/dashboard-functions.php';
    }
});

/**
 * Render Teacher Dashboard
 */
function mcqhome_render_teacher_dashboard($user_id) {
    ?>
    <div class="teacher-dashboard">
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php
            $stats = mcqhome_get_teacher_stats($user_id);
            ?>
            
            <!-- Created MCQs -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Created MCQs', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['created_mcqs']; ?></p>
                    </div>
                </div>
            </div>

            <!-- MCQ Sets -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('MCQ Sets', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['mcq_sets']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Total Students', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_students']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Institution -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Institution', 'mcqhome'); ?></p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo esc_html($stats['institution_name']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Institution Associations -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Institution Associations', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_teacher_institutions($user_id); ?>
                    </div>
                </div>

                <!-- Content Management -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900"><?php _e('Content Management', 'mcqhome'); ?></h2>
                            <div class="space-x-2">
                                <a href="<?php echo admin_url('post-new.php?post_type=mcq'); ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <?php _e('Create MCQ', 'mcqhome'); ?>
                                </a>
                                <a href="<?php echo admin_url('post-new.php?post_type=mcq_set'); ?>" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    <?php _e('Create Set', 'mcqhome'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_teacher_content_management($user_id); ?>
                    </div>
                </div>

                <!-- Student Enrollment Overview -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Student Enrollment Overview', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_teacher_student_overview($user_id); ?>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Quick Actions', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_teacher_quick_actions($user_id); ?>
                    </div>
                </div>

                <!-- Performance Analytics -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Performance Analytics', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_teacher_performance_analytics($user_id); ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Recent Activity', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_teacher_recent_activity($user_id); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php
}

/**
 * Get teacher statistics
 */
function mcqhome_get_teacher_stats($user_id) {
    global $wpdb;
    
    // Get created MCQs count
    $created_mcqs = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'mcq' AND post_status = 'publish'",
        $user_id
    ));
    
    // Get MCQ sets count
    $mcq_sets = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'mcq_set' AND post_status = 'publish'",
        $user_id
    ));
    
    // Get total students enrolled in teacher's content
    $total_students = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT ue.user_id) 
         FROM {$wpdb->prefix}mcq_user_enrollments ue
         JOIN {$wpdb->posts} p ON ue.mcq_set_id = p.ID
         WHERE p.post_author = %d AND ue.status = 'active'",
        $user_id
    ));
    
    // Get institution name
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    $institution_name = __('Independent', 'mcqhome');
    if ($institution_id) {
        $institution = get_post($institution_id);
        if ($institution) {
            $institution_name = $institution->post_title;
        }
    }
    
    return [
        'created_mcqs' => intval($created_mcqs ?: 0),
        'mcq_sets' => intval($mcq_sets ?: 0),
        'total_students' => intval($total_students ?: 0),
        'institution_name' => $institution_name
    ];
}

/**
 * Render teacher institutions section
 */
function mcqhome_render_teacher_institutions($user_id) {
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    
    if (!$institution_id) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('Independent Teacher', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('You are operating as an independent teacher under MCQ Academy.', 'mcqhome') . '</p>';
        echo '<div class="mt-6">';
        echo '<a href="' . home_url('/institutions/') . '" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">';
        echo __('Browse Institutions', 'mcqhome');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        return;
    }
    
    $institution = get_post($institution_id);
    if (!$institution) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    echo '<div class="border border-gray-200 rounded-lg p-6">';
    echo '<div class="flex items-start justify-between">';
    echo '<div class="flex items-center space-x-4">';
    
    // Institution logo/avatar
    $institution_logo = get_the_post_thumbnail($institution->ID, 'thumbnail', ['class' => 'w-16 h-16 rounded-lg object-cover']);
    if ($institution_logo) {
        echo $institution_logo;
    } else {
        echo '<div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">';
        echo '<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>';
        echo '</svg>';
        echo '</div>';
    }
    
    echo '<div>';
    echo '<h3 class="text-lg font-semibold text-gray-900">' . esc_html($institution->post_title) . '</h3>';
    echo '<p class="text-sm text-gray-500 mt-1">' . __('Your Institution', 'mcqhome') . '</p>';
    
    // Institution stats
    $institution_teachers = get_users([
        'meta_key' => 'institution_id',
        'meta_value' => $institution_id,
        'role' => 'teacher',
        'count_total' => true
    ]);
    
    $institution_students = get_users([
        'meta_key' => 'institution_id',
        'meta_value' => $institution_id,
        'role' => 'student',
        'count_total' => true
    ]);
    
    echo '<div class="mt-3 flex items-center space-x-4 text-sm text-gray-500">';
    echo '<span>' . sprintf(__('%d Teachers', 'mcqhome'), $institution_teachers) . '</span>';
    echo '<span>•</span>';
    echo '<span>' . sprintf(__('%d Students', 'mcqhome'), $institution_students) . '</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="mt-4">';
    echo '<a href="' . get_permalink($institution_id) . '" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">';
    echo __('View Institution', 'mcqhome');
    echo '</a>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

/**
 * Render teacher content management section
 */
function mcqhome_render_teacher_content_management($user_id) {
    // Get recent MCQs
    $recent_mcqs = get_posts([
        'post_type' => 'mcq',
        'author' => $user_id,
        'posts_per_page' => 5,
        'post_status' => ['publish', 'draft'],
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    // Get recent MCQ sets
    $recent_sets = get_posts([
        'post_type' => 'mcq_set',
        'author' => $user_id,
        'posts_per_page' => 5,
        'post_status' => ['publish', 'draft'],
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    echo '<div class="space-y-6">';
    
    // Recent MCQs
    echo '<div>';
    echo '<h3 class="text-md font-medium text-gray-900 mb-4">' . __('Recent MCQs', 'mcqhome') . '</h3>';
    
    if (empty($recent_mcqs)) {
        echo '<div class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg">';
        echo '<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        echo '</svg>';
        echo '<h4 class="mt-2 text-sm font-medium text-gray-900">' . __('No MCQs created yet', 'mcqhome') . '</h4>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Start creating MCQs to build your question bank.', 'mcqhome') . '</p>';
        echo '<div class="mt-4">';
        echo '<a href="' . admin_url('post-new.php?post_type=mcq') . '" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">';
        echo __('Create Your First MCQ', 'mcqhome');
        echo '</a>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="space-y-3">';
        foreach ($recent_mcqs as $mcq) {
            $status_color = $mcq->post_status === 'publish' ? 'text-green-600' : 'text-yellow-600';
            $status_text = $mcq->post_status === 'publish' ? __('Published', 'mcqhome') : __('Draft', 'mcqhome');
            
            echo '<div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow">';
            echo '<div class="flex-1">';
            echo '<h4 class="text-sm font-medium text-gray-900">';
            echo '<a href="' . get_edit_post_link($mcq->ID) . '" class="hover:text-blue-600">' . esc_html($mcq->post_title) . '</a>';
            echo '</h4>';
            echo '<div class="mt-1 flex items-center space-x-2 text-xs text-gray-500">';
            echo '<span class="' . $status_color . ' font-medium">' . $status_text . '</span>';
            echo '<span>•</span>';
            echo '<span>' . human_time_diff(strtotime($mcq->post_date)) . ' ' . __('ago', 'mcqhome') . '</span>';
            echo '</div>';
            echo '</div>';
            echo '<div class="flex items-center space-x-2">';
            echo '<a href="' . get_edit_post_link($mcq->ID) . '" class="text-blue-600 hover:text-blue-500 text-sm">' . __('Edit', 'mcqhome') . '</a>';
            if ($mcq->post_status === 'publish') {
                echo '<a href="' . get_permalink($mcq->ID) . '" class="text-gray-600 hover:text-gray-500 text-sm">' . __('View', 'mcqhome') . '</a>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        
        echo '<div class="text-center mt-4">';
        echo '<a href="' . admin_url('edit.php?post_type=mcq&author=' . $user_id) . '" class="text-blue-600 hover:text-blue-500 text-sm font-medium">';
        echo __('View all MCQs', 'mcqhome');
        echo '</a>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Recent MCQ Sets
    echo '<div>';
    echo '<h3 class="text-md font-medium text-gray-900 mb-4">' . __('Recent MCQ Sets', 'mcqhome') . '</h3>';
    
    if (empty($recent_sets)) {
        echo '<div class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg">';
        echo '<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>';
        echo '</svg>';
        echo '<h4 class="mt-2 text-sm font-medium text-gray-900">' . __('No MCQ sets created yet', 'mcqhome') . '</h4>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Create MCQ sets to organize your questions into assessments.', 'mcqhome') . '</p>';
        echo '<div class="mt-4">';
        echo '<a href="' . admin_url('post-new.php?post_type=mcq_set') . '" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">';
        echo __('Create Your First Set', 'mcqhome');
        echo '</a>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="space-y-3">';
        foreach ($recent_sets as $set) {
            $status_color = $set->post_status === 'publish' ? 'text-green-600' : 'text-yellow-600';
            $status_text = $set->post_status === 'publish' ? __('Published', 'mcqhome') : __('Draft', 'mcqhome');
            
            // Get question count
            $mcq_ids = get_post_meta($set->ID, '_mcq_set_questions', true);
            $question_count = is_array($mcq_ids) ? count($mcq_ids) : 0;
            
            echo '<div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow">';
            echo '<div class="flex-1">';
            echo '<h4 class="text-sm font-medium text-gray-900">';
            echo '<a href="' . get_edit_post_link($set->ID) . '" class="hover:text-blue-600">' . esc_html($set->post_title) . '</a>';
            echo '</h4>';
            echo '<div class="mt-1 flex items-center space-x-2 text-xs text-gray-500">';
            echo '<span class="' . $status_color . ' font-medium">' . $status_text . '</span>';
            echo '<span>•</span>';
            echo '<span>' . sprintf(__('%d questions', 'mcqhome'), $question_count) . '</span>';
            echo '<span>•</span>';
            echo '<span>' . human_time_diff(strtotime($set->post_date)) . ' ' . __('ago', 'mcqhome') . '</span>';
            echo '</div>';
            echo '</div>';
            echo '<div class="flex items-center space-x-2">';
            echo '<a href="' . get_edit_post_link($set->ID) . '" class="text-blue-600 hover:text-blue-500 text-sm">' . __('Edit', 'mcqhome') . '</a>';
            if ($set->post_status === 'publish') {
                echo '<a href="' . get_permalink($set->ID) . '" class="text-gray-600 hover:text-gray-500 text-sm">' . __('View', 'mcqhome') . '</a>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        
        echo '<div class="text-center mt-4">';
        echo '<a href="' . admin_url('edit.php?post_type=mcq_set&author=' . $user_id) . '" class="text-blue-600 hover:text-blue-500 text-sm font-medium">';
        echo __('View all MCQ Sets', 'mcqhome');
        echo '</a>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

/**
 * Render teacher student overview section
 */
function mcqhome_render_teacher_student_overview($user_id) {
    global $wpdb;
    
    // Get students enrolled in teacher's content
    $enrolled_students = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT u.ID, u.display_name, u.user_email, 
                COUNT(DISTINCT ue.mcq_set_id) as enrolled_sets,
                AVG(ma.score_percentage) as avg_score,
                MAX(ma.completed_at) as last_activity
         FROM {$wpdb->users} u
         JOIN {$wpdb->prefix}mcq_user_enrollments ue ON u.ID = ue.user_id
         JOIN {$wpdb->posts} p ON ue.mcq_set_id = p.ID
         LEFT JOIN {$wpdb->prefix}mcq_attempts ma ON u.ID = ma.user_id AND ma.status = 'completed'
         WHERE p.post_author = %d AND ue.status = 'active'
         GROUP BY u.ID
         ORDER BY last_activity DESC
         LIMIT 10",
        $user_id
    ));
    
    if (empty($enrolled_students)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No students enrolled yet', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Students will appear here when they enroll in your MCQ sets.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="overflow-hidden">';
    echo '<table class="min-w-full divide-y divide-gray-200">';
    echo '<thead class="bg-gray-50">';
    echo '<tr>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Student', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Enrolled Sets', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Avg Score', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Last Activity', 'mcqhome') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody class="bg-white divide-y divide-gray-200">';
    
    foreach ($enrolled_students as $student) {
        $avatar = get_avatar($student->ID, 32, '', '', ['class' => 'w-8 h-8 rounded-full']);
        $score_color = $student->avg_score >= 70 ? 'text-green-600' : ($student->avg_score >= 50 ? 'text-yellow-600' : 'text-red-600');
        
        echo '<tr class="hover:bg-gray-50">';
        echo '<td class="px-6 py-4 whitespace-nowrap">';
        echo '<div class="flex items-center">';
        echo '<div class="flex-shrink-0">' . $avatar . '</div>';
        echo '<div class="ml-3">';
        echo '<div class="text-sm font-medium text-gray-900">' . esc_html($student->display_name) . '</div>';
        echo '<div class="text-sm text-gray-500">' . esc_html($student->user_email) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $student->enrolled_sets . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm ' . $score_color . ' font-medium">';
        echo $student->avg_score ? round($student->avg_score, 1) . '%' : '-';
        echo '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
        echo $student->last_activity ? human_time_diff(strtotime($student->last_activity)) . ' ' . __('ago', 'mcqhome') : __('Never', 'mcqhome');
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    echo '<div class="mt-4 text-center">';
    echo '<a href="' . home_url('/dashboard/?tab=students') . '" class="text-blue-600 hover:text-blue-500 text-sm font-medium">';
    echo __('View all students', 'mcqhome');
    echo '</a>';
    echo '</div>';
}

/**
 * Render teacher quick actions section
 */
function mcqhome_render_teacher_quick_actions($user_id) {
    echo '<div class="space-y-3">';
    
    // Create MCQ
    echo '<a href="' . admin_url('post-new.php?post_type=mcq') . '" class="flex items-center p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group">';
    echo '<div class="flex-shrink-0">';
    echo '<div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center group-hover:bg-blue-200">';
    echo '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>';
    echo '</svg>';
    echo '</div>';
    echo '</div>';
    echo '<div class="ml-3">';
    echo '<p class="text-sm font-medium text-gray-900">' . __('Create New MCQ', 'mcqhome') . '</p>';
    echo '<p class="text-xs text-gray-500">' . __('Add a new question to your bank', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</a>';
    
    // Create MCQ Set
    echo '<a href="' . admin_url('post-new.php?post_type=mcq_set') . '" class="flex items-center p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group">';
    echo '<div class="flex-shrink-0">';
    echo '<div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center group-hover:bg-green-200">';
    echo '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>';
    echo '</svg>';
    echo '</div>';
    echo '</div>';
    echo '<div class="ml-3">';
    echo '<p class="text-sm font-medium text-gray-900">' . __('Create MCQ Set', 'mcqhome') . '</p>';
    echo '<p class="text-xs text-gray-500">' . __('Organize questions into assessments', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</a>';
    
    // View All MCQs
    echo '<a href="' . admin_url('edit.php?post_type=mcq&author=' . $user_id) . '" class="flex items-center p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group">';
    echo '<div class="flex-shrink-0">';
    echo '<div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center group-hover:bg-yellow-200">';
    echo '<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
    echo '</svg>';
    echo '</div>';
    echo '</div>';
    echo '<div class="ml-3">';
    echo '<p class="text-sm font-medium text-gray-900">' . __('Manage MCQs', 'mcqhome') . '</p>';
    echo '<p class="text-xs text-gray-500">' . __('View and edit your questions', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</a>';
    
    // View All Sets
    echo '<a href="' . admin_url('edit.php?post_type=mcq_set&author=' . $user_id) . '" class="flex items-center p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group">';
    echo '<div class="flex-shrink-0">';
    echo '<div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center group-hover:bg-purple-200">';
    echo '<svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>';
    echo '</svg>';
    echo '</div>';
    echo '</div>';
    echo '<div class="ml-3">';
    echo '<p class="text-sm font-medium text-gray-900">' . __('Manage Sets', 'mcqhome') . '</p>';
    echo '<p class="text-xs text-gray-500">' . __('View and edit your MCQ sets', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</a>';
    
    echo '</div>';
}

/**
 * Render teacher performance analytics section
 */
function mcqhome_render_teacher_performance_analytics($user_id) {
    global $wpdb;
    
    // Get content performance data
    $content_performance = $wpdb->get_results($wpdb->prepare(
        "SELECT p.post_title, p.post_type, 
                COUNT(DISTINCT ue.user_id) as enrolled_students,
                AVG(ma.score_percentage) as avg_score,
                COUNT(ma.id) as total_attempts
         FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->prefix}mcq_user_enrollments ue ON p.ID = ue.mcq_set_id AND ue.status = 'active'
         LEFT JOIN {$wpdb->prefix}mcq_attempts ma ON p.ID = ma.mcq_set_id AND ma.status = 'completed'
         WHERE p.post_author = %d AND p.post_type IN ('mcq_set') AND p.post_status = 'publish'
         GROUP BY p.ID
         ORDER BY enrolled_students DESC, avg_score DESC
         LIMIT 5",
        $user_id
    ));
    
    if (empty($content_performance)) {
        echo '<div class="text-center py-6">';
        echo '<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>';
        echo '</svg>';
        echo '<p class="mt-2 text-sm text-gray-500">' . __('No performance data available yet.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-4">';
    echo '<h4 class="text-sm font-medium text-gray-900">' . __('Top Performing Content', 'mcqhome') . '</h4>';
    
    foreach ($content_performance as $content) {
        $score_color = $content->avg_score >= 70 ? 'text-green-600' : ($content->avg_score >= 50 ? 'text-yellow-600' : 'text-red-600');
        
        echo '<div class="border border-gray-200 rounded-lg p-3">';
        echo '<div class="flex items-center justify-between">';
        echo '<div class="flex-1">';
        echo '<h5 class="text-sm font-medium text-gray-900">' . esc_html($content->post_title) . '</h5>';
        echo '<div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">';
        echo '<span>' . sprintf(__('%d students', 'mcqhome'), $content->enrolled_students) . '</span>';
        echo '<span>•</span>';
        echo '<span>' . sprintf(__('%d attempts', 'mcqhome'), $content->total_attempts) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '<div class="text-right">';
        echo '<div class="text-sm ' . $score_color . ' font-medium">';
        echo $content->avg_score ? round($content->avg_score, 1) . '%' : '-';
        echo '</div>';
        echo '<div class="text-xs text-gray-500">' . __('Avg Score', 'mcqhome') . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Overall stats
    $overall_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(DISTINCT ue.user_id) as total_students,
                AVG(ma.score_percentage) as overall_avg,
                COUNT(ma.id) as total_attempts
         FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->prefix}mcq_user_enrollments ue ON p.ID = ue.mcq_set_id AND ue.status = 'active'
         LEFT JOIN {$wpdb->prefix}mcq_attempts ma ON p.ID = ma.mcq_set_id AND ma.status = 'completed'
         WHERE p.post_author = %d AND p.post_type = 'mcq_set' AND p.post_status = 'publish'",
        $user_id
    ));
    
    if ($overall_stats && $overall_stats->total_students > 0) {
        echo '<div class="mt-6 pt-6 border-t border-gray-200">';
        echo '<h4 class="text-sm font-medium text-gray-900 mb-3">' . __('Overall Performance', 'mcqhome') . '</h4>';
        echo '<div class="grid grid-cols-3 gap-4 text-center">';
        
        echo '<div>';
        echo '<div class="text-lg font-semibold text-gray-900">' . $overall_stats->total_students . '</div>';
        echo '<div class="text-xs text-gray-500">' . __('Total Students', 'mcqhome') . '</div>';
        echo '</div>';
        
        echo '<div>';
        echo '<div class="text-lg font-semibold text-gray-900">' . $overall_stats->total_attempts . '</div>';
        echo '<div class="text-xs text-gray-500">' . __('Total Attempts', 'mcqhome') . '</div>';
        echo '</div>';
        
        echo '<div>';
        echo '<div class="text-lg font-semibold text-gray-900">' . round($overall_stats->overall_avg, 1) . '%</div>';
        echo '<div class="text-xs text-gray-500">' . __('Avg Score', 'mcqhome') . '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Render teacher recent activity section
 */
function mcqhome_render_teacher_recent_activity($user_id) {
    global $wpdb;
    
    // Get recent student activities on teacher's content
    $recent_activities = $wpdb->get_results($wpdb->prepare(
        "SELECT ma.*, u.display_name as student_name, p.post_title as content_title
         FROM {$wpdb->prefix}mcq_attempts ma
         JOIN {$wpdb->users} u ON ma.user_id = u.ID
         JOIN {$wpdb->posts} p ON ma.mcq_set_id = p.ID
         WHERE p.post_author = %d AND ma.status = 'completed'
         ORDER BY ma.completed_at DESC
         LIMIT 10",
        $user_id
    ));
    
    if (empty($recent_activities)) {
        echo '<div class="text-center py-6">';
        echo '<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>';
        echo '</svg>';
        echo '<p class="mt-2 text-sm text-gray-500">' . __('No recent student activity.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-3">';
    
    foreach ($recent_activities as $activity) {
        $score_color = $activity->score_percentage >= 70 ? 'text-green-600' : ($activity->score_percentage >= 50 ? 'text-yellow-600' : 'text-red-600');
        
        echo '<div class="flex items-start space-x-3">';
        echo '<div class="flex-shrink-0">';
        echo '<div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">';
        echo '<svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">';
        echo '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>';
        echo '</svg>';
        echo '</div>';
        echo '</div>';
        echo '<div class="flex-1 min-w-0">';
        echo '<p class="text-sm text-gray-900">';
        echo '<span class="font-medium">' . esc_html($activity->student_name) . '</span>';
        echo ' ' . __('completed', 'mcqhome') . ' ';
        echo '<span class="font-medium">' . esc_html($activity->content_title) . '</span>';
        echo '</p>';
        echo '<div class="mt-1 flex items-center space-x-2">';
        echo '<span class="text-xs ' . $score_color . ' font-medium">' . $activity->score_percentage . '%</span>';
        echo '<span class="text-xs text-gray-500">•</span>';
        echo '<span class="text-xs text-gray-500">' . human_time_diff(strtotime($activity->completed_at)) . ' ' . __('ago', 'mcqhome') . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}/**

 * Render Institution Dashboard
 */
function mcqhome_render_institution_dashboard($user_id) {
    ?>
    <div class="institution-dashboard">
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php
            $stats = mcqhome_get_institution_stats($user_id);
            ?>
            
            <!-- Total Teachers -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Teachers', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_teachers']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Students', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_students']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Content -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('MCQ Sets', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_content']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Average Performance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php _e('Avg Performance', 'mcqhome'); ?></p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['avg_performance']; ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Institution Metrics and Analytics -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Institution Analytics', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_institution_analytics($user_id); ?>
                    </div>
                </div>

                <!-- Teacher Management -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900"><?php _e('Teacher Management', 'mcqhome'); ?></h2>
                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700" onclick="mcqhome_open_add_teacher_modal()">
                                <?php _e('Add Teacher', 'mcqhome'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_institution_teacher_management($user_id); ?>
                    </div>
                </div>

                <!-- Student Enrollment Tools -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Student Enrollment Overview', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_institution_student_enrollment($user_id); ?>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                
                <!-- Content Oversight -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Content Oversight', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_institution_content_oversight($user_id); ?>
                    </div>
                </div>

                <!-- Branding Customization -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Branding', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_institution_branding($user_id); ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Recent Activity', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <?php mcqhome_render_institution_recent_activity($user_id); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php
}

/**
 * Get institution statistics
 */
function mcqhome_get_institution_stats($user_id) {
    global $wpdb;
    
    // Get institution ID
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    if (!$institution_id) {
        return [
            'total_teachers' => 0,
            'total_students' => 0,
            'total_content' => 0,
            'avg_performance' => 0
        ];
    }
    
    // Get total teachers
    $total_teachers = get_users([
        'meta_key' => 'institution_id',
        'meta_value' => $institution_id,
        'role' => 'teacher',
        'count_total' => true
    ]);
    
    // Get total students enrolled in institution content
    $total_students = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT ue.user_id)
         FROM {$wpdb->prefix}mcq_user_enrollments ue
         JOIN {$wpdb->posts} p ON ue.mcq_set_id = p.ID
         JOIN {$wpdb->users} u ON p.post_author = u.ID
         JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
         WHERE um.meta_key = 'institution_id' AND um.meta_value = %s AND ue.status = 'active'",
        $institution_id
    ));
    
    // Get total content (MCQ sets)
    $total_content = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(p.ID)
         FROM {$wpdb->posts} p
         JOIN {$wpdb->users} u ON p.post_author = u.ID
         JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
         WHERE p.post_type = 'mcq_set' AND p.post_status = 'publish'
         AND um.meta_key = 'institution_id' AND um.meta_value = %s",
        $institution_id
    ));
    
    // Get average performance
    $avg_performance = $wpdb->get_var($wpdb->prepare(
        "SELECT AVG(ma.score_percentage)
         FROM {$wpdb->prefix}mcq_attempts ma
         JOIN {$wpdb->posts} p ON ma.mcq_set_id = p.ID
         JOIN {$wpdb->users} u ON p.post_author = u.ID
         JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
         WHERE ma.status = 'completed'
         AND um.meta_key = 'institution_id' AND um.meta_value = %s",
        $institution_id
    ));
    
    return [
        'total_teachers' => intval($total_teachers ?: 0),
        'total_students' => intval($total_students ?: 0),
        'total_content' => intval($total_content ?: 0),
        'avg_performance' => round(floatval($avg_performance ?: 0), 1)
    ];
}

/**
 * Render institution analytics section
 */
function mcqhome_render_institution_analytics($user_id) {
    global $wpdb;
    
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    if (!$institution_id) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    // Get monthly performance data
    $monthly_data = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE_FORMAT(ma.completed_at, '%%Y-%%m') as month,
                COUNT(DISTINCT ma.user_id) as active_students,
                COUNT(ma.id) as total_attempts,
                AVG(ma.score_percentage) as avg_score
         FROM {$wpdb->prefix}mcq_attempts ma
         JOIN {$wpdb->posts} p ON ma.mcq_set_id = p.ID
         JOIN {$wpdb->users} u ON p.post_author = u.ID
         JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
         WHERE ma.status = 'completed' AND ma.completed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         AND um.meta_key = 'institution_id' AND um.meta_value = %s
         GROUP BY month
         ORDER BY month DESC
         LIMIT 6",
        $institution_id
    ));
    
    if (empty($monthly_data)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No analytics data', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Analytics will appear here once students start taking assessments.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-6">';
    
    // Performance trend
    echo '<div>';
    echo '<h3 class="text-md font-medium text-gray-900 mb-4">' . __('6-Month Performance Trend', 'mcqhome') . '</h3>';
    echo '<div class="space-y-3">';
    
    foreach (array_reverse($monthly_data) as $data) {
        $month_name = date_i18n('F Y', strtotime($data->month . '-01'));
        $score_color = $data->avg_score >= 70 ? 'bg-green-500' : ($data->avg_score >= 50 ? 'bg-yellow-500' : 'bg-red-500');
        
        echo '<div class="flex items-center justify-between">';
        echo '<div class="flex items-center space-x-3">';
        echo '<div class="text-sm font-medium text-gray-900 w-24">' . $month_name . '</div>';
        echo '<div class="flex-1 bg-gray-200 rounded-full h-2 w-32">';
        echo '<div class="' . $score_color . ' h-2 rounded-full" style="width: ' . $data->avg_score . '%"></div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="text-sm text-gray-500">';
        echo sprintf(__('%d students, %s%% avg', 'mcqhome'), $data->active_students, round($data->avg_score, 1));
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // Top performing teachers
    $top_teachers = $wpdb->get_results($wpdb->prepare(
        "SELECT u.ID, u.display_name,
                COUNT(DISTINCT ue.user_id) as student_count,
                AVG(ma.score_percentage) as avg_score,
                COUNT(DISTINCT p.ID) as content_count
         FROM {$wpdb->users} u
         JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
         JOIN {$wpdb->posts} p ON u.ID = p.post_author
         LEFT JOIN {$wpdb->prefix}mcq_user_enrollments ue ON p.ID = ue.mcq_set_id AND ue.status = 'active'
         LEFT JOIN {$wpdb->prefix}mcq_attempts ma ON p.ID = ma.mcq_set_id AND ma.status = 'completed'
         WHERE um.meta_key = 'institution_id' AND um.meta_value = %s
         AND p.post_type = 'mcq_set' AND p.post_status = 'publish'
         GROUP BY u.ID
         ORDER BY avg_score DESC, student_count DESC
         LIMIT 5",
        $institution_id
    ));
    
    if (!empty($top_teachers)) {
        echo '<div>';
        echo '<h3 class="text-md font-medium text-gray-900 mb-4">' . __('Top Performing Teachers', 'mcqhome') . '</h3>';
        echo '<div class="space-y-3">';
        
        foreach ($top_teachers as $teacher) {
            $score_color = $teacher->avg_score >= 70 ? 'text-green-600' : ($teacher->avg_score >= 50 ? 'text-yellow-600' : 'text-red-600');
            
            echo '<div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">';
            echo '<div class="flex items-center space-x-3">';
            echo '<div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">';
            echo '<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
            echo '</svg>';
            echo '</div>';
            echo '<div>';
            echo '<div class="text-sm font-medium text-gray-900">' . esc_html($teacher->display_name) . '</div>';
            echo '<div class="text-xs text-gray-500">';
            echo sprintf(__('%d students • %d sets', 'mcqhome'), $teacher->student_count, $teacher->content_count);
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<div class="text-sm ' . $score_color . ' font-medium">';
            echo $teacher->avg_score ? round($teacher->avg_score, 1) . '%' : '-';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}

/**
 * Render institution teacher management section
 */
function mcqhome_render_institution_teacher_management($user_id) {
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    if (!$institution_id) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    // Get teachers associated with this institution
    $teachers = get_users([
        'meta_key' => 'institution_id',
        'meta_value' => $institution_id,
        'role' => 'teacher',
        'orderby' => 'registered',
        'order' => 'DESC'
    ]);
    
    if (empty($teachers)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No teachers yet', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Add teachers to your institution to get started.', 'mcqhome') . '</p>';
        echo '<div class="mt-6">';
        echo '<button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700" onclick="mcqhome_open_add_teacher_modal()">';
        echo __('Add Your First Teacher', 'mcqhome');
        echo '</button>';
        echo '</div>';
        echo '</div>';
        return;
    }
    
    echo '<div class="overflow-hidden">';
    echo '<table class="min-w-full divide-y divide-gray-200">';
    echo '<thead class="bg-gray-50">';
    echo '<tr>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Teacher', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Content', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Students', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Performance', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Actions', 'mcqhome') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody class="bg-white divide-y divide-gray-200">';
    
    foreach ($teachers as $teacher) {
        global $wpdb;
        
        // Get teacher stats
        $content_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'mcq_set' AND post_status = 'publish'",
            $teacher->ID
        ));
        
        $student_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT ue.user_id)
             FROM {$wpdb->prefix}mcq_user_enrollments ue
             JOIN {$wpdb->posts} p ON ue.mcq_set_id = p.ID
             WHERE p.post_author = %d AND ue.status = 'active'",
            $teacher->ID
        ));
        
        $avg_performance = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(ma.score_percentage)
             FROM {$wpdb->prefix}mcq_attempts ma
             JOIN {$wpdb->posts} p ON ma.mcq_set_id = p.ID
             WHERE p.post_author = %d AND ma.status = 'completed'",
            $teacher->ID
        ));
        
        $avatar = get_avatar($teacher->ID, 32, '', '', ['class' => 'w-8 h-8 rounded-full']);
        $score_color = $avg_performance >= 70 ? 'text-green-600' : ($avg_performance >= 50 ? 'text-yellow-600' : 'text-red-600');
        
        echo '<tr class="hover:bg-gray-50">';
        echo '<td class="px-6 py-4 whitespace-nowrap">';
        echo '<div class="flex items-center">';
        echo '<div class="flex-shrink-0">' . $avatar . '</div>';
        echo '<div class="ml-3">';
        echo '<div class="text-sm font-medium text-gray-900">' . esc_html($teacher->display_name) . '</div>';
        echo '<div class="text-sm text-gray-500">' . esc_html($teacher->user_email) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . intval($content_count) . ' ' . __('sets', 'mcqhome') . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . intval($student_count) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm ' . $score_color . ' font-medium">';
        echo $avg_performance ? round($avg_performance, 1) . '%' : '-';
        echo '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
        echo '<div class="flex items-center space-x-2">';
        echo '<a href="' . get_edit_user_link($teacher->ID) . '" class="text-blue-600 hover:text-blue-500">' . __('Edit', 'mcqhome') . '</a>';
        echo '<button type="button" class="text-red-600 hover:text-red-500" onclick="mcqhome_remove_teacher(' . $teacher->ID . ')">' . __('Remove', 'mcqhome') . '</button>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

/**
 * Render institution student enrollment section
 */
function mcqhome_render_institution_student_enrollment($user_id) {
    global $wpdb;
    
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    if (!$institution_id) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    // Get students enrolled in institution content
    $enrolled_students = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT u.ID, u.display_name, u.user_email,
                COUNT(DISTINCT ue.mcq_set_id) as enrolled_sets,
                AVG(ma.score_percentage) as avg_score,
                MAX(ma.completed_at) as last_activity
         FROM {$wpdb->users} u
         JOIN {$wpdb->prefix}mcq_user_enrollments ue ON u.ID = ue.user_id
         JOIN {$wpdb->posts} p ON ue.mcq_set_id = p.ID
         JOIN {$wpdb->users} teacher ON p.post_author = teacher.ID
         JOIN {$wpdb->usermeta} um ON teacher.ID = um.user_id
         LEFT JOIN {$wpdb->prefix}mcq_attempts ma ON u.ID = ma.user_id AND ma.status = 'completed'
         WHERE um.meta_key = 'institution_id' AND um.meta_value = %s AND ue.status = 'active'
         GROUP BY u.ID
         ORDER BY last_activity DESC
         LIMIT 15",
        $institution_id
    ));
    
    if (empty($enrolled_students)) {
        echo '<div class="text-center py-8">';
        echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>';
        echo '</svg>';
        echo '<h3 class="mt-2 text-sm font-medium text-gray-900">' . __('No students enrolled yet', 'mcqhome') . '</h3>';
        echo '<p class="mt-1 text-sm text-gray-500">' . __('Students will appear here when they enroll in your institution\'s content.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="overflow-hidden">';
    echo '<table class="min-w-full divide-y divide-gray-200">';
    echo '<thead class="bg-gray-50">';
    echo '<tr>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Student', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Enrolled Sets', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Avg Score', 'mcqhome') . '</th>';
    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . __('Last Activity', 'mcqhome') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody class="bg-white divide-y divide-gray-200">';
    
    foreach ($enrolled_students as $student) {
        $avatar = get_avatar($student->ID, 32, '', '', ['class' => 'w-8 h-8 rounded-full']);
        $score_color = $student->avg_score >= 70 ? 'text-green-600' : ($student->avg_score >= 50 ? 'text-yellow-600' : 'text-red-600');
        
        echo '<tr class="hover:bg-gray-50">';
        echo '<td class="px-6 py-4 whitespace-nowrap">';
        echo '<div class="flex items-center">';
        echo '<div class="flex-shrink-0">' . $avatar . '</div>';
        echo '<div class="ml-3">';
        echo '<div class="text-sm font-medium text-gray-900">' . esc_html($student->display_name) . '</div>';
        echo '<div class="text-sm text-gray-500">' . esc_html($student->user_email) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . $student->enrolled_sets . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm ' . $score_color . ' font-medium">';
        echo $student->avg_score ? round($student->avg_score, 1) . '%' : '-';
        echo '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
        echo $student->last_activity ? human_time_diff(strtotime($student->last_activity)) . ' ' . __('ago', 'mcqhome') : __('Never', 'mcqhome');
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
    echo '<div class="mt-4 text-center">';
    echo '<a href="' . home_url('/dashboard/?tab=students') . '" class="text-blue-600 hover:text-blue-500 text-sm font-medium">';
    echo __('View all students', 'mcqhome');
    echo '</a>';
    echo '</div>';
}

/**
 * Render institution content oversight section
 */
function mcqhome_render_institution_content_oversight($user_id) {
    global $wpdb;
    
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    if (!$institution_id) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    // Get recent content from institution teachers
    $recent_content = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID, p.post_title, p.post_type, p.post_status, p.post_date,
                u.display_name as author_name
         FROM {$wpdb->posts} p
         JOIN {$wpdb->users} u ON p.post_author = u.ID
         JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
         WHERE p.post_type IN ('mcq', 'mcq_set')
         AND um.meta_key = 'institution_id' AND um.meta_value = %s
         ORDER BY p.post_date DESC
         LIMIT 10",
        $institution_id
    ));
    
    if (empty($recent_content)) {
        echo '<div class="text-center py-6">';
        echo '<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
        echo '</svg>';
        echo '<p class="mt-2 text-sm text-gray-500">' . __('No content created yet.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-3">';
    
    foreach ($recent_content as $content) {
        $status_color = $content->post_status === 'publish' ? 'text-green-600' : 'text-yellow-600';
        $status_text = $content->post_status === 'publish' ? __('Published', 'mcqhome') : __('Draft', 'mcqhome');
        $type_label = $content->post_type === 'mcq' ? __('MCQ', 'mcqhome') : __('MCQ Set', 'mcqhome');
        
        echo '<div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow">';
        echo '<div class="flex-1">';
        echo '<h4 class="text-sm font-medium text-gray-900">' . esc_html($content->post_title) . '</h4>';
        echo '<div class="mt-1 flex items-center space-x-2 text-xs text-gray-500">';
        echo '<span>' . $type_label . '</span>';
        echo '<span>•</span>';
        echo '<span class="' . $status_color . ' font-medium">' . $status_text . '</span>';
        echo '<span>•</span>';
        echo '<span>' . sprintf(__('by %s', 'mcqhome'), esc_html($content->author_name)) . '</span>';
        echo '<span>•</span>';
        echo '<span>' . human_time_diff(strtotime($content->post_date)) . ' ' . __('ago', 'mcqhome') . '</span>';
        echo '</div>';
        echo '</div>';
        echo '<div class="flex items-center space-x-2">';
        echo '<a href="' . get_edit_post_link($content->ID) . '" class="text-blue-600 hover:text-blue-500 text-sm">' . __('Review', 'mcqhome') . '</a>';
        if ($content->post_status === 'publish') {
            echo '<a href="' . get_permalink($content->ID) . '" class="text-gray-600 hover:text-gray-500 text-sm">' . __('View', 'mcqhome') . '</a>';
        }
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    echo '<div class="mt-4 text-center">';
    echo '<a href="' . admin_url('edit.php?post_type=mcq_set') . '" class="text-blue-600 hover:text-blue-500 text-sm font-medium">';
    echo __('View all content', 'mcqhome');
    echo '</a>';
    echo '</div>';
}

/**
 * Render institution branding section
 */
function mcqhome_render_institution_branding($user_id) {
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    if (!$institution_id) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    $institution = get_post($institution_id);
    if (!$institution) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    echo '<div class="space-y-4">';
    
    // Institution logo
    echo '<div class="text-center">';
    $institution_logo = get_the_post_thumbnail($institution->ID, 'medium', ['class' => 'mx-auto w-24 h-24 rounded-lg object-cover']);
    if ($institution_logo) {
        echo $institution_logo;
    } else {
        echo '<div class="mx-auto w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center">';
        echo '<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>';
        echo '</svg>';
        echo '</div>';
    }
    echo '<h3 class="mt-2 text-lg font-semibold text-gray-900">' . esc_html($institution->post_title) . '</h3>';
    echo '</div>';
    
    // Quick branding actions
    echo '<div class="space-y-2">';
    
    echo '<a href="' . get_edit_post_link($institution_id) . '" class="flex items-center p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group">';
    echo '<div class="flex-shrink-0">';
    echo '<div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center group-hover:bg-blue-200">';
    echo '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
    echo '</svg>';
    echo '</div>';
    echo '</div>';
    echo '<div class="ml-3">';
    echo '<p class="text-sm font-medium text-gray-900">' . __('Edit Profile', 'mcqhome') . '</p>';
    echo '<p class="text-xs text-gray-500">' . __('Update institution information', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</a>';
    
    echo '<a href="' . get_permalink($institution_id) . '" class="flex items-center p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group">';
    echo '<div class="flex-shrink-0">';
    echo '<div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center group-hover:bg-green-200">';
    echo '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    echo '</svg>';
    echo '</div>';
    echo '</div>';
    echo '<div class="ml-3">';
    echo '<p class="text-sm font-medium text-gray-900">' . __('View Public Page', 'mcqhome') . '</p>';
    echo '<p class="text-xs text-gray-500">' . __('See how students see your institution', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</a>';
    
    echo '<button type="button" class="w-full flex items-center p-3 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow group" onclick="mcqhome_customize_branding()">';
    echo '<div class="flex-shrink-0">';
    echo '<div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center group-hover:bg-purple-200">';
    echo '<svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h4a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2z"></path>';
    echo '</svg>';
    echo '</div>';
    echo '</div>';
    echo '<div class="ml-3 text-left">';
    echo '<p class="text-sm font-medium text-gray-900">' . __('Customize Branding', 'mcqhome') . '</p>';
    echo '<p class="text-xs text-gray-500">' . __('Colors, fonts, and styling', 'mcqhome') . '</p>';
    echo '</div>';
    echo '</button>';
    
    echo '</div>';
    echo '</div>';
}

/**
 * Render institution recent activity section
 */
function mcqhome_render_institution_recent_activity($user_id) {
    global $wpdb;
    
    $institution_id = get_user_meta($user_id, 'institution_id', true);
    if (!$institution_id) {
        echo '<p class="text-gray-500">' . __('Institution not found.', 'mcqhome') . '</p>';
        return;
    }
    
    // Get recent activities from institution
    $recent_activities = $wpdb->get_results($wpdb->prepare(
        "SELECT 'enrollment' as activity_type, ue.created_at as activity_date,
                u.display_name as user_name, p.post_title as content_title
         FROM {$wpdb->prefix}mcq_user_enrollments ue
         JOIN {$wpdb->users} u ON ue.user_id = u.ID
         JOIN {$wpdb->posts} p ON ue.mcq_set_id = p.ID
         JOIN {$wpdb->users} teacher ON p.post_author = teacher.ID
         JOIN {$wpdb->usermeta} um ON teacher.ID = um.user_id
         WHERE um.meta_key = 'institution_id' AND um.meta_value = %s AND ue.status = 'active'
         
         UNION ALL
         
         SELECT 'completion' as activity_type, ma.completed_at as activity_date,
                u.display_name as user_name, p.post_title as content_title
         FROM {$wpdb->prefix}mcq_attempts ma
         JOIN {$wpdb->users} u ON ma.user_id = u.ID
         JOIN {$wpdb->posts} p ON ma.mcq_set_id = p.ID
         JOIN {$wpdb->users} teacher ON p.post_author = teacher.ID
         JOIN {$wpdb->usermeta} um ON teacher.ID = um.user_id
         WHERE um.meta_key = 'institution_id' AND um.meta_value = %s AND ma.status = 'completed'
         
         ORDER BY activity_date DESC
         LIMIT 10",
        $institution_id, $institution_id
    ));
    
    if (empty($recent_activities)) {
        echo '<div class="text-center py-6">';
        echo '<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>';
        echo '</svg>';
        echo '<p class="mt-2 text-sm text-gray-500">' . __('No recent activity.', 'mcqhome') . '</p>';
        echo '</div>';
        return;
    }
    
    echo '<div class="space-y-3">';
    
    foreach ($recent_activities as $activity) {
        $icon_color = $activity->activity_type === 'enrollment' ? 'text-blue-600' : 'text-green-600';
        $bg_color = $activity->activity_type === 'enrollment' ? 'bg-blue-100' : 'bg-green-100';
        $action_text = $activity->activity_type === 'enrollment' ? __('enrolled in', 'mcqhome') : __('completed', 'mcqhome');
        
        echo '<div class="flex items-start space-x-3">';
        echo '<div class="flex-shrink-0">';
        echo '<div class="w-8 h-8 ' . $bg_color . ' rounded-full flex items-center justify-center">';
        if ($activity->activity_type === 'enrollment') {
            echo '<svg class="w-4 h-4 ' . $icon_color . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>';
            echo '</svg>';
        } else {
            echo '<svg class="w-4 h-4 ' . $icon_color . '" fill="currentColor" viewBox="0 0 20 20">';
            echo '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>';
            echo '</svg>';
        }
        echo '</div>';
        echo '</div>';
        echo '<div class="flex-1 min-w-0">';
        echo '<p class="text-sm text-gray-900">';
        echo '<span class="font-medium">' . esc_html($activity->user_name) . '</span>';
        echo ' ' . $action_text . ' ';
        echo '<span class="font-medium">' . esc_html($activity->content_title) . '</span>';
        echo '</p>';
        echo '<p class="text-xs text-gray-500">' . human_time_diff(strtotime($activity->activity_date)) . ' ' . __('ago', 'mcqhome') . '</p>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}