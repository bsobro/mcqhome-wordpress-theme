<?php
/**
 * Ultra-minimal MCQHome Theme functions for debugging
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Basic theme setup
function mcqhome_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'mcqhome_setup');

// Basic styles
function mcqhome_scripts() {
    wp_enqueue_style('mcqhome-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'mcqhome_scripts');

/**
 * Missing functions that templates need - minimal versions to prevent errors
 */

// Get post views (simple version)
function mcqhome_get_post_views($post_id) {
    $views = get_post_meta($post_id, '_post_views', true);
    return $views ? intval($views) : 0;
}

// Get MCQ success rate (simple version)
function mcqhome_get_mcq_success_rate($mcq_id) {
    return 0;
}

// Get related MCQs (simple version)
function mcqhome_get_related_mcqs($mcq_id, $limit = 4) {
    $args = array(
        'post_type' => 'mcq',
        'posts_per_page' => $limit,
        'post__not_in' => array($mcq_id),
        'post_status' => 'publish'
    );
    return new WP_Query($args);
}

// Get MCQ set question count
function mcqhome_get_mcq_set_question_count($set_id) {
    $mcq_ids = get_post_meta($set_id, '_mcq_set_questions', true);
    return is_array($mcq_ids) ? count($mcq_ids) : 0;
}

// Get MCQ set rating
function mcqhome_get_mcq_set_rating($set_id) {
    $rating = get_post_meta($set_id, '_average_rating', true);
    return $rating ? floatval($rating) : 0;
}

// Get user role
function mcqhome_get_user_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $user = get_userdata($user_id);
    return $user && !empty($user->roles) ? $user->roles[0] : 'subscriber';
}

// Get user primary role
function mcqhome_get_user_primary_role($user_id = null) {
    return mcqhome_get_user_role($user_id);
}

// Get user role display name
function mcqhome_get_user_role_display_name($role) {
    $role_names = array(
        'student' => __('Student', 'mcqhome'),
        'teacher' => __('Teacher', 'mcqhome'),
        'institution' => __('Institution', 'mcqhome'),
        'administrator' => __('Administrator', 'mcqhome'),
    );
    return isset($role_names[$role]) ? $role_names[$role] : ucfirst($role);
}

// Get institution stats
function mcqhome_get_institution_stats($institution_id) {
    return array(
        'teachers' => 0,
        'students' => 0,
        'mcq_sets' => 0,
        'total_questions' => 0
    );
}

// Get institution teachers
function mcqhome_get_institution_teachers($institution_id) {
    return array();
}

// Get institution MCQ sets
function mcqhome_get_institution_mcq_sets($institution_id, $limit = 6) {
    $args = array(
        'post_type' => 'mcq_set',
        'posts_per_page' => $limit,
        'post_status' => 'publish'
    );
    return new WP_Query($args);
}

// Get institution subjects
function mcqhome_get_institution_subjects($institution_id) {
    return array();
}

// Get user progress
function mcqhome_get_user_progress($user_id, $mcq_set_id) {
    return null;
}

// Get activity feed
function mcqhome_get_activity_feed($user_id, $limit = 10) {
    return array();
}

// Get unread notifications count
function mcqhome_get_unread_notifications_count($user_id) {
    return 0;
}

// Get user notifications
function mcqhome_get_user_notifications($user_id, $limit = 3) {
    return array();
}

// Get assessment results
function mcqhome_get_assessment_results($user_id, $mcq_set_id, $attempt_id) {
    return array();
}

// Get user performance analytics
function mcqhome_get_user_performance_analytics($user_id, $mcq_set_id) {
    return array();
}

// Get performance comparison
function mcqhome_get_performance_comparison($user_id, $mcq_set_id) {
    return array();
}