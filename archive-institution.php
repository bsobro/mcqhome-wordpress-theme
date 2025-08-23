<?php
/**
 * Archive Institutions Template
 * 
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    
    <!-- Archive Header -->
    <header class="archive-header mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php _e('Institutions', 'mcqhome'); ?></h1>
        <p class="text-lg text-gray-600"><?php _e('Explore educational institutions and their MCQ collections.', 'mcqhome'); ?></p>
    </header>

    <!-- Institutions Grid -->
    <?php if (have_posts()) : ?>
        <div class="institutions-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php while (have_posts()) : the_post(); ?>
                <article class="institution-card bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    
                    <!-- Institution Logo -->
                    <div class="institution-logo mb-4 text-center">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('class' => 'institution-archive-logo')); ?>
                            </a>
                        <?php else : ?>
                            <div class="institution-placeholder">
                                <svg class="icon-medium text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Institution Name -->
                    <h2 class="text-lg font-semibold mb-2 text-center">
                        <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-blue-600">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    
                    <!-- Excerpt -->
                    <?php if (has_excerpt()) : ?>
                        <p class="text-gray-600 text-sm mb-4 text-center"><?php the_excerpt(); ?></p>
                    <?php endif; ?>
                    
                    <!-- Institution Stats -->
                    <div class="institution-stats text-center mb-4">
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
                        
                        <div class="flex justify-center items-center space-x-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <?php printf(_n('%d MCQ Set', '%d MCQ Sets', $mcq_count, 'mcqhome'), $mcq_count); ?>
                            </span>
                            
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <?php echo get_the_date('Y'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="text-center">
                        <a href="<?php the_permalink(); ?>" 
                           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                            <?php _e('View Institution', 'mcqhome'); ?>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
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
        <div class="no-institutions text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4 icon-large" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"></path>
            </svg>
            <h2 class="text-xl font-medium text-gray-900 mb-2"><?php _e('No Institutions Found', 'mcqhome'); ?></h2>
            <p class="text-gray-500"><?php _e('There are no institutions registered at the moment. Please check back later!', 'mcqhome'); ?></p>
        </div>

    <?php endif; ?>
</div>

<?php get_footer(); ?>