<?php
/**
 * Asset Minification Functions
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Minify CSS files
 */
function mcqhome_minify_css_files() {
    $css_files = [
        'main.css',
        'dashboard.css',
        'assessment.css',
        'browse.css',
        'mcq-builder.css',
        'mcq-editor.css',
        'mcq-set-builder.css'
    ];
    
    foreach ($css_files as $file) {
        $source_path = MCQHOME_THEME_DIR . '/assets/css/' . $file;
        $minified_path = MCQHOME_THEME_DIR . '/assets/css/min/' . str_replace('.css', '.min.css', $file);
        
        if (file_exists($source_path)) {
            $css_content = file_get_contents($source_path);
            $minified_css = mcqhome_minify_css_content($css_content);
            
            // Create min directory if it doesn't exist
            $min_dir = dirname($minified_path);
            if (!file_exists($min_dir)) {
                wp_mkdir_p($min_dir);
            }
            
            file_put_contents($minified_path, $minified_css);
        }
    }
}

/**
 * Minify JavaScript files
 */
function mcqhome_minify_js_files() {
    $js_files = [
        'main.js',
        'dashboard.js',
        'assessment.js',
        'browse.js',
        'mcq-builder.js',
        'mcq-set-builder.js'
    ];
    
    foreach ($js_files as $file) {
        $source_path = MCQHOME_THEME_DIR . '/assets/js/' . $file;
        $minified_path = MCQHOME_THEME_DIR . '/assets/js/min/' . str_replace('.js', '.min.js', $file);
        
        if (file_exists($source_path)) {
            $js_content = file_get_contents($source_path);
            $minified_js = mcqhome_minify_js_content($js_content);
            
            // Create min directory if it doesn't exist
            $min_dir = dirname($minified_path);
            if (!file_exists($min_dir)) {
                wp_mkdir_p($min_dir);
            }
            
            file_put_contents($minified_path, $minified_js);
        }
    }
}

/**
 * Minify CSS content
 */
function mcqhome_minify_css_content($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    
    // Remove whitespace
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    
    // Remove extra spaces
    $css = preg_replace('/\s+/', ' ', $css);
    
    // Remove spaces around specific characters
    $css = str_replace([' {', '{ ', ' }', '} ', ' :', ': ', ' ;', '; ', ' ,', ', ', ' >', '> ', ' +', '+ ', ' ~', '~ '], ['{', '{', '}', '}', ':', ':', ';', ';', ',', ',', '>', '>', '+', '+', '~', '~'], $css);
    
    // Remove trailing semicolon before closing brace
    $css = str_replace(';}', '}', $css);
    
    return trim($css);
}

/**
 * Minify JavaScript content
 */
function mcqhome_minify_js_content($js) {
    // Remove single-line comments (but preserve URLs)
    $js = preg_replace('/(?<![:\'])\/\/.*$/m', '', $js);
    
    // Remove multi-line comments
    $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
    
    // Remove extra whitespace
    $js = preg_replace('/\s+/', ' ', $js);
    
    // Remove spaces around operators and punctuation
    $js = str_replace([' = ', ' + ', ' - ', ' * ', ' / ', ' % ', ' == ', ' === ', ' != ', ' !== ', ' < ', ' > ', ' <= ', ' >= ', ' && ', ' || ', ' { ', ' } ', ' ( ', ' ) ', ' [ ', ' ] ', ' ; ', ' , '], ['=', '+', '-', '*', '/', '%', '==', '===', '!=', '!==', '<', '>', '<=', '>=', '&&', '||', '{', '}', '(', ')', '[', ']', ';', ','], $js);
    
    return trim($js);
}

/**
 * Use minified assets in production
 */
function mcqhome_use_minified_assets() {
    // Only use minified assets if not in debug mode
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        add_filter('mcqhome_asset_url', 'mcqhome_get_minified_asset_url', 10, 2);
    }
}
add_action('init', 'mcqhome_use_minified_assets');

/**
 * Get minified asset URL
 */
function mcqhome_get_minified_asset_url($url, $file) {
    $file_info = pathinfo($file);
    $extension = $file_info['extension'];
    $filename = $file_info['filename'];
    
    $minified_file = $filename . '.min.' . $extension;
    $minified_path = MCQHOME_THEME_DIR . '/assets/' . $extension . '/min/' . $minified_file;
    
    if (file_exists($minified_path)) {
        return MCQHOME_THEME_URL . '/assets/' . $extension . '/min/' . $minified_file;
    }
    
    return $url;
}

/**
 * Combine CSS files for better performance
 */
function mcqhome_combine_css_files() {
    $css_files = [
        'main.css',
        'dashboard.css',
        'assessment.css',
        'browse.css'
    ];
    
    $combined_css = '';
    $combined_file_path = MCQHOME_THEME_DIR . '/assets/css/combined.min.css';
    
    foreach ($css_files as $file) {
        $file_path = MCQHOME_THEME_DIR . '/assets/css/' . $file;
        if (file_exists($file_path)) {
            $css_content = file_get_contents($file_path);
            $combined_css .= "/* {$file} */\n" . $css_content . "\n\n";
        }
    }
    
    if ($combined_css) {
        $minified_combined = mcqhome_minify_css_content($combined_css);
        file_put_contents($combined_file_path, $minified_combined);
    }
}

/**
 * Combine JavaScript files for better performance
 */
function mcqhome_combine_js_files() {
    $js_files = [
        'main.js',
        'dashboard.js',
        'assessment.js',
        'browse.js'
    ];
    
    $combined_js = '';
    $combined_file_path = MCQHOME_THEME_DIR . '/assets/js/combined.min.js';
    
    foreach ($js_files as $file) {
        $file_path = MCQHOME_THEME_DIR . '/assets/js/' . $file;
        if (file_exists($file_path)) {
            $js_content = file_get_contents($file_path);
            $combined_js .= "/* {$file} */\n" . $js_content . "\n\n";
        }
    }
    
    if ($combined_js) {
        $minified_combined = mcqhome_minify_js_content($combined_js);
        file_put_contents($combined_file_path, $minified_combined);
    }
}

/**
 * Generate critical CSS for above-the-fold content
 */
function mcqhome_generate_critical_css() {
    $critical_css = '
/* Critical CSS for above-the-fold content */
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    color: #1f2937;
    background-color: #ffffff;
}

.header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
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
    transition: all 0.2s ease-in-out;
    border: none;
    cursor: pointer;
    text-align: center;
}

.btn-primary {
    background-color: #2563eb;
    color: #ffffff;
}

.btn-primary:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #6b7280;
    color: #ffffff;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.loading {
    opacity: 0.6;
    pointer-events: none;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Navigation */
.nav-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-menu li {
    margin: 0 1rem;
}

.nav-menu a {
    text-decoration: none;
    color: #374151;
    font-weight: 500;
    transition: color 0.2s;
}

.nav-menu a:hover {
    color: #2563eb;
}

/* Hero section */
.hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    padding: 4rem 0;
    text-align: center;
}

.hero h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.hero p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

/* Cards */
.card {
    background: #ffffff;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* Responsive design */
@media (max-width: 768px) {
    .container {
        padding: 0 0.5rem;
    }
    
    .hero h1 {
        font-size: 2rem;
    }
    
    .hero p {
        font-size: 1rem;
    }
    
    .nav-menu {
        flex-direction: column;
    }
    
    .nav-menu li {
        margin: 0.5rem 0;
    }
}

/* Form elements */
.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #374151;
}
';
    
    $critical_css_path = MCQHOME_THEME_DIR . '/assets/css/critical.min.css';
    $minified_critical = mcqhome_minify_css_content($critical_css);
    file_put_contents($critical_css_path, $minified_critical);
}

/**
 * Build assets on theme activation
 */
function mcqhome_build_assets() {
    mcqhome_minify_css_files();
    mcqhome_minify_js_files();
    mcqhome_combine_css_files();
    mcqhome_combine_js_files();
    mcqhome_generate_critical_css();
}

/**
 * Schedule asset building
 */
function mcqhome_schedule_asset_building() {
    // Build assets on theme activation
    add_action('after_switch_theme', 'mcqhome_build_assets');
    
    // Rebuild assets when files are modified (in development)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        add_action('wp_loaded', 'mcqhome_check_asset_modifications');
    }
}
add_action('init', 'mcqhome_schedule_asset_building');

/**
 * Check for asset modifications and rebuild if necessary
 */
function mcqhome_check_asset_modifications() {
    $css_dir = MCQHOME_THEME_DIR . '/assets/css/';
    $js_dir = MCQHOME_THEME_DIR . '/assets/js/';
    $combined_css_file = $css_dir . 'combined.min.css';
    $combined_js_file = $js_dir . 'combined.min.js';
    
    $rebuild_needed = false;
    
    // Check if combined files exist
    if (!file_exists($combined_css_file) || !file_exists($combined_js_file)) {
        $rebuild_needed = true;
    } else {
        // Check modification times
        $combined_css_time = filemtime($combined_css_file);
        $combined_js_time = filemtime($combined_js_file);
        
        // Check CSS files
        $css_files = glob($css_dir . '*.css');
        foreach ($css_files as $file) {
            if (basename($file) !== 'combined.min.css' && filemtime($file) > $combined_css_time) {
                $rebuild_needed = true;
                break;
            }
        }
        
        // Check JS files
        if (!$rebuild_needed) {
            $js_files = glob($js_dir . '*.js');
            foreach ($js_files as $file) {
                if (basename($file) !== 'combined.min.js' && filemtime($file) > $combined_js_time) {
                    $rebuild_needed = true;
                    break;
                }
            }
        }
    }
    
    if ($rebuild_needed) {
        mcqhome_build_assets();
    }
}

/**
 * Add asset versioning for cache busting
 */
function mcqhome_add_asset_version($src, $handle) {
    // Add file modification time as version for cache busting
    if (strpos($src, MCQHOME_THEME_URL) !== false) {
        $file_path = str_replace(MCQHOME_THEME_URL, MCQHOME_THEME_DIR, $src);
        if (file_exists($file_path)) {
            $version = filemtime($file_path);
            $src = add_query_arg('v', $version, $src);
        }
    }
    
    return $src;
}
add_filter('style_loader_src', 'mcqhome_add_asset_version', 10, 2);
add_filter('script_loader_src', 'mcqhome_add_asset_version', 10, 2);