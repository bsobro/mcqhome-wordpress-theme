<?php
/**
 * Archive MCQ Sets Template
 * 
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Archive Header -->
    <header class="archive-header mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php _e('MCQ Sets', 'mcqhome'); ?></h1>
        <p class="text-lg text-gray-600"><?php _e('Browse all available MCQ sets and start practicing!', 'mcqhome'); ?></p>
    </header>

    <!-- MCQ Sets Grid -->
    <?php if (have_posts()) : ?>
        <div class="mcq-sets-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php while (have_posts()) : the_post(); ?>
                <article class="mcq-set-card bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    
                    <!-- Thumbnail -->
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="mcq-set-thumbnail mb-4">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('class' => 'w-full h-48 object-cover rounded-lg')); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Title -->
                    <h2 class="text-lg font-semibold mb-2">
                        <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-blue-600">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    
                    <!-- Excerpt -->
                    <?php if (has_excerpt()) : ?>
                        <p class="text-gray-600 text-sm mb-4"><?php the_excerpt(); ?></p>
                    <?php endif; ?>
                    
                    <!-- Meta Info -->
                    <div class="mcq-set-meta text-xs text-gray-500 mb-4">
                        <span class="flex items-center mb-1">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <?php printf(__('By %s', 'mcqhome'), get_the_author()); ?>
                        </span>
                        
                        <?php
                        $questions = get_post_meta(get_the_ID(), '_mcq_set_questions', true);
                        $question_count = is_array($questions) ? count($questions) : 0;
                        ?>
                        <span class="flex items-center mb-1">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <?php printf(_n('%d Question', '%d Questions', $question_count, 'mcqhome'), $question_count); ?>
                        </span>
                        
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <?php echo get_the_date(); ?>
                        </span>
                    </div>
                    
                    <!-- Action Button -->
                    <a href="<?php the_permalink(); ?>" 
                       class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                        <?php _e('Start Assessment', 'mcqhome'); ?>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => __('← Previous', 'mcqhome'),
                'next_text' => __('Next →', 'mcqhome'),
            ));
            ?>
        </div>

    <?php else : ?>
        
        <!-- No Posts Found -->
        <div class="no-mcq-sets text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h2 class="text-xl font-medium text-gray-900 mb-2"><?php _e('No MCQ Sets Found', 'mcqhome'); ?></h2>
            <p class="text-gray-500"><?php _e('There are no MCQ sets available at the moment. Please check back later!', 'mcqhome'); ?></p>
        </div>

    <?php endif; ?>
</div>

<?php get_footer(); ?>