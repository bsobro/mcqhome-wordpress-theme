<?php
/**
 * Admin interface for Legacy Redirect System
 * 
 * Provides admin tools for managing MCQ redirects, viewing statistics,
 * and maintaining the redirect mapping system.
 *
 * @package MCQHome
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MCQHome_Redirect_Admin
 * 
 * Handles admin interface for redirect management
 */
class MCQHome_Redirect_Admin {

    /**
     * Initialize admin interface
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_init', [__CLASS__, 'handle_admin_actions']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
        add_action('wp_ajax_rebuild_redirect_mappings', [__CLASS__, 'ajax_rebuild_mappings']);
        add_action('wp_ajax_clear_orphaned_mcqs', [__CLASS__, 'ajax_clear_orphaned_mcqs']);
    }

    /**
     * Add admin menu page
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=mcq_set',
            __('Redirect Management', 'mcqhome'),
            __('Redirects', 'mcqhome'),
            'manage_options',
            'mcq-redirects',
            [__CLASS__, 'admin_page']
        );
    }

    /**
     * Handle admin actions
     */
    public static function handle_admin_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['action']) && wp_verify_nonce($_POST['_wpnonce'], 'mcq_redirect_admin')) {
            switch ($_POST['action']) {
                case 'rebuild_mappings':
                    $count = MCQHome_Legacy_Redirect_System::rebuild_redirect_mappings();
                    add_settings_error(
                        'mcq_redirects',
                        'mappings_rebuilt',
                        sprintf(__('Successfully rebuilt %d redirect mappings.', 'mcqhome'), $count),
                        'updated'
                    );
                    break;

                case 'clear_orphaned':
                    delete_option('mcqhome_orphaned_mcqs');
                    add_settings_error(
                        'mcq_redirects',
                        'orphaned_cleared',
                        __('Orphaned MCQ list cleared.', 'mcqhome'),
                        'updated'
                    );
                    break;
            }
        }
    }

    /**
     * Enqueue admin scripts
     */
    public static function enqueue_admin_scripts($hook) {
        if ($hook !== 'mcq_set_page_mcq-redirects') {
            return;
        }

        wp_enqueue_script(
            'mcq-redirect-admin',
            get_template_directory_uri() . '/assets/js/redirect-admin.js',
            ['jquery'],
            MCQHOME_VERSION,
            true
        );

        wp_localize_script('mcq-redirect-admin', 'mcqRedirectAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mcq_redirect_admin'),
            'confirmRebuild' => __('Are you sure you want to rebuild all redirect mappings? This may take a while.', 'mcqhome'),
            'confirmClear' => __('Are you sure you want to clear the orphaned MCQ list?', 'mcqhome'),
            'rebuilding' => __('Rebuilding mappings...', 'mcqhome'),
            'clearing' => __('Clearing orphaned MCQs...', 'mcqhome'),
            'success' => __('Operation completed successfully.', 'mcqhome'),
            'error' => __('An error occurred. Please try again.', 'mcqhome')
        ]);

        wp_enqueue_style(
            'mcq-redirect-admin',
            get_template_directory_uri() . '/assets/css/redirect-admin.css',
            [],
            MCQHOME_VERSION
        );
    }

    /**
     * Admin page content
     */
    public static function admin_page() {
        $stats = MCQHome_Legacy_Redirect_System::get_redirect_statistics();
        ?>
        <div class="wrap">
            <h1><?php _e('MCQ Redirect Management', 'mcqhome'); ?></h1>
            
            <?php settings_errors('mcq_redirects'); ?>
            
            <div class="mcq-redirect-admin">
                
                <!-- Statistics Overview -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Redirect Statistics', 'mcqhome'); ?></h2>
                    <div class="inside">
                        <div class="mcq-stats-grid">
                            <div class="mcq-stat-item">
                                <div class="mcq-stat-number"><?php echo number_format($stats['total_mappings']); ?></div>
                                <div class="mcq-stat-label"><?php _e('Total Redirect Mappings', 'mcqhome'); ?></div>
                            </div>
                            
                            <div class="mcq-stat-item">
                                <div class="mcq-stat-number"><?php echo count($stats['orphaned_mcqs']); ?></div>
                                <div class="mcq-stat-label"><?php _e('Orphaned MCQs', 'mcqhome'); ?></div>
                            </div>
                            
                            <div class="mcq-stat-item">
                                <div class="mcq-stat-number"><?php echo count($stats['top_redirected_sets']); ?></div>
                                <div class="mcq-stat-label"><?php _e('Active MCQ Sets', 'mcqhome'); ?></div>
                            </div>
                        </div>
                        
                        <p class="description">
                            <?php _e('These statistics show the current state of your MCQ redirect system. Redirect mappings are automatically created when MCQs are assigned to MCQ sets.', 'mcqhome'); ?>
                        </p>
                    </div>
                </div>

                <!-- Management Actions -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Management Actions', 'mcqhome'); ?></h2>
                    <div class="inside">
                        <form method="post" action="">
                            <?php wp_nonce_field('mcq_redirect_admin'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Rebuild Redirect Mappings', 'mcqhome'); ?></th>
                                    <td>
                                        <button type="submit" name="action" value="rebuild_mappings" class="button button-secondary" id="rebuild-mappings">
                                            <?php _e('Rebuild All Mappings', 'mcqhome'); ?>
                                        </button>
                                        <p class="description">
                                            <?php _e('Rebuilds all redirect mappings by scanning MCQ sets. Use this if redirects are not working properly.', 'mcqhome'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row"><?php _e('Clear Orphaned MCQs', 'mcqhome'); ?></th>
                                    <td>
                                        <button type="submit" name="action" value="clear_orphaned" class="button button-secondary" id="clear-orphaned">
                                            <?php _e('Clear Orphaned List', 'mcqhome'); ?>
                                        </button>
                                        <p class="description">
                                            <?php _e('Clears the list of orphaned MCQs that have been accessed but have no parent MCQ set.', 'mcqhome'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>

                <!-- Top Redirected MCQ Sets -->
                <?php if (!empty($stats['top_redirected_sets'])): ?>
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Most Redirected MCQ Sets', 'mcqhome'); ?></h2>
                    <div class="inside">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('MCQ Set', 'mcqhome'); ?></th>
                                    <th><?php _e('Redirect Count', 'mcqhome'); ?></th>
                                    <th><?php _e('Actions', 'mcqhome'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['top_redirected_sets'] as $set): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <a href="<?php echo get_edit_post_link($set['id']); ?>">
                                                <?php echo esc_html($set['title']); ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td><?php echo number_format($set['redirect_count']); ?></td>
                                    <td>
                                        <a href="<?php echo get_permalink($set['id']); ?>" class="button button-small" target="_blank">
                                            <?php _e('View', 'mcqhome'); ?>
                                        </a>
                                        <a href="<?php echo get_edit_post_link($set['id']); ?>" class="button button-small">
                                            <?php _e('Edit', 'mcqhome'); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Orphaned MCQs -->
                <?php if (!empty($stats['orphaned_mcqs'])): ?>
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Orphaned MCQs', 'mcqhome'); ?></h2>
                    <div class="inside">
                        <p class="description">
                            <?php _e('These MCQs have been accessed via direct URLs but are not assigned to any MCQ set. Consider assigning them to MCQ sets or deleting them.', 'mcqhome'); ?>
                        </p>
                        
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('MCQ ID', 'mcqhome'); ?></th>
                                    <th><?php _e('Title', 'mcqhome'); ?></th>
                                    <th><?php _e('Status', 'mcqhome'); ?></th>
                                    <th><?php _e('Actions', 'mcqhome'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($stats['orphaned_mcqs'], 0, 20) as $mcq_id): ?>
                                <?php 
                                $mcq = get_post($mcq_id);
                                $status = $mcq ? get_post_status($mcq) : 'deleted';
                                ?>
                                <tr>
                                    <td><?php echo $mcq_id; ?></td>
                                    <td>
                                        <?php if ($mcq): ?>
                                            <a href="<?php echo get_edit_post_link($mcq_id); ?>">
                                                <?php echo esc_html($mcq->post_title); ?>
                                            </a>
                                        <?php else: ?>
                                            <em><?php _e('Post not found', 'mcqhome'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="mcq-status mcq-status-<?php echo $status; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($mcq && $status === 'publish'): ?>
                                            <a href="<?php echo get_edit_post_link($mcq_id); ?>" class="button button-small">
                                                <?php _e('Edit', 'mcqhome'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (count($stats['orphaned_mcqs']) > 20): ?>
                        <p class="description">
                            <?php printf(__('Showing first 20 of %d orphaned MCQs.', 'mcqhome'), count($stats['orphaned_mcqs'])); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- SEO Information -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('SEO Configuration', 'mcqhome'); ?></h2>
                    <div class="inside">
                        <h4><?php _e('Current SEO Settings', 'mcqhome'); ?></h4>
                        <ul class="mcq-seo-list">
                            <li>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('MCQ posts excluded from XML sitemaps', 'mcqhome'); ?>
                            </li>
                            <li>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('MCQ URLs disallowed in robots.txt', 'mcqhome'); ?>
                            </li>
                            <li>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('301 redirects implemented for legacy MCQ URLs', 'mcqhome'); ?>
                            </li>
                            <li>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('Canonical URLs point to MCQ sets', 'mcqhome'); ?>
                            </li>
                            <li>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e('MCQ pages marked with noindex meta tag', 'mcqhome'); ?>
                            </li>
                        </ul>
                        
                        <h4><?php _e('Robots.txt Configuration', 'mcqhome'); ?></h4>
                        <p class="description">
                            <?php _e('The following rules are automatically added to your robots.txt:', 'mcqhome'); ?>
                        </p>
                        <pre class="mcq-robots-preview">
# MCQHome Theme - Exclude individual MCQ pages
Disallow: /mcq/
Disallow: */mcq/*

# Allow MCQ Sets
Allow: /mcq-set/
Allow: */mcq-set/*</pre>
                        
                        <p>
                            <a href="<?php echo home_url('/robots.txt'); ?>" target="_blank" class="button button-secondary">
                                <?php _e('View robots.txt', 'mcqhome'); ?>
                            </a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for rebuilding mappings
     */
    public static function ajax_rebuild_mappings() {
        check_ajax_referer('mcq_redirect_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'mcqhome'));
        }

        $count = MCQHome_Legacy_Redirect_System::rebuild_redirect_mappings();
        
        wp_send_json_success([
            'message' => sprintf(__('Successfully rebuilt %d redirect mappings.', 'mcqhome'), $count),
            'count' => $count
        ]);
    }

    /**
     * AJAX handler for clearing orphaned MCQs
     */
    public static function ajax_clear_orphaned_mcqs() {
        check_ajax_referer('mcq_redirect_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'mcqhome'));
        }

        delete_option('mcqhome_orphaned_mcqs');
        
        wp_send_json_success([
            'message' => __('Orphaned MCQ list cleared successfully.', 'mcqhome')
        ]);
    }
}

// Initialize admin interface
MCQHome_Redirect_Admin::init();