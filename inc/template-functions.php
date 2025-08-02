<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package MCQHome
 * @since 1.0.0
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function mcqhome_body_classes($classes) {
    // Adds a class of hfeed to non-singular pages.
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }

    // Adds a class of no-sidebar when there is no sidebar present.
    if (!is_active_sidebar('sidebar-1')) {
        $classes[] = 'no-sidebar';
    }

    // Add user role classes for logged-in users
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (!empty($user->roles)) {
            foreach ($user->roles as $role) {
                $classes[] = 'user-role-' . sanitize_html_class($role);
            }
        }
    }

    return $classes;
}
add_filter('body_class', 'mcqhome_body_classes');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function mcqhome_pingback_header() {
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
    }
}
add_action('wp_head', 'mcqhome_pingback_header');

/**
 * Default menu fallback
 */
function mcqhome_default_menu() {
    echo '<ul class="flex space-x-6">';
    echo '<li><a href="' . esc_url(home_url('/')) . '" class="text-gray-700 hover:text-blue-600">' . esc_html__('Home', 'mcqhome') . '</a></li>';
    
    $dashboard_page = get_page_by_path('dashboard');
    if ($dashboard_page) {
        echo '<li><a href="' . esc_url(get_permalink($dashboard_page)) . '" class="text-gray-700 hover:text-blue-600">' . esc_html__('Dashboard', 'mcqhome') . '</a></li>';
    }
    
    $browse_page = get_page_by_path('browse');
    if ($browse_page) {
        echo '<li><a href="' . esc_url(get_permalink($browse_page)) . '" class="text-gray-700 hover:text-blue-600">' . esc_html__('Browse MCQs', 'mcqhome') . '</a></li>';
    }
    
    $institutions_page = get_page_by_path('institutions');
    if ($institutions_page) {
        echo '<li><a href="' . esc_url(get_permalink($institutions_page)) . '" class="text-gray-700 hover:text-blue-600">' . esc_html__('Institutions', 'mcqhome') . '</a></li>';
    }
    
    echo '</ul>';
}

/**
 * Default footer menu fallback
 */
function mcqhome_default_footer_menu() {
    echo '<ul class="space-y-2">';
    echo '<li><a href="' . esc_url(home_url('/')) . '" class="text-gray-300 hover:text-white">' . esc_html__('Home', 'mcqhome') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/about')) . '" class="text-gray-300 hover:text-white">' . esc_html__('About', 'mcqhome') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/contact')) . '" class="text-gray-300 hover:text-white">' . esc_html__('Contact', 'mcqhome') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/privacy-policy')) . '" class="text-gray-300 hover:text-white">' . esc_html__('Privacy Policy', 'mcqhome') . '</a></li>';
    echo '</ul>';
}

/**
 * Custom excerpt length
 */
function mcqhome_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'mcqhome_excerpt_length', 999);

/**
 * Custom excerpt more string
 */
function mcqhome_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'mcqhome_excerpt_more');

/**
 * Add mobile menu toggle functionality
 */
function mcqhome_mobile_menu_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        if (mobileMenuToggle && mobileMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'mcqhome_mobile_menu_script');

/**
 * Get user primary role
 */
function mcqhome_get_user_primary_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $user = get_userdata($user_id);
    if (!$user || empty($user->roles)) {
        return false;
    }
    
    // Return the first role (primary role)
    return $user->roles[0];
}

/**
 * Get user role display name
 */
function mcqhome_get_user_role_display_name($role) {
    $role_names = [
        'administrator' => __('Administrator', 'mcqhome'),
        'institution' => __('Institution', 'mcqhome'),
        'teacher' => __('Teacher', 'mcqhome'),
        'student' => __('Student', 'mcqhome'),
    ];
    
    return isset($role_names[$role]) ? $role_names[$role] : ucfirst($role);
}

/**
 * Browse and Discovery System Functions
 */

/**
 * Get institution statistics
 */
function mcqhome_get_institution_stats($institution_id) {
    global $wpdb;
    
    // Check if custom tables exist to prevent fatal errors
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mcq_user_follows'");
    
    // Get teachers count
    $teachers_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(DISTINCT u.ID) 
        FROM {$wpdb->users} u 
        INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id 
        WHERE um.meta_key = 'institution_id' 
        AND um.meta_value = %d
    ", $institution_id));
    
    // Get MCQ sets count
    $mcq_sets_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(p.ID) 
        FROM {$wpdb->posts} p 
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'mcq_set' 
        AND p.post_status = 'publish' 
        AND pm.meta_key = '_institution_id' 
        AND pm.meta_value = %d
    ", $institution_id));
    
    // Get total MCQs count
    $total_mcqs_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(p.ID) 
        FROM {$wpdb->posts} p 
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'mcq' 
        AND p.post_status = 'publish' 
        AND pm.meta_key = '_institution_id' 
        AND pm.meta_value = %d
    ", $institution_id));
    
    // Get followers count (only if table exists)
    $followers_count = 0;
    if ($table_exists) {
        $followers_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}mcq_user_follows 
            WHERE followed_type = 'institution' 
            AND followed_id = %d
        ", $institution_id));
    }
    
    return [
        'teachers' => (int) ($teachers_count ?: 0),
        'mcq_sets' => (int) ($mcq_sets_count ?: 0),
        'total_mcqs' => (int) ($total_mcqs_count ?: 0),
        'followers' => (int) ($followers_count ?: 0)
    ];
}

/**
 * Get teacher statistics
 */
function mcqhome_get_teacher_stats($teacher_id) {
    global $wpdb;
    
    // Check if custom tables exist to prevent fatal errors
    $attempts_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mcq_attempts'");
    $follows_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mcq_user_follows'");
    
    // Get MCQ sets count
    $mcq_sets_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(ID) 
        FROM {$wpdb->posts} 
        WHERE post_type = 'mcq_set' 
        AND post_status = 'publish' 
        AND post_author = %d
    ", $teacher_id));
    
    // Get total MCQs count
    $total_mcqs_count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(ID) 
        FROM {$wpdb->posts} 
        WHERE post_type = 'mcq' 
        AND post_status = 'publish' 
        AND post_author = %d
    ", $teacher_id));
    
    // Get students count (enrolled in teacher's sets) - only if table exists
    $students_count = 0;
    if ($attempts_table_exists) {
        $students_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->prefix}mcq_attempts ma
            INNER JOIN {$wpdb->posts} p ON ma.mcq_set_id = p.ID
            WHERE p.post_author = %d
        ", $teacher_id));
    }
    
    // Get followers count - only if table exists
    $followers_count = 0;
    if ($follows_table_exists) {
        $followers_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}mcq_user_follows 
            WHERE followed_type = 'teacher' 
            AND followed_id = %d
        ", $teacher_id));
    }
    
    return [
        'mcq_sets' => (int) ($mcq_sets_count ?: 0),
        'total_mcqs' => (int) ($total_mcqs_count ?: 0),
        'students' => (int) ($students_count ?: 0),
        'followers' => (int) ($followers_count ?: 0)
    ];
}

/**
 * Get teachers associated with an institution
 */
function mcqhome_get_institution_teachers($institution_id) {
    global $wpdb;
    
    $teacher_ids = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT u.ID 
        FROM {$wpdb->users} u 
        INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id 
        WHERE um.meta_key = 'institution_id' 
        AND um.meta_value = %d
        ORDER BY u.display_name ASC
    ", $institution_id));
    
    if (empty($teacher_ids)) {
        return [];
    }
    
    return get_users([
        'include' => $teacher_ids,
        'orderby' => 'display_name',
        'order' => 'ASC'
    ]);
}

/**
 * Get institutions associated with a teacher
 */
function mcqhome_get_teacher_institutions($teacher_id) {
    $institution_id = get_user_meta($teacher_id, 'institution_id', true);
    
    if (!$institution_id) {
        return [];
    }
    
    $institutions = get_posts([
        'post_type' => 'institution',
        'include' => [$institution_id],
        'post_status' => 'publish'
    ]);
    
    return $institutions;
}

/**
 * Get MCQ sets for an institution
 */
function mcqhome_get_institution_mcq_sets($institution_id, $limit = -1) {
    return new WP_Query([
        'post_type' => 'mcq_set',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => '_institution_id',
                'value' => $institution_id,
                'compare' => '='
            ]
        ],
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
}

/**
 * Get subjects for an institution
 */
function mcqhome_get_institution_subjects($institution_id) {
    global $wpdb;
    
    $subject_ids = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT tt.term_id
        FROM {$wpdb->term_taxonomy} tt
        INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE tt.taxonomy = 'mcq_subject'
        AND p.post_type IN ('mcq', 'mcq_set')
        AND p.post_status = 'publish'
        AND pm.meta_key = '_institution_id'
        AND pm.meta_value = %d
    ", $institution_id));
    
    if (empty($subject_ids)) {
        return [];
    }
    
    return get_terms([
        'taxonomy' => 'mcq_subject',
        'include' => $subject_ids,
        'hide_empty' => false
    ]);
}

/**
 * Get subjects for a teacher
 */
function mcqhome_get_teacher_subjects($teacher_id) {
    global $wpdb;
    
    $subject_ids = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT tt.term_id
        FROM {$wpdb->term_taxonomy} tt
        INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        WHERE tt.taxonomy = 'mcq_subject'
        AND p.post_type IN ('mcq', 'mcq_set')
        AND p.post_status = 'publish'
        AND p.post_author = %d
    ", $teacher_id));
    
    if (empty($subject_ids)) {
        return [];
    }
    
    return get_terms([
        'taxonomy' => 'mcq_subject',
        'include' => $subject_ids,
        'hide_empty' => false
    ]);
}

/**
 * Get MCQ set question count
 */
function mcqhome_get_mcq_set_question_count($mcq_set_id) {
    $mcq_ids = get_post_meta($mcq_set_id, '_mcq_ids', true);
    return is_array($mcq_ids) ? count($mcq_ids) : 0;
}

/**
 * Get MCQ set rating
 */
function mcqhome_get_mcq_set_rating($mcq_set_id) {
    global $wpdb;
    
    // Check if ratings table exists to prevent fatal errors
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mcq_ratings'");
    
    if (!$table_exists) {
        return 0;
    }
    
    $rating = $wpdb->get_var($wpdb->prepare("
        SELECT AVG(rating) 
        FROM {$wpdb->prefix}mcq_ratings 
        WHERE mcq_set_id = %d
    ", $mcq_set_id));
    
    return $rating ? round($rating, 1) : 0;
}

/**
 * Get user role helper function
 */
function mcqhome_get_user_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $user = get_userdata($user_id);
    if (!$user || empty($user->roles)) {
        return false;
    }
    
    // Return the first role (primary role)
    return $user->roles[0];
}

/**
 * Check if user is following an institution or teacher
 */
function mcqhome_is_following($follower_id, $followed_id, $followed_type) {
    global $wpdb;
    
    // Check if table exists to prevent fatal errors
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mcq_user_follows'");
    
    if (!$table_exists) {
        return false;
    }
    
    $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) 
        FROM {$wpdb->prefix}mcq_user_follows 
        WHERE follower_id = %d 
        AND followed_id = %d 
        AND followed_type = %s
    ", $follower_id, $followed_id, $followed_type));
    
    return $count > 0;
}

/**
 * Add follow/unfollow functionality
 */
function mcqhome_toggle_follow() {
    if (!is_user_logged_in()) {
        wp_die(__('You must be logged in to follow.', 'mcqhome'));
    }
    
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_nonce')) {
        wp_die(__('Security check failed.', 'mcqhome'));
    }
    
    $follower_id = get_current_user_id();
    $followed_id = intval($_POST['followed_id']);
    $followed_type = sanitize_text_field($_POST['followed_type']);
    
    if (!in_array($followed_type, ['institution', 'teacher'])) {
        wp_die(__('Invalid follow type.', 'mcqhome'));
    }
    
    global $wpdb;
    
    // Check if already following
    $is_following = mcqhome_is_following($follower_id, $followed_id, $followed_type);
    
    if ($is_following) {
        // Unfollow
        $wpdb->delete(
            $wpdb->prefix . 'mcq_user_follows',
            [
                'follower_id' => $follower_id,
                'followed_id' => $followed_id,
                'followed_type' => $followed_type
            ],
            ['%d', '%d', '%s']
        );
        
        wp_send_json_success([
            'action' => 'unfollowed',
            'message' => __('Unfollowed successfully.', 'mcqhome')
        ]);
    } else {
        // Follow
        $wpdb->insert(
            $wpdb->prefix . 'mcq_user_follows',
            [
                'follower_id' => $follower_id,
                'followed_id' => $followed_id,
                'followed_type' => $followed_type,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s']
        );
        
        wp_send_json_success([
            'action' => 'followed',
            'message' => __('Following successfully.', 'mcqhome')
        ]);
    }
}
add_action('wp_ajax_mcqhome_toggle_follow', 'mcqhome_toggle_follow');
/**

 * Enhanced Browse System Functions
 */

/**
 * Get hierarchical subject structure with topics
 */
function mcqhome_get_subject_hierarchy() {
    $subjects = get_terms([
        'taxonomy' => 'mcq_subject',
        'hide_empty' => true,
        'orderby' => 'count',
        'order' => 'DESC'
    ]);
    
    if (is_wp_error($subjects) || empty($subjects)) {
        return [];
    }
    
    $hierarchy = [];
    foreach ($subjects as $subject) {
        $topics = get_terms([
            'taxonomy' => 'mcq_topic',
            'hide_empty' => true,
            'meta_query' => [
                [
                    'key' => 'parent_subject',
                    'value' => $subject->term_id,
                    'compare' => '='
                ]
            ],
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        $hierarchy[$subject->term_id] = [
            'subject' => $subject,
            'topics' => is_wp_error($topics) ? [] : $topics
        ];
    }
    
    return $hierarchy;
}

/**
 * Get content discovery statistics
 */
function mcqhome_get_discovery_stats() {
    global $wpdb;
    
    static $stats = null;
    
    if ($stats === null) {
        // Safe counting with fallbacks for when post types/taxonomies don't exist yet
        $mcq_set_count = post_type_exists('mcq_set') ? wp_count_posts('mcq_set') : null;
        $mcq_count = post_type_exists('mcq') ? wp_count_posts('mcq') : null;
        $institution_count = post_type_exists('institution') ? wp_count_posts('institution') : null;
        
        $stats = [
            'total_mcq_sets' => $mcq_set_count ? $mcq_set_count->publish : 0,
            'total_mcqs' => $mcq_count ? $mcq_count->publish : 0,
            'total_institutions' => $institution_count ? $institution_count->publish : 0,
            'total_subjects' => taxonomy_exists('mcq_subject') ? wp_count_terms(['taxonomy' => 'mcq_subject', 'hide_empty' => true]) : 0,
            'total_topics' => taxonomy_exists('mcq_topic') ? wp_count_terms(['taxonomy' => 'mcq_topic', 'hide_empty' => true]) : 0,
            'total_teachers' => get_role('teacher') ? count(get_users(['role' => 'teacher'])) : 0,
            'total_students' => get_role('student') ? count(get_users(['role' => 'student'])) : 0
        ];
        
        // Cache for 1 hour
        set_transient('mcqhome_discovery_stats', $stats, HOUR_IN_SECONDS);
    }
    
    return $stats;
}

/**
 * Get popular content for browse page
 */
function mcqhome_get_popular_content($content_type = 'mcq_set', $limit = 6) {
    $args = [
        'post_type' => $content_type,
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'meta_value_num',
        'meta_key' => '_view_count',
        'order' => 'DESC'
    ];
    
    return new WP_Query($args);
}

/**
 * Get recently added content
 */
function mcqhome_get_recent_content($content_type = 'mcq_set', $limit = 6) {
    $args = [
        'post_type' => $content_type,
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    return new WP_Query($args);
}

/**
 * Get highly rated content
 */
function mcqhome_get_highly_rated_content($content_type = 'mcq_set', $limit = 6) {
    $args = [
        'post_type' => $content_type,
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'meta_value_num',
        'meta_key' => '_average_rating',
        'order' => 'DESC',
        'meta_query' => [
            [
                'key' => '_average_rating',
                'value' => 3.5,
                'compare' => '>=',
                'type' => 'DECIMAL'
            ]
        ]
    ];
    
    return new WP_Query($args);
}

/**
 * Get content by difficulty level
 */
function mcqhome_get_content_by_difficulty($difficulty_slug, $content_type = 'mcq_set', $limit = 6) {
    $args = [
        'post_type' => $content_type,
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'tax_query' => [
            [
                'taxonomy' => 'mcq_difficulty',
                'field' => 'slug',
                'terms' => $difficulty_slug
            ]
        ],
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    return new WP_Query($args);
}

/**
 * Get breadcrumb navigation for taxonomy pages
 */
function mcqhome_get_taxonomy_breadcrumb($term, $taxonomy) {
    $breadcrumb = [];
    
    // Add home
    $breadcrumb[] = [
        'title' => __('Browse', 'mcqhome'),
        'url' => home_url('/browse/')
    ];
    
    // Add taxonomy archive
    $taxonomy_obj = get_taxonomy($taxonomy);
    if ($taxonomy_obj) {
        $breadcrumb[] = [
            'title' => $taxonomy_obj->labels->name,
            'url' => home_url('/browse/?view=' . $taxonomy)
        ];
    }
    
    // Add parent terms if hierarchical
    if ($taxonomy_obj && $taxonomy_obj->hierarchical && $term->parent) {
        $parent_terms = [];
        $parent_id = $term->parent;
        
        while ($parent_id) {
            $parent_term = get_term($parent_id, $taxonomy);
            if (!is_wp_error($parent_term)) {
                $parent_terms[] = $parent_term;
                $parent_id = $parent_term->parent;
            } else {
                break;
            }
        }
        
        // Reverse to show correct hierarchy
        $parent_terms = array_reverse($parent_terms);
        
        foreach ($parent_terms as $parent_term) {
            $breadcrumb[] = [
                'title' => $parent_term->name,
                'url' => get_term_link($parent_term)
            ];
        }
    }
    
    // Add current term
    $breadcrumb[] = [
        'title' => $term->name,
        'url' => get_term_link($term),
        'current' => true
    ];
    
    return $breadcrumb;
}

/**
 * Render breadcrumb navigation
 */
function mcqhome_render_breadcrumb($breadcrumb) {
    if (empty($breadcrumb)) {
        return;
    }
    
    echo '<nav class="breadcrumb mb-6">';
    echo '<ol class="flex items-center space-x-2 text-sm text-gray-600">';
    
    foreach ($breadcrumb as $index => $item) {
        if ($index > 0) {
            echo '<li><span class="mx-2">/</span></li>';
        }
        
        echo '<li>';
        if (isset($item['current']) && $item['current']) {
            echo '<span class="text-blue-600 font-medium">' . esc_html($item['title']) . '</span>';
        } else {
            echo '<a href="' . esc_url($item['url']) . '" class="hover:text-blue-600">' . esc_html($item['title']) . '</a>';
        }
        echo '</li>';
    }
    
    echo '</ol>';
    echo '</nav>';
}

/**
 * Get search suggestions based on query
 */
function mcqhome_get_search_suggestions($query, $limit = 5) {
    $suggestions = [];
    
    // Search in subjects
    $subjects = get_terms([
        'taxonomy' => 'mcq_subject',
        'name__like' => $query,
        'hide_empty' => true,
        'number' => $limit
    ]);
    
    if (!is_wp_error($subjects)) {
        foreach ($subjects as $subject) {
            $suggestions[] = [
                'type' => 'subject',
                'title' => $subject->name,
                'url' => get_term_link($subject),
                'count' => $subject->count
            ];
        }
    }
    
    // Search in topics
    $topics = get_terms([
        'taxonomy' => 'mcq_topic',
        'name__like' => $query,
        'hide_empty' => true,
        'number' => $limit
    ]);
    
    if (!is_wp_error($topics)) {
        foreach ($topics as $topic) {
            $suggestions[] = [
                'type' => 'topic',
                'title' => $topic->name,
                'url' => get_term_link($topic),
                'count' => $topic->count
            ];
        }
    }
    
    // Search in institutions
    $institutions = get_posts([
        'post_type' => 'institution',
        's' => $query,
        'posts_per_page' => $limit,
        'post_status' => 'publish'
    ]);
    
    foreach ($institutions as $institution) {
        $suggestions[] = [
            'type' => 'institution',
            'title' => $institution->post_title,
            'url' => get_permalink($institution),
            'count' => 0 // Could add content count
        ];
    }
    
    return array_slice($suggestions, 0, $limit);
}

/**
 * Check if current page is a browse-related page
 */
function mcqhome_is_browse_page() {
    global $wp_query;
    
    // Check if it's the main browse page
    if (is_page('browse')) {
        return true;
    }
    
    // Check if it's a taxonomy page for MCQ taxonomies
    if (is_tax(['mcq_subject', 'mcq_topic', 'mcq_difficulty'])) {
        return true;
    }
    
    // Check if it's an institution archive
    if (is_post_type_archive('institution')) {
        return true;
    }
    
    return false;
}