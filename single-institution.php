<?php
/**
 * Single Institution Template
 * 
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('institution-single'); ?>>
            
            <!-- Institution Header -->
            <header class="institution-header mb-8">
                <div class="flex flex-col md:flex-row md:items-start gap-6">
                    
                    <!-- Institution Logo/Thumbnail -->
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="institution-logo flex-shrink-0">
                            <?php the_post_thumbnail('medium', array('class' => 'w-32 h-32 object-cover rounded-lg')); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Institution Info -->
                    <div class="institution-info flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php the_title(); ?></h1>
                        
                        <div class="institution-meta flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <?php printf(__('Established %s', 'mcqhome'), get_the_date()); ?>
                            </span>
                            
                            <?php
                            // Count MCQ Sets from this institution
                            $mcq_sets = get_posts(array(
                                'post_type' => 'mcq_set',
                                'author' => get_the_author_meta('ID'),
                                'post_status' => 'publish',
                                'numberposts' => -1
                            ));
                            $mcq_count = count($mcq_sets);
                            ?>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <?php printf(_n('%d MCQ Set', '%d MCQ Sets', $mcq_count, 'mcqhome'), $mcq_count); ?>
                            </span>
                        </div>
                        
                        <?php if (has_excerpt()) : ?>
                            <div class="institution-excerpt text-lg text-gray-700">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Institution Content -->
            <div class="institution-content mb-8">
                <?php if (get_the_content()) : ?>
                    <div class="prose max-w-none mb-8">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Institution MCQ Sets -->
                <?php if (!empty($mcq_sets)) : ?>
                    <div class="institution-mcq-sets">
                        <h3 class="text-2xl font-semibold mb-6"><?php _e('MCQ Sets from this Institution', 'mcqhome'); ?></h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($mcq_sets as $mcq_set) : ?>
                                <div class="mcq-set-card bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                    <h4 class="text-lg font-semibold mb-2">
                                        <a href="<?php echo get_permalink($mcq_set->ID); ?>" class="text-gray-900 hover:text-blue-600">
                                            <?php echo esc_html($mcq_set->post_title); ?>
                                        </a>
                                    </h4>
                                    
                                    <?php if ($mcq_set->post_excerpt) : ?>
                                        <p class="text-gray-600 text-sm mb-4"><?php echo esc_html($mcq_set->post_excerpt); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $questions = get_post_meta($mcq_set->ID, '_mcq_set_questions', true);
                                    $question_count = is_array($questions) ? count($questions) : 0;
                                    ?>
                                    
                                    <div class="mcq-set-meta text-xs text-gray-500 mb-4">
                                        <span><?php printf(_n('%d Question', '%d Questions', $question_count, 'mcqhome'), $question_count); ?></span>
                                        <span class="mx-2">•</span>
                                        <span><?php echo get_the_date('M j, Y', $mcq_set->ID); ?></span>
                                    </div>
                                    
                                    <a href="<?php echo get_permalink($mcq_set->ID); ?>" 
                                       class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                        <?php _e('View MCQ Set', 'mcqhome'); ?>
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="no-mcq-sets text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2"><?php _e('No MCQ Sets Yet', 'mcqhome'); ?></h3>
                        <p class="text-gray-500"><?php _e('This institution hasn\'t published any MCQ sets yet.', 'mcqhome'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>