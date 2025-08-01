<?php
/**
 * Template for displaying MCQ subject taxonomy archives
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header();

$term = get_queried_object();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Subject Header -->
    <div class="subject-header bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2"><?php echo esc_html($term->name); ?></h1>
                <?php if ($term->description) : ?>
                    <p class="text-blue-100 text-lg"><?php echo esc_html($term->description); ?></p>
                <?php endif; ?>
                
                <div class="subject-meta mt-4 flex items-center gap-4 text-blue-100">
                    <span class="flex items-center gap-1">
                        <i class="fas fa-book"></i>
                        <?php printf(_n('%d item', '%d items', $term->count, 'mcqhome'), $term->count); ?>
                    </span>
                    
                    <?php
                    // Get related topics
                    $related_topics = get_terms([
                        'taxonomy' => 'mcq_topic',
                        'hide_empty' => true,
                        'meta_query' => [
                            [
                                'key' => 'related_subject',
                                'value' => $term->term_id,
                                'compare' => '='
                            ]
                        ]
                    ]);
                    
                    if (!empty($related_topics) && !is_wp_error($related_topics)) :
                    ?>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-tags"></i>
                            <?php printf(_n('%d topic', '%d topics', count($related_topics), 'mcqhome'), count($related_topics)); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="subject-icon text-6xl opacity-20">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
    
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="<?php echo home_url('/browse/'); ?>" class="hover:text-blue-600"><?php _e('Browse', 'mcqhome'); ?></a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="<?php echo home_url('/browse/?view=subjects'); ?>" class="hover:text-blue-600"><?php _e('Subjects', 'mcqhome'); ?></a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-blue-600 font-medium"><?php echo esc_html($term->name); ?></li>
        </ol>
    </nav>
    
    <!-- Related Topics and Subtopics -->
    <?php if (!empty($related_topics) && !is_wp_error($related_topics)) : ?>
        <div class="related-topics bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold"><?php _e('Topics in this Subject', 'mcqhome'); ?></h2>
                <span class="text-sm text-gray-500"><?php printf(_n('%d topic', '%d topics', count($related_topics), 'mcqhome'), count($related_topics)); ?></span>
            </div>
            
            <!-- Organize topics hierarchically -->
            <?php
            // Separate parent topics from child topics
            $parent_topics = [];
            $child_topics = [];
            
            foreach ($related_topics as $topic) {
                if ($topic->parent == 0) {
                    $parent_topics[] = $topic;
                } else {
                    if (!isset($child_topics[$topic->parent])) {
                        $child_topics[$topic->parent] = [];
                    }
                    $child_topics[$topic->parent][] = $topic;
                }
            }
            ?>
            
            <div class="topics-hierarchy">
                <?php if (!empty($parent_topics)) : ?>
                    <div class="parent-topics mb-6">
                        <h3 class="text-lg font-medium mb-3"><?php _e('Main Topics', 'mcqhome'); ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($parent_topics as $parent_topic) : ?>
                                <div class="topic-group bg-gray-50 rounded-lg p-4">
                                    <a href="<?php echo get_term_link($parent_topic); ?>" 
                                       class="topic-title flex items-center text-blue-700 hover:text-blue-900 font-medium mb-2">
                                        <i class="fas fa-tag mr-2"></i>
                                        <?php echo esc_html($parent_topic->name); ?>
                                        <span class="ml-2 text-sm text-gray-500">(<?php echo $parent_topic->count; ?>)</span>
                                    </a>
                                    
                                    <?php if (isset($child_topics[$parent_topic->term_id])) : ?>
                                        <div class="subtopics mt-2">
                                            <div class="grid grid-cols-1 gap-1">
                                                <?php foreach (array_slice($child_topics[$parent_topic->term_id], 0, 3) as $subtopic) : ?>
                                                    <a href="<?php echo get_term_link($subtopic); ?>" 
                                                       class="subtopic-link text-sm text-gray-600 hover:text-blue-600 pl-4">
                                                        <?php echo esc_html($subtopic->name); ?> (<?php echo $subtopic->count; ?>)
                                                    </a>
                                                <?php endforeach; ?>
                                                
                                                <?php if (count($child_topics[$parent_topic->term_id]) > 3) : ?>
                                                    <a href="<?php echo get_term_link($parent_topic); ?>" 
                                                       class="text-xs text-blue-600 hover:text-blue-800 pl-4">
                                                        <?php printf(__('+ %d more', 'mcqhome'), count($child_topics[$parent_topic->term_id]) - 3); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Standalone topics (no parent) -->
                <?php
                $standalone_topics = array_filter($related_topics, function($topic) use ($parent_topics) {
                    $parent_ids = array_column($parent_topics, 'term_id');
                    return $topic->parent == 0 && !in_array($topic->term_id, $parent_ids);
                });
                
                if (!empty($standalone_topics)) :
                ?>
                    <div class="standalone-topics">
                        <h3 class="text-lg font-medium mb-3"><?php _e('Other Topics', 'mcqhome'); ?></h3>
                        <div class="topics-grid grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <?php foreach ($standalone_topics as $topic) : ?>
                                <a href="<?php echo get_term_link($topic); ?>" 
                                   class="topic-card bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg p-3 text-center transition-colors">
                                    <div class="text-blue-600 text-lg mb-1">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="text-sm font-medium text-gray-800"><?php echo esc_html($topic->name); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $topic->count; ?> <?php _e('items', 'mcqhome'); ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Filters and Search -->
    <div class="filters-section bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="subject-filter-form">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Search', 'mcqhome'); ?>
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo esc_attr(get_query_var('search', '')); ?>"
                           placeholder="<?php _e('Search within subject...', 'mcqhome'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Content Type Filter -->
                <div>
                    <label for="content_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Content Type', 'mcqhome'); ?>
                    </label>
                    <select id="content_type" 
                            name="content_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" <?php selected(get_query_var('content_type'), ''); ?>><?php _e('All Types', 'mcqhome'); ?></option>
                        <option value="mcq_set" <?php selected(get_query_var('content_type'), 'mcq_set'); ?>><?php _e('MCQ Sets', 'mcqhome'); ?></option>
                        <option value="mcq" <?php selected(get_query_var('content_type'), 'mcq'); ?>><?php _e('Individual MCQs', 'mcqhome'); ?></option>
                    </select>
                </div>
                
                <!-- Difficulty Filter -->
                <div>
                    <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Difficulty', 'mcqhome'); ?>
                    </label>
                    <select id="difficulty" 
                            name="difficulty" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php _e('All Levels', 'mcqhome'); ?></option>
                        <?php
                        $difficulties = get_terms([
                            'taxonomy' => 'mcq_difficulty',
                            'hide_empty' => true
                        ]);
                        
                        if ($difficulties && !is_wp_error($difficulties)) :
                            foreach ($difficulties as $difficulty) :
                        ?>
                            <option value="<?php echo $difficulty->slug; ?>" <?php selected(get_query_var('difficulty'), $difficulty->slug); ?>>
                                <?php echo esc_html($difficulty->name); ?>
                            </option>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>
                
                <!-- Sort Options -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Sort By', 'mcqhome'); ?>
                    </label>
                    <select id="sort" 
                            name="sort" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="date" <?php selected(get_query_var('sort'), 'date'); ?>><?php _e('Latest', 'mcqhome'); ?></option>
                        <option value="title" <?php selected(get_query_var('sort'), 'title'); ?>><?php _e('Title', 'mcqhome'); ?></option>
                        <option value="popular" <?php selected(get_query_var('sort'), 'popular'); ?>><?php _e('Most Popular', 'mcqhome'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    <?php _e('Filter', 'mcqhome'); ?>
                </button>
                <a href="<?php echo get_term_link($term); ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    <?php _e('Clear', 'mcqhome'); ?>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Content Results -->
    <div class="content-results">
        <?php
        // Build query arguments
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $search = get_query_var('search');
        $content_type = get_query_var('content_type', 'mcq_set');
        $difficulty = get_query_var('difficulty');
        $sort = get_query_var('sort', 'date');
        
        $args = [
            'post_type' => $content_type ?: ['mcq', 'mcq_set'],
            'posts_per_page' => 12,
            'paged' => $paged,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'mcq_subject',
                    'field' => 'term_id',
                    'terms' => $term->term_id
                ]
            ]
        ];
        
        // Add search
        if ($search) {
            $args['s'] = $search;
        }
        
        // Add difficulty filter
        if ($difficulty) {
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = [];
            }
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => 'mcq_difficulty',
                'field' => 'slug',
                'terms' => $difficulty
            ];
        }
        
        // Add sorting
        switch ($sort) {
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'popular':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }
        
        $subject_query = new WP_Query($args);
        
        if ($subject_query->have_posts()) :
        ?>
            <!-- Results Header -->
            <div class="results-header flex justify-between items-center mb-6">
                <div class="results-count">
                    <span class="text-gray-600">
                        <?php printf(__('Showing %d of %d results for "%s"', 'mcqhome'), 
                            min($subject_query->post_count, $subject_query->found_posts), 
                            $subject_query->found_posts,
                            $term->name); ?>
                    </span>
                </div>
            </div>
            
            <!-- Results Grid -->
            <div class="results-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php while ($subject_query->have_posts()) : $subject_query->the_post(); ?>
                    <div class="content-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
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
                            
                            <!-- Content Stats -->
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
                                $topics = get_the_terms(get_the_ID(), 'mcq_topic');
                                $difficulties = get_the_terms(get_the_ID(), 'mcq_difficulty');
                                
                                if ($topics && !is_wp_error($topics)) :
                                    foreach (array_slice($topics, 0, 2) as $topic) :
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
                                    <?php echo get_post_type() === 'mcq_set' ? __('Take Assessment', 'mcqhome') : __('View Question', 'mcqhome'); ?>
                                </a>
                                
                                <?php
                                $pricing_type = get_post_meta(get_the_ID(), '_pricing_type', true);
                                if ($pricing_type === 'paid') :
                                    $price = get_post_meta(get_the_ID(), '_price', true);
                                ?>
                                    <span class="text-green-600 font-semibold text-sm">
                                        $<?php echo number_format($price, 2); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="text-green-600 font-semibold text-sm">
                                        <?php _e('Free', 'mcqhome'); ?>
                                    </span>
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
                    'total' => $subject_query->max_num_pages,
                    'current' => $paged,
                    'format' => '?paged=%#%',
                    'prev_text' => '<i class="fas fa-chevron-left"></i> ' . __('Previous', 'mcqhome'),
                    'next_text' => __('Next', 'mcqhome') . ' <i class="fas fa-chevron-right"></i>',
                    'class' => 'flex justify-center space-x-2'
                ]);
                ?>
            </div>
            
        <?php 
            wp_reset_postdata();
        else : 
        ?>
            <div class="no-results text-center py-12">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-search text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php _e('No content found', 'mcqhome'); ?></h3>
                <p class="text-gray-600 mb-4"><?php _e('Try adjusting your filters or browse other subjects.', 'mcqhome'); ?></p>
                <a href="<?php echo home_url('/browse/'); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <?php _e('Browse All Content', 'mcqhome'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>