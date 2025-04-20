<?php
/**
 * Backend functions for the Delete & Disable Comments plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define time constants if not already defined (WordPress usually defines these)
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 60 * 60 );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
}

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Delete all spam comments from the database
 */
function ddwpc_delete_spam_comments() {
    // Verify nonce
    if (!check_ajax_referer('ddwpc_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        ));
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        ));
    }

    // Get spam comments count from cache
    $cache_key = 'ddwpc_spam_comments_count';
    $spam_count = wp_cache_get($cache_key, 'delete-disable-comments');
    if (false === $spam_count) {
        $args = array(
            'status' => 'spam',
            'count' => true
        );
        $spam_count = get_comments($args);
        wp_cache_set($cache_key, $spam_count, 'delete-disable-comments', HOUR_IN_SECONDS);
    }

    if ($spam_count > 0) {
        // Get all spam comments
        $spam_comments = get_comments(array(
            'status' => 'spam',
            'fields' => 'ids'
        ));
        
        // Delete spam comments
        foreach ($spam_comments as $comment_id) {
            wp_delete_comment($comment_id, true);
        }
        
        // Clear cache
        wp_cache_delete($cache_key, 'delete-disable-comments');
        
        wp_send_json_success(array(
            'message' => sprintf(
                /* translators: %d: number of deleted comments */
                esc_html__('Successfully deleted %d spam comments.', 'delete-disable-comments'),
                $spam_count
            )
        ));
    }
    
    wp_send_json_success(array(
        'message' => esc_html__('No spam comments found.', 'delete-disable-comments')
    ));
}

/**
 * Delete all comments from the database
 */
function ddwpc_delete_all_comments() {
    // Verify nonce
    if (!check_ajax_referer('ddwpc_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        ));
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        ));
    }

    // Get total comments count from cache
    $cache_key = 'ddwpc_total_comments_count';
    $total_count = wp_cache_get($cache_key, 'delete-disable-comments');
    if (false === $total_count) {
        $args = array(
            'count' => true
        );
        $total_count = get_comments($args);
        wp_cache_set($cache_key, $total_count, 'delete-disable-comments', HOUR_IN_SECONDS);
    }

    if ($total_count > 0) {
        // Get all comments
        $comments = get_comments(array(
            'fields' => 'ids',
            'number' => 0 // Get all comments
        ));
        
        // Delete all comments and their meta
        foreach ($comments as $comment_id) {
            wp_delete_comment($comment_id, true);
        }
        
        // Clear cache
        wp_cache_delete($cache_key, 'delete-disable-comments');
        
        wp_send_json_success(array(
            'message' => esc_html__('Successfully deleted all comments.', 'delete-disable-comments')
        ));
    }
    
    wp_send_json_success(array(
        'message' => esc_html__('No comments found.', 'delete-disable-comments')
    ));
}

/**
 * Create and download a backup of all comments
 */
function ddwpc_backup_comments() {
    // Verify nonce
    if (!check_ajax_referer('ddwpc_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        ));
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        ));
    }

    // Get comments from database with caching
    $cache_key = 'ddwpc_all_comments_backup';
    $comments = wp_cache_get($cache_key, 'delete-disable-comments');
    if (false === $comments) {
        $comments = get_comments(array(
            'status' => 'all',
            'type' => 'comment',
            'number' => 0, // Get all comments
            'orderby' => 'comment_ID',
            'order' => 'ASC'
        ));
        
        // Convert comments to array format
        $comments_array = array();
        foreach ($comments as $comment) {
            $comments_array[] = array(
                'comment_ID' => $comment->comment_ID,
                'comment_post_ID' => $comment->comment_post_ID,
                'comment_author' => $comment->comment_author,
                'comment_author_email' => $comment->comment_author_email,
                'comment_author_url' => $comment->comment_author_url,
                'comment_author_IP' => $comment->comment_author_IP,
                'comment_date' => $comment->comment_date,
                'comment_date_gmt' => $comment->comment_date_gmt,
                'comment_content' => $comment->comment_content,
                'comment_karma' => $comment->comment_karma,
                'comment_approved' => $comment->comment_approved,
                'comment_agent' => $comment->comment_agent,
                'comment_type' => $comment->comment_type,
                'comment_parent' => $comment->comment_parent,
                'user_id' => $comment->user_id
            );
        }
        
        wp_cache_set($cache_key, $comments_array, 'delete-disable-comments', HOUR_IN_SECONDS);
        $comments = $comments_array;
    }
    
    if (empty($comments)) {
        wp_send_json_error(array(
            'message' => esc_html__('No comments found to backup.', 'delete-disable-comments')
        ));
    }
    
    // Prepare backup directory under wp-content/uploads/delete-disable-comments
    $backup_dir = WP_CONTENT_DIR . '/uploads/delete-disable-comments';
    if ( ! file_exists( $backup_dir ) ) {
        wp_mkdir_p( $backup_dir );
    }
    $filename = 'ddwpc-comments-backup-' . gmdate('Y-m-d-H-i-s') . '.csv';
    $backup_file = rtrim( $backup_dir, '/' ) . '/' . $filename;
    
    // Initialize WP_Filesystem
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        if (!function_exists('WP_Filesystem')) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
        }
        // Attempt to initialize WP_Filesystem
        if (!WP_Filesystem()) {
            // Send JSON error if filesystem init fails
             wp_send_json_error(array(
                 'success' => false,
                 'message' => esc_html__('Could not initialize WP_Filesystem. Check file permissions or provide FTP credentials if required.', 'delete-disable-comments')
             ));
             // No need to return here, wp_send_json_error includes die()
        }
    }
    
    // Prepare CSV content
    $csv_content = '';
    $headers = array_keys($comments[0]);
    $csv_content .= implode(',', $headers) . "\n";
    
    foreach ($comments as $comment) {
        $csv_content .= implode(',', array_map('wp_json_encode', $comment)) . "\n";
    }
    
    // Write file using WP_Filesystem
    if (!$wp_filesystem->put_contents($backup_file, $csv_content)) {
        wp_send_json_error(array(
            'message' => esc_html__('Failed to create backup file.', 'delete-disable-comments')
        ));
    }
    
    // Clean up old backup files
    $backup_files = glob( rtrim( $backup_dir, '/' ) . '/ddwpc-comments-backup-*.csv' );
    if ($backup_files) {
        foreach ($backup_files as $file) {
            if ($file !== $backup_file && (time() - filemtime($file)) > DAY_IN_SECONDS) {
                wp_delete_file($file);
            }
        }
    }
    
    // Return download URL
    wp_send_json_success(array(
        'message' => esc_html__('Backup created successfully.', 'delete-disable-comments'),
        'file_url' => WP_CONTENT_URL . '/uploads/delete-disable-comments/' . $filename
    ));
}

/**
 * Toggle comments status
 */
function ddwpc_toggle_comments() {
    // Verify nonce
    if (!check_ajax_referer('ddwpc_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        ));
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        ));
        return;
    }

    // Properly sanitize and validate the input
    if (!isset($_POST['disabled']) || !is_string($_POST['disabled'])) {
        wp_send_json_error(array(
            'message' => esc_html__('Invalid input format.', 'delete-disable-comments')
        ));
        return;
    }
    
    $disabled_input = sanitize_text_field(wp_unslash($_POST['disabled']));
    $disabled = filter_var($disabled_input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    
    if ($disabled === null) {
        wp_send_json_error(array(
            'message' => esc_html__('Invalid boolean value.', 'delete-disable-comments')
        ));
        return;
    }
    
    // Update option with strict boolean to string conversion
    $update_result = update_option('ddwpc_disable_comments', $disabled ? '1' : '0');
    
    if ($update_result === false) {
        // Check if the value is already the same
        $current_value = get_option('ddwpc_disable_comments');
        if ($current_value === ($disabled ? '1' : '0')) {
            // Value unchanged, not an error in this context
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to update comment settings.', 'delete-disable-comments')
            ));
            return;
        }
    }
    
    // Get post types with comments enabled
    $cache_key = 'ddwpc_post_types_with_comments';
    $post_types = wp_cache_get($cache_key);
    
    if (false === $post_types) {
        $post_types = get_post_types(array('public' => true), 'names');
        wp_cache_set($cache_key, $post_types, 'delete-disable-comments', HOUR_IN_SECONDS);
    }
    
    global $wpdb;
    
    // Close comments on all posts if disabled
    if ($disabled) {
        $cache_key_closed = 'ddwpc_comments_closed';
        $comments_closed = wp_cache_get($cache_key_closed);
        
        if (false === $comments_closed) {
            // Get all posts
            $posts = get_posts(array(
                'post_type' => $post_types,
                'posts_per_page' => -1,
                'post_status' => 'any',
                'fields' => 'ids'
            ));
            
            // Update each post
            foreach ($posts as $post_id) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
            }
            
            wp_cache_set($cache_key_closed, true, 'delete-disable-comments', HOUR_IN_SECONDS);
        }
        
        // Update default settings
        update_option('default_comment_status', 'closed');
        update_option('default_ping_status', 'closed');
    }
    
    $message = $disabled ? 
        esc_html__('Comments have been disabled site-wide.', 'delete-disable-comments') :
        esc_html__('Comments have been enabled site-wide.', 'delete-disable-comments');
    
    wp_send_json_success(array(
        'message' => $message,
        'status' => $disabled ? 'disabled' : 'enabled'
    ));
}

/**
 * Get the current status of comments
 */
function ddwpc_get_status() {
    // Check nonce for security
    if (!check_ajax_referer('ddwpc_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        ));
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        ));
        return;
    }

    // Get the current status (force string value)
    $disabled = get_option('ddwpc_disable_comments', "0");

    wp_send_json_success(array(
        'disabled' => $disabled,
        'message' => $disabled === "1"
            ? esc_html__('Comments are currently disabled', 'delete-disable-comments')
            : esc_html__('Comments are currently enabled', 'delete-disable-comments'),
        'status' => $disabled === "1" ? 'disabled' : 'enabled'
    ));
}

/**
 * Register AJAX handlers for the plugin.
 */
function ddwpc_register_ajax_handlers() {
    add_action('wp_ajax_ddwpc_delete_spam', 'ddwpc_delete_spam_comments');
    add_action('wp_ajax_ddwpc_delete_all', 'ddwpc_delete_all_comments');
    add_action('wp_ajax_ddwpc_backup_comments', 'ddwpc_backup_comments');
    add_action('wp_ajax_ddwpc_toggle_comments', 'ddwpc_toggle_comments');
    add_action('wp_ajax_ddwpc_get_status', 'ddwpc_get_status');
}
add_action('init', 'ddwpc_register_ajax_handlers'); // Register handlers on init 