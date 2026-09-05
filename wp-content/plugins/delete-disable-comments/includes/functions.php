<?php
/**
 * Backend functions for the Delete & Disable Comments plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define time constants if not already defined (WordPress usually defines these).
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 60 * 60 );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
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

    $deleted = 0;
    $last_id = 0;
    try {
        do {
            $comments = ddwpc_get_comment_batch($last_id);
            foreach ($comments as $comment) {
                $last_id = (int) $comment->comment_ID;
                // Use the core API to remove metadata, update counts and fire hooks.
                if (wp_delete_comment($last_id, true)) {
                    $deleted++;
                }
            }
        } while (count($comments) === 500);

        // A failed deletion or concurrent insertion must not look like full success.
        $remaining = ddwpc_get_comment_batch(0, 1);
    } catch (RuntimeException $error) {
        $remaining = null;
    }

    wp_cache_delete('ddwpc_total_comments_count', 'delete-disable-comments');
    wp_cache_delete('ddwpc_spam_comments_count', 'delete-disable-comments');

    if (null === $remaining || !empty($remaining)) {
        wp_send_json_error(array(
            'message' => sprintf(
                /* translators: %d: number of comments actually deleted */
                esc_html__('Deleted %d comments, but could not verify that all comments were removed. Please try again.', 'delete-disable-comments'),
                $deleted
            ),
            'deleted' => $deleted,
        ));
        return;
    }

    wp_send_json_success(array(
        'message' => sprintf(
            /* translators: %d: number of comments actually deleted */
            esc_html__('Successfully deleted %d comments. No comments remain.', 'delete-disable-comments'),
            $deleted
        ),
        'deleted' => $deleted,
    ));
}

/**
 * Read every comment status using a bounded primary-key cursor.
 *
 * WP_Comment_Query's "all" excludes spam/trash. Reading the table directly also
 * includes custom statuses and avoids third-party query filters hiding records.
 * No offset: removing earlier rows cannot cause later rows to be skipped.
 *
 * @param int $after_id Last processed comment ID.
 * @param int $limit Maximum rows to read.
 * @return object[] Comment table rows.
 * @throws RuntimeException When the database read fails.
 */
function ddwpc_get_comment_batch($after_id, $limit = 500) {
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fresh complete table traversal is required for backup/deletion verification.
    $comments = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->comments} WHERE comment_ID > %d ORDER BY comment_ID ASC LIMIT %d",
        max(0, (int) $after_id),
        max(1, min(500, (int) $limit))
    ));
    if (null === $comments || $wpdb->last_error) {
        throw new RuntimeException('Could not read comments.');
    }
    return $comments;
}

/**
 * Build the authenticated backup download URL for administrators.
 *
 * @return string
 */
function ddwpc_get_backup_download_url() {
    return wp_nonce_url(
        admin_url('admin-post.php?action=ddwpc_backup_comments'),
        'ddwpc_backup_comments',
        'nonce'
    );
}

/**
 * Create and stream a CSV backup of all comments.
 *
 * The backup contains personal data from the comments table, so it is served
 * through an authenticated admin-post request instead of writing a public file
 * under uploads.
 *
 * @return void
 */
function ddwpc_backup_comments() {
    $nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';

    if (!wp_verify_nonce($nonce, 'ddwpc_backup_comments')) {
        wp_die(esc_html__('Security check failed.', 'delete-disable-comments'), '', array('response' => 403));
    }

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Insufficient permissions.', 'delete-disable-comments'), '', array('response' => 403));
    }

    // Finish the export in a private temporary stream before sending download
    // headers, so database/write failures cannot produce a successful partial CSV.
    $output = fopen('php://temp/maxmemory:5242880', 'w+');
    if (false === $output) {
        wp_die(esc_html__('Failed to create backup file.', 'delete-disable-comments'), '', array('response' => 500));
    }
    try {
        $exported = ddwpc_write_comment_backup($output);
    } catch (RuntimeException $error) {
        fclose($output);
        wp_die(esc_html__('Failed to create backup file.', 'delete-disable-comments'), '', array('response' => 500));
        return;
    }

    $filename = 'ddwpc-comments-backup-' . gmdate('Y-m-d-H-i-s') . '-' . $exported . '-comments.csv';
    rewind($output);
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    header('X-DDWPC-Exported-Comments: ' . $exported);
    fpassthru($output);
    fclose($output);
    exit;
}

/**
 * Write the CSV to a stream and return the actual number of exported comments.
 *
 * @param resource $output Writable stream.
 * @return int Exported row count, excluding the header.
 * @throws RuntimeException When reading or writing fails.
 */
function ddwpc_write_comment_backup($output) {
    if (false === fputcsv($output, ddwpc_get_comment_backup_headers(), ',', '"', '')) {
        throw new RuntimeException('Could not write CSV header.');
    }
    $last_id = 0;
    $exported = 0;
    do {
        $comments = ddwpc_get_comment_batch($last_id);
        foreach ($comments as $comment) {
            if (false === fputcsv($output, ddwpc_format_comment_for_backup($comment), ',', '"', '')) {
                throw new RuntimeException('Could not write CSV row.');
            }
            $last_id = (int) $comment->comment_ID;
            $exported++;
        }
    } while (count($comments) === 500);
    return $exported;
}

/**
 * CSV headers used for comment backups.
 *
 * @return string[]
 */
function ddwpc_get_comment_backup_headers() {
    return array(
        'comment_ID',
        'comment_post_ID',
        'comment_author',
        'comment_author_email',
        'comment_author_url',
        'comment_author_IP',
        'comment_date',
        'comment_date_gmt',
        'comment_content',
        'comment_karma',
        'comment_approved',
        'comment_agent',
        'comment_type',
        'comment_parent',
        'user_id',
    );
}

/**
 * Convert a WP_Comment object into a stable CSV row.
 *
 * @param WP_Comment $comment Comment object.
 * @return array
 */
function ddwpc_format_comment_for_backup($comment) {
    return array(
        $comment->comment_ID,
        $comment->comment_post_ID,
        $comment->comment_author,
        $comment->comment_author_email,
        $comment->comment_author_url,
        $comment->comment_author_IP,
        $comment->comment_date,
        $comment->comment_date_gmt,
        $comment->comment_content,
        $comment->comment_karma,
        $comment->comment_approved,
        $comment->comment_agent,
        $comment->comment_type,
        $comment->comment_parent,
        $comment->user_id,
    );
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

    // Sanitize and validate the toggle payload (accept string or bool from jQuery).
    if (!isset($_POST['disabled'])) {
        wp_send_json_error(array(
            'message' => esc_html__('Invalid input format.', 'delete-disable-comments'),
        ));
        return;
    }

    $disabled_raw = sanitize_text_field(wp_unslash($_POST['disabled']));
    $disabled     = filter_var($disabled_raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    if ($disabled === null) {
        wp_send_json_error(array(
            'message' => esc_html__('Invalid boolean value.', 'delete-disable-comments'),
        ));
        return;
    }

    // Persist the new setting (string '0' / '1' for backwards compatibility).
    $new_value     = $disabled ? '1' : '0';
    $current_value = get_option('ddwpc_disable_comments');

    if ((string) $current_value !== $new_value) {
        update_option('ddwpc_disable_comments', $new_value);
    }

    // Verify persisted state (update_option() may return false when the value is unchanged).
    if (ddwpc_is_disable_comments_enabled() !== $disabled) {
        wp_send_json_error(array(
            'message' => esc_html__('Failed to update comment settings.', 'delete-disable-comments'),
        ));
        return;
    }

    if ($disabled) {
        // Fast path only: flip defaults so new content stays closed.
        // Bulk-closing existing posts is intentionally deferred to the
        // "Close all comments now" maintenance action so the AJAX request
        // returns immediately and does not lock wp_posts on large sites.
        ddwpc_apply_disable_comments_defaults(true);
    }

    $message = $disabled
        ? esc_html__(
            'Comments have been disabled site-wide. Use "Close all comments now" below if existing posts still allow comments.',
            'delete-disable-comments'
        )
        : esc_html__('Comments have been enabled site-wide.', 'delete-disable-comments');

    wp_send_json_success(array(
        'message' => $message,
        'status'  => $disabled ? 'disabled' : 'enabled',
    ));
}

/**
 * Manually trigger the bulk-close action from the admin UI.
 *
 * Useful when the operator imports posts later, restores from backup,
 * or used to run a previous version of the plugin where some posts may
 * still have open comment_status / ping_status fields.
 *
 * Idempotent: returns 0 when nothing needs to be closed.
 *
 * @since 1.0.2
 * @return void Sends a JSON response and exits.
 */
function ddwpc_close_all_now() {
    if (!check_ajax_referer('ddwpc_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => esc_html__('Security check failed.', 'delete-disable-comments'),
        ));
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => esc_html__('Insufficient permissions.', 'delete-disable-comments'),
        ));
        return;
    }

    $closed = ddwpc_close_all_post_comments_in_db();

    wp_send_json_success(array(
        'message' => sprintf(
            /* translators: %d: number of posts whose comments were just closed */
            esc_html(_n(
                '%d post was closed.',
                '%d posts were closed.',
                $closed,
                'delete-disable-comments'
            )),
            $closed
        ),
        'closed'  => $closed,
        'remaining' => ddwpc_count_posts_with_open_comments(),
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
    add_action('wp_ajax_ddwpc_toggle_comments', 'ddwpc_toggle_comments');
    add_action('wp_ajax_ddwpc_get_status', 'ddwpc_get_status');
    add_action('wp_ajax_ddwpc_close_all_now', 'ddwpc_close_all_now');
    add_action('admin_post_ddwpc_backup_comments', 'ddwpc_backup_comments');
}
