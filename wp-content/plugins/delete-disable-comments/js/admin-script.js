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
        showConfirmDialog(ddcAjax.confirm_delete_spam, function(confirmed) { // Use prefixed JS object
            if (confirmed) {
                $button.text(ddcAjax.deleting).prop('disabled', true); // Use prefixed JS object
                $.post(ddcAjax.ajaxurl, { // Use prefixed JS object
                    action: 'ddc_delete_spam', // Prefixed action
                    nonce: ddcAjax.nonce // Use prefixed JS object
                }, function(response) {
                    $button.text(ddcAjax.delete_spam_button).prop('disabled', false); // Use prefixed JS object
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                    } else {
                        showMessage(response.data.message || ddcAjax.error_delete_spam, 'error'); // Use prefixed JS object
                    }
                }).fail(function() {
                    $button.text(ddcAjax.delete_spam_button).prop('disabled', false); // Use prefixed JS object
                    showMessage(ddcAjax.network_error_spam, 'error'); // Use prefixed JS object
                });
            }
        });
    });

    // Handle Delete All Comments
    $('#delete-all-comments').on('click', function() {
        var $button = $(this);
        showConfirmDialog(ddcAjax.confirm_delete_all, function(confirmed) { // Use prefixed JS object
            if (confirmed) {
                $button.text(ddcAjax.deleting).prop('disabled', true); // Use prefixed JS object
                $.post(ddcAjax.ajaxurl, { // Use prefixed JS object
                    action: 'ddc_delete_all', // Prefixed action
                    nonce: ddcAjax.nonce // Use prefixed JS object
                }, function(response) {
                    $button.text(ddcAjax.delete_all_button).prop('disabled', false); // Use prefixed JS object
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                    } else {
                        showMessage(response.data.message || ddcAjax.error_delete_all, 'error'); // Use prefixed JS object
                    }
                }).fail(function() {
                    $button.text(ddcAjax.delete_all_button).prop('disabled', false); // Use prefixed JS object
                    showMessage(ddcAjax.network_error_all, 'error'); // Use prefixed JS object
                });
            }
        });
    });

    // Handle Download Backup
    $('#download-backup').on('click', function() {
        var $button = $(this);
        $button.text(ddcAjax.creating_backup).prop('disabled', true); // Use prefixed JS object
        $.post(ddcAjax.ajaxurl, { // Use prefixed JS object
            action: 'ddc_backup_comments', // Prefixed action
            nonce: ddcAjax.nonce // Use prefixed JS object
        }, function(response) {
            $button.text(ddcAjax.backup_button).prop('disabled', false); // Use prefixed JS object
            if (response.success) {
                showMessage(response.data.message, 'success');
                // Trigger file download
                window.location.href = response.data.file_url;
            } else {
                showMessage(response.data.message || ddcAjax.error_backup, 'error'); // Use prefixed JS object
            }
        }).fail(function() {
            $button.text(ddcAjax.backup_button).prop('disabled', false); // Use prefixed JS object
            showMessage(ddcAjax.network_error_backup, 'error'); // Use prefixed JS object
        });
    });

    // Handle Toggle Comments
    $('#toggle-comments').on('change', function() {
        var $toggle = $(this);
        var $statusLabel = $('.toggle-label');
        var disabled = $toggle.is(':checked');

        $statusLabel.text(ddcAjax.updating); // Use prefixed JS object

        $.post(ddcAjax.ajaxurl, { // Use prefixed JS object
            action: 'ddc_toggle_comments', // Prefixed action
            nonce: ddcAjax.nonce, // Use prefixed JS object
            disabled: disabled ? 'true' : 'false' // Send as string
        }, function(response) {
            if (response.success) {
                $statusLabel.text(response.data.message);
                $('.comment-status').removeClass('enabled disabled').addClass(response.data.status);
                 // Ensure the toggle visually matches the state if AJAX call succeeded
                $toggle.prop('checked', response.data.status === 'disabled');
            } else {
                showMessage(response.data.message || ddcAjax.error_toggling, 'error'); // Use prefixed JS object
                // Revert visual state on error
                $toggle.prop('checked', !disabled);
                $statusLabel.text(disabled ? ddcAjax.comments_enabled : ddcAjax.comments_disabled); // Use prefixed JS object
            }
        }).fail(function() {
            showMessage(ddcAjax.network_error, 'error'); // Use prefixed JS object
            // Revert visual state on error
            $toggle.prop('checked', !disabled);
            $statusLabel.text(disabled ? ddcAjax.comments_enabled : ddcAjax.comments_disabled); // Use prefixed JS object
        });
    });

    // Get initial status on page load
    function getInitialStatus() {
        var $toggle = $('#toggle-comments');
        var $statusLabel = $('.toggle-label');

        $.post(ddcAjax.ajaxurl, { // Use prefixed JS object
            action: 'ddc_get_status', // Prefixed action
            nonce: ddcAjax.nonce // Use prefixed JS object
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