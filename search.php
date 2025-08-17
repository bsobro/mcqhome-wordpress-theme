<?php
/**
 * Template for displaying search results
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Search Results Header -->
    <div class="search-header mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            <?php printf(__('Search Results for: %s', 'mcqhome'), '<span class="text-blue-600">' . get_search_query() . '</span>'); ?>
        </h1>
        
        <?php if (have_posts()) : ?>
            <p class="text-gray-600">
                <?php printf(_n('Found %d result', 'Found %d results', $wp_query->found_posts, 'mcqhome'), $wp_query->found_posts); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Search Form -->
    <div class="search-form-container bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="<?php echo home_url('/'); ?>" class="flex gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="s" 
                       value="<?php echo esc_attr(get_search_query()); ?>"
                       placeholder="<?php _e('Search MCQ sets, institutions...', 'mcqhome'); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                <?php _e('Search', 'mcqhome'); ?>
            </button>
        </form>
    </div>

    <!-- Search Results -->
    <div class="search-results">
        <?php if (have_posts()) : ?>
            <div class="results-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php while (have_posts()) : the_post(); ?>
                    <div class="result-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Card Header -->
                        <div class="card-header bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4">
                            <h3 class="text-lg font-semibold mb-2">
                                <a href="<?php the_permalink(); ?>" class="hover:text-blue-200">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="card-meta text-blue-100 text-sm">
                                <span><?php echo get_the_author(); ?></span>
                                <span class="mx-2">•</span>
                                <span><?php echo get_the_date(); ?></span>
                                <span class="mx-2">•</span>
                                <span class="capitalize"><?php echo get_post_type(); ?></span>
                            </div>
                        </div>
                        
                        <!-- Card Content -->
                        <div class="card-content p-4">
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </p>
                            
                            <?php if (get_post_type() === 'mcq_set') : ?>
                                <div class="content-stats flex justify-between items-center mb-4 text-sm">
                                    <span class="text-gray-600">
                                        <i class="fas fa-question-circle mr-1"></i>
                                        <?php echo mcqhome_get_mcq_set_question_count(get_the_ID()); ?> <?php _e('questions', 'mcqhome'); ?>
                                    </span>
                                    
                                    <?php 
                                    $rating = mcqhome_get_mcq_set_rating(get_the_ID());
                                    if ($rating > 0) :
                                    ?>
                                        <div class="rating flex items-center">
                                            <div class="stars flex mr-1">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <i class="fas fa-star text-xs <?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-gray-600"><?php echo number_format($rating, 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Tags -->
                            <div class="content-tags mb-4">
                                <?php
                                $subjects = get_the_terms(get_the_ID(), 'mcq_subject');
                                $topics = get_the_terms(get_the_ID(), 'mcq_topic');
                                $difficulties = get_the_terms(get_the_ID(), 'mcq_difficulty');
                                
                                if ($subjects && !is_wp_error($subjects)) :
                                    foreach (array_slice($subjects, 0, 2) as $subject) :
                                ?>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-2 mb-1">
                                        <?php echo esc_html($subject->name); ?>
                                    </span>
                                <?php 
                                    endforeach;
                                endif;
                                
                                if ($topics && !is_wp_error($topics)) :
                                    foreach (array_slice($topics, 0, 1) as $topic) :
                                ?>
                                    <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full mr-2 mb-1">
                                        <?php echo esc_html($topic->name); ?>
                                    </span>
                                <?php 
                                    endforeach;
                                endif;
                                
                                if ($difficulties && !is_wp_error($difficulties)) :
                                    foreach ($difficulties as $difficulty) :
                                        $color_class = '';
                                        switch (strtolower($difficulty->slug)) {
                                            case 'easy':
                                                $color_class = 'bg-green-100 text-green-800';
                                                break;
                                            case 'medium':
                                                $color_class = 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'hard':
                                                $color_class = 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                $color_class = 'bg-gray-100 text-gray-800';
                                        }
                                ?>
                                    <span class="inline-block <?php echo $color_class; ?> text-xs px-2 py-1 rounded-full mr-2 mb-1">
                                        <?php echo esc_html($difficulty->name); ?>
                                    </span>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </div>
                            
                            <!-- Actions -->
                            <div class="card-actions flex justify-between items-center">
                                <a href="<?php the_permalink(); ?>" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition-colors">
                                    <?php echo get_post_type() === 'mcq_set' ? __('Take Assessment', 'mcqhome') : __('View', 'mcqhome'); ?>
                                </a>
                                
                                <?php if (get_post_type() === 'mcq_set') : ?>
                                    <?php
                                    $pricing_type = get_post_meta(get_the_ID(), '_mcq_set_pricing_type', true);
                                    if ($pricing_type === 'paid') :
                                        $price = get_post_meta(get_the_ID(), '_mcq_set_price', true);
                                    ?>
                                        <span class="text-green-600 font-semibold text-sm">
                                            $<?php echo number_format($price, 2); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-green-600 font-semibold text-sm">
                                            <?php _e('Free', 'mcqhome'); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination-wrapper">
                <?php
                echo paginate_links([
                    'prev_text' => '<i class="fas fa-chevron-left"></i> ' . __('Previous', 'mcqhome'),
                    'next_text' => __('Next', 'mcqhome') . ' <i class="fas fa-chevron-right"></i>',
                ]);
                ?>
            </div>
            
        <?php else : ?>
            <div class="no-results text-center py-12">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-search text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php _e('No results found', 'mcqhome'); ?></h3>
                <p class="text-gray-600 mb-4"><?php _e('Try different keywords or browse our content.', 'mcqhome'); ?></p>
                <a href="<?php echo home_url('/browse/'); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <?php _e('Browse Content', 'mcqhome'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>