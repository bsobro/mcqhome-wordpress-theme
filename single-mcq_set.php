<?php
/**
 * Single MCQ Set Template
 * 
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('mcq-set-single'); ?>>
            
            <!-- MCQ Set Header -->
            <header class="mcq-set-header mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php the_title(); ?></h1>
                
                <div class="mcq-set-meta flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php printf(__('By %s', 'mcqhome'), get_the_author()); ?>
                    </span>
                    
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <?php echo get_the_date(); ?>
                    </span>
                    
                    <?php
                    $questions = get_post_meta(get_the_ID(), '_mcq_set_questions', true);
                    $question_count = is_array($questions) ? count($questions) : 0;
                    ?>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <?php printf(_n('%d Question', '%d Questions', $question_count, 'mcqhome'), $question_count); ?>
                    </span>
                </div>
                
                <?php if (has_excerpt()) : ?>
                    <div class="mcq-set-excerpt text-lg text-gray-700 mb-6">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>
            </header>

            <!-- MCQ Set Content -->
            <div class="mcq-set-content mb-8">
                <?php if (get_the_content()) : ?>
                    <div class="prose max-w-none mb-8">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>
                
                <!-- MCQ Set Details -->
                <div class="mcq-set-details bg-gray-50 rounded-lg p-6 mb-8">
                    <h3 class="text-xl font-semibold mb-4"><?php _e('Assessment Details', 'mcqhome'); ?></h3>
                    
                    <?php
                    $settings = get_post_meta(get_the_ID(), '_mcq_set_settings', true);
                    $settings = wp_parse_args($settings, array(
                        'time_limit' => 0,
                        'passing_marks' => 70,
                        'negative_marking' => 0,
                        'display_format' => 'single_page',
                        'is_free' => 'yes'
                    ));
                    ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="detail-item">
                            <span class="label font-medium text-gray-600"><?php _e('Questions:', 'mcqhome'); ?></span>
                            <span class="value text-gray-900"><?php echo $question_count; ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="label font-medium text-gray-600"><?php _e('Time Limit:', 'mcqhome'); ?></span>
                            <span class="value text-gray-900">
                                <?php echo $settings['time_limit'] > 0 ? sprintf(__('%d minutes', 'mcqhome'), $settings['time_limit']) : __('No limit', 'mcqhome'); ?>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="label font-medium text-gray-600"><?php _e('Passing Marks:', 'mcqhome'); ?></span>
                            <span class="value text-gray-900"><?php echo $settings['passing_marks']; ?>%</span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="label font-medium text-gray-600"><?php _e('Access:', 'mcqhome'); ?></span>
                            <span class="value text-gray-900">
                                <?php echo $settings['is_free'] === 'yes' ? __('Free', 'mcqhome') : __('Paid', 'mcqhome'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Start Assessment Button -->
                <div class="mcq-set-actions text-center">
                    <?php if ($question_count > 0) : ?>
                        <a href="<?php echo add_query_arg('action', 'start', get_permalink()); ?>" 
                           class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m6-6V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2z"></path>
                            </svg>
                            <?php _e('Start Assessment', 'mcqhome'); ?>
                        </a>
                    <?php else : ?>
                        <p class="text-gray-500"><?php _e('No questions available in this MCQ set yet.', 'mcqhome'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>