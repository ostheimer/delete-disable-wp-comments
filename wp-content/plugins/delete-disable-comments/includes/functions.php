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

    ddwpc_process_delete_batch(array('public'), true);
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

    $allowed = array('public', 'reviews', 'notes', 'other');
    $raw_scopes = isset($_POST['scopes']) ? wp_unslash($_POST['scopes']) : array('public'); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each scope is sanitized with sanitize_key() below.
    if (!is_array($raw_scopes)) {
        wp_send_json_error(array(
            'message' => esc_html__('Choose at least one valid comment type.', 'delete-disable-comments'),
        ));
        return;
    }
    $scopes = array_values(array_unique(array_map('sanitize_key', $raw_scopes)));
    if (!$scopes || array_diff($scopes, $allowed)) {
        wp_send_json_error(array(
            'message' => esc_html__('Choose at least one valid comment type.', 'delete-disable-comments'),
        ));
        return;
    }
    ddwpc_process_delete_batch($scopes, false);
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
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fresh complete table traversal is required for export/deletion verification.
    $comments = $wpdb->get_results($wpdb->prepare(
        "SELECT c.*, COALESCE(p.post_type, '') AS ddwpc_post_type
         FROM {$wpdb->comments} AS c LEFT JOIN {$wpdb->posts} AS p ON p.ID = c.comment_post_ID
         WHERE c.comment_ID > %d ORDER BY c.comment_ID ASC LIMIT %d",
        max(0, (int) $after_id),
        max(1, min(500, (int) $limit))
    ));
    if (null === $comments || $wpdb->last_error) {
        throw new RuntimeException('Could not read comments.');
    }
    return $comments;
}

/** Keep editor Notes, shop reviews and extension data out of the default cleanup. */
function ddwpc_comment_scope($comment) {
    $type = (string) $comment->comment_type;
    $post_type = isset($comment->ddwpc_post_type) ? (string) $comment->ddwpc_post_type : '';
    if ('note' === $type) {
        return 'notes';
    }
    if ('review' === $type || 'product' === $post_type) {
        return 'reviews';
    }
    if (in_array($type, array('', 'comment', 'pingback', 'trackback'), true)) {
        return 'public';
    }
    return 'other';
}

/** Return totals by scope, plus the spam count for ordinary public comments. */
function ddwpc_get_scope_counts() {
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Fresh grouped counts are needed before irreversible cleanup.
    $rows = $wpdb->get_results(
        "SELECT c.comment_type, COALESCE(p.post_type, '') AS ddwpc_post_type,
                c.comment_approved, COUNT(*) AS ddwpc_total
         FROM {$wpdb->comments} AS c LEFT JOIN {$wpdb->posts} AS p ON p.ID = c.comment_post_ID
         GROUP BY c.comment_type, p.post_type, c.comment_approved"
    );
    if (null === $rows || $wpdb->last_error) {
        throw new RuntimeException('Could not count comments.');
    }
    $counts = array('public' => 0, 'reviews' => 0, 'notes' => 0, 'other' => 0, 'public_spam' => 0);
    foreach ($rows as $row) {
        $scope = ddwpc_comment_scope($row);
        $counts[$scope] += (int) $row->ddwpc_total;
        if ('public' === $scope && 'spam' === (string) $row->comment_approved) {
            $counts['public_spam'] += (int) $row->ddwpc_total;
        }
    }
    return $counts;
}

/** Delete one bounded batch, returning a cursor so the browser can resume. */
function ddwpc_process_delete_batch($scopes, $spam_only) {
    // The AJAX entry points verify the nonce and administrator capability before calling this helper.
    $cursor = isset($_POST['cursor']) ? sanitize_text_field(wp_unslash($_POST['cursor'])) : '0'; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by the calling AJAX handler.
    if (!is_scalar($cursor) || !ctype_digit((string) $cursor)) {
        wp_send_json_error(array('message' => esc_html__('Invalid cleanup cursor.', 'delete-disable-comments')));
        return;
    }
    $last_id = (int) $cursor;
    $deleted = 0;
    try {
        $comments = ddwpc_get_comment_batch($last_id, 100);
        foreach ($comments as $comment) {
            $last_id = (int) $comment->comment_ID;
            if (!in_array(ddwpc_comment_scope($comment), $scopes, true)
                || ($spam_only && 'spam' !== (string) $comment->comment_approved)) {
                continue;
            }
            if (!wp_delete_comment($last_id, true)) {
                throw new RuntimeException('Could not delete comment.');
            }
            $deleted++;
        }
        $more = count($comments) === 100;
        $remaining = null;
        if (!$more) {
            $counts = ddwpc_get_scope_counts();
            $remaining = $spam_only ? $counts['public_spam'] : array_sum(array_intersect_key($counts, array_fill_keys($scopes, true)));
            if ($remaining > 0) {
                throw new RuntimeException('Matching comments remain.');
            }
        }
    } catch (RuntimeException $error) {
        wp_send_json_error(array(
            'message' => esc_html__('Cleanup stopped before completion. Some comments may already be deleted; check the counts and try again.', 'delete-disable-comments'),
            'deleted' => $deleted,
            'cursor' => $last_id,
        ));
        return;
    }
    wp_send_json_success(array(
        'message' => $more
            ? esc_html__('Cleanup continues in the next batch.', 'delete-disable-comments')
            : esc_html__('Selected comments were deleted.', 'delete-disable-comments'),
        'deleted' => $deleted,
        'cursor' => $last_id,
        'more' => $more,
        'remaining' => $remaining,
    ));
}

/**
 * Build the authenticated CSV export download URL for administrators.
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
 * Create and stream a CSV export of all comments.
 *
 * The export contains personal data from the comments table, so it is served
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
        wp_die(esc_html__('Failed to create CSV export.', 'delete-disable-comments'), '', array('response' => 500));
    }
    try {
        $exported = ddwpc_write_comment_backup($output);
    } catch (RuntimeException $error) {
        // PHP closes the request-local temporary stream when wp_die() terminates.
        wp_die(esc_html__('Failed to create CSV export.', 'delete-disable-comments'), '', array('response' => 500));
        return;
    }

    $filename = 'ddwpc-comments-export-' . gmdate('Y-m-d-H-i-s') . '-' . $exported . '-comments.csv';
    rewind($output);
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    header('X-DDWPC-Exported-Comments: ' . $exported);
    fpassthru($output);
    // PHP closes the request-local temporary stream on exit.
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
 * Prevent a spreadsheet from interpreting an untrusted CSV value as a formula.
 *
 * Comment fields may be supplied by unauthenticated visitors. Prefixing risky
 * values with an apostrophe keeps the value visible while making common
 * spreadsheet applications treat it as text.
 *
 * @param mixed $value CSV cell value.
 * @return mixed Neutralized string or the original non-string value.
 */
function ddwpc_neutralize_csv_formula($value) {
    if (!is_string($value) || '' === $value) {
        return $value;
    }

    if (preg_match('/^[\x09\x0D\x0A]|^\s*[=+\-@]/u', $value)) {
        return "'" . $value;
    }

    return $value;
}

/**
 * Convert a WP_Comment object into a stable CSV row.
 *
 * @param WP_Comment $comment Comment object.
 * @return array
 */
function ddwpc_format_comment_for_backup($comment) {
    $row = array(
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

    return array_map('ddwpc_neutralize_csv_formula', $row);
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
    } else {
        ddwpc_apply_disable_comments_defaults(false);
    }

    $message = $disabled
        ? esc_html__(
            'Comments have been disabled site-wide. Use "Close all comments now" below if existing posts still allow comments.',
            'delete-disable-comments'
        )
        : esc_html__('The plugin is no longer blocking comments. Check Discussion defaults and any posts closed by the separate maintenance action.', 'delete-disable-comments');

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

    if (false === $closed) {
        wp_send_json_error(array('message' => esc_html__('Could not close post comments. No success was reported.', 'delete-disable-comments')));
        return;
    }

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

function ddwpc_get_counts() {
    if (!check_ajax_referer('ddwpc_nonce', 'nonce', false) || !current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'delete-disable-comments')));
        return;
    }
    try {
        wp_send_json_success(ddwpc_get_scope_counts());
    } catch (RuntimeException $error) {
        wp_send_json_error(array('message' => esc_html__('Could not read comment counts.', 'delete-disable-comments')));
    }
}

/**
 * Register AJAX handlers for the plugin.
 */
function ddwpc_register_ajax_handlers() {
    add_action('wp_ajax_ddwpc_delete_spam', 'ddwpc_delete_spam_comments');
    add_action('wp_ajax_ddwpc_delete_all', 'ddwpc_delete_all_comments');
    add_action('wp_ajax_ddwpc_toggle_comments', 'ddwpc_toggle_comments');
    add_action('wp_ajax_ddwpc_get_status', 'ddwpc_get_status');
    add_action('wp_ajax_ddwpc_get_counts', 'ddwpc_get_counts');
    add_action('wp_ajax_ddwpc_close_all_now', 'ddwpc_close_all_now');
    add_action('admin_post_ddwpc_backup_comments', 'ddwpc_backup_comments');
}
