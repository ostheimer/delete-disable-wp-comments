jQuery(document).ready(function($) {
    // Function to show messages
    function showMessage(message, type) {
        var $messageDiv = type === 'error' ? $('#error-message') : $('#status-message');
        $messageDiv.text(message).fadeIn();
        setTimeout(function() {
            $messageDiv.fadeOut();
        }, 5000); // Hide after 5 seconds
    }

    // Function to show confirmation dialog
    function showConfirmDialog(message, callback) {
        var $dialog = $('#confirm-dialog');
        var returnFocus = document.activeElement;
        function closeDialog(confirmed) {
            $dialog.off('keydown').hide();
            if (returnFocus && typeof returnFocus.focus === 'function') returnFocus.focus();
            callback(confirmed);
        }
        $('#confirm-message').text(message);
        $dialog.show();
        $('#confirm-no').trigger('focus');

        $('#confirm-yes').off('click').on('click', function() {
            closeDialog(true);
        });

        $('#confirm-no').off('click').on('click', function() {
            closeDialog(false);
        });
        $dialog.off('keydown').on('keydown', function(event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeDialog(false);
            } else if (event.key === 'Tab') {
                var $target = event.shiftKey ? $('#confirm-no') : $('#confirm-yes');
                if (document.activeElement === $target[0]) {
                    event.preventDefault();
                    (event.shiftKey ? $('#confirm-yes') : $('#confirm-no')).trigger('focus');
                }
            }
        });
    }

    function refreshCounts() {
        $.post(ddwpcAjax.ajaxurl, { action: 'ddwpc_get_counts', nonce: ddwpcAjax.nonce })
            .done(function(response) {
                if (!response || !response.success) return;
                $.each(['public', 'reviews', 'notes', 'other'], function(_, scope) {
                    $('.ddwpc-scope-count[data-scope="' + scope + '"]').text(response.data[scope]);
                });
                $('.ddwpc-count strong').text(response.data.public_spam);
            });
    }

    function runDeletion(action, scopes, $button, originalText) {
        var cursor = 0;
        var deleted = 0;
        $button.text(ddwpcAjax.deleting).prop('disabled', true);
        function nextBatch() {
            $.post(ddwpcAjax.ajaxurl, {
                action: action, nonce: ddwpcAjax.nonce, cursor: cursor, scopes: scopes
            }).done(function(response) {
                if (!response || !response.success) {
                    deleted += Number(response && response.data && response.data.deleted || 0);
                    $('#ddwpc-progress').text(deleted + ' ' + ddwpcAjax.deleted_so_far);
                    showMessage((response && response.data && response.data.message) || ddwpcAjax.error_delete_all, 'error');
                    $button.text(originalText).prop('disabled', false);
                    refreshCounts();
                    return;
                }
                deleted += Number(response.data.deleted || 0);
                cursor = Number(response.data.cursor || cursor);
                $('#ddwpc-progress').text(deleted + ' ' + ddwpcAjax.deleted_so_far);
                if (response.data.more) {
                    nextBatch();
                    return;
                }
                $button.text(originalText).prop('disabled', false);
                showMessage(response.data.message, 'success');
                refreshCounts();
            }).fail(function() {
                $button.text(originalText).prop('disabled', false);
                showMessage(ddwpcAjax.network_error_all, 'error');
                refreshCounts();
            });
        }
        nextBatch();
    }

    $('#delete-spam-comments').on('click', function() {
        var $button = $(this);
        showConfirmDialog(ddwpcAjax.confirm_delete_spam, function(confirmed) {
            if (confirmed) runDeletion('ddwpc_delete_spam', ['public'], $button, ddwpcAjax.delete_spam_button);
        });
    });

    $('#delete-all-comments').on('click', function() {
        var $button = $(this);
        var scopes = $('input[name="ddwpc_scope"]:checked').map(function() { return this.value; }).get();
        if (!scopes.length) {
            showMessage(ddwpcAjax.choose_scope, 'error');
            return;
        }
        var count = 0;
        $.each(scopes, function(_, scope) {
            count += Number($('.ddwpc-scope-count[data-scope="' + scope + '"]').text()) || 0;
        });
        if (!count) {
            showMessage(ddwpcAjax.no_matching_comments, 'error');
            return;
        }
        showConfirmDialog(ddwpcAjax.confirm_delete_selected.replace('%d', count), function(confirmed) {
            if (confirmed) runDeletion('ddwpc_delete_all', scopes, $button, ddwpcAjax.delete_all_button);
        });
    });

    // The link points to a protected admin-post CSV export.
    $('#download-backup').on('click', function() {
        var $button = $(this);
        $button.text(ddwpcAjax.creating_export).attr('aria-disabled', 'true');
        setTimeout(function() {
            $button.text(ddwpcAjax.backup_button).removeAttr('aria-disabled');
        }, 2000);
    });

    // Handle Toggle Comments
    $('#toggle-comments').on('change', function() {
        var $toggle = $(this);
        var $statusLabel = $('.toggle-label');
        var disabled = $toggle.is(':checked');
        var previousChecked = !disabled;

        $statusLabel.text(ddwpcAjax.updating);
        $toggle.prop('disabled', true);

        $.post(ddwpcAjax.ajaxurl, {
            action: 'ddwpc_toggle_comments',
            nonce: ddwpcAjax.nonce,
            disabled: disabled ? 'true' : 'false'
        })
        .done(function(response) {
            if (response && response.success) {
                // Reload so the maintenance notice and open-post count render server-side.
                window.location.reload();
                return;
            }

            var errorMessage = (response && response.data && response.data.message)
                ? response.data.message
                : ddwpcAjax.error_toggling;
            showMessage(errorMessage, 'error');
            $toggle.prop('checked', previousChecked);
            $statusLabel.text(previousChecked ? ddwpcAjax.comments_disabled : ddwpcAjax.comments_enabled);
        })
        .fail(function() {
            showMessage(ddwpcAjax.network_error, 'error');
            $toggle.prop('checked', previousChecked);
            $statusLabel.text(previousChecked ? ddwpcAjax.comments_disabled : ddwpcAjax.comments_enabled);
        })
        .always(function() {
            $toggle.prop('disabled', false);
        });
    });

    // Handle the separate permanent change to post comment statuses.
    $(document).on('click', '#ddwpc-close-all-now', function() {
        var $button = $(this);
        var $notice = $button.closest('.ddwpc-maintenance-notice');
        var $count  = $notice.find('.ddwpc-open-posts-count');
        showConfirmDialog(ddwpcAjax.confirm_close_posts, function(confirmed) {
            if (!confirmed) return;
            $button.text(ddwpcAjax.closing_now).prop('disabled', true);
            $.post(ddwpcAjax.ajaxurl, {
            action: 'ddwpc_close_all_now',
            nonce: ddwpcAjax.nonce
            }, function(response) {
            $button.text(ddwpcAjax.close_all_now_button).prop('disabled', false);
            if (response.success) {
                showMessage(response.data.message, 'success');
                if (typeof response.data.remaining !== 'undefined') {
                    $count.text(response.data.remaining);
                    if (response.data.remaining === 0) {
                        $notice.fadeOut();
                    }
                }
            } else {
                showMessage((response.data && response.data.message) || ddwpcAjax.error_close_all_now, 'error');
            }
            }).fail(function() {
            $button.text(ddwpcAjax.close_all_now_button).prop('disabled', false);
            showMessage(ddwpcAjax.network_error_close_all_now, 'error');
            });
        });
    });

    // Get initial status on page load
    function getInitialStatus() {
        var $toggle = $('#toggle-comments');
        var $statusLabel = $('.toggle-label');

        $.post(ddwpcAjax.ajaxurl, { // Use prefixed JS object
            action: 'ddwpc_get_status', // Prefixed action
            nonce: ddwpcAjax.nonce // Use prefixed JS object
        }, function(response) {
            if (response.success) {
                var isDisabled = response.data.status === 'disabled';
                $toggle.prop('checked', isDisabled);
                $statusLabel.text(response.data.message);
                $('.comment-status').removeClass('enabled disabled').addClass(response.data.status);
            } else {
                // Handle error fetching status if necessary
                console.error('Error fetching initial comment status:', response.data.message);
                $statusLabel.text(ddwpcAjax.error_loading_status);
            }
        }).fail(function() {
            console.error('Network error fetching initial comment status.');
            $statusLabel.text(ddwpcAjax.error_loading_status);
        });
    }
    
    // Call on page load if the toggle exists
    /* // Temporarily disable initial status fetch for debugging
    if ($('#toggle-comments').length) {
        getInitialStatus();
    }
    */

});
