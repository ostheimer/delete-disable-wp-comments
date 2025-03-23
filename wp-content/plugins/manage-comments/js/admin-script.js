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

    // Initialisiere den Toggle-Status beim Laden
    function initializeToggleState() {
        // Setze den Toggle-Status basierend auf dem localStorage
        const isDisabled = localStorage.getItem('commentsDisabled') === 'true';
        $('#toggle-comments').prop('checked', isDisabled);
    }

    // Initialisiere den Toggle-Status beim Laden der Seite
    initializeToggleState();

    // Kommentare aktivieren/deaktivieren
    $('#toggle-comments').on('change', function() {
        const checkbox = $(this);
        const isDisabled = checkbox.prop('checked');
        const statusElement = $('.comment-status');

        $.ajax({
            url: manageCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_comments',
                disabled: isDisabled,
                nonce: manageCommentsAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Korrekte Meldung basierend auf dem Status
                    const message = isDisabled ? 
                        'Kommentare wurden site-weit deaktiviert.' : 
                        'Kommentare wurden site-weit aktiviert.';
                    showNotice(message, 'success');
                    
                    // Status-Text und Klasse aktualisieren
                    statusElement
                        .text(isDisabled ? 'Comments are currently disabled' : 'Comments are currently enabled')
                        .removeClass('enabled disabled')
                        .addClass(isDisabled ? 'disabled' : 'enabled');
                    
                    // Speichere den Status im localStorage
                    localStorage.setItem('commentsDisabled', isDisabled);
                } else {
                    showNotice(response.data, 'error');
                    checkbox.prop('checked', !isDisabled);
                    
                    // Status-Text und Klasse zurücksetzen
                    statusElement
                        .text(!isDisabled ? 'Comments are currently disabled' : 'Comments are currently enabled')
                        .removeClass('enabled disabled')
                        .addClass(!isDisabled ? 'disabled' : 'enabled');
                }
            },
            error: function() {
                showNotice('Ein Fehler ist aufgetreten.', 'error');
                checkbox.prop('checked', !isDisabled);
                
                // Status-Text und Klasse zurücksetzen
                statusElement
                    .text(!isDisabled ? 'Comments are currently disabled' : 'Comments are currently enabled')
                    .removeClass('enabled disabled')
                    .addClass(!isDisabled ? 'disabled' : 'enabled');
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
            url: manageCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_spam_comments',
                nonce: manageCommentsAjax.nonce
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
            url: manageCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_all_comments',
                nonce: manageCommentsAjax.nonce
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
            url: manageCommentsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'backup_comments',
                nonce: manageCommentsAjax.nonce
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