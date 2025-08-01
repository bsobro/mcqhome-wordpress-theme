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
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function mcqhome_customize_preview_js() {
    wp_enqueue_script('mcqhome-customizer', get_template_directory_uri() . '/assets/js/customizer.js', ['customize-preview'], MCQHOME_VERSION, true);
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

        .container {
            max-width: var(--mcqhome-container-width);
        }

        .bg-blue-600, .text-blue-600 {
            background-color: var(--mcqhome-primary-color) !important;
            color: var(--mcqhome-primary-color) !important;
        }

        .hover\:bg-blue-700:hover {
            background-color: color-mix(in srgb, var(--mcqhome-primary-color) 90%, black) !important;
        }
    </style>
    <?php
}
add_action('wp_head', 'mcqhome_customizer_styles');