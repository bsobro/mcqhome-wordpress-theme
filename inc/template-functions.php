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