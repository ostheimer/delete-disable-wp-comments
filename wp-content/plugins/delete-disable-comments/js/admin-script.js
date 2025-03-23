jQuery(document).ready(function($) {
    // Show notification function
    function showNotice(message, type = 'success') {
        // Remove any existing notices
        $('.notice').remove();
        
        // Create notice element
        const notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Insert notice at the top of the page
        $('.wrap h1').after(notice);
        
        // Add fade-in effect
        notice.hide().fadeIn();
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Hilfsfunktion zum Anzeigen des Ladezustands
    function toggleLoading(button, isLoading) {
        button.prop('disabled', isLoading);
        if (isLoading) {
            button.addClass('updating-message');
        } else {
            button.removeClass('updating-message');
        }
    }

    // Initialize toggle state
    function initializeToggleState() {
        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_comments_status',
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#toggle-comments').prop('checked', response.data.disabled === "1");
                    $('.comment-status').text(response.data.message);
                    $('.comment-status').removeClass('enabled disabled').addClass(response.data.status);
                }
            },
            error: function() {
                showNotice(deleteDisableCommentsAjax.network_error, 'error');
            }
        });
    }

    // Initialize on page load
    initializeToggleState();

    // Handle delete spam comments
    $('#delete-spam-comments').on('click', function() {
        if (!confirm(deleteDisableCommentsAjax.confirm_delete_spam)) {
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text(deleteDisableCommentsAjax.deleting);

        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_spam_comments',
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(deleteDisableCommentsAjax.success_delete_spam);
                } else {
                    showNotice(response.data.message || deleteDisableCommentsAjax.error_delete_spam, 'error');
                }
            },
            error: function() {
                showNotice(deleteDisableCommentsAjax.network_error_spam, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(deleteDisableCommentsAjax.delete_spam_button);
            }
        });
    });

    // Handle delete all comments
    $('#delete-all-comments').on('click', function() {
        if (!confirm(deleteDisableCommentsAjax.confirm_delete_all)) {
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text(deleteDisableCommentsAjax.deleting);

        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_all_comments',
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(deleteDisableCommentsAjax.success_delete_all);
                } else {
                    showNotice(response.data.message || deleteDisableCommentsAjax.error_delete_all, 'error');
                }
            },
            error: function() {
                showNotice(deleteDisableCommentsAjax.network_error_all, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(deleteDisableCommentsAjax.delete_all_button);
            }
        });
    });

    // Handle backup comments
    $('#backup-comments').on('click', function() {
        const $button = $(this);
        $button.prop('disabled', true).text(deleteDisableCommentsAjax.creating_backup);

        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'backup_comments',
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data.file_url) {
                    showNotice(deleteDisableCommentsAjax.success_backup);
                    // Create temporary link and click it to start download
                    const link = document.createElement('a');
                    link.href = response.data.file_url;
                    link.download = '';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    showNotice(response.data.message || deleteDisableCommentsAjax.error_backup, 'error');
                }
            },
            error: function() {
                showNotice(deleteDisableCommentsAjax.network_error_backup, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(deleteDisableCommentsAjax.backup_button);
            }
        });
    });

    // Handle toggle comments
    $('#toggle-comments').on('change', function() {
        const $toggle = $(this);
        const $status = $('.comment-status');
        const isDisabled = $toggle.prop('checked');
        
        // Show loading state
        $toggle.prop('disabled', true);
        $status.text(deleteDisableCommentsAjax.updating);

        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_comments',
                disabled: isDisabled,
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message);
                    $status.text(isDisabled ? 
                        deleteDisableCommentsAjax.comments_disabled : 
                        deleteDisableCommentsAjax.comments_enabled
                    );
                    $status.removeClass('enabled disabled').addClass(response.data.status);
                } else {
                    // Revert toggle state on error
                    $toggle.prop('checked', !isDisabled);
                    showNotice(response.data.message || deleteDisableCommentsAjax.error_toggling, 'error');
                }
            },
            error: function() {
                // Revert toggle state on network error
                $toggle.prop('checked', !isDisabled);
                showNotice(deleteDisableCommentsAjax.network_error, 'error');
            },
            complete: function() {
                $toggle.prop('disabled', false);
            }
        });
    });

    // Füge Ladeindikator zu allen Buttons hinzu
    $('.button').after('<span class="loading"></span>');
}); 