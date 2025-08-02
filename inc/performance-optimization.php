<?php
/**
 * Performance Optimization Functions
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enable GZIP compression
 */
function mcqhome_enable_gzip_compression() {
    if (!ob_get_level() && extension_loaded('zlib') && !headers_sent()) {
        ob_start('ob_gzhandler');
    }
}
add_action('init', 'mcqhome_enable_gzip_compression', 1);

/**
 * Optimize WordPress database queries
 */
function mcqhome_optimize_database_queries() {
    // Remove unnecessary queries
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Disable pingbacks and trackbacks
    add_filter('xmlrpc_enabled', '__return_false');
    add_filter('wp_headers', 'mcqhome_remove_x_pingback_header');
    
    // Disable self-pingbacks
    add_action('pre_ping', 'mcqhome_disable_self_pingbacks');
    
    // Limit post revisions
    if (!defined('WP_POST_REVISIONS')) {
        define('WP_POST_REVISIONS', 3);
    }
    
    // Increase autosave interval
    if (!defined('AUTOSAVE_INTERVAL')) {
        define('AUTOSAVE_INTERVAL', 300); // 5 minutes
    }
}
add_action('init', 'mcqhome_optimize_database_queries');

/**
 * Remove X-Pingback header
 */
function mcqhome_remove_x_pingback_header($headers) {
    unset($headers['X-Pingback']);
    return $headers;
}

/**
 * Disable self-pingbacks
 */
function mcqhome_disable_self_pingbacks(&$links) {
    $home = get_option('home');
    foreach ($links as $l => $link) {
        if (strpos($link, $home) === 0) {
            unset($links[$l]);
        }
    }
}

/**
 * Optimize CSS and JavaScript loading
 */
function mcqhome_optimize_assets() {
    // Remove WordPress default styles and scripts we don't need
    if (!is_admin()) {
        // Remove block library CSS if not using Gutenberg blocks
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        
        // Remove classic theme styles
        wp_dequeue_style('classic-theme-styles');
        
        // Remove global styles
        wp_dequeue_style('global-styles');
        
        // Remove jQuery migrate
        add_action('wp_enqueue_scripts', 'mcqhome_remove_jquery_migrate');
    }
}
add_action('wp_enqueue_scripts', 'mcqhome_optimize_assets', 100);

/**
 * Remove jQuery migrate
 */
function mcqhome_remove_jquery_migrate() {
    if (!is_admin() && !is_customize_preview()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', includes_url('/js/jquery/jquery.min.js'), false, null, true);
        wp_enqueue_script('jquery');
    }
}

/**
 * Minify CSS output
 */
function mcqhome_minify_css($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    
    // Remove whitespace
    $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
    
    // Remove unnecessary spaces
    $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ' :', ' ,', ', '], [';', '{', '{', '}', '}', ':', ':', ',', ','], $css);
    
    return trim($css);
}

/**
 * Minify JavaScript output
 */
function mcqhome_minify_js($js) {
    // Simple JS minification - remove comments and extra whitespace
    $js = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '', $js);
    $js = preg_replace('/\s+/', ' ', $js);
    $js = str_replace(['; ', ' {', '{ ', ' }', '} ', ' (', '( ', ' )', ') ', ' =', '= ', ' +', '+ ', ' -', '- '], [';', '{', '{', '}', '}', '(', '(', ')', ')', '=', '=', '+', '+', '-', '-'], $js);
    
    return trim($js);
}

/**
 * Add cache headers for static assets
 */
function mcqhome_add_cache_headers() {
    if (!is_admin() && !is_user_logged_in()) {
        $expires = 31536000; // 1 year
        
        // Set cache headers for static assets
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $_SERVER['REQUEST_URI'])) {
            header('Cache-Control: public, max-age=' . $expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
            header('Pragma: public');
        }
    }
}
add_action('send_headers', 'mcqhome_add_cache_headers');

/**
 * Optimize images with lazy loading attributes
 */
function mcqhome_add_lazy_loading_attributes($attr, $attachment, $size) {
    // Skip if in admin or feed
    if (is_admin() || is_feed()) {
        return $attr;
    }
    
    // Add loading="lazy" for better performance
    if (!isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    
    // Add decoding="async" for better performance
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'mcqhome_add_lazy_loading_attributes', 10, 3);

/**
 * Optimize content images with advanced lazy loading
 */
function mcqhome_optimize_content_images($content) {
    // Add lazy loading to content images with intersection observer fallback
    $content = preg_replace_callback(
        '/<img([^>]+)>/i',
        function($matches) {
            $img_tag = $matches[0];
            $attributes = $matches[1];
            
            // Skip if already has loading attribute
            if (strpos($attributes, 'loading=') !== false) {
                return $img_tag;
            }
            
            // Add lazy loading attributes
            $img_tag = str_replace('<img ', '<img loading="lazy" decoding="async" ', $img_tag);
            
            // Add fetchpriority="low" for better performance (except for first image)
            if (strpos($img_tag, 'fetchpriority=') === false) {
                static $first_image = true;
                if ($first_image) {
                    $img_tag = str_replace('<img ', '<img fetchpriority="high" ', $img_tag);
                    $first_image = false;
                } else {
                    $img_tag = str_replace('<img ', '<img fetchpriority="low" ', $img_tag);
                }
            }
            
            // Add intersection observer data attributes for advanced lazy loading
            $img_tag = str_replace('<img ', '<img data-lazy="true" ', $img_tag);
            
            return $img_tag;
        },
        $content
    );
    
    return $content;
}
add_filter('the_content', 'mcqhome_optimize_content_images');

/**
 * Add lazy loading JavaScript for advanced image loading
 */
function mcqhome_add_lazy_loading_script() {
    ?>
    <script>
    // Intersection Observer for lazy loading
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Load the image
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                        img.removeAttribute('data-srcset');
                    }
                    
                    img.classList.remove('lazy');
                    img.classList.add('lazy-loaded');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });
        
        // Observe all lazy images
        document.querySelectorAll('img[data-lazy]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Fallback for browsers without Intersection Observer
    else {
        document.querySelectorAll('img[data-lazy]').forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
            }
        });
    }
    </script>
    <?php
}
add_action('wp_footer', 'mcqhome_add_lazy_loading_script');

/**
 * Add lazy loading for background images
 */
function mcqhome_lazy_load_background_images($content) {
    // Convert inline background images to lazy loading
    $content = preg_replace_callback(
        '/style=["\']([^"\']*background-image:\s*url\([^)]+\)[^"\']*)["\']/',
        function($matches) {
            $style = $matches[1];
            
            // Extract background image URL
            if (preg_match('/background-image:\s*url\(([^)]+)\)/', $style, $url_matches)) {
                $image_url = trim($url_matches[1], '"\'');
                $style_without_bg = preg_replace('/background-image:\s*url\([^)]+\);?/', '', $style);
                
                return 'data-bg="' . esc_attr($image_url) . '" style="' . esc_attr($style_without_bg) . '"';
            }
            
            return $matches[0];
        },
        $content
    );
    
    return $content;
}
add_filter('the_content', 'mcqhome_lazy_load_background_images');

/**
 * Preload critical resources
 */
function mcqhome_preload_critical_resources() {
    // Preload critical CSS
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
    echo '<noscript><link rel="stylesheet" href="' . get_stylesheet_uri() . '"></noscript>' . "\n";
    
    // Preload main CSS if exists
    if (file_exists(MCQHOME_THEME_DIR . '/assets/css/main.css')) {
        echo '<link rel="preload" href="' . MCQHOME_THEME_URL . '/assets/css/main.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        echo '<noscript><link rel="stylesheet" href="' . MCQHOME_THEME_URL . '/assets/css/main.css"></noscript>' . "\n";
    }
    
    // Preload fonts
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    
    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
}
add_action('wp_head', 'mcqhome_preload_critical_resources', 1);

/**
 * Defer non-critical CSS
 */
function mcqhome_defer_non_critical_css($html, $handle, $href, $media) {
    // List of non-critical CSS handles to defer
    $defer_styles = [
        'mcqhome-dashboard',
        'mcqhome-assessment',
        'mcqhome-browse',
        'mcqhome-mcq-builder',
        'mcqhome-mcq-editor',
        'mcqhome-mcq-set-builder'
    ];
    
    if (in_array($handle, $defer_styles)) {
        return '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . 
               '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
    }
    
    return $html;
}
add_filter('style_loader_tag', 'mcqhome_defer_non_critical_css', 10, 4);

/**
 * Defer and async JavaScript
 */
function mcqhome_defer_scripts($tag, $handle, $src) {
    // Scripts to defer (load after HTML parsing)
    $defer_scripts = [
        'mcqhome-dashboard',
        'mcqhome-browse',
        'mcqhome-mcq-builder',
        'mcqhome-mcq-set-builder'
    ];
    
    // Scripts to load async (load in parallel)
    $async_scripts = [
        'google-analytics',
        'gtag'
    ];
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    if (in_array($handle, $async_scripts)) {
        return str_replace(' src', ' async src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'mcqhome_defer_scripts', 10, 3);

/**
 * Remove unused WordPress features
 */
function mcqhome_remove_unused_features() {
    // Remove emoji support
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    
    // Remove oEmbed discovery links
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
    
    // Remove REST API links
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    
    // Remove feed links if not needed
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    
    // Remove adjacent post links
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
}
add_action('init', 'mcqhome_remove_unused_features');

/**
 * Optimize WordPress heartbeat
 */
function mcqhome_optimize_heartbeat() {
    // Disable heartbeat on frontend
    if (!is_admin()) {
        wp_deregister_script('heartbeat');
    }
    
    // Modify heartbeat settings in admin
    add_filter('heartbeat_settings', function($settings) {
        $settings['interval'] = 60; // 60 seconds instead of 15
        return $settings;
    });
}
add_action('init', 'mcqhome_optimize_heartbeat');

/**
 * Clean up WordPress head
 */
function mcqhome_cleanup_wp_head() {
    // Remove version numbers for security
    remove_action('wp_head', 'wp_generator');
    
    // Remove Windows Live Writer link
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Remove canonical link (we'll add our own)
    remove_action('wp_head', 'rel_canonical');
}
add_action('init', 'mcqhome_cleanup_wp_head');

/**
 * Optimize database cleanup
 */
function mcqhome_database_cleanup() {
    global $wpdb;
    
    // Clean up spam comments
    $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam' AND comment_date < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    // Clean up trashed posts older than 30 days
    $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_status = 'trash' AND post_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    // Clean up expired transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_name NOT LIKE '_transient_timeout_%' AND option_name NOT IN (SELECT REPLACE(option_name, '_transient_timeout_', '_transient_') FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%')");
    
    // Optimize database tables
    $wpdb->query("OPTIMIZE TABLE {$wpdb->posts}");
    $wpdb->query("OPTIMIZE TABLE {$wpdb->postmeta}");
    $wpdb->query("OPTIMIZE TABLE {$wpdb->comments}");
    $wpdb->query("OPTIMIZE TABLE {$wpdb->commentmeta}");
    $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");
}

/**
 * Schedule database cleanup
 */
function mcqhome_schedule_cleanup() {
    if (!wp_next_scheduled('mcqhome_database_cleanup')) {
        wp_schedule_event(time(), 'weekly', 'mcqhome_database_cleanup');
    }
}
add_action('wp', 'mcqhome_schedule_cleanup');
add_action('mcqhome_database_cleanup', 'mcqhome_database_cleanup');

/**
 * Enable browser caching via .htaccess
 */
function mcqhome_enable_browser_caching() {
    $htaccess_rules = '
# MCQHome Theme - Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType application/x-javascript "access plus 1 year"
    ExpiresByType text/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType text/plain "access plus 1 month"
    ExpiresByType application/x-font-woff "access plus 1 year"
    ExpiresByType application/x-font-woff2 "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType application/vnd.ms-fontobject "access plus 1 year"
    ExpiresByType application/x-font-ttf "access plus 1 year"
    ExpiresByType font/opentype "access plus 1 year"
</IfModule>

# MCQHome Theme - Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE text/javascript
</IfModule>

# MCQHome Theme - Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>
';
    
    return $htaccess_rules;
}

/**
 * Add performance monitoring
 */
function mcqhome_performance_monitoring() {
    if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
        add_action('wp_footer', function() {
            $queries = get_num_queries();
            $memory = size_format(memory_get_peak_usage(true));
            $time = timer_stop(0, 3);
            
            echo "<!-- Performance: {$queries} queries, {$memory} memory, {$time}s -->";
        });
    }
}
add_action('init', 'mcqhome_performance_monitoring');