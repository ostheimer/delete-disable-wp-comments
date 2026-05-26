<?php
/**
 * Plugin Name: Delete & Disable Comments
 * Plugin URI: https://github.com/ostheimer/delete-disable-wp-comments
 * Description: A WordPress plugin that helps site administrators manage comments by deleting spam comments, removing all comments with backup, or disabling comments site-wide.
 * Version: 1.0.6
 * Author: Andreas Ostheimer
 * Author URI: https://github.com/ostheimer
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: delete-disable-comments
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Removed direct loading of wp-load.php via require_once to comply with review.

// Define plugin constants
define('DDWPC_VERSION', '1.0.6');
define('DDWPC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DDWPC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include backend functions
require_once DDWPC_PLUGIN_DIR . 'includes/functions.php';
require_once DDWPC_PLUGIN_DIR . 'admin/admin-page.php';

// Enqueue admin styles and scripts
function ddwpc_admin_enqueue_scripts($hook) {
    // Only load on our plugin's admin page
    if ($hook !== 'tools_page_delete-disable-comments') {
        return;
    }

    wp_enqueue_style(
        'ddwpc-admin-style',
        DDWPC_PLUGIN_URL . 'css/admin-style.css',
        array(),
        DDWPC_VERSION
    );

    wp_enqueue_script(
        'ddwpc-admin-script',
        DDWPC_PLUGIN_URL . 'js/admin-script.js',
        array('jquery'),
        DDWPC_VERSION,
        true
    );

    wp_localize_script(
        'ddwpc-admin-script',
        'ddwpcAjax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ddwpc_nonce'),
            'error_toggling' => esc_html__('Error toggling comments.', 'delete-disable-comments'),
            'network_error' => esc_html__('Network error while updating comments.', 'delete-disable-comments'),
            'comments_disabled' => esc_html__('Comments are currently disabled', 'delete-disable-comments'),
            'comments_enabled' => esc_html__('Comments are currently enabled', 'delete-disable-comments'),
            'confirm_delete_spam' => esc_html__('Do you really want to delete all spam comments?', 'delete-disable-comments'),
            'confirm_delete_all' => esc_html__('Do you really want to delete ALL comments? This action cannot be undone!', 'delete-disable-comments'),
            'deleting' => esc_html__('Deleting...', 'delete-disable-comments'),
            'updating' => esc_html__('Updating...', 'delete-disable-comments'),
            'creating_backup' => esc_html__('Creating backup...', 'delete-disable-comments'),
            'success_delete_spam' => esc_html__('Spam comments have been successfully deleted.', 'delete-disable-comments'),
            'success_delete_all' => esc_html__('All comments have been successfully deleted.', 'delete-disable-comments'),
            'error_delete_spam' => esc_html__('Error deleting spam comments.', 'delete-disable-comments'),
            'error_delete_all' => esc_html__('Error deleting all comments.', 'delete-disable-comments'),
            'network_error_spam' => esc_html__('Network error while deleting spam comments.', 'delete-disable-comments'),
            'network_error_all' => esc_html__('Network error while deleting all comments.', 'delete-disable-comments'),
            'delete_spam_button' => esc_html__('Delete Spam Comments', 'delete-disable-comments'),
            'delete_all_button' => esc_html__('Delete All Comments', 'delete-disable-comments'),
            'backup_button' => esc_html__('Download Backup', 'delete-disable-comments'),
            'close_all_now_button' => esc_html__('Close all comments now', 'delete-disable-comments'),
            'closing_now' => esc_html__('Closing...', 'delete-disable-comments'),
            'error_close_all_now' => esc_html__('Error closing comments.', 'delete-disable-comments'),
            'network_error_close_all_now' => esc_html__('Network error while closing comments.', 'delete-disable-comments'),
            'error_loading_status' => esc_html__('Error loading status.', 'delete-disable-comments'),
        )
    );
}
add_action('admin_enqueue_scripts', 'ddwpc_admin_enqueue_scripts');

// Add admin menu
function ddwpc_admin_menu() {
    $menu_title = esc_html__('Delete & Disable Comments', 'delete-disable-comments');
    $page_title = esc_html__('Delete & Disable Comments', 'delete-disable-comments');
    
    add_submenu_page(
        'tools.php',           
        $page_title,          
        $menu_title,          
        'manage_options',     
        'delete-disable-comments', 
        'ddwpc_admin_page' 
    );
}
add_action('admin_menu', 'ddwpc_admin_menu');

// Activation Hook
function ddwpc_activate() {
    // Seed default option only on first activation; do not overwrite existing user choice on reactivation.
    add_option('ddwpc_disable_comments', '0');

    // If the operator already had the "disable comments" toggle on (e.g. plugin was reinstalled),
    // make sure the WordPress defaults reflect that and that all posts in the DB are actually closed.
    if (ddwpc_is_disable_comments_enabled()) {
        ddwpc_apply_disable_comments_defaults();
        ddwpc_close_all_post_comments_in_db();
    }
}
register_activation_hook(__FILE__, 'ddwpc_activate');

// Deactivation Hook
function ddwpc_deactivate() {
    // Note: We intentionally keep the user's `ddwpc_disable_comments` setting in place,
    // so that re-activating the plugin restores the previous behaviour. WordPress core
    // deletes plugin data only via uninstall.php, which we leave to a future iteration.
    delete_option('ddwpc_disable_comments');
}
register_deactivation_hook(__FILE__, 'ddwpc_deactivate');

/**
 * Whether the operator has switched the plugin into "disable comments site-wide" mode.
 *
 * Wrapper around get_option() that normalises every truthy representation
 * the option has historically been stored as ('1', 1, true, 'true').
 *
 * @since 1.0.2
 * @return bool
 */
function ddwpc_is_disable_comments_enabled() {
    $value = get_option('ddwpc_disable_comments', '0');
    return in_array($value, array('1', 1, true, 'true'), true);
}

/**
 * Persist the WordPress defaults that match the plugin state.
 *
 * Idempotent: only writes when the current value differs from the desired value,
 * which avoids unnecessary autoload churn and DB writes.
 *
 * @since 1.0.2
 * @param bool $disabled True to apply "comments off" defaults, false to leave defaults untouched.
 * @return void
 */
function ddwpc_apply_disable_comments_defaults($disabled = true) {
    if ($disabled) {
        if (get_option('default_comment_status') !== 'closed') {
            update_option('default_comment_status', 'closed');
        }
        if (get_option('default_ping_status') !== 'closed') {
            update_option('default_ping_status', 'closed');
        }
    }
}

/**
 * Close comments and pings on every post directly in the database.
 *
 * Uses $wpdb->query() with a single UPDATE statement instead of wp_update_post()
 * to avoid triggering save_post / transition_post_status / wp_after_insert_post
 * hooks. Those hooks are expensive and have been observed to trigger fatal errors
 * in third-party plugins (e.g. WPML) when invoked from the public init hook.
 *
 * Idempotent: the WHERE clause skips posts that are already closed.
 *
 * @since 1.0.2
 * @return int Number of posts whose comment_status / ping_status was changed.
 */
function ddwpc_close_all_post_comments_in_db() {
    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional single bulk UPDATE; avoids per-post wp_update_post() and save_post hooks.
    $rows = $wpdb->query(
        "UPDATE {$wpdb->posts}
         SET comment_status = 'closed', ping_status = 'closed'
         WHERE comment_status <> 'closed' OR ping_status <> 'closed'"
    );

    if (false === $rows) {
        return 0;
    }

    // Comment count caches and post-meta caches don't depend on these columns,
    // but other caches that key on the post object should be invalidated.
    ddwpc_invalidate_posts_cache();

    return (int) $rows;
}

/**
 * Bump the 'posts' cache group's last_changed marker so any downstream caches
 * keyed off get_posts() pick up the new comment status.
 *
 * Wrapped in its own function so it can be stubbed in unit tests.
 *
 * @since 1.0.2
 * @return void
 */
function ddwpc_invalidate_posts_cache() {
    if (function_exists('wp_cache_set_last_changed')) {
        wp_cache_set_last_changed('posts');
    } else {
        wp_cache_set('last_changed', microtime(), 'posts');
    }
}

/**
 * Count how many posts in the DB still have comments or pings open.
 *
 * Used by the admin UI to show whether the operator should run the
 * "Close all comments now" maintenance action.
 *
 * @since 1.0.2
 * @return int
 */
function ddwpc_count_posts_with_open_comments() {
    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional aggregate COUNT for admin maintenance UI; not suitable for object-cache wrapping.
    $count = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE comment_status <> 'closed' OR ping_status <> 'closed'"
    );

    return (int) $count;
}

// Initialize the plugin
function ddwpc_init() {
    // Bail out cheaply if the operator did not enable site-wide disabling.
    if ( ! ddwpc_is_disable_comments_enabled() ) {
        return;
    }

    // Remove comment / trackback support from public post types.
    // Note: this is in-memory only, no DB writes, so it is safe on every request.
    $post_types = get_post_types(array('public' => true), 'names');
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }

    // Filter to ensure comments are closed.
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);

    // Remove comment-related menu items.
    add_action('admin_menu', function() {
        remove_menu_page('edit-comments.php');
    });

    // Hide comment counts in admin bar.
    add_action('wp_before_admin_bar_render', function() {
        global $wp_admin_bar;
        if ($wp_admin_bar instanceof WP_Admin_Bar) {
            $wp_admin_bar->remove_menu('comments');
        }
    });

    // Additional filters to hide comments UI.
    add_filter('comments_template', 'ddwpc_use_blank_template', 20);

    // Block comment feeds.
    add_action('template_redirect', 'ddwpc_block_comment_feed');

    // Remove comments from admin bar.
    add_action('admin_init', function() {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    });

    // Remove comments CSS from theme.
    add_action('wp_enqueue_scripts', 'ddwpc_dequeue_comment_styles');

    // Hide existing comments.
    add_filter('comments_array', '__return_empty_array', 20);

    // Disable comments REST API endpoint.
    add_filter('rest_endpoints', 'ddwpc_disable_rest_endpoints_filter');

    // Remove comment-related blocks.
    add_filter('allowed_block_types_all', 'ddwpc_remove_comment_blocks');

    // Remove comment links from post meta.
    add_filter('post_comments_feed_link', '__return_false');
    add_filter('comments_link_feed', '__return_false');
    add_filter('comment_link', '__return_false');
    add_filter('get_comments_link', '__return_false');
    add_filter('get_comments_number', '__return_zero');

    // Remove comment-related widgets.
    add_action('widgets_init', 'ddwpc_unregister_comment_widgets');

    // Remove comment form.
    add_filter('comments_template_query_args', '__return_empty_array');

    // Remove comment patterns from theme.
    add_filter('theme_file_path', 'ddwpc_filter_theme_comment_patterns', 10, 2);
}
add_action('init', 'ddwpc_init', 100);

// Callback Functions (previously anonymous or defined elsewhere)

function ddwpc_use_blank_template($template) {
    if (ddwpc_is_disable_comments_enabled()) {
        return DDWPC_PLUGIN_DIR . 'templates/blank.php';
    }
    return $template;
}

function ddwpc_block_comment_feed() {
    if (ddwpc_is_disable_comments_enabled() && is_comment_feed()) {
        wp_die(esc_html__('Comments are closed.', 'delete-disable-comments'), '', array('response' => 403));
    }
}

function ddwpc_dequeue_comment_styles() {
    if (ddwpc_is_disable_comments_enabled()) {
        wp_dequeue_style('comments-template');
        wp_dequeue_style('twentytwentyfive-comments');
        wp_dequeue_style('wp-block-comments');
        wp_dequeue_style('wp-block-comments-form');
    }
}

function ddwpc_disable_rest_endpoints_filter($endpoints) {
    if (ddwpc_is_disable_comments_enabled()) {
        if (isset($endpoints['/wp/v2/comments'])) {
            unset($endpoints['/wp/v2/comments']);
        }
        if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
        }
    }
    return $endpoints;
}

function ddwpc_remove_comment_blocks($allowed_blocks) {
    if (ddwpc_is_disable_comments_enabled()) {
        if (!is_array($allowed_blocks)) {
            return $allowed_blocks;
        }
        return array_diff($allowed_blocks, array(
            'core/comments',
            'core/comments-title',
            'core/comments-pagination',
            'core/comments-pagination-next',
            'core/comments-pagination-previous',
            'core/comments-pagination-numbers',
            'core/comment-template',
            'core/post-comments-form',
        ));
    }
    return $allowed_blocks;
}

function ddwpc_unregister_comment_widgets() {
    if (ddwpc_is_disable_comments_enabled()) {
        unregister_widget('WP_Widget_Recent_Comments');
    }
}

function ddwpc_filter_theme_comment_patterns($path, $file) {
    if (ddwpc_is_disable_comments_enabled() && $file === 'patterns/comments.php') {
        return DDWPC_PLUGIN_DIR . 'templates/blank.php';
    }
    return $path;
}

add_action('init', 'ddwpc_register_ajax_handlers');
