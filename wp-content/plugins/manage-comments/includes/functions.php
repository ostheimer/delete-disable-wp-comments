<?php
/**
 * Backend functions for the Manage Comments plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Delete all spam comments from the database
 */
function manage_comments_delete_spam() {
    // Check nonce for security
    if (!check_ajax_referer('manage_comments_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;

    // Get the number of spam comments before deletion
    $spam_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = %s",
            'spam'
        )
    );

    // Delete all spam comments
    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->comments WHERE comment_approved = %s",
            'spam'
        )
    );

    if ($deleted === false) {
        wp_send_json_error('Database error occurred');
        return;
    }

    wp_send_json_success(array(
        'message' => sprintf(
            /* translators: %d: number of deleted spam comments */
            _n(
                '%d spam comment has been deleted.',
                '%d spam comments have been deleted.',
                $spam_count,
                'manage-comments'
            ),
            $spam_count
        )
    ));
}
add_action('wp_ajax_delete_spam_comments', 'manage_comments_delete_spam');

/**
 * Generate and download a backup of all comments
 */
function manage_comments_backup() {
    // Check nonce
    if (!check_ajax_referer('manage_comments_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;

    // Get all comments
    $comments = $wpdb->get_results(
        "SELECT * FROM $wpdb->comments ORDER BY comment_ID ASC",
        ARRAY_A
    );

    if (empty($comments)) {
        wp_send_json_error('No comments to backup');
        return;
    }

    // Create a temporary file
    $filename = 'comments-backup-' . date('Y-m-d') . '.csv';
    $upload_dir = wp_upload_dir();
    $temp_file = $upload_dir['path'] . '/' . $filename;

    // Open file for writing
    $output = fopen($temp_file, 'w');
    if ($output === false) {
        wp_send_json_error('Could not create backup file');
        return;
    }

    // Add CSV headers
    fputcsv($output, array_keys($comments[0]));

    // Add comment data
    foreach ($comments as $comment) {
        fputcsv($output, $comment);
    }

    fclose($output);

    // Read the file content
    $content = file_get_contents($temp_file);
    if ($content === false) {
        wp_send_json_error('Could not read backup file');
        unlink($temp_file);
        return;
    }

    // Delete the temporary file
    unlink($temp_file);

    // Send the file content as base64
    wp_send_json_success(array(
        'content' => base64_encode($content),
        'filename' => $filename
    ));
}
add_action('wp_ajax_backup_comments', 'manage_comments_backup');

/**
 * Delete all comments from the database
 */
function manage_comments_delete_all() {
    // Check nonce for security
    if (!check_ajax_referer('manage_comments_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;

    // Get the number of comments before deletion
    $comment_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments");

    // Delete all comments
    $deleted = $wpdb->query("TRUNCATE TABLE $wpdb->comments");
    $deleted_meta = $wpdb->query("TRUNCATE TABLE $wpdb->commentmeta");

    if ($deleted === false || $deleted_meta === false) {
        wp_send_json_error('Database error occurred');
        return;
    }

    wp_send_json_success(array(
        'message' => sprintf(
            /* translators: %d: number of deleted comments */
            _n(
                '%d comment has been deleted.',
                '%d comments have been deleted.',
                $comment_count,
                'manage-comments'
            ),
            $comment_count
        )
    ));
}
add_action('wp_ajax_delete_all_comments', 'manage_comments_delete_all');

/**
 * Toggle comments on/off site-wide
 */
function manage_comments_toggle() {
    // Check nonce for security
    if (!check_ajax_referer('manage_comments_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Get the disabled state from the AJAX request
    $disabled = isset($_POST['disabled']) ? (bool) $_POST['disabled'] : false;

    // Update the option
    update_option('disable_comments', $disabled);

    global $wpdb;

    if ($disabled) {
        // Disable comments for all post types
        $post_types = get_post_types(array('public' => true), 'names');
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }

        // Close comments on all existing posts
        $wpdb->query("UPDATE $wpdb->posts SET comment_status = 'closed', ping_status = 'closed'");
        
        // Update comment_status option
        update_option('default_comment_status', 'closed');
        update_option('default_ping_status', 'closed');
    } else {
        // Enable comments for all post types
        $post_types = get_post_types(array('public' => true), 'names');
        foreach ($post_types as $post_type) {
            add_post_type_support($post_type, 'comments');
            add_post_type_support($post_type, 'trackbacks');
        }

        // Open comments on all existing posts
        $wpdb->query("UPDATE $wpdb->posts SET comment_status = 'open', ping_status = 'open'");
        
        // Update comment_status option
        update_option('default_comment_status', 'open');
        update_option('default_ping_status', 'open');
    }

    wp_send_json_success(array(
        'message' => $disabled
            ? __('Comments have been disabled site-wide.', 'manage-comments')
            : __('Comments have been enabled site-wide.', 'manage-comments')
    ));
}
add_action('wp_ajax_toggle_comments', 'manage_comments_toggle');

/**
 * Get the current status of comments
 */
function manage_comments_get_status() {
    // Check nonce for security
    if (!check_ajax_referer('manage_comments_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Get the current status
    $disabled = get_option('disable_comments', '0');

    wp_send_json_success(array(
        'disabled' => $disabled
    ));
}
add_action('wp_ajax_get_comments_status', 'manage_comments_get_status'); 