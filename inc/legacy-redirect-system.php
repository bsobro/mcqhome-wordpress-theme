<?php
/**
 * Legacy URL Redirect System for MCQHome Theme
 * 
 * This file implements the complete legacy URL redirect system to handle
 * redirects from old MCQ URLs to their parent MCQ sets with proper SEO
 * handling and sitemap exclusions.
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MCQHome_Legacy_Redirect_System
 * 
 * Handles all aspects of legacy MCQ URL redirects including:
 * - 301 redirects from MCQ URLs to MCQ sets
 * - MCQ-to-MCQ-set mapping system
 * - SEO-friendly redirect handling
 * - Sitemap exclusions
 */
class MCQHome_Legacy_Redirect_System {

    /**
     * Initialize the redirect system
     */
    public static function init() {
        // Handle template redirects with high priority
        add_action('template_redirect', [__CLASS__, 'handle_legacy_mcq_redirect'], 5);
        
        // Handle redirects for direct URL access
        add_action('wp', [__CLASS__, 'handle_direct_mcq_access'], 5);
        
        // Exclude MCQ posts from sitemaps
        add_filter('wp_sitemaps_post_types', [__CLASS__, 'exclude_mcq_from_sitemap'], 10, 2);
        
        // Handle XML sitemap exclusions
        add_filter('wp_sitemaps_posts_query_args', [__CLASS__, 'exclude_mcq_from_sitemap_query'], 10, 2);
        
        // Handle robots.txt modifications
        add_filter('robots_txt', [__CLASS__, 'modify_robots_txt'], 10, 2);
        
        // Handle canonical URLs for MCQ sets
        add_filter('get_canonical_url', [__CLASS__, 'handle_canonical_urls'], 10, 2);
        
        // Add redirect headers for better SEO
        add_action('wp_head', [__CLASS__, 'add_redirect_meta_tags']);
        
        // Handle RSS feed exclusions
        add_action('pre_get_posts', [__CLASS__, 'exclude_mcq_from_feeds'], 5);
        
        // Create redirect mapping table on activation
        register_activation_hook(__FILE__, [__CLASS__, 'create_redirect_mapping_table']);
        
        // Clean up orphaned redirects
        add_action('wp_trash_post', [__CLASS__, 'cleanup_redirect_mapping']);
        add_action('delete_post', [__CLASS__, 'cleanup_redirect_mapping']);
    }

    /**
     * Handle legacy MCQ URL redirects
     * 
     * This is the main redirect handler that processes all MCQ URL access
     * and redirects to appropriate MCQ sets with proper anchors.
     */
    public static function handle_legacy_mcq_redirect() {
        // Only handle single MCQ pages
        if (!is_singular('mcq')) {
            return;
        }
        
        $mcq_id = get_the_ID();
        if (!$mcq_id) {
            return;
        }
        
        // Find the MCQ set containing this question
        $mcq_set_id = self::find_mcq_set_for_question($mcq_id);
        
        if ($mcq_set_id) {
            // Create SEO-friendly redirect URL with anchor
            $redirect_url = self::build_redirect_url($mcq_set_id, $mcq_id);
            
            // Log the redirect for analytics
            self::log_redirect($mcq_id, $mcq_set_id, $redirect_url);
            
            // Perform 301 redirect
            wp_redirect($redirect_url, 301);
            exit;
        } else {
            // MCQ is orphaned - redirect to browse page with message
            $browse_url = home_url('/browse/');
            $browse_url = add_query_arg([
                'message' => 'mcq_not_found',
                'mcq_id' => $mcq_id
            ], $browse_url);
            
            // Log orphaned MCQ access
            self::log_orphaned_mcq_access($mcq_id);
            
            wp_redirect($browse_url, 301);
            exit;
        }
    }

    /**
     * Handle direct MCQ access via wp query
     */
    public static function handle_direct_mcq_access() {
        global $wp_query;
        
        // Check if this is a direct MCQ access
        if (is_singular('mcq') && !is_admin()) {
            $mcq_id = get_queried_object_id();
            if ($mcq_id) {
                self::handle_legacy_mcq_redirect();
            }
        }
    }

    /**
     * Find MCQ set that contains a specific MCQ
     * 
     * This function implements an efficient mapping system to find
     * which MCQ set contains a given MCQ question.
     * 
     * @param int $mcq_id The MCQ post ID
     * @return int|null The MCQ set ID or null if not found
     */
    public static function find_mcq_set_for_question($mcq_id) {
        global $wpdb;
        
        // First check the redirect mapping cache table
        $cached_mapping = self::get_cached_redirect_mapping($mcq_id);
        if ($cached_mapping) {
            // Verify the mapping is still valid
            if (get_post_status($cached_mapping) === 'publish') {
                return $cached_mapping;
            } else {
                // Clean up invalid mapping
                self::remove_redirect_mapping($mcq_id);
            }
        }
        
        // Search in new questions_order format (preferred)
        $mcq_set_id = $wpdb->get_var($wpdb->prepare("
            SELECT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_mcq_set_questions_order' 
            AND meta_value LIKE %s
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'mcq_set' 
                AND post_status = 'publish'
            )
        ", '%' . $wpdb->esc_like('"mcq_id":' . $mcq_id) . '%'));
        
        if ($mcq_set_id) {
            // Verify the MCQ is actually in the set
            $questions_order = json_decode(get_post_meta($mcq_set_id, '_mcq_set_questions_order', true), true);
            if (is_array($questions_order) && isset($questions_order['questions'])) {
                foreach ($questions_order['questions'] as $question_data) {
                    if (isset($question_data['mcq_id']) && $question_data['mcq_id'] == $mcq_id) {
                        // Cache this mapping for future use
                        self::cache_redirect_mapping($mcq_id, $mcq_set_id);
                        return $mcq_set_id;
                    }
                }
            }
        }
        
        // Fallback: Search in legacy questions format
        $mcq_set_id = $wpdb->get_var($wpdb->prepare("
            SELECT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_mcq_set_questions' 
            AND meta_value LIKE %s
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'mcq_set' 
                AND post_status = 'publish'
            )
        ", '%' . $wpdb->esc_like('"' . $mcq_id . '"') . '%'));
        
        if ($mcq_set_id) {
            // Verify the MCQ is actually in the set
            $questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
            if (is_array($questions) && in_array($mcq_id, $questions)) {
                // Cache this mapping for future use
                self::cache_redirect_mapping($mcq_id, $mcq_set_id);
                return $mcq_set_id;
            }
        }
        
        return null;
    }

    /**
     * Build SEO-friendly redirect URL with proper anchor
     * 
     * @param int $mcq_set_id The target MCQ set ID
     * @param int $mcq_id The original MCQ ID
     * @return string The complete redirect URL
     */
    public static function build_redirect_url($mcq_set_id, $mcq_id) {
        $base_url = get_permalink($mcq_set_id);
        
        // Add anchor to jump to specific question
        $anchor = '#question-' . $mcq_id;
        
        // Add query parameter for tracking (optional)
        $redirect_url = add_query_arg([
            'from_mcq' => $mcq_id,
            'redirect' => '1'
        ], $base_url);
        
        return $redirect_url . $anchor;
    }

    /**
     * Exclude MCQ posts from XML sitemaps
     * 
     * @param array $post_types Current post types in sitemap
     * @param string $post_type The post type being processed
     * @return array|false Modified post types or false to exclude
     */
    public static function exclude_mcq_from_sitemap($post_types, $post_type = null) {
        if ($post_type === 'mcq') {
            return false; // Exclude MCQ post type completely
        }
        
        // Remove MCQ from the list if it exists
        if (isset($post_types['mcq'])) {
            unset($post_types['mcq']);
        }
        
        return $post_types;
    }

    /**
     * Exclude MCQ posts from sitemap queries
     * 
     * @param array $args Query arguments
     * @param string $post_type Post type being queried
     * @return array Modified query arguments
     */
    public static function exclude_mcq_from_sitemap_query($args, $post_type) {
        if ($post_type === 'mcq') {
            // Return empty results for MCQ queries
            $args['post__in'] = [0];
        }
        
        return $args;
    }

    /**
     * Modify robots.txt to exclude MCQ URLs
     * 
     * @param string $output Current robots.txt content
     * @param bool $public Whether site is public
     * @return string Modified robots.txt content
     */
    public static function modify_robots_txt($output, $public) {
        if ($public) {
            $output .= "\n# MCQHome Theme - Exclude individual MCQ pages\n";
            $output .= "Disallow: /mcq/\n";
            $output .= "Disallow: */mcq/*\n";
            $output .= "\n# Allow MCQ Sets\n";
            $output .= "Allow: /mcq-set/\n";
            $output .= "Allow: */mcq-set/*\n";
        }
        
        return $output;
    }

    /**
     * Handle canonical URLs for redirected pages
     * 
     * @param string $canonical_url Current canonical URL
     * @param WP_Post $post Post object
     * @return string Modified canonical URL
     */
    public static function handle_canonical_urls($canonical_url, $post) {
        if ($post && $post->post_type === 'mcq') {
            $mcq_set_id = self::find_mcq_set_for_question($post->ID);
            if ($mcq_set_id) {
                return get_permalink($mcq_set_id);
            }
        }
        
        return $canonical_url;
    }

    /**
     * Add meta tags for redirected pages
     */
    public static function add_redirect_meta_tags() {
        if (is_singular('mcq')) {
            $mcq_id = get_the_ID();
            $mcq_set_id = self::find_mcq_set_for_question($mcq_id);
            
            if ($mcq_set_id) {
                $canonical_url = get_permalink($mcq_set_id);
                echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";
                echo '<meta name="robots" content="noindex, follow" />' . "\n";
            }
        }
    }

    /**
     * Exclude MCQ posts from RSS feeds
     * 
     * @param WP_Query $query The query object
     */
    public static function exclude_mcq_from_feeds($query) {
        if (!is_admin() && $query->is_main_query() && $query->is_feed()) {
            $post_types = $query->get('post_type');
            if (empty($post_types)) {
                $query->set('post_type', ['post', 'mcq_set', 'institution']);
            } elseif (is_array($post_types)) {
                $post_types = array_diff($post_types, ['mcq']);
                if (!empty($post_types)) {
                    $query->set('post_type', $post_types);
                }
            }
        }
    }

    /**
     * Create redirect mapping table for caching
     */
    public static function create_redirect_mapping_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_redirect_mappings';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            mcq_id bigint(20) unsigned NOT NULL,
            mcq_set_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY mcq_id (mcq_id),
            KEY mcq_set_id (mcq_set_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Cache redirect mapping for performance
     * 
     * @param int $mcq_id MCQ post ID
     * @param int $mcq_set_id MCQ set post ID
     */
    public static function cache_redirect_mapping($mcq_id, $mcq_set_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_redirect_mappings';
        
        $wpdb->replace(
            $table_name,
            [
                'mcq_id' => $mcq_id,
                'mcq_set_id' => $mcq_set_id
            ],
            ['%d', '%d']
        );
    }

    /**
     * Get cached redirect mapping
     * 
     * @param int $mcq_id MCQ post ID
     * @return int|null MCQ set ID or null if not found
     */
    public static function get_cached_redirect_mapping($mcq_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_redirect_mappings';
        
        $mcq_set_id = $wpdb->get_var($wpdb->prepare(
            "SELECT mcq_set_id FROM $table_name WHERE mcq_id = %d",
            $mcq_id
        ));
        
        return $mcq_set_id ? (int) $mcq_set_id : null;
    }

    /**
     * Remove redirect mapping
     * 
     * @param int $mcq_id MCQ post ID
     */
    public static function remove_redirect_mapping($mcq_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_redirect_mappings';
        
        $wpdb->delete(
            $table_name,
            ['mcq_id' => $mcq_id],
            ['%d']
        );
    }

    /**
     * Clean up redirect mappings when posts are deleted
     * 
     * @param int $post_id Post ID being deleted
     */
    public static function cleanup_redirect_mapping($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return;
        }
        
        if ($post->post_type === 'mcq') {
            // Remove MCQ mapping
            self::remove_redirect_mapping($post_id);
        } elseif ($post->post_type === 'mcq_set') {
            // Remove all mappings pointing to this MCQ set
            global $wpdb;
            $table_name = $wpdb->prefix . 'mcq_redirect_mappings';
            
            $wpdb->delete(
                $table_name,
                ['mcq_set_id' => $post_id],
                ['%d']
            );
        }
    }

    /**
     * Log redirect for analytics and debugging
     * 
     * @param int $mcq_id Original MCQ ID
     * @param int $mcq_set_id Target MCQ set ID
     * @param string $redirect_url Final redirect URL
     */
    public static function log_redirect($mcq_id, $mcq_set_id, $redirect_url) {
        // Only log in debug mode to avoid performance issues
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'MCQHome Redirect: MCQ %d -> MCQ Set %d (%s)',
                $mcq_id,
                $mcq_set_id,
                $redirect_url
            ));
        }
        
        // Update redirect statistics (optional)
        $redirect_count = get_post_meta($mcq_set_id, '_mcq_redirect_count', true);
        $redirect_count = $redirect_count ? (int) $redirect_count + 1 : 1;
        update_post_meta($mcq_set_id, '_mcq_redirect_count', $redirect_count);
    }

    /**
     * Log orphaned MCQ access for cleanup
     * 
     * @param int $mcq_id Orphaned MCQ ID
     */
    public static function log_orphaned_mcq_access($mcq_id) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'MCQHome: Orphaned MCQ access detected - MCQ %d has no parent MCQ set',
                $mcq_id
            ));
        }
        
        // Track orphaned MCQs for admin attention
        $orphaned_mcqs = get_option('mcqhome_orphaned_mcqs', []);
        if (!in_array($mcq_id, $orphaned_mcqs)) {
            $orphaned_mcqs[] = $mcq_id;
            update_option('mcqhome_orphaned_mcqs', $orphaned_mcqs);
        }
    }

    /**
     * Get redirect statistics for admin dashboard
     * 
     * @return array Redirect statistics
     */
    public static function get_redirect_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mcq_redirect_mappings';
        
        $stats = [
            'total_mappings' => 0,
            'orphaned_mcqs' => [],
            'top_redirected_sets' => []
        ];
        
        // Get total mappings
        $stats['total_mappings'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Get orphaned MCQs
        $stats['orphaned_mcqs'] = get_option('mcqhome_orphaned_mcqs', []);
        
        // Get top redirected MCQ sets
        $top_sets = $wpdb->get_results("
            SELECT mcq_set_id, COUNT(*) as redirect_count 
            FROM $table_name 
            GROUP BY mcq_set_id 
            ORDER BY redirect_count DESC 
            LIMIT 10
        ");
        
        foreach ($top_sets as $set) {
            $post = get_post($set->mcq_set_id);
            if ($post) {
                $stats['top_redirected_sets'][] = [
                    'id' => $set->mcq_set_id,
                    'title' => $post->post_title,
                    'redirect_count' => $set->redirect_count
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Rebuild redirect mappings (maintenance function)
     */
    public static function rebuild_redirect_mappings() {
        global $wpdb;
        
        // Clear existing mappings
        $table_name = $wpdb->prefix . 'mcq_redirect_mappings';
        $wpdb->query("TRUNCATE TABLE $table_name");
        
        // Get all MCQ sets
        $mcq_sets = get_posts([
            'post_type' => 'mcq_set',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        $mapped_count = 0;
        
        foreach ($mcq_sets as $mcq_set_id) {
            // Check new format first
            $questions_order = json_decode(get_post_meta($mcq_set_id, '_mcq_set_questions_order', true), true);
            if (is_array($questions_order) && isset($questions_order['questions'])) {
                foreach ($questions_order['questions'] as $question_data) {
                    if (isset($question_data['mcq_id'])) {
                        self::cache_redirect_mapping($question_data['mcq_id'], $mcq_set_id);
                        $mapped_count++;
                    }
                }
            } else {
                // Fallback to legacy format
                $questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
                if (is_array($questions)) {
                    foreach ($questions as $mcq_id) {
                        self::cache_redirect_mapping($mcq_id, $mcq_set_id);
                        $mapped_count++;
                    }
                }
            }
        }
        
        return $mapped_count;
    }
}

// Initialize the redirect system
MCQHome_Legacy_Redirect_System::init();

/**
 * Helper functions for backward compatibility
 */

/**
 * Find MCQ set for question (backward compatibility)
 * 
 * @param int $mcq_id MCQ post ID
 * @return int|null MCQ set ID or null if not found
 */
function mcqhome_find_mcq_set_for_question($mcq_id) {
    return MCQHome_Legacy_Redirect_System::find_mcq_set_for_question($mcq_id);
}

/**
 * Handle legacy MCQ redirect (backward compatibility)
 */
function mcqhome_handle_legacy_mcq_redirect() {
    MCQHome_Legacy_Redirect_System::handle_legacy_mcq_redirect();
}

/**
 * Admin function to rebuild redirect mappings
 */
function mcqhome_rebuild_redirect_mappings() {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    return MCQHome_Legacy_Redirect_System::rebuild_redirect_mappings();
}

/**
 * Get redirect statistics for admin
 */
function mcqhome_get_redirect_statistics() {
    if (!current_user_can('manage_options')) {
        return [];
    }
    
    return MCQHome_Legacy_Redirect_System::get_redirect_statistics();
}