<?php
/**
 * Template for Social Feed Page
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header();

// Check if user is logged in
if (!is_user_logged_in()) {
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php _e('Social Feed', 'mcqhome'); ?></h1>
            <p class="text-gray-600 mb-8"><?php _e('Please log in to view your personalized social feed.', 'mcqhome'); ?></p>
            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <?php _e('Log In', 'mcqhome'); ?>
            </a>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

$user_id = get_current_user_id();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php _e('Social Feed', 'mcqhome'); ?></h1>
            <p class="text-gray-600"><?php _e('Stay updated with the latest content from institutions and teachers you follow.', 'mcqhome'); ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Feed -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900"><?php _e('Activity Feed', 'mcqhome'); ?></h2>
                    </div>
                    <div class="p-6">
                        <div id="social-activity-feed">
                            <?php
                            $activities = mcqhome_get_activity_feed($user_id, 10);
                            
                            if (empty($activities)) {
                                echo '<div class="text-center py-12">';
                                echo '<svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
                                echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>';
                                echo '</svg>';
                                echo '<h3 class="mt-4 text-lg font-medium text-gray-900">' . __('No activity yet', 'mcqhome') . '</h3>';
                                echo '<p class="mt-2 text-gray-500">' . __('Follow institutions and teachers to see their latest activity here.', 'mcqhome') . '</p>';
                                echo '<div class="mt-6">';
                                echo '<a href="' . home_url('/institutions/') . '" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">';
                                echo __('Browse Institutions', 'mcqhome');
                                echo '</a>';
                                echo '</div>';
                                echo '</div>';
                            } else {
                                echo '<div class="space-y-6">';
                                foreach ($activities as $activity) {
                                    mcqhome_render_activity_item($activity);
                                }
                                echo '</div>';
                                
                                echo '<div class="mt-6 text-center">';
                                echo '<button class="load-more-activity-btn text-blue-600 hover:text-blue-500 font-medium" data-user-id="' . $user_id . '" data-page="1">';
                                echo __('Load more activity', 'mcqhome');
                                echo '</button>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Following Summary -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Following', 'mcqhome'); ?></h3>
                    </div>
                    <div class="p-6">
                        <?php
                        $following_institutions = get_user_meta($user_id, 'following_institutions', true);
                        $following_teachers = get_user_meta($user_id, 'following_teachers', true);
                        $total_following = 0;
                        
                        if (is_array($following_institutions)) $total_following += count($following_institutions);
                        if (is_array($following_teachers)) $total_following += count($following_teachers);
                        ?>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600"><?php echo $total_following; ?></div>
                            <div class="text-sm text-gray-500"><?php _e('Following', 'mcqhome'); ?></div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                            <div>
                                <div class="text-lg font-semibold text-gray-900">
                                    <?php echo is_array($following_institutions) ? count($following_institutions) : 0; ?>
                                </div>
                                <div class="text-xs text-gray-500"><?php _e('Institutions', 'mcqhome'); ?></div>
                            </div>
                            <div>
                                <div class="text-lg font-semibold text-gray-900">
                                    <?php echo is_array($following_teachers) ? count($following_teachers) : 0; ?>
                                </div>
                                <div class="text-xs text-gray-500"><?php _e('Teachers', 'mcqhome'); ?></div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="<?php echo home_url('/dashboard/?tab=following'); ?>" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <?php _e('Manage Following', 'mcqhome'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Notifications -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Recent Notifications', 'mcqhome'); ?></h3>
                        <?php
                        $unread_count = mcqhome_get_unread_notifications_count($user_id);
                        if ($unread_count > 0) {
                            echo '<span class="bg-red-500 text-white text-xs rounded-full px-2 py-1">' . $unread_count . '</span>';
                        }
                        ?>
                    </div>
                    <div class="p-6">
                        <?php
                        $notifications = mcqhome_get_user_notifications($user_id, 3);
                        
                        if (empty($notifications)) {
                            echo '<div class="text-center py-6">';
                            echo '<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
                            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 17H7l4 4v-4zM13 3h4l-2 2-2-2zM3 13v4l2-2-2-2z"></path>';
                            echo '</svg>';
                            echo '<p class="mt-2 text-sm text-gray-500">' . __('No notifications yet', 'mcqhome') . '</p>';
                            echo '</div>';
                        } else {
                            echo '<div class="space-y-3">';
                            foreach ($notifications as $notification) {
                                mcqhome_render_notification_item($notification);
                            }
                            echo '</div>';
                            
                            echo '<div class="mt-4">';
                            echo '<a href="' . home_url('/dashboard/?tab=notifications') . '" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">';
                            echo __('View All Notifications', 'mcqhome');
                            echo '</a>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Quick Actions', 'mcqhome'); ?></h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="<?php echo home_url('/institutions/'); ?>" class="block w-full text-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <?php _e('Browse Institutions', 'mcqhome'); ?>
                        </a>
                        <a href="<?php echo home_url('/browse/'); ?>" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <?php _e('Browse MCQ Sets', 'mcqhome'); ?>
                        </a>
                        <a href="<?php echo home_url('/dashboard/'); ?>" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <?php _e('My Dashboard', 'mcqhome'); ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-refresh feed every 5 minutes
    setInterval(function() {
        // Refresh activity feed
        $.ajax({
            url: mcqhome_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mcqhome_get_activity_feed',
                limit: 5,
                nonce: mcqhome_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.activities.length > 0) {
                    // Add new activities to the top of the feed
                    var $feed = $('#social-activity-feed .space-y-6');
                    if ($feed.length) {
                        response.data.activities.forEach(function(activity) {
                            // Check if activity already exists
                            if (!$feed.find('[data-activity-id="' + activity.id + '"]').length) {
                                var $newActivity = $(MCQHomeDashboard.renderActivityItem(activity));
                                $newActivity.addClass('new').hide().prependTo($feed).slideDown();
                            }
                        });
                    }
                }
            }
        });
    }, 300000); // 5 minutes
});
</script>

<?php get_footer(); ?>