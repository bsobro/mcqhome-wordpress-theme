<?php
// Absolute bare minimum - just to get theme activated
if (!defined('ABSPATH')) exit;
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
});