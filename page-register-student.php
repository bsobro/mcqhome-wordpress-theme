<?php
/**
 * Template Name: Student Registration
 * Description: Registration page specifically for students
 */

get_header(); ?>

<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-xl rounded-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Student Registration</h1>
            <p class="text-gray-600">Start your learning journey with MCQHome</p>
        </div>

        <div class="registration-form-container">
            <?php echo do_shortcode('[mcqhome_registration_student]'); ?>
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