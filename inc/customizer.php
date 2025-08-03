<?php
/**
 * MCQHome Theme Customizer
 *
 * @package MCQHome
 * @since 1.0.0
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function mcqhome_customize_register($wp_customize) {
    $wp_customize->get_setting('blogname')->transport         = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport  = 'postMessage';
    $wp_customize->get_setting('header_textcolor')->transport = 'postMessage';

    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial(
            'blogname',
            [
                'selector'        => '.site-title a',
                'render_callback' => 'mcqhome_customize_partial_blogname',
            ]
        );
        $wp_customize->selective_refresh->add_partial(
            'blogdescription',
            [
                'selector'        => '.site-description',
                'render_callback' => 'mcqhome_customize_partial_blogdescription',
            ]
        );
    }

    // Add MCQHome Theme Options Panel
    $wp_customize->add_panel('mcqhome_theme_options', [
        'title'       => esc_html__('MCQHome Theme Options', 'mcqhome'),
        'description' => esc_html__('Customize your MCQHome theme settings.', 'mcqhome'),
        'priority'    => 30,
    ]);

    // Colors Section
    $wp_customize->add_section('mcqhome_colors', [
        'title'    => esc_html__('Colors', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 10,
    ]);

    // Primary Color
    $wp_customize->add_setting('mcqhome_primary_color', [
        'default'           => '#2563eb',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'mcqhome_primary_color', [
        'label'    => esc_html__('Primary Color', 'mcqhome'),
        'section'  => 'mcqhome_colors',
        'settings' => 'mcqhome_primary_color',
    ]));

    // Secondary Color
    $wp_customize->add_setting('mcqhome_secondary_color', [
        'default'           => '#64748b',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'mcqhome_secondary_color', [
        'label'    => esc_html__('Secondary Color', 'mcqhome'),
        'section'  => 'mcqhome_colors',
        'settings' => 'mcqhome_secondary_color',
    ]));

    // Typography Section
    $wp_customize->add_section('mcqhome_typography', [
        'title'    => esc_html__('Typography', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 20,
    ]);

    // Body Font
    $wp_customize->add_setting('mcqhome_body_font', [
        'default'           => 'Inter',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_body_font', [
        'label'   => esc_html__('Body Font', 'mcqhome'),
        'section' => 'mcqhome_typography',
        'type'    => 'select',
        'choices' => [
            'Inter'     => 'Inter',
            'Roboto'    => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Lato'      => 'Lato',
            'Poppins'   => 'Poppins',
        ],
    ]);

    // Heading Font
    $wp_customize->add_setting('mcqhome_heading_font', [
        'default'           => 'Inter',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_heading_font', [
        'label'   => esc_html__('Heading Font', 'mcqhome'),
        'section' => 'mcqhome_typography',
        'type'    => 'select',
        'choices' => [
            'Inter'     => 'Inter',
            'Roboto'    => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Lato'      => 'Lato',
            'Poppins'   => 'Poppins',
        ],
    ]);

    // Layout Section
    $wp_customize->add_section('mcqhome_layout', [
        'title'    => esc_html__('Layout', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 30,
    ]);

    // Container Width
    $wp_customize->add_setting('mcqhome_container_width', [
        'default'           => '1200',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_container_width', [
        'label'       => esc_html__('Container Width (px)', 'mcqhome'),
        'section'     => 'mcqhome_layout',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 960,
            'max'  => 1600,
            'step' => 10,
        ],
    ]);

    // Header Layout
    $wp_customize->add_setting('mcqhome_header_layout', [
        'default'           => 'default',
        'sanitize_callback' => 'mcqhome_sanitize_select',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('mcqhome_header_layout', [
        'label'   => esc_html__('Header Layout', 'mcqhome'),
        'section' => 'mcqhome_layout',
        'type'    => 'select',
        'choices' => [
            'default' => esc_html__('Default', 'mcqhome'),
            'centered' => esc_html__('Centered', 'mcqhome'),
            'minimal' => esc_html__('Minimal', 'mcqhome'),
        ],
    ]);

    // MCQ Theme Section
    $wp_customize->add_section('mcqhome_mcq_settings', [
        'title'    => esc_html__('MCQ Settings', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 40,
    ]);

    // Default MCQ Display Format
    $wp_customize->add_setting('mcqhome_default_mcq_format', [
        'default'           => 'next_next',
        'sanitize_callback' => 'mcqhome_sanitize_select',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('mcqhome_default_mcq_format', [
        'label'       => esc_html__('Default MCQ Display Format', 'mcqhome'),
        'description' => esc_html__('Choose the default format for displaying MCQ assessments.', 'mcqhome'),
        'section'     => 'mcqhome_mcq_settings',
        'type'        => 'select',
        'choices'     => [
            'next_next'   => esc_html__('Next-Next Format (One question per page)', 'mcqhome'),
            'single_page' => esc_html__('Single Page Format (All questions on one page)', 'mcqhome'),
        ],
    ]);

    // Show Question Numbers
    $wp_customize->add_setting('mcqhome_show_question_numbers', [
        'default'           => true,
        'sanitize_callback' => 'mcqhome_sanitize_checkbox',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_show_question_numbers', [
        'label'   => esc_html__('Show Question Numbers', 'mcqhome'),
        'section' => 'mcqhome_mcq_settings',
        'type'    => 'checkbox',
    ]);

    // Enable Auto-Save
    $wp_customize->add_setting('mcqhome_enable_autosave', [
        'default'           => true,
        'sanitize_callback' => 'mcqhome_sanitize_checkbox',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('mcqhome_enable_autosave', [
        'label'       => esc_html__('Enable Auto-Save', 'mcqhome'),
        'description' => esc_html__('Automatically save student progress during assessments.', 'mcqhome'),
        'section'     => 'mcqhome_mcq_settings',
        'type'        => 'checkbox',
    ]);

    // Dashboard Section
    $wp_customize->add_section('mcqhome_dashboard_settings', [
        'title'    => esc_html__('Dashboard Settings', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 50,
    ]);

    // Show Welcome Message
    $wp_customize->add_setting('mcqhome_show_welcome_message', [
        'default'           => true,
        'sanitize_callback' => 'mcqhome_sanitize_checkbox',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_show_welcome_message', [
        'label'   => esc_html__('Show Welcome Message', 'mcqhome'),
        'section' => 'mcqhome_dashboard_settings',
        'type'    => 'checkbox',
    ]);

    // Dashboard Layout
    $wp_customize->add_setting('mcqhome_dashboard_layout', [
        'default'           => 'grid',
        'sanitize_callback' => 'mcqhome_sanitize_select',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('mcqhome_dashboard_layout', [
        'label'   => esc_html__('Dashboard Layout', 'mcqhome'),
        'section' => 'mcqhome_dashboard_settings',
        'type'    => 'select',
        'choices' => [
            'grid' => esc_html__('Grid Layout', 'mcqhome'),
            'list' => esc_html__('List Layout', 'mcqhome'),
            'cards' => esc_html__('Card Layout', 'mcqhome'),
        ],
    ]);

    // Performance Section
    $wp_customize->add_section('mcqhome_performance', [
        'title'    => esc_html__('Performance', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 60,
    ]);

    // Enable Lazy Loading
    $wp_customize->add_setting('mcqhome_enable_lazy_loading', [
        'default'           => true,
        'sanitize_callback' => 'mcqhome_sanitize_checkbox',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('mcqhome_enable_lazy_loading', [
        'label'       => esc_html__('Enable Lazy Loading', 'mcqhome'),
        'description' => esc_html__('Load images and content as needed to improve page speed.', 'mcqhome'),
        'section'     => 'mcqhome_performance',
        'type'        => 'checkbox',
    ]);

    // Enable Caching
    $wp_customize->add_setting('mcqhome_enable_caching', [
        'default'           => true,
        'sanitize_callback' => 'mcqhome_sanitize_checkbox',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('mcqhome_enable_caching', [
        'label'       => esc_html__('Enable Theme Caching', 'mcqhome'),
        'description' => esc_html__('Cache theme assets and data for better performance.', 'mcqhome'),
        'section'     => 'mcqhome_performance',
        'type'        => 'checkbox',
    ]);

    // Social Media Section
    $wp_customize->add_section('mcqhome_social_media', [
        'title'    => esc_html__('Social Media', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 70,
    ]);

    // Facebook URL
    $wp_customize->add_setting('mcqhome_facebook_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_facebook_url', [
        'label'   => esc_html__('Facebook URL', 'mcqhome'),
        'section' => 'mcqhome_social_media',
        'type'    => 'url',
    ]);

    // Twitter URL
    $wp_customize->add_setting('mcqhome_twitter_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_twitter_url', [
        'label'   => esc_html__('Twitter URL', 'mcqhome'),
        'section' => 'mcqhome_social_media',
        'type'    => 'url',
    ]);

    // LinkedIn URL
    $wp_customize->add_setting('mcqhome_linkedin_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_linkedin_url', [
        'label'   => esc_html__('LinkedIn URL', 'mcqhome'),
        'section' => 'mcqhome_social_media',
        'type'    => 'url',
    ]);

    // Instagram URL
    $wp_customize->add_setting('mcqhome_instagram_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_instagram_url', [
        'label'   => esc_html__('Instagram URL', 'mcqhome'),
        'section' => 'mcqhome_social_media',
        'type'    => 'url',
    ]);

    // Footer Section
    $wp_customize->add_section('mcqhome_footer_settings', [
        'title'    => esc_html__('Footer Settings', 'mcqhome'),
        'panel'    => 'mcqhome_theme_options',
        'priority' => 80,
    ]);

    // Footer Copyright Text
    $wp_customize->add_setting('mcqhome_footer_copyright', [
        'default'           => sprintf(esc_html__('© %s MCQHome. All rights reserved.', 'mcqhome'), date('Y')),
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_footer_copyright', [
        'label'   => esc_html__('Footer Copyright Text', 'mcqhome'),
        'section' => 'mcqhome_footer_settings',
        'type'    => 'textarea',
    ]);

    // Show Footer Social Links
    $wp_customize->add_setting('mcqhome_show_footer_social', [
        'default'           => true,
        'sanitize_callback' => 'mcqhome_sanitize_checkbox',
        'transport'         => 'postMessage',
    ]);

    $wp_customize->add_control('mcqhome_show_footer_social', [
        'label'   => esc_html__('Show Social Links in Footer', 'mcqhome'),
        'section' => 'mcqhome_footer_settings',
        'type'    => 'checkbox',
    ]);
}
add_action('customize_register', 'mcqhome_customize_register');

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function mcqhome_customize_partial_blogname() {
    bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function mcqhome_customize_partial_blogdescription() {
    bloginfo('description');
}

/**
 * Sanitize select fields
 */
function mcqhome_sanitize_select($input, $setting) {
    $input = sanitize_key($input);
    $choices = $setting->manager->get_control($setting->id)->choices;
    return (array_key_exists($input, $choices) ? $input : $setting->default);
}

/**
 * Sanitize checkbox fields
 */
function mcqhome_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function mcqhome_customize_preview_js() {
    $version = defined('MCQHOME_VERSION') ? MCQHOME_VERSION : '1.0.0';
    wp_enqueue_script('mcqhome-customizer', get_template_directory_uri() . '/assets/js/customizer.js', ['customize-preview'], $version, true);
}
add_action('customize_preview_init', 'mcqhome_customize_preview_js');

/**
 * Output customizer styles
 */
function mcqhome_customizer_styles() {
    $primary_color = get_theme_mod('mcqhome_primary_color', '#2563eb');
    $secondary_color = get_theme_mod('mcqhome_secondary_color', '#64748b');
    $body_font = get_theme_mod('mcqhome_body_font', 'Inter');
    $heading_font = get_theme_mod('mcqhome_heading_font', 'Inter');
    $container_width = get_theme_mod('mcqhome_container_width', '1200');

    ?>
    <style type="text/css">
        :root {
            --mcqhome-primary-color: <?php echo esc_attr($primary_color); ?>;
            --mcqhome-secondary-color: <?php echo esc_attr($secondary_color); ?>;
            --mcqhome-body-font: '<?php echo esc_attr($body_font); ?>', sans-serif;
            --mcqhome-heading-font: '<?php echo esc_attr($heading_font); ?>', sans-serif;
            --mcqhome-container-width: <?php echo esc_attr($container_width); ?>px;
        }

        body {
            font-family: var(--mcqhome-body-font);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--mcqhome-heading-font);
        }

        .container, .max-w-7xl, .max-w-6xl, .max-w-5xl, .max-w-4xl {
            max-width: var(--mcqhome-container-width);
        }

        /* Primary Color Applications */
        .bg-blue-600, .bg-blue-500 {
            background-color: var(--mcqhome-primary-color) !important;
        }
        
        .text-blue-600, .text-blue-500 {
            color: var(--mcqhome-primary-color) !important;
        }

        .border-blue-600, .border-blue-500 {
            border-color: var(--mcqhome-primary-color) !important;
        }

        .hover\:bg-blue-700:hover, .hover\:bg-blue-600:hover {
            background-color: color-mix(in srgb, var(--mcqhome-primary-color) 90%, black) !important;
        }

        .hover\:text-blue-700:hover, .hover\:text-blue-600:hover {
            color: color-mix(in srgb, var(--mcqhome-primary-color) 90%, black) !important;
        }

        .focus\:ring-blue-500:focus {
            --tw-ring-color: var(--mcqhome-primary-color) !important;
        }

        .focus\:border-blue-500:focus {
            border-color: var(--mcqhome-primary-color) !important;
        }

        /* Secondary Color Applications */
        .text-gray-600, .text-gray-500 {
            color: var(--mcqhome-secondary-color) !important;
        }

        /* Role-specific colors */
        .role-card.selected {
            border-color: var(--mcqhome-primary-color) !important;
            background-color: color-mix(in srgb, var(--mcqhome-primary-color) 10%, white) !important;
        }

        .role-card.selected h3 {
            color: color-mix(in srgb, var(--mcqhome-primary-color) 90%, black) !important;
        }

        .role-card.selected p {
            color: color-mix(in srgb, var(--mcqhome-primary-color) 80%, black) !important;
        }

        /* MCQ Assessment Colors */
        .mcq-option.selected {
            background-color: color-mix(in srgb, var(--mcqhome-primary-color) 10%, white) !important;
            border-color: var(--mcqhome-primary-color) !important;
        }

        .mcq-option.correct {
            background-color: color-mix(in srgb, #10b981 10%, white) !important;
            border-color: #10b981 !important;
        }

        .mcq-option.incorrect {
            background-color: color-mix(in srgb, #ef4444 10%, white) !important;
            border-color: #ef4444 !important;
        }

        /* Dashboard customizations */
        <?php if (get_theme_mod('mcqhome_dashboard_layout', 'grid') === 'list'): ?>
        .dashboard-grid {
            display: flex !important;
            flex-direction: column !important;
        }
        <?php elseif (get_theme_mod('mcqhome_dashboard_layout', 'grid') === 'cards'): ?>
        .dashboard-grid .dashboard-item {
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        <?php endif; ?>

        /* Google Fonts Import */
        @import url('https://fonts.googleapis.com/css2?family=<?php echo esc_attr(str_replace(' ', '+', $body_font)); ?>:wght@300;400;500;600;700&family=<?php echo esc_attr(str_replace(' ', '+', $heading_font)); ?>:wght@400;500;600;700;800&display=swap');
    </style>
    <?php
}
add_action('wp_head', 'mcqhome_customizer_styles');
/**

 * Helper functions to get customizer values
 */

/**
 * Get primary color
 */
function mcqhome_get_primary_color() {
    return get_theme_mod('mcqhome_primary_color', '#2563eb');
}

/**
 * Get secondary color
 */
function mcqhome_get_secondary_color() {
    return get_theme_mod('mcqhome_secondary_color', '#64748b');
}

/**
 * Get body font
 */
function mcqhome_get_body_font() {
    return get_theme_mod('mcqhome_body_font', 'Inter');
}

/**
 * Get heading font
 */
function mcqhome_get_heading_font() {
    return get_theme_mod('mcqhome_heading_font', 'Inter');
}

/**
 * Get container width
 */
function mcqhome_get_container_width() {
    return get_theme_mod('mcqhome_container_width', '1200');
}

/**
 * Get default MCQ format
 */
function mcqhome_get_default_mcq_format() {
    return get_theme_mod('mcqhome_default_mcq_format', 'next_next');
}

/**
 * Check if question numbers should be shown
 */
function mcqhome_show_question_numbers() {
    return get_theme_mod('mcqhome_show_question_numbers', true);
}

/**
 * Check if auto-save is enabled
 */
function mcqhome_is_autosave_enabled() {
    return get_theme_mod('mcqhome_enable_autosave', true);
}

/**
 * Check if welcome message should be shown
 */
function mcqhome_show_welcome_message() {
    return get_theme_mod('mcqhome_show_welcome_message', true);
}

/**
 * Get dashboard layout
 */
function mcqhome_get_dashboard_layout() {
    return get_theme_mod('mcqhome_dashboard_layout', 'grid');
}

/**
 * Check if lazy loading is enabled
 */
function mcqhome_is_lazy_loading_enabled() {
    return get_theme_mod('mcqhome_enable_lazy_loading', true);
}

/**
 * Check if caching is enabled
 */
function mcqhome_is_caching_enabled() {
    return get_theme_mod('mcqhome_enable_caching', true);
}

/**
 * Get social media URLs
 */
function mcqhome_get_social_urls() {
    return array(
        'facebook' => get_theme_mod('mcqhome_facebook_url', ''),
        'twitter' => get_theme_mod('mcqhome_twitter_url', ''),
        'linkedin' => get_theme_mod('mcqhome_linkedin_url', ''),
        'instagram' => get_theme_mod('mcqhome_instagram_url', ''),
    );
}

/**
 * Get footer copyright text
 */
function mcqhome_get_footer_copyright() {
    return get_theme_mod('mcqhome_footer_copyright', sprintf(esc_html__('© %s MCQHome. All rights reserved.', 'mcqhome'), date('Y')));
}

/**
 * Check if footer social links should be shown
 */
function mcqhome_show_footer_social() {
    return get_theme_mod('mcqhome_show_footer_social', true);
}

/**
 * Get header layout
 */
function mcqhome_get_header_layout() {
    return get_theme_mod('mcqhome_header_layout', 'default');
}

/**
 * Output social media links HTML
 */
function mcqhome_social_links($classes = '') {
    $social_urls = mcqhome_get_social_urls();
    $output = '';
    
    if (array_filter($social_urls)) {
        $output .= '<div class="social-links ' . esc_attr($classes) . '">';
        
        foreach ($social_urls as $platform => $url) {
            if (!empty($url)) {
                $output .= sprintf(
                    '<a href="%s" class="social-%s" target="_blank" rel="noopener noreferrer" aria-label="%s">',
                    esc_url($url),
                    esc_attr($platform),
                    sprintf(esc_attr__('Follow us on %s', 'mcqhome'), ucfirst($platform))
                );
                
                // Add platform-specific icons
                switch ($platform) {
                    case 'facebook':
                        $output .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
                        break;
                    case 'twitter':
                        $output .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>';
                        break;
                    case 'linkedin':
                        $output .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
                        break;
                    case 'instagram':
                        $output .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987 6.62 0 11.987-5.367 11.987-11.987C24.014 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.596-3.205-1.533l1.714-1.714c.39.586.996.977 1.491.977.595 0 1.133-.391 1.133-.977 0-.586-.538-.977-1.133-.977-.495 0-1.101.391-1.491.977L5.244 12.527c.757-.937 1.908-1.533 3.205-1.533 2.344 0 4.244 1.9 4.244 4.244s-1.9 4.244-4.244 4.244z"/></svg>';
                        break;
                }
                
                $output .= '</a>';
            }
        }
        
        $output .= '</div>';
    }
    
    return $output;
}

/**
 * Enqueue customizer control scripts
 */
function mcqhome_customizer_controls_scripts() {
    wp_enqueue_script(
        'mcqhome-customizer-controls',
        get_template_directory_uri() . '/assets/js/customizer-controls.js',
        array('customize-controls'),
        MCQHOME_VERSION,
        true
    );
}
add_action('customize_controls_enqueue_scripts', 'mcqhome_customizer_controls_scripts');