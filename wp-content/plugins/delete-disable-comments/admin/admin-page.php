<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ddwpc_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Delete & Disable Comments', 'delete-disable-comments'); ?></h1>
        
        <div class="notice-container"></div>
        
        <div class="card">
            <h2><?php esc_html_e('Delete Spam Comments', 'delete-disable-comments'); ?></h2>
            <p><?php esc_html_e('Remove all comments marked as spam from your database.', 'delete-disable-comments'); ?></p>
            <button class="button button-primary" data-cy="delete-spam-btn" id="delete-spam-comments">
                <?php esc_html_e('Delete Spam Comments', 'delete-disable-comments'); ?>
            </button>
        </div>
        
        <div class="card">
            <h2><?php esc_html_e('Delete All Comments', 'delete-disable-comments'); ?></h2>
            <p><?php esc_html_e('Remove all comments from your website. You can download a backup before deletion.', 'delete-disable-comments'); ?></p>
            <button class="button button-primary" data-cy="delete-all-btn" id="delete-all-comments">
                <?php esc_html_e('Delete All Comments', 'delete-disable-comments'); ?>
            </button>
            <button class="button" data-cy="backup-btn" id="download-backup">
                <?php esc_html_e('Download Backup', 'delete-disable-comments'); ?>
            </button>
        </div>
        
        <div class="card">
            <h2><?php esc_html_e('Disable Comments', 'delete-disable-comments'); ?></h2>
            <p><?php esc_html_e('Toggle comments on or off for your entire website.', 'delete-disable-comments'); ?></p>
            <div class="toggle-container">
                <label class="switch">
                    <input type="checkbox" data-cy="toggle-comments" id="toggle-comments" <?php echo ddwpc_is_disable_comments_enabled() ? 'checked' : ''; ?>>
                    <span class="slider round"></span>
                </label>
                <span class="toggle-label" data-cy="toggle-status">
                    <?php echo esc_html(ddwpc_is_disable_comments_enabled()
                        ? __('Comments are currently disabled', 'delete-disable-comments')
                        : __('Comments are currently enabled', 'delete-disable-comments')); ?>
                </span>
            </div>

            <?php
            $open_posts_count = ddwpc_count_posts_with_open_comments();
            if (ddwpc_is_disable_comments_enabled() && $open_posts_count > 0) :
                ?>
                <div class="ddwpc-maintenance-notice" data-cy="open-posts-notice" style="margin-top:1em;padding:.75em 1em;background:#fff8e5;border-left:4px solid #dba617;">
                    <p>
                        <strong>
                            <?php
                            echo esc_html(sprintf(
                                /* translators: %d: number of posts with open comments */
                                _n(
                                    '%d post in your database still has open comments or pings.',
                                    '%d posts in your database still have open comments or pings.',
                                    $open_posts_count,
                                    'delete-disable-comments'
                                ),
                                $open_posts_count
                            ));
                            ?>
                        </strong>
                    </p>
                    <p>
                        <?php esc_html_e('Click the button below to close them in a single safe SQL update. This bypasses save_post hooks and is compatible with WPML, Yoast, and other plugins.', 'delete-disable-comments'); ?>
                    </p>
                    <button class="button button-secondary" data-cy="close-all-now-btn" id="ddwpc-close-all-now">
                        <?php esc_html_e('Close all comments now', 'delete-disable-comments'); ?>
                    </button>
                    <span class="ddwpc-open-posts-count" data-cy="open-posts-count" style="margin-left:.5em;color:#666;">
                        <?php echo esc_html($open_posts_count); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Confirmation Dialog -->
    <div id="confirm-dialog" data-cy="confirm-dialog" class="modal" style="display: none;">
        <div class="modal-content">
            <p id="confirm-message"></p>
            <div class="modal-buttons">
                <button class="button button-primary" data-cy="confirm-dialog-confirm" id="confirm-yes">
                    <?php esc_html_e('Yes', 'delete-disable-comments'); ?>
                </button>
                <button class="button" data-cy="confirm-dialog-cancel" id="confirm-no">
                    <?php esc_html_e('No', 'delete-disable-comments'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Status Messages -->
    <div id="status-message" data-cy="status-message" class="notice" style="display: none;"></div>
    <div id="error-message" data-cy="error-message" class="notice notice-error" style="display: none;"></div>
    <?php
} 