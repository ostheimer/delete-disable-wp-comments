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

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Make sure we have access to WordPress functions
if (!defined('ABSPATH')) {
    /** Set up WordPress environment */
    require_once(dirname(dirname(dirname(__DIR__))) . '/wp-load.php');
}

// Required WordPress functions
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-includes/pluggable.php');
require_once(ABSPATH . 'wp-includes/l10n.php');
require_once(ABSPATH . 'wp-includes/locale.php');

// Define plugin constants
define('DELETE_DISABLE_COMMENTS_VERSION', '1.0.0');
define('DELETE_DISABLE_COMMENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DELETE_DISABLE_COMMENTS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load translations early
function delete_disable_comments_load_textdomain() {
    $domain = 'delete-disable-comments';
    $locale = determine_locale();
    
    // Try plugin languages directory first
    $plugin_mofile = dirname(__FILE__) . '/languages/' . $domain . '-' . $locale . '.mo';
    if (file_exists($plugin_mofile)) {
        $loaded = load_textdomain($domain, $plugin_mofile);
        if ($loaded) {
            return;
        }
    }
    
    // Try WordPress languages directory as fallback
    $wp_mofile = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo';
    if (file_exists($wp_mofile)) {
        load_textdomain($domain, $wp_mofile);
    }
    
    // If specific locale failed, try the generic de_DE as fallback for all German variants
    if (strpos($locale, 'de_') === 0) {
        $fallback_mofile = dirname(__FILE__) . '/languages/' . $domain . '-de_DE.mo';
        if (file_exists($fallback_mofile)) {
            load_textdomain($domain, $fallback_mofile);
        }
    }
}

// Make sure translations are loaded before anything else
remove_action('plugins_loaded', 'delete_disable_comments_load_textdomain');
add_action('plugins_loaded', 'delete_disable_comments_load_textdomain', 0);

// Include backend functions
require_once DELETE_DISABLE_COMMENTS_PLUGIN_DIR . 'includes/functions.php';
require_once DELETE_DISABLE_COMMENTS_PLUGIN_DIR . 'includes/check-languages.php';

// Enqueue admin styles and scripts
function delete_disable_comments_admin_enqueue_scripts($hook) {
    // Only load on our plugin's admin page
    if ($hook !== 'tools_page_delete-disable-comments') {
        return;
    }

    wp_enqueue_style(
        'delete-disable-comments-admin',
        DELETE_DISABLE_COMMENTS_PLUGIN_URL . 'css/admin-style.css',
        array(),
        DELETE_DISABLE_COMMENTS_VERSION
    );

    wp_enqueue_script(
        'delete-disable-comments-admin',
        DELETE_DISABLE_COMMENTS_PLUGIN_URL . 'js/admin-script.js',
        array('jquery'),
        DELETE_DISABLE_COMMENTS_VERSION,
        true
    );

    wp_localize_script(
        'delete-disable-comments-admin',
        'deleteDisableCommentsAjax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('delete_disable_comments_nonce'),
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
add_action('admin_enqueue_scripts', 'delete_disable_comments_admin_enqueue_scripts');

// Add admin menu
function delete_disable_comments_admin_menu() {
    $menu_title = esc_html__('Delete & Disable Comments', 'delete-disable-comments');
    $page_title = esc_html__('Delete & Disable Comments', 'delete-disable-comments');
    
    add_submenu_page(
        'tools.php',           // Parent slug (Werkzeuge)
        $page_title,          // Page title
        $menu_title,          // Menu title
        'manage_options',     // Capability required
        'delete-disable-comments', // Menu slug
        'delete_disable_comments_admin_page' // Function to display the page
    );
}
add_action('admin_menu', 'delete_disable_comments_admin_menu');

// Admin page display function
function delete_disable_comments_admin_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Admin page content will go here
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="delete-disable-comments-controls">
            <!-- Delete Spam Comments -->
            <div class="card">
                <h2><?php esc_html_e('Delete Spam Comments', 'delete-disable-comments'); ?></h2>
                <p><?php esc_html_e('Remove all comments marked as spam from your database.', 'delete-disable-comments'); ?></p>
                <button id="delete-spam-comments" class="button button-primary"><?php esc_html_e('Delete Spam Comments', 'delete-disable-comments'); ?></button>
            </div>

            <!-- Delete All Comments -->
            <div class="card">
                <h2><?php esc_html_e('Delete All Comments', 'delete-disable-comments'); ?></h2>
                <p><?php esc_html_e('Remove all comments from your website. You can download a backup before deletion.', 'delete-disable-comments'); ?></p>
                <button id="backup-comments" class="button"><?php esc_html_e('Download Backup', 'delete-disable-comments'); ?></button>
                <button id="delete-all-comments" class="button button-primary"><?php esc_html_e('Delete All Comments', 'delete-disable-comments'); ?></button>
            </div>

            <!-- Disable Comments -->
            <div class="card">
                <h2><?php esc_html_e('Disable Comments', 'delete-disable-comments'); ?></h2>
                <p><?php esc_html_e('Toggle comments on or off for your entire website.', 'delete-disable-comments'); ?></p>
                <div class="toggle-container">
                    <label class="switch">
                        <input type="checkbox" id="toggle-comments" <?php echo esc_attr(checked(get_option('disable_comments', false), true, false)); ?>>
                        <span class="slider round"></span>
                    </label>
                    <div class="comment-status <?php echo esc_attr(get_option('disable_comments', false) ? 'disabled' : 'enabled'); ?>">
                        <?php echo esc_html(get_option('disable_comments', false) ? 
                            __('Comments are currently disabled', 'delete-disable-comments') : 
                            __('Comments are currently enabled', 'delete-disable-comments')); 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Activation Hook
function delete_disable_comments_activate() {
    // Add default options
    add_option('disable_comments', false);
}
register_activation_hook(__FILE__, 'delete_disable_comments_activate');

// Deactivation Hook
function delete_disable_comments_deactivate() {
    // Cleanup if needed
    delete_option('disable_comments');
}
register_deactivation_hook(__FILE__, 'delete_disable_comments_deactivate');

// Initialize the plugin
function delete_disable_comments_init() {
    // If comments are disabled, remove support for comments and trackbacks
    if (get_option('disable_comments', false)) {
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
        add_filter('comments_template', function() {
            return DELETE_DISABLE_COMMENTS_PLUGIN_DIR . 'templates/blank.php';
        }, 20);
        
        // Remove comments from post type supports
        add_action('template_redirect', function() {
            if (is_comment_feed()) {
                wp_die(esc_html__('Comments are closed.', 'delete-disable-comments'), '', array('response' => 403));
            }
        });

        // Remove comments from admin bar
        add_action('admin_init', function() {
            if (is_admin_bar_showing()) {
                remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
            }
        });

        // Remove comments CSS from theme
        add_action('wp_enqueue_scripts', function() {
            wp_dequeue_style('comments-template');
            wp_dequeue_style('twentytwentyfive-comments');
            wp_dequeue_style('wp-block-comments');
            wp_dequeue_style('wp-block-comments-form');
        });

        // Hide existing comments
        add_filter('comments_array', '__return_empty_array', 20);
        
        // Disable comments REST API endpoint
        add_filter('rest_endpoints', function($endpoints) {
            if (isset($endpoints['/wp/v2/comments'])) {
                unset($endpoints['/wp/v2/comments']);
            }
            if (isset($endpoints['/wp/v2/comments/(?P<id>[\\d]+)'])) {
                unset($endpoints['/wp/v2/comments/(?P<id>[\\d]+)']);
            }
            return $endpoints;
        });

        // Remove comment-related blocks
        add_filter('allowed_block_types_all', function($allowed_blocks) {
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
        });

        // Remove comment links from post meta
        add_filter('post_comments_feed_link', '__return_false');
        add_filter('comments_link_feed', '__return_false');
        add_filter('comment_link', '__return_false');
        add_filter('get_comments_link', '__return_false');
        add_filter('get_comments_number', '__return_zero');

        // Remove comment-related widgets
        add_action('widgets_init', function() {
            unregister_widget('WP_Widget_Recent_Comments');
        });

        // Remove comment support from post types
        add_action('init', function() {
            remove_post_type_support('post', 'comments');
            remove_post_type_support('page', 'comments');
        }, 100);

        // Remove comment form
        add_filter('comments_template_query_args', '__return_empty_array');
        add_filter('comments_open', '__return_false');
        add_filter('comments_array', '__return_empty_array');

        // Remove comment patterns from theme
        add_filter('theme_file_path', function($path, $file) {
            if ($file === 'patterns/comments.php') {
                return DELETE_DISABLE_COMMENTS_PLUGIN_DIR . 'templates/blank.php';
            }
            return $path;
        }, 10, 2);
    }
}
add_action('init', 'delete_disable_comments_init', 100);

// Override comment template when comments are disabled
function delete_disable_comments_override_template($template) {
    if (get_option('disable_comments', '0') === '1') {
        return DELETE_DISABLE_COMMENTS_PLUGIN_DIR . 'templates/blank.php';
    }
    return $template;
}
add_filter('comments_template', 'delete_disable_comments_override_template', 20);

// Disable comments feed
function delete_disable_comments_disable_feeds() {
    if (get_option('disable_comments', '0') === '1') {
        wp_die(esc_html__('Comments are disabled.', 'delete-disable-comments'));
    }
}
add_action('do_feed_rss2_comments', 'delete_disable_comments_disable_feeds', 1);
add_action('do_feed_atom_comments', 'delete_disable_comments_disable_feeds', 1);

// Remove comments from admin bar
function delete_disable_comments_admin_bar_render() {
    if (get_option('disable_comments', '0') === '1') {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }
}
add_action('wp_before_admin_bar_render', 'delete_disable_comments_admin_bar_render');

// Dequeue comment reply script
function delete_disable_comments_dequeue_scripts() {
    if (get_option('disable_comments', '0') === '1') {
        wp_dequeue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'delete_disable_comments_dequeue_scripts', 100);

// Hide existing comments
function delete_disable_comments_hide_existing_comments($comments) {
    if (get_option('disable_comments', '0') === '1') {
        return array();
    }
    return $comments;
}
add_filter('comments_array', 'delete_disable_comments_hide_existing_comments', 20, 2);

// Disable comments REST API endpoint
function delete_disable_comments_disable_rest_endpoints($endpoints) {
    if (get_option('disable_comments', '0') === '1') {
        if (isset($endpoints['/wp/v2/comments'])) {
            unset($endpoints['/wp/v2/comments']);
        }
        if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
        }
    }
    return $endpoints;
}
add_filter('rest_endpoints', 'delete_disable_comments_disable_rest_endpoints'); 