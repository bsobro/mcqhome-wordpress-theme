<?php
/**
 * Template for displaying single MCQ posts
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
            <p class="text-yellow-700 mb-4"><?php _e('Please log in to view MCQ questions.', 'mcqhome'); ?></p>
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
    
    // Get MCQ data
    $question_text = get_post_meta(get_the_ID(), '_mcq_question_text', true);
    $option_a = get_post_meta(get_the_ID(), '_mcq_option_a', true);
    $option_b = get_post_meta(get_the_ID(), '_mcq_option_b', true);
    $option_c = get_post_meta(get_the_ID(), '_mcq_option_c', true);
    $option_d = get_post_meta(get_the_ID(), '_mcq_option_d', true);
    $correct_answer = get_post_meta(get_the_ID(), '_mcq_correct_answer', true);
    $explanation = get_post_meta(get_the_ID(), '_mcq_explanation', true);
    $marks = get_post_meta(get_the_ID(), '_mcq_marks', true) ?: 1;
    $negative_marks = get_post_meta(get_the_ID(), '_mcq_negative_marks', true) ?: 0;
    $time_limit = get_post_meta(get_the_ID(), '_mcq_time_limit', true);
    $hint = get_post_meta(get_the_ID(), '_mcq_hint', true);
    $reference = get_post_meta(get_the_ID(), '_mcq_reference', true);
    
    // Get taxonomies
    $subjects = get_the_terms(get_the_ID(), 'mcq_subject');
    $topics = get_the_terms(get_the_ID(), 'mcq_topic');
    $difficulty = get_the_terms(get_the_ID(), 'mcq_difficulty');
    
    // Get author info
    $author = get_userdata(get_the_author_meta('ID'));
    $author_role = mcqhome_get_user_primary_role($author->ID);
    ?>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="<?php echo home_url(); ?>" class="hover:text-blue-600"><?php _e('Home', 'mcqhome'); ?></a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="<?php echo home_url('/browse/'); ?>" class="hover:text-blue-600"><?php _e('Browse MCQs', 'mcqhome'); ?></a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900"><?php the_title(); ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-2">
                
                <!-- MCQ Header -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php the_title(); ?></h1>
                            
                            <!-- Meta Info -->
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <?php printf(__('by %s', 'mcqhome'), $author->display_name); ?>
                                </span>
                                
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?php echo get_the_date(); ?>
                                </span>
                                
                                <?php if ($marks): ?>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                    <?php printf(__('%s marks', 'mcqhome'), $marks); ?>
                                </span>
                                <?php endif; ?>
                                
                                <?php if ($time_limit): ?>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?php printf(__('%d seconds', 'mcqhome'), $time_limit); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Difficulty Badge -->
                        <?php if ($difficulty && !is_wp_error($difficulty)): ?>
                        <div class="flex-shrink-0">
                            <?php 
                            $difficulty_name = $difficulty[0]->name;
                            $difficulty_color = 'bg-gray-100 text-gray-800';
                            if ($difficulty[0]->slug === 'easy') $difficulty_color = 'bg-green-100 text-green-800';
                            elseif ($difficulty[0]->slug === 'medium') $difficulty_color = 'bg-yellow-100 text-yellow-800';
                            elseif ($difficulty[0]->slug === 'hard') $difficulty_color = 'bg-red-100 text-red-800';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $difficulty_color; ?>">
                                <?php echo esc_html($difficulty_name); ?>
                            </span>
                        </div>
                        <?php endif; ?>
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

                <!-- MCQ Question -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Question', 'mcqhome'); ?></h2>
                    
                    <?php if ($question_text): ?>
                        <div class="prose max-w-none mb-6">
                            <?php echo wp_kses_post($question_text); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Answer Options -->
                    <div class="space-y-3">
                        <?php 
                        $options = [
                            'A' => $option_a,
                            'B' => $option_b,
                            'C' => $option_c,
                            'D' => $option_d
                        ];
                        
                        foreach ($options as $key => $option):
                            if (empty($option)) continue;
                            $is_correct = ($key === $correct_answer);
                        ?>
                        <div class="flex items-start p-3 rounded-lg border <?php echo $is_correct ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50'; ?>">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full <?php echo $is_correct ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700'; ?> flex items-center justify-center text-sm font-medium mr-3">
                                <?php echo $key; ?>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-900"><?php echo esc_html($option); ?></p>
                                <?php if ($is_correct): ?>
                                    <p class="text-green-600 text-sm mt-1 font-medium">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <?php _e('Correct Answer', 'mcqhome'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Explanation -->
                <?php if ($explanation): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Explanation', 'mcqhome'); ?></h2>
                    <div class="prose max-w-none">
                        <?php echo wp_kses_post($explanation); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Hint -->
                <?php if ($hint): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-blue-900 mb-4">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <?php _e('Hint', 'mcqhome'); ?>
                    </h2>
                    <p class="text-blue-800"><?php echo esc_html($hint); ?></p>
                </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                
                <!-- MCQ Details -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('MCQ Details', 'mcqhome'); ?></h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Marks:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php echo $marks; ?></span>
                        </div>
                        
                        <?php if ($negative_marks > 0): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Negative Marks:', 'mcqhome'); ?></span>
                            <span class="font-medium text-red-600">-<?php echo $negative_marks; ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($time_limit): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Time Limit:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php printf(__('%d sec', 'mcqhome'), $time_limit); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Created:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php echo get_the_date(); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php _e('Author:', 'mcqhome'); ?></span>
                            <span class="font-medium"><?php echo $author->display_name; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Reference -->
                <?php if ($reference): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Reference', 'mcqhome'); ?></h3>
                    <p class="text-gray-700"><?php echo esc_html($reference); ?></p>
                </div>
                <?php endif; ?>

                <!-- Author Info -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('About the Author', 'mcqhome'); ?></h3>
                    
                    <div class="flex items-center mb-3">
                        <?php echo get_avatar($author->ID, 48, '', '', ['class' => 'w-12 h-12 rounded-full mr-3']); ?>
                        <div>
                            <h4 class="font-medium text-gray-900"><?php echo $author->display_name; ?></h4>
                            <p class="text-sm text-gray-600"><?php echo ucfirst($author_role); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($author->description): ?>
                        <p class="text-gray-700 text-sm"><?php echo esc_html($author->description); ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="<?php echo get_author_posts_url($author->ID); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <?php _e('View all MCQs by this author', 'mcqhome'); ?> →
                        </a>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Actions', 'mcqhome'); ?></h3>
                    
                    <div class="space-y-3">
                        <a href="<?php echo home_url('/browse/'); ?>" class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            <?php _e('Browse More MCQs', 'mcqhome'); ?>
                        </a>
                        
                        <?php if ($subjects && !is_wp_error($subjects)): ?>
                            <a href="<?php echo get_term_link($subjects[0]); ?>" class="block w-full bg-gray-100 text-gray-800 text-center py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                                <?php printf(__('More from %s', 'mcqhome'), $subjects[0]->name); ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo get_author_posts_url($author->ID); ?>" class="block w-full bg-gray-100 text-gray-800 text-center py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                            <?php printf(__('More by %s', 'mcqhome'), $author->display_name); ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php
endwhile;

get_footer();
