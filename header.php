<?php
/**
 * The header for our theme
 *
 * @package MCQHome
 * @since 1.0.0
 */
?>
<!doctype html>
<html <?php language_attributes(); ?> <?php echo function_exists('mcqhome_get_html_attributes') ? mcqhome_get_html_attributes() : ''; ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php echo function_exists('mcqhome_get_body_attributes') ? mcqhome_get_body_attributes() : ''; ?>>
<?php wp_body_open(); ?>

<?php if (function_exists('mcqhome_semantic_skip_links')) mcqhome_semantic_skip_links(); ?>

<div id="page" class="site min-h-screen flex flex-col" itemscope itemtype="https://schema.org/WebPage">
    
    <?php if (function_exists('mcqhome_semantic_header')) : ?>
        <?php mcqhome_semantic_header(); ?>
    <?php else : ?>
        <header id="masthead" class="site-header bg-white shadow-sm border-b">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between py-4">
                    <div class="site-branding">
                        <?php if (has_custom_logo()) : ?>
                            <div class="site-logo">
                                <?php the_custom_logo(); ?>
                            </div>
                        <?php else : ?>
                            <h1 class="site-title text-2xl font-bold">
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-900 hover:text-blue-600">
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

                    <nav id="site-navigation" class="main-navigation hidden md:block">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'container'      => false,
                            'menu_class'     => 'flex space-x-6',
                            'fallback_cb'    => function_exists('mcqhome_default_menu') ? 'mcqhome_default_menu' : false,
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
                        'menu_id'        => 'mobile-menu',
                        'container'      => false,
                        'menu_class'     => 'flex flex-col space-y-2',
                        'fallback_cb'    => function_exists('mcqhome_default_menu') ? 'mcqhome_default_menu' : false,
                    ]);
                    ?>
                </div>
            </div>
        </header>
    <?php endif; ?>
    
    <div class="site-content flex-1">
        <?php if (function_exists('mcqhome_semantic_breadcrumbs')) mcqhome_semantic_breadcrumbs(); ?>