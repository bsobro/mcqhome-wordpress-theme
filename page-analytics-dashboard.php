<?php
/**
 * Template for displaying MCQ Set Analytics Dashboard
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Check if user has permission to view analytics
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get MCQ set ID
$mcq_set_id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;

if (!$mcq_set_id) {
    wp_redirect(home_url());
    exit;
}

// Get MCQ set data
$mcq_set = get_post($mcq_set_id);
if (!$mcq_set || $mcq_set->post_type !== 'mcq_set') {
    wp_redirect(home_url());
    exit;
}

// Check if user has permission to view analytics for this MCQ set
$can_view_analytics = false;
if (current_user_can('manage_options') || 
    $mcq_set->post_author == $user_id || 
    current_user_can('edit_post', $mcq_set_id)) {
    $can_view_analytics = true;
}

// Check if user is associated with the same institution
if (!$can_view_analytics) {
    $user_institution = get_user_meta($user_id, 'institution_id', true);
    $author_institution = get_user_meta($mcq_set->post_author, 'institution_id', true);
    
    if ($user_institution && $author_institution && $user_institution == $author_institution) {
        $can_view_analytics = true;
    }
}

if (!$can_view_analytics) {
    wp_redirect(home_url());
    exit;
}

// Get date range parameters
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : null;
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : null;

// Default to last 30 days if no date range specified
if (!$date_from && !$date_to) {
    $date_from = date('Y-m-d', strtotime('-30 days'));
    $date_to = date('Y-m-d');
}

// Generate comprehensive analytics
$analytics_data = mcqhome_generate_section_analytics_dashboard($mcq_set_id, $date_from, $date_to);

if (is_wp_error($analytics_data)) {
    wp_redirect(home_url());
    exit;
}

$overall_report = $analytics_data['overall_report'];
$section_analytics = $analytics_data['section_analytics'];
$top_performers = $analytics_data['top_performers'];
$difficulty_analysis = $analytics_data['difficulty_analysis'];
?>

<div class="analytics-dashboard-container bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-7xl">
        
        <!-- Dashboard Header -->
        <div class="dashboard-header bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php _e('Analytics Dashboard', 'mcqhome'); ?></h1>
                    <p class="text-gray-600"><?php echo esc_html($mcq_set->post_title); ?></p>
                </div>
                
                <!-- Date Range Filter -->
                <div class="mt-4 md:mt-0">
                    <form method="GET" class="flex flex-col md:flex-row gap-2">
                        <input type="hidden" name="set_id" value="<?php echo $mcq_set_id; ?>">
                        <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                            <?php _e('Filter', 'mcqhome'); ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="mt-4 text-sm text-gray-600">
                <?php printf(__('Showing data from %s to %s', 'mcqhome'), 
                    date('M j, Y', strtotime($date_from)), 
                    date('M j, Y', strtotime($date_to))
                ); ?>
            </div>
        </div>
        
        <!-- Overall Statistics -->
        <div class="overall-stats bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Overall Performance', 'mcqhome'); ?></h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="stat-card text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $overall_report['stats']->total_attempts; ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Total Attempts', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-card text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600"><?php echo $overall_report['stats']->unique_users; ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Unique Users', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-card text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600"><?php echo number_format($overall_report['stats']->avg_score, 1); ?>%</div>
                    <div class="text-sm text-gray-600"><?php _e('Average Score', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-card text-center p-4 bg-yellow-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600"><?php echo number_format($overall_report['pass_rate'], 1); ?>%</div>
                    <div class="text-sm text-gray-600"><?php _e('Pass Rate', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-card text-center p-4 bg-indigo-50 rounded-lg">
                    <div class="text-2xl font-bold text-indigo-600"><?php echo number_format($overall_report['stats']->max_score, 1); ?>%</div>
                    <div class="text-sm text-gray-600"><?php _e('Highest Score', 'mcqhome'); ?></div>
                </div>
                
                <div class="stat-card text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-600"><?php echo mcqhome_format_duration($overall_report['stats']->avg_time); ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Avg. Time', 'mcqhome'); ?></div>
                </div>
            </div>
        </div>
        
        <?php if ($section_analytics['has_sections']): ?>
        <!-- Section Performance Analytics -->
        <div class="section-analytics bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Section Performance Analysis', 'mcqhome'); ?></h2>
            
            <div class="space-y-6">
                <?php foreach ($section_analytics['sections'] as $section_id => $section_data): ?>
                <div class="section-analytics-card border border-gray-200 rounded-lg p-4">
                    <div class="section-header mb-4">
                        <h3 class="text-lg font-medium text-gray-800">
                            <?php echo esc_html($section_data['section_info']['name']); ?>
                        </h3>
                        <?php if (!empty($section_data['section_info']['description'])): ?>
                        <p class="text-gray-600 text-sm mt-1"><?php echo esc_html($section_data['section_info']['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-lg font-bold text-blue-600"><?php echo $section_data['question_count']; ?></div>
                            <div class="text-xs text-gray-600"><?php _e('Questions', 'mcqhome'); ?></div>
                        </div>
                        
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-lg font-bold text-green-600"><?php echo $section_data['overall_stats']->total_attempts; ?></div>
                            <div class="text-xs text-gray-600"><?php _e('Total Attempts', 'mcqhome'); ?></div>
                        </div>
                        
                        <div class="text-center p-3 bg-purple-50 rounded-lg">
                            <div class="text-lg font-bold text-purple-600"><?php echo number_format($section_data['overall_stats']->accuracy_percentage, 1); ?>%</div>
                            <div class="text-xs text-gray-600"><?php _e('Accuracy', 'mcqhome'); ?></div>
                        </div>
                        
                        <div class="text-center p-3 bg-yellow-50 rounded-lg">
                            <div class="text-lg font-bold text-yellow-600"><?php echo number_format($section_data['overall_stats']->avg_score_points, 1); ?></div>
                            <div class="text-xs text-gray-600"><?php _e('Avg. Points', 'mcqhome'); ?></div>
                        </div>
                    </div>
                    
                    <!-- Top Performers for this section -->
                    <?php if (!empty($top_performers[$section_id])): ?>
                    <div class="top-performers">
                        <h4 class="font-medium text-gray-700 mb-2"><?php _e('Top Performers', 'mcqhome'); ?></h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-gray-600"><?php _e('Student', 'mcqhome'); ?></th>
                                        <th class="px-3 py-2 text-center text-gray-600"><?php _e('Accuracy', 'mcqhome'); ?></th>
                                        <th class="px-3 py-2 text-center text-gray-600"><?php _e('Score', 'mcqhome'); ?></th>
                                        <th class="px-3 py-2 text-center text-gray-600"><?php _e('Date', 'mcqhome'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($top_performers[$section_id], 0, 5) as $performer): ?>
                                    <tr class="border-t border-gray-100">
                                        <td class="px-3 py-2"><?php echo esc_html($performer->display_name); ?></td>
                                        <td class="px-3 py-2 text-center"><?php echo number_format($performer->accuracy, 1); ?>%</td>
                                        <td class="px-3 py-2 text-center"><?php echo number_format($performer->section_score, 1); ?></td>
                                        <td class="px-3 py-2 text-center"><?php echo date('M j', strtotime($performer->completed_at)); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Difficulty Analysis by Section -->
        <div class="difficulty-analysis bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Question Difficulty Analysis', 'mcqhome'); ?></h2>
            
            <div class="space-y-6">
                <?php foreach ($difficulty_analysis as $section_id => $questions): ?>
                <?php 
                $section_info = null;
                foreach ($section_analytics['sections'] as $s_id => $s_data) {
                    if ($s_id === $section_id) {
                        $section_info = $s_data['section_info'];
                        break;
                    }
                }
                ?>
                <div class="section-difficulty">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">
                        <?php echo esc_html($section_info['name'] ?? __('Section', 'mcqhome')); ?>
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($questions as $question): ?>
                        <?php
                        $difficulty_class = '';
                        $difficulty_label = '';
                        if ($question->success_rate >= 80) {
                            $difficulty_class = 'bg-green-50 border-green-200 text-green-800';
                            $difficulty_label = __('Easy', 'mcqhome');
                        } elseif ($question->success_rate >= 60) {
                            $difficulty_class = 'bg-yellow-50 border-yellow-200 text-yellow-800';
                            $difficulty_label = __('Medium', 'mcqhome');
                        } else {
                            $difficulty_class = 'bg-red-50 border-red-200 text-red-800';
                            $difficulty_label = __('Hard', 'mcqhome');
                        }
                        ?>
                        <div class="question-difficulty-card border rounded-lg p-3 <?php echo $difficulty_class; ?>">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium"><?php printf(__('Question #%d', 'mcqhome'), $question->mcq_id); ?></span>
                                <span class="text-xs px-2 py-1 rounded-full bg-white bg-opacity-50"><?php echo $difficulty_label; ?></span>
                            </div>
                            <div class="text-sm">
                                <div><?php printf(__('Success Rate: %s%%', 'mcqhome'), number_format($question->success_rate, 1)); ?></div>
                                <div><?php printf(__('Attempts: %d', 'mcqhome'), $question->total_attempts); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Score Distribution -->
        <div class="score-distribution bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Score Distribution', 'mcqhome'); ?></h2>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <?php foreach ($overall_report['score_distribution'] as $grade): ?>
                <div class="grade-distribution text-center p-4 border border-gray-200 rounded-lg">
                    <div class="text-2xl font-bold text-gray-700"><?php echo $grade->count; ?></div>
                    <div class="text-sm text-gray-600"><?php echo esc_html($grade->grade); ?></div>
                    <div class="text-xs text-gray-500 mt-1">
                        <?php echo $overall_report['stats']->total_attempts > 0 ? number_format(($grade->count / $overall_report['stats']->total_attempts) * 100, 1) : 0; ?>%
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons bg-white rounded-lg shadow-md p-6 text-center">
            <div class="space-x-4">
                <a href="<?php echo get_permalink($mcq_set_id); ?>" 
                   class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors duration-200">
                    <?php _e('View MCQ Set', 'mcqhome'); ?>
                </a>
                
                <button type="button" 
                        onclick="window.print()" 
                        class="inline-block bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    <?php _e('Print Report', 'mcqhome'); ?>
                </button>
                
                <a href="<?php echo home_url('/dashboard/'); ?>" 
                   class="inline-block bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                    <?php _e('Back to Dashboard', 'mcqhome'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .action-buttons,
    .dashboard-header form {
        display: none !important;
    }
    
    .analytics-dashboard-container {
        background: white !important;
        padding: 0 !important;
    }
    
    .bg-white {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
    }
}
</style>

<?php
get_footer();
?>