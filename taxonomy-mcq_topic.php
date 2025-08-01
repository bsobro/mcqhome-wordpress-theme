<?php
/**
 * Template for displaying MCQ topic taxonomy archives
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header();

$term = get_queried_object();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Topic Header -->
    <div class="topic-header bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb mb-4">
                    <ol class="flex items-center space-x-2 text-purple-100">
                        <li><a href="<?php echo home_url('/browse/'); ?>" class="hover:text-white"><?php _e('Browse', 'mcqhome'); ?></a></li>
                        <li><span class="mx-2">/</span></li>
                        <?php
                        // Get parent subject if exists
                        $parent_subject = get_term_meta($term->term_id, 'parent_subject', true);
                        if ($parent_subject) {
                            $subject = get_term($parent_subject, 'mcq_subject');
                            if ($subject && !is_wp_error($subject)) :
                        ?>
                            <li><a href="<?php echo get_term_link($subject); ?>" class="hover:text-white"><?php echo esc_html($subject->name); ?></a></li>
                            <li><span class="mx-2">/</span></li>
                        <?php 
                            endif;
                        }
                        ?>
                        <li class="text-white font-medium"><?php echo esc_html($term->name); ?></li>
                    </ol>
                </nav>
                
                <h1 class="text-3xl font-bold mb-2"><?php echo esc_html($term->name); ?></h1>
                <?php if ($term->description) : ?>
                    <p class="text-purple-100 text-lg"><?php echo esc_html($term->description); ?></p>
                <?php endif; ?>
                
                <div class="topic-meta mt-4 flex items-center gap-4 text-purple-100">
                    <span class="flex items-center gap-1">
                        <i class="fas fa-tag"></i>
                        <?php printf(_n('%d item', '%d items', $term->count, 'mcqhome'), $term->count); ?>
                    </span>
                    
                    <?php if ($parent_subject && $subject) : ?>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-book"></i>
                            <a href="<?php echo get_term_link($subject); ?>" class="text-purple-100 hover:text-white">
                                <?php echo esc_html($subject->name); ?>
                            </a>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="topic-icon text-6xl opacity-20">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>
    
    <!-- Related Topics and Subtopics -->
    <?php
    // Get child topics (subtopics)
    $child_topics = get_terms([
        'taxonomy' => 'mcq_topic',
        'parent' => $term->term_id,
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    
    // Get sibling topics
    $sibling_topics = [];
    if ($term->parent) {
        $sibling_topics = get_terms([
            'taxonomy' => 'mcq_topic',
            'parent' => $term->parent,
            'hide_empty' => true,
            'exclude' => [$term->term_id],
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
    }
    ?>
    
    <?php if (!empty($child_topics) || !empty($sibling_topics)) : ?>
        <div class="related-topics bg-white rounded-lg shadow-md p-6 mb-8">
            <?php if (!empty($child_topics)) : ?>
                <div class="subtopics-section mb-6">
                    <h2 class="text-xl font-semibold mb-4"><?php _e('Subtopics', 'mcqhome'); ?></h2>
                    <div class="topics-grid grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <?php foreach ($child_topics as $child_topic) : ?>
                            <a href="<?php echo get_term_link($child_topic); ?>" 
                               class="topic-card bg-purple-50 hover:bg-purple-100 border border-purple-200 hover:border-purple-300 rounded-lg p-3 text-center transition-colors">
                                <div class="text-purple-600 text-lg mb-1">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="text-sm font-medium text-purple-800"><?php echo esc_html($child_topic->name); ?></div>
                                <div class="text-xs text-purple-600"><?php echo $child_topic->count; ?> <?php _e('items', 'mcqhome'); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($sibling_topics)) : ?>
                <div class="related-topics-section">
                    <h2 class="text-xl font-semibold mb-4"><?php _e('Related Topics', 'mcqhome'); ?></h2>
                    <div class="topics-grid grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <?php foreach ($sibling_topics as $sibling_topic) : ?>
                            <a href="<?php echo get_term_link($sibling_topic); ?>" 
                               class="topic-card bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg p-3 text-center transition-colors">
                                <div class="text-blue-600 text-lg mb-1">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="text-sm font-medium text-gray-800"><?php echo esc_html($sibling_topic->name); ?></div>
                                <div class="text-xs text-gray-500"><?php echo $sibling_topic->count; ?> <?php _e('items', 'mcqhome'); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Advanced Filters and Search -->
    <div class="filters-section bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="topic-filter-form">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Search', 'mcqhome'); ?>
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo esc_attr(get_query_var('search', '')); ?>"
                           placeholder="<?php _e('Search within topic...', 'mcqhome'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                
                <!-- Content Type Filter -->
                <div>
                    <label for="content_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Content Type', 'mcqhome'); ?>
                    </label>
                    <select id="content_type" 
                            name="content_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
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
                
                <!-- Institution Filter -->
                <div>
                    <label for="institution" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Institution', 'mcqhome'); ?>
                    </label>
                    <select id="institution" 
                            name="institution" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value=""><?php _e('All Institutions', 'mcqhome'); ?></option>
                        <?php
                        $institutions = get_posts([
                            'post_type' => 'institution',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ]);
                        
                        foreach ($institutions as $institution) :
                        ?>
                            <option value="<?php echo $institution->ID; ?>" <?php selected(get_query_var('institution'), $institution->ID); ?>>
                                <?php echo esc_html($institution->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Sort Options -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Sort By', 'mcqhome'); ?>
                    </label>
                    <select id="sort" 
                            name="sort" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="date" <?php selected(get_query_var('sort'), 'date'); ?>><?php _e('Latest', 'mcqhome'); ?></option>
                        <option value="title" <?php selected(get_query_var('sort'), 'title'); ?>><?php _e('Title', 'mcqhome'); ?></option>
                        <option value="popular" <?php selected(get_query_var('sort'), 'popular'); ?>><?php _e('Most Popular', 'mcqhome'); ?></option>
                        <option value="rating" <?php selected(get_query_var('sort'), 'rating'); ?>><?php _e('Highest Rated', 'mcqhome'); ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Additional Filters Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <!-- Price Filter -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Price', 'mcqhome'); ?>
                    </label>
                    <select id="price" 
                            name="price" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value=""><?php _e('All', 'mcqhome'); ?></option>
                        <option value="free" <?php selected(get_query_var('price'), 'free'); ?>><?php _e('Free', 'mcqhome'); ?></option>
                        <option value="paid" <?php selected(get_query_var('price'), 'paid'); ?>><?php _e('Paid', 'mcqhome'); ?></option>
                    </select>
                </div>
                
                <!-- Teacher Filter -->
                <div>
                    <label for="teacher" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Teacher', 'mcqhome'); ?>
                    </label>
                    <select id="teacher" 
                            name="teacher" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value=""><?php _e('All Teachers', 'mcqhome'); ?></option>
                        <?php
                        // Get teachers who have content in this topic
                        global $wpdb;
                        $teacher_ids = $wpdb->get_col($wpdb->prepare("
                            SELECT DISTINCT p.post_author
                            FROM {$wpdb->posts} p
                            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                            WHERE tt.taxonomy = 'mcq_topic'
                            AND tt.term_id = %d
                            AND p.post_type IN ('mcq', 'mcq_set')
                            AND p.post_status = 'publish'
                        ", $term->term_id));
                        
                        if (!empty($teacher_ids)) {
                            $teachers = get_users([
                                'include' => $teacher_ids,
                                'orderby' => 'display_name',
                                'order' => 'ASC'
                            ]);
                            
                            foreach ($teachers as $teacher) :
                        ?>
                            <option value="<?php echo $teacher->ID; ?>" <?php selected(get_query_var('teacher'), $teacher->ID); ?>>
                                <?php echo esc_html($teacher->display_name); ?>
                            </option>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Rating Filter -->
                <div>
                    <label for="min_rating" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Minimum Rating', 'mcqhome'); ?>
                    </label>
                    <select id="min_rating" 
                            name="min_rating" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value=""><?php _e('Any Rating', 'mcqhome'); ?></option>
                        <option value="4" <?php selected(get_query_var('min_rating'), '4'); ?>><?php _e('4+ Stars', 'mcqhome'); ?></option>
                        <option value="3" <?php selected(get_query_var('min_rating'), '3'); ?>><?php _e('3+ Stars', 'mcqhome'); ?></option>
                        <option value="2" <?php selected(get_query_var('min_rating'), '2'); ?>><?php _e('2+ Stars', 'mcqhome'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 transition-colors">
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
        $institution = get_query_var('institution');
        $teacher = get_query_var('teacher');
        $price = get_query_var('price');
        $min_rating = get_query_var('min_rating');
        $sort = get_query_var('sort', 'date');
        
        $args = [
            'post_type' => $content_type ?: ['mcq', 'mcq_set'],
            'posts_per_page' => 12,
            'paged' => $paged,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'mcq_topic',
                    'field' => 'term_id',
                    'terms' => $term->term_id
                ]
            ]
        ];
        
        // Add search
        if ($search) {
            $args['s'] = $search;
        }
        
        // Add author filter
        if ($teacher) {
            $args['author'] = $teacher;
        }
        
        // Add additional taxonomy filters
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
        
        // Add meta queries
        $meta_query = [];
        if ($price) {
            $meta_query[] = [
                'key' => '_pricing_type',
                'value' => $price,
                'compare' => '='
            ];
        }
        
        if ($institution) {
            $meta_query[] = [
                'key' => '_institution_id',
                'value' => $institution,
                'compare' => '='
            ];
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        // Add sorting
        switch ($sort) {
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'popular':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_view_count';
                $args['order'] = 'DESC';
                break;
            case 'rating':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_average_rating';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }
        
        $topic_query = new WP_Query($args);
        
        if ($topic_query->have_posts()) :
        ?>
            <!-- Results Header -->
            <div class="results-header flex justify-between items-center mb-6">
                <div class="results-count">
                    <span class="text-gray-600">
                        <?php printf(__('Showing %d of %d results for "%s"', 'mcqhome'), 
                            min($topic_query->post_count, $topic_query->found_posts), 
                            $topic_query->found_posts,
                            $term->name); ?>
                    </span>
                </div>
                
                <div class="view-toggle">
                    <button class="view-grid-btn bg-purple-600 text-white px-3 py-2 rounded-l-md">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-list-btn bg-gray-300 text-gray-700 px-3 py-2 rounded-r-md">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Results Grid -->
            <div class="results-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php while ($topic_query->have_posts()) : $topic_query->the_post(); ?>
                    <div class="content-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Card Header -->
                        <div class="card-header bg-gradient-to-r from-purple-500 to-pink-600 text-white p-4">
                            <h3 class="text-lg font-semibold mb-2">
                                <a href="<?php the_permalink(); ?>" class="hover:text-purple-200">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="card-meta text-purple-100 text-sm">
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
                                $subjects = get_the_terms(get_the_ID(), 'mcq_subject');
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
                                   class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm hover:bg-purple-700 transition-colors">
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
                    'total' => $topic_query->max_num_pages,
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
                <p class="text-gray-600 mb-4"><?php _e('Try adjusting your filters or browse other topics.', 'mcqhome'); ?></p>
                <a href="<?php echo home_url('/browse/'); ?>" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 transition-colors">
                    <?php _e('Browse All Content', 'mcqhome'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>