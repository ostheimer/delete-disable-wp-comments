<?php
/**
 * Plugin Name: Delete & Disable Comments
 * Plugin URI: https://github.com/ostheimer/delete-disable-wp-comments
 * Description: A WordPress plugin that helps site administrators manage comments by deleting spam comments, removing all comments with backup, or disabling comments site-wide.
 * Version: 1.0.1
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
define('DDWPC_VERSION', '1.0.1');
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
    // Add default options
    add_option('ddwpc_disable_comments', false);
}
register_activation_hook(__FILE__, 'ddwpc_activate');

// Deactivation Hook
function ddwpc_deactivate() {
    // Cleanup if needed
    delete_option('ddwpc_disable_comments');
}
register_deactivation_hook(__FILE__, 'ddwpc_deactivate');

// Initialize the plugin
function ddwpc_init() {
    // If comments are disabled, remove support for comments and trackbacks
    if (get_option('ddwpc_disable_comments', false)) {
        // Get post types with comments enabled and cache the result
        $cache_key = 'ddwpc_post_types_with_comments';
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
        $cache_key = 'ddwpc_comments_closed';
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
        add_filter('comments_template', 'ddwpc_use_blank_template', 20);
        
        // Remove comments from post type supports
        add_action('template_redirect', 'ddwpc_block_comment_feed');

        // Remove comments from admin bar
        add_action('admin_init', function() {
            if (is_admin_bar_showing()) {
                remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
            }
        });

        // Remove comments CSS from theme
        add_action('wp_enqueue_scripts', 'ddwpc_dequeue_comment_styles');

        // Hide existing comments
        add_filter('comments_array', '__return_empty_array', 20);
        
        // Disable comments REST API endpoint
        add_filter('rest_endpoints', 'ddwpc_disable_rest_endpoints_filter');

        // Remove comment-related blocks
        add_filter('allowed_block_types_all', 'ddwpc_remove_comment_blocks');

        // Remove comment links from post meta
        add_filter('post_comments_feed_link', '__return_false');
        add_filter('comments_link_feed', '__return_false');
        add_filter('comment_link', '__return_false');
        add_filter('get_comments_link', '__return_false');
        add_filter('get_comments_number', '__return_zero');

        // Remove comment-related widgets
        add_action('widgets_init', 'ddwpc_unregister_comment_widgets');

        // Remove comment support from post types
        add_action('init', 'ddwpc_remove_post_type_comment_support', 100);

        // Remove comment form
        add_filter('comments_template_query_args', '__return_empty_array');
        add_filter('comments_open', '__return_false');
        add_filter('comments_array', '__return_empty_array');

        // Remove comment patterns from theme
        add_filter('theme_file_path', 'ddwpc_filter_theme_comment_patterns', 10, 2);
    }
}
add_action('init', 'ddwpc_init', 100);

// Callback Functions (previously anonymous or defined elsewhere)

function ddwpc_use_blank_template($template) {
     if (get_option('ddwpc_disable_comments', '0') === '1') {
        return DDWPC_PLUGIN_DIR . 'templates/blank.php';
    }
    return $template;
}

function ddwpc_block_comment_feed() {
    if (get_option('ddwpc_disable_comments', '0') === '1') {
        if (is_comment_feed()) {
            wp_die(esc_html__('Comments are closed.', 'delete-disable-comments'), '', array('response' => 403));
        }
    }
}

function ddwpc_dequeue_comment_styles() {
    if (get_option('ddwpc_disable_comments', '0') === '1') {
        wp_dequeue_style('comments-template');
        wp_dequeue_style('twentytwentyfive-comments');
        wp_dequeue_style('wp-block-comments');
        wp_dequeue_style('wp-block-comments-form');
    }
}

function ddwpc_disable_rest_endpoints_filter($endpoints) {
    if (get_option('ddwpc_disable_comments', '0') === '1') {
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
    if (get_option('ddwpc_disable_comments', '0') === '1') {
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

function ddwpc_unregister_comment_widgets() {
    if (get_option('ddwpc_disable_comments', '0') === '1') {
        unregister_widget('WP_Widget_Recent_Comments');
    }
}

function ddwpc_remove_post_type_comment_support() {
    if (get_option('ddwpc_disable_comments', '0') === '1') {
        $post_types = get_post_types(array('public' => true), 'names');
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }
}

function ddwpc_filter_theme_comment_patterns($path, $file) {
    if (get_option('ddwpc_disable_comments', '0') === '1') {
        if ($file === 'patterns/comments.php') {
            return DDWPC_PLUGIN_DIR . 'templates/blank.php';
        }
    }
    return $path;
}

// --- Hooks from includes/functions.php (already prefixed and registered in ddwpc_register_ajax_handlers) ---
// add_action('wp_ajax_ddwpc_delete_spam', 'ddwpc_delete_spam_comments');
// add_action('wp_ajax_ddwpc_delete_all', 'ddwpc_delete_all_comments');
// add_action('wp_ajax_ddwpc_backup_comments', 'ddwpc_backup_comments'); 
// add_action('wp_ajax_ddwpc_toggle_comments', 'ddwpc_toggle_comments'); 
// add_action('wp_ajax_ddwpc_get_status', 'ddwpc_get_status');

// --- Other Hooks (using prefixed functions where needed) ---
// add_action('plugins_loaded', 'ddwpc_load_textdomain'); // Already done
// add_action('admin_enqueue_scripts', 'ddwpc_admin_enqueue_scripts'); // Already done
// add_action('admin_menu', 'ddwpc_admin_menu'); // Already done
// register_activation_hook(__FILE__, 'ddwpc_activate'); // Already done
// register_deactivation_hook(__FILE__, 'ddwpc_deactivate'); // Already done
// add_action('init', 'ddwpc_init', 100); // Already done
add_filter('comments_template', 'ddwpc_use_blank_template', 20); 
add_action('template_redirect', 'ddwpc_block_comment_feed');
// add_action('wp_before_admin_bar_render', 'ddwpc_admin_bar_render'); // Covered by closure in ddwpc_init
add_action('wp_enqueue_scripts', 'ddwpc_dequeue_comment_styles', 100); 
// add_filter('comments_array', 'ddwpc_hide_existing_comments', 20, 2); // Using __return_empty_array in ddwpc_init
add_filter('rest_endpoints', 'ddwpc_disable_rest_endpoints_filter'); 
add_action('widgets_init', 'ddwpc_unregister_comment_widgets');


/* // Remove the include from the end of the file
// Include the admin page function if it exists as a separate file
if (file_exists(DDWPC_PLUGIN_DIR . 'admin/admin-page.php')) {
    require_once DDWPC_PLUGIN_DIR . 'admin/admin-page.php';
}
*/