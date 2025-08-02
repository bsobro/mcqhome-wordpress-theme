<?php
/**
 * Semantic HTML Helper Functions
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output semantic header with proper ARIA attributes
 */
function mcqhome_semantic_header() {
    ?>
    <header class="site-header" role="banner" itemscope itemtype="https://schema.org/WPHeader">
        <div class="container">
            <div class="header-content">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-logo" itemprop="logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <h1 class="site-title" itemprop="name">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <?php bloginfo('name'); ?>
                        </a>
                    </h1>
                <?php endif; ?>
                
                <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Primary Navigation', 'mcqhome'); ?>" itemscope itemtype="https://schema.org/SiteNavigationElement">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_class' => 'nav-menu',
                        'container' => false,
                        'fallback_cb' => 'mcqhome_fallback_menu'
                    ]);
                    ?>
                </nav>
            </div>
        </div>
    </header>
    <?php
}

/**
 * Output semantic footer with proper ARIA attributes
 */
function mcqhome_semantic_footer() {
    ?>
    <footer class="site-footer" role="contentinfo" itemscope itemtype="https://schema.org/WPFooter">
        <div class="container">
            <div class="footer-content">
                <?php if (is_active_sidebar('footer-1')) : ?>
                    <aside class="footer-widgets" role="complementary" aria-label="<?php esc_attr_e('Footer Widgets', 'mcqhome'); ?>">
                        <?php dynamic_sidebar('footer-1'); ?>
                    </aside>
                <?php endif; ?>
                
                <div class="footer-info">
                    <p class="copyright" itemprop="copyrightNotice">
                        &copy; <?php echo date('Y'); ?> 
                        <span itemprop="copyrightHolder" itemscope itemtype="https://schema.org/Organization">
                            <span itemprop="name"><?php bloginfo('name'); ?></span>
                        </span>
                        <?php esc_html_e('All rights reserved.', 'mcqhome'); ?>
                    </p>
                    
                    <?php if (has_nav_menu('footer')) : ?>
                        <nav class="footer-navigation" role="navigation" aria-label="<?php esc_attr_e('Footer Navigation', 'mcqhome'); ?>">
                            <?php
                            wp_nav_menu([
                                'theme_location' => 'footer',
                                'menu_class' => 'footer-nav-menu',
                                'container' => false,
                                'depth' => 1
                            ]);
                            ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
    <?php
}

/**
 * Output semantic main content area
 */
function mcqhome_semantic_main_start($class = '') {
    $classes = 'main-content';
    if ($class) {
        $classes .= ' ' . $class;
    }
    ?>
    <main class="<?php echo esc_attr($classes); ?>" role="main" id="main-content" tabindex="-1">
    <?php
}

/**
 * Close semantic main content area
 */
function mcqhome_semantic_main_end() {
    ?>
    </main>
    <?php
}

/**
 * Output semantic article with proper microdata
 */
function mcqhome_semantic_article_start($post_id = null) {
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    
    $post_type = get_post_type($post_id);
    $schema_type = 'Article';
    
    // Set appropriate schema type based on post type
    switch ($post_type) {
        case 'mcq_set':
            $schema_type = 'Quiz';
            break;
        case 'institution':
            $schema_type = 'EducationalOrganization';
            break;
        case 'mcq':
            $schema_type = 'Question';
            break;
    }
    ?>
    <article class="post-<?php echo $post_id; ?> <?php echo esc_attr($post_type); ?>" 
             itemscope 
             itemtype="https://schema.org/<?php echo $schema_type; ?>"
             role="article">
    <?php
}

/**
 * Close semantic article
 */
function mcqhome_semantic_article_end() {
    ?>
    </article>
    <?php
}

/**
 * Output semantic heading with proper hierarchy
 */
function mcqhome_semantic_heading($text, $level = 1, $class = '', $itemprop = '') {
    $level = max(1, min(6, intval($level))); // Ensure level is between 1-6
    $classes = $class ? ' class="' . esc_attr($class) . '"' : '';
    $microdata = $itemprop ? ' itemprop="' . esc_attr($itemprop) . '"' : '';
    
    echo "<h{$level}{$classes}{$microdata}>" . esc_html($text) . "</h{$level}>";
}

/**
 * Output semantic breadcrumbs
 */
function mcqhome_semantic_breadcrumbs() {
    if (is_front_page()) {
        return;
    }
    
    $breadcrumbs = [];
    
    // Home
    $breadcrumbs[] = [
        'url' => home_url('/'),
        'title' => __('Home', 'mcqhome')
    ];
    
    // Add breadcrumbs based on current page
    if (is_singular()) {
        $post_type = get_post_type();
        
        if ($post_type === 'mcq_set') {
            $breadcrumbs[] = [
                'url' => home_url('/browse/'),
                'title' => __('Browse', 'mcqhome')
            ];
            
            $subjects = wp_get_post_terms(get_the_ID(), 'mcq_subject');
            if (!empty($subjects)) {
                $breadcrumbs[] = [
                    'url' => get_term_link($subjects[0]),
                    'title' => $subjects[0]->name
                ];
            }
        } elseif ($post_type === 'institution') {
            $breadcrumbs[] = [
                'url' => home_url('/institutions/'),
                'title' => __('Institutions', 'mcqhome')
            ];
        }
        
        // Current page
        $breadcrumbs[] = [
            'url' => get_permalink(),
            'title' => get_the_title(),
            'current' => true
        ];
    } elseif (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        $breadcrumbs[] = [
            'url' => get_term_link($term),
            'title' => $term->name,
            'current' => true
        ];
    } elseif (is_author()) {
        $author = get_queried_object();
        $breadcrumbs[] = [
            'url' => get_author_posts_url($author->ID),
            'title' => $author->display_name,
            'current' => true
        ];
    }
    
    if (count($breadcrumbs) > 1) {
        ?>
        <nav class="breadcrumbs" role="navigation" aria-label="<?php esc_attr_e('Breadcrumb Navigation', 'mcqhome'); ?>" itemscope itemtype="https://schema.org/BreadcrumbList">
            <ol class="breadcrumb-list">
                <?php foreach ($breadcrumbs as $index => $breadcrumb) : ?>
                    <li class="breadcrumb-item<?php echo isset($breadcrumb['current']) ? ' current' : ''; ?>" 
                        itemprop="itemListElement" 
                        itemscope 
                        itemtype="https://schema.org/ListItem">
                        <?php if (isset($breadcrumb['current'])) : ?>
                            <span itemprop="name" aria-current="page"><?php echo esc_html($breadcrumb['title']); ?></span>
                        <?php else : ?>
                            <a href="<?php echo esc_url($breadcrumb['url']); ?>" itemprop="item">
                                <span itemprop="name"><?php echo esc_html($breadcrumb['title']); ?></span>
                            </a>
                        <?php endif; ?>
                        <meta itemprop="position" content="<?php echo $index + 1; ?>">
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
    }
}

/**
 * Output semantic search form
 */
function mcqhome_semantic_search_form($placeholder = '') {
    if (!$placeholder) {
        $placeholder = __('Search MCQs, institutions, teachers...', 'mcqhome');
    }
    ?>
    <form class="search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Site Search', 'mcqhome'); ?>">
        <label for="search-field" class="sr-only"><?php esc_html_e('Search for:', 'mcqhome'); ?></label>
        <input type="search" 
               id="search-field" 
               class="search-field" 
               placeholder="<?php echo esc_attr($placeholder); ?>" 
               value="<?php echo get_search_query(); ?>" 
               name="s" 
               required
               aria-describedby="search-submit">
        <button type="submit" 
                id="search-submit" 
                class="search-submit" 
                aria-label="<?php esc_attr_e('Submit Search', 'mcqhome'); ?>">
            <span class="sr-only"><?php esc_html_e('Search', 'mcqhome'); ?></span>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </form>
    <?php
}

/**
 * Output semantic pagination
 */
function mcqhome_semantic_pagination($query = null) {
    global $wp_query;
    
    if (!$query) {
        $query = $wp_query;
    }
    
    $total_pages = $query->max_num_pages;
    $current_page = max(1, get_query_var('paged'));
    
    if ($total_pages <= 1) {
        return;
    }
    ?>
    <nav class="pagination-nav" role="navigation" aria-label="<?php esc_attr_e('Posts Navigation', 'mcqhome'); ?>">
        <?php
        echo paginate_links([
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format' => '?paged=%#%',
            'current' => $current_page,
            'total' => $total_pages,
            'prev_text' => '<span class="sr-only">' . __('Previous Page', 'mcqhome') . '</span><span aria-hidden="true">&laquo;</span>',
            'next_text' => '<span class="sr-only">' . __('Next Page', 'mcqhome') . '</span><span aria-hidden="true">&raquo;</span>',
            'before_page_number' => '<span class="sr-only">' . __('Page', 'mcqhome') . ' </span>',
            'type' => 'list'
        ]);
        ?>
    </nav>
    <?php
}

/**
 * Output semantic skip links for accessibility
 */
function mcqhome_semantic_skip_links() {
    ?>
    <div class="skip-links">
        <a class="skip-link sr-only-focusable" href="#main-content"><?php esc_html_e('Skip to main content', 'mcqhome'); ?></a>
        <a class="skip-link sr-only-focusable" href="#main-navigation"><?php esc_html_e('Skip to navigation', 'mcqhome'); ?></a>
        <a class="skip-link sr-only-focusable" href="#footer"><?php esc_html_e('Skip to footer', 'mcqhome'); ?></a>
    </div>
    <?php
}

/**
 * Add semantic attributes to body tag
 */
function mcqhome_body_semantic_attributes($attributes) {
    $attributes['itemscope'] = '';
    $attributes['itemtype'] = 'https://schema.org/WebPage';
    
    if (is_singular('mcq_set')) {
        $attributes['itemtype'] = 'https://schema.org/Quiz';
    } elseif (is_singular('institution')) {
        $attributes['itemtype'] = 'https://schema.org/EducationalOrganization';
    } elseif (is_author()) {
        $attributes['itemtype'] = 'https://schema.org/ProfilePage';
    }
    
    return $attributes;
}
add_filter('mcqhome_body_attributes', 'mcqhome_body_semantic_attributes');

/**
 * Fallback menu for primary navigation
 */
function mcqhome_fallback_menu() {
    ?>
    <ul class="nav-menu fallback-menu">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'mcqhome'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/browse/')); ?>"><?php esc_html_e('Browse', 'mcqhome'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/institutions/')); ?>"><?php esc_html_e('Institutions', 'mcqhome'); ?></a></li>
        <?php if (is_user_logged_in()) : ?>
            <li><a href="<?php echo esc_url(home_url('/dashboard/')); ?>"><?php esc_html_e('Dashboard', 'mcqhome'); ?></a></li>
        <?php else : ?>
            <li><a href="<?php echo esc_url(home_url('/register/')); ?>"><?php esc_html_e('Register', 'mcqhome'); ?></a></li>
            <li><a href="<?php echo esc_url(wp_login_url()); ?>"><?php esc_html_e('Login', 'mcqhome'); ?></a></li>
        <?php endif; ?>
    </ul>
    <?php
}

/**
 * Add ARIA attributes to menu items
 */
function mcqhome_add_menu_aria_attributes($atts, $item, $args) {
    // Add ARIA attributes for dropdown menus
    if (in_array('menu-item-has-children', $item->classes)) {
        $atts['aria-haspopup'] = 'true';
        $atts['aria-expanded'] = 'false';
    }
    
    // Add current page indicator
    if (in_array('current-menu-item', $item->classes)) {
        $atts['aria-current'] = 'page';
    }
    
    return $atts;
}
add_filter('nav_menu_link_attributes', 'mcqhome_add_menu_aria_attributes', 10, 3);

/**
 * Add semantic HTML5 input types
 */
function mcqhome_html5_input_types($field_type, $field_name) {
    $html5_types = [
        'email' => 'email',
        'url' => 'url',
        'phone' => 'tel',
        'number' => 'number',
        'date' => 'date',
        'time' => 'time',
        'search' => 'search'
    ];
    
    foreach ($html5_types as $pattern => $type) {
        if (strpos($field_name, $pattern) !== false) {
            return $type;
        }
    }
    
    return $field_type;
}

/**
 * Add semantic validation attributes
 */
function mcqhome_add_validation_attributes($field_name, $required = false) {
    $attributes = [];
    
    if ($required) {
        $attributes['required'] = 'required';
        $attributes['aria-required'] = 'true';
    }
    
    // Add pattern validation for specific fields
    if (strpos($field_name, 'email') !== false) {
        $attributes['pattern'] = '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$';
        $attributes['title'] = __('Please enter a valid email address', 'mcqhome');
    } elseif (strpos($field_name, 'phone') !== false) {
        $attributes['pattern'] = '[0-9\-\+\s\(\)]+';
        $attributes['title'] = __('Please enter a valid phone number', 'mcqhome');
    } elseif (strpos($field_name, 'url') !== false) {
        $attributes['pattern'] = 'https?://.+';
        $attributes['title'] = __('Please enter a valid URL starting with http:// or https://', 'mcqhome');
    }
    
    return $attributes;
}