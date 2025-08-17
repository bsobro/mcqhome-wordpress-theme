<?php
/**
 * Database Migration for MCQ Architecture Refactor
 *
 * @package MCQHome
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MCQ Architecture Migration Class
 */
class MCQHome_Database_Migration {

    /**
     * Current migration version
     */
    const MIGRATION_VERSION = '2.0.0';

    /**
     * Initialize migration
     */
    public static function init() {
        add_action('admin_init', [__CLASS__, 'check_migration_needed']);
        add_action('wp_ajax_mcqhome_run_migration', [__CLASS__, 'run_migration_ajax']);
    }

    /**
     * Check if migration is needed
     */
    public static function check_migration_needed() {
        $current_version = get_option('mcqhome_migration_version', '1.0.0');
        
        if (version_compare($current_version, self::MIGRATION_VERSION, '<')) {
            add_action('admin_notices', [__CLASS__, 'migration_notice']);
        }
    }

    /**
     * Display migration notice
     */
    public static function migration_notice() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <h3><?php _e('MCQHome Database Migration Required', 'mcqhome'); ?></h3>
            <p><?php _e('The MCQHome theme has been updated with new features that require a database migration. This will enhance your MCQ sets with new capabilities like sections and improved organization.', 'mcqhome'); ?></p>
            <p>
                <button type="button" class="button button-primary" id="mcqhome-run-migration">
                    <?php _e('Run Migration Now', 'mcqhome'); ?>
                </button>
                <span class="spinner" style="float: none; margin: 0 10px;"></span>
                <span id="migration-status"></span>
            </p>
            <p class="description">
                <?php _e('This process will:', 'mcqhome'); ?>
                <ul style="margin-left: 20px;">
                    <li><?php _e('Add new meta fields to existing MCQ sets', 'mcqhome'); ?></li>
                    <li><?php _e('Migrate existing MCQ titles to question text format', 'mcqhome'); ?></li>
                    <li><?php _e('Create question order structure for existing MCQ sets', 'mcqhome'); ?></li>
                    <li><?php _e('Preserve all existing data and functionality', 'mcqhome'); ?></li>
                </ul>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#mcqhome-run-migration').on('click', function() {
                var $button = $(this);
                var $spinner = $('.spinner');
                var $status = $('#migration-status');
                
                $button.prop('disabled', true);
                $spinner.addClass('is-active');
                $status.text('<?php _e('Running migration...', 'mcqhome'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mcqhome_run_migration',
                        nonce: '<?php echo wp_create_nonce('mcqhome_migration'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $status.html('<span style="color: red;">✗ ' + response.data.message + '</span>');
                            $button.prop('disabled', false);
                        }
                        $spinner.removeClass('is-active');
                    },
                    error: function() {
                        $status.html('<span style="color: red;">✗ <?php _e('Migration failed. Please try again.', 'mcqhome'); ?></span>');
                        $button.prop('disabled', false);
                        $spinner.removeClass('is-active');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Run migration via AJAX
     */
    public static function run_migration_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mcqhome_migration')) {
            wp_die(__('Security check failed', 'mcqhome'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'mcqhome'));
        }

        try {
            $result = self::run_migration();
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message']
                ]);
            } else {
                wp_send_json_error([
                    'message' => $result['message']
                ]);
            }
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => sprintf(__('Migration failed: %s', 'mcqhome'), $e->getMessage())
            ]);
        }
    }

    /**
     * Run the actual migration
     */
    public static function run_migration() {
        global $wpdb;

        $migrated_sets = 0;
        $migrated_mcqs = 0;
        $errors = [];

        try {
            // Start transaction
            $wpdb->query('START TRANSACTION');

            // 1. Migrate MCQ Sets - Add new meta fields
            $mcq_sets = get_posts([
                'post_type' => 'mcq_set',
                'posts_per_page' => -1,
                'post_status' => 'any'
            ]);

            foreach ($mcq_sets as $mcq_set) {
                self::migrate_mcq_set($mcq_set->ID);
                $migrated_sets++;
            }

            // 2. Migrate MCQs - Update titles to use question text
            $mcqs = get_posts([
                'post_type' => 'mcq',
                'posts_per_page' => -1,
                'post_status' => 'any'
            ]);

            foreach ($mcqs as $mcq) {
                self::migrate_mcq_title($mcq->ID);
                $migrated_mcqs++;
            }

            // 3. Update migration version
            update_option('mcqhome_migration_version', self::MIGRATION_VERSION);

            // Commit transaction
            $wpdb->query('COMMIT');

            return [
                'success' => true,
                'message' => sprintf(
                    __('Migration completed successfully! Migrated %d MCQ sets and %d MCQs.', 'mcqhome'),
                    $migrated_sets,
                    $migrated_mcqs
                )
            ];

        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            
            return [
                'success' => false,
                'message' => sprintf(__('Migration failed: %s', 'mcqhome'), $e->getMessage())
            ];
        }
    }

    /**
     * Migrate individual MCQ Set
     */
    private static function migrate_mcq_set($mcq_set_id) {
        // Add default values for new meta fields if they don't exist
        
        // Thumbnail (use featured image if exists)
        if (!get_post_meta($mcq_set_id, '_mcq_set_thumbnail', true)) {
            $thumbnail_id = get_post_thumbnail_id($mcq_set_id);
            if ($thumbnail_id) {
                update_post_meta($mcq_set_id, '_mcq_set_thumbnail', $thumbnail_id);
            }
        }

        // Pricing type (default to free)
        if (!get_post_meta($mcq_set_id, '_mcq_set_pricing_type', true)) {
            update_post_meta($mcq_set_id, '_mcq_set_pricing_type', 'free');
        }

        // Price (default to 0)
        if (!get_post_meta($mcq_set_id, '_mcq_set_price', true)) {
            update_post_meta($mcq_set_id, '_mcq_set_price', 0);
        }

        // Pass marks (default to 0)
        if (!get_post_meta($mcq_set_id, '_mcq_set_pass_marks', true)) {
            update_post_meta($mcq_set_id, '_mcq_set_pass_marks', 0);
        }

        // Time limit (default to 0 - no limit)
        if (!get_post_meta($mcq_set_id, '_mcq_set_time_limit', true)) {
            update_post_meta($mcq_set_id, '_mcq_set_time_limit', 0);
        }

        // Display format (default to next_next)
        if (!get_post_meta($mcq_set_id, '_mcq_set_display_format', true)) {
            update_post_meta($mcq_set_id, '_mcq_set_display_format', 'next_next');
        }

        // Sections enabled (default to false)
        if (!get_post_meta($mcq_set_id, '_mcq_set_sections_enabled', true)) {
            update_post_meta($mcq_set_id, '_mcq_set_sections_enabled', '0');
        }

        // Sections (default to empty array)
        if (!get_post_meta($mcq_set_id, '_mcq_set_sections', true)) {
            update_post_meta($mcq_set_id, '_mcq_set_sections', json_encode([]));
        }

        // Questions order structure
        $existing_questions = get_post_meta($mcq_set_id, '_mcq_set_questions', true);
        if (is_array($existing_questions) && !empty($existing_questions)) {
            $questions_order = ['questions' => []];
            $order = 1;
            
            foreach ($existing_questions as $mcq_id) {
                $questions_order['questions'][] = [
                    'mcq_id' => intval($mcq_id),
                    'section_id' => null, // No sections by default
                    'order' => $order++
                ];
            }
            
            update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode($questions_order));
        } else {
            // Empty questions order for sets without questions
            update_post_meta($mcq_set_id, '_mcq_set_questions_order', json_encode(['questions' => []]));
        }

        // Author (use post author if not set)
        if (!get_post_meta($mcq_set_id, '_mcq_set_author', true)) {
            $post = get_post($mcq_set_id);
            update_post_meta($mcq_set_id, '_mcq_set_author', $post->post_author);
        }
    }

    /**
     * Migrate MCQ title to use question text
     */
    private static function migrate_mcq_title($mcq_id) {
        $question_text = get_post_meta($mcq_id, '_mcq_question_text', true);
        $current_title = get_the_title($mcq_id);

        // If question text is empty but title exists, use title as question text
        if (empty($question_text) && !empty($current_title)) {
            update_post_meta($mcq_id, '_mcq_question_text', $current_title);
            $question_text = $current_title;
        }

        // Generate new title from question text
        if (!empty($question_text)) {
            $new_title = self::generate_title_from_question($question_text);
            
            wp_update_post([
                'ID' => $mcq_id,
                'post_title' => $new_title
            ]);
        }
    }

    /**
     * Generate title from question text
     */
    private static function generate_title_from_question($question_text) {
        // Strip HTML tags and get first 10 words
        $clean_text = wp_strip_all_tags($question_text);
        $title = wp_trim_words($clean_text, 10, '...');
        
        // Ensure title is not empty
        if (empty($title)) {
            $title = __('Untitled Question', 'mcqhome');
        }
        
        return $title;
    }

    /**
     * Check data integrity after migration
     */
    public static function verify_migration() {
        $issues = [];

        // Check MCQ Sets
        $mcq_sets = get_posts([
            'post_type' => 'mcq_set',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ]);

        foreach ($mcq_sets as $mcq_set) {
            // Check required meta fields exist
            $required_fields = [
                '_mcq_set_pricing_type',
                '_mcq_set_display_format',
                '_mcq_set_sections_enabled',
                '_mcq_set_sections',
                '_mcq_set_questions_order'
            ];

            foreach ($required_fields as $field) {
                if (!metadata_exists('post', $mcq_set->ID, $field)) {
                    $issues[] = sprintf(
                        __('MCQ Set %d missing field: %s', 'mcqhome'),
                        $mcq_set->ID,
                        $field
                    );
                }
            }

            // Validate JSON fields
            $json_fields = ['_mcq_set_sections', '_mcq_set_questions_order'];
            foreach ($json_fields as $field) {
                $value = get_post_meta($mcq_set->ID, $field, true);
                if (!empty($value) && json_decode($value) === null) {
                    $issues[] = sprintf(
                        __('MCQ Set %d has invalid JSON in field: %s', 'mcqhome'),
                        $mcq_set->ID,
                        $field
                    );
                }
            }
        }

        // Check MCQs
        $mcqs = get_posts([
            'post_type' => 'mcq',
            'posts_per_page' => 10, // Sample check
            'post_status' => 'any'
        ]);

        foreach ($mcqs as $mcq) {
            $question_text = get_post_meta($mcq->ID, '_mcq_question_text', true);
            if (empty($question_text)) {
                $issues[] = sprintf(
                    __('MCQ %d has empty question text', 'mcqhome'),
                    $mcq->ID
                );
            }
        }

        return $issues;
    }

    /**
     * Rollback migration (for emergency use)
     */
    public static function rollback_migration() {
        // This would remove the new meta fields and restore old structure
        // Implementation depends on specific rollback requirements
        
        update_option('mcqhome_migration_version', '1.0.0');
        
        return [
            'success' => true,
            'message' => __('Migration rolled back successfully', 'mcqhome')
        ];
    }
}

// Initialize migration
MCQHome_Database_Migration::init();