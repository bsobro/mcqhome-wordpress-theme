<?php
// Absolutely minimal WordPress theme functions
// Nothing but the bare essentials

if (!defined('ABSPATH')) {
    exit;
}

// Basic theme setup
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
});

// Basic styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('theme-style', get_stylesheet_uri());
});