<?php
/**
 * Template Name: Registration Page
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="registration-page min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <?php
            // Display registration form
            echo do_shortcode('[mcqhome_registration]');
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>