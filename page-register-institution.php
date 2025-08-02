<?php
/**
 * Template Name: Institution Registration
 * Description: Registration page specifically for institutions
 */

get_header(); ?>

<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-xl rounded-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Institution Registration</h1>
            <p class="text-gray-600">Register your institution and manage your team</p>
        </div>

        <div class="registration-form-container">
            <?php echo do_shortcode('[mcqhome_registration_institution]'); ?>
        </div>

        <div class="text-center mt-6">
            <p class="text-gray-600">
                Already have an account? 
                <a href="<?php echo wp_login_url(); ?>" class="text-blue-600 hover:underline font-medium">
                    Sign In
                </a>
            </p>
        </div>
    </div>
</div>

<?php get_footer(); ?>