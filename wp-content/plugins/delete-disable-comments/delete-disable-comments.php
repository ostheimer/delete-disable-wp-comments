<?php
/**
 * Plugin Name: Delete & Disable Comments
 * Plugin URI: https://github.com/ostheimer/delete-disable-wp-comments
 * Description: A WordPress plugin that helps site administrators manage comments by deleting spam comments, removing all comments with backup, or disabling comments site-wide.
 * Version: 1.0.0
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

// Make sure we have access to WordPress functions
if (!defined('ABSPATH')) {
    /** Set up WordPress environment */
    require_once(dirname(dirname(dirname(__DIR__))) . '/wp-load.php');
}

// Define plugin constants
define('DDC_VERSION', '1.0.0');
define('DDC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DDC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load translations early
function ddc_load_textdomain() {
    load_plugin_textdomain(
        'delete-disable-comments',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}
add_action('plugins_loaded', 'ddc_load_textdomain');

// Include backend functions
require_once DDC_PLUGIN_DIR . 'includes/functions.php';
require_once DDC_PLUGIN_DIR . 'admin/admin-page.php';

// Enqueue admin styles and scripts
function ddc_admin_enqueue_scripts($hook) {
    // Only load on our plugin's admin page
    if ($hook !== 'tools_page_delete-disable-comments') {
        return;
    }

    wp_enqueue_style(
        'ddc-admin-style',
        DDC_PLUGIN_URL . 'css/admin-style.css',
        array(),
        DDC_VERSION
    );

    wp_enqueue_script(
        'ddc-admin-script',
        DDC_PLUGIN_URL . 'js/admin-script.js',
        array('jquery'),
        DDC_VERSION,
        true
    );

    wp_localize_script(
        'ddc-admin-script',
        'ddcAjax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ddc_nonce'),
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
            'success_backup' => esc_html__('Backup has been successfully created.', 'delete-disable-comments'),
            'error_delete_spam' => esc_html__('Error deleting spam comments.', 'delete-disable-comments'),
            'error_delete_all' => esc_html__('Error deleting all comments.', 'delete-disable-comments'),
            'error_backup' => esc_html__('Error creating backup.', 'delete-disable-comments'),
            'network_error_spam' => esc_html__('Network error while deleting spam comments.', 'delete-disable-comments'),
            'network_error_all' => esc_html__('Network error while deleting all comments.', 'delete-disable-comments'),
            'network_error_backup' => esc_html__('Network error while creating backup.', 'delete-disable-comments'),
            'delete_spam_button' => esc_html__('Delete Spam Comments', 'delete-disable-comments'),
            'delete_all_button' => esc_html__('Delete All Comments', 'delete-disable-comments'),
            'backup_button' => esc_html__('Download Backup', 'delete-disable-comments')
        )
    );
}
add_action('admin_enqueue_scripts', 'ddc_admin_enqueue_scripts');

// Add admin menu
function ddc_admin_menu() {
    $menu_title = esc_html__('Delete & Disable Comments', 'delete-disable-comments');
    $page_title = esc_html__('Delete & Disable Comments', 'delete-disable-comments');
    
    add_submenu_page(
        'tools.php',           
        $page_title,          
        $menu_title,          
        'manage_options',     
        'delete-disable-comments', 
        'ddc_admin_page' 
    );
}
add_action('admin_menu', 'ddc_admin_menu');

// Activation Hook
function ddc_activate() {
    // Add default options
    add_option('ddc_disable_comments', false);
}
register_activation_hook(__FILE__, 'ddc_activate');

// Deactivation Hook
function ddc_deactivate() {
    // Cleanup if needed
    delete_option('ddc_disable_comments');
}
register_deactivation_hook(__FILE__, 'ddc_deactivate');

// Initialize the plugin
function ddc_init() {
    // If comments are disabled, remove support for comments and trackbacks
    if (get_option('ddc_disable_comments', false)) {
        // Get post types with comments enabled and cache the result
        $cache_key = 'ddc_post_types_with_comments';
        $post_types = wp_cache_get($cache_key);
        
        if (false === $post_types) {
            $post_types = get_post_types(array('public' => true), 'names');
            wp_cache_set($cache_key, $post_types, 'delete-disable-comments', HOUR_IN_SECONDS);
        }
        
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }

        // Close comments on all posts with caching
        global $wpdb;
        $cache_key = 'ddc_comments_closed';
        $comments_closed = wp_cache_get($cache_key);
        
        if (false === $comments_closed) {
            // Get all posts
            $posts = get_posts(array(
                'post_type' => 'any',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'fields' => 'ids'
            ));
            
            // Update each post individually
            foreach ($posts as $post_id) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
            }
            
            wp_cache_set($cache_key, true, 'delete-disable-comments', HOUR_IN_SECONDS);
        }

        // Ensure new posts have comments disabled by default
        update_option('default_comment_status', 'closed');
        update_option('default_ping_status', 'closed');

        // Filter to ensure comments are closed
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        
        // Remove comment-related menu items
        add_action('admin_menu', function() {
            remove_menu_page('edit-comments.php');
        });
        
        // Hide comment counts in admin bar
        add_action('wp_before_admin_bar_render', function() {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('comments');
        });

        // Additional filters to hide comments UI
        add_filter('comments_template', 'ddc_use_blank_template', 20);
        
        // Remove comments from post type supports
        add_action('template_redirect', 'ddc_block_comment_feed');

        // Remove comments from admin bar
        add_action('admin_init', function() {
            if (is_admin_bar_showing()) {
                remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
            }
        });

        // Remove comments CSS from theme
        add_action('wp_enqueue_scripts', 'ddc_dequeue_comment_styles');

        // Hide existing comments
        add_filter('comments_array', '__return_empty_array', 20);
        
        // Disable comments REST API endpoint
        add_filter('rest_endpoints', 'ddc_disable_rest_endpoints_filter');

        // Remove comment-related blocks
        add_filter('allowed_block_types_all', 'ddc_remove_comment_blocks');

        // Remove comment links from post meta
        add_filter('post_comments_feed_link', '__return_false');
        add_filter('comments_link_feed', '__return_false');
        add_filter('comment_link', '__return_false');
        add_filter('get_comments_link', '__return_false');
        add_filter('get_comments_number', '__return_zero');

        // Remove comment-related widgets
        add_action('widgets_init', 'ddc_unregister_comment_widgets');

        // Remove comment support from post types
        add_action('init', 'ddc_remove_post_type_comment_support', 100);

        // Remove comment form
        add_filter('comments_template_query_args', '__return_empty_array');
        add_filter('comments_open', '__return_false');
        add_filter('comments_array', '__return_empty_array');

        // Remove comment patterns from theme
        add_filter('theme_file_path', 'ddc_filter_theme_comment_patterns', 10, 2);
    }
}
add_action('init', 'ddc_init', 100);

// Callback Functions (previously anonymous or defined elsewhere)

function ddc_use_blank_template($template) {
     if (get_option('ddc_disable_comments', '0') === '1') {
        return DDC_PLUGIN_DIR . 'templates/blank.php';
    }
    return $template;
}

function ddc_block_comment_feed() {
    if (get_option('ddc_disable_comments', '0') === '1') {
        if (is_comment_feed()) {
            wp_die(esc_html__('Comments are closed.', 'delete-disable-comments'), '', array('response' => 403));
        }
    }
}

function ddc_dequeue_comment_styles() {
    if (get_option('ddc_disable_comments', '0') === '1') {
        wp_dequeue_style('comments-template');
        wp_dequeue_style('twentytwentyfive-comments');
        wp_dequeue_style('wp-block-comments');
        wp_dequeue_style('wp-block-comments-form');
    }
}

function ddc_disable_rest_endpoints_filter($endpoints) {
    if (get_option('ddc_disable_comments', '0') === '1') {
        if (isset($endpoints['/wp/v2/comments'])) {
            unset($endpoints['/wp/v2/comments']);
        }
        if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
        }
    }
    return $endpoints;
}

function ddc_remove_comment_blocks($allowed_blocks) {
    if (get_option('ddc_disable_comments', '0') === '1') {
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
            'core/post-comments-form'
        ));
    }
    return $allowed_blocks;
}

function ddc_unregister_comment_widgets() {
    if (get_option('ddc_disable_comments', '0') === '1') {
        unregister_widget('WP_Widget_Recent_Comments');
    }
}

function ddc_remove_post_type_comment_support() {
    if (get_option('ddc_disable_comments', '0') === '1') {
        $post_types = get_post_types(array('public' => true), 'names');
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }
}

function ddc_filter_theme_comment_patterns($path, $file) {
    if (get_option('ddc_disable_comments', '0') === '1') {
        if ($file === 'patterns/comments.php') {
            return DDC_PLUGIN_DIR . 'templates/blank.php';
        }
    }
    return $path;
}

// --- Hooks from includes/functions.php (already prefixed and registered in ddc_register_ajax_handlers) ---
// add_action('wp_ajax_ddc_delete_spam', 'ddc_delete_spam_comments');
// add_action('wp_ajax_ddc_delete_all', 'ddc_delete_all_comments');
// add_action('wp_ajax_ddc_backup_comments', 'ddc_backup_comments'); 
// add_action('wp_ajax_ddc_toggle_comments', 'ddc_toggle_comments'); 
// add_action('wp_ajax_ddc_get_status', 'ddc_get_status');

// --- Other Hooks (using prefixed functions where needed) ---
// add_action('plugins_loaded', 'ddc_load_textdomain'); // Already done
// add_action('admin_enqueue_scripts', 'ddc_admin_enqueue_scripts'); // Already done
// add_action('admin_menu', 'ddc_admin_menu'); // Already done
// register_activation_hook(__FILE__, 'ddc_activate'); // Already done
// register_deactivation_hook(__FILE__, 'ddc_deactivate'); // Already done
// add_action('init', 'ddc_init', 100); // Already done
add_filter('comments_template', 'ddc_use_blank_template', 20); 
add_action('template_redirect', 'ddc_block_comment_feed');
// add_action('wp_before_admin_bar_render', 'ddc_admin_bar_render'); // Covered by closure in ddc_init
add_action('wp_enqueue_scripts', 'ddc_dequeue_comment_styles', 100); 
// add_filter('comments_array', 'ddc_hide_existing_comments', 20, 2); // Using __return_empty_array in ddc_init
add_filter('rest_endpoints', 'ddc_disable_rest_endpoints_filter'); 
add_action('widgets_init', 'ddc_unregister_comment_widgets');


/* // Remove the include from the end of the file
// Include the admin page function if it exists as a separate file
if (file_exists(DDC_PLUGIN_DIR . 'admin/admin-page.php')) {
    require_once DDC_PLUGIN_DIR . 'admin/admin-page.php';
}
*/