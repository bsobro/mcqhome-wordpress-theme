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
    <header id="masthead" class="site-header bg-white shadow-sm border-b" role="banner" itemscope itemtype="https://schema.org/WPHeader">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="site-branding">
                    <?php if (has_custom_logo()) : ?>
                        <div class="site-logo" itemprop="logo">
                            <?php the_custom_logo(); ?>
                        </div>
                    <?php else : ?>
                        <h1 class="site-title text-2xl font-bold" itemprop="name">
                            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="text-gray-900 hover:text-blue-600">
                                <?php bloginfo('name'); ?>
                            </a>
                        </h1>
                        <?php
                        $description = get_bloginfo('description', 'display');
                        if ($description || is_customize_preview()) :
                        ?>
                            <p class="site-description text-gray-600 text-sm"><?php echo $description; ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <nav id="site-navigation" class="main-navigation hidden md:block" role="navigation" aria-label="<?php esc_attr_e('Primary Navigation', 'mcqhome'); ?>" itemscope itemtype="https://schema.org/SiteNavigationElement">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_id' => 'primary-menu',
                        'container' => false,
                        'menu_class' => 'flex space-x-6',
                        'fallback_cb' => 'mcqhome_fallback_menu'
                    ]);
                    ?>
                </nav>

                <div class="header-actions flex items-center space-x-4">
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('dashboard'))); ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                            <?php esc_html_e('Dashboard', 'mcqhome'); ?>
                        </a>
                        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="text-gray-600 hover:text-gray-900">
                            <?php esc_html_e('Logout', 'mcqhome'); ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url()); ?>" class="text-gray-600 hover:text-gray-900">
                            <?php esc_html_e('Login', 'mcqhome'); ?>
                        </a>
                        <a href="<?php echo esc_url(wp_registration_url()); ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                            <?php esc_html_e('Register', 'mcqhome'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <button class="mobile-menu-toggle md:hidden p-2" aria-label="<?php esc_attr_e('Toggle mobile menu', 'mcqhome'); ?>">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile menu -->
            <div class="mobile-menu hidden md:hidden py-4 border-t">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'menu_id' => 'mobile-menu',
                    'container' => false,
                    'menu_class' => 'flex flex-col space-y-2',
                    'fallback_cb' => 'mcqhome_fallback_menu'
                ]);
                ?>
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
    <footer id="colophon" class="site-footer bg-gray-900 text-white mt-auto" role="contentinfo" itemscope itemtype="https://schema.org/WPFooter">
        <div class="container mx-auto px-4 py-8">
            <?php if (is_active_sidebar('footer-1')) : ?>
                <div class="footer-widgets mb-8" role="complementary" aria-label="<?php esc_attr_e('Footer Widgets', 'mcqhome'); ?>">
                    <?php dynamic_sidebar('footer-1'); ?>
                </div>
            <?php endif; ?>

            <div class="footer-info grid md:grid-cols-3 gap-8 mb-8">
                <div class="footer-about">
                    <h3 class="text-lg font-semibold mb-4" itemprop="name"><?php bloginfo('name'); ?></h3>
                    <p class="text-gray-300">
                        <?php 
                        $description = get_bloginfo('description');
                        echo $description ? esc_html($description) : esc_html__('Your comprehensive MCQ learning platform', 'mcqhome');
                        ?>
                    </p>
                </div>

                <div class="footer-links">
                    <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Quick Links', 'mcqhome'); ?></h3>
                    <?php if (has_nav_menu('footer')) : ?>
                        <nav class="footer-navigation" role="navigation" aria-label="<?php esc_attr_e('Footer Navigation', 'mcqhome'); ?>">
                            <?php
                            wp_nav_menu([
                                'theme_location' => 'footer',
                                'menu_id' => 'footer-menu',
                                'container' => false,
                                'menu_class' => 'space-y-2',
                                'depth' => 1
                            ]);
                            ?>
                        </nav>
                    <?php else : ?>
                        <?php if (function_exists('mcqhome_default_footer_menu')) mcqhome_default_footer_menu(); ?>
                    <?php endif; ?>
                </div>

                <div class="footer-contact">
                    <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Contact Info', 'mcqhome'); ?></h3>
                    <div class="space-y-2 text-gray-300">
                        <p><?php esc_html_e('Email: info@mcqhome.com', 'mcqhome'); ?></p>
                        <p><?php esc_html_e('Phone: +1 (555) 123-4567', 'mcqhome'); ?></p>
                    </div>
                </div>
            </div>

            <div class="footer-bottom border-t border-gray-700 pt-6 flex flex-col md:flex-row justify-between items-center">
                <div class="site-info text-gray-300 text-sm">
                    <p class="copyright" itemprop="copyrightNotice">
                        &copy; <?php echo date('Y'); ?> 
                        <span itemprop="copyrightHolder" itemscope itemtype="https://schema.org/Organization">
                            <span itemprop="name"><?php bloginfo('name'); ?></span>
                        </span>. 
                        <?php esc_html_e('All rights reserved.', 'mcqhome'); ?>
                    </p>
                </div>
                
                <div class="footer-social mt-4 md:mt-0">
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white transition-colors" aria-label="<?php esc_attr_e('Facebook', 'mcqhome'); ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors" aria-label="<?php esc_attr_e('Twitter', 'mcqhome'); ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors" aria-label="<?php esc_attr_e('LinkedIn', 'mcqhome'); ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
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
        <nav class="breadcrumbs mb-4 px-4" role="navigation" aria-label="<?php esc_attr_e('Breadcrumb Navigation', 'mcqhome'); ?>" itemscope itemtype="https://schema.org/BreadcrumbList">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <?php foreach ($breadcrumbs as $index => $breadcrumb) : ?>
                    <?php if ($index > 0) : ?>
                        <li><span class="mx-2">/</span></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item<?php echo isset($breadcrumb['current']) ? ' current' : ''; ?>" 
                        itemprop="itemListElement" 
                        itemscope 
                        itemtype="https://schema.org/ListItem">
                        <?php if (isset($breadcrumb['current'])) : ?>
                            <span itemprop="name" aria-current="page" class="text-blue-600 font-medium"><?php echo esc_html($breadcrumb['title']); ?></span>
                        <?php else : ?>
                            <a href="<?php echo esc_url($breadcrumb['url']); ?>" itemprop="item" class="hover:text-blue-600">
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
        <a class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-blue-600 text-white p-2 z-50" href="#main-content"><?php esc_html_e('Skip to main content', 'mcqhome'); ?></a>
        <a class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-blue-600 text-white p-2 z-50" href="#site-navigation"><?php esc_html_e('Skip to navigation', 'mcqhome'); ?></a>
        <a class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-blue-600 text-white p-2 z-50" href="#colophon"><?php esc_html_e('Skip to footer', 'mcqhome'); ?></a>
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
    <ul class="flex space-x-6">
        <li><a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-700 hover:text-blue-600"><?php esc_html_e('Home', 'mcqhome'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/browse/')); ?>" class="text-gray-700 hover:text-blue-600"><?php esc_html_e('Browse', 'mcqhome'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/institutions/')); ?>" class="text-gray-700 hover:text-blue-600"><?php esc_html_e('Institutions', 'mcqhome'); ?></a></li>
        <?php if (is_user_logged_in()) : ?>
            <li><a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="text-gray-700 hover:text-blue-600"><?php esc_html_e('Dashboard', 'mcqhome'); ?></a></li>
        <?php else : ?>
            <li><a href="<?php echo esc_url(home_url('/register/')); ?>" class="text-gray-700 hover:text-blue-600"><?php esc_html_e('Register', 'mcqhome'); ?></a></li>
            <li><a href="<?php echo esc_url(wp_login_url()); ?>" class="text-gray-700 hover:text-blue-600"><?php esc_html_e('Login', 'mcqhome'); ?></a></li>
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