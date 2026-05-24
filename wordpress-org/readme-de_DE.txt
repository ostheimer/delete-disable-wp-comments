Kurzbeschreibung:
Spam bereinigen, alle Kommentare mit CSV-Backup löschen oder Kommentare websiteweit deaktivieren.

Beschreibung:
Delete & Disable Comments bietet WordPress-Administratoren eine fokussierte Ansicht für Kommentarbereinigung und das Abschalten von Kommentarfunktionen. Öffnen Sie das Plugin unter Werkzeuge → Kommentare löschen & deaktivieren (Tools → Delete & Disable Comments).

Viele ältere Websites sammeln Spam-Kommentare, nicht mehr benötigte Diskussionen oder importierte Kommentardaten, die nicht mehr zur Website gehören. Dieses Plugin bündelt diese Wartungsaufgaben an einer Stelle, ohne dass Sie die Datenbank manuell bearbeiten müssen.

Warum verwenden?

* Löschen Sie alle als Spam markierten Kommentare nach Bestätigung.
* Löschen Sie alle Kommentare nach Bestätigung.
* Laden Sie vor dem Löschen aller Kommentare ein CSV-Backup herunter.
* Deaktivieren Sie Kommentare websiteweit mit einem Schalter.
* Schließen Sie Kommentare und Pings für bestehende Beiträge, wenn der Deaktivierungsmodus aktiv ist.
* Nutzen Sie eine standardmäßige WordPress-Admin-Ansicht, die nur Administratoren zur Verfügung steht.

Typische Anwendungsfälle:

* Angesammelte Spam-Kommentare von einer Website entfernen.
* Eine Website vorbereiten, die keine Diskussionen mehr akzeptieren soll.
* Kommentare vor einem Redesign, einer Migration oder einer Kundenübergabe bereinigen.
* Vor dem dauerhaften Löschen eine CSV-Kopie der Kommentardaten herunterladen.
* Offene Kommentarstatus bestehender Beiträge schließen, ohne save_post-Hooks auszulösen.

Was „Kommentare deaktivieren“ bewirkt:

Der Schalter „Kommentare deaktivieren“ ändert das Kommentarverhalten der Website. Wenn er aktiv ist, setzt das Plugin die WordPress-Standardwerte für neue Inhalte auf geschlossen, verhindert neue Kommentar- und Ping-Einreichungen, blendet die Kommentar-Ausgabe im Frontend aus, entfernt kommentarbezogene UI, blockiert Kommentar-REST-Endpunkte, entfernt kommentarbezogene Blöcke und deaktiviert das Widget für neue Kommentare.

Es löscht keine bestehenden Kommentare. Es führt auch keine geplante Bereinigung aus. Wenn bestehende Beiträge weiterhin offene Kommentare oder Pings haben, zeigt die Admin-Ansicht einen Wartungshinweis mit dem Button „Alle Kommentare jetzt schließen“. Dieser Button führt ein direktes SQL-Update aus und vermeidet save_post-Hooks pro Beitrag. Das ist nützlich für Websites mit WPML, Yoast SEO, Polylang oder anderen Plugins, die auf Beitragsspeicherungen reagieren.

Übersetzungen:

Das Plugin enthält gettext-Übersetzungsdateien für breite EU-Sprachunterstützung, einschließlich Deutsch und weiterer EU-Sprachen. WordPress lädt die passende .mo-Datei automatisch anhand der Website-Sprache. Textdomain: delete-disable-comments.

Installation:
1. Laden Sie den Plugin-Ordner nach /wp-content/plugins/delete-disable-comments hoch oder installieren Sie die ZIP-Datei über Plugins → Neues Plugin hinzufügen → Plugin hochladen.
2. Aktivieren Sie das Plugin in WordPress über die Ansicht Plugins.
3. Öffnen Sie Werkzeuge → Kommentare löschen & deaktivieren (Tools → Delete & Disable Comments), um Spam-Kommentare zu löschen, ein CSV-Backup herunterzuladen, alle Kommentare zu löschen oder Kommentare websiteweit zu deaktivieren.

FAQ:

Kann ich Kommentare vor dem Löschen sichern?

Ja. Nutzen Sie „Backup herunterladen“, bevor Sie „Alle Kommentare löschen“ verwenden. Das Backup ist eine CSV-Datei aus den vorhandenen Kommentaren und wird als Browser-Download angeboten.

Kann das Plugin gelöschte Kommentare aus einem CSV-Backup wiederherstellen?

Nein. Das CSV-Backup dient der Aufbewahrung oder manuellen Import-Workflows. Das Plugin enthält kein Wiederherstellungswerkzeug.

Löscht das Deaktivieren von Kommentaren bestehende Kommentare?

Nein. Der Deaktivierungs-Schalter verhindert und blendet Kommentarfunktionen aus. Bestehende Kommentardatensätze bleiben in der Datenbank, bis Sie sie ausdrücklich löschen.

Was macht „Alle Kommentare jetzt schließen“?

Wenn der Deaktivierungsmodus aktiv ist, können einige bestehende Beiträge weiterhin comment_status oder ping_status auf open gesetzt haben. Der Button schließt diese Felder mit einem SQL-Update.

Ist das Schließen mit WPML, Yoast SEO oder Polylang sicher?

Die Schließaktion ruft nicht für jeden Beitrag wp_update_post() auf und löst keine save_post-Hooks aus. Dadurch eignet sie sich für Websites mit WPML, Yoast SEO, Polylang und anderen Plugins, die auf Beitragsspeicherungen reagieren.

Wer kann die Plugin-Ansicht verwenden?

Nur Benutzer mit der Berechtigung manage_options, üblicherweise Administratoren. AJAX-Aktionen sind mit WordPress-Nonces und Berechtigungsprüfungen geschützt.

Unterstützt das Plugin WordPress Multisite?

Nein. Dieses Plugin ist für WordPress-Einzelseiten ausgelegt.

Führt das Plugin geplante Bereinigungsaufgaben aus?

Nein. Bereinigungsaktionen laufen nur, wenn ein Administrator den entsprechenden Button anklickt.

Wo werden CSV-Backup-Dateien gespeichert?

Backups werden unter wp-content/uploads/delete-disable-comments/ gespeichert und zusätzlich als direkter Browser-Download angeboten.

Screenshot-Beschriftungen:
1. Hauptbereich mit drei Abschnitten: Spam, alle Kommentare löschen plus Backup, Deaktivierungs-Schalter
2. Bestätigungsdialog zum Löschen von Spam mit Ja und Nein
3. Warnung zum Löschen aller Kommentare plus Backup-Erinnerung
4. Deaktivierungs-Schalter EIN plus gelber Wartungshinweis und Button „Alle Kommentare jetzt schließen“

Upgrade Notices:

1.0.5:
Übersetzungsrelease mit breiter EU-Sprachunterstützung für die Admin-Oberfläche.

1.0.4:
Wartungsrelease für Plugin-Check und WordPress-7.0-Kompatibilitätsmetadaten.

1.0.3:
Empfohlen, wenn der Schalter zum Deaktivieren von Kommentaren bei „Wird aktualisiert...“ hängen blieb oder große Websites beim Umschalten zu lange brauchten.

1.0.2:
Empfohlen für Websites mit WPML, Yoast SEO, Polylang oder Plugins, die sich in Beitragsspeicherungen einhängen.

1.0.1:
Präfixänderung zu ddwpc_, sicherere Pfade und Backup-Dateien im Uploads-Verzeichnis.

1.0.0:
Erste Veröffentlichung.
