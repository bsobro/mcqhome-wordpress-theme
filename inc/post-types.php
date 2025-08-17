<?php
/**
 * Custom Post Types for MCQHome Theme
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register MCQ custom post type
 */
function mcqhome_register_mcq_post_type() {
    $labels = [
        'name'                  => _x('MCQs', 'Post type general name', 'mcqhome'),
        'singular_name'         => _x('MCQ', 'Post type singular name', 'mcqhome'),
        'menu_name'             => _x('MCQs', 'Admin Menu text', 'mcqhome'),
        'name_admin_bar'        => _x('MCQ', 'Add New on Toolbar', 'mcqhome'),
        'add_new'               => __('Add New', 'mcqhome'),
        'add_new_item'          => __('Add New MCQ', 'mcqhome'),
        'new_item'              => __('New MCQ', 'mcqhome'),
        'edit_item'             => __('Edit MCQ', 'mcqhome'),
        'view_item'             => __('View MCQ', 'mcqhome'),
        'all_items'             => __('All MCQs', 'mcqhome'),
        'search_items'          => __('Search MCQs', 'mcqhome'),
        'parent_item_colon'     => __('Parent MCQs:', 'mcqhome'),
        'not_found'             => __('No MCQs found.', 'mcqhome'),
        'not_found_in_trash'    => __('No MCQs found in Trash.', 'mcqhome'),
        'featured_image'        => _x('MCQ Featured Image', 'Overrides the "Featured Image" phrase', 'mcqhome'),
        'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'mcqhome'),
        'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'mcqhome'),
        'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'mcqhome'),
        'archives'              => _x('MCQ archives', 'The post type archive label', 'mcqhome'),
        'insert_into_item'      => _x('Insert into MCQ', 'Overrides the "Insert into post" phrase', 'mcqhome'),
        'uploaded_to_this_item' => _x('Uploaded to this MCQ', 'Overrides the "Uploaded to this post" phrase', 'mcqhome'),
        'filter_items_list'     => _x('Filter MCQs list', 'Screen reader text for the filter links', 'mcqhome'),
        'items_list_navigation' => _x('MCQs list navigation', 'Screen reader text for the pagination', 'mcqhome'),
        'items_list'            => _x('MCQs list', 'Screen reader text for the items list', 'mcqhome'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => false,  // Make non-public to exclude from frontend queries
        'publicly_queryable' => true,   // Still allow direct URL access for redirects
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'mcq'],
        'capability_type'    => 'post',
        'has_archive'        => false,  // No archive page for individual MCQs
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-editor-help',
        'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'       => true,
        'rest_base'          => 'mcqs',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'exclude_from_search' => true,  // Explicitly exclude from search
        'show_in_nav_menus'  => false,  // Don't show in navigation menus
        'can_export'         => true,   // Allow export for admin
    ];

    register_post_type('mcq', $args);
}
add_action('init', 'mcqhome_register_mcq_post_type');

/**
 * Register MCQ Set custom post type
 */
function mcqhome_register_mcq_set_post_type() {
    $labels = [
        'name'                  => _x('MCQ Sets', 'Post type general name', 'mcqhome'),
        'singular_name'         => _x('MCQ Set', 'Post type singular name', 'mcqhome'),
        'menu_name'             => _x('MCQ Sets', 'Admin Menu text', 'mcqhome'),
        'name_admin_bar'        => _x('MCQ Set', 'Add New on Toolbar', 'mcqhome'),
        'add_new'               => __('Add New', 'mcqhome'),
        'add_new_item'          => __('Add New MCQ Set', 'mcqhome'),
        'new_item'              => __('New MCQ Set', 'mcqhome'),
        'edit_item'             => __('Edit MCQ Set', 'mcqhome'),
        'view_item'             => __('View MCQ Set', 'mcqhome'),
        'all_items'             => __('All MCQ Sets', 'mcqhome'),
        'search_items'          => __('Search MCQ Sets', 'mcqhome'),
        'parent_item_colon'     => __('Parent MCQ Sets:', 'mcqhome'),
        'not_found'             => __('No MCQ Sets found.', 'mcqhome'),
        'not_found_in_trash'    => __('No MCQ Sets found in Trash.', 'mcqhome'),
        'featured_image'        => _x('MCQ Set Thumbnail', 'Overrides the "Featured Image" phrase', 'mcqhome'),
        'set_featured_image'    => _x('Set thumbnail', 'Overrides the "Set featured image" phrase', 'mcqhome'),
        'remove_featured_image' => _x('Remove thumbnail', 'Overrides the "Remove featured image" phrase', 'mcqhome'),
        'use_featured_image'    => _x('Use as thumbnail', 'Overrides the "Use as featured image" phrase', 'mcqhome'),
        'archives'              => _x('MCQ Set archives', 'The post type archive label', 'mcqhome'),
        'insert_into_item'      => _x('Insert into MCQ Set', 'Overrides the "Insert into post" phrase', 'mcqhome'),
        'uploaded_to_this_item' => _x('Uploaded to this MCQ Set', 'Overrides the "Uploaded to this post" phrase', 'mcqhome'),
        'filter_items_list'     => _x('Filter MCQ Sets list', 'Screen reader text for the filter links', 'mcqhome'),
        'items_list_navigation' => _x('MCQ Sets list navigation', 'Screen reader text for the pagination', 'mcqhome'),
        'items_list'            => _x('MCQ Sets list', 'Screen reader text for the items list', 'mcqhome'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'mcq-set'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 21,
        'menu_icon'          => 'dashicons-list-view',
        'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'       => true,
        'rest_base'          => 'mcq-sets',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    ];

    register_post_type('mcq_set', $args);
}
add_action('init', 'mcqhome_register_mcq_set_post_type');

/**
 * Exclude MCQ posts from public queries and search results
 * 
 * This function implements the core requirement of Task 12: MCQ Post Type Query Exclusion
 * It ensures MCQ posts are hidden from all public frontend queries while remaining
 * accessible in the admin area.
 */
function mcqhome_exclude_mcq_from_public($query) {
    // Only modify main queries on frontend - preserve admin access
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Get current post types
    $post_types = $query->get('post_type');
    
    // Handle different query types where MCQs should be excluded
    if (is_home() || is_search() || is_archive() || is_feed()) {
        if (empty($post_types)) {
            // Default query - set explicit post types excluding MCQ
            $post_types = ['post', 'mcq_set', 'institution'];
        } else {
            // Remove MCQ from existing post types
            if (is_array($post_types)) {
                $post_types = array_diff($post_types, ['mcq']);
                // Ensure we have at least one post type
                if (empty($post_types)) {
                    $post_types = ['mcq_set'];
                }
            } elseif ($post_types === 'mcq') {
                // If only MCQ was requested, show MCQ sets instead
                $post_types = ['mcq_set'];
            }
        }
        $query->set('post_type', $post_types);
    }
    
    // Also exclude from category and tag archives
    if (is_category() || is_tag() || is_tax()) {
        $current_post_types = $query->get('post_type');
        if (empty($current_post_types)) {
            // Set default post types for taxonomy archives
            $query->set('post_type', ['post', 'mcq_set', 'institution']);
        } elseif (is_array($current_post_types) && in_array('mcq', $current_post_types)) {
            // Remove MCQ from taxonomy queries
            $filtered_types = array_diff($current_post_types, ['mcq']);
            if (!empty($filtered_types)) {
                $query->set('post_type', $filtered_types);
            }
        }
    }
}
add_action('pre_get_posts', 'mcqhome_exclude_mcq_from_public');

/**
 * Note: MCQ sitemap exclusion is handled by the Legacy Redirect System
 * in inc/legacy-redirect-system.php for comprehensive SEO management
 */

/**
 * Exclude MCQ posts from REST API public queries
 * 
 * This prevents MCQ posts from being accessible via REST API endpoints
 * unless specifically requested by authenticated users
 */
function mcqhome_exclude_mcq_from_rest($args, $request) {
    // Only apply to public REST requests
    if (!is_user_logged_in() && !current_user_can('edit_posts')) {
        if (isset($args['post_type']) && $args['post_type'] === 'mcq') {
            // Return empty results for MCQ queries from non-authenticated users
            $args['post__in'] = [0]; // No posts will match this
        }
    }
    return $args;
}
add_filter('rest_post_query', 'mcqhome_exclude_mcq_from_rest', 10, 2);

/**
 * Exclude MCQ posts from RSS feeds
 * 
 * Ensures MCQ posts don't appear in RSS/Atom feeds
 */
function mcqhome_exclude_mcq_from_feeds($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_feed()) {
        $post_types = $query->get('post_type');
        if (empty($post_types)) {
            $query->set('post_type', ['post', 'mcq_set', 'institution']);
        } elseif (is_array($post_types)) {
            $post_types = array_diff($post_types, ['mcq']);
            if (!empty($post_types)) {
                $query->set('post_type', $post_types);
            }
        }
    }
}
add_action('pre_get_posts', 'mcqhome_exclude_mcq_from_feeds', 5); // Higher priority

/**
 * Filter MCQ posts from get_posts() calls
 * 
 * This catches any get_posts() calls that might bypass the main query filters
 */
function mcqhome_filter_get_posts($posts, $parsed_args) {
    // Only filter on frontend and if no specific post type is requested
    if (!is_admin() && (!isset($parsed_args['post_type']) || empty($parsed_args['post_type']))) {
        // Filter out any MCQ posts that might have slipped through
        $posts = array_filter($posts, function($post) {
            return $post->post_type !== 'mcq';
        });
    }
    return $posts;
}
add_filter('get_posts', 'mcqhome_filter_get_posts', 10, 2);

/**
 * Exclude MCQ posts from search widget and search forms
 * 
 * This ensures search widgets don't return MCQ posts
 */
function mcqhome_exclude_mcq_from_search_widget($query) {
    if (!is_admin() && $query->is_search()) {
        $post_types = $query->get('post_type');
        if (empty($post_types)) {
            $query->set('post_type', ['post', 'mcq_set', 'institution']);
        }
    }
}
add_action('pre_get_posts', 'mcqhome_exclude_mcq_from_search_widget', 15);

/**
 * Generate MCQ title from question text
 */
function mcqhome_generate_mcq_title($question_text) {
    if (empty($question_text)) {
        return __('Untitled Question', 'mcqhome');
    }
    
    // Strip HTML tags and get first 10 words
    $clean_text = wp_strip_all_tags($question_text);
    $title = wp_trim_words($clean_text, 10, '...');
    
    return !empty($title) ? $title : __('Untitled Question', 'mcqhome');
}

/**
 * Find MCQ set that contains a specific MCQ
 */
function mcqhome_find_mcq_set_for_question($mcq_id) {
    global $wpdb;
    
    // Search in MCQ set meta for this question ID
    $mcq_set_id = $wpdb->get_var($wpdb->prepare("
        SELECT post_id 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_mcq_set_questions' 
        AND meta_value LIKE %s
    ", '%' . $wpdb->esc_like('"' . $mcq_id . '"') . '%'));
    
    if ($mcq_set_id) {
        // Verify the MCQ is actually in the set
        $questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
        if (is_array($questions) && in_array($mcq_id, $questions)) {
            return $mcq_set_id;
        }
    }
    
    // Also check in the new questions_order format
    $mcq_set_id = $wpdb->get_var($wpdb->prepare("
        SELECT post_id 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_mcq_set_questions_order' 
        AND meta_value LIKE %s
    ", '%' . $wpdb->esc_like('"mcq_id":' . $mcq_id) . '%'));
    
    if ($mcq_set_id) {
        // Verify the MCQ is actually in the set
        $questions_order = json_decode(get_post_meta($mcq_set_id, '_mcq_set_questions_order', true), true);
        if (is_array($questions_order) && isset($questions_order['questions'])) {
            foreach ($questions_order['questions'] as $question_data) {
                if (isset($question_data['mcq_id']) && $question_data['mcq_id'] == $mcq_id) {
                    return $mcq_set_id;
                }
            }
        }
    }
    
    return null;
}

// Legacy redirect handling is now managed by inc/legacy-redirect-system.php

/**
 * Auto-generate MCQ title when saving
 */
function mcqhome_auto_generate_mcq_title($post_id, $post, $update) {
    // Only process MCQ posts
    if ($post->post_type !== 'mcq') {
        return;
    }
    
    // Skip if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Skip if user doesn't have permission
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Get question text
    $question_text = get_post_meta($post_id, '_mcq_question_text', true);
    
    // If no question text but has existing title, use title as question text
    if (empty($question_text) && !empty($post->post_title)) {
        update_post_meta($post_id, '_mcq_question_text', $post->post_title);
        $question_text = $post->post_title;
    }
    
    // Generate new title from question text
    if (!empty($question_text)) {
        $new_title = mcqhome_generate_mcq_title($question_text);
        
        // Only update if title is different to avoid infinite loops
        if ($new_title !== $post->post_title) {
            // Remove this hook temporarily to avoid infinite loop
            remove_action('save_post', 'mcqhome_auto_generate_mcq_title', 10, 3);
            
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $new_title
            ]);
            
            // Re-add the hook
            add_action('save_post', 'mcqhome_auto_generate_mcq_title', 10, 3);
        }
    }
}
add_action('save_post', 'mcqhome_auto_generate_mcq_title', 10, 3);

/**
 * Register MCQ taxonomies
 */
function mcqhome_register_mcq_taxonomies() {
    // Subject taxonomy
    $subject_labels = [
        'name'              => _x('Subjects', 'taxonomy general name', 'mcqhome'),
        'singular_name'     => _x('Subject', 'taxonomy singular name', 'mcqhome'),
        'search_items'      => __('Search Subjects', 'mcqhome'),
        'all_items'         => __('All Subjects', 'mcqhome'),
        'parent_item'       => __('Parent Subject', 'mcqhome'),
        'parent_item_colon' => __('Parent Subject:', 'mcqhome'),
        'edit_item'         => __('Edit Subject', 'mcqhome'),
        'update_item'       => __('Update Subject', 'mcqhome'),
        'add_new_item'      => __('Add New Subject', 'mcqhome'),
        'new_item_name'     => __('New Subject Name', 'mcqhome'),
        'menu_name'         => __('Subjects', 'mcqhome'),
    ];

    $subject_args = [
        'hierarchical'      => true,
        'labels'            => $subject_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'subject'],
        'show_in_rest'      => true,
        'rest_base'         => 'subjects',
    ];

    register_taxonomy('mcq_subject', ['mcq', 'mcq_set'], $subject_args);

    // Topic taxonomy
    $topic_labels = [
        'name'              => _x('Topics', 'taxonomy general name', 'mcqhome'),
        'singular_name'     => _x('Topic', 'taxonomy singular name', 'mcqhome'),
        'search_items'      => __('Search Topics', 'mcqhome'),
        'all_items'         => __('All Topics', 'mcqhome'),
        'parent_item'       => __('Parent Topic', 'mcqhome'),
        'parent_item_colon' => __('Parent Topic:', 'mcqhome'),
        'edit_item'         => __('Edit Topic', 'mcqhome'),
        'update_item'       => __('Update Topic', 'mcqhome'),
        'add_new_item'      => __('Add New Topic', 'mcqhome'),
        'new_item_name'     => __('New Topic Name', 'mcqhome'),
        'menu_name'         => __('Topics', 'mcqhome'),
    ];

    $topic_args = [
        'hierarchical'      => true,
        'labels'            => $topic_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'topic'],
        'show_in_rest'      => true,
        'rest_base'         => 'topics',
    ];

    register_taxonomy('mcq_topic', ['mcq', 'mcq_set'], $topic_args);

    // Difficulty taxonomy
    $difficulty_labels = [
        'name'              => _x('Difficulty Levels', 'taxonomy general name', 'mcqhome'),
        'singular_name'     => _x('Difficulty Level', 'taxonomy singular name', 'mcqhome'),
        'search_items'      => __('Search Difficulty Levels', 'mcqhome'),
        'all_items'         => __('All Difficulty Levels', 'mcqhome'),
        'edit_item'         => __('Edit Difficulty Level', 'mcqhome'),
        'update_item'       => __('Update Difficulty Level', 'mcqhome'),
        'add_new_item'      => __('Add New Difficulty Level', 'mcqhome'),
        'new_item_name'     => __('New Difficulty Level Name', 'mcqhome'),
        'menu_name'         => __('Difficulty', 'mcqhome'),
    ];

    $difficulty_args = [
        'hierarchical'      => false,
        'labels'            => $difficulty_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'difficulty'],
        'show_in_rest'      => true,
        'rest_base'         => 'difficulty',
    ];

    register_taxonomy('mcq_difficulty', ['mcq', 'mcq_set'], $difficulty_args);
}
add_action('init', 'mcqhome_register_mcq_taxonomies');

/**
 * Add MCQ meta boxes
 */
function mcqhome_add_mcq_meta_boxes() {
    add_meta_box(
        'mcq_question_details',
        __('MCQ Question Details', 'mcqhome'),
        'mcqhome_mcq_question_details_callback',
        'mcq',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_answer_options',
        __('Answer Options', 'mcqhome'),
        'mcqhome_mcq_answer_options_callback',
        'mcq',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_explanation',
        __('Answer Explanation', 'mcqhome'),
        'mcqhome_mcq_explanation_callback',
        'mcq',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_categorization',
        __('Categorization & Metadata', 'mcqhome'),
        'mcqhome_mcq_categorization_callback',
        'mcq',
        'side',
        'high'
    );

    add_meta_box(
        'mcq_settings',
        __('MCQ Settings', 'mcqhome'),
        'mcqhome_mcq_settings_callback',
        'mcq',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'mcqhome_add_mcq_meta_boxes');

/**
 * Add MCQ Set meta boxes
 */
function mcqhome_add_mcq_set_meta_boxes() {
    // Remove default editor for MCQ Sets to replace with tabbed interface
    remove_post_type_support('mcq_set', 'editor');
    
    add_meta_box(
        'mcq_set_tabs',
        __('MCQ Set Configuration', 'mcqhome'),
        'mcqhome_mcq_set_tabs_callback',
        'mcq_set',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'mcqhome_add_mcq_set_meta_boxes');

/**
 * MCQ Question Details meta box callback
 */
function mcqhome_mcq_question_details_callback($post) {
    wp_nonce_field('mcqhome_save_mcq_meta', 'mcqhome_mcq_meta_nonce');
    
    $question_text = get_post_meta($post->ID, '_mcq_question_text', true);
    
    echo '<div class="mcq-question-section">';
    echo '<div class="mcq-question-header">';
    echo '<h4>' . __('Question Content', 'mcqhome') . '</h4>';
    echo '<p class="description">' . __('Write your question using the rich text editor below. You can format text, add images, videos, and other media to create engaging questions.', 'mcqhome') . '</p>';
    echo '</div>';
    
    echo '<div class="mcq-question-editor">';
    wp_editor($question_text, 'mcq_question_text', [
        'textarea_name' => 'mcq_question_text',
        'media_buttons' => true,
        'textarea_rows' => 10,
        'teeny' => false,
        'textarea_class' => 'mcq-form-field',
        'tinymce' => [
            'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,blockquote,|,link,unlink,|,image,media,|,spellchecker,fullscreen,wp_adv',
            'toolbar2' => 'formatselect,fontselect,fontsizeselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify,|,indent,outdent,|,undo,redo',
            'content_css' => get_template_directory_uri() . '/assets/css/mcq-editor.css',
            'body_class' => 'mcq-question-content',
            'setup' => 'function(editor) {
                editor.on("init", function() {
                    editor.getDoc().body.style.fontSize = "16px";
                    editor.getDoc().body.style.lineHeight = "1.6";
                });
            }'
        ],
        'quicktags' => [
            'buttons' => 'strong,em,ul,ol,li,link,img,close'
        ]
    ]);
    echo '</div>';
    
    echo '<div class="mcq-question-tips">';
    echo '<h5>' . __('Question Writing Tips:', 'mcqhome') . '</h5>';
    echo '<ul>';
    echo '<li>' . __('Keep questions clear and concise', 'mcqhome') . '</li>';
    echo '<li>' . __('Use images or diagrams when they help clarify the question', 'mcqhome') . '</li>';
    echo '<li>' . __('Avoid negative phrasing when possible', 'mcqhome') . '</li>';
    echo '<li>' . __('Make sure the question tests the intended learning objective', 'mcqhome') . '</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '</div>';
}

/**
 * MCQ Answer Options meta box callback
 */
function mcqhome_mcq_answer_options_callback($post) {
    $option_a = get_post_meta($post->ID, '_mcq_option_a', true);
    $option_b = get_post_meta($post->ID, '_mcq_option_b', true);
    $option_c = get_post_meta($post->ID, '_mcq_option_c', true);
    $option_d = get_post_meta($post->ID, '_mcq_option_d', true);
    $correct_answer = get_post_meta($post->ID, '_mcq_correct_answer', true);
    
    echo '<div class="mcq-options-container">';
    echo '<div class="mcq-instructions">';
    echo '<p><strong>' . __('Create your multiple choice question by filling in the four options below and selecting the correct answer:', 'mcqhome') . '</strong></p>';
    echo '<p class="description">' . __('Click the radio button next to the correct answer. The preview will update in real-time as you type.', 'mcqhome') . '</p>';
    echo '</div>';
    
    $options = [
        'A' => $option_a,
        'B' => $option_b,
        'C' => $option_c,
        'D' => $option_d
    ];
    
    foreach ($options as $key => $value) {
        $is_correct = ($correct_answer === $key);
        $row_class = $is_correct ? 'mcq-option-row selected' : 'mcq-option-row';
        
        echo '<div class="' . $row_class . '" data-option="' . $key . '">';
        echo '<input type="radio" name="mcq_correct_answer" value="' . $key . '" class="mcq-option-radio mcq-form-field" id="correct_' . $key . '"' . checked($correct_answer, $key, false) . '>';
        echo '<label class="mcq-option-label" for="correct_' . $key . '">' . $key . '.</label>';
        echo '<input type="text" name="mcq_option_' . strtolower($key) . '" value="' . esc_attr($value) . '" class="mcq-option-input mcq-form-field" placeholder="' . sprintf(__('Enter option %s text here...', 'mcqhome'), $key) . '" maxlength="200">';
        
        if ($is_correct) {
            echo '<span class="mcq-correct-indicator">✓ ' . __('Correct Answer', 'mcqhome') . '</span>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    
    // Add media upload button for question content
    echo '<div class="mcq-media-section">';
    echo '<button type="button" class="button mcq-media-upload-btn">';
    echo '<span class="dashicons dashicons-admin-media"></span>';
    echo __('Add Media to Question', 'mcqhome');
    echo '</button>';
    echo '<p class="description">' . __('You can add images, videos, or audio files to your question text using the media library.', 'mcqhome') . '</p>';
    echo '</div>';
}

/**
 * MCQ Explanation meta box callback
 */
function mcqhome_mcq_explanation_callback($post) {
    $explanation = get_post_meta($post->ID, '_mcq_explanation', true);
    
    echo '<div class="mcq-explanation-section">';
    echo '<div class="mcq-explanation-header">';
    echo '<h4>' . __('Answer Explanation', 'mcqhome') . '</h4>';
    echo '<p class="description">' . __('Provide a clear explanation of why the correct answer is right. This helps students learn from their mistakes and understand the concept better.', 'mcqhome') . '</p>';
    echo '</div>';
    
    echo '<div class="mcq-explanation-editor">';
    wp_editor($explanation, 'mcq_explanation', [
        'textarea_name' => 'mcq_explanation',
        'media_buttons' => true,
        'textarea_rows' => 8,
        'teeny' => false,
        'textarea_class' => 'mcq-form-field',
        'tinymce' => [
            'toolbar1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,image,media,|,spellchecker,fullscreen',
            'toolbar2' => 'formatselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,|,undo,redo',
            'content_css' => get_template_directory_uri() . '/assets/css/mcq-editor.css',
            'body_class' => 'mcq-explanation-content'
        ],
        'quicktags' => [
            'buttons' => 'strong,em,ul,ol,li,link,img,close'
        ]
    ]);
    echo '</div>';
    
    echo '<div class="mcq-explanation-tips">';
    echo '<h5>' . __('Explanation Writing Tips:', 'mcqhome') . '</h5>';
    echo '<ul>';
    echo '<li>' . __('Explain why the correct answer is right', 'mcqhome') . '</li>';
    echo '<li>' . __('Briefly mention why other options are incorrect', 'mcqhome') . '</li>';
    echo '<li>' . __('Include relevant formulas, concepts, or references', 'mcqhome') . '</li>';
    echo '<li>' . __('Use simple language that students can understand', 'mcqhome') . '</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '</div>';
}

/**
 * MCQ Categorization meta box callback
 */
function mcqhome_mcq_categorization_callback($post) {
    // Get current values
    $selected_subjects = wp_get_post_terms($post->ID, 'mcq_subject', ['fields' => 'ids']);
    $selected_topics = wp_get_post_terms($post->ID, 'mcq_topic', ['fields' => 'ids']);
    $selected_difficulty = wp_get_post_terms($post->ID, 'mcq_difficulty', ['fields' => 'ids']);
    
    // Get all available terms
    $subjects = get_terms(['taxonomy' => 'mcq_subject', 'hide_empty' => false]);
    $topics = get_terms(['taxonomy' => 'mcq_topic', 'hide_empty' => false]);
    $difficulties = get_terms(['taxonomy' => 'mcq_difficulty', 'hide_empty' => false]);
    
    echo '<div class="mcq-categorization-section">';
    
    // Subject selection
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_subject_select"><strong>' . __('Subject', 'mcqhome') . '</strong></label>';
    echo '<select name="tax_input[mcq_subject][]" id="mcq_subject_select" class="widefat mcq-form-field" multiple>';
    if (!empty($subjects)) {
        foreach ($subjects as $subject) {
            $selected = in_array($subject->term_id, $selected_subjects) ? 'selected' : '';
            echo '<option value="' . $subject->term_id . '" ' . $selected . '>' . esc_html($subject->name) . '</option>';
        }
    }
    echo '</select>';
    echo '<p class="description">' . __('Select one or more subjects for this MCQ. Hold Ctrl/Cmd to select multiple.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Topic selection
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_topic_select"><strong>' . __('Topic', 'mcqhome') . '</strong></label>';
    echo '<select name="tax_input[mcq_topic][]" id="mcq_topic_select" class="widefat mcq-form-field" multiple>';
    if (!empty($topics)) {
        foreach ($topics as $topic) {
            $selected = in_array($topic->term_id, $selected_topics) ? 'selected' : '';
            echo '<option value="' . $topic->term_id . '" ' . $selected . '>' . esc_html($topic->name) . '</option>';
        }
    }
    echo '</select>';
    echo '<p class="description">' . __('Select relevant topics for this MCQ.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Difficulty selection
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_difficulty_select"><strong>' . __('Difficulty Level', 'mcqhome') . '</strong></label>';
    echo '<select name="tax_input[mcq_difficulty]" id="mcq_difficulty_select" class="widefat mcq-form-field">';
    echo '<option value="">' . __('Select Difficulty', 'mcqhome') . '</option>';
    if (!empty($difficulties)) {
        foreach ($difficulties as $difficulty) {
            $selected = in_array($difficulty->term_id, $selected_difficulty) ? 'selected' : '';
            echo '<option value="' . $difficulty->term_id . '" ' . $selected . '>' . esc_html($difficulty->name) . '</option>';
        }
    }
    echo '</select>';
    echo '<p class="description">' . __('Choose the difficulty level for this MCQ.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Quick add buttons for new terms
    echo '<div class="mcq-quick-add">';
    echo '<h4>' . __('Quick Add', 'mcqhome') . '</h4>';
    echo '<button type="button" class="button button-secondary mcq-quick-add-btn" data-taxonomy="mcq_subject">' . __('Add New Subject', 'mcqhome') . '</button>';
    echo '<button type="button" class="button button-secondary mcq-quick-add-btn" data-taxonomy="mcq_topic">' . __('Add New Topic', 'mcqhome') . '</button>';
    echo '</div>';
    
    echo '</div>';
}

/**
 * MCQ Settings meta box callback
 */
function mcqhome_mcq_settings_callback($post) {
    // Get current settings
    $mcq_tags = get_post_meta($post->ID, '_mcq_tags', true);
    $mcq_time_limit = get_post_meta($post->ID, '_mcq_time_limit', true);
    $mcq_marks = get_post_meta($post->ID, '_mcq_marks', true);
    $mcq_negative_marks = get_post_meta($post->ID, '_mcq_negative_marks', true);
    $mcq_hint = get_post_meta($post->ID, '_mcq_hint', true);
    $mcq_reference = get_post_meta($post->ID, '_mcq_reference', true);
    
    echo '<div class="mcq-settings-section">';
    
    // Tags
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_tags"><strong>' . __('Tags', 'mcqhome') . '</strong></label>';
    echo '<input type="text" name="mcq_tags" id="mcq_tags" value="' . esc_attr($mcq_tags) . '" class="widefat mcq-form-field" placeholder="' . __('Enter tags separated by commas', 'mcqhome') . '">';
    echo '<p class="description">' . __('Add relevant tags to help with search and organization.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Time limit
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_time_limit"><strong>' . __('Time Limit (seconds)', 'mcqhome') . '</strong></label>';
    echo '<input type="number" name="mcq_time_limit" id="mcq_time_limit" value="' . esc_attr($mcq_time_limit) . '" class="widefat mcq-form-field" min="0" placeholder="60">';
    echo '<p class="description">' . __('Optional time limit for this question in seconds. Leave empty for no limit.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Marks
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_marks"><strong>' . __('Marks', 'mcqhome') . '</strong></label>';
    echo '<input type="number" name="mcq_marks" id="mcq_marks" value="' . esc_attr($mcq_marks ?: '1') . '" class="widefat mcq-form-field" min="0" step="0.5">';
    echo '<p class="description">' . __('Points awarded for correct answer.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Negative marks
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_negative_marks"><strong>' . __('Negative Marks', 'mcqhome') . '</strong></label>';
    echo '<input type="number" name="mcq_negative_marks" id="mcq_negative_marks" value="' . esc_attr($mcq_negative_marks ?: '0') . '" class="widefat mcq-form-field" min="0" step="0.25">';
    echo '<p class="description">' . __('Points deducted for incorrect answer.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Hint
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_hint"><strong>' . __('Hint', 'mcqhome') . '</strong></label>';
    echo '<textarea name="mcq_hint" id="mcq_hint" class="widefat mcq-form-field" rows="3" placeholder="' . __('Optional hint for students...', 'mcqhome') . '">' . esc_textarea($mcq_hint) . '</textarea>';
    echo '<p class="description">' . __('Optional hint to help students with this question.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Reference
    echo '<div class="mcq-meta-field">';
    echo '<label for="mcq_reference"><strong>' . __('Reference', 'mcqhome') . '</strong></label>';
    echo '<input type="text" name="mcq_reference" id="mcq_reference" value="' . esc_attr($mcq_reference) . '" class="widefat mcq-form-field" placeholder="' . __('Book, chapter, page, etc.', 'mcqhome') . '">';
    echo '<p class="description">' . __('Reference source for this question.', 'mcqhome') . '</p>';
    echo '</div>';
    
    echo '</div>';
}

/**
 * Save MCQ meta data
 */
function mcqhome_save_mcq_meta($post_id) {
    // Check if nonce is valid
    if (!isset($_POST['mcqhome_mcq_meta_nonce']) || !wp_verify_nonce($_POST['mcqhome_mcq_meta_nonce'], 'mcqhome_save_mcq_meta')) {
        return;
    }

    // Check if user has permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if not an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check post type
    if (get_post_type($post_id) !== 'mcq') {
        return;
    }

    // Save question text
    if (isset($_POST['mcq_question_text'])) {
        update_post_meta($post_id, '_mcq_question_text', wp_kses_post($_POST['mcq_question_text']));
    }

    // Save answer options
    $options = ['a', 'b', 'c', 'd'];
    foreach ($options as $option) {
        if (isset($_POST['mcq_option_' . $option])) {
            update_post_meta($post_id, '_mcq_option_' . $option, sanitize_text_field($_POST['mcq_option_' . $option]));
        }
    }

    // Save correct answer
    if (isset($_POST['mcq_correct_answer']) && in_array($_POST['mcq_correct_answer'], ['A', 'B', 'C', 'D'])) {
        update_post_meta($post_id, '_mcq_correct_answer', sanitize_text_field($_POST['mcq_correct_answer']));
    }

    // Save explanation
    if (isset($_POST['mcq_explanation'])) {
        update_post_meta($post_id, '_mcq_explanation', wp_kses_post($_POST['mcq_explanation']));
    }

    // Save additional metadata
    $meta_fields = [
        'mcq_tags' => 'sanitize_text_field',
        'mcq_time_limit' => 'intval',
        'mcq_marks' => 'floatval',
        'mcq_negative_marks' => 'floatval',
        'mcq_hint' => 'sanitize_textarea_field',
        'mcq_reference' => 'sanitize_text_field'
    ];

    foreach ($meta_fields as $field => $sanitize_function) {
        if (isset($_POST[$field])) {
            $value = $sanitize_function($_POST[$field]);
            update_post_meta($post_id, '_' . $field, $value);
        }
    }
}
add_action('save_post', 'mcqhome_save_mcq_meta');

/**
 * Create default difficulty terms on theme activation
 */
function mcqhome_create_default_difficulty_terms() {
    $default_difficulties = [
        'easy' => __('Easy', 'mcqhome'),
        'medium' => __('Medium', 'mcqhome'),
        'hard' => __('Hard', 'mcqhome')
    ];

    foreach ($default_difficulties as $slug => $name) {
        if (!term_exists($slug, 'mcq_difficulty')) {
            wp_insert_term($name, 'mcq_difficulty', [
                'slug' => $slug
            ]);
        }
    }
}
add_action('after_switch_theme', 'mcqhome_create_default_difficulty_terms');

/**
 * Customize MCQ admin columns
 */
function mcqhome_mcq_admin_columns($columns) {
    $new_columns = [];
    
    // Add checkbox
    $new_columns['cb'] = $columns['cb'];
    
    // Replace title with question text
    $new_columns['mcq_question'] = __('Question Text', 'mcqhome');
    
    // Add custom columns
    $new_columns['mcq_correct_answer'] = __('Correct Answer', 'mcqhome');
    $new_columns['mcq_subject'] = __('Subject', 'mcqhome');
    $new_columns['mcq_difficulty'] = __('Difficulty', 'mcqhome');
    $new_columns['mcq_set'] = __('MCQ Set', 'mcqhome');
    $new_columns['author'] = $columns['author'];
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}
add_filter('manage_mcq_posts_columns', 'mcqhome_mcq_admin_columns');

/**
 * Populate custom MCQ admin columns
 */
function mcqhome_mcq_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'mcq_question':
            $question_text = get_post_meta($post_id, '_mcq_question_text', true);
            if ($question_text) {
                echo '<div style="max-width: 400px; overflow: hidden;">';
                echo '<strong>' . wp_trim_words(strip_tags($question_text), 20, '...') . '</strong>';
                echo '</div>';
            } else {
                // Fallback to post title if no question text
                $title = get_the_title($post_id);
                if ($title) {
                    echo '<div style="max-width: 400px; overflow: hidden;">';
                    echo '<strong>' . esc_html($title) . '</strong>';
                    echo '<br><small style="color: #666;">' . __('(Using title - no question text set)', 'mcqhome') . '</small>';
                    echo '</div>';
                } else {
                    echo '<em>' . __('No question text', 'mcqhome') . '</em>';
                }
            }
            break;
            
        case 'mcq_correct_answer':
            $correct_answer = get_post_meta($post_id, '_mcq_correct_answer', true);
            $option_text = get_post_meta($post_id, '_mcq_option_' . strtolower($correct_answer), true);
            if ($correct_answer && $option_text) {
                echo '<strong>' . $correct_answer . '.</strong> ' . wp_trim_words($option_text, 8, '...');
            } else {
                echo '<em>' . __('Not set', 'mcqhome') . '</em>';
            }
            break;
            
        case 'mcq_subject':
            $terms = get_the_terms($post_id, 'mcq_subject');
            if ($terms && !is_wp_error($terms)) {
                $term_names = wp_list_pluck($terms, 'name');
                echo implode(', ', $term_names);
            } else {
                echo '<em>' . __('No subject', 'mcqhome') . '</em>';
            }
            break;
            
        case 'mcq_difficulty':
            $terms = get_the_terms($post_id, 'mcq_difficulty');
            if ($terms && !is_wp_error($terms)) {
                $term_names = wp_list_pluck($terms, 'name');
                echo implode(', ', $term_names);
            } else {
                echo '<em>' . __('No difficulty', 'mcqhome') . '</em>';
            }
            break;
            
        case 'mcq_set':
            $mcq_set_id = mcqhome_find_mcq_set_for_question($post_id);
            if ($mcq_set_id) {
                $mcq_set_title = get_the_title($mcq_set_id);
                $edit_link = get_edit_post_link($mcq_set_id);
                echo '<a href="' . esc_url($edit_link) . '">' . esc_html($mcq_set_title) . '</a>';
            } else {
                echo '<em style="color: #d63638;">' . __('Not assigned to any set', 'mcqhome') . '</em>';
            }
            break;
    }
}
add_action('manage_mcq_posts_custom_column', 'mcqhome_mcq_admin_column_content', 10, 2);

/**
 * Make MCQ admin columns sortable
 */
function mcqhome_mcq_sortable_columns($columns) {
    $columns['mcq_subject'] = 'mcq_subject';
    $columns['mcq_difficulty'] = 'mcq_difficulty';
    return $columns;
}
add_filter('manage_edit-mcq_sortable_columns', 'mcqhome_mcq_sortable_columns');

/**
 * Handle sorting for MCQ admin columns
 */
function mcqhome_mcq_admin_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ($orderby === 'mcq_subject') {
        $query->set('meta_key', 'mcq_subject');
        $query->set('orderby', 'meta_value');
    } elseif ($orderby === 'mcq_difficulty') {
        $query->set('meta_key', 'mcq_difficulty');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'mcqhome_mcq_admin_orderby');

/**
 * Add bulk actions for MCQ management
 */
function mcqhome_mcq_bulk_actions($bulk_actions) {
    $bulk_actions['assign_to_set'] = __('Assign to MCQ Set', 'mcqhome');
    $bulk_actions['remove_from_set'] = __('Remove from MCQ Set', 'mcqhome');
    $bulk_actions['update_difficulty'] = __('Update Difficulty', 'mcqhome');
    $bulk_actions['update_subject'] = __('Update Subject', 'mcqhome');
    return $bulk_actions;
}
add_filter('bulk_actions-edit-mcq', 'mcqhome_mcq_bulk_actions');

/**
 * Handle MCQ bulk actions
 */
function mcqhome_handle_mcq_bulk_actions($redirect_to, $doaction, $post_ids) {
    if (empty($post_ids)) {
        return $redirect_to;
    }
    
    switch ($doaction) {
        case 'assign_to_set':
            if (isset($_REQUEST['mcq_target_set']) && !empty($_REQUEST['mcq_target_set'])) {
                $target_set_id = intval($_REQUEST['mcq_target_set']);
                $assigned_count = 0;
                
                foreach ($post_ids as $post_id) {
                    if (get_post_type($post_id) === 'mcq') {
                        mcqhome_add_question_to_mcq_set($target_set_id, $post_id);
                        $assigned_count++;
                    }
                }
                
                $redirect_to = add_query_arg([
                    'bulk_assigned' => $assigned_count,
                    'target_set' => $target_set_id
                ], $redirect_to);
            }
            break;
            
        case 'remove_from_set':
            $removed_count = 0;
            foreach ($post_ids as $post_id) {
                if (get_post_type($post_id) === 'mcq') {
                    mcqhome_remove_question_from_all_sets($post_id);
                    $removed_count++;
                }
            }
            
            $redirect_to = add_query_arg('bulk_removed', $removed_count, $redirect_to);
            break;
            
        case 'update_difficulty':
            if (isset($_REQUEST['mcq_target_difficulty']) && !empty($_REQUEST['mcq_target_difficulty'])) {
                $difficulty_id = intval($_REQUEST['mcq_target_difficulty']);
                $updated_count = 0;
                
                foreach ($post_ids as $post_id) {
                    if (get_post_type($post_id) === 'mcq') {
                        wp_set_post_terms($post_id, [$difficulty_id], 'mcq_difficulty');
                        $updated_count++;
                    }
                }
                
                $redirect_to = add_query_arg('bulk_difficulty_updated', $updated_count, $redirect_to);
            }
            break;
            
        case 'update_subject':
            if (isset($_REQUEST['mcq_target_subject']) && !empty($_REQUEST['mcq_target_subject'])) {
                $subject_id = intval($_REQUEST['mcq_target_subject']);
                $updated_count = 0;
                
                foreach ($post_ids as $post_id) {
                    if (get_post_type($post_id) === 'mcq') {
                        wp_set_post_terms($post_id, [$subject_id], 'mcq_subject');
                        $updated_count++;
                    }
                }
                
                $redirect_to = add_query_arg('bulk_subject_updated', $updated_count, $redirect_to);
            }
            break;
    }
    
    return $redirect_to;
}
add_filter('handle_bulk_actions-edit-mcq', 'mcqhome_handle_mcq_bulk_actions', 10, 3);

/**
 * Add bulk actions for MCQ Set management
 */
function mcqhome_mcq_set_bulk_actions($bulk_actions) {
    $bulk_actions['toggle_featured'] = __('Toggle Featured Status', 'mcqhome');
    $bulk_actions['set_pricing_free'] = __('Set as Free', 'mcqhome');
    $bulk_actions['set_pricing_paid'] = __('Set as Paid', 'mcqhome');
    $bulk_actions['update_format_next'] = __('Set Next-Next Format', 'mcqhome');
    $bulk_actions['update_format_single'] = __('Set Single Page Format', 'mcqhome');
    return $bulk_actions;
}
add_filter('bulk_actions-edit-mcq_set', 'mcqhome_mcq_set_bulk_actions');

/**
 * Handle MCQ Set bulk actions
 */
function mcqhome_handle_mcq_set_bulk_actions($redirect_to, $doaction, $post_ids) {
    if (empty($post_ids)) {
        return $redirect_to;
    }
    
    $updated_count = 0;
    
    foreach ($post_ids as $post_id) {
        if (get_post_type($post_id) !== 'mcq_set') {
            continue;
        }
        
        switch ($doaction) {
            case 'toggle_featured':
                $current_featured = get_post_meta($post_id, '_mcq_set_featured', true);
                $new_featured = ($current_featured === '1') ? '0' : '1';
                update_post_meta($post_id, '_mcq_set_featured', $new_featured);
                $updated_count++;
                break;
                
            case 'set_pricing_free':
                update_post_meta($post_id, '_mcq_set_pricing_type', 'free');
                delete_post_meta($post_id, '_mcq_set_price');
                $updated_count++;
                break;
                
            case 'set_pricing_paid':
                update_post_meta($post_id, '_mcq_set_pricing_type', 'paid');
                if (!get_post_meta($post_id, '_mcq_set_price', true)) {
                    update_post_meta($post_id, '_mcq_set_price', '9.99');
                }
                $updated_count++;
                break;
                
            case 'update_format_next':
                update_post_meta($post_id, '_mcq_set_display_format', 'next_next');
                $updated_count++;
                break;
                
            case 'update_format_single':
                update_post_meta($post_id, '_mcq_set_display_format', 'single_page');
                $updated_count++;
                break;
        }
    }
    
    if ($updated_count > 0) {
        $redirect_to = add_query_arg('bulk_updated', $updated_count, $redirect_to);
    }
    
    return $redirect_to;
}
add_filter('handle_bulk_actions-edit-mcq_set', 'mcqhome_handle_mcq_set_bulk_actions', 10, 3);

/**
 * Display admin notices for bulk actions
 */
function mcqhome_bulk_action_admin_notices() {
    if (isset($_REQUEST['bulk_assigned'])) {
        $count = intval($_REQUEST['bulk_assigned']);
        $set_id = intval($_REQUEST['target_set']);
        $set_title = get_the_title($set_id);
        
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            sprintf(
                _n(
                    '%d MCQ assigned to "%s".',
                    '%d MCQs assigned to "%s".',
                    $count,
                    'mcqhome'
                ),
                $count,
                esc_html($set_title)
            )
        );
    }
    
    if (isset($_REQUEST['bulk_removed'])) {
        $count = intval($_REQUEST['bulk_removed']);
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            sprintf(
                _n(
                    '%d MCQ removed from all sets.',
                    '%d MCQs removed from all sets.',
                    $count,
                    'mcqhome'
                ),
                $count
            )
        );
    }
    
    if (isset($_REQUEST['bulk_difficulty_updated'])) {
        $count = intval($_REQUEST['bulk_difficulty_updated']);
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            sprintf(
                _n(
                    'Difficulty updated for %d MCQ.',
                    'Difficulty updated for %d MCQs.',
                    $count,
                    'mcqhome'
                ),
                $count
            )
        );
    }
    
    if (isset($_REQUEST['bulk_subject_updated'])) {
        $count = intval($_REQUEST['bulk_subject_updated']);
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            sprintf(
                _n(
                    'Subject updated for %d MCQ.',
                    'Subject updated for %d MCQs.',
                    $count,
                    'mcqhome'
                ),
                $count
            )
        );
    }
    
    if (isset($_REQUEST['bulk_updated'])) {
        $count = intval($_REQUEST['bulk_updated']);
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            sprintf(
                _n(
                    '%d MCQ Set updated.',
                    '%d MCQ Sets updated.',
                    $count,
                    'mcqhome'
                ),
                $count
            )
        );
    }
}
add_action('admin_notices', 'mcqhome_bulk_action_admin_notices');

/**
 * Add bulk action UI elements to MCQ admin page
 */
function mcqhome_add_mcq_bulk_ui() {
    global $post_type;
    
    if ($post_type !== 'mcq') {
        return;
    }
    
    // Get MCQ Sets for assignment dropdown
    $mcq_sets = get_posts([
        'post_type' => 'mcq_set',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    // Get taxonomies for bulk updates
    $difficulties = get_terms(['taxonomy' => 'mcq_difficulty', 'hide_empty' => false]);
    $subjects = get_terms(['taxonomy' => 'mcq_subject', 'hide_empty' => false]);
    
    ?>
    <div class="mcq-bulk-actions" style="display: none;">
        <h4><?php _e('Bulk Action Options', 'mcqhome'); ?></h4>
        
        <div class="bulk-option" id="bulk-assign-set" style="display: none;">
            <label for="mcq_target_set"><?php _e('Select MCQ Set:', 'mcqhome'); ?></label>
            <select name="mcq_target_set" id="mcq_target_set">
                <option value=""><?php _e('Choose MCQ Set...', 'mcqhome'); ?></option>
                <?php foreach ($mcq_sets as $set): ?>
                    <option value="<?php echo $set->ID; ?>"><?php echo esc_html($set->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="bulk-option" id="bulk-update-difficulty" style="display: none;">
            <label for="mcq_target_difficulty"><?php _e('Select Difficulty:', 'mcqhome'); ?></label>
            <select name="mcq_target_difficulty" id="mcq_target_difficulty">
                <option value=""><?php _e('Choose Difficulty...', 'mcqhome'); ?></option>
                <?php foreach ($difficulties as $difficulty): ?>
                    <option value="<?php echo $difficulty->term_id; ?>"><?php echo esc_html($difficulty->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="bulk-option" id="bulk-update-subject" style="display: none;">
            <label for="mcq_target_subject"><?php _e('Select Subject:', 'mcqhome'); ?></label>
            <select name="mcq_target_subject" id="mcq_target_subject">
                <option value=""><?php _e('Choose Subject...', 'mcqhome'); ?></option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo $subject->term_id; ?>"><?php echo esc_html($subject->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Show/hide bulk action options based on selection
        $('#bulk-action-selector-top, #bulk-action-selector-bottom').on('change', function() {
            var action = $(this).val();
            var bulkContainer = $('.mcq-bulk-actions');
            var allOptions = bulkContainer.find('.bulk-option');
            
            allOptions.hide();
            
            if (action === 'assign_to_set') {
                bulkContainer.show();
                $('#bulk-assign-set').show();
            } else if (action === 'update_difficulty') {
                bulkContainer.show();
                $('#bulk-update-difficulty').show();
            } else if (action === 'update_subject') {
                bulkContainer.show();
                $('#bulk-update-subject').show();
            } else {
                bulkContainer.hide();
            }
        });
        
        // Validate bulk actions before submission
        $('#doaction, #doaction2').on('click', function(e) {
            var action = $(this).siblings('select').val();
            var hasSelection = $('.wp-list-table input[type="checkbox"]:checked').length > 0;
            
            if (!hasSelection) {
                return true; // Let WordPress handle the "no items selected" message
            }
            
            if (action === 'assign_to_set' && !$('#mcq_target_set').val()) {
                e.preventDefault();
                alert('<?php _e('Please select an MCQ Set to assign questions to.', 'mcqhome'); ?>');
                return false;
            }
            
            if (action === 'update_difficulty' && !$('#mcq_target_difficulty').val()) {
                e.preventDefault();
                alert('<?php _e('Please select a difficulty level.', 'mcqhome'); ?>');
                return false;
            }
            
            if (action === 'update_subject' && !$('#mcq_target_subject').val()) {
                e.preventDefault();
                alert('<?php _e('Please select a subject.', 'mcqhome'); ?>');
                return false;
            }
        });
    });
    </script>
    <?php
}
add_action('admin_footer-edit.php', 'mcqhome_add_mcq_bulk_ui');

/**
 * Add admin styles for MCQ and MCQ Set columns
 */
function mcqhome_admin_styles() {
    global $post_type;
    
    if ($post_type === 'mcq') {
        echo '<style>
            .wp-list-table .column-mcq_question { width: 35%; }
            .wp-list-table .column-mcq_correct_answer { width: 20%; }
            .wp-list-table .column-mcq_subject { width: 12%; }
            .wp-list-table .column-mcq_difficulty { width: 10%; }
            .wp-list-table .column-mcq_set { width: 15%; }
            .wp-list-table .column-author { width: 8%; }
            
            /* Enhanced MCQ admin styling */
            .mcq-admin-orphaned {
                background-color: #fff2cd !important;
            }
            .mcq-admin-orphaned td {
                border-left: 4px solid #dba617 !important;
            }
            .mcq-bulk-actions {
                margin: 10px 0;
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 3px;
            }
            .mcq-bulk-actions select,
            .mcq-bulk-actions input {
                margin-right: 10px;
            }
        </style>';
    } elseif ($post_type === 'mcq_set') {
        echo '<style>
            .wp-list-table .column-title { width: 25%; }
            .wp-list-table .column-mcq_count { width: 15%; }
            .wp-list-table .column-sections_info { width: 15%; }
            .wp-list-table .column-total_marks { width: 10%; }
            .wp-list-table .column-pricing { width: 10%; }
            .wp-list-table .column-display_format { width: 12%; }
            .wp-list-table .column-featured { width: 8%; }
            .wp-list-table .column-author { width: 10%; }
            .wp-list-table .column-date { width: 10%; }
            
            /* Enhanced MCQ Set admin styling */
            .mcq-set-admin-featured {
                background-color: #f0f8ff !important;
            }
            .mcq-set-admin-featured td {
                border-left: 4px solid #0073aa !important;
            }
            .mcq-set-bulk-actions {
                margin: 10px 0;
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 3px;
            }
            .mcq-set-bulk-actions select,
            .mcq-set-bulk-actions input {
                margin-right: 10px;
            }
        </style>';
    }
}
add_action('admin_head', 'mcqhome_admin_styles');

/**
 * Register MCQ Set custom post type
 */
function mcqhome_register_mcq_set_post_type() {
    $labels = [
        'name'                  => _x('MCQ Sets', 'Post type general name', 'mcqhome'),
        'singular_name'         => _x('MCQ Set', 'Post type singular name', 'mcqhome'),
        'menu_name'             => _x('MCQ Sets', 'Admin Menu text', 'mcqhome'),
        'name_admin_bar'        => _x('MCQ Set', 'Add New on Toolbar', 'mcqhome'),
        'add_new'               => __('Add New', 'mcqhome'),
        'add_new_item'          => __('Add New MCQ Set', 'mcqhome'),
        'new_item'              => __('New MCQ Set', 'mcqhome'),
        'edit_item'             => __('Edit MCQ Set', 'mcqhome'),
        'view_item'             => __('View MCQ Set', 'mcqhome'),
        'all_items'             => __('All MCQ Sets', 'mcqhome'),
        'search_items'          => __('Search MCQ Sets', 'mcqhome'),
        'parent_item_colon'     => __('Parent MCQ Sets:', 'mcqhome'),
        'not_found'             => __('No MCQ Sets found.', 'mcqhome'),
        'not_found_in_trash'    => __('No MCQ Sets found in Trash.', 'mcqhome'),
        'featured_image'        => _x('MCQ Set Featured Image', 'Overrides the "Featured Image" phrase', 'mcqhome'),
        'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'mcqhome'),
        'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'mcqhome'),
        'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'mcqhome'),
        'archives'              => _x('MCQ Set archives', 'The post type archive label', 'mcqhome'),
        'insert_into_item'      => _x('Insert into MCQ Set', 'Overrides the "Insert into post" phrase', 'mcqhome'),
        'uploaded_to_this_item' => _x('Uploaded to this MCQ Set', 'Overrides the "Uploaded to this post" phrase', 'mcqhome'),
        'filter_items_list'     => _x('Filter MCQ Sets list', 'Screen reader text for the filter links', 'mcqhome'),
        'items_list_navigation' => _x('MCQ Sets list navigation', 'Screen reader text for the pagination', 'mcqhome'),
        'items_list'            => _x('MCQ Sets list', 'Screen reader text for the items list', 'mcqhome'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'mcq-set'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 21,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt'],
        'show_in_rest'       => true,
        'rest_base'          => 'mcq-sets',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    ];

    register_post_type('mcq_set', $args);
}
add_action('init', 'mcqhome_register_mcq_set_post_type');

/**
 * Add MCQ Set meta boxes
 */
function mcqhome_add_mcq_set_meta_boxes() {
    add_meta_box(
        'mcq_set_general',
        __('General Settings', 'mcqhome'),
        'mcqhome_mcq_set_general_callback',
        'mcq_set',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_set_assessment',
        __('Assessment Configuration', 'mcqhome'),
        'mcqhome_mcq_set_assessment_callback',
        'mcq_set',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_set_sections',
        __('Exam Sections (Optional)', 'mcqhome'),
        'mcqhome_mcq_set_sections_callback',
        'mcq_set',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_set_questions',
        __('Questions Management', 'mcqhome'),
        'mcqhome_mcq_set_questions_callback',
        'mcq_set',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_set_pricing',
        __('Pricing & Publication', 'mcqhome'),
        'mcqhome_mcq_set_pricing_callback',
        'mcq_set',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'mcqhome_add_mcq_set_meta_boxes');

/**
 * MCQ Set General Settings meta box callback
 */
function mcqhome_mcq_set_general_callback($post) {
    wp_nonce_field('mcqhome_save_mcq_set_meta', 'mcqhome_mcq_set_meta_nonce');
    
    $thumbnail_id = get_post_meta($post->ID, '_mcq_set_thumbnail', true);
    $pricing_type = get_post_meta($post->ID, '_mcq_set_pricing_type', true) ?: 'free';
    $price = get_post_meta($post->ID, '_mcq_set_price', true) ?: 0;
    $author_id = get_post_meta($post->ID, '_mcq_set_author', true) ?: get_current_user_id();
    
    echo '<table class="form-table">';
    
    // Thumbnail
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_thumbnail">' . __('Quiz Thumbnail', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<div id="mcq-set-thumbnail-container">';
    if ($thumbnail_id) {
        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
        echo '<img src="' . esc_url($thumbnail_url) . '" style="max-width: 200px; height: auto; display: block; margin-bottom: 10px;">';
        echo '<input type="hidden" name="mcq_set_thumbnail" value="' . esc_attr($thumbnail_id) . '">';
        echo '<button type="button" class="button" id="remove-thumbnail">' . __('Remove Thumbnail', 'mcqhome') . '</button>';
    } else {
        echo '<button type="button" class="button" id="upload-thumbnail">' . __('Set Thumbnail', 'mcqhome') . '</button>';
        echo '<input type="hidden" name="mcq_set_thumbnail" value="">';
    }
    echo '</div>';
    echo '<p class="description">' . __('Upload a thumbnail image for this MCQ set.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Categories (using existing taxonomies)
    echo '<tr>';
    echo '<th scope="row">' . __('Categories', 'mcqhome') . '</th>';
    echo '<td>';
    $subjects = get_terms(['taxonomy' => 'mcq_subject', 'hide_empty' => false]);
    $selected_subjects = wp_get_post_terms($post->ID, 'mcq_subject', ['fields' => 'ids']);
    
    if (!empty($subjects)) {
        echo '<select name="tax_input[mcq_subject][]" multiple class="widefat" style="height: 100px;">';
        foreach ($subjects as $subject) {
            $selected = in_array($subject->term_id, $selected_subjects) ? 'selected' : '';
            echo '<option value="' . $subject->term_id . '" ' . $selected . '>' . esc_html($subject->name) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Select categories for this MCQ set. Hold Ctrl/Cmd to select multiple.', 'mcqhome') . '</p>';
    } else {
        echo '<p><em>' . __('No categories available. Create subjects first.', 'mcqhome') . '</em></p>';
    }
    echo '</td>';
    echo '</tr>';
    
    // Pricing
    echo '<tr>';
    echo '<th scope="row">' . __('Pricing', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<fieldset>';
    echo '<label><input type="radio" name="mcq_set_pricing_type" value="free"' . checked($pricing_type, 'free', false) . '> ' . __('Free Access', 'mcqhome') . '</label><br>';
    echo '<label><input type="radio" name="mcq_set_pricing_type" value="paid"' . checked($pricing_type, 'paid', false) . '> ' . __('Paid Access', 'mcqhome') . '</label>';
    echo '</fieldset>';
    echo '<div id="price-field" style="margin-top: 10px; ' . ($pricing_type === 'free' ? 'display:none;' : '') . '">';
    echo '<label for="mcq_set_price">' . __('Price:', 'mcqhome') . '</label><br>';
    echo '<input type="number" name="mcq_set_price" id="mcq_set_price" value="' . esc_attr($price) . '" min="0" step="0.01" class="regular-text">';
    echo '</div>';
    echo '</td>';
    echo '</tr>';
    
    // Author selection
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_author">' . __('Author/Teacher', 'mcqhome') . '</label></th>';
    echo '<td>';
    $users = get_users(['role__in' => ['administrator', 'editor', 'author']]);
    echo '<select name="mcq_set_author" id="mcq_set_author" class="regular-text">';
    foreach ($users as $user) {
        $selected = selected($author_id, $user->ID, false);
        echo '<option value="' . $user->ID . '" ' . $selected . '>' . esc_html($user->display_name) . ' (' . $user->user_login . ')</option>';
    }
    echo '</select>';
    echo '<p class="description">' . __('Select the author/teacher for this MCQ set.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    // JavaScript for thumbnail upload and pricing toggle
    echo '<script>
    jQuery(document).ready(function($) {
        var mediaUploader;
        
        $("#upload-thumbnail").on("click", function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: "' . __('Choose Thumbnail', 'mcqhome') . '",
                button: {
                    text: "' . __('Choose Thumbnail', 'mcqhome') . '"
                },
                multiple: false
            });
            
            mediaUploader.on("select", function() {
                var attachment = mediaUploader.state().get("selection").first().toJSON();
                $("#mcq-set-thumbnail-container").html(
                    "<img src=\"" + attachment.sizes.medium.url + "\" style=\"max-width: 200px; height: auto; display: block; margin-bottom: 10px;\">" +
                    "<input type=\"hidden\" name=\"mcq_set_thumbnail\" value=\"" + attachment.id + "\">" +
                    "<button type=\"button\" class=\"button\" id=\"remove-thumbnail\">' . __('Remove Thumbnail', 'mcqhome') . '</button>"
                );
            });
            
            mediaUploader.open();
        });
        
        $(document).on("click", "#remove-thumbnail", function(e) {
            e.preventDefault();
            $("#mcq-set-thumbnail-container").html(
                "<button type=\"button\" class=\"button\" id=\"upload-thumbnail\">' . __('Set Thumbnail', 'mcqhome') . '</button>" +
                "<input type=\"hidden\" name=\"mcq_set_thumbnail\" value=\"\">"
            );
        });
        
        $("input[name=mcq_set_pricing_type]").on("change", function() {
            if ($(this).val() === "paid") {
                $("#price-field").show();
            } else {
                $("#price-field").hide();
                $("#mcq_set_price").val(0);
            }
        });
    });
    </script>';
}

/**
 * MCQ Set Assessment Configuration meta box callback
 */
function mcqhome_mcq_set_assessment_callback($post) {
    $pass_marks = get_post_meta($post->ID, '_mcq_set_pass_marks', true) ?: 0;
    $time_limit = get_post_meta($post->ID, '_mcq_set_time_limit', true) ?: 0;
    $display_format = get_post_meta($post->ID, '_mcq_set_display_format', true) ?: 'next_next';
    $show_results_immediately = get_post_meta($post->ID, '_mcq_set_show_results_immediately', true) !== '0';
    $allow_retakes = get_post_meta($post->ID, '_mcq_set_allow_retakes', true) !== '0';
    $shuffle_questions = get_post_meta($post->ID, '_mcq_set_shuffle_questions', true) === '1';
    
    echo '<table class="form-table">';
    
    // Pass marks
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_pass_marks">' . __('Pass Marks', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="mcq_set_pass_marks" id="mcq_set_pass_marks" value="' . esc_attr($pass_marks) . '" min="0" step="0.5" class="regular-text">';
    echo '<p class="description">' . __('Minimum marks required to pass this assessment.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Time limit
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_time_limit">' . __('Time Limit (minutes)', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="mcq_set_time_limit" id="mcq_set_time_limit" value="' . esc_attr($time_limit) . '" min="0" class="regular-text">';
    echo '<p class="description">' . __('Time limit in minutes. Set to 0 for no time limit.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Display format
    echo '<tr>';
    echo '<th scope="row">' . __('Display Format', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<fieldset>';
    echo '<label><input type="radio" name="mcq_set_display_format" value="next_next"' . checked($display_format, 'next_next', false) . '> ' . __('Next-Next Format', 'mcqhome') . '</label><br>';
    echo '<p class="description" style="margin-left: 25px; margin-bottom: 10px;">' . __('One question per page with navigation buttons.', 'mcqhome') . '</p>';
    echo '<label><input type="radio" name="mcq_set_display_format" value="single_page"' . checked($display_format, 'single_page', false) . '> ' . __('Single Page Format', 'mcqhome') . '</label><br>';
    echo '<p class="description" style="margin-left: 25px;">' . __('All questions displayed on one scrollable page organized by sections.', 'mcqhome') . '</p>';
    echo '</fieldset>';
    echo '</td>';
    echo '</tr>';
    
    // Result display options
    echo '<tr>';
    echo '<th scope="row">' . __('Result Display', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="mcq_set_show_results_immediately" value="1"' . checked($show_results_immediately, true, false) . '> ' . __('Show results immediately after submission', 'mcqhome') . '</label>';
    echo '<p class="description">' . __('If unchecked, results will be shown only after manual review.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Additional options
    echo '<tr>';
    echo '<th scope="row">' . __('Additional Options', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="mcq_set_allow_retakes" value="1"' . checked($allow_retakes, true, false) . '> ' . __('Allow retakes', 'mcqhome') . '</label><br>';
    echo '<label><input type="checkbox" name="mcq_set_shuffle_questions" value="1"' . checked($shuffle_questions, true, false) . '> ' . __('Shuffle questions for each attempt', 'mcqhome') . '</label>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
}

/**
 * MCQ Set Sections meta box callback
 */
function mcqhome_mcq_set_sections_callback($post) {
    $sections_enabled = get_post_meta($post->ID, '_mcq_set_sections_enabled', true) === '1';
    $sections = get_post_meta($post->ID, '_mcq_set_sections', true);
    if (!is_array($sections)) {
        $sections = [];
    }
    
    echo '<div class="mcq-sections-container">';
    
    // Enable/Disable sections toggle
    echo '<div class="sections-toggle">';
    echo '<label><input type="checkbox" name="mcq_set_sections_enabled" value="1" id="sections-enabled-toggle"' . checked($sections_enabled, true, false) . '> ' . __('Enable exam sections for this MCQ set', 'mcqhome') . '</label>';
    echo '<p class="description">' . __('Sections allow you to organize questions into topics (e.g., Reading, Writing, Listening). Leave unchecked for simple MCQ sets without sections.', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Sections management (hidden by default if sections not enabled)
    echo '<div id="sections-management" style="' . ($sections_enabled ? '' : 'display: none;') . '">';
    echo '<h4>' . __('Manage Sections', 'mcqhome') . '</h4>';
    
    echo '<div id="sections-list">';
    if (!empty($sections)) {
        foreach ($sections as $index => $section) {
            echo '<div class="section-item" data-index="' . $index . '">';
            echo '<div class="section-header">';
            echo '<span class="section-handle">⋮⋮</span>';
            echo '<input type="text" name="mcq_set_sections[' . $index . '][name]" value="' . esc_attr($section['name']) . '" placeholder="' . __('Section Name', 'mcqhome') . '" class="section-name-input">';
            echo '<button type="button" class="button remove-section">' . __('Remove', 'mcqhome') . '</button>';
            echo '</div>';
            echo '<div class="section-description">';
            echo '<textarea name="mcq_set_sections[' . $index . '][description]" placeholder="' . __('Section Description (optional)', 'mcqhome') . '" class="section-description-input">' . esc_textarea($section['description']) . '</textarea>';
            echo '</div>';
            echo '<input type="hidden" name="mcq_set_sections[' . $index . '][id]" value="' . esc_attr($section['id']) . '">';
            echo '<input type="hidden" name="mcq_set_sections[' . $index . '][order]" value="' . esc_attr($section['order']) . '" class="section-order">';
            echo '</div>';
        }
    }
    echo '</div>';
    
    echo '<button type="button" id="add-section" class="button">' . __('Add Section', 'mcqhome') . '</button>';
    echo '</div>';
    
    echo '</div>';
    
    // CSS and JavaScript for sections management
    echo '<style>
        .mcq-sections-container { margin-top: 10px; }
        .sections-toggle { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #ddd; }
        .section-item { border: 1px solid #ddd; margin-bottom: 10px; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .section-handle { cursor: move; color: #666; font-size: 16px; }
        .section-name-input { flex: 1; }
        .section-description-input { width: 100%; rows: 2; }
        .remove-section { background: #dc3232; color: white; border: none; }
        .remove-section:hover { background: #a00; }
        #sections-list { margin-bottom: 15px; }
        .ui-sortable-helper { background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>';
    
    echo '<script>
    jQuery(document).ready(function($) {
        var sectionIndex = ' . count($sections) . ';
        
        // Toggle sections management
        $("#sections-enabled-toggle").on("change", function() {
            if ($(this).is(":checked")) {
                $("#sections-management").show();
            } else {
                $("#sections-management").hide();
            }
        });
        
        // Add new section
        $("#add-section").on("click", function() {
            var sectionId = "section_" + Date.now();
            var sectionHtml = 
                "<div class=\"section-item\" data-index=\"" + sectionIndex + "\">" +
                "<div class=\"section-header\">" +
                "<span class=\"section-handle\">⋮⋮</span>" +
                "<input type=\"text\" name=\"mcq_set_sections[" + sectionIndex + "][name]\" placeholder=\"' . __('Section Name', 'mcqhome') . '\" class=\"section-name-input\">" +
                "<button type=\"button\" class=\"button remove-section\">' . __('Remove', 'mcqhome') . '</button>" +
                "</div>" +
                "<div class=\"section-description\">" +
                "<textarea name=\"mcq_set_sections[" + sectionIndex + "][description]\" placeholder=\"' . __('Section Description (optional)', 'mcqhome') . '\" class=\"section-description-input\"></textarea>" +
                "</div>" +
                "<input type=\"hidden\" name=\"mcq_set_sections[" + sectionIndex + "][id]\" value=\"" + sectionId + "\">" +
                "<input type=\"hidden\" name=\"mcq_set_sections[" + sectionIndex + "][order]\" value=\"" + (sectionIndex + 1) + "\" class=\"section-order\">" +
                "</div>";
            
            $("#sections-list").append(sectionHtml);
            sectionIndex++;
            updateSectionOrder();
        });
        
        // Remove section
        $(document).on("click", ".remove-section", function() {
            $(this).closest(".section-item").remove();
            updateSectionOrder();
        });
        
        // Make sections sortable
        $("#sections-list").sortable({
            handle: ".section-handle",
            update: function() {
                updateSectionOrder();
            }
        });
        
        function updateSectionOrder() {
            $("#sections-list .section-item").each(function(index) {
                $(this).find(".section-order").val(index + 1);
            });
        }
    });
    </script>';
}

/**
 * MCQ Set Questions meta box callback
 */
function mcqhome_mcq_set_questions_callback($post) {
    wp_nonce_field('mcqhome_save_mcq_set_meta', 'mcqhome_mcq_set_meta_nonce');
    
    $questions_order = get_post_meta($post->ID, '_mcq_set_questions_order', true);
    if (!is_array($questions_order)) {
        $questions_order = ['questions' => []];
    }
    
    $sections_enabled = get_post_meta($post->ID, '_mcq_set_sections_enabled', true) === '1';
    $sections = get_post_meta($post->ID, '_mcq_set_sections', true);
    if (!is_array($sections)) {
        $sections = [];
    }
    
    // Get all available MCQs
    $mcqs = get_posts([
        'post_type' => 'mcq',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    // Get currently selected MCQ IDs for easier checking
    $selected_mcq_ids = array_column($questions_order['questions'], 'mcq_id');
    
    echo '<style>
        .mcq-selection-container { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; }
        .mcq-item { display: flex; align-items: flex-start; margin-bottom: 10px; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
        .mcq-item:hover { background: #f9f9f9; }
        .mcq-item input[type="checkbox"] { margin-right: 10px; margin-top: 2px; }
        .mcq-item-content { flex: 1; }
        .mcq-item-title { font-weight: bold; margin-bottom: 5px; }
        .mcq-item-preview { color: #666; font-size: 0.9em; }
        .mcq-item-meta { color: #999; font-size: 0.8em; margin-top: 5px; }
        .mcq-item-section { margin-top: 5px; }
        .mcq-item-section select { width: 100%; }
        .mcq-selected-count { background: #0073aa; color: white; padding: 5px 10px; border-radius: 3px; margin-bottom: 15px; display: inline-block; }
        .mcq-search-box { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .mcq-filter-buttons { margin-bottom: 15px; }
        .mcq-filter-buttons button { margin-right: 10px; padding: 5px 10px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 3px; }
        .mcq-filter-buttons button.active { background: #0073aa; color: white; }
        .sections-notice { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>';
    
    echo '<div class="mcq-selection-wrapper">';
    
    // Show sections notice if sections are enabled
    if ($sections_enabled && !empty($sections)) {
        echo '<div class="sections-notice">';
        echo '<strong>' . __('Sections Enabled:', 'mcqhome') . '</strong> ';
        echo __('You can assign questions to specific sections below. Questions without a section assignment will be grouped together.', 'mcqhome');
        echo '</div>';
    }
    
    echo '<div class="mcq-selected-count">' . sprintf(__('Selected: %d questions', 'mcqhome'), count($selected_mcq_ids)) . '</div>';
    
    // Search and filter controls
    echo '<input type="text" class="mcq-search-box" placeholder="' . __('Search questions...', 'mcqhome') . '" id="mcq-search">';
    
    echo '<div class="mcq-filter-buttons">';
    echo '<button type="button" class="mcq-filter-btn active" data-filter="all">' . __('All', 'mcqhome') . '</button>';
    echo '<button type="button" class="mcq-filter-btn" data-filter="selected">' . __('Selected', 'mcqhome') . '</button>';
    echo '<button type="button" class="mcq-filter-btn" data-filter="unselected">' . __('Unselected', 'mcqhome') . '</button>';
    echo '</div>';
    
    echo '<div class="mcq-selection-container" id="mcq-selection-container">';
    
    foreach ($mcqs as $mcq) {
        $question_text = get_post_meta($mcq->ID, '_mcq_question_text', true);
        $correct_answer = get_post_meta($mcq->ID, '_mcq_correct_answer', true);
        $subjects = get_the_terms($mcq->ID, 'mcq_subject');
        $difficulty = get_the_terms($mcq->ID, 'mcq_difficulty');
        
        $is_selected = in_array($mcq->ID, $selected_mcq_ids);
        
        // Find current section assignment for this MCQ
        $current_section = null;
        foreach ($questions_order['questions'] as $question_data) {
            if ($question_data['mcq_id'] == $mcq->ID) {
                $current_section = $question_data['section_id'];
                break;
            }
        }
        
        echo '<div class="mcq-item" data-selected="' . ($is_selected ? 'true' : 'false') . '">';
        echo '<input type="checkbox" name="mcq_set_questions[]" value="' . $mcq->ID . '" id="mcq_' . $mcq->ID . '"' . checked($is_selected, true, false) . '>';
        echo '<div class="mcq-item-content">';
        echo '<div class="mcq-item-title">' . esc_html($mcq->post_title) . '</div>';
        
        if ($question_text) {
            echo '<div class="mcq-item-preview">' . wp_trim_words(strip_tags($question_text), 20, '...') . '</div>';
        }
        
        echo '<div class="mcq-item-meta">';
        if ($subjects && !is_wp_error($subjects)) {
            echo __('Subject:', 'mcqhome') . ' ' . implode(', ', wp_list_pluck($subjects, 'name')) . ' | ';
        }
        if ($difficulty && !is_wp_error($difficulty)) {
            echo __('Difficulty:', 'mcqhome') . ' ' . implode(', ', wp_list_pluck($difficulty, 'name')) . ' | ';
        }
        if ($correct_answer) {
            echo __('Correct Answer:', 'mcqhome') . ' ' . $correct_answer;
        }
        echo '</div>';
        
        // Section assignment (only show if sections are enabled)
        if ($sections_enabled && !empty($sections)) {
            echo '<div class="mcq-item-section">';
            echo '<label for="mcq_section_' . $mcq->ID . '">' . __('Assign to Section:', 'mcqhome') . '</label>';
            echo '<select name="mcq_question_sections[' . $mcq->ID . ']" id="mcq_section_' . $mcq->ID . '" class="mcq-section-select">';
            echo '<option value="">' . __('No Section', 'mcqhome') . '</option>';
            
            foreach ($sections as $section) {
                $selected = ($current_section === $section['id']) ? 'selected' : '';
                echo '<option value="' . esc_attr($section['id']) . '" ' . $selected . '>' . esc_html($section['name']) . '</option>';
            }
            
            echo '</select>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // JavaScript for search and filter functionality
    echo '<script>
    jQuery(document).ready(function($) {
        var $container = $("#mcq-selection-container");
        var $items = $container.find(".mcq-item");
        var $searchBox = $("#mcq-search");
        var $filterBtns = $(".mcq-filter-btn");
        var $selectedCount = $(".mcq-selected-count");
        
        function updateSelectedCount() {
            var count = $container.find("input[type=checkbox]:checked").length;
            $selectedCount.text("' . __('Selected:', 'mcqhome') . ' " + count + " ' . __('questions', 'mcqhome') . '");
        }
        
        function filterItems() {
            var searchTerm = $searchBox.val().toLowerCase();
            var activeFilter = $filterBtns.filter(".active").data("filter");
            
            $items.each(function() {
                var $item = $(this);
                var text = $item.text().toLowerCase();
                var isSelected = $item.find("input[type=checkbox]").is(":checked");
                var matchesSearch = text.indexOf(searchTerm) !== -1;
                var matchesFilter = activeFilter === "all" || 
                                  (activeFilter === "selected" && isSelected) || 
                                  (activeFilter === "unselected" && !isSelected);
                
                $item.toggle(matchesSearch && matchesFilter);
            });
        }
        
        $searchBox.on("input", filterItems);
        
        $filterBtns.on("click", function() {
            $filterBtns.removeClass("active");
            $(this).addClass("active");
            filterItems();
        });
        
        $container.on("change", "input[type=checkbox]", function() {
            updateSelectedCount();
            $(this).closest(".mcq-item").attr("data-selected", $(this).is(":checked") ? "true" : "false");
            if ($filterBtns.filter(".active").data("filter") !== "all") {
                filterItems();
            }
        });
        
        updateSelectedCount();
    });
    </script>';
}

/**
 * MCQ Set Scoring Configuration meta box callback
 */
function mcqhome_mcq_set_scoring_callback($post) {
    $marks_per_question = get_post_meta($post->ID, '_mcq_set_marks_per_question', true) ?: 1;
    $negative_marking = get_post_meta($post->ID, '_mcq_set_negative_marking', true) ?: 0;
    $total_marks = get_post_meta($post->ID, '_mcq_set_total_marks', true) ?: 0;
    $passing_marks = get_post_meta($post->ID, '_mcq_set_passing_marks', true) ?: 0;
    $individual_marks = get_post_meta($post->ID, '_mcq_set_individual_marks', true);
    if (!is_array($individual_marks)) {
        $individual_marks = [];
    }
    
    echo '<table class="form-table">';
    
    // Default marks per question
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_marks_per_question">' . __('Default Marks per Question', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="mcq_set_marks_per_question" id="mcq_set_marks_per_question" value="' . esc_attr($marks_per_question) . '" min="0" step="0.5" class="small-text">';
    echo '<p class="description">' . __('Default marks awarded for each correct answer. You can set individual marks below.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Negative marking
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_negative_marking">' . __('Negative Marking', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="mcq_set_negative_marking" id="mcq_set_negative_marking" value="' . esc_attr($negative_marking) . '" min="0" step="0.25" class="small-text">';
    echo '<p class="description">' . __('Marks deducted for each incorrect answer. Set to 0 for no negative marking.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Total marks (calculated automatically)
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_total_marks">' . __('Total Marks', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="mcq_set_total_marks" id="mcq_set_total_marks" value="' . esc_attr($total_marks) . '" min="0" step="0.5" class="small-text" readonly>';
    echo '<p class="description">' . __('Total marks for this set (calculated automatically based on selected questions and their individual marks).', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Passing marks
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_passing_marks">' . __('Passing Marks', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="mcq_set_passing_marks" id="mcq_set_passing_marks" value="' . esc_attr($passing_marks) . '" min="0" step="0.5" class="small-text">';
    echo '<p class="description">' . __('Minimum marks required to pass this MCQ set.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    // Individual question marks section
    echo '<h4>' . __('Individual Question Marks', 'mcqhome') . '</h4>';
    echo '<p>' . __('Set specific marks for individual questions. Leave blank to use default marks.', 'mcqhome') . '</p>';
    echo '<div id="individual-marks-container">';
    echo '<p><em>' . __('Select questions above to configure individual marks.', 'mcqhome') . '</em></p>';
    echo '</div>';
    
    // JavaScript for dynamic individual marks
    echo '<script>
    // Make individual marks data available globally
    window.mcqSetIndividualMarks = ' . json_encode($individual_marks) . ';
    
    jQuery(document).ready(function($) {
        function updateIndividualMarks() {
            var selectedMCQs = $("input[name=\'mcq_set_questions[]\']:checked");
            var container = $("#individual-marks-container");
            var defaultMarks = parseFloat($("#mcq_set_marks_per_question").val()) || 1;
            
            if (selectedMCQs.length === 0) {
                container.html("<p><em>' . __('Select questions above to configure individual marks.', 'mcqhome') . '</em></p>");
                return;
            }
            
            var html = "<table class=\"widefat\"><thead><tr><th>' . __('Question', 'mcqhome') . '</th><th>' . __('Marks', 'mcqhome') . '</th></tr></thead><tbody>";
            
            selectedMCQs.each(function() {
                var mcqId = $(this).val();
                var mcqTitle = $(this).closest(".mcq-item").find(".mcq-item-title").text();
                var currentMarks = window.mcqSetIndividualMarks && window.mcqSetIndividualMarks[mcqId] ? window.mcqSetIndividualMarks[mcqId] : defaultMarks;
                
                html += "<tr>";
                html += "<td>" + mcqTitle + "</td>";
                html += "<td><input type=\"number\" name=\"mcq_set_individual_marks[" + mcqId + "]\" value=\"" + currentMarks + "\" min=\"0\" step=\"0.5\" class=\"small-text individual-marks-input\"></td>";
                html += "</tr>";
            });
            
            html += "</tbody></table>";
            container.html(html);
            
            calculateTotalMarks();
        }
        
        function calculateTotalMarks() {
            var total = 0;
            $(".individual-marks-input").each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $("#mcq_set_total_marks").val(total);
        }
        
        // Update individual marks when questions are selected/deselected
        $(document).on("change", "input[name=\'mcq_set_questions[]\']", updateIndividualMarks);
        
        // Update total marks when individual marks change
        $(document).on("input", ".individual-marks-input", calculateTotalMarks);
        
        // Update individual marks when default marks change
        $("#mcq_set_marks_per_question").on("input", function() {
            var newDefault = parseFloat($(this).val()) || 1;
            $(".individual-marks-input").each(function() {
                if (!$(this).data("manually-set")) {
                    $(this).val(newDefault);
                }
            });
            calculateTotalMarks();
        });
        
        // Mark individual inputs as manually set when changed
        $(document).on("input", ".individual-marks-input", function() {
            $(this).data("manually-set", true);
        });
        
        // Initial update
        updateIndividualMarks();
    });
    </script>';
}

/**
 * MCQ Set Display & Settings meta box callback
 */
function mcqhome_mcq_set_display_callback($post) {
    $display_format = get_post_meta($post->ID, '_mcq_set_display_format', true) ?: 'next_next';
    $time_limit = get_post_meta($post->ID, '_mcq_set_time_limit', true) ?: 0;
    $show_results_immediately = get_post_meta($post->ID, '_mcq_set_show_results_immediately', true) !== '0';
    $allow_retakes = get_post_meta($post->ID, '_mcq_set_allow_retakes', true) !== '0';
    $shuffle_questions = get_post_meta($post->ID, '_mcq_set_shuffle_questions', true) === '1';
    
    echo '<table class="form-table">';
    
    // Display format
    echo '<tr>';
    echo '<th scope="row">' . __('Display Format', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<fieldset>';
    echo '<label><input type="radio" name="mcq_set_display_format" value="next_next"' . checked($display_format, 'next_next', false) . '> ' . __('Next-Next Format', 'mcqhome') . '</label><br>';
    echo '<p class="description" style="margin-left: 25px;">' . __('One question per page with navigation buttons.', 'mcqhome') . '</p>';
    echo '<label><input type="radio" name="mcq_set_display_format" value="single_page"' . checked($display_format, 'single_page', false) . '> ' . __('Single Page Format', 'mcqhome') . '</label><br>';
    echo '<p class="description" style="margin-left: 25px;">' . __('All questions displayed on one scrollable page.', 'mcqhome') . '</p>';
    echo '</fieldset>';
    echo '</td>';
    echo '</tr>';
    
    // Time limit
    echo '<tr>';
    echo '<th scope="row"><label for="mcq_set_time_limit">' . __('Time Limit (minutes)', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="mcq_set_time_limit" id="mcq_set_time_limit" value="' . esc_attr($time_limit) . '" min="0" class="small-text">';
    echo '<p class="description">' . __('Time limit in minutes. Set to 0 for no time limit.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Show results immediately
    echo '<tr>';
    echo '<th scope="row">' . __('Results Display', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="mcq_set_show_results_immediately" value="1"' . checked($show_results_immediately, true, false) . '> ' . __('Show results immediately after submission', 'mcqhome') . '</label>';
    echo '<p class="description">' . __('If unchecked, results will be shown only after manual review.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Allow retakes
    echo '<tr>';
    echo '<th scope="row">' . __('Retakes', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="mcq_set_allow_retakes" value="1"' . checked($allow_retakes, true, false) . '> ' . __('Allow students to retake this MCQ set', 'mcqhome') . '</label>';
    echo '<p class="description">' . __('Students can attempt this set multiple times if enabled.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Shuffle questions
    echo '<tr>';
    echo '<th scope="row">' . __('Question Order', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="mcq_set_shuffle_questions" value="1"' . checked($shuffle_questions, true, false) . '> ' . __('Shuffle questions for each attempt', 'mcqhome') . '</label>';
    echo '<p class="description">' . __('Questions will appear in random order for each student.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
}

/**
 * MCQ Set Pricing & Publication meta box callback
 */
function mcqhome_mcq_set_pricing_callback($post) {
    $pricing_type = get_post_meta($post->ID, '_mcq_set_pricing_type', true) ?: 'free';
    $price = get_post_meta($post->ID, '_mcq_set_price', true) ?: 0;
    $featured = get_post_meta($post->ID, '_mcq_set_featured', true) === '1';
    
    echo '<div class="misc-pub-section">';
    echo '<h4>' . __('Pricing', 'mcqhome') . '</h4>';
    
    echo '<p><label><input type="radio" name="mcq_set_pricing_type" value="free"' . checked($pricing_type, 'free', false) . '> ' . __('Free', 'mcqhome') . '</label></p>';
    echo '<p><label><input type="radio" name="mcq_set_pricing_type" value="paid"' . checked($pricing_type, 'paid', false) . '> ' . __('Paid', 'mcqhome') . '</label></p>';
    
    echo '<div id="price-field" style="' . ($pricing_type === 'free' ? 'display:none;' : '') . '">';
    echo '<p><label for="mcq_set_price">' . __('Price:', 'mcqhome') . '</label><br>';
    echo '<input type="number" name="mcq_set_price" id="mcq_set_price" value="' . esc_attr($price) . '" min="0" step="0.01" class="widefat"></p>';
    echo '</div>';
    
    echo '</div>';
    
    echo '<div class="misc-pub-section">';
    echo '<h4>' . __('Visibility', 'mcqhome') . '</h4>';
    echo '<p><label><input type="checkbox" name="mcq_set_featured" value="1"' . checked($featured, true, false) . '> ' . __('Featured MCQ Set', 'mcqhome') . '</label></p>';
    echo '<p class="description">' . __('Featured sets appear prominently on the homepage and browse pages.', 'mcqhome') . '</p>';
    echo '</div>';
    
    echo '<script>
    jQuery(document).ready(function($) {
        $("input[name=mcq_set_pricing_type]").on("change", function() {
            if ($(this).val() === "paid") {
                $("#price-field").show();
            } else {
                $("#price-field").hide();
                $("#mcq_set_price").val(0);
            }
        });
    });
    </script>';
}

/**
 * Save MCQ Set meta data
 */
function mcqhome_save_mcq_set_meta($post_id) {
    // Check if nonce is valid
    if (!isset($_POST['mcqhome_mcq_set_meta_nonce']) || !wp_verify_nonce($_POST['mcqhome_mcq_set_meta_nonce'], 'mcqhome_save_mcq_set_meta')) {
        return;
    }

    // Check if user has permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if not an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check post type
    if (get_post_type($post_id) !== 'mcq_set') {
        return;
    }

    // Save enhanced MCQ Set meta fields according to new schema
    
    // Save thumbnail
    if (isset($_POST['mcq_set_thumbnail'])) {
        update_post_meta($post_id, '_mcq_set_thumbnail', intval($_POST['mcq_set_thumbnail']));
    }

    // Save pricing configuration
    if (isset($_POST['mcq_set_pricing_type']) && in_array($_POST['mcq_set_pricing_type'], ['free', 'paid'])) {
        update_post_meta($post_id, '_mcq_set_pricing_type', sanitize_text_field($_POST['mcq_set_pricing_type']));
    }

    if (isset($_POST['mcq_set_price'])) {
        update_post_meta($post_id, '_mcq_set_price', floatval($_POST['mcq_set_price']));
    }

    // Save assessment configuration
    if (isset($_POST['mcq_set_pass_marks'])) {
        update_post_meta($post_id, '_mcq_set_pass_marks', floatval($_POST['mcq_set_pass_marks']));
    }

    if (isset($_POST['mcq_set_time_limit'])) {
        update_post_meta($post_id, '_mcq_set_time_limit', intval($_POST['mcq_set_time_limit']));
    }

    // Save display format
    if (isset($_POST['mcq_set_display_format']) && in_array($_POST['mcq_set_display_format'], ['next_next', 'single_page'])) {
        update_post_meta($post_id, '_mcq_set_display_format', sanitize_text_field($_POST['mcq_set_display_format']));
    }

    // Save sections configuration
    $sections_enabled = isset($_POST['mcq_set_sections_enabled']) && $_POST['mcq_set_sections_enabled'] === '1';
    update_post_meta($post_id, '_mcq_set_sections_enabled', $sections_enabled ? '1' : '0');

    // Save sections data (optional - can be empty array)
    $sections = [];
    if ($sections_enabled && isset($_POST['mcq_set_sections']) && is_array($_POST['mcq_set_sections'])) {
        foreach ($_POST['mcq_set_sections'] as $section_data) {
            if (!empty($section_data['name'])) {
                $sections[] = [
                    'id' => sanitize_text_field($section_data['id']),
                    'name' => sanitize_text_field($section_data['name']),
                    'description' => sanitize_textarea_field($section_data['description']),
                    'order' => intval($section_data['order'])
                ];
            }
        }
    }
    update_post_meta($post_id, '_mcq_set_sections', json_encode($sections));

    // Save questions order with section assignments
    $questions_order = ['questions' => []];
    if (isset($_POST['mcq_set_questions']) && is_array($_POST['mcq_set_questions'])) {
        $order = 1;
        foreach ($_POST['mcq_set_questions'] as $mcq_id) {
            $section_id = null;
            
            // Check if section assignment exists for this question
            if (isset($_POST['mcq_question_sections'][$mcq_id])) {
                $section_id = sanitize_text_field($_POST['mcq_question_sections'][$mcq_id]);
                // If sections are disabled or section_id is empty, set to null
                if (!$sections_enabled || empty($section_id)) {
                    $section_id = null;
                }
            }
            
            $questions_order['questions'][] = [
                'mcq_id' => intval($mcq_id),
                'section_id' => $section_id,
                'order' => $order++
            ];
        }
    }
    update_post_meta($post_id, '_mcq_set_questions_order', json_encode($questions_order));

    // Save legacy selected MCQs for backward compatibility
    if (isset($_POST['mcq_set_questions']) && is_array($_POST['mcq_set_questions'])) {
        $selected_mcqs = array_map('intval', $_POST['mcq_set_questions']);
        update_post_meta($post_id, '_mcq_set_questions', $selected_mcqs);
    } else {
        update_post_meta($post_id, '_mcq_set_questions', []);
    }

    // Save scoring configuration
    $scoring_fields = [
        'mcq_set_marks_per_question' => 'floatval',
        'mcq_set_negative_marking' => 'floatval',
        'mcq_set_total_marks' => 'floatval',
        'mcq_set_passing_marks' => 'floatval'
    ];

    foreach ($scoring_fields as $field => $sanitize_func) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, $sanitize_func($_POST[$field]));
        }
    }

    // Save individual marks
    if (isset($_POST['mcq_set_individual_marks']) && is_array($_POST['mcq_set_individual_marks'])) {
        $individual_marks = [];
        foreach ($_POST['mcq_set_individual_marks'] as $mcq_id => $marks) {
            $individual_marks[intval($mcq_id)] = floatval($marks);
        }
        update_post_meta($post_id, '_mcq_set_individual_marks', $individual_marks);
    }

    // Save boolean settings
    $boolean_fields = [
        'mcq_set_show_results_immediately',
        'mcq_set_allow_retakes',
        'mcq_set_shuffle_questions',
        'mcq_set_featured'
    ];

    foreach ($boolean_fields as $field) {
        $value = isset($_POST[$field]) && $_POST[$field] === '1' ? '1' : '0';
        update_post_meta($post_id, '_' . $field, $value);
    }

    // Save author assignment
    if (isset($_POST['mcq_set_author'])) {
        update_post_meta($post_id, '_mcq_set_author', intval($_POST['mcq_set_author']));
    }
}
add_action('save_post', 'mcqhome_save_mcq_set_meta');

/**
 * Customize MCQ Set admin columns
 */
function mcqhome_mcq_set_admin_columns($columns) {
    $new_columns = [];
    
    // Add checkbox and title
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    
    // Add custom columns with enhanced information
    $new_columns['mcq_count'] = __('Questions', 'mcqhome');
    $new_columns['sections_info'] = __('Sections', 'mcqhome');
    $new_columns['total_marks'] = __('Total Marks', 'mcqhome');
    $new_columns['pricing'] = __('Pricing', 'mcqhome');
    $new_columns['display_format'] = __('Format', 'mcqhome');
    $new_columns['featured'] = __('Featured', 'mcqhome');
    $new_columns['author'] = $columns['author'];
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}
add_filter('manage_mcq_set_posts_columns', 'mcqhome_mcq_set_admin_columns');

/**
 * Populate custom MCQ Set admin columns
 */
function mcqhome_mcq_set_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'mcq_count':
            $questions = get_post_meta($post_id, '_mcq_set_questions', true);
            $count = is_array($questions) ? count($questions) : 0;
            
            // Get questions organized by section for more detailed info
            $questions_by_section = mcqhome_get_mcq_set_questions_by_section($post_id);
            $section_count = count($questions_by_section);
            
            echo '<strong>' . $count . '</strong> ' . _n('question', 'questions', $count, 'mcqhome');
            
            if ($section_count > 1) {
                echo '<br><small style="color: #666;">';
                echo sprintf(_n('%d section', '%d sections', $section_count, 'mcqhome'), $section_count);
                echo '</small>';
            }
            
            // Add quick edit link for questions
            if ($count > 0) {
                $edit_url = add_query_arg([
                    'post' => $post_id,
                    'action' => 'edit',
                    'tab' => 'questions'
                ], admin_url('post.php'));
                echo '<br><a href="' . esc_url($edit_url) . '" style="font-size: 11px;">' . __('Manage Questions', 'mcqhome') . '</a>';
            }
            break;
            
        case 'sections_info':
            $sections_enabled = get_post_meta($post_id, '_mcq_set_sections_enabled', true);
            $sections = mcqhome_get_mcq_set_sections($post_id);
            
            if ($sections_enabled === '1' && !empty($sections)) {
                echo '<strong>' . count($sections) . '</strong> ' . _n('section', 'sections', count($sections), 'mcqhome');
                echo '<br><small style="color: #666;">';
                $section_names = array_slice(array_column($sections, 'name'), 0, 2);
                echo esc_html(implode(', ', $section_names));
                if (count($sections) > 2) {
                    echo ', +' . (count($sections) - 2) . ' ' . __('more', 'mcqhome');
                }
                echo '</small>';
            } else {
                echo '<span style="color: #999;">' . __('No sections', 'mcqhome') . '</span>';
            }
            break;
            
        case 'total_marks':
            $questions = get_post_meta($post_id, '_mcq_set_questions', true);
            $question_count = is_array($questions) ? count($questions) : 0;
            $total_marks = $question_count; // Assuming 1 mark per question
            
            $pass_marks = get_post_meta($post_id, '_mcq_set_pass_marks', true);
            
            echo '<strong>' . $total_marks . '</strong> ' . _n('mark', 'marks', $total_marks, 'mcqhome');
            if ($pass_marks) {
                echo '<br><small style="color: #666;">' . sprintf(__('Pass: %s', 'mcqhome'), $pass_marks) . '</small>';
            }
            break;
            
        case 'pricing':
            $pricing_type = get_post_meta($post_id, '_mcq_set_pricing_type', true);
            if ($pricing_type === 'paid') {
                $price = get_post_meta($post_id, '_mcq_set_price', true);
                echo '<span style="color: #d63638; font-weight: bold;">$' . number_format($price, 2) . '</span>';
            } else {
                echo '<span style="color: #00a32a; font-weight: bold;">' . __('Free', 'mcqhome') . '</span>';
            }
            break;
            
        case 'display_format':
            $format = get_post_meta($post_id, '_mcq_set_display_format', true);
            if ($format === 'single_page') {
                echo '<span style="color: #0073aa;">' . __('Single Page', 'mcqhome') . '</span>';
            } else {
                echo '<span style="color: #00a32a;">' . __('Next-Next', 'mcqhome') . '</span>';
            }
            
            // Add time limit info if set
            $time_limit = get_post_meta($post_id, '_mcq_set_time_limit', true);
            if ($time_limit) {
                echo '<br><small style="color: #666;">' . sprintf(__('%d min', 'mcqhome'), $time_limit) . '</small>';
            }
            break;
            
        case 'featured':
            $featured = get_post_meta($post_id, '_mcq_set_featured', true);
            if ($featured === '1') {
                echo '<span style="color: #d63638; font-size: 16px;">★</span>';
            } else {
                echo '<span style="color: #ddd; font-size: 16px;">☆</span>';
            }
            break;
    }
}
add_action('manage_mcq_set_posts_custom_column', 'mcqhome_mcq_set_admin_column_content', 10, 2);

/**
 * Helper Functions for Enhanced MCQ Set Architecture
 */

/**
 * Get MCQ Set sections
 */
function mcqhome_get_mcq_set_sections($mcq_set_id) {
    $sections = get_post_meta($mcq_set_id, '_mcq_set_sections', true);
    if (empty($sections)) {
        return [];
    }
    
    $decoded = json_decode($sections, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Get MCQ Set questions organized by section
 */
function mcqhome_get_mcq_set_questions_by_section($mcq_set_id) {
    $questions_order = get_post_meta($mcq_set_id, '_mcq_set_questions_order', true);
    if (empty($questions_order)) {
        return [];
    }
    
    $decoded = json_decode($questions_order, true);
    if (!is_array($decoded) || !isset($decoded['questions'])) {
        return [];
    }
    
    $organized = [];
    foreach ($decoded['questions'] as $question_data) {
        $section_id = $question_data['section_id'] ?? 'default';
        
        if (!isset($organized[$section_id])) {
            $organized[$section_id] = [];
        }
        
        $organized[$section_id][] = [
            'mcq_id' => $question_data['mcq_id'],
            'order' => $question_data['order']
        ];
    }
    
    // Sort questions within each section by order
    foreach ($organized as $section_id => $questions) {
        usort($organized[$section_id], function($a, $b) {
            return $a['order'] - $b['order'];
        });
    }
    
    return $organized;
}

/**
 * Check if MCQ Set has sections enabled
 */
function mcqhome_mcq_set_has_sections($mcq_set_id) {
    return get_post_meta($mcq_set_id, '_mcq_set_sections_enabled', true) === '1';
}

/**
 * Get MCQ Set display format
 */
function mcqhome_get_mcq_set_display_format($mcq_set_id) {
    $format = get_post_meta($mcq_set_id, '_mcq_set_display_format', true);
    return in_array($format, ['next_next', 'single_page']) ? $format : 'next_next';
}

/**
 * Add section to MCQ Set
 */
function mcqhome_add_mcq_set_section($mcq_set_id, $name, $description = '') {
    $sections = mcqhome_get_mcq_set_sections($mcq_set_id);
    
    $new_section = [
        'id' => 'section_' . uniqid(),
        'name' => sanitize_text_field($name),
        'description' => sanitize_textarea_field($description),
        'order' => count($sections) + 1
    ];
    
    $sections[] = $new_section;
    
    update_post_meta($mcq_set_id, '_mcq_set_sections', json_encode($sections));
    
    return $new_section['id'];
}

/**
 * Add question to MCQ Set with optional section assignment
 */
function mcqhome_add_question_to_mcq_set($mcq_set_id, $mcq_id, $section_id = null) {
    $questions_order = get_post_meta($mcq_set_id, '_mcq_set_questions_order', true);
    if (empty($questions_order)) {
        $questions_order = ['questions' => []];
    } else {
        $questions_order = json_decode($questions_order, true);
        if (!is_array($questions_order) || !isset($questions_order['questions'])) {
            $questions_order = ['questions' => []];
        }
    }
    
    // Check if question already exists
    foreach ($questions_order['questions'] as $existing) {
        if ($existing['mcq_id'] == $mcq_id) {
            return false; // Question already exists in set
        }
    }
    
    // Add new question
    $questions_order['questions'][] = [
        'mcq_id' => intval($mcq_id),
        'section_id' => $section_id ?: '',
        'order' => count($questions_order['questions']) + 1
    ];
    
    // Update meta
    update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode($questions_order));
    
    // Update simple questions array for backward compatibility
    $question_ids = array_column($questions_order['questions'], 'mcq_id');
    update_post_meta($mcq_set_id, '_mcq_set_questions', $question_ids);
    
    return true;
}

/**
 * Remove question from all MCQ sets
 */
function mcqhome_remove_question_from_all_sets($mcq_id) {
    global $wpdb;
    
    // Find all MCQ sets that contain this question
    $mcq_sets = $wpdb->get_results($wpdb->prepare("
        SELECT post_id, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_mcq_set_questions_order' 
        AND meta_value LIKE %s
    ", '%"mcq_id":' . intval($mcq_id) . '%'));
    
    foreach ($mcq_sets as $set_data) {
        $questions_order = json_decode($set_data->meta_value, true);
        if (!is_array($questions_order) || !isset($questions_order['questions'])) {
            continue;
        }
        
        // Remove the question from the array
        $questions_order['questions'] = array_filter($questions_order['questions'], function($question) use ($mcq_id) {
            return $question['mcq_id'] != $mcq_id;
        });
        
        // Reorder remaining questions
        $questions_order['questions'] = array_values($questions_order['questions']);
        foreach ($questions_order['questions'] as $index => &$question) {
            $question['order'] = $index + 1;
        }
        
        // Update meta
        update_post_meta($set_data->post_id, '_mcq_set_questions_order', json_encode($questions_order));
        
        // Update simple questions array for backward compatibility
        $question_ids = array_column($questions_order['questions'], 'mcq_id');
        update_post_meta($set_data->post_id, '_mcq_set_questions', $question_ids);
    }
}

/**
 * Enhanced row classes for admin tables
 */
function mcqhome_admin_row_classes($classes, $post) {
    if ($post->post_type === 'mcq') {
        // Highlight orphaned MCQs (not assigned to any set)
        $mcq_set_id = mcqhome_find_mcq_set_for_question($post->ID);
        if (!$mcq_set_id) {
            $classes[] = 'mcq-admin-orphaned';
        }
    } elseif ($post->post_type === 'mcq_set') {
        // Highlight featured MCQ sets
        $featured = get_post_meta($post->ID, '_mcq_set_featured', true);
        if ($featured === '1') {
            $classes[] = 'mcq-set-admin-featured';
        }
    }
    
    return $classes;
}
add_filter('post_class', 'mcqhome_admin_row_classes', 10, 2);

/**
 * Add custom filters to MCQ admin page
 */
function mcqhome_add_mcq_admin_filters() {
    global $typenow;
    
    if ($typenow !== 'mcq') {
        return;
    }
    
    // MCQ Set filter
    $mcq_sets = get_posts([
        'post_type' => 'mcq_set',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    $selected_set = isset($_GET['mcq_set_filter']) ? $_GET['mcq_set_filter'] : '';
    
    echo '<select name="mcq_set_filter">';
    echo '<option value="">' . __('All MCQ Sets', 'mcqhome') . '</option>';
    echo '<option value="orphaned"' . selected($selected_set, 'orphaned', false) . '>' . __('Orphaned (No Set)', 'mcqhome') . '</option>';
    
    foreach ($mcq_sets as $set) {
        echo '<option value="' . $set->ID . '"' . selected($selected_set, $set->ID, false) . '>';
        echo esc_html($set->post_title);
        echo '</option>';
    }
    echo '</select>';
    
    // Subject filter
    $subjects = get_terms(['taxonomy' => 'mcq_subject', 'hide_empty' => false]);
    if (!empty($subjects)) {
        $selected_subject = isset($_GET['mcq_subject_filter']) ? $_GET['mcq_subject_filter'] : '';
        
        echo '<select name="mcq_subject_filter">';
        echo '<option value="">' . __('All Subjects', 'mcqhome') . '</option>';
        
        foreach ($subjects as $subject) {
            echo '<option value="' . $subject->term_id . '"' . selected($selected_subject, $subject->term_id, false) . '>';
            echo esc_html($subject->name);
            echo '</option>';
        }
        echo '</select>';
    }
    
    // Difficulty filter
    $difficulties = get_terms(['taxonomy' => 'mcq_difficulty', 'hide_empty' => false]);
    if (!empty($difficulties)) {
        $selected_difficulty = isset($_GET['mcq_difficulty_filter']) ? $_GET['mcq_difficulty_filter'] : '';
        
        echo '<select name="mcq_difficulty_filter">';
        echo '<option value="">' . __('All Difficulties', 'mcqhome') . '</option>';
        
        foreach ($difficulties as $difficulty) {
            echo '<option value="' . $difficulty->term_id . '"' . selected($selected_difficulty, $difficulty->term_id, false) . '>';
            echo esc_html($difficulty->name);
            echo '</option>';
        }
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'mcqhome_add_mcq_admin_filters');

/**
 * Add custom filters to MCQ Set admin page
 */
function mcqhome_add_mcq_set_admin_filters() {
    global $typenow;
    
    if ($typenow !== 'mcq_set') {
        return;
    }
    
    // Pricing filter
    $selected_pricing = isset($_GET['pricing_filter']) ? $_GET['pricing_filter'] : '';
    
    echo '<select name="pricing_filter">';
    echo '<option value="">' . __('All Pricing Types', 'mcqhome') . '</option>';
    echo '<option value="free"' . selected($selected_pricing, 'free', false) . '>' . __('Free', 'mcqhome') . '</option>';
    echo '<option value="paid"' . selected($selected_pricing, 'paid', false) . '>' . __('Paid', 'mcqhome') . '</option>';
    echo '</select>';
    
    // Format filter
    $selected_format = isset($_GET['format_filter']) ? $_GET['format_filter'] : '';
    
    echo '<select name="format_filter">';
    echo '<option value="">' . __('All Formats', 'mcqhome') . '</option>';
    echo '<option value="next_next"' . selected($selected_format, 'next_next', false) . '>' . __('Next-Next', 'mcqhome') . '</option>';
    echo '<option value="single_page"' . selected($selected_format, 'single_page', false) . '>' . __('Single Page', 'mcqhome') . '</option>';
    echo '</select>';
    
    // Featured filter
    $selected_featured = isset($_GET['featured_filter']) ? $_GET['featured_filter'] : '';
    
    echo '<select name="featured_filter">';
    echo '<option value="">' . __('All Sets', 'mcqhome') . '</option>';
    echo '<option value="1"' . selected($selected_featured, '1', false) . '>' . __('Featured Only', 'mcqhome') . '</option>';
    echo '<option value="0"' . selected($selected_featured, '0', false) . '>' . __('Not Featured', 'mcqhome') . '</option>';
    echo '</select>';
    
    // Sections filter
    $selected_sections = isset($_GET['sections_filter']) ? $_GET['sections_filter'] : '';
    
    echo '<select name="sections_filter">';
    echo '<option value="">' . __('All Sets', 'mcqhome') . '</option>';
    echo '<option value="with_sections"' . selected($selected_sections, 'with_sections', false) . '>' . __('With Sections', 'mcqhome') . '</option>';
    echo '<option value="no_sections"' . selected($selected_sections, 'no_sections', false) . '>' . __('No Sections', 'mcqhome') . '</option>';
    echo '</select>';
}
add_action('restrict_manage_posts', 'mcqhome_add_mcq_set_admin_filters');

/**
 * Handle custom MCQ admin filters
 */
function mcqhome_handle_mcq_admin_filters($query) {
    global $pagenow, $typenow;
    
    if ($pagenow !== 'edit.php' || !is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($typenow === 'mcq') {
        // MCQ Set filter
        if (isset($_GET['mcq_set_filter']) && !empty($_GET['mcq_set_filter'])) {
            if ($_GET['mcq_set_filter'] === 'orphaned') {
                // Show only orphaned MCQs
                $meta_query = $query->get('meta_query') ?: [];
                $meta_query[] = [
                    'key' => '_mcq_orphaned',
                    'compare' => 'NOT EXISTS'
                ];
                $query->set('meta_query', $meta_query);
                
                // Custom filter to exclude MCQs that are in sets
                add_filter('posts_where', 'mcqhome_filter_orphaned_mcqs');
            } else {
                // Show MCQs from specific set
                $set_id = intval($_GET['mcq_set_filter']);
                $questions = get_post_meta($set_id, '_mcq_set_questions', true);
                
                if (is_array($questions) && !empty($questions)) {
                    $query->set('post__in', $questions);
                } else {
                    $query->set('post__in', [0]); // No results
                }
            }
        }
        
        // Subject filter
        if (isset($_GET['mcq_subject_filter']) && !empty($_GET['mcq_subject_filter'])) {
            $tax_query = $query->get('tax_query') ?: [];
            $tax_query[] = [
                'taxonomy' => 'mcq_subject',
                'field' => 'term_id',
                'terms' => intval($_GET['mcq_subject_filter'])
            ];
            $query->set('tax_query', $tax_query);
        }
        
        // Difficulty filter
        if (isset($_GET['mcq_difficulty_filter']) && !empty($_GET['mcq_difficulty_filter'])) {
            $tax_query = $query->get('tax_query') ?: [];
            $tax_query[] = [
                'taxonomy' => 'mcq_difficulty',
                'field' => 'term_id',
                'terms' => intval($_GET['mcq_difficulty_filter'])
            ];
            $query->set('tax_query', $tax_query);
        }
    } elseif ($typenow === 'mcq_set') {
        $meta_query = $query->get('meta_query') ?: [];
        
        // Pricing filter
        if (isset($_GET['pricing_filter']) && !empty($_GET['pricing_filter'])) {
            $meta_query[] = [
                'key' => '_mcq_set_pricing_type',
                'value' => sanitize_text_field($_GET['pricing_filter']),
                'compare' => '='
            ];
        }
        
        // Format filter
        if (isset($_GET['format_filter']) && !empty($_GET['format_filter'])) {
            $meta_query[] = [
                'key' => '_mcq_set_display_format',
                'value' => sanitize_text_field($_GET['format_filter']),
                'compare' => '='
            ];
        }
        
        // Featured filter
        if (isset($_GET['featured_filter']) && $_GET['featured_filter'] !== '') {
            $meta_query[] = [
                'key' => '_mcq_set_featured',
                'value' => sanitize_text_field($_GET['featured_filter']),
                'compare' => '='
            ];
        }
        
        // Sections filter
        if (isset($_GET['sections_filter']) && !empty($_GET['sections_filter'])) {
            if ($_GET['sections_filter'] === 'with_sections') {
                $meta_query[] = [
                    'key' => '_mcq_set_sections_enabled',
                    'value' => '1',
                    'compare' => '='
                ];
            } elseif ($_GET['sections_filter'] === 'no_sections') {
                $meta_query[] = [
                    'relation' => 'OR',
                    [
                        'key' => '_mcq_set_sections_enabled',
                        'value' => '0',
                        'compare' => '='
                    ],
                    [
                        'key' => '_mcq_set_sections_enabled',
                        'compare' => 'NOT EXISTS'
                    ]
                ];
            }
        }
        
        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'mcqhome_handle_mcq_admin_filters');

/**
 * Filter orphaned MCQs (not assigned to any set)
 */
function mcqhome_filter_orphaned_mcqs($where) {
    global $wpdb;
    
    // Get all MCQ IDs that are assigned to sets
    $assigned_mcqs = $wpdb->get_col("
        SELECT DISTINCT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, '\"mcq_id\":', -1), ',', 1) AS UNSIGNED) as mcq_id
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_mcq_set_questions_order' 
        AND meta_value LIKE '%\"mcq_id\":%'
    ");
    
    if (!empty($assigned_mcqs)) {
        $assigned_ids = implode(',', array_map('intval', $assigned_mcqs));
        $where .= " AND {$wpdb->posts}.ID NOT IN ($assigned_ids)";
    }
    
    // Remove the filter to avoid affecting other queries
    remove_filter('posts_where', 'mcqhome_filter_orphaned_mcqs');
    
    return $where;
}
    
    $questions_order['questions'][] = [
        'mcq_id' => intval($mcq_id),
        'section_id' => $section_id,
        'order' => count($questions_order['questions']) + 1
    ];
    
    update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode($questions_order));
    
    // Update legacy questions array for backward compatibility
    $legacy_questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    if (!is_array($legacy_questions)) {
        $legacy_questions = [];
    }
    
    if (!in_array($mcq_id, $legacy_questions)) {
        $legacy_questions[] = intval($mcq_id);
        update_post_meta($mcq_set_id, '_mcq_set_questions', $legacy_questions);
    }
    
    return true;
}

/**
 * Remove question from MCQ Set
 */
function mcqhome_remove_question_from_mcq_set($mcq_set_id, $mcq_id) {
    $questions_order = get_post_meta($mcq_set_id, '_mcq_set_questions_order', true);
    if (empty($questions_order)) {
        return false;
    }
    
    $decoded = json_decode($questions_order, true);
    if (!is_array($decoded) || !isset($decoded['questions'])) {
        return false;
    }
    
    // Remove question from order structure
    $decoded['questions'] = array_filter($decoded['questions'], function($question) use ($mcq_id) {
        return $question['mcq_id'] != $mcq_id;
    });
    
    // Reorder remaining questions
    $order = 1;
    foreach ($decoded['questions'] as &$question) {
        $question['order'] = $order++;
    }
    
    update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode($decoded));
    
    // Update legacy questions array
    $legacy_questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
    if (is_array($legacy_questions)) {
        $legacy_questions = array_filter($legacy_questions, function($id) use ($mcq_id) {
            return $id != $mcq_id;
        });
        update_post_meta($mcq_set_id, '_mcq_set_questions', array_values($legacy_questions));
    }
    
    return true;
}

/**
 * Get total question count for MCQ Set
 */
function mcqhome_get_mcq_set_question_count($mcq_set_id) {
    $questions_order = get_post_meta($mcq_set_id, '_mcq_set_questions_order', true);
    if (empty($questions_order)) {
        return 0;
    }
    
    $decoded = json_decode($questions_order, true);
    if (!is_array($decoded) || !isset($decoded['questions'])) {
        return 0;
    }
    
    return count($decoded['questions']);
}

/**
 * Validate MCQ Set data integrity
 */
function mcqhome_validate_mcq_set_integrity($mcq_set_id) {
    $issues = [];
    
    // Check if sections JSON is valid
    $sections = get_post_meta($mcq_set_id, '_mcq_set_sections', true);
    if (!empty($sections) && json_decode($sections) === null) {
        $issues[] = 'Invalid sections JSON';
    }
    
    // Check if questions order JSON is valid
    $questions_order = get_post_meta($mcq_set_id, '_mcq_set_questions_order', true);
    if (!empty($questions_order) && json_decode($questions_order) === null) {
        $issues[] = 'Invalid questions order JSON';
    }
    
    // Check if all referenced MCQs exist
    if (!empty($questions_order)) {
        $decoded = json_decode($questions_order, true);
        if (is_array($decoded) && isset($decoded['questions'])) {
            foreach ($decoded['questions'] as $question_data) {
                $mcq_id = $question_data['mcq_id'];
                if (!get_post($mcq_id) || get_post_type($mcq_id) !== 'mcq') {
                    $issues[] = "Referenced MCQ {$mcq_id} does not exist";
                }
            }
        }
    }
    
    return $issues;
}

/**
 
* Register Institution custom post type
 */
function mcqhome_register_institution_post_type() {
    $labels = [
        'name'                  => _x('Institutions', 'Post type general name', 'mcqhome'),
        'singular_name'         => _x('Institution', 'Post type singular name', 'mcqhome'),
        'menu_name'             => _x('Institutions', 'Admin Menu text', 'mcqhome'),
        'name_admin_bar'        => _x('Institution', 'Add New on Toolbar', 'mcqhome'),
        'add_new'               => __('Add New', 'mcqhome'),
        'add_new_item'          => __('Add New Institution', 'mcqhome'),
        'new_item'              => __('New Institution', 'mcqhome'),
        'edit_item'             => __('Edit Institution', 'mcqhome'),
        'view_item'             => __('View Institution', 'mcqhome'),
        'all_items'             => __('All Institutions', 'mcqhome'),
        'search_items'          => __('Search Institutions', 'mcqhome'),
        'parent_item_colon'     => __('Parent Institutions:', 'mcqhome'),
        'not_found'             => __('No Institutions found.', 'mcqhome'),
        'not_found_in_trash'    => __('No Institutions found in Trash.', 'mcqhome'),
        'featured_image'        => _x('Institution Logo', 'Overrides the "Featured Image" phrase', 'mcqhome'),
        'set_featured_image'    => _x('Set institution logo', 'Overrides the "Set featured image" phrase', 'mcqhome'),
        'remove_featured_image' => _x('Remove institution logo', 'Overrides the "Remove featured image" phrase', 'mcqhome'),
        'use_featured_image'    => _x('Use as institution logo', 'Overrides the "Use as featured image" phrase', 'mcqhome'),
        'archives'              => _x('Institution archives', 'The post type archive label', 'mcqhome'),
        'insert_into_item'      => _x('Insert into Institution', 'Overrides the "Insert into post" phrase', 'mcqhome'),
        'uploaded_to_this_item' => _x('Uploaded to this Institution', 'Overrides the "Uploaded to this post" phrase', 'mcqhome'),
        'filter_items_list'     => _x('Filter Institutions list', 'Screen reader text for the filter links', 'mcqhome'),
        'items_list_navigation' => _x('Institutions list navigation', 'Screen reader text for the pagination', 'mcqhome'),
        'items_list'            => _x('Institutions list', 'Screen reader text for the items list', 'mcqhome'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'institution'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 22,
        'menu_icon'          => 'dashicons-building',
        'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt'],
        'show_in_rest'       => true,
        'rest_base'          => 'institutions',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    ];

    register_post_type('institution', $args);
}
add_action('init', 'mcqhome_register_institution_post_type');

/**
 * Add Institution meta boxes
 */
function mcqhome_add_institution_meta_boxes() {
    add_meta_box(
        'institution_details',
        __('Institution Details', 'mcqhome'),
        'mcqhome_institution_details_callback',
        'institution',
        'normal',
        'high'
    );

    add_meta_box(
        'institution_branding',
        __('Branding & Customization', 'mcqhome'),
        'mcqhome_institution_branding_callback',
        'institution',
        'normal',
        'high'
    );

    add_meta_box(
        'institution_teachers',
        __('Associated Teachers', 'mcqhome'),
        'mcqhome_institution_teachers_callback',
        'institution',
        'normal',
        'default'
    );

    add_meta_box(
        'institution_stats',
        __('Statistics', 'mcqhome'),
        'mcqhome_institution_stats_callback',
        'institution',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'mcqhome_add_institution_meta_boxes');

/**
 * Institution Details meta box callback
 */
function mcqhome_institution_details_callback($post) {
    wp_nonce_field('mcqhome_save_institution_meta', 'mcqhome_institution_meta_nonce');
    
    $contact_email = get_post_meta($post->ID, '_institution_contact_email', true);
    $contact_phone = get_post_meta($post->ID, '_institution_contact_phone', true);
    $website_url = get_post_meta($post->ID, '_institution_website_url', true);
    $address = get_post_meta($post->ID, '_institution_address', true);
    $established_year = get_post_meta($post->ID, '_institution_established_year', true);
    $institution_type = get_post_meta($post->ID, '_institution_type', true) ?: 'educational';
    $specializations = get_post_meta($post->ID, '_institution_specializations', true);
    if (!is_array($specializations)) {
        $specializations = [];
    }
    
    echo '<table class="form-table">';
    
    // Contact Email
    echo '<tr>';
    echo '<th scope="row"><label for="institution_contact_email">' . __('Contact Email', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="email" name="institution_contact_email" id="institution_contact_email" value="' . esc_attr($contact_email) . '" class="regular-text">';
    echo '<p class="description">' . __('Primary contact email for this institution.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Contact Phone
    echo '<tr>';
    echo '<th scope="row"><label for="institution_contact_phone">' . __('Contact Phone', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="tel" name="institution_contact_phone" id="institution_contact_phone" value="' . esc_attr($contact_phone) . '" class="regular-text">';
    echo '<p class="description">' . __('Primary contact phone number.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Website URL
    echo '<tr>';
    echo '<th scope="row"><label for="institution_website_url">' . __('Website URL', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="url" name="institution_website_url" id="institution_website_url" value="' . esc_attr($website_url) . '" class="regular-text">';
    echo '<p class="description">' . __('Official website URL of the institution.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Address
    echo '<tr>';
    echo '<th scope="row"><label for="institution_address">' . __('Address', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<textarea name="institution_address" id="institution_address" rows="3" class="large-text">' . esc_textarea($address) . '</textarea>';
    echo '<p class="description">' . __('Complete address of the institution.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Established Year
    echo '<tr>';
    echo '<th scope="row"><label for="institution_established_year">' . __('Established Year', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="number" name="institution_established_year" id="institution_established_year" value="' . esc_attr($established_year) . '" min="1800" max="' . date('Y') . '" class="small-text">';
    echo '<p class="description">' . __('Year when the institution was established.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Institution Type
    echo '<tr>';
    echo '<th scope="row">' . __('Institution Type', 'mcqhome') . '</th>';
    echo '<td>';
    echo '<fieldset>';
    $types = [
        'educational' => __('Educational Institution', 'mcqhome'),
        'training' => __('Training Center', 'mcqhome'),
        'corporate' => __('Corporate Training', 'mcqhome'),
        'coaching' => __('Coaching Institute', 'mcqhome'),
        'online' => __('Online Platform', 'mcqhome'),
        'other' => __('Other', 'mcqhome')
    ];
    
    foreach ($types as $value => $label) {
        echo '<label><input type="radio" name="institution_type" value="' . $value . '"' . checked($institution_type, $value, false) . '> ' . $label . '</label><br>';
    }
    echo '</fieldset>';
    echo '</td>';
    echo '</tr>';
    
    // Specializations
    echo '<tr>';
    echo '<th scope="row"><label for="institution_specializations">' . __('Specializations', 'mcqhome') . '</label></th>';
    echo '<td>';
    
    // Get all available subjects for specializations
    $subjects = get_terms([
        'taxonomy' => 'mcq_subject',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    
    if ($subjects && !is_wp_error($subjects)) {
        echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
        foreach ($subjects as $subject) {
            $checked = in_array($subject->term_id, $specializations);
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="institution_specializations[]" value="' . $subject->term_id . '"' . checked($checked, true, false) . '> ';
            echo esc_html($subject->name);
            echo '</label>';
        }
        echo '</div>';
    } else {
        echo '<p><em>' . __('No subjects available. Create subjects first to set specializations.', 'mcqhome') . '</em></p>';
        echo '<input type="hidden" name="institution_specializations[]" value="">';
    }
    
    echo '<p class="description">' . __('Select the subjects this institution specializes in.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
}

/**
 * Institution Branding meta box callback
 */
function mcqhome_institution_branding_callback($post) {
    $primary_color = get_post_meta($post->ID, '_institution_primary_color', true) ?: '#0073aa';
    $secondary_color = get_post_meta($post->ID, '_institution_secondary_color', true) ?: '#005177';
    $banner_image = get_post_meta($post->ID, '_institution_banner_image', true);
    $custom_css = get_post_meta($post->ID, '_institution_custom_css', true);
    $social_links = get_post_meta($post->ID, '_institution_social_links', true);
    if (!is_array($social_links)) {
        $social_links = [
            'facebook' => '',
            'twitter' => '',
            'linkedin' => '',
            'instagram' => '',
            'youtube' => ''
        ];
    }
    
    echo '<table class="form-table">';
    
    // Primary Color
    echo '<tr>';
    echo '<th scope="row"><label for="institution_primary_color">' . __('Primary Color', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="color" name="institution_primary_color" id="institution_primary_color" value="' . esc_attr($primary_color) . '">';
    echo '<p class="description">' . __('Primary brand color for this institution.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Secondary Color
    echo '<tr>';
    echo '<th scope="row"><label for="institution_secondary_color">' . __('Secondary Color', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="color" name="institution_secondary_color" id="institution_secondary_color" value="' . esc_attr($secondary_color) . '">';
    echo '<p class="description">' . __('Secondary brand color for accents and highlights.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    // Banner Image
    echo '<tr>';
    echo '<th scope="row"><label for="institution_banner_image">' . __('Banner Image', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<input type="hidden" name="institution_banner_image" id="institution_banner_image" value="' . esc_attr($banner_image) . '">';
    echo '<div id="banner-image-preview">';
    if ($banner_image) {
        $image_url = wp_get_attachment_image_url($banner_image, 'medium');
        if ($image_url) {
            echo '<img src="' . esc_url($image_url) . '" style="max-width: 300px; height: auto; display: block; margin-bottom: 10px;">';
        }
    }
    echo '</div>';
    echo '<button type="button" class="button" id="upload-banner-btn">' . __('Upload Banner Image', 'mcqhome') . '</button>';
    echo '<button type="button" class="button" id="remove-banner-btn" style="' . ($banner_image ? '' : 'display:none;') . '">' . __('Remove Banner', 'mcqhome') . '</button>';
    echo '<p class="description">' . __('Banner image for the institution profile page (recommended size: 1200x300px).', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    // Social Links Section
    echo '<h4>' . __('Social Media Links', 'mcqhome') . '</h4>';
    echo '<table class="form-table">';
    
    $social_platforms = [
        'facebook' => __('Facebook', 'mcqhome'),
        'twitter' => __('Twitter', 'mcqhome'),
        'linkedin' => __('LinkedIn', 'mcqhome'),
        'instagram' => __('Instagram', 'mcqhome'),
        'youtube' => __('YouTube', 'mcqhome')
    ];
    
    foreach ($social_platforms as $platform => $label) {
        echo '<tr>';
        echo '<th scope="row"><label for="institution_social_' . $platform . '">' . $label . '</label></th>';
        echo '<td>';
        echo '<input type="url" name="institution_social_links[' . $platform . ']" id="institution_social_' . $platform . '" value="' . esc_attr($social_links[$platform] ?? '') . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    // Custom CSS Section
    echo '<h4>' . __('Custom CSS', 'mcqhome') . '</h4>';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="institution_custom_css">' . __('Custom CSS', 'mcqhome') . '</label></th>';
    echo '<td>';
    echo '<textarea name="institution_custom_css" id="institution_custom_css" rows="10" class="large-text code">' . esc_textarea($custom_css) . '</textarea>';
    echo '<p class="description">' . __('Custom CSS styles for this institution\'s pages. Use with caution.', 'mcqhome') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    
    // JavaScript for media uploader
    echo '<script>
    jQuery(document).ready(function($) {
        var mediaUploader;
        
        $("#upload-banner-btn").on("click", function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: "' . __('Choose Banner Image', 'mcqhome') . '",
                button: {
                    text: "' . __('Choose Image', 'mcqhome') . '"
                },
                multiple: false
            });
            
            mediaUploader.on("select", function() {
                var attachment = mediaUploader.state().get("selection").first().toJSON();
                $("#institution_banner_image").val(attachment.id);
                $("#banner-image-preview").html("<img src=\"" + attachment.url + "\" style=\"max-width: 300px; height: auto; display: block; margin-bottom: 10px;\">");
                $("#remove-banner-btn").show();
            });
            
            mediaUploader.open();
        });
        
        $("#remove-banner-btn").on("click", function(e) {
            e.preventDefault();
            $("#institution_banner_image").val("");
            $("#banner-image-preview").html("");
            $(this).hide();
        });
    });
    </script>';
}

/**
 * Institution Teachers meta box callback
 */
function mcqhome_institution_teachers_callback($post) {
    // Get users with teacher role associated with this institution
    $associated_teachers = get_users([
        'role' => 'teacher',
        'meta_key' => 'institution_id',
        'meta_value' => $post->ID,
        'orderby' => 'display_name',
        'order' => 'ASC'
    ]);
    
    // Get all teachers not associated with any institution or associated with this one
    $all_teachers = get_users([
        'role' => 'teacher',
        'orderby' => 'display_name',
        'order' => 'ASC'
    ]);
    
    echo '<div class="institution-teachers-management">';
    
    if (!empty($associated_teachers)) {
        echo '<h4>' . __('Current Teachers', 'mcqhome') . '</h4>';
        echo '<table class="widefat">';
        echo '<thead><tr><th>' . __('Teacher', 'mcqhome') . '</th><th>' . __('Email', 'mcqhome') . '</th><th>' . __('Joined', 'mcqhome') . '</th><th>' . __('Actions', 'mcqhome') . '</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($associated_teachers as $teacher) {
            $user_registered = date('M j, Y', strtotime($teacher->user_registered));
            echo '<tr>';
            echo '<td><strong>' . esc_html($teacher->display_name) . '</strong></td>';
            echo '<td>' . esc_html($teacher->user_email) . '</td>';
            echo '<td>' . $user_registered . '</td>';
            echo '<td>';
            echo '<button type="button" class="button button-small remove-teacher-btn" data-teacher-id="' . $teacher->ID . '">' . __('Remove', 'mcqhome') . '</button>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p><em>' . __('No teachers are currently associated with this institution.', 'mcqhome') . '</em></p>';
    }
    
    // Add teacher section
    echo '<h4>' . __('Add Teachers', 'mcqhome') . '</h4>';
    echo '<p>' . __('Select teachers to associate with this institution:', 'mcqhome') . '</p>';
    
    $available_teachers = [];
    foreach ($all_teachers as $teacher) {
        $current_institution = get_user_meta($teacher->ID, 'institution_id', true);
        if (empty($current_institution) || $current_institution == $post->ID) {
            continue; // Skip if already associated with this institution
        }
        $available_teachers[] = $teacher;
    }
    
    if (!empty($available_teachers)) {
        echo '<select id="available-teachers" class="regular-text">';
        echo '<option value="">' . __('Select a teacher...', 'mcqhome') . '</option>';
        foreach ($available_teachers as $teacher) {
            $current_institution_name = '';
            $current_institution_id = get_user_meta($teacher->ID, 'institution_id', true);
            if ($current_institution_id) {
                $institution_post = get_post($current_institution_id);
                $current_institution_name = $institution_post ? ' (' . __('Currently at:', 'mcqhome') . ' ' . $institution_post->post_title . ')' : '';
            }
            echo '<option value="' . $teacher->ID . '">' . esc_html($teacher->display_name) . ' - ' . esc_html($teacher->user_email) . $current_institution_name . '</option>';
        }
        echo '</select>';
        echo '<button type="button" class="button" id="add-teacher-btn">' . __('Add Teacher', 'mcqhome') . '</button>';
    } else {
        echo '<p><em>' . __('No available teachers to add. All teachers are either already associated with this institution or other institutions.', 'mcqhome') . '</em></p>';
    }
    
    echo '</div>';
    
    // JavaScript for teacher management
    echo '<script>
    jQuery(document).ready(function($) {
        // Add teacher
        $("#add-teacher-btn").on("click", function() {
            var teacherId = $("#available-teachers").val();
            if (!teacherId) {
                alert("' . __('Please select a teacher to add.', 'mcqhome') . '");
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "mcqhome_add_teacher_to_institution",
                    teacher_id: teacherId,
                    institution_id: ' . $post->ID . ',
                    nonce: "' . wp_create_nonce('mcqhome_teacher_management') . '"
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || "' . __('Error adding teacher.', 'mcqhome') . '");
                    }
                },
                error: function() {
                    alert("' . __('Error adding teacher.', 'mcqhome') . '");
                }
            });
        });
        
        // Remove teacher
        $(".remove-teacher-btn").on("click", function() {
            if (!confirm("' . __('Are you sure you want to remove this teacher from the institution?', 'mcqhome') . '")) {
                return;
            }
            
            var teacherId = $(this).data("teacher-id");
            
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "mcqhome_remove_teacher_from_institution",
                    teacher_id: teacherId,
                    institution_id: ' . $post->ID . ',
                    nonce: "' . wp_create_nonce('mcqhome_teacher_management') . '"
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || "' . __('Error removing teacher.', 'mcqhome') . '");
                    }
                },
                error: function() {
                    alert("' . __('Error removing teacher.', 'mcqhome') . '");
                }
            });
        });
    });
    </script>';
}

/**
 * Institution Statistics meta box callback
 */
function mcqhome_institution_stats_callback($post) {
    // Get statistics for this institution
    $teacher_count = count(get_users([
        'role' => 'teacher',
        'meta_key' => 'institution_id',
        'meta_value' => $post->ID
    ]));
    
    $mcq_count = get_posts([
        'post_type' => 'mcq',
        'post_status' => 'publish',
        'meta_key' => 'institution_id',
        'meta_value' => $post->ID,
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    $mcq_count = count($mcq_count);
    
    $mcq_set_count = get_posts([
        'post_type' => 'mcq_set',
        'post_status' => 'publish',
        'meta_key' => 'institution_id',
        'meta_value' => $post->ID,
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    $mcq_set_count = count($mcq_set_count);
    
    echo '<div class="misc-pub-section">';
    echo '<h4>' . __('Content Statistics', 'mcqhome') . '</h4>';
    echo '<p><strong>' . $teacher_count . '</strong> ' . _n('Teacher', 'Teachers', $teacher_count, 'mcqhome') . '</p>';
    echo '<p><strong>' . $mcq_count . '</strong> ' . _n('MCQ', 'MCQs', $mcq_count, 'mcqhome') . '</p>';
    echo '<p><strong>' . $mcq_set_count . '</strong> ' . _n('MCQ Set', 'MCQ Sets', $mcq_set_count, 'mcqhome') . '</p>';
    echo '</div>';
    
    echo '<div class="misc-pub-section">';
    echo '<h4>' . __('Quick Actions', 'mcqhome') . '</h4>';
    echo '<p><a href="' . admin_url('edit.php?post_type=mcq&institution_id=' . $post->ID) . '" class="button button-small">' . __('View MCQs', 'mcqhome') . '</a></p>';
    echo '<p><a href="' . admin_url('edit.php?post_type=mcq_set&institution_id=' . $post->ID) . '" class="button button-small">' . __('View MCQ Sets', 'mcqhome') . '</a></p>';
    echo '<p><a href="' . admin_url('users.php?role=teacher&institution_id=' . $post->ID) . '" class="button button-small">' . __('View Teachers', 'mcqhome') . '</a></p>';
    echo '</div>';
}

/**
 * Save Institution meta data
 */
function mcqhome_save_institution_meta($post_id) {
    // Check if nonce is valid
    if (!isset($_POST['mcqhome_institution_meta_nonce']) || !wp_verify_nonce($_POST['mcqhome_institution_meta_nonce'], 'mcqhome_save_institution_meta')) {
        return;
    }

    // Check if user has permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if not an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check post type
    if (get_post_type($post_id) !== 'institution') {
        return;
    }

    // Save basic details
    $basic_fields = [
        'institution_contact_email' => 'sanitize_email',
        'institution_contact_phone' => 'sanitize_text_field',
        'institution_website_url' => 'esc_url_raw',
        'institution_address' => 'sanitize_textarea_field',
        'institution_established_year' => 'intval'
    ];

    foreach ($basic_fields as $field => $sanitize_func) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, $sanitize_func($_POST[$field]));
        }
    }

    // Save institution type
    if (isset($_POST['institution_type'])) {
        $allowed_types = ['educational', 'training', 'corporate', 'coaching', 'online', 'other'];
        $type = in_array($_POST['institution_type'], $allowed_types) ? $_POST['institution_type'] : 'educational';
        update_post_meta($post_id, '_institution_type', $type);
    }

    // Save specializations
    if (isset($_POST['institution_specializations']) && is_array($_POST['institution_specializations'])) {
        $specializations = array_map('intval', $_POST['institution_specializations']);
        $specializations = array_filter($specializations); // Remove empty values
        update_post_meta($post_id, '_institution_specializations', $specializations);
    } else {
        update_post_meta($post_id, '_institution_specializations', []);
    }

    // Save branding
    $branding_fields = [
        'institution_primary_color' => 'sanitize_hex_color',
        'institution_secondary_color' => 'sanitize_hex_color',
        'institution_banner_image' => 'intval',
        'institution_custom_css' => 'wp_strip_all_tags'
    ];

    foreach ($branding_fields as $field => $sanitize_func) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, $sanitize_func($_POST[$field]));
        }
    }

    // Save social links
    if (isset($_POST['institution_social_links']) && is_array($_POST['institution_social_links'])) {
        $social_links = [];
        foreach ($_POST['institution_social_links'] as $platform => $url) {
            $social_links[sanitize_key($platform)] = esc_url_raw($url);
        }
        update_post_meta($post_id, '_institution_social_links', $social_links);
    }
}
add_action('save_post', 'mcqhome_save_institution_meta');

/**
 * AJAX handler for adding teacher to institution
 */
function mcqhome_add_teacher_to_institution() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_teacher_management')) {
        wp_die(__('Security check failed.', 'mcqhome'));
    }

    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have permission to perform this action.', 'mcqhome'));
    }

    $teacher_id = intval($_POST['teacher_id']);
    $institution_id = intval($_POST['institution_id']);

    // Validate inputs
    if (!$teacher_id || !$institution_id) {
        wp_send_json_error(__('Invalid teacher or institution ID.', 'mcqhome'));
    }

    // Check if teacher exists and has teacher role
    $teacher = get_user_by('ID', $teacher_id);
    if (!$teacher || !in_array('teacher', $teacher->roles)) {
        wp_send_json_error(__('Invalid teacher.', 'mcqhome'));
    }

    // Check if institution exists
    $institution = get_post($institution_id);
    if (!$institution || $institution->post_type !== 'institution') {
        wp_send_json_error(__('Invalid institution.', 'mcqhome'));
    }

    // Update teacher's institution association
    update_user_meta($teacher_id, 'institution_id', $institution_id);

    wp_send_json_success(__('Teacher added successfully.', 'mcqhome'));
}
add_action('wp_ajax_mcqhome_add_teacher_to_institution', 'mcqhome_add_teacher_to_institution');

/**
 * AJAX handler for removing teacher from institution
 */
function mcqhome_remove_teacher_from_institution() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_teacher_management')) {
        wp_die(__('Security check failed.', 'mcqhome'));
    }

    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have permission to perform this action.', 'mcqhome'));
    }

    $teacher_id = intval($_POST['teacher_id']);
    $institution_id = intval($_POST['institution_id']);

    // Validate inputs
    if (!$teacher_id || !$institution_id) {
        wp_send_json_error(__('Invalid teacher or institution ID.', 'mcqhome'));
    }

    // Remove teacher's institution association (set to default MCQ Academy)
    $default_institution = get_posts([
        'post_type' => 'institution',
        'meta_key' => '_institution_is_default',
        'meta_value' => '1',
        'posts_per_page' => 1,
        'fields' => 'ids'
    ]);

    if (!empty($default_institution)) {
        update_user_meta($teacher_id, 'institution_id', $default_institution[0]);
    } else {
        delete_user_meta($teacher_id, 'institution_id');
    }

    wp_send_json_success(__('Teacher removed successfully.', 'mcqhome'));
}
add_action('wp_ajax_mcqhome_remove_teacher_from_institution', 'mcqhome_remove_teacher_from_institution');

/**
 * Customize Institution admin columns
 */
function mcqhome_institution_admin_columns($columns) {
    $new_columns = [];
    
    // Add checkbox and title
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    
    // Add custom columns
    $new_columns['institution_type'] = __('Type', 'mcqhome');
    $new_columns['teachers_count'] = __('Teachers', 'mcqhome');
    $new_columns['content_count'] = __('Content', 'mcqhome');
    $new_columns['contact_info'] = __('Contact', 'mcqhome');
    $new_columns['author'] = $columns['author'];
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}
add_filter('manage_institution_posts_columns', 'mcqhome_institution_admin_columns');

/**
 * Populate custom Institution admin columns
 */
function mcqhome_institution_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'institution_type':
            $type = get_post_meta($post_id, '_institution_type', true);
            $types = [
                'educational' => __('Educational', 'mcqhome'),
                'training' => __('Training', 'mcqhome'),
                'corporate' => __('Corporate', 'mcqhome'),
                'coaching' => __('Coaching', 'mcqhome'),
                'online' => __('Online', 'mcqhome'),
                'other' => __('Other', 'mcqhome')
            ];
            echo $types[$type] ?? __('Educational', 'mcqhome');
            break;
            
        case 'teachers_count':
            $teacher_count = count(get_users([
                'role' => 'teacher',
                'meta_key' => 'institution_id',
                'meta_value' => $post_id
            ]));
            echo '<strong>' . $teacher_count . '</strong> ' . _n('teacher', 'teachers', $teacher_count, 'mcqhome');
            break;
            
        case 'content_count':
            $mcq_count = count(get_posts([
                'post_type' => 'mcq',
                'post_status' => 'publish',
                'meta_key' => 'institution_id',
                'meta_value' => $post_id,
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]));
            
            $set_count = count(get_posts([
                'post_type' => 'mcq_set',
                'post_status' => 'publish',
                'meta_key' => 'institution_id',
                'meta_value' => $post_id,
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]));
            
            echo $mcq_count . ' MCQs<br>' . $set_count . ' Sets';
            break;
            
        case 'contact_info':
            $email = get_post_meta($post_id, '_institution_contact_email', true);
            $phone = get_post_meta($post_id, '_institution_contact_phone', true);
            
            if ($email) {
                echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
            }
            if ($phone) {
                echo $email ? '<br>' : '';
                echo esc_html($phone);
            }
            if (!$email && !$phone) {
                echo '<em>' . __('No contact info', 'mcqhome') . '</em>';
            }
            break;
    }
}
add_action('manage_institution_posts_custom_column', 'mcqhome_institution_admin_column_content', 10, 2);

/**
 * Create default MCQ Academy institution on theme activation
 */
function mcqhome_create_default_institution() {
    // Check if default institution already exists
    $existing_default = get_posts([
        'post_type' => 'institution',
        'meta_key' => '_institution_is_default',
        'meta_value' => '1',
        'posts_per_page' => 1,
        'post_status' => 'any'
    ]);

    if (empty($existing_default)) {
        // Create MCQ Academy default institution
        $institution_id = wp_insert_post([
            'post_title' => __('MCQ Academy', 'mcqhome'),
            'post_content' => __('MCQ Academy is the default institution for independent teachers and educators who want to create and share MCQ content without being associated with a specific organization.', 'mcqhome'),
            'post_status' => 'publish',
            'post_type' => 'institution',
            'post_author' => 1, // Admin user
        ]);

        if ($institution_id && !is_wp_error($institution_id)) {
            // Set as default institution
            update_post_meta($institution_id, '_institution_is_default', '1');
            update_post_meta($institution_id, '_institution_type', 'online');
            update_post_meta($institution_id, '_institution_contact_email', get_option('admin_email'));
            update_post_meta($institution_id, '_institution_primary_color', '#0073aa');
            update_post_meta($institution_id, '_institution_secondary_color', '#005177');
        }
    }
}
add_action('after_switch_theme', 'mcqhome_create_default_institution');
/**
 *
 MCQ Set Tabbed Interface callback
 */
function mcqhome_mcq_set_tabs_callback($post) {
    wp_nonce_field('mcqhome_save_mcq_set_meta', 'mcqhome_mcq_set_meta_nonce');
    
    // Get current values
    $pricing_type = get_post_meta($post->ID, '_mcq_set_pricing_type', true) ?: 'free';
    $price = get_post_meta($post->ID, '_mcq_set_price', true) ?: '';
    $pass_marks = get_post_meta($post->ID, '_mcq_set_pass_marks', true) ?: '';
    $time_limit = get_post_meta($post->ID, '_mcq_set_time_limit', true) ?: '';
    $display_format = get_post_meta($post->ID, '_mcq_set_display_format', true) ?: 'next_next';
    $sections_enabled = get_post_meta($post->ID, '_mcq_set_sections_enabled', true);
    $sections = json_decode(get_post_meta($post->ID, '_mcq_set_sections', true), true) ?: [];
    $questions_order = json_decode(get_post_meta($post->ID, '_mcq_set_questions_order', true), true) ?: ['questions' => []];
    $description = get_post_meta($post->ID, '_mcq_set_description', true) ?: $post->post_content;
    
    // Get available users for author selection
    $users = get_users(['role__in' => ['teacher', 'administrator']]);
    $selected_author = $post->post_author;
    
    // Get taxonomies
    $selected_subjects = wp_get_post_terms($post->ID, 'mcq_subject', ['fields' => 'ids']);
    $selected_topics = wp_get_post_terms($post->ID, 'mcq_topic', ['fields' => 'ids']);
    $subjects = get_terms(['taxonomy' => 'mcq_subject', 'hide_empty' => false]);
    $topics = get_terms(['taxonomy' => 'mcq_topic', 'hide_empty' => false]);
    
    ?>
    <div class="mcq-set-editor-container">
        <!-- Tab Navigation -->
        <div class="mcq-set-tabs-nav">
            <ul class="mcq-set-tab-list">
                <li><a href="#tab-general" class="mcq-set-tab-link active"><?php _e('General Settings', 'mcqhome'); ?></a></li>
                <li><a href="#tab-assessment" class="mcq-set-tab-link"><?php _e('Assessment Configuration', 'mcqhome'); ?></a></li>
                <li><a href="#tab-sections" class="mcq-set-tab-link"><?php _e('Exam Sections', 'mcqhome'); ?></a></li>
                <li><a href="#tab-questions" class="mcq-set-tab-link"><?php _e('Questions Management', 'mcqhome'); ?></a></li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="mcq-set-tabs-content">
            
            <!-- General Settings Tab -->
            <div id="tab-general" class="mcq-set-tab-content active">
                <div class="mcq-set-tab-inner">
                    <h3><?php _e('General Settings', 'mcqhome'); ?></h3>
                    
                    <div class="mcq-set-form-grid">
                        <!-- Description -->
                        <div class="mcq-set-form-field full-width">
                            <label for="mcq_set_description"><strong><?php _e('MCQ Set Description', 'mcqhome'); ?></strong></label>
                            <?php
                            wp_editor($description, 'mcq_set_description', [
                                'textarea_name' => 'mcq_set_description',
                                'media_buttons' => true,
                                'textarea_rows' => 8,
                                'teeny' => false,
                                'textarea_class' => 'mcq-form-field',
                                'tinymce' => [
                                    'toolbar1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,image,media,|,spellchecker,fullscreen',
                                    'toolbar2' => 'formatselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,|,undo,redo',
                                    'content_css' => get_template_directory_uri() . '/assets/css/mcq-editor.css',
                                    'body_class' => 'mcq-set-description-content'
                                ],
                                'quicktags' => [
                                    'buttons' => 'strong,em,ul,ol,li,link,img,close'
                                ]
                            ]);
                            ?>
                            <p class="description"><?php _e('Provide a detailed description of this MCQ set, including learning objectives and instructions.', 'mcqhome'); ?></p>
                        </div>
                        
                        <!-- Thumbnail Upload -->
                        <div class="mcq-set-form-field">
                            <label><strong><?php _e('Thumbnail Image', 'mcqhome'); ?></strong></label>
                            <div class="mcq-set-thumbnail-upload">
                                <?php
                                $thumbnail_id = get_post_thumbnail_id($post->ID);
                                $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
                                ?>
                                <div class="mcq-set-thumbnail-preview" <?php echo $thumbnail_url ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php _e('Thumbnail Preview', 'mcqhome'); ?>" style="max-width: 200px; height: auto;">
                                    <button type="button" class="button mcq-set-remove-thumbnail"><?php _e('Remove', 'mcqhome'); ?></button>
                                </div>
                                <button type="button" class="button mcq-set-upload-thumbnail" <?php echo $thumbnail_url ? 'style="display: none;"' : ''; ?>>
                                    <?php _e('Upload Thumbnail', 'mcqhome'); ?>
                                </button>
                                <input type="hidden" name="mcq_set_thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>" class="mcq-set-thumbnail-id">
                            </div>
                            <p class="description"><?php _e('Upload a thumbnail image for this MCQ set. Recommended size: 400x300 pixels.', 'mcqhome'); ?></p>
                        </div>
                        
                        <!-- Category Selection -->
                        <div class="mcq-set-form-field">
                            <label for="mcq_set_subjects"><strong><?php _e('Subjects', 'mcqhome'); ?></strong></label>
                            <select name="tax_input[mcq_subject][]" id="mcq_set_subjects" class="widefat mcq-form-field" multiple>
                                <?php if (!empty($subjects)): ?>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject->term_id; ?>" <?php echo in_array($subject->term_id, $selected_subjects) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($subject->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="description"><?php _e('Select relevant subjects for this MCQ set.', 'mcqhome'); ?></p>
                        </div>
                        
                        <div class="mcq-set-form-field">
                            <label for="mcq_set_topics"><strong><?php _e('Topics', 'mcqhome'); ?></strong></label>
                            <select name="tax_input[mcq_topic][]" id="mcq_set_topics" class="widefat mcq-form-field" multiple>
                                <?php if (!empty($topics)): ?>
                                    <?php foreach ($topics as $topic): ?>
                                        <option value="<?php echo $topic->term_id; ?>" <?php echo in_array($topic->term_id, $selected_topics) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($topic->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="description"><?php _e('Select relevant topics for this MCQ set.', 'mcqhome'); ?></p>
                        </div>
                        
                        <!-- Pricing Configuration -->
                        <div class="mcq-set-form-field">
                            <label><strong><?php _e('Pricing Type', 'mcqhome'); ?></strong></label>
                            <div class="mcq-set-pricing-options">
                                <label class="mcq-set-radio-label">
                                    <input type="radio" name="mcq_set_pricing_type" value="free" <?php checked($pricing_type, 'free'); ?> class="mcq-set-pricing-radio">
                                    <?php _e('Free', 'mcqhome'); ?>
                                </label>
                                <label class="mcq-set-radio-label">
                                    <input type="radio" name="mcq_set_pricing_type" value="paid" <?php checked($pricing_type, 'paid'); ?> class="mcq-set-pricing-radio">
                                    <?php _e('Paid', 'mcqhome'); ?>
                                </label>
                            </div>
                            <div class="mcq-set-price-field" <?php echo $pricing_type === 'paid' ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                                <label for="mcq_set_price"><?php _e('Price ($)', 'mcqhome'); ?></label>
                                <input type="number" name="mcq_set_price" id="mcq_set_price" value="<?php echo esc_attr($price); ?>" class="widefat mcq-form-field" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        
                        <!-- Author Assignment -->
                        <div class="mcq-set-form-field">
                            <label for="mcq_set_author"><strong><?php _e('Author/Teacher', 'mcqhome'); ?></strong></label>
                            <select name="post_author_override" id="mcq_set_author" class="widefat mcq-form-field">
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user->ID; ?>" <?php selected($selected_author, $user->ID); ?>>
                                        <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Select the teacher or author for this MCQ set.', 'mcqhome'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Assessment Configuration Tab -->
            <div id="tab-assessment" class="mcq-set-tab-content">
                <div class="mcq-set-tab-inner">
                    <h3><?php _e('Assessment Configuration', 'mcqhome'); ?></h3>
                    
                    <div class="mcq-set-form-grid">
                        <!-- Pass Marks -->
                        <div class="mcq-set-form-field">
                            <label for="mcq_set_pass_marks"><strong><?php _e('Pass Marks (%)', 'mcqhome'); ?></strong></label>
                            <input type="number" name="mcq_set_pass_marks" id="mcq_set_pass_marks" value="<?php echo esc_attr($pass_marks); ?>" class="widefat mcq-form-field" min="0" max="100" placeholder="60">
                            <p class="description"><?php _e('Minimum percentage required to pass this assessment.', 'mcqhome'); ?></p>
                        </div>
                        
                        <!-- Time Limit -->
                        <div class="mcq-set-form-field">
                            <label for="mcq_set_time_limit"><strong><?php _e('Time Limit (minutes)', 'mcqhome'); ?></strong></label>
                            <input type="number" name="mcq_set_time_limit" id="mcq_set_time_limit" value="<?php echo esc_attr($time_limit); ?>" class="widefat mcq-form-field" min="0" placeholder="60">
                            <p class="description"><?php _e('Time limit for completing this assessment. Leave empty for no time limit.', 'mcqhome'); ?></p>
                        </div>
                        
                        <!-- Display Format -->
                        <div class="mcq-set-form-field full-width">
                            <label><strong><?php _e('Assessment Display Format', 'mcqhome'); ?></strong></label>
                            <p class="description"><?php _e('Choose how questions will be presented to students during the assessment.', 'mcqhome'); ?></p>
                            
                            <div class="mcq-set-format-options">
                                <div class="mcq-format-option-card">
                                    <label class="mcq-set-radio-label">
                                        <input type="radio" name="mcq_set_display_format" value="next_next" <?php checked($display_format, 'next_next'); ?> class="mcq-format-radio">
                                        <div class="mcq-set-format-option">
                                            <div class="format-header">
                                                <strong><?php _e('Next-Next Format', 'mcqhome'); ?></strong>
                                                <span class="format-badge recommended"><?php _e('Recommended', 'mcqhome'); ?></span>
                                            </div>
                                            <p class="format-description"><?php _e('Students see one question at a time with navigation controls. Best for focused attention and preventing overwhelming students.', 'mcqhome'); ?></p>
                                            
                                            <div class="format-features">
                                                <h4><?php _e('Features:', 'mcqhome'); ?></h4>
                                                <ul>
                                                    <li>✓ <?php _e('One question per page', 'mcqhome'); ?></li>
                                                    <li>✓ <?php _e('Question navigation panel', 'mcqhome'); ?></li>
                                                    <li>✓ <?php _e('Progress tracking', 'mcqhome'); ?></li>
                                                    <li>✓ <?php _e('Section-wise organization (if sections enabled)', 'mcqhome'); ?></li>
                                                    <li>✓ <?php _e('Mobile-friendly interface', 'mcqhome'); ?></li>
                                                </ul>
                                            </div>
                                            
                                            <div class="format-preview">
                                                <div class="preview-mockup next-next-preview">
                                                    <div class="preview-nav">
                                                        <div class="nav-item">1</div>
                                                        <div class="nav-item active">2</div>
                                                        <div class="nav-item">3</div>
                                                        <div class="nav-item">4</div>
                                                    </div>
                                                    <div class="preview-question">
                                                        <div class="question-text">Question 2 of 4</div>
                                                        <div class="question-options">
                                                            <div class="option">A. Option A</div>
                                                            <div class="option selected">B. Option B</div>
                                                        </div>
                                                        <div class="nav-buttons">
                                                            <button>Previous</button>
                                                            <button>Next</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="mcq-format-option-card">
                                    <label class="mcq-set-radio-label">
                                        <input type="radio" name="mcq_set_display_format" value="single_page" <?php checked($display_format, 'single_page'); ?> class="mcq-format-radio">
                                        <div class="mcq-set-format-option">
                                            <div class="format-header">
                                                <strong><?php _e('Single Page Format', 'mcqhome'); ?></strong>
                                                <span class="format-badge"><?php _e('Traditional', 'mcqhome'); ?></span>
                                            </div>
                                            <p class="format-description"><?php _e('All questions displayed on one scrollable page. Suitable for shorter assessments or when students prefer to see all questions at once.', 'mcqhome'); ?></p>
                                            
                                            <div class="format-features">
                                                <h4><?php _e('Features:', 'mcqhome'); ?></h4>
                                                <ul>
                                                    <li>✓ <?php _e('All questions visible at once', 'mcqhome'); ?></li>
                                                    <li>✓ <?php _e('Scrollable interface', 'mcqhome'); ?></li>
                                                    <li>✓ <?php _e('Quick question navigation', 'mcqhome'); ?></li>
                                                    <li>✓ <?php _e('Section headers (if sections enabled)', 'mcqhome'); ?></li>
                                                    <li>⚠ <?php _e('May be overwhelming for long assessments', 'mcqhome'); ?></li>
                                                </ul>
                                            </div>
                                            
                                            <div class="format-preview">
                                                <div class="preview-mockup single-page-preview">
                                                    <div class="preview-nav">
                                                        <div class="nav-item">1</div>
                                                        <div class="nav-item">2</div>
                                                        <div class="nav-item">3</div>
                                                        <div class="nav-item">4</div>
                                                    </div>
                                                    <div class="preview-questions">
                                                        <div class="question-card">Q1: Question text...</div>
                                                        <div class="question-card">Q2: Question text...</div>
                                                        <div class="question-card">Q3: Question text...</div>
                                                        <div class="submit-btn">Submit</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="format-recommendation">
                                <div class="recommendation-box">
                                    <h4><?php _e('💡 Recommendation', 'mcqhome'); ?></h4>
                                    <p><?php _e('For most assessments, we recommend the <strong>Next-Next Format</strong> as it provides better focus, reduces cognitive load, and works well on all devices. Use Single Page Format only for short quizzes (under 10 questions) or when specifically requested.', 'mcqhome'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Exam Sections Tab -->
            <div id="tab-sections" class="mcq-set-tab-content">
                <div class="mcq-set-tab-inner">
                    <h3><?php _e('Exam Sections', 'mcqhome'); ?></h3>
                    <p class="description"><?php _e('Organize your questions into sections (e.g., Reading, Writing, Listening). Sections are optional.', 'mcqhome'); ?></p>
                    
                    <!-- Enable/Disable Sections -->
                    <div class="mcq-set-sections-toggle">
                        <label class="mcq-set-checkbox-label">
                            <input type="checkbox" name="mcq_set_sections_enabled" value="1" <?php checked($sections_enabled, '1'); ?> class="mcq-set-sections-enabled">
                            <strong><?php _e('Enable Exam Sections', 'mcqhome'); ?></strong>
                        </label>
                        <p class="description"><?php _e('Check this to organize questions into sections. If disabled, all questions will be treated as a single group.', 'mcqhome'); ?></p>
                    </div>
                    
                    <!-- Sections Management -->
                    <div class="mcq-set-sections-manager" <?php echo $sections_enabled ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                        <div class="mcq-set-sections-list">
                            <?php if (!empty($sections)): ?>
                                <?php foreach ($sections as $index => $section): ?>
                                    <div class="mcq-set-section-item" data-section-index="<?php echo $index; ?>">
                                        <div class="mcq-set-section-header">
                                            <span class="mcq-set-section-handle">⋮⋮</span>
                                            <input type="text" name="mcq_set_sections[<?php echo $index; ?>][name]" value="<?php echo esc_attr($section['name']); ?>" placeholder="<?php _e('Section Name', 'mcqhome'); ?>" class="mcq-set-section-name">
                                            <button type="button" class="button mcq-set-remove-section"><?php _e('Remove', 'mcqhome'); ?></button>
                                        </div>
                                        <div class="mcq-set-section-description">
                                            <textarea name="mcq_set_sections[<?php echo $index; ?>][description]" placeholder="<?php _e('Section Description (optional)', 'mcqhome'); ?>" class="mcq-set-section-desc"><?php echo esc_textarea($section['description'] ?? ''); ?></textarea>
                                        </div>
                                        <input type="hidden" name="mcq_set_sections[<?php echo $index; ?>][id]" value="<?php echo esc_attr($section['id']); ?>">
                                        <input type="hidden" name="mcq_set_sections[<?php echo $index; ?>][order]" value="<?php echo esc_attr($section['order'] ?? $index + 1); ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="button button-secondary mcq-set-add-section">
                            <?php _e('Add New Section', 'mcqhome'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Questions Management Tab -->
            <div id="tab-questions" class="mcq-set-tab-content">
                <div class="mcq-set-tab-inner">
                    <h3><?php _e('Questions Management', 'mcqhome'); ?></h3>
                    <p class="description"><?php _e('Create and manage questions for this MCQ set. You can add questions inline or select from existing questions.', 'mcqhome'); ?></p>
                    
                    <!-- Question Creation Interface -->
                    <div class="mcq-set-question-builder">
                        <div class="mcq-set-question-tabs">
                            <button type="button" class="button button-primary mcq-set-tab-btn active" data-tab="create"><?php _e('Create New Question', 'mcqhome'); ?></button>
                            <button type="button" class="button button-secondary mcq-set-tab-btn" data-tab="existing"><?php _e('Add Existing Questions', 'mcqhome'); ?></button>
                        </div>
                        
                        <!-- Create New Question Tab -->
                        <div class="mcq-set-question-tab-content" id="mcq-create-tab">
                            <div class="mcq-set-new-question-form">
                                <div class="mcq-set-question-field">
                                    <label><strong><?php _e('Question', 'mcqhome'); ?></strong></label>
                                    <p class="description"><?php _e('Write your question using the rich text editor. You can add images, videos, audio, and other media to create engaging questions.', 'mcqhome'); ?></p>
                                    <?php
                                    wp_editor('', 'mcq_new_question_text', [
                                        'textarea_name' => 'mcq_new_question_text',
                                        'media_buttons' => true,
                                        'textarea_rows' => 8,
                                        'teeny' => false,
                                        'textarea_class' => 'mcq-form-field mcq-question-editor',
                                        'tinymce' => [
                                            'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,blockquote,|,link,unlink,|,image,media,|,spellchecker,fullscreen,wp_adv',
                                            'toolbar2' => 'formatselect,fontselect,fontsizeselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify,|,indent,outdent,|,undo,redo',
                                            'content_css' => get_template_directory_uri() . '/assets/css/mcq-editor.css',
                                            'body_class' => 'mcq-question-content',
                                            'plugins' => 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                                            'setup' => 'function(editor) {
                                                editor.on("init", function() {
                                                    editor.getDoc().body.style.fontSize = "16px";
                                                    editor.getDoc().body.style.lineHeight = "1.6";
                                                });
                                            }'
                                        ],
                                        'quicktags' => [
                                            'buttons' => 'strong,em,ul,ol,li,link,img,close'
                                        ]
                                    ]);
                                    ?>
                                </div>
                                
                                <!-- Answer Options -->
                                <div class="mcq-set-options-grid">
                                    <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
                                        <div class="mcq-set-option-field">
                                            <label><strong><?php printf(__('Option %s', 'mcqhome'), $option); ?></strong></label>
                                            <p class="description"><?php _e('Add text, images, or other media for this answer option.', 'mcqhome'); ?></p>
                                            <?php
                                            wp_editor('', 'mcq_new_option_' . strtolower($option), [
                                                'textarea_name' => 'mcq_new_option_' . strtolower($option),
                                                'media_buttons' => true,
                                                'textarea_rows' => 4,
                                                'teeny' => false,
                                                'textarea_class' => 'mcq-form-field mcq-option-editor',
                                                'tinymce' => [
                                                    'toolbar1' => 'bold,italic,underline,|,bullist,numlist,|,link,unlink,|,image,media,|,forecolor,backcolor',
                                                    'content_css' => get_template_directory_uri() . '/assets/css/mcq-editor.css',
                                                    'body_class' => 'mcq-option-content',
                                                    'plugins' => 'charmap colorpicker compat3x image lists media paste textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                                                    'setup' => 'function(editor) {
                                                        editor.on("init", function() {
                                                            editor.getDoc().body.style.fontSize = "14px";
                                                            editor.getDoc().body.style.lineHeight = "1.5";
                                                        });
                                                    }'
                                                ],
                                                'quicktags' => [
                                                    'buttons' => 'strong,em,link,img,close'
                                                ]
                                            ]);
                                            ?>
                                            <label class="mcq-set-correct-option">
                                                <input type="radio" name="mcq_new_correct_answer" value="<?php echo $option; ?>">
                                                <span><?php _e('Correct Answer', 'mcqhome'); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Explanation -->
                                <div class="mcq-set-question-field">
                                    <label><strong><?php _e('Answer Explanation', 'mcqhome'); ?></strong></label>
                                    <p class="description"><?php _e('Provide a clear explanation of why the correct answer is right. This helps students learn from their mistakes.', 'mcqhome'); ?></p>
                                    <?php
                                    wp_editor('', 'mcq_new_explanation', [
                                        'textarea_name' => 'mcq_new_explanation',
                                        'media_buttons' => true,
                                        'textarea_rows' => 6,
                                        'teeny' => false,
                                        'textarea_class' => 'mcq-form-field mcq-explanation-editor',
                                        'tinymce' => [
                                            'toolbar1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,image,media,|,forecolor,backcolor',
                                            'toolbar2' => 'formatselect,|,alignleft,aligncenter,alignright,|,undo,redo',
                                            'content_css' => get_template_directory_uri() . '/assets/css/mcq-editor.css',
                                            'body_class' => 'mcq-explanation-content',
                                            'plugins' => 'charmap colorpicker compat3x image lists media paste textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                                            'setup' => 'function(editor) {
                                                editor.on("init", function() {
                                                    editor.getDoc().body.style.fontSize = "14px";
                                                    editor.getDoc().body.style.lineHeight = "1.6";
                                                });
                                            }'
                                        ],
                                        'quicktags' => [
                                            'buttons' => 'strong,em,ul,ol,li,link,img,close'
                                        ]
                                    ]);
                                    ?>
                                </div>
                                
                                <!-- Section Assignment (if sections enabled) -->
                                <div class="mcq-set-section-assignment" <?php echo $sections_enabled ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                                    <label for="mcq_new_section"><strong><?php _e('Assign to Section', 'mcqhome'); ?></strong></label>
                                    <select name="mcq_new_section" id="mcq_new_section" class="widefat mcq-form-field mcq-section-select">
                                        <option value=""><?php _e('No Section', 'mcqhome'); ?></option>
                                        <?php if (!empty($sections)): ?>
                                            <?php foreach ($sections as $section): ?>
                                                <option value="<?php echo esc_attr($section['id']); ?>"><?php echo esc_html($section['name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="mcq-set-question-actions">
                                    <button type="button" class="button button-primary mcq-set-save-question"><?php _e('Add Question', 'mcqhome'); ?></button>
                                    <button type="button" class="button button-secondary mcq-set-save-and-add-another"><?php _e('Add Question & Create Another', 'mcqhome'); ?></button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Add Existing Questions Tab -->
                        <div class="mcq-set-question-tab-content" id="mcq-existing-tab" style="display: none;">
                            <div class="mcq-set-existing-questions">
                                <p><?php _e('Select existing MCQ questions to add to this set:', 'mcqhome'); ?></p>
                                <!-- This will be populated via AJAX -->
                                <div class="mcq-set-existing-list">
                                    <p class="description"><?php _e('Loading available questions...', 'mcqhome'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Questions List -->
                    <div class="mcq-set-current-questions">
                        <h4><?php _e('Current Questions', 'mcqhome'); ?></h4>
                        <div class="mcq-set-questions-list">
                            <?php if (!empty($questions_order['questions'])): ?>
                                <?php foreach ($questions_order['questions'] as $index => $question_data): ?>
                                    <?php
                                    $mcq = get_post($question_data['mcq_id']);
                                    if (!$mcq) continue;
                                    $question_text = get_post_meta($mcq->ID, '_mcq_question_text', true);
                                    $section_name = '';
                                    if (!empty($question_data['section_id']) && !empty($sections)) {
                                        foreach ($sections as $section) {
                                            if ($section['id'] === $question_data['section_id']) {
                                                $section_name = $section['name'];
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="mcq-set-question-item" data-question-id="<?php echo $mcq->ID; ?>">
                                        <div class="mcq-set-question-handle">⋮⋮</div>
                                        <div class="mcq-set-question-content">
                                            <h5><?php echo esc_html($mcq->post_title); ?></h5>
                                            <p><?php echo esc_html(wp_trim_words(strip_tags($question_text), 15)); ?></p>
                                            <?php if ($section_name): ?>
                                                <span class="mcq-set-question-section"><?php printf(__('Section: %s', 'mcqhome'), $section_name); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mcq-set-question-actions">
                                            <button type="button" class="button button-small mcq-set-edit-question"><?php _e('Edit', 'mcqhome'); ?></button>
                                            <button type="button" class="button button-small mcq-set-remove-question"><?php _e('Remove', 'mcqhome'); ?></button>
                                        </div>
                                        <input type="hidden" name="mcq_set_questions[<?php echo $index; ?>][mcq_id]" value="<?php echo $mcq->ID; ?>">
                                        <input type="hidden" name="mcq_set_questions[<?php echo $index; ?>][section_id]" value="<?php echo esc_attr($question_data['section_id'] ?? ''); ?>">
                                        <input type="hidden" name="mcq_set_questions[<?php echo $index; ?>][order]" value="<?php echo $index + 1; ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="mcq-set-no-questions"><?php _e('No questions added yet. Create your first question above.', 'mcqhome'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Tab navigation
        $('.mcq-set-tab-link').on('click', function(e) {
            e.preventDefault();
            
            const targetTab = $(this).attr('href');
            
            // Update active tab link
            $('.mcq-set-tab-link').removeClass('active');
            $(this).addClass('active');
            
            // Update active tab content
            $('.mcq-set-tab-content').removeClass('active');
            $(targetTab).addClass('active');
        });
        
        // Pricing toggle
        $('.mcq-set-pricing-radio').on('change', function() {
            const pricingType = $(this).val();
            const priceField = $('.mcq-set-price-field');
            
            if (pricingType === 'paid') {
                priceField.slideDown(200);
            } else {
                priceField.slideUp(200);
                $('#mcq_set_price').val('');
            }
        });
        
        // Sections toggle
        $('.mcq-set-sections-enabled').on('change', function() {
            const sectionsManager = $('.mcq-set-sections-manager');
            const sectionAssignment = $('.mcq-set-section-assignment');
            
            if ($(this).is(':checked')) {
                sectionsManager.slideDown(300);
                sectionAssignment.slideDown(300);
            } else {
                sectionsManager.slideUp(300);
                sectionAssignment.slideUp(300);
            }
        });
        
        // Add new section
        let sectionIndex = $('.mcq-set-section-item').length;
        
        $('.mcq-set-add-section').on('click', function() {
            const sectionId = 'section_' + Date.now();
            const sectionHtml = `
                <div class="mcq-set-section-item" data-section-index="${sectionIndex}">
                    <div class="mcq-set-section-header">
                        <span class="mcq-set-section-handle">⋮⋮</span>
                        <input type="text" 
                               name="mcq_set_sections[${sectionIndex}][name]" 
                               placeholder="<?php _e('Section Name', 'mcqhome'); ?>" 
                               class="mcq-set-section-name mcq-form-field">
                        <button type="button" class="button mcq-set-remove-section"><?php _e('Remove', 'mcqhome'); ?></button>
                    </div>
                    <div class="mcq-set-section-description">
                        <textarea name="mcq_set_sections[${sectionIndex}][description]" 
                                  placeholder="<?php _e('Section Description (optional)', 'mcqhome'); ?>" 
                                  class="mcq-set-section-desc mcq-form-field"></textarea>
                    </div>
                    <input type="hidden" name="mcq_set_sections[${sectionIndex}][id]" value="${sectionId}">
                    <input type="hidden" name="mcq_set_sections[${sectionIndex}][order]" value="${sectionIndex + 1}" class="section-order">
                </div>
            `;
            
            $('.mcq-set-sections-list').append(sectionHtml);
            sectionIndex++;
            
            updateSectionOrder();
            updateSectionSelects();
        });
        
        // Remove section
        $(document).on('click', '.mcq-set-remove-section', function() {
            $(this).closest('.mcq-set-section-item').fadeOut(300, function() {
                $(this).remove();
                updateSectionOrder();
                updateSectionSelects();
            });
        });
        
        // Section name change
        $(document).on('input', '.mcq-set-section-name', function() {
            updateSectionSelects();
        });
        
        function updateSectionOrder() {
            $('.mcq-set-section-item').each(function(index) {
                $(this).find('.section-order').val(index + 1);
            });
        }
        
        function updateSectionSelects() {
            const sectionSelect = $('.mcq-section-select');
            const currentValue = sectionSelect.val();
            
            // Clear existing options except "No Section"
            sectionSelect.find('option:not(:first)').remove();
            
            // Add sections to select
            $('.mcq-set-section-item').each(function() {
                const sectionId = $(this).find('input[name*="[id]"]').val();
                const sectionName = $(this).find('.mcq-set-section-name').val();
                
                if (sectionName.trim()) {
                    sectionSelect.append(`<option value="${sectionId}">${sectionName}</option>`);
                }
            });
            
            // Restore previous value if it still exists
            if (currentValue && sectionSelect.find(`option[value="${currentValue}"]`).length) {
                sectionSelect.val(currentValue);
            }
        }
        
        // Thumbnail upload
        let mediaUploader;
        
        $('.mcq-set-upload-thumbnail').on('click', function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: '<?php _e('Choose Thumbnail', 'mcqhome'); ?>',
                button: {
                    text: '<?php _e('Choose Thumbnail', 'mcqhome'); ?>'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                const previewHtml = `
                    <div class="mcq-set-thumbnail-preview" style="display: block;">
                        <img src="${attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url}" 
                             alt="<?php _e('Thumbnail Preview', 'mcqhome'); ?>" 
                             style="max-width: 200px; height: auto;">
                        <button type="button" class="button mcq-set-remove-thumbnail"><?php _e('Remove', 'mcqhome'); ?></button>
                    </div>
                `;
                
                $('.mcq-set-thumbnail-upload').html(previewHtml);
                $('.mcq-set-thumbnail-id').val(attachment.id);
            });
            
            mediaUploader.open();
        });
        
        // Remove thumbnail
        $(document).on('click', '.mcq-set-remove-thumbnail', function(e) {
            e.preventDefault();
            
            const uploadHtml = `
                <button type="button" class="button mcq-set-upload-thumbnail">
                    <?php _e('Upload Thumbnail', 'mcqhome'); ?>
                </button>
            `;
            
            $('.mcq-set-thumbnail-upload').html(uploadHtml);
            $('.mcq-set-thumbnail-id').val('');
        });
        
        // Question tab switching
        $('.mcq-set-tab-btn').on('click', function() {
            const targetTab = $(this).data('tab');
            
            $('.mcq-set-tab-btn').removeClass('active');
            $(this).addClass('active');
            
            $('.mcq-set-question-tab-content').hide();
            $(`#mcq-${targetTab}-tab`).show();
        });
        
        // Make sections sortable if jQuery UI is available
        if ($.fn.sortable) {
            $('.mcq-set-sections-list').sortable({
                handle: '.mcq-set-section-handle',
                placeholder: 'mcq-section-placeholder',
                update: function() {
                    updateSectionOrder();
                }
            });
            
            $('.mcq-set-questions-list').sortable({
                handle: '.mcq-set-question-handle',
                placeholder: 'mcq-question-placeholder',
                update: function() {
                    updateQuestionOrder();
                }
            });
        }
        
        function updateQuestionOrder() {
            $('.mcq-set-question-item').each(function(index) {
                $(this).find('input[name*="[order]"]').val(index + 1);
            });
        }
        
        // Remove question
        $(document).on('click', '.mcq-set-remove-question', function() {
            $(this).closest('.mcq-set-question-item').fadeOut(300, function() {
                $(this).remove();
                updateQuestionOrder();
            });
        });
    });
    </script>
    <?php
}

/**
 * Save MCQ Set meta data
 */
function mcqhome_save_mcq_set_meta($post_id) {
    // Check if nonce is valid
    if (!isset($_POST['mcqhome_mcq_set_meta_nonce']) || !wp_verify_nonce($_POST['mcqhome_mcq_set_meta_nonce'], 'mcqhome_save_mcq_set_meta')) {
        return;
    }

    // Check if user has permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if not an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check post type
    if (get_post_type($post_id) !== 'mcq_set') {
        return;
    }

    // Save description
    if (isset($_POST['mcq_set_description'])) {
        update_post_meta($post_id, '_mcq_set_description', wp_kses_post($_POST['mcq_set_description']));
        
        // Also update post content for compatibility
        wp_update_post([
            'ID' => $post_id,
            'post_content' => wp_kses_post($_POST['mcq_set_description'])
        ]);
    }

    // Save thumbnail
    if (isset($_POST['mcq_set_thumbnail_id'])) {
        $thumbnail_id = intval($_POST['mcq_set_thumbnail_id']);
        if ($thumbnail_id > 0) {
            set_post_thumbnail($post_id, $thumbnail_id);
        } else {
            delete_post_thumbnail($post_id);
        }
    }

    // Save pricing
    if (isset($_POST['mcq_set_pricing_type'])) {
        update_post_meta($post_id, '_mcq_set_pricing_type', sanitize_text_field($_POST['mcq_set_pricing_type']));
    }
    
    if (isset($_POST['mcq_set_price'])) {
        update_post_meta($post_id, '_mcq_set_price', floatval($_POST['mcq_set_price']));
    }

    // Save assessment configuration
    $assessment_fields = [
        'mcq_set_pass_marks' => 'intval',
        'mcq_set_time_limit' => 'intval',
        'mcq_set_display_format' => 'sanitize_text_field'
    ];

    foreach ($assessment_fields as $field => $sanitize_function) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, $sanitize_function($_POST[$field]));
        }
    }

    // Save sections
    $sections_enabled = isset($_POST['mcq_set_sections_enabled']) ? '1' : '0';
    update_post_meta($post_id, '_mcq_set_sections_enabled', $sections_enabled);
    
    if (isset($_POST['mcq_set_sections']) && is_array($_POST['mcq_set_sections'])) {
        $sections = [];
        foreach ($_POST['mcq_set_sections'] as $section_data) {
            if (!empty($section_data['name'])) {
                $sections[] = [
                    'id' => sanitize_text_field($section_data['id']) ?: 'section_' . uniqid(),
                    'name' => sanitize_text_field($section_data['name']),
                    'description' => sanitize_textarea_field($section_data['description'] ?? ''),
                    'order' => intval($section_data['order'] ?? 1)
                ];
            }
        }
        update_post_meta($post_id, '_mcq_set_sections', json_encode($sections));
    }

    // Save questions order
    if (isset($_POST['mcq_set_questions']) && is_array($_POST['mcq_set_questions'])) {
        $questions = [];
        foreach ($_POST['mcq_set_questions'] as $question_data) {
            if (!empty($question_data['mcq_id'])) {
                $questions[] = [
                    'mcq_id' => intval($question_data['mcq_id']),
                    'section_id' => sanitize_text_field($question_data['section_id'] ?? ''),
                    'order' => intval($question_data['order'] ?? 1)
                ];
            }
        }
        update_post_meta($post_id, '_mcq_set_questions_order', json_encode(['questions' => $questions]));
        
        // Also save simple questions array for backward compatibility
        $question_ids = array_column($questions, 'mcq_id');
        update_post_meta($post_id, '_mcq_set_questions', $question_ids);
    }
}
add_action('save_post', 'mcqhome_save_mcq_set_meta');