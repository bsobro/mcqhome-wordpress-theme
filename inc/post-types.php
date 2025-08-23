<?php
/**
 * Custom Post Types for MCQHome Theme
 * 
 * Simplified approach: Focus on MCQ Sets and Institutions only
 * Individual MCQs are stored as meta data within MCQ Sets
 * 
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register MCQ Set Custom Post Type
 * This is the main content type that students interact with
 */
function mcqhome_register_mcq_set_post_type() {
    $labels = array(
        "name" => __("MCQ Sets", "mcqhome"),
        "singular_name" => __("MCQ Set", "mcqhome"),
        "menu_name" => __("MCQ Sets", "mcqhome"),
        "all_items" => __("All MCQ Sets", "mcqhome"),
        "add_new" => __("Add New", "mcqhome"),
        "add_new_item" => __("Add New MCQ Set", "mcqhome"),
        "edit_item" => __("Edit MCQ Set", "mcqhome"),
        "new_item" => __("New MCQ Set", "mcqhome"),
        "view_item" => __("View MCQ Set", "mcqhome"),
        "view_items" => __("View MCQ Sets", "mcqhome"),
        "search_items" => __("Search MCQ Sets", "mcqhome"),
    );

    $args = array(
        "label" => __("MCQ Sets", "mcqhome"),
        "labels" => $labels,
        "description" => "Collections of Multiple Choice Questions",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "mcq-sets",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "mcq-set", "with_front" => true),
        "query_var" => true,
        "menu_icon" => "dashicons-list-view",
        "supports" => array("title", "editor", "thumbnail", "author", "excerpt"),
        "taxonomies" => array("mcq_category", "mcq_difficulty"),
    );

    register_post_type("mcq_set", $args);
}
add_action("init", "mcqhome_register_mcq_set_post_type");

/**
 * Register Institution Custom Post Type
 */
function mcqhome_register_institution_post_type() {
    $labels = array(
        "name" => __("Institutions", "mcqhome"),
        "singular_name" => __("Institution", "mcqhome"),
        "menu_name" => __("Institutions", "mcqhome"),
        "all_items" => __("All Institutions", "mcqhome"),
        "add_new" => __("Add New", "mcqhome"),
        "add_new_item" => __("Add New Institution", "mcqhome"),
        "edit_item" => __("Edit Institution", "mcqhome"),
        "new_item" => __("New Institution", "mcqhome"),
        "view_item" => __("View Institution", "mcqhome"),
        "view_items" => __("View Institutions", "mcqhome"),
        "search_items" => __("Search Institutions", "mcqhome"),
    );

    $args = array(
        "label" => __("Institutions", "mcqhome"),
        "labels" => $labels,
        "description" => "Educational Institutions",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "institutions",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "institution", "with_front" => true),
        "query_var" => true,
        "menu_icon" => "dashicons-building",
        "supports" => array("title", "editor", "thumbnail", "author", "excerpt"),
    );

    register_post_type("institution", $args);
}
add_action("init", "mcqhome_register_institution_post_type");

/**
 * Register Custom Taxonomies
 */
function mcqhome_register_taxonomies() {
    // MCQ Category taxonomy (hierarchical - like subjects/topics)
    register_taxonomy("mcq_category", array("mcq_set"), array(
        "hierarchical" => true,
        "label" => __("Categories", "mcqhome"),
        "show_ui" => true,
        "show_admin_column" => true,
        "query_var" => true,
        "rewrite" => array("slug" => "category"),
        "show_in_rest" => true,
    ));

    // MCQ Difficulty taxonomy (non-hierarchical - easy, medium, hard)
    register_taxonomy("mcq_difficulty", array("mcq_set"), array(
        "hierarchical" => false,
        "label" => __("Difficulty Levels", "mcqhome"),
        "show_ui" => true,
        "show_admin_column" => true,
        "query_var" => true,
        "rewrite" => array("slug" => "difficulty"),
        "show_in_rest" => true,
    ));
}
add_action("init", "mcqhome_register_taxonomies");

/**
 * Add MCQ Set meta boxes for question management
 */
function mcqhome_add_mcq_set_meta_boxes() {
    add_meta_box(
        'mcq_set_questions',
        __('MCQ Questions', 'mcqhome'),
        'mcqhome_mcq_set_questions_callback',
        'mcq_set',
        'normal',
        'high'
    );

    add_meta_box(
        'mcq_set_settings',
        __('Assessment Settings', 'mcqhome'),
        'mcqhome_mcq_set_settings_callback',
        'mcq_set',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'mcqhome_add_mcq_set_meta_boxes');

/**
 * MCQ Set Questions meta box callback
 * This is where individual questions are managed within the set
 */
function mcqhome_mcq_set_questions_callback($post) {
    wp_nonce_field('mcqhome_save_mcq_set_meta', 'mcqhome_mcq_set_meta_nonce');
    
    $questions = get_post_meta($post->ID, '_mcq_set_questions', true);
    if (!is_array($questions)) {
        $questions = array();
    }
    
    echo '<div id="mcq-set-questions-container">';
    echo '<div class="mcq-set-header">';
    echo '<h4>' . __('Manage Questions in this MCQ Set', 'mcqhome') . '</h4>';
    echo '<button type="button" class="button button-primary" id="add-new-question">' . __('Add New Question', 'mcqhome') . '</button>';
    echo '</div>';
    
    echo '<div id="mcq-questions-list">';
    if (!empty($questions)) {
        foreach ($questions as $index => $question) {
            mcqhome_render_question_editor($index, $question);
        }
    } else {
        echo '<p class="no-questions">' . __('No questions added yet. Click "Add New Question" to get started.', 'mcqhome') . '</p>';
    }
    echo '</div>';
    
    echo '</div>';
    
    // Add JavaScript for question management
    echo '<script type="text/javascript">
    jQuery(document).ready(function($) {
        var questionIndex = ' . count($questions) . ';
        
        $("#add-new-question").click(function() {
            var questionHtml = ' . json_encode(mcqhome_get_question_template()) . ';
            questionHtml = questionHtml.replace(/\[INDEX\]/g, questionIndex);
            $("#mcq-questions-list").append(questionHtml);
            $(".no-questions").hide();
            questionIndex++;
        });
        
        $(document).on("click", ".remove-question", function() {
            $(this).closest(".mcq-question-item").remove();
            if ($("#mcq-questions-list .mcq-question-item").length === 0) {
                $(".no-questions").show();
            }
        });
    });
    </script>';
}

/**
 * Render individual question editor
 */
function mcqhome_render_question_editor($index, $question = array()) {
    $question = wp_parse_args($question, array(
        'question_text' => '',
        'option_a' => '',
        'option_b' => '',
        'option_c' => '',
        'option_d' => '',
        'correct_answer' => 'A',
        'explanation' => '',
        'marks' => 1
    ));
    
    echo '<div class="mcq-question-item" data-index="' . $index . '">';
    echo '<div class="mcq-question-header">';
    echo '<h5>' . sprintf(__('Question %d', 'mcqhome'), $index + 1) . '</h5>';
    echo '<button type="button" class="button button-link-delete remove-question">' . __('Remove', 'mcqhome') . '</button>';
    echo '</div>';
    
    echo '<div class="mcq-question-content">';
    
    // Question text
    echo '<div class="mcq-field">';
    echo '<label>' . __('Question Text:', 'mcqhome') . '</label>';
    echo '<textarea name="mcq_questions[' . $index . '][question_text]" rows="3" class="widefat">' . esc_textarea($question['question_text']) . '</textarea>';
    echo '</div>';
    
    // Answer options
    echo '<div class="mcq-options">';
    echo '<label>' . __('Answer Options:', 'mcqhome') . '</label>';
    $options = array('A', 'B', 'C', 'D');
    foreach ($options as $option) {
        $field_name = 'option_' . strtolower($option);
        echo '<div class="mcq-option">';
        echo '<input type="radio" name="mcq_questions[' . $index . '][correct_answer]" value="' . $option . '" ' . checked($question['correct_answer'], $option, false) . '>';
        echo '<label>' . $option . ':</label>';
        echo '<input type="text" name="mcq_questions[' . $index . '][' . $field_name . ']" value="' . esc_attr($question[$field_name]) . '" class="widefat">';
        echo '</div>';
    }
    echo '</div>';
    
    // Explanation
    echo '<div class="mcq-field">';
    echo '<label>' . __('Explanation:', 'mcqhome') . '</label>';
    echo '<textarea name="mcq_questions[' . $index . '][explanation]" rows="2" class="widefat">' . esc_textarea($question['explanation']) . '</textarea>';
    echo '</div>';
    
    // Marks
    echo '<div class="mcq-field mcq-marks">';
    echo '<label>' . __('Marks:', 'mcqhome') . '</label>';
    echo '<input type="number" name="mcq_questions[' . $index . '][marks]" value="' . esc_attr($question['marks']) . '" min="0" step="0.5" class="small-text">';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

/**
 * Get question template for JavaScript
 */
function mcqhome_get_question_template() {
    ob_start();
    mcqhome_render_question_editor('[INDEX]');
    return ob_get_clean();
}

/**
 * MCQ Set Settings meta box callback
 */
function mcqhome_mcq_set_settings_callback($post) {
    $settings = get_post_meta($post->ID, '_mcq_set_settings', true);
    $settings = wp_parse_args($settings, array(
        'time_limit' => 0,
        'passing_marks' => 70,
        'negative_marking' => 0,
        'display_format' => 'single_page',
        'show_results' => 'immediate',
        'allow_review' => 'yes',
        'is_free' => 'yes'
    ));
    
    echo '<div class="mcq-set-settings">';
    
    // Time limit
    echo '<div class="mcq-setting-field">';
    echo '<label>' . __('Time Limit (minutes):', 'mcqhome') . '</label>';
    echo '<input type="number" name="mcq_set_settings[time_limit]" value="' . esc_attr($settings['time_limit']) . '" min="0" class="widefat">';
    echo '<p class="description">' . __('0 = No time limit', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Passing marks
    echo '<div class="mcq-setting-field">';
    echo '<label>' . __('Passing Marks (%):', 'mcqhome') . '</label>';
    echo '<input type="number" name="mcq_set_settings[passing_marks]" value="' . esc_attr($settings['passing_marks']) . '" min="0" max="100" class="widefat">';
    echo '</div>';
    
    // Negative marking
    echo '<div class="mcq-setting-field">';
    echo '<label>' . __('Negative Marking:', 'mcqhome') . '</label>';
    echo '<input type="number" name="mcq_set_settings[negative_marking]" value="' . esc_attr($settings['negative_marking']) . '" min="0" step="0.25" class="widefat">';
    echo '<p class="description">' . __('Marks deducted for wrong answers', 'mcqhome') . '</p>';
    echo '</div>';
    
    // Display format
    echo '<div class="mcq-setting-field">';
    echo '<label>' . __('Display Format:', 'mcqhome') . '</label>';
    echo '<select name="mcq_set_settings[display_format]" class="widefat">';
    echo '<option value="single_page"' . selected($settings['display_format'], 'single_page', false) . '>' . __('Single Page', 'mcqhome') . '</option>';
    echo '<option value="next_next"' . selected($settings['display_format'], 'next_next', false) . '>' . __('Next-Next Format', 'mcqhome') . '</option>';
    echo '</select>';
    echo '</div>';
    
    // Free/Paid
    echo '<div class="mcq-setting-field">';
    echo '<label>' . __('Access:', 'mcqhome') . '</label>';
    echo '<select name="mcq_set_settings[is_free]" class="widefat">';
    echo '<option value="yes"' . selected($settings['is_free'], 'yes', false) . '>' . __('Free', 'mcqhome') . '</option>';
    echo '<option value="no"' . selected($settings['is_free'], 'no', false) . '>' . __('Paid', 'mcqhome') . '</option>';
    echo '</select>';
    echo '</div>';
    
    echo '</div>';
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

    // Don't save during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save questions
    if (isset($_POST['mcq_questions'])) {
        $questions = array();
        foreach ($_POST['mcq_questions'] as $question_data) {
            $questions[] = array(
                'question_text' => sanitize_textarea_field($question_data['question_text']),
                'option_a' => sanitize_text_field($question_data['option_a']),
                'option_b' => sanitize_text_field($question_data['option_b']),
                'option_c' => sanitize_text_field($question_data['option_c']),
                'option_d' => sanitize_text_field($question_data['option_d']),
                'correct_answer' => sanitize_text_field($question_data['correct_answer']),
                'explanation' => sanitize_textarea_field($question_data['explanation']),
                'marks' => floatval($question_data['marks'])
            );
        }
        update_post_meta($post_id, '_mcq_set_questions', $questions);
    }

    // Save settings
    if (isset($_POST['mcq_set_settings'])) {
        $settings = array(
            'time_limit' => intval($_POST['mcq_set_settings']['time_limit']),
            'passing_marks' => intval($_POST['mcq_set_settings']['passing_marks']),
            'negative_marking' => floatval($_POST['mcq_set_settings']['negative_marking']),
            'display_format' => sanitize_text_field($_POST['mcq_set_settings']['display_format']),
            'show_results' => sanitize_text_field($_POST['mcq_set_settings']['show_results']),
            'allow_review' => sanitize_text_field($_POST['mcq_set_settings']['allow_review']),
            'is_free' => sanitize_text_field($_POST['mcq_set_settings']['is_free'])
        );
        update_post_meta($post_id, '_mcq_set_settings', $settings);
    }
}
add_action('save_post', 'mcqhome_save_mcq_set_meta');

/**
 * Flush rewrite rules on theme activation
 */
function mcqhome_flush_rewrite_rules() {
    mcqhome_register_mcq_set_post_type();
    mcqhome_register_institution_post_type();
    mcqhome_register_taxonomies();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, "mcqhome_flush_rewrite_rules");

/**
 * Add CSS for MCQ Set admin interface
 */
function mcqhome_admin_styles($hook) {
    global $post_type;
    if ($hook == 'post.php' || $hook == 'post-new.php') {
        if ($post_type == 'mcq_set') {
            echo '<style>
            .mcq-question-item {
                border: 1px solid #ddd;
                margin: 10px 0;
                padding: 15px;
                background: #f9f9f9;
            }
            .mcq-question-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            .mcq-options {
                margin: 10px 0;
            }
            .mcq-option {
                display: flex;
                align-items: center;
                margin: 5px 0;
                gap: 10px;
            }
            .mcq-option input[type="radio"] {
                margin: 0;
            }
            .mcq-option label {
                min-width: 20px;
                font-weight: bold;
            }
            .mcq-field {
                margin: 10px 0;
            }
            .mcq-marks {
                max-width: 100px;
            }
            .mcq-set-settings .mcq-setting-field {
                margin: 15px 0;
            }
            .mcq-set-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            </style>';
        }
    }
}
add_action('admin_head', 'mcqhome_admin_styles');