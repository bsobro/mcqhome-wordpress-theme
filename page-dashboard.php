<?php
/**
 * Template for Dashboard Page
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_role = mcqhome_get_user_primary_role($current_user->ID);

?>

<div class="dashboard-container min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
        
        <!-- Dashboard Header -->
        <div class="mb-6 lg:mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <div class="dashboard-header flex items-center justify-between">
                    <div class="flex-1">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">
                            <?php printf(__('Welcome back, %s!', 'mcqhome'), esc_html($current_user->display_name)); ?>
                        </h1>
                        <p class="text-gray-600 mt-1 text-sm sm:text-base">
                            <?php echo mcqhome_get_user_role_display_name($user_role); ?> Dashboard
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs sm:text-sm text-gray-500">
                            <?php echo date_i18n(get_option('date_format')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-specific Dashboard Content -->
        <?php
        switch ($user_role) {
            case 'student':
                mcqhome_render_student_dashboard($current_user->ID);
                break;
            case 'teacher':
                mcqhome_render_teacher_dashboard($current_user->ID);
                break;
            case 'institution':
                mcqhome_render_institution_dashboard($current_user->ID);
                break;
            case 'administrator':
                mcqhome_render_admin_dashboard($current_user->ID);
                break;
            default:
                echo '<div class="bg-white rounded-lg shadow-sm p-6">';
                echo '<p class="text-gray-600">' . __('Dashboard not available for your user role.', 'mcqhome') . '</p>';
                echo '</div>';
                break;
        }
        ?>

    </div>
</div>

<?php get_footer(); ?>