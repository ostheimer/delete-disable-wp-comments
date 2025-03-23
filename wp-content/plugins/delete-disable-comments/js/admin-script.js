jQuery(document).ready(function($) {
    // Hilfsfunktion zum Anzeigen von Benachrichtigungen
    function showNotice(message, type) {
        const notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        // Füge die Meldung nach der Überschrift ein
        $('.wrap h1').after(notice);
        
        // Mache die Meldung nach 3 Sekunden automatisch ausblendbar
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
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

    // Initialize toggle state from server
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
                    const isDisabled = response.data.disabled === "1" || response.data.disabled === true;
                    console.log('Initial state:', response.data.disabled, isDisabled);
                    $('#toggle-comments').prop('checked', isDisabled);
                    
                    // Update status text
                    const statusDiv = $('.comment-status');
                    statusDiv.text(response.data.message);
                    statusDiv.removeClass('enabled disabled').addClass(isDisabled ? 'disabled' : 'enabled');
                }
            }
        });
    }
    initializeToggleState();

    // Toggle comments
    $('#toggle-comments').on('change', function() {
        const isDisabled = $(this).prop('checked');
        console.log('Toggle state:', isDisabled);
        
        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_comments',
                nonce: deleteDisableCommentsAjax.nonce,
                disabled: isDisabled ? "1" : "0"
            },
            success: function(response) {
                if (response.success) {
                    console.log('Server response:', response.data);
                    // Use the translated message from the server
                    showNotice(response.data.message, 'success');
                    
                    // Update the status text and class
                    const statusDiv = $('.comment-status');
                    statusDiv.text(response.data.message);
                    statusDiv.removeClass('enabled disabled').addClass(isDisabled ? 'disabled' : 'enabled');
                } else {
                    showNotice('Error toggling comments.', 'error');
                }
            },
            error: function() {
                showNotice('Error toggling comments.', 'error');
            }
        });
    });

    // Handle delete spam comments
    $('#delete-spam-comments').on('click', function(e) {
        e.preventDefault();
        const button = $(this);

        if (!confirm('Möchten Sie wirklich alle Spam-Kommentare löschen?')) {
            return;
        }

        toggleLoading(button, true);
        
        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_spam_comments',
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data, 'error');
                }
            },
            error: function() {
                showNotice('Ein Fehler ist aufgetreten.', 'error');
            },
            complete: function() {
                toggleLoading(button, false);
            }
        });
    });

    // Handle delete all comments
    $('#delete-all-comments').on('click', function(e) {
        e.preventDefault();
        const button = $(this);

        if (!confirm('WARNUNG: Möchten Sie wirklich ALLE Kommentare löschen? Diese Aktion kann nicht rückgängig gemacht werden. Es wird empfohlen, vorher ein Backup zu erstellen.')) {
            return;
        }

        toggleLoading(button, true);

        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_all_comments',
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data, 'error');
                }
            },
            error: function() {
                showNotice('Ein Fehler ist aufgetreten.', 'error');
            },
            complete: function() {
                toggleLoading(button, false);
            }
        });
    });

    // Handle backup download
    $('#backup-comments').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        toggleLoading(button, true);

        $.ajax({
            url: deleteDisableCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'backup_comments',
                nonce: deleteDisableCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // CSV-Datei herunterladen
                    const blob = new Blob([atob(response.data.content)], { type: 'text/csv' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = response.data.filename;
                    link.click();
                    showNotice('Backup wurde erfolgreich heruntergeladen.', 'success');
                } else {
                    showNotice(response.data, 'error');
                }
            },
            error: function() {
                showNotice('Fehler beim Herunterladen des Backups.', 'error');
            },
            complete: function() {
                toggleLoading(button, false);
            }
        });
    });

    // Füge Ladeindikator zu allen Buttons hinzu
    $('.button').after('<span class="loading"></span>');
}); 