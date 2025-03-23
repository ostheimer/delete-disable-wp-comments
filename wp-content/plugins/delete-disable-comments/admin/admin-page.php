<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function delete_disable_comments_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Delete & Disable Comments', 'delete-disable-comments'); ?></h1>
        
        <div class="notice-container"></div>
        
        <div class="card">
            <h2><?php _e('Delete Spam Comments', 'delete-disable-comments'); ?></h2>
            <p><?php _e('Remove all comments marked as spam from your database.', 'delete-disable-comments'); ?></p>
            <button class="button button-primary" data-cy="delete-spam-btn" id="delete-spam-comments">
                <?php _e('Delete Spam Comments', 'delete-disable-comments'); ?>
            </button>
        </div>
        
        <div class="card">
            <h2><?php _e('Delete All Comments', 'delete-disable-comments'); ?></h2>
            <p><?php _e('Remove all comments from your website. You can download a backup before deletion.', 'delete-disable-comments'); ?></p>
            <button class="button button-primary" data-cy="delete-all-btn" id="delete-all-comments">
                <?php _e('Delete All Comments', 'delete-disable-comments'); ?>
            </button>
            <button class="button" data-cy="backup-btn" id="download-backup">
                <?php _e('Download Backup', 'delete-disable-comments'); ?>
            </button>
        </div>
        
        <div class="card">
            <h2><?php _e('Disable Comments', 'delete-disable-comments'); ?></h2>
            <p><?php _e('Toggle comments on or off for your entire website.', 'delete-disable-comments'); ?></p>
            <div class="toggle-container">
                <label class="switch">
                    <input type="checkbox" class="toggle-switch" id="toggle-comments" <?php echo get_option('disable_comments') ? 'checked' : ''; ?>>
                    <span class="slider round"></span>
                </label>
                <span class="toggle-label">
                    <?php echo get_option('disable_comments') ? 
                        __('Comments are currently disabled', 'delete-disable-comments') : 
                        __('Comments are currently enabled', 'delete-disable-comments'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Confirmation Dialog -->
    <div id="confirm-dialog" data-cy="confirm-dialog" class="modal" style="display: none;">
        <div class="modal-content">
            <p id="confirm-message"></p>
            <div class="modal-buttons">
                <button class="button button-primary" data-cy="confirm-dialog-confirm" id="confirm-yes">
                    <?php _e('Yes', 'delete-disable-comments'); ?>
                </button>
                <button class="button" data-cy="confirm-dialog-cancel" id="confirm-no">
                    <?php _e('No', 'delete-disable-comments'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Status Messages -->
    <div id="status-message" data-cy="status-message" class="notice" style="display: none;"></div>
    <div id="error-message" data-cy="error-message" class="notice notice-error" style="display: none;"></div>
    <?php
} 