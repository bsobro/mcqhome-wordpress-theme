<?php
/**
 * Template for displaying single MCQ Set posts
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header();

// Check if user is logged in
if (!is_user_logged_in()) {
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold text-yellow-800 mb-2"><?php _e('Login Required', 'mcqhome'); ?></h2>
            <p class="text-yellow-700 mb-4"><?php _e('Please log in to view and take MCQ assessments.', 'mcqhome'); ?></p>
            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                <?php _e('Login', 'mcqhome'); ?>
            </a>
        </div>
    </div>
    <?php
    get_footer();
    exit;
}

while (have_posts()) :
    the_post();
    
    $user_id = get_current_user_id();
    $mcq_set_id = get_the_ID();
    
    // Get MCQ set data
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    $display_format = get_post_meta($mcq_set_id, '_mcq_set_display_format', true) ?: 'next_next';
    $time_limit = get_post_meta($mcq_set_id, '_mcq_set_time_limit', true);
    $total_marks = get_post_meta($mcq_set_id, '_mcq_set_total_marks', true);
    $passing_marks = get_post_meta($mcq_set_id, '_mcq_set_passing_marks', true);
    $negative_marking = get_post_meta($mcq_set_id, '_mcq_set_negative_marking', true) ?: 0;
    $pricing_type = get_post_meta($mcq_set_id, '_mcq_set_pricing_type', true) ?: 'free';
    $price = get_post_meta($mcq_set_id, '_mcq_set_price', true) ?: 0;
    $allow_retakes = get_post_meta($mcq_set_id, '_mcq_set_allow_retakes', true);
    $show_results = get_post_meta($mcq_set_id, '_mcq_set_show_results', true) !== 'no';
    
    // Get taxonomies
    $subjects = get_the_terms($mcq_set_id, 'mcq_subject');
    $topics = get_the_terms($mcq_set_id, 'mcq_topic');
    
    // Get author and institution info
    $author = get_userdata(get_the_author_meta('ID'));
    $author_role = function_exists('mcqhome_get_user_primary_role') ? mcqhome_get_user_primary_role($author->ID) : 'author';
    $institution_id = get_post_meta($mcq_set_id, 'institution_id', true);
    $institution = $institution_id ? get_post($institution_id) : null;
    
    // Check user enrollment and progress
    $is_enrolled = false;
    $user_progress = null;
    $user_attempts = [];
    $best_attempt = null;
    
    if (function_exists('mcqhome_check_user_enrollment')) {
        try {
            $is_enrolled = mcqhome_check_user_enrollment($user_id, $mcq_set_id);
        } catch (Exception $e) {
            $is_enrolled = true; // Allow access if database isn't ready
        }
    }
    
    if (function_exists('mcqhome_get_user_progress')) {
        try {
            $user_progress = mcqhome_get_user_progress($user_id, $mcq_set_id);
        } catch (Exception $e) {
            $user_progress = null;
        }
    }
    
    if (function_exists('mcqhome_get_user_attempts')) {
        try {
            $user_attempts = mcqhome_get_user_attempts($user_id, $mcq_set_id);
            $best_attempt = function_exists('mcqhome_get_user_best_attempt') ? mcqhome_get_user_best_attempt($user_id, $mcq_set_id) : null;
        } catch (Exception $e) {
            $user_attempts = [];
            $best_attempt = null;
        }
    }
    
    // Calculate statistics
    $total_questions = is_array($mcq_ids) ? count($mcq_ids) : 0;
    $has_completed = !empty($user_attempts);
    $has_ongoing = $user_progress !== null;
    $can_retake = $allow_retakes || !$has_completed;
    
    // Get MCQ set statistics
    $set_stats = null;
    if (function_exists('mcqhome_get_mcq_set_stats')) {
        try {
            $set_stats = mcqhome_get_mcq_set_stats($mcq_set_id);
        } catch (Exception $e) {
            $set_stats = null;
        }
    }
    ?>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="<?php echo home_url(); ?>" class="hover:text-blue-600"><?php _e('Home', 'mcqhome'); ?></a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="<?php echo home_url('/browse/'); ?>" class="hover:text-blue-600"><?php _e('Browse MCQ Sets', 'mcqhome'); ?></a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900"><?php the_title(); ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2">
                
                <!-- MCQ Set Header -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-3"><?php the_title(); ?></h1>
                            
                            <!-- Meta Info -->
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                                <?php if ($institution): ?>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>
                                    </svg>
                                    <a href="<?php echo get_permalink($institution->ID); ?>" class="hover:text-blue-600"><?php echo esc_html($institution->post_title); ?></a>
                                </span>
                                <?php endif; ?>
                                
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <a href="<?php echo get_author_posts_url($author->ID); ?>" class="hover:text-blue-600"><?php printf(__('by %s', 'mcqhome'), $author->display_name); ?></a>
                                </span>
                                
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?php echo get_the_date(); ?>
                                </span>
                                
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <?php printf(__('%d Questions', 'mcqhome'), $total_questions); ?>
                                </span>
                            </div>
                            
                            <!-- Taxonomies -->
                            <?php if (($subjects && !is_wp_error($subjects)) || ($topics && !is_wp_error($topics))): ?>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php if ($subjects && !is_wp_error($subjects)): ?>
                                    <?php foreach ($subjects as $subject): ?>
                                        <a href="<?php echo get_term_link($subject); ?>" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">
                                            <?php echo esc_html($subject->name); ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if ($topics && !is_wp_error($topics)): ?>
                                    <?php foreach ($topics as $topic): ?>
                                        <a href="<?php echo get_term_link($topic); ?>" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 hover:bg-purple-200">
                                            <?php echo esc_html($topic->name); ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pricing Badge -->
                        <div class="flex-shrink-0">
                            <?php if ($pricing_type === 'free'): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <?php _e('Free', 'mcqhome'); ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <?php printf(__('$%s', 'mcqhome'), number_format($price, 2)); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <?php if (get_the_content()): ?>
                    <div class="prose max-w-none mb-6">
                        <?php the_content(); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Assessment Configuration -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php _e('Assessment Details', 'mcqhome'); ?></h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Total Questions:', 'mcqhome'); ?></span>
                                <span class="font-semibold"><?php echo $total_questions; ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Total Marks:', 'mcqhome'); ?></span>
                                <span class="font-semibold"><?php echo $total_marks ?: $total_questions; ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Passing Marks:', 'mcqhome'); ?></span>
                                <span class="font-semibold"><?php echo $passing_marks ?: ceil(($total_marks ?: $total_questions) * 0.6); ?></span>
                            </div>
                            
                            <?php if ($negative_marking > 0): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Negative Marking:', 'mcqhome'); ?></span>
                                <span class="font-semibold text-red-600"><?php printf(__('-%s per wrong answer', 'mcqhome'), $negative_marking); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="space-y-4">
                            <?php if ($time_limit): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Time Limit:', 'mcqhome'); ?></span>
                                <span class="font-semibold"><?php printf(__('%d minutes', 'mcqhome'), $time_limit); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Display Format:', 'mcqhome'); ?></span>
                                <span class="font-semibold">
                                    <?php echo $display_format === 'single_page' ? __('Single Page', 'mcqhome') : __('Next-Next Format', 'mcqhome'); ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Retakes Allowed:', 'mcqhome'); ?></span>
                                <span class="font-semibold">
                                    <?php echo $allow_retakes ? __('Yes', 'mcqhome') : __('No', 'mcqhome'); ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"><?php _e('Show Results:', 'mcqhome'); ?></span>
                                <span class="font-semibold">
                                    <?php echo $show_results ? __('Yes', 'mcqhome') : __('No', 'mcqhome'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Progress & Attempts -->
                <?php if ($has_completed || $has_ongoing): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php _e('Your Progress', 'mcqhome'); ?></h2>
                    
                    <?php if ($has_ongoing): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="font-medium text-blue-900"><?php _e('Assessment in Progress', 'mcqhome'); ?></h3>
                                <p class="text-blue-700 text-sm"><?php _e('You have an ongoing assessment. You can continue where you left off.', 'mcqhome'); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($best_attempt): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <h3 class="font-medium text-green-900 mb-2"><?php _e('Best Score', 'mcqhome'); ?></h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-green-700"><?php _e('Score:', 'mcqhome'); ?></span>
                                <span class="font-semibold text-green-900"><?php echo $best_attempt->score_percentage; ?>%</span>
                            </div>
                            <div>
                                <span class="text-green-700"><?php _e('Marks:', 'mcqhome'); ?></span>
                                <span class="font-semibold text-green-900"><?php echo $best_attempt->total_score; ?>/<?php echo $best_attempt->max_score; ?></span>
                            </div>
                            <div>
                                <span class="text-green-700"><?php _e('Result:', 'mcqhome'); ?></span>
                                <span class="font-semibold <?php echo $best_attempt->is_passed ? 'text-green-900' : 'text-red-600'; ?>">
                                    <?php echo $best_attempt->is_passed ? __('Passed', 'mcqhome') : __('Failed', 'mcqhome'); ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-green-700"><?php _e('Date:', 'mcqhome'); ?></span>
                                <span class="font-semibold text-green-900"><?php echo date_i18n(get_option('date_format'), strtotime($best_attempt->completed_at)); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($user_attempts)): ?>
                    <div class="mt-4">
                        <h3 class="font-medium text-gray-900 mb-3"><?php _e('Attempt History', 'mcqhome'); ?></h3>
                        <div class="space-y-2">
                            <?php foreach (array_slice($user_attempts, 0, 5) as $attempt): ?>
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-600"><?php echo date_i18n(get_option('date_format'), strtotime($attempt->completed_at)); ?></span>
                                    <span class="text-sm font-medium"><?php echo $attempt->score_percentage; ?>%</span>
                                    <span class="text-xs px-2 py-1 rounded <?php echo $attempt->is_passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $attempt->is_passed ? __('Passed', 'mcqhome') : __('Failed', 'mcqhome'); ?>
                                    </span>
                                </div>
                                <?php if ($show_results): ?>
                                <a href="<?php echo add_query_arg(['set_id' => $mcq_set_id, 'attempt_id' => $attempt->id], home_url('/assessment-results/')); ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <?php _e('View Results', 'mcqhome'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Questions Preview -->
                <?php if (!empty($mcq_ids)): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php _e('Questions Preview', 'mcqhome'); ?></h2>
                    <p class="text-gray-600 mb-4"><?php _e('Here are the first few questions from this MCQ set:', 'mcqhome'); ?></p>
                    
                    <div class="space-y-4">
                        <?php 
                        $preview_count = min(3, count($mcq_ids));
                        for ($i = 0; $i < $preview_count; $i++):
                            $mcq_id = $mcq_ids[$i];
                            $mcq = get_post($mcq_id);
                            if (!$mcq) continue;
                            
                            $question_text = get_post_meta($mcq_id, '_mcq_question_text', true);
                            $question_text = wp_trim_words(strip_tags($question_text), 20, '...');
                        ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                    <?php echo $i + 1; ?>
                                </span>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900 mb-1"><?php echo esc_html($mcq->post_title); ?></h3>
                                    <?php if ($question_text): ?>
                                        <p class="text-gray-600 text-sm"><?php echo esc_html($question_text); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                        
                        <?php if (count($mcq_ids) > 3): ?>
                        <div class="text-center py-4">
                            <p class="text-gray-500 text-sm"><?php printf(__('... and %d more questions', 'mcqhome'), count($mcq_ids) - 3); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                
                <!-- Action Panel -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Take Assessment', 'mcqhome'); ?></h3>
                    
                    <?php if (!$is_enrolled && $pricing_type === 'paid'): ?>
                        <!-- Enrollment Required -->
                        <div class="text-center">
                            <p class="text-gray-600 mb-4"><?php _e('Enroll to access this assessment', 'mcqhome'); ?></p>
                            <button class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium">
                                <?php printf(__('Enroll for $%s', 'mcqhome'), number_format($price, 2)); ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Assessment Actions -->
                        <div class="space-y-3">
                            <?php if ($has_ongoing): ?>
                                <a href="<?php echo add_query_arg('set_id', $mcq_set_id, home_url('/take-assessment/')); ?>" class="block w-full bg-blue-600 text-white text-center py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    <?php _e('Continue Assessment', 'mcqhome'); ?>
                                </a>
                            <?php elseif ($can_retake): ?>
                                <a href="<?php echo add_query_arg('set_id', $mcq_set_id, home_url('/take-assessment/')); ?>" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium">
                                    <?php echo $has_completed ? __('Retake Assessment', 'mcqhome') : __('Start Assessment', 'mcqhome'); ?>
                                </a>
                            <?php else: ?>
                                <div class="text-center">
                                    <p class="text-gray-600 mb-2"><?php _e('Assessment completed', 'mcqhome'); ?></p>
                                    <p class="text-sm text-gray-500"><?php _e('Retakes not allowed', 'mcqhome'); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($has_completed && $show_results && $best_attempt): ?>
                                <a href="<?php echo add_query_arg(['set_id' => $mcq_set_id, 'attempt_id' => $best_attempt->id], home_url('/assessment-results/')); ?>" class="block w-full bg-gray-100 text-gray-800 text-center py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                                    <?php _e('View Best Results', 'mcqhome'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Statistics -->
                <?php if ($set_stats): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Statistics', 'mcqhome'); ?></h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Total Attempts:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php echo $set_stats->total_attempts ?: 0; ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Unique Users:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php echo $set_stats->total_users ?: 0; ?></span>
                        </div>
                        
                        <?php if ($set_stats->avg_score): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Average Score:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php echo round($set_stats->avg_score, 1); ?>%</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($set_stats->passed_attempts): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Pass Rate:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php echo round(($set_stats->passed_attempts / $set_stats->total_attempts) * 100, 1); ?>%</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Author Info -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Created By', 'mcqhome'); ?></h3>
                    
                    <div class="flex items-center mb-3">
                        <?php echo get_avatar($author->ID, 48, '', '', ['class' => 'w-12 h-12 rounded-full mr-3']); ?>
                        <div>
                            <h4 class="font-medium text-gray-900"><?php echo $author->display_name; ?></h4>
                            <p class="text-sm text-gray-600"><?php echo ucfirst($author_role); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($author->description): ?>
                        <p class="text-gray-700 text-sm mb-3"><?php echo esc_html($author->description); ?></p>
                    <?php endif; ?>
                    
                    <div class="space-y-2">
                        <a href="<?php echo get_author_posts_url($author->ID); ?>" class="block text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <?php _e('View all MCQ sets by this author', 'mcqhome'); ?> →
                        </a>
                        
                        <?php if ($institution): ?>
                        <a href="<?php echo get_permalink($institution->ID); ?>" class="block text-purple-600 hover:text-purple-800 text-sm font-medium">
                            <?php printf(__('Visit %s', 'mcqhome'), $institution->post_title); ?> →
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Related MCQ Sets -->
                <?php
                $related_args = [
                    'post_type' => 'mcq_set',
                    'posts_per_page' => 3,
                    'post__not_in' => [$mcq_set_id],
                    'post_status' => 'publish'
                ];
                
                if ($subjects && !is_wp_error($subjects)) {
                    $related_args['tax_query'] = [
                        [
                            'taxonomy' => 'mcq_subject',
                            'field' => 'term_id',
                            'terms' => wp_list_pluck($subjects, 'term_id')
                        ]
                    ];
                }
                
                $related_query = new WP_Query($related_args);
                
                if ($related_query->have_posts()):
                ?>
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Related MCQ Sets', 'mcqhome'); ?></h3>
                    
                    <div class="space-y-3">
                        <?php while ($related_query->have_posts()): $related_query->the_post(); ?>
                        <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                            <h4 class="font-medium text-gray-900 mb-1">
                                <a href="<?php the_permalink(); ?>" class="hover:text-blue-600"><?php the_title(); ?></a>
                            </h4>
                            <p class="text-sm text-gray-600">
                                <?php 
                                $related_mcq_ids = get_post_meta(get_the_ID(), '_mcq_set_questions', true);
                                $related_count = is_array($related_mcq_ids) ? count($related_mcq_ids) : 0;
                                printf(__('%d questions', 'mcqhome'), $related_count);
                                ?>
                            </p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="<?php echo home_url('/browse/'); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <?php _e('Browse all MCQ sets', 'mcqhome'); ?> →
                        </a>
                    </div>
                </div>
                <?php 
                wp_reset_postdata();
                endif; 
                ?>

            </div>
        </div>
    </div>

<?php
endwhile;

get_footer();