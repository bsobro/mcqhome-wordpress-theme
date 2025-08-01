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
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'mcq'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-editor-help',
        'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest'       => true,
        'rest_base'          => 'mcqs',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    ];

    register_post_type('mcq', $args);
}
add_action('init', 'mcqhome_register_mcq_post_type');

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

    register_taxonomy('mcq_subject', ['mcq'], $subject_args);

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

    register_taxonomy('mcq_topic', ['mcq'], $topic_args);

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

    register_taxonomy('mcq_difficulty', ['mcq'], $difficulty_args);
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
    
    // Add checkbox and title
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    
    // Add custom columns
    $new_columns['mcq_preview'] = __('Question Preview', 'mcqhome');
    $new_columns['mcq_correct_answer'] = __('Correct Answer', 'mcqhome');
    $new_columns['mcq_subject'] = __('Subject', 'mcqhome');
    $new_columns['mcq_difficulty'] = __('Difficulty', 'mcqhome');
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
        case 'mcq_preview':
            $question_text = get_post_meta($post_id, '_mcq_question_text', true);
            if ($question_text) {
                echo '<div style="max-width: 300px; overflow: hidden;">' . wp_trim_words(strip_tags($question_text), 15, '...') . '</div>';
            } else {
                echo '<em>' . __('No question text', 'mcqhome') . '</em>';
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
    }
}
add_action('manage_mcq_posts_custom_column', 'mcqhome_mcq_admin_column_content', 10, 2);
/*
*
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
        'mcq_set_questions',
        __('MCQ Selection', 'mcqhome'),
        'mcqhome_mcq_set_questions_callback',
        'mcq_set',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_set_scoring',
        __('Scoring Configuration', 'mcqhome'),
        'mcqhome_mcq_set_scoring_callback',
        'mcq_set',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_set_display',
        __('Display & Settings', 'mcqhome'),
        'mcqhome_mcq_set_display_callback',
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
 * MCQ Set Questions meta box callback
 */
function mcqhome_mcq_set_questions_callback($post) {
    wp_nonce_field('mcqhome_save_mcq_set_meta', 'mcqhome_mcq_set_meta_nonce');
    
    $selected_mcqs = get_post_meta($post->ID, '_mcq_set_questions', true);
    if (!is_array($selected_mcqs)) {
        $selected_mcqs = [];
    }
    
    // Get all available MCQs
    $mcqs = get_posts([
        'post_type' => 'mcq',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    echo '<style>
        .mcq-selection-container { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; }
        .mcq-item { display: flex; align-items: flex-start; margin-bottom: 10px; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
        .mcq-item:hover { background: #f9f9f9; }
        .mcq-item input[type="checkbox"] { margin-right: 10px; margin-top: 2px; }
        .mcq-item-content { flex: 1; }
        .mcq-item-title { font-weight: bold; margin-bottom: 5px; }
        .mcq-item-preview { color: #666; font-size: 0.9em; }
        .mcq-item-meta { color: #999; font-size: 0.8em; margin-top: 5px; }
        .mcq-selected-count { background: #0073aa; color: white; padding: 5px 10px; border-radius: 3px; margin-bottom: 15px; display: inline-block; }
        .mcq-search-box { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .mcq-filter-buttons { margin-bottom: 15px; }
        .mcq-filter-buttons button { margin-right: 10px; padding: 5px 10px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 3px; }
        .mcq-filter-buttons button.active { background: #0073aa; color: white; }
    </style>';
    
    echo '<div class="mcq-selection-wrapper">';
    echo '<div class="mcq-selected-count">' . sprintf(__('Selected: %d questions', 'mcqhome'), count($selected_mcqs)) . '</div>';
    
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
        
        $is_selected = in_array($mcq->ID, $selected_mcqs);
        
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

    // Save selected MCQs
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

    // Save display settings
    $display_fields = [
        'mcq_set_display_format' => ['next_next', 'single_page'],
        'mcq_set_time_limit' => 'intval'
    ];

    foreach ($display_fields as $field => $validation) {
        if (isset($_POST[$field])) {
            if (is_array($validation)) {
                // Validate against allowed values
                $value = in_array($_POST[$field], $validation) ? $_POST[$field] : $validation[0];
                update_post_meta($post_id, '_' . $field, sanitize_text_field($value));
            } else {
                // Use sanitization function
                update_post_meta($post_id, '_' . $field, $validation($_POST[$field]));
            }
        }
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

    // Save pricing
    if (isset($_POST['mcq_set_pricing_type']) && in_array($_POST['mcq_set_pricing_type'], ['free', 'paid'])) {
        update_post_meta($post_id, '_mcq_set_pricing_type', sanitize_text_field($_POST['mcq_set_pricing_type']));
    }

    if (isset($_POST['mcq_set_price'])) {
        update_post_meta($post_id, '_mcq_set_price', floatval($_POST['mcq_set_price']));
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
    
    // Add custom columns
    $new_columns['mcq_count'] = __('Questions', 'mcqhome');
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
            echo '<strong>' . $count . '</strong> ' . _n('question', 'questions', $count, 'mcqhome');
            break;
            
        case 'total_marks':
            $total_marks = get_post_meta($post_id, '_mcq_set_total_marks', true);
            $passing_marks = get_post_meta($post_id, '_mcq_set_passing_marks', true);
            echo '<strong>' . ($total_marks ?: '0') . '</strong>';
            if ($passing_marks) {
                echo '<br><small>' . sprintf(__('Pass: %s', 'mcqhome'), $passing_marks) . '</small>';
            }
            break;
            
        case 'pricing':
            $pricing_type = get_post_meta($post_id, '_mcq_set_pricing_type', true);
            if ($pricing_type === 'paid') {
                $price = get_post_meta($post_id, '_mcq_set_price', true);
                echo '<span style="color: #d63638;">$' . number_format($price, 2) . '</span>';
            } else {
                echo '<span style="color: #00a32a;">' . __('Free', 'mcqhome') . '</span>';
            }
            break;
            
        case 'display_format':
            $format = get_post_meta($post_id, '_mcq_set_display_format', true);
            if ($format === 'single_page') {
                echo __('Single Page', 'mcqhome');
            } else {
                echo __('Next-Next', 'mcqhome');
            }
            break;
            
        case 'featured':
            $featured = get_post_meta($post_id, '_mcq_set_featured', true);
            if ($featured === '1') {
                echo '<span style="color: #d63638;">★ ' . __('Featured', 'mcqhome') . '</span>';
            } else {
                echo '—';
            }
            break;
    }
}
add_action('manage_mcq_set_posts_custom_column', 'mcqhome_mcq_set_admin_column_content', 10, 2);/**
 
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