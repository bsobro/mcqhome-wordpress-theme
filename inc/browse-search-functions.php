<?php
/**
 * Browse and Search System Functions
 * 
 * Functions to handle browse page and search functionality updates
 * to show only MCQ sets instead of individual MCQs
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exclude MCQ posts from public queries (home, search, archives)
 * Only show MCQ sets in public areas
 */
function mcqhome_exclude_mcq_from_public($query) {
    // Only modify main queries on frontend
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Get current post types
    $post_types = $query->get('post_type');
    
    // Handle different query types
    if (is_home() || is_search() || is_archive()) {
        // If no post type is set, WordPress defaults to 'post'
        if (empty($post_types)) {
            // Set default post types excluding MCQ
            $post_types = ['post', 'mcq_set', 'institution'];
        } else {
            // If post types are set, ensure MCQ is not included
            if (is_array($post_types)) {
                $post_types = array_diff($post_types, ['mcq']);
                // Ensure mcq_set is included if not already
                if (!in_array('mcq_set', $post_types)) {
                    $post_types[] = 'mcq_set';
                }
            } elseif ($post_types === 'mcq') {
                // If only MCQ was requested, change to MCQ sets
                $post_types = 'mcq_set';
            }
        }
        
        $query->set('post_type', $post_types);
    }
    
    // Handle browse page specifically
    if (is_page('browse')) {
        $content_type = get_query_var('content_type');
        
        // If content_type is not specified or is 'mcq', default to 'mcq_set'
        if (empty($content_type) || $content_type === 'mcq') {
            $query->set('post_type', 'mcq_set');
        } elseif ($content_type === 'all') {
            // If 'all' is requested, show mcq_set and institution but not mcq
            $query->set('post_type', ['mcq_set', 'institution']);
        }
    }
}
add_action('pre_get_posts', 'mcqhome_exclude_mcq_from_public');

/**
 * Exclude MCQ posts from search results
 * Ensure search only returns MCQ sets
 */
function mcqhome_exclude_mcq_from_search($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        $post_types = $query->get('post_type');
        
        if (empty($post_types)) {
            // Default search should include mcq_set but not mcq
            $query->set('post_type', ['post', 'mcq_set', 'institution']);
        } else {
            // Remove mcq from any search
            if (is_array($post_types)) {
                $post_types = array_diff($post_types, ['mcq']);
                if (!in_array('mcq_set', $post_types)) {
                    $post_types[] = 'mcq_set';
                }
            } elseif ($post_types === 'mcq') {
                $post_types = 'mcq_set';
            }
            $query->set('post_type', $post_types);
        }
    }
}
add_action('pre_get_posts', 'mcqhome_exclude_mcq_from_search', 20);

/**
 * Modify taxonomy queries to show only MCQ sets
 */
function mcqhome_modify_taxonomy_queries($query) {
    if (!is_admin() && $query->is_main_query() && (is_tax('mcq_subject') || is_tax('mcq_topic') || is_tax('mcq_difficulty'))) {
        $content_type = get_query_var('content_type');
        
        // Default to mcq_set if no content type specified or if mcq is specified
        if (empty($content_type) || $content_type === 'mcq') {
            $query->set('post_type', 'mcq_set');
        } elseif ($content_type === 'all') {
            // Show both mcq_set and institution but not individual mcqs
            $query->set('post_type', ['mcq_set', 'institution']);
        }
    }
}
add_action('pre_get_posts', 'mcqhome_modify_taxonomy_queries', 15);

/**
 * Filter get_posts() calls to exclude MCQ posts from public areas
 */
function mcqhome_filter_get_posts($posts, $parsed_args) {
    // Only filter on frontend
    if (is_admin()) {
        return $posts;
    }
    
    // Check if this is a public query that should exclude MCQs
    $post_type = isset($parsed_args['post_type']) ? $parsed_args['post_type'] : 'post';
    
    if (empty($post_type) || $post_type === 'any' || (is_array($post_type) && in_array('mcq', $post_type))) {
        // Filter out MCQ posts from results
        $filtered_posts = array_filter($posts, function($post) {
            return $post->post_type !== 'mcq';
        });
        return array_values($filtered_posts);
    }
    
    return $posts;
}
add_filter('get_posts', 'mcqhome_filter_get_posts', 10, 2);

/**
 * Exclude MCQ posts from REST API queries
 */
function mcqhome_exclude_mcq_from_rest($args, $request) {
    // Only modify public REST requests
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        $post_types = isset($args['post_type']) ? $args['post_type'] : ['post'];
        
        if (is_array($post_types)) {
            $args['post_type'] = array_diff($post_types, ['mcq']);
            if (!in_array('mcq_set', $args['post_type'])) {
                $args['post_type'][] = 'mcq_set';
            }
        } elseif ($post_types === 'mcq') {
            $args['post_type'] = 'mcq_set';
        }
    }
    
    return $args;
}
add_filter('rest_post_query', 'mcqhome_exclude_mcq_from_rest', 10, 2);

/**
 * Exclude MCQ posts from sitemaps
 */
function mcqhome_exclude_mcq_from_sitemap($post_types) {
    unset($post_types['mcq']);
    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'mcqhome_exclude_mcq_from_sitemap');

/**
 * Exclude MCQ posts from feeds
 */
function mcqhome_exclude_mcq_from_feeds($query) {
    if (!is_admin() && $query->is_feed() && $query->is_main_query()) {
        $post_types = $query->get('post_type');
        
        if (empty($post_types)) {
            $query->set('post_type', ['post', 'mcq_set', 'institution']);
        } else {
            if (is_array($post_types)) {
                $post_types = array_diff($post_types, ['mcq']);
            } elseif ($post_types === 'mcq') {
                $post_types = 'mcq_set';
            }
            $query->set('post_type', $post_types);
        }
    }
}
add_action('pre_get_posts', 'mcqhome_exclude_mcq_from_feeds', 25);

/**
 * Exclude MCQ posts from search widget
 */
function mcqhome_exclude_mcq_from_search_widget($query) {
    if (!is_admin() && $query->is_search()) {
        $post_types = $query->get('post_type');
        
        if (empty($post_types)) {
            $query->set('post_type', ['post', 'mcq_set', 'institution']);
        } else {
            if (is_array($post_types)) {
                $post_types = array_diff($post_types, ['mcq']);
                if (!in_array('mcq_set', $post_types)) {
                    $post_types[] = 'mcq_set';
                }
            } elseif ($post_types === 'mcq') {
                $post_types = 'mcq_set';
            }
            $query->set('post_type', $post_types);
        }
    }
}
add_action('pre_get_posts', 'mcqhome_exclude_mcq_from_search_widget', 30);

/**
 * Update browse page filters to work with MCQ set properties
 */
function mcqhome_modify_browse_query_for_mcq_sets($query_args) {
    // Ensure we're only querying MCQ sets on browse page
    if (is_page('browse')) {
        $content_type = get_query_var('content_type');
        
        if (empty($content_type) || $content_type === 'mcq') {
            $query_args['post_type'] = 'mcq_set';
        }
        
        // Handle filters that need to work with MCQ set properties
        $subject = get_query_var('subject');
        $topic = get_query_var('topic');
        $difficulty = get_query_var('difficulty');
        $min_questions = get_query_var('min_questions');
        $price = get_query_var('price');
        $institution = get_query_var('institution');
        $teacher = get_query_var('teacher');
        $min_rating = get_query_var('min_rating');
        
        // Build tax_query for taxonomies
        $tax_query = [];
        if ($subject) {
            $tax_query[] = [
                'taxonomy' => 'mcq_subject',
                'field' => 'slug',
                'terms' => $subject
            ];
        }
        
        if ($topic) {
            $tax_query[] = [
                'taxonomy' => 'mcq_topic',
                'field' => 'slug',
                'terms' => $topic
            ];
        }
        
        if ($difficulty) {
            $tax_query[] = [
                'taxonomy' => 'mcq_difficulty',
                'field' => 'slug',
                'terms' => $difficulty
            ];
        }
        
        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $query_args['tax_query'] = $tax_query;
        }
        
        // Build meta_query for MCQ set properties
        $meta_query = [];
        
        if ($min_questions) {
            $meta_query[] = [
                'key' => '_mcq_set_question_count',
                'value' => intval($min_questions),
                'compare' => '>='
            ];
        }
        
        if ($price) {
            $meta_query[] = [
                'key' => '_mcq_set_pricing_type',
                'value' => $price,
                'compare' => '='
            ];
        }
        
        if ($institution) {
            $meta_query[] = [
                'key' => '_mcq_set_institution_id',
                'value' => intval($institution),
                'compare' => '='
            ];
        }
        
        if ($min_rating) {
            $meta_query[] = [
                'key' => '_mcq_set_average_rating',
                'value' => floatval($min_rating),
                'compare' => '>='
            ];
        }
        
        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
            $query_args['meta_query'] = $meta_query;
        }
        
        // Handle author filter
        if ($teacher) {
            $query_args['author'] = intval($teacher);
        }
    }
    
    return $query_args;
}

/**
 * Get MCQ set question count for display
 */
function mcqhome_get_mcq_set_question_count($mcq_set_id) {
    $questions_order = get_post_meta($mcq_set_id, '_mcq_set_questions_order', true);
    if ($questions_order) {
        $questions_data = json_decode($questions_order, true);
        return isset($questions_data['questions']) ? count($questions_data['questions']) : 0;
    }
    return 0;
}

/**
 * Get MCQ set rating for display
 */
function mcqhome_get_mcq_set_rating($mcq_set_id) {
    $rating = get_post_meta($mcq_set_id, '_mcq_set_average_rating', true);
    return $rating ? floatval($rating) : 0;
}

/**
 * Update search functionality to work with MCQ set content
 */
function mcqhome_enhance_search_for_mcq_sets($search, $wp_query) {
    global $wpdb;
    
    if (!is_admin() && $wp_query->is_search() && $wp_query->is_main_query()) {
        $search_term = $wp_query->get('s');
        
        if (!empty($search_term)) {
            // Enhance search to include MCQ set meta fields and contained MCQ content
            $search_meta_keys = [
                '_mcq_set_description',
                '_mcq_set_sections',
                '_mcq_set_questions_order'
            ];
            
            $meta_search_parts = [];
            foreach ($search_meta_keys as $meta_key) {
                $meta_search_parts[] = "({$wpdb->postmeta}.meta_key = '{$meta_key}' AND {$wpdb->postmeta}.meta_value LIKE '%{$search_term}%')";
            }
            
            if (!empty($meta_search_parts)) {
                $meta_search = "OR EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} 
                    WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                    AND (" . implode(' OR ', $meta_search_parts) . ")
                )";
                
                // Add meta search to the existing search
                $search = str_replace(
                    "AND (({$wpdb->posts}.post_status = 'publish'))",
                    "{$meta_search} AND (({$wpdb->posts}.post_status = 'publish'))",
                    $search
                );
            }
        }
    }
    
    return $search;
}
add_filter('posts_search', 'mcqhome_enhance_search_for_mcq_sets', 10, 2);

/**
 * Create search template if it doesn't exist
 */
function mcqhome_create_search_template() {
    $search_template_path = get_template_directory() . '/search.php';
    
    if (!file_exists($search_template_path)) {
        $search_template_content = '<?php
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
            <?php printf(__("Search Results for: %s", "mcqhome"), "<span class=\"text-blue-600\">" . get_search_query() . "</span>"); ?>
        </h1>
        
        <?php if (have_posts()) : ?>
            <p class="text-gray-600">
                <?php printf(_n("Found %d result", "Found %d results", $wp_query->found_posts, "mcqhome"), $wp_query->found_posts); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Search Form -->
    <div class="search-form-container bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="<?php echo home_url(\'/\'); ?>" class="flex gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="s" 
                       value="<?php echo esc_attr(get_search_query()); ?>"
                       placeholder="<?php _e(\'Search MCQ sets, institutions...\', \'mcqhome\'); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                <?php _e(\'Search\', \'mcqhome\'); ?>
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
                            
                            <?php if (get_post_type() === \'mcq_set\') : ?>
                                <div class="content-stats flex justify-between items-center mb-4 text-sm">
                                    <span class="text-gray-600">
                                        <i class="fas fa-question-circle mr-1"></i>
                                        <?php echo mcqhome_get_mcq_set_question_count(get_the_ID()); ?> <?php _e(\'questions\', \'mcqhome\'); ?>
                                    </span>
                                    
                                    <?php 
                                    $rating = mcqhome_get_mcq_set_rating(get_the_ID());
                                    if ($rating > 0) :
                                    ?>
                                        <div class="rating flex items-center">
                                            <div class="stars flex mr-1">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <i class="fas fa-star text-xs <?php echo $i <= $rating ? \'text-yellow-400\' : \'text-gray-300\'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-gray-600"><?php echo number_format($rating, 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="card-actions">
                                <a href="<?php the_permalink(); ?>" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition-colors">
                                    <?php echo get_post_type() === \'mcq_set\' ? __\'Take Assessment\', \'mcqhome\') : __\'View\', \'mcqhome\'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination-wrapper">
                <?php
                echo paginate_links([
                    \'prev_text\' => \'<i class="fas fa-chevron-left"></i> \' . __(\'Previous\', \'mcqhome\'),
                    \'next_text\' => __(\'Next\', \'mcqhome\') . \' <i class="fas fa-chevron-right"></i>\',
                ]);
                ?>
            </div>
            
        <?php else : ?>
            <div class="no-results text-center py-12">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-search text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2"><?php _e(\'No results found\', \'mcqhome\'); ?></h3>
                <p class="text-gray-600 mb-4"><?php _e(\'Try different keywords or browse our content.\', \'mcqhome\'); ?></p>
                <a href="<?php echo home_url(\'/browse/\'); ?>" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <?php _e(\'Browse Content\', \'mcqhome\'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>';
        
        file_put_contents($search_template_path, $search_template_content);
    }
}

// Create search template on theme activation
add_action('after_setup_theme', 'mcqhome_create_search_template');