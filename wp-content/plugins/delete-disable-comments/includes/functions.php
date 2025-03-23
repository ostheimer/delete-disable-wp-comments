<?php
/**
 * Backend functions for the Delete & Disable Comments plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}

// Include WordPress files
require_once(ABSPATH . 'wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
require_once(ABSPATH . 'wp-includes/pluggable.php');

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Delete all spam comments from the database
 */
function delete_spam_comments() {
    // Verify nonce
    if (!check_ajax_referer('delete_disable_comments_nonce', 'nonce', false)) {
        return array(
            'success' => false,
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        );
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return array(
            'success' => false,
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        );
    }

    global $wpdb;
    
    // Get spam comments count from cache
    $spam_count = wp_cache_get('spam_comments_count', 'delete-disable-comments');
    if (false === $spam_count) {
        $spam_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = %s",
                'spam'
            )
        );
        wp_cache_set('spam_comments_count', $spam_count, 'delete-disable-comments', HOUR_IN_SECONDS);
    }

    if ($spam_count > 0) {
        // Delete spam comments
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->comments WHERE comment_approved = %s",
                'spam'
            )
        );
        
        // Clear cache
        wp_cache_delete('spam_comments_count', 'delete-disable-comments');
        
        return array(
            'success' => true,
            'message' => sprintf(
                /* translators: %d: number of deleted comments */
                esc_html__('Successfully deleted %d spam comments.', 'delete-disable-comments'),
                $deleted
            )
        );
    }
    
    return array(
        'success' => true,
        'message' => esc_html__('No spam comments found.', 'delete-disable-comments')
    );
}

/**
 * Delete all comments from the database
 */
function delete_all_comments() {
    // Verify nonce
    if (!check_ajax_referer('delete_disable_comments_nonce', 'nonce', false)) {
        return array(
            'success' => false,
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        );
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return array(
            'success' => false,
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        );
    }

    global $wpdb;
    
    // Get total comments count from cache
    $total_count = wp_cache_get('total_comments_count', 'delete-disable-comments');
    if (false === $total_count) {
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments");
        wp_cache_set('total_comments_count', $total_count, 'delete-disable-comments', HOUR_IN_SECONDS);
    }

    if ($total_count > 0) {
        // Delete all comments
        $deleted = $wpdb->query("TRUNCATE TABLE $wpdb->comments");
        $wpdb->query("TRUNCATE TABLE $wpdb->commentmeta");
        
        // Clear cache
        wp_cache_delete('total_comments_count', 'delete-disable-comments');
        
        return array(
            'success' => true,
            'message' => esc_html__('Successfully deleted all comments.', 'delete-disable-comments')
        );
    }
    
    return array(
        'success' => true,
        'message' => esc_html__('No comments found.', 'delete-disable-comments')
    );
}

/**
 * Create and download a backup of all comments
 */
function backup_comments() {
    // Verify nonce
    if (!check_ajax_referer('delete_disable_comments_nonce', 'nonce', false)) {
        return array(
            'success' => false,
            'message' => esc_html__('Security check failed.', 'delete-disable-comments')
        );
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return array(
            'success' => false,
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')
        );
    }

    global $wpdb;
    
    // Get comments from database with caching
    $comments = wp_cache_get('all_comments_backup', 'delete-disable-comments');
    if (false === $comments) {
        $comments = $wpdb->get_results("SELECT * FROM $wpdb->comments", ARRAY_A);
        wp_cache_set('all_comments_backup', $comments, 'delete-disable-comments', HOUR_IN_SECONDS);
    }
    
    if (empty($comments)) {
        return array(
            'success' => false,
            'message' => esc_html__('No comments found to backup.', 'delete-disable-comments')
        );
    }
    
    // Create backup filename with gmdate
    $filename = 'comments-backup-' . gmdate('Y-m-d-H-i-s') . '.csv';
    $upload_dir = wp_upload_dir();
    $backup_file = trailingslashit($upload_dir['path']) . $filename;
    
    // Use WP_Filesystem
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
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
        return array(
            'success' => false,
            'message' => esc_html__('Failed to create backup file.', 'delete-disable-comments')
        );
    }
    
    // Clean up old backup files
    $backup_files = glob(trailingslashit($upload_dir['path']) . 'comments-backup-*.csv');
    if ($backup_files) {
        foreach ($backup_files as $file) {
            if ($file !== $backup_file && (time() - filemtime($file)) > DAY_IN_SECONDS) {
                wp_delete_file($file);
            }
        }
    }
    
    // Return download URL
    return array(
        'success' => true,
        'message' => esc_html__('Backup created successfully.', 'delete-disable-comments'),
        'file_url' => trailingslashit($upload_dir['url']) . $filename
    );
}

/**
 * Toggle comments status
 */
function toggle_comments() {
    // Verify nonce
    if (!check_ajax_referer('delete_disable_comments_nonce', 'nonce', false)) {
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

    if (!isset($_POST['disabled'])) {
        wp_send_json_error(array(
            'message' => esc_html__('Invalid request.', 'delete-disable-comments')
        ));
        return;
    }
    
    // Properly unslash and sanitize the input
    $disabled = wp_unslash($_POST['disabled']);
    $disabled = sanitize_text_field($disabled);
    $disabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
    
    // Update option with strict boolean to string conversion
    $update_result = update_option('disable_comments', $disabled ? '1' : '0');
    
    if ($update_result === false) {
        wp_send_json_error(array(
            'message' => esc_html__('Failed to update comment settings.', 'delete-disable-comments')
        ));
        return;
    }
    
    // Get post types with comments enabled
    $cache_key = 'ddc_post_types_with_comments';
    $post_types = wp_cache_get($cache_key);
    
    if (false === $post_types) {
        $post_types = get_post_types(array('public' => true), 'names');
        wp_cache_set($cache_key, $post_types, 'delete-disable-comments', HOUR_IN_SECONDS);
    }
    
    global $wpdb;
    
    // Close comments on all posts if disabled
    if ($disabled) {
        $cache_key = 'ddc_comments_closed';
        $comments_closed = wp_cache_get($cache_key);
        
        if (false === $comments_closed) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $wpdb->posts SET comment_status = %s, ping_status = %s",
                    'closed',
                    'closed'
                )
            );
            wp_cache_set($cache_key, true, 'delete-disable-comments', HOUR_IN_SECONDS);
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

// Register AJAX handlers
add_action('wp_ajax_toggle_comments', 'toggle_comments');

/**
 * Get the current status of comments
 */
function delete_disable_comments_get_status() {
    // Check nonce for security
    if (!check_ajax_referer('delete_disable_comments_nonce', 'nonce', false)) {
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
    $disabled = get_option('disable_comments', "0");

    wp_send_json_success(array(
        'disabled' => $disabled,
        'message' => $disabled === "1"
            ? esc_html__('Comments are currently disabled', 'delete-disable-comments')
            : esc_html__('Comments are currently enabled', 'delete-disable-comments'),
        'status' => $disabled === "1" ? 'disabled' : 'enabled'
    ));
}
add_action('wp_ajax_get_comments_status', 'delete_disable_comments_get_status'); 