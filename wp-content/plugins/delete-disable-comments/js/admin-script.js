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
        $('#confirm-message').text(message);
        $('#confirm-dialog').fadeIn();

        $('#confirm-yes').off('click').on('click', function() {
            $('#confirm-dialog').fadeOut();
            callback(true);
        });

        $('#confirm-no').off('click').on('click', function() {
            $('#confirm-dialog').fadeOut();
            callback(false);
        });
    }

    // Handle Delete Spam Comments
    $('#delete-spam-comments').on('click', function() {
        var $button = $(this);
        showConfirmDialog(ddwpcAjax.confirm_delete_spam, function(confirmed) { // Use prefixed JS object
            if (confirmed) {
                $button.text(ddwpcAjax.deleting).prop('disabled', true); // Use prefixed JS object
                $.post(ddwpcAjax.ajaxurl, { // Use prefixed JS object
                    action: 'ddwpc_delete_spam', // Prefixed action
                    nonce: ddwpcAjax.nonce // Use prefixed JS object
                }, function(response) {
                    $button.text(ddwpcAjax.delete_spam_button).prop('disabled', false); // Use prefixed JS object
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                    } else {
                        showMessage(response.data.message || ddwpcAjax.error_delete_spam, 'error'); // Use prefixed JS object
                    }
                }).fail(function() {
                    $button.text(ddwpcAjax.delete_spam_button).prop('disabled', false); // Use prefixed JS object
                    showMessage(ddwpcAjax.network_error_spam, 'error'); // Use prefixed JS object
                });
            }
        });
    });

    // Handle Delete All Comments
    $('#delete-all-comments').on('click', function() {
        var $button = $(this);
        showConfirmDialog(ddwpcAjax.confirm_delete_all, function(confirmed) { // Use prefixed JS object
            if (confirmed) {
                $button.text(ddwpcAjax.deleting).prop('disabled', true); // Use prefixed JS object
                $.post(ddwpcAjax.ajaxurl, { // Use prefixed JS object
                    action: 'ddwpc_delete_all', // Prefixed action
                    nonce: ddwpcAjax.nonce // Use prefixed JS object
                }, function(response) {
                    $button.text(ddwpcAjax.delete_all_button).prop('disabled', false); // Use prefixed JS object
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                    } else {
                        showMessage(response.data.message || ddwpcAjax.error_delete_all, 'error'); // Use prefixed JS object
                    }
                }).fail(function() {
                    $button.text(ddwpcAjax.delete_all_button).prop('disabled', false); // Use prefixed JS object
                    showMessage(ddwpcAjax.network_error_all, 'error'); // Use prefixed JS object
                });
            }
        });
    });

    // Handle Download Backup
    $('#download-backup').on('click', function() {
        var $button = $(this);
        $button.text(ddwpcAjax.creating_backup).prop('disabled', true); // Use prefixed JS object
        $.post(ddwpcAjax.ajaxurl, { // Use prefixed JS object
            action: 'ddwpc_backup_comments', // Prefixed action
            nonce: ddwpcAjax.nonce // Use prefixed JS object
        }, function(response) {
            $button.text(ddwpcAjax.backup_button).prop('disabled', false); // Use prefixed JS object
            if (response.success) {
                showMessage(response.data.message, 'success');
                // Trigger file download
                window.location.href = response.data.file_url;
            } else {
                showMessage(response.data.message || ddwpcAjax.error_backup, 'error'); // Use prefixed JS object
            }
        }).fail(function() {
            $button.text(ddwpcAjax.backup_button).prop('disabled', false); // Use prefixed JS object
            showMessage(ddwpcAjax.network_error_backup, 'error'); // Use prefixed JS object
        });
    });

    // Handle Toggle Comments
    $('#toggle-comments').on('change', function() {
        var $toggle = $(this);
        var $statusLabel = $('.toggle-label');
        var disabled = $toggle.is(':checked');

        $statusLabel.text(ddwpcAjax.updating); // Use prefixed JS object

        $.post(ddwpcAjax.ajaxurl, { // Use prefixed JS object
            action: 'ddwpc_toggle_comments', // Prefixed action
            nonce: ddwpcAjax.nonce, // Use prefixed JS object
            disabled: disabled ? 'true' : 'false' // Send as string
        }, function(response) {
            if (response.success) {
                $statusLabel.text(response.data.message);
                $('.comment-status').removeClass('enabled disabled').addClass(response.data.status);
                 // Ensure the toggle visually matches the state if AJAX call succeeded
                $toggle.prop('checked', response.data.status === 'disabled');
            } else {
                showMessage(response.data.message || ddwpcAjax.error_toggling, 'error'); // Use prefixed JS object
                // Revert visual state on error
                $toggle.prop('checked', !disabled);
                $statusLabel.text(disabled ? ddwpcAjax.comments_enabled : ddwpcAjax.comments_disabled); // Use prefixed JS object
            }
        }).fail(function() {
            showMessage(ddwpcAjax.network_error, 'error'); // Use prefixed JS object
            // Revert visual state on error
            $toggle.prop('checked', !disabled);
            $statusLabel.text(disabled ? ddwpcAjax.comments_enabled : ddwpcAjax.comments_disabled); // Use prefixed JS object
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
                $statusLabel.text('Error loading status');
            }
        }).fail(function() {
            console.error('Network error fetching initial comment status.');
            $statusLabel.text('Error loading status');
        });
    }
    
    // Call on page load if the toggle exists
    /* // Temporarily disable initial status fetch for debugging
    if ($('#toggle-comments').length) {
        getInitialStatus();
    }
    */

}); 