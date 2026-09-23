<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ddwpc_admin_page() {
    try {
        $scope_counts = ddwpc_get_scope_counts();
    } catch (RuntimeException $error) {
        $scope_counts = null;
    }
    ?>
    <div class="wrap ddwpc-admin">
        <h1><?php esc_html_e('Delete & Disable Comments', 'delete-disable-comments'); ?></h1>
        
        <div class="notice-container"></div>
        <?php if (null === $scope_counts) : ?>
            <div class="notice notice-error"><p><?php esc_html_e('Could not read comment counts. Cleanup is unavailable until the database can be read.', 'delete-disable-comments'); ?></p></div>
        <?php endif; ?>
        
        <div class="ddwpc-admin-grid">
            <div class="card ddwpc-card ddwpc-card-spam">
                <div class="ddwpc-card-icon" aria-hidden="true">
                    <span class="dashicons dashicons-trash"></span>
                </div>
                <h2><?php esc_html_e('Delete Spam Comments', 'delete-disable-comments'); ?></h2>
                <p><?php esc_html_e('Remove spam from ordinary public comments. Product reviews, editor Notes and custom types stay untouched.', 'delete-disable-comments'); ?></p>
                <p class="ddwpc-count"><strong><?php echo esc_html(null === $scope_counts ? '–' : (string) $scope_counts['public_spam']); ?></strong> <?php esc_html_e('matching spam comments', 'delete-disable-comments'); ?></p>
                <button class="button button-primary ddwpc-button-danger" data-cy="delete-spam-btn" id="delete-spam-comments" <?php disabled(null === $scope_counts); ?>>
                    <?php esc_html_e('Delete Spam Comments', 'delete-disable-comments'); ?>
                </button>
            </div>
            
            <div class="card ddwpc-card ddwpc-card-delete">
                <div class="ddwpc-card-icon" aria-hidden="true">
                    <span class="dashicons dashicons-download"></span>
                </div>
                <h2><?php esc_html_e('Delete Selected Comments', 'delete-disable-comments'); ?></h2>
                <p><?php esc_html_e('Choose what to remove. Editor Notes, product reviews and extension data are protected by default.', 'delete-disable-comments'); ?></p>
                <fieldset class="ddwpc-scopes" id="ddwpc-scopes">
                    <legend class="screen-reader-text"><?php esc_html_e('Comment types to delete', 'delete-disable-comments'); ?></legend>
                    <?php
                    $scope_labels = array(
                        'public' => __('Public comments and pingbacks', 'delete-disable-comments'),
                        'reviews' => __('Product reviews', 'delete-disable-comments'),
                        'notes' => __('Editor Notes', 'delete-disable-comments'),
                        'other' => __('Other custom comment types', 'delete-disable-comments'),
                    );
                    foreach ($scope_labels as $scope => $label) :
                        ?>
                        <label><input type="checkbox" name="ddwpc_scope" value="<?php echo esc_attr($scope); ?>" <?php checked('public' === $scope); ?>>
                            <?php echo esc_html($label); ?> <span class="ddwpc-scope-count" data-scope="<?php echo esc_attr($scope); ?>"><?php echo esc_html(null === $scope_counts ? '–' : (string) $scope_counts[$scope]); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <button class="button button-primary ddwpc-button-danger" data-cy="delete-all-btn" id="delete-all-comments" <?php disabled(null === $scope_counts); ?>>
                    <?php esc_html_e('Delete Selected Comments', 'delete-disable-comments'); ?>
                </button>
                <a class="button" data-cy="backup-btn" id="download-backup" href="<?php echo esc_url(ddwpc_get_backup_download_url()); ?>">
                    <?php esc_html_e('Export all comments as CSV', 'delete-disable-comments'); ?>
                </a>
                <p class="description"><?php esc_html_e('The CSV includes personal data and all comment types. It is not a restorable backup. Make a database backup before deletion.', 'delete-disable-comments'); ?></p>
                <p id="ddwpc-progress" role="status" aria-live="polite"></p>
            </div>
            
            <div class="card ddwpc-card ddwpc-card-disable">
                <div class="ddwpc-card-icon" aria-hidden="true">
                    <span class="dashicons dashicons-admin-comments"></span>
                </div>
                <h2><?php esc_html_e('Disable Comments', 'delete-disable-comments'); ?></h2>
                <p><?php esc_html_e('Toggle comments on or off for your entire website.', 'delete-disable-comments'); ?></p>
                <p class="description"><?php esc_html_e('Disabling also closes product reviews. Switching back restores saved site defaults when available, but does not reopen posts closed with the separate maintenance button.', 'delete-disable-comments'); ?></p>
                <?php if (ddwpc_is_disable_comments_enabled() && false === get_option('ddwpc_previous_defaults', false)) : ?>
                    <p class="description"><?php esc_html_e('This site was disabled before its previous Discussion defaults were recorded. Check Settings → Discussion after switching back on.', 'delete-disable-comments'); ?></p>
                <?php endif; ?>
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
                // Only run the open-post count query when the disable toggle is on:
                // the SELECT COUNT(*) over wp_posts becomes a full-table scan on
                // large sites and would otherwise add latency to the settings page
                // for operators who keep the toggle off.
                $open_posts_count = ddwpc_is_disable_comments_enabled()
                    ? ddwpc_count_posts_with_open_comments()
                    : 0;
                if (ddwpc_is_disable_comments_enabled() && $open_posts_count > 0) :
                    ?>
                    <div class="ddwpc-maintenance-notice" data-cy="open-posts-notice">
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
                            <?php esc_html_e('This permanently changes the comment and ping status of existing posts. Switching the toggle off will not reopen them.', 'delete-disable-comments'); ?>
                        </p>
                        <button class="button button-secondary" data-cy="close-all-now-btn" id="ddwpc-close-all-now">
                            <?php esc_html_e('Close all comments now', 'delete-disable-comments'); ?>
                        </button>
                        <span class="ddwpc-open-posts-count" data-cy="open-posts-count">
                            <?php echo esc_html($open_posts_count); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Confirmation Dialog -->
    <div id="confirm-dialog" data-cy="confirm-dialog" class="modal" role="dialog" aria-modal="true" aria-labelledby="ddwpc-confirm-title" aria-describedby="confirm-message" style="display: none;">
        <div class="modal-content">
            <h2 id="ddwpc-confirm-title"><?php esc_html_e('Confirm permanent change', 'delete-disable-comments'); ?></h2>
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
    <p class="ddwpc-help"><?php esc_html_e('Need help with a complex cleanup or migration?', 'delete-disable-comments'); ?> <a href="https://www.ostheimer.at/leistungen/wordpress-plugins/delete-disable-comments" target="_blank" rel="noopener noreferrer"><?php esc_html_e('WordPress plugin support', 'delete-disable-comments'); ?></a></p>
    <?php
} 
