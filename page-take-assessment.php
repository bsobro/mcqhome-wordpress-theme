<?php
/**
 * Template for taking MCQ assessments
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Enqueue accessibility compliance assets
wp_enqueue_script('mcqhome-accessibility', get_template_directory_uri() . '/assets/js/accessibility-compliance.js', array('jquery'), '1.0.0', true);
wp_enqueue_style('mcqhome-accessibility', get_template_directory_uri() . '/assets/css/accessibility-compliance.css', array(), '1.0.0');

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Get MCQ set ID from URL parameter
$mcq_set_id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;

if (!$mcq_set_id) {
    wp_redirect(home_url());
    exit;
}

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Use the new assessment controller
$controller = mcqhome_get_assessment_controller();
$assessment_data = $controller->start_assessment($mcq_set_id, $user_id);

if (is_wp_error($assessment_data)) {
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold text-red-800 mb-2"><?php _e('Assessment Error', 'mcqhome'); ?></h2>
            <p class="text-red-700 mb-4"><?php echo esc_html($assessment_data->get_error_message()); ?></p>
            <a href="<?php echo home_url(); ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                <?php _e('Go Home', 'mcqhome'); ?>
            </a>
        </div>
    </div>
    <?php
    get_footer();
    exit;
}

// Extract assessment data
$config = $assessment_data['config'];
$progress = $assessment_data['progress'];
$session_data = $assessment_data['session_data'];

// Get MCQ set post for header info
$mcq_set = get_post($mcq_set_id);
?>

<div class="mcq-assessment-container" role="main" aria-label="Assessment Interface">
    <!-- Skip to content link -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="assessment-header" role="banner" aria-label="Assessment Information">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="assessment-info">
                    <h1 class="text-2xl font-bold text-gray-800" id="assessment-title"><?php echo esc_html($mcq_set->post_title); ?></h1>
                    <div class="assessment-meta text-sm text-gray-600 mt-1">
                        <span class="total-questions"><?php printf(__('Total Questions: %d', 'mcqhome'), $config['total_questions']); ?></span>
                        <span class="separator mx-2">•</span>
                        <span class="total-marks"><?php printf(__('Pass Marks: %d', 'mcqhome'), $config['pass_marks']); ?></span>
                        <?php if ($config['time_limit']): ?>
                        <span class="separator mx-2">•</span>
                        <span class="time-limit"><?php printf(__('Time Limit: %d minutes', 'mcqhome'), $config['time_limit']); ?></span>
                        <?php endif; ?>
                        <span class="separator mx-2">•</span>
                        <span class="display-format"><?php echo $config['display_format'] === 'single_page' ? __('Single Page Format', 'mcqhome') : __('Next-Next Format', 'mcqhome'); ?></span>
                        <?php if ($config['sections_enabled'] && !empty($config['sections'])): ?>
                        <span class="separator mx-2">•</span>
                        <span class="sections-count"><?php printf(__('%d Sections', 'mcqhome'), count($config['sections'])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="assessment-controls">
                    <?php if ($config['time_limit']): ?>
                    <div class="timer-display bg-red-100 text-red-800 px-4 py-2 rounded-lg font-mono text-lg" 
                         role="timer" 
                         aria-live="polite" 
                         aria-label="Time remaining"
                         aria-atomic="true">
                        <span id="timer-display" aria-label="Timer display">--:--</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="assessment-body">
        <div class="container mx-auto px-4 py-6">
            <main id="main-content" role="region" aria-labelledby="assessment-title" tabindex="-1">
                <?php echo $controller->render_assessment($config, $progress); ?>
            </main>
        </div>
    </div>
</div>

<!-- Assessment Data -->
<script type="text/javascript">
window.assessmentData = {
    setId: <?php echo $config['mcq_set_id']; ?>,
    userId: <?php echo $user_id; ?>,
    displayFormat: '<?php echo $config['display_format']; ?>',
    totalQuestions: <?php echo $config['total_questions']; ?>,
    currentQuestion: <?php echo $progress['current_question']; ?>,
    timeLimit: <?php echo $config['time_limit'] ? $config['time_limit'] * 60 : 0; ?>, // Convert to seconds
    answersData: <?php echo json_encode($progress['answers']); ?>,
    sectionsEnabled: <?php echo $config['sections_enabled'] ? 'true' : 'false'; ?>,
    sections: <?php echo json_encode($config['sections']); ?>,
    ajaxUrl: '<?php echo $session_data['ajax_url']; ?>',
    nonce: '<?php echo $session_data['nonce']; ?>',
    startTime: <?php echo $progress['start_time']; ?>
};
</script>

<?php
get_footer();
?>