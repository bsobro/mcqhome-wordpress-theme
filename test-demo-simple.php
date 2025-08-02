<?php
/**
 * Simple test for demo content generation
 */

// Load WordPress
require_once('../../../wp-config.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo '<h1>Simple Demo Content Test</h1>';

// Test creating a simple MCQ Academy institution
try {
    echo '<h2>Testing MCQ Academy Creation</h2>';
    
    // Check if institution post type exists
    if (!post_type_exists('institution')) {
        echo '<p>❌ Institution post type not registered</p>';
        exit;
    }
    
    // Try to create MCQ Academy
    $mcq_academy_id = wp_insert_post([
        'post_title' => 'MCQ Academy Test',
        'post_content' => 'Test institution for debugging',
        'post_status' => 'publish',
        'post_type' => 'institution',
        'post_author' => 1
    ]);
    
    if (is_wp_error($mcq_academy_id)) {
        echo '<p>❌ Failed to create institution: ' . $mcq_academy_id->get_error_message() . '</p>';
    } else {
        echo '<p>✅ Successfully created test institution with ID: ' . $mcq_academy_id . '</p>';
        
        // Clean up
        wp_delete_post($mcq_academy_id, true);
        echo '<p>✅ Cleaned up test institution</p>';
    }
    
} catch (Exception $e) {
    echo '<p>❌ Exception: ' . $e->getMessage() . '</p>';
}

// Test creating a simple subject
try {
    echo '<h2>Testing Subject Creation</h2>';
    
    // Check if taxonomy exists
    if (!taxonomy_exists('mcq_subject')) {
        echo '<p>❌ mcq_subject taxonomy not registered</p>';
    } else {
        // Try to create a test subject
        $subject_term = wp_insert_term('Test Subject', 'mcq_subject', [
            'description' => 'Test subject for debugging'
        ]);
        
        if (is_wp_error($subject_term)) {
            echo '<p>❌ Failed to create subject: ' . $subject_term->get_error_message() . '</p>';
        } else {
            echo '<p>✅ Successfully created test subject with ID: ' . $subject_term['term_id'] . '</p>';
            
            // Clean up
            wp_delete_term($subject_term['term_id'], 'mcq_subject');
            echo '<p>✅ Cleaned up test subject</p>';
        }
    }
    
} catch (Exception $e) {
    echo '<p>❌ Exception: ' . $e->getMessage() . '</p>';
}

// Test creating a simple user
try {
    echo '<h2>Testing User Creation</h2>';
    
    // Check if teacher role exists
    if (!get_role('teacher')) {
        echo '<p>❌ Teacher role not registered</p>';
    } else {
        // Try to create a test user
        $user_id = wp_create_user(
            'test_teacher_' . time(),
            wp_generate_password(12, false),
            'test' . time() . '@example.com'
        );
        
        if (is_wp_error($user_id)) {
            echo '<p>❌ Failed to create user: ' . $user_id->get_error_message() . '</p>';
        } else {
            echo '<p>✅ Successfully created test user with ID: ' . $user_id . '</p>';
            
            // Set role
            $user = new WP_User($user_id);
            $user->set_role('teacher');
            echo '<p>✅ Set user role to teacher</p>';
            
            // Clean up
            wp_delete_user($user_id);
            echo '<p>✅ Cleaned up test user</p>';
        }
    }
    
} catch (Exception $e) {
    echo '<p>❌ Exception: ' . $e->getMessage() . '</p>';
}

echo '<p><a href="' . admin_url('themes.php?page=mcqhome-demo-content') . '">← Back to Demo Content Page</a></p>';
?>