<?php
/**
 * SEO and Performance Functions
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom meta tags to head
 */
function mcqhome_add_meta_tags() {
    global $post;
    
    // Default meta description
    $description = get_bloginfo('description');
    $title = get_bloginfo('name');
    $url = home_url();
    $image = '';
    
    // Get theme customizer logo or default
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_data = wp_get_attachment_image_src($custom_logo_id, 'full');
        $image = $logo_data[0];
    }
    
    // Page-specific meta data
    if (is_singular()) {
        $title = get_the_title() . ' - ' . get_bloginfo('name');
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 30);
        $url = get_permalink();
        
        // Get featured image
        if (has_post_thumbnail()) {
            $image = get_the_post_thumbnail_url($post, 'large');
        }
        
        // MCQ Set specific meta
        if (get_post_type() === 'mcq_set') {
            $question_count = mcqhome_get_mcq_set_question_count($post->ID);
            $total_marks = get_post_meta($post->ID, '_mcq_set_total_marks', true);
            $difficulty = wp_get_post_terms($post->ID, 'mcq_difficulty');
            $subjects = wp_get_post_terms($post->ID, 'mcq_subject');
            
            $meta_parts = [];
            if ($question_count) {
                $meta_parts[] = $question_count . ' questions';
            }
            if ($total_marks) {
                $meta_parts[] = $total_marks . ' marks';
            }
            if (!empty($difficulty)) {
                $meta_parts[] = $difficulty[0]->name . ' difficulty';
            }
            if (!empty($subjects)) {
                $meta_parts[] = 'Subject: ' . $subjects[0]->name;
            }
            
            if (!empty($meta_parts)) {
                $description = implode(' • ', $meta_parts) . '. ' . $description;
            }
        }
        
        // Institution specific meta
        if (get_post_type() === 'institution') {
            $stats = mcqhome_get_institution_stats($post->ID);
            $meta_parts = [];
            
            if ($stats['teachers']) {
                $meta_parts[] = $stats['teachers'] . ' teachers';
            }
            if ($stats['mcq_sets']) {
                $meta_parts[] = $stats['mcq_sets'] . ' MCQ sets';
            }
            
            if (!empty($meta_parts)) {
                $description = implode(' • ', $meta_parts) . '. ' . $description;
            }
        }
    }
    
    // Category/taxonomy pages
    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        $title = $term->name . ' - ' . get_bloginfo('name');
        $description = $term->description ?: 'Browse ' . $term->name . ' content on ' . get_bloginfo('name');
        $url = get_term_link($term);
    }
    
    // Author pages
    if (is_author()) {
        $author = get_queried_object();
        $title = $author->display_name . ' - ' . get_bloginfo('name');
        $description = get_user_meta($author->ID, 'bio', true) ?: 'View content by ' . $author->display_name;
        $url = get_author_posts_url($author->ID);
        
        // Get author avatar
        $avatar_url = get_avatar_url($author->ID, ['size' => 300]);
        if ($avatar_url) {
            $image = $avatar_url;
        }
    }
    
    // Clean up description
    $description = wp_strip_all_tags($description);
    $description = wp_trim_words($description, 30);
    
    // Output meta tags
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    
    // Open Graph tags
    echo '<meta property="og:locale" content="' . get_locale() . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
    
    // Twitter Card tags
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }
    
    // Article specific tags
    if (is_singular() && in_array(get_post_type(), ['mcq', 'mcq_set', 'institution'])) {
        echo '<meta property="article:published_time" content="' . get_the_date('c') . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . get_the_modified_date('c') . '">' . "\n";
        
        $author = get_the_author_meta('display_name');
        if ($author) {
            echo '<meta property="article:author" content="' . esc_attr($author) . '">' . "\n";
        }
        
        // Add category tags
        $categories = get_the_terms($post->ID, 'mcq_subject');
        if ($categories && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                echo '<meta property="article:section" content="' . esc_attr($category->name) . '">' . "\n";
            }
        }
    }
}
add_action('wp_head', 'mcqhome_add_meta_tags', 1);

/**
 * Add structured data (JSON-LD)
 */
function mcqhome_add_structured_data() {
    global $post;
    
    $structured_data = [];
    
    // Website schema
    $structured_data[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => home_url(),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => home_url('/browse/?s={search_term_string}'),
            'query-input' => 'required name=search_term_string'
        ]
    ];
    
    // Organization schema
    $structured_data[] = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => home_url(),
        'description' => get_bloginfo('description'),
        'sameAs' => []
    ];
    
    // Page-specific structured data
    if (is_singular()) {
        // MCQ Set schema
        if (get_post_type() === 'mcq_set') {
            $question_count = mcqhome_get_mcq_set_question_count($post->ID);
            $total_marks = get_post_meta($post->ID, '_mcq_set_total_marks', true);
            $difficulty = wp_get_post_terms($post->ID, 'mcq_difficulty');
            $subjects = wp_get_post_terms($post->ID, 'mcq_subject');
            $author = get_the_author();
            
            $quiz_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Quiz',
                'name' => get_the_title(),
                'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 30),
                'url' => get_permalink(),
                'datePublished' => get_the_date('c'),
                'dateModified' => get_the_modified_date('c'),
                'author' => [
                    '@type' => 'Person',
                    'name' => $author
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => get_bloginfo('name'),
                    'url' => home_url()
                ]
            ];
            
            if ($question_count) {
                $quiz_schema['numberOfQuestions'] = $question_count;
            }
            
            if (!empty($subjects)) {
                $quiz_schema['about'] = [
                    '@type' => 'Thing',
                    'name' => $subjects[0]->name
                ];
            }
            
            if (!empty($difficulty)) {
                $quiz_schema['educationalLevel'] = $difficulty[0]->name;
            }
            
            if (has_post_thumbnail()) {
                $quiz_schema['image'] = get_the_post_thumbnail_url($post, 'large');
            }
            
            $structured_data[] = $quiz_schema;
        }
        
        // Institution schema
        if (get_post_type() === 'institution') {
            $stats = mcqhome_get_institution_stats($post->ID);
            
            $org_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'EducationalOrganization',
                'name' => get_the_title(),
                'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 30),
                'url' => get_permalink(),
                'foundingDate' => get_the_date('c')
            ];
            
            if (has_post_thumbnail()) {
                $org_schema['logo'] = get_the_post_thumbnail_url($post, 'medium');
                $org_schema['image'] = get_the_post_thumbnail_url($post, 'large');
            }
            
            // Add address if available
            $address = get_post_meta($post->ID, '_institution_address', true);
            if ($address) {
                $org_schema['address'] = [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $address
                ];
            }
            
            $structured_data[] = $org_schema;
        }
        
        // Regular article schema for other post types
        if (in_array(get_post_type(), ['post', 'page'])) {
            $article_schema = [
                '@context' => 'https://schema.org',
                '@type' => is_page() ? 'WebPage' : 'Article',
                'headline' => get_the_title(),
                'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 30),
                'url' => get_permalink(),
                'datePublished' => get_the_date('c'),
                'dateModified' => get_the_modified_date('c'),
                'author' => [
                    '@type' => 'Person',
                    'name' => get_the_author()
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => get_bloginfo('name'),
                    'url' => home_url()
                ]
            ];
            
            if (has_post_thumbnail()) {
                $article_schema['image'] = get_the_post_thumbnail_url($post, 'large');
            }
            
            $structured_data[] = $article_schema;
        }
    }
    
    // Author page schema
    if (is_author()) {
        $author = get_queried_object();
        $user_role = mcqhome_get_user_primary_role($author->ID);
        
        $person_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $author->display_name,
            'url' => get_author_posts_url($author->ID),
            'description' => get_user_meta($author->ID, 'bio', true) ?: ''
        ];
        
        if ($user_role === 'teacher') {
            $person_schema['@type'] = 'EducationalOrganization';
            $person_schema['jobTitle'] = 'Teacher';
        }
        
        $structured_data[] = $person_schema;
    }
    
    // Output structured data
    if (!empty($structured_data)) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo "\n" . '</script>' . "\n";
    }
}
add_action('wp_head', 'mcqhome_add_structured_data', 2);

/**
 * Optimize images with lazy loading
 */
function mcqhome_add_lazy_loading($attr, $attachment, $size) {
    // Skip lazy loading for above-the-fold images
    if (is_admin() || is_feed() || wp_is_mobile()) {
        return $attr;
    }
    
    // Add loading="lazy" attribute
    $attr['loading'] = 'lazy';
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'mcqhome_add_lazy_loading', 10, 3);

/**
 * Add preload hints for critical resources
 */
function mcqhome_add_preload_hints() {
    // Preload critical CSS
    if (file_exists(MCQHOME_THEME_DIR . '/assets/css/main.css')) {
        echo '<link rel="preload" href="' . MCQHOME_THEME_URL . '/assets/css/main.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        echo '<noscript><link rel="stylesheet" href="' . MCQHOME_THEME_URL . '/assets/css/main.css"></noscript>' . "\n";
    }
    
    // Preload fonts if using custom fonts
    $body_font = get_theme_mod('mcqhome_body_font', 'Inter');
    $heading_font = get_theme_mod('mcqhome_heading_font', 'Inter');
    
    if ($body_font !== 'system') {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }
    
    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
}
add_action('wp_head', 'mcqhome_add_preload_hints', 0);

/**
 * Optimize CSS delivery
 */
function mcqhome_optimize_css_delivery() {
    // Remove WordPress default CSS that we don't need
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-block-style');
    
    // Only load on frontend
    if (!is_admin()) {
        // Defer non-critical CSS
        add_filter('style_loader_tag', 'mcqhome_defer_non_critical_css', 10, 4);
    }
}
add_action('wp_enqueue_scripts', 'mcqhome_optimize_css_delivery', 100);

/**
 * Defer non-critical CSS
 */
function mcqhome_defer_non_critical_css($html, $handle, $href, $media) {
    // List of non-critical CSS handles
    $non_critical_styles = [
        'mcqhome-dashboard',
        'mcqhome-assessment',
        'mcqhome-browse',
        'mcqhome-mcq-builder',
        'mcqhome-mcq-editor'
    ];
    
    if (in_array($handle, $non_critical_styles)) {
        $html = '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
        $html .= '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
    }
    
    return $html;
}

/**
 * Optimize JavaScript loading
 */
function mcqhome_optimize_js_loading() {
    // Defer non-critical JavaScript
    add_filter('script_loader_tag', 'mcqhome_defer_non_critical_js', 10, 3);
}
add_action('wp_enqueue_scripts', 'mcqhome_optimize_js_loading', 100);

/**
 * Defer non-critical JavaScript
 */
function mcqhome_defer_non_critical_js($tag, $handle, $src) {
    // List of scripts to defer
    $defer_scripts = [
        'mcqhome-dashboard',
        'mcqhome-browse',
        'mcqhome-mcq-builder',
        'mcqhome-mcq-set-builder'
    ];
    
    // List of scripts to load async
    $async_scripts = [
        'mcqhome-customizer'
    ];
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    if (in_array($handle, $async_scripts)) {
        return str_replace(' src', ' async src', $tag);
    }
    
    return $tag;
}

/**
 * Add critical CSS inline
 */
function mcqhome_add_critical_css() {
    // Only add critical CSS on homepage and key pages
    if (is_front_page() || is_page(['dashboard', 'browse', 'institutions'])) {
        ?>
        <style id="mcqhome-critical-css">
        /* Critical CSS for above-the-fold content */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }
        }
        </style>
        <?php
    }
}
add_action('wp_head', 'mcqhome_add_critical_css', 3);

/**
 * Remove unnecessary WordPress features for performance
 */
function mcqhome_remove_unnecessary_features() {
    // Remove emoji scripts and styles
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // Remove WordPress version from head
    remove_action('wp_head', 'wp_generator');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove Windows Live Writer link
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Remove REST API links
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    
    // Remove feed links (if not needed)
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
}
add_action('init', 'mcqhome_remove_unnecessary_features');

/**
 * Optimize database queries
 */
function mcqhome_optimize_queries() {
    // Remove unnecessary queries
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    
    // Limit post revisions
    if (!defined('WP_POST_REVISIONS')) {
        define('WP_POST_REVISIONS', 3);
    }
    
    // Disable file editing in admin
    if (!defined('DISALLOW_FILE_EDIT')) {
        define('DISALLOW_FILE_EDIT', true);
    }
}
add_action('init', 'mcqhome_optimize_queries');

/**
 * Add cache headers for static assets
 */
function mcqhome_add_cache_headers() {
    if (!is_admin()) {
        // Add cache headers for static assets
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    }
}

/**
 * Minify HTML output
 */
function mcqhome_minify_html($buffer) {
    // Only minify on frontend
    if (is_admin() || wp_is_json_request()) {
        return $buffer;
    }
    
    // Simple HTML minification
    $buffer = preg_replace('/<!--(?!<!)[^\[>].*?-->/', '', $buffer);
    $buffer = preg_replace('/\s+/', ' ', $buffer);
    $buffer = preg_replace('/>\s+</', '><', $buffer);
    
    return trim($buffer);
}

/**
 * Enable HTML minification
 */
function mcqhome_enable_html_minification() {
    if (!is_admin() && !wp_doing_ajax()) {
        ob_start('mcqhome_minify_html');
    }
}
add_action('template_redirect', 'mcqhome_enable_html_minification');

/**
 * Add breadcrumb structured data
 */
function mcqhome_add_breadcrumb_schema() {
    if (is_singular() || is_category() || is_tag() || is_tax()) {
        $breadcrumbs = [];
        
        // Home
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => home_url()
        ];
        
        $position = 2;
        
        // Add category/taxonomy breadcrumbs
        if (is_singular()) {
            $post_type = get_post_type();
            
            if ($post_type === 'mcq_set') {
                $breadcrumbs[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => 'Browse',
                    'item' => home_url('/browse/')
                ];
                
                $subjects = wp_get_post_terms(get_the_ID(), 'mcq_subject');
                if (!empty($subjects)) {
                    $breadcrumbs[] = [
                        '@type' => 'ListItem',
                        'position' => $position++,
                        'name' => $subjects[0]->name,
                        'item' => get_term_link($subjects[0])
                    ];
                }
            }
            
            if ($post_type === 'institution') {
                $breadcrumbs[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => 'Institutions',
                    'item' => home_url('/institutions/')
                ];
            }
            
            // Current page
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => get_the_title(),
                'item' => get_permalink()
            ];
        }
        
        if (!empty($breadcrumbs)) {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbs
            ];
            
            echo '<script type="application/ld+json">';
            echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES);
            echo '</script>';
        }
    }
}
add_action('wp_head', 'mcqhome_add_breadcrumb_schema', 4);

/**
 * Add FAQ structured data for MCQ sets
 */
function mcqhome_add_faq_schema() {
    if (is_singular('mcq_set')) {
        global $post;
        
        // Get MCQs in this set
        $mcq_ids = get_post_meta($post->ID, '_mcq_set_questions', true);
        if (empty($mcq_ids) || !is_array($mcq_ids)) {
            return;
        }
        
        $faq_items = [];
        $count = 0;
        
        foreach ($mcq_ids as $mcq_id) {
            if ($count >= 5) break; // Limit to first 5 questions for schema
            
            $mcq_post = get_post($mcq_id);
            if (!$mcq_post) continue;
            
            $question_text = get_post_meta($mcq_id, '_mcq_question_text', true);
            $explanation = get_post_meta($mcq_id, '_mcq_explanation', true);
            
            if ($question_text && $explanation) {
                $faq_items[] = [
                    '@type' => 'Question',
                    'name' => wp_strip_all_tags($question_text),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => wp_strip_all_tags($explanation)
                    ]
                ];
                $count++;
            }
        }
        
        if (!empty($faq_items)) {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faq_items
            ];
            
            echo '<script type="application/ld+json">';
            echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            echo '</script>';
        }
    }
}
add_action('wp_head', 'mcqhome_add_faq_schema', 5);

/**
 * Add Course structured data for MCQ sets
 */
function mcqhome_add_course_schema() {
    if (is_singular('mcq_set')) {
        global $post;
        
        $question_count = mcqhome_get_mcq_set_question_count($post->ID);
        $total_marks = get_post_meta($post->ID, '_mcq_set_total_marks', true);
        $difficulty = wp_get_post_terms($post->ID, 'mcq_difficulty');
        $subjects = wp_get_post_terms($post->ID, 'mcq_subject');
        $author = get_the_author();
        $pricing = get_post_meta($post->ID, '_mcq_set_pricing', true);
        
        $course_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => get_the_title(),
            'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 30),
            'url' => get_permalink(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'instructor' => [
                '@type' => 'Person',
                'name' => $author
            ],
            'provider' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'url' => home_url()
            ],
            'courseMode' => 'online',
            'educationalCredentialAwarded' => 'Certificate of Completion'
        ];
        
        if ($question_count) {
            $course_schema['numberOfCredits'] = $question_count;
        }
        
        if (!empty($subjects)) {
            $course_schema['about'] = [
                '@type' => 'Thing',
                'name' => $subjects[0]->name
            ];
        }
        
        if (!empty($difficulty)) {
            $course_schema['educationalLevel'] = $difficulty[0]->name;
        }
        
        if (has_post_thumbnail()) {
            $course_schema['image'] = get_the_post_thumbnail_url($post, 'large');
        }
        
        // Add pricing information
        if ($pricing && isset($pricing['type'])) {
            if ($pricing['type'] === 'free') {
                $course_schema['isAccessibleForFree'] = true;
            } else {
                $course_schema['offers'] = [
                    '@type' => 'Offer',
                    'price' => $pricing['price'] ?? '0',
                    'priceCurrency' => 'USD',
                    'availability' => 'https://schema.org/InStock'
                ];
            }
        }
        
        echo '<script type="application/ld+json">';
        echo wp_json_encode($course_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo '</script>';
    }
}
add_action('wp_head', 'mcqhome_add_course_schema', 6);

/**
 * Add semantic HTML attributes to body
 */
function mcqhome_add_semantic_body_attributes($classes) {
    // Add microdata attributes
    if (is_singular('mcq_set')) {
        $classes[] = 'quiz-page';
    } elseif (is_singular('institution')) {
        $classes[] = 'institution-page';
    } elseif (is_author()) {
        $classes[] = 'author-page';
    } elseif (is_page('dashboard')) {
        $classes[] = 'dashboard-page';
    } elseif (is_page('browse')) {
        $classes[] = 'browse-page';
    }
    
    return $classes;
}
add_filter('body_class', 'mcqhome_add_semantic_body_attributes');

/**
 * Add semantic HTML5 elements and ARIA attributes
 */
function mcqhome_add_semantic_html() {
    // Add semantic navigation attributes
    add_filter('wp_nav_menu_args', function($args) {
        if ($args['theme_location'] === 'primary') {
            $args['container'] = 'nav';
            $args['container_aria_label'] = 'Primary Navigation';
            $args['container_role'] = 'navigation';
        }
        return $args;
    });
    
    // Add semantic search form attributes
    add_filter('get_search_form', function($form) {
        $form = str_replace('<form', '<form role="search" aria-label="Site Search"', $form);
        return $form;
    });
}
add_action('init', 'mcqhome_add_semantic_html');

/**
 * Add Open Graph image optimization
 */
function mcqhome_optimize_og_images() {
    if (is_singular()) {
        global $post;
        
        // Generate optimized OG image for MCQ sets
        if (get_post_type() === 'mcq_set') {
            $og_image_url = mcqhome_generate_og_image_for_mcq_set($post->ID);
            if ($og_image_url) {
                echo '<meta property="og:image" content="' . esc_url($og_image_url) . '">' . "\n";
                echo '<meta property="og:image:width" content="1200">' . "\n";
                echo '<meta property="og:image:height" content="630">' . "\n";
                echo '<meta property="og:image:type" content="image/png">' . "\n";
            }
        }
    }
}

/**
 * Generate OG image for MCQ sets
 */
function mcqhome_generate_og_image_for_mcq_set($post_id) {
    // This would generate a dynamic OG image with MCQ set details
    // For now, return the featured image or a default
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, 'large');
    }
    
    // Return default OG image
    return MCQHOME_THEME_URL . '/assets/images/default-og-image.png';
}

/**
 * Add Twitter Card optimization
 */
function mcqhome_optimize_twitter_cards() {
    if (is_singular('mcq_set')) {
        global $post;
        
        $question_count = mcqhome_get_mcq_set_question_count($post->ID);
        $total_marks = get_post_meta($post->ID, '_mcq_set_total_marks', true);
        $subjects = wp_get_post_terms($post->ID, 'mcq_subject');
        
        $twitter_description = '';
        if ($question_count) {
            $twitter_description .= $question_count . ' questions';
        }
        if ($total_marks) {
            $twitter_description .= ($twitter_description ? ' • ' : '') . $total_marks . ' marks';
        }
        if (!empty($subjects)) {
            $twitter_description .= ($twitter_description ? ' • ' : '') . $subjects[0]->name;
        }
        
        if ($twitter_description) {
            echo '<meta name="twitter:description" content="' . esc_attr($twitter_description) . '">' . "\n";
        }
        
        // Add Twitter Card with app info
        echo '<meta name="twitter:app:name:iphone" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        echo '<meta name="twitter:app:name:ipad" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        echo '<meta name="twitter:app:name:googleplay" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    }
}
add_action('wp_head', 'mcqhome_optimize_twitter_cards', 7);

/**
 * Add JSON-LD for local business (if institution has address)
 */
function mcqhome_add_local_business_schema() {
    if (is_singular('institution')) {
        global $post;
        
        $address = get_post_meta($post->ID, '_institution_address', true);
        $phone = get_post_meta($post->ID, '_institution_phone', true);
        $email = get_post_meta($post->ID, '_institution_email', true);
        $website = get_post_meta($post->ID, '_institution_website', true);
        
        if ($address || $phone || $email) {
            $business_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'EducationalOrganization',
                'name' => get_the_title(),
                'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 30),
                'url' => get_permalink()
            ];
            
            if ($address) {
                $business_schema['address'] = [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $address
                ];
            }
            
            if ($phone) {
                $business_schema['telephone'] = $phone;
            }
            
            if ($email) {
                $business_schema['email'] = $email;
            }
            
            if ($website) {
                $business_schema['sameAs'] = [$website];
            }
            
            if (has_post_thumbnail()) {
                $business_schema['logo'] = get_the_post_thumbnail_url($post, 'medium');
                $business_schema['image'] = get_the_post_thumbnail_url($post, 'large');
            }
            
            echo '<script type="application/ld+json">';
            echo wp_json_encode($business_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            echo '</script>';
        }
    }
}
add_action('wp_head', 'mcqhome_add_local_business_schema', 8);

/**
 * Add sitemap generation
 */
function mcqhome_generate_sitemap() {
    // Enable WordPress XML sitemaps
    add_filter('wp_sitemaps_enabled', '__return_true');
    
    // Add custom post types to sitemap
    add_filter('wp_sitemaps_post_types', function($post_types) {
        $post_types['mcq_set'] = get_post_type_object('mcq_set');
        $post_types['institution'] = get_post_type_object('institution');
        return $post_types;
    });
    
    // Add custom taxonomies to sitemap
    add_filter('wp_sitemaps_taxonomies', function($taxonomies) {
        $taxonomies['mcq_subject'] = get_taxonomy('mcq_subject');
        $taxonomies['mcq_topic'] = get_taxonomy('mcq_topic');
        $taxonomies['mcq_difficulty'] = get_taxonomy('mcq_difficulty');
        return $taxonomies;
    });
}
add_action('init', 'mcqhome_generate_sitemap');

/**
 * Add robots.txt optimization
 */
function mcqhome_optimize_robots_txt($output) {
    $output .= "\n# MCQHome Theme Optimizations\n";
    $output .= "Disallow: /wp-admin/\n";
    $output .= "Disallow: /wp-includes/\n";
    $output .= "Disallow: /wp-content/plugins/\n";
    $output .= "Disallow: /wp-content/themes/\n";
    $output .= "Disallow: /wp-json/\n";
    $output .= "Disallow: /xmlrpc.php\n";
    $output .= "Disallow: /?s=\n";
    $output .= "Disallow: /search/\n";
    $output .= "Allow: /wp-content/uploads/\n";
    $output .= "\n# Sitemap\n";
    $output .= "Sitemap: " . home_url('/wp-sitemap.xml') . "\n";
    
    return $output;
}
add_filter('robots_txt', 'mcqhome_optimize_robots_txt');

/**
 * Add canonical URL optimization
 */
function mcqhome_add_canonical_url() {
    if (is_singular()) {
        $canonical_url = get_permalink();
        
        // Remove query parameters for cleaner canonical URLs
        $canonical_url = strtok($canonical_url, '?');
        
        echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";
    } elseif (is_home() || is_front_page()) {
        echo '<link rel="canonical" href="' . esc_url(home_url('/')) . '">' . "\n";
    } elseif (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        echo '<link rel="canonical" href="' . esc_url(get_term_link($term)) . '">' . "\n";
    } elseif (is_author()) {
        $author = get_queried_object();
        echo '<link rel="canonical" href="' . esc_url(get_author_posts_url($author->ID)) . '">' . "\n";
    }
}
add_action('wp_head', 'mcqhome_add_canonical_url', 1);