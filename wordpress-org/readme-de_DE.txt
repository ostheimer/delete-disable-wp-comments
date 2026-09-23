Kurzbeschreibung:
Kommentarzahlen prüfen, ausgewählte Typen in fortsetzbaren Schritten löschen, CSV exportieren oder öffentliche Kommentare deaktivieren.

Beschreibung:
Delete & Disable Comments bietet WordPress-Administratoren eine fokussierte Ansicht für Kommentarbereinigung und das Abschalten von Kommentarfunktionen. Öffnen Sie das Plugin unter Werkzeuge → Kommentare löschen & deaktivieren (Tools → Delete & Disable Comments).

Viele ältere Websites sammeln Spam-Kommentare, nicht mehr benötigte Diskussionen oder importierte Kommentardaten, die nicht mehr zur Website gehören. Dieses Plugin bündelt diese Wartungsaufgaben an einer Stelle, ohne dass Sie die Datenbank manuell bearbeiten müssen.

Warum verwenden?

* Prüfen Sie vor dem Löschen Anzahl und Typen der betroffenen Kommentare.
* Löschen Sie gewöhnlichen Spam oder ausgewählte Kommentare in kleinen, fortsetzbaren Schritten.
* Editor-Notizen, Produktbewertungen und benutzerdefinierte Kommentartypen bleiben standardmäßig erhalten.
* Exportieren Sie alle Kommentarzeilen als CSV. Der Export enthält keine Kommentar-Metadaten und ist kein wiederherstellbares Datenbank-Backup.
* Öffnen Sie CSV-Exporte in Tabellenkalkulationen, ohne formelartige Kommentarwerte auszuführen.
* Deaktivieren Sie Kommentare websiteweit mit einem Schalter.
* Stellen Sie beim erneuten Einschalten zuvor gespeicherte WordPress-Standardwerte wieder her. Dauerhaft geschlossene Beiträge bleiben geschlossen.
* Nutzen Sie eine standardmäßige WordPress-Admin-Ansicht, die nur Administratoren zur Verfügung steht.

Typische Anwendungsfälle:

* Angesammelte Spam-Kommentare von einer Website entfernen.
* Eine Website vorbereiten, die keine Diskussionen mehr akzeptieren soll.
* Kommentare vor einem Redesign, einer Migration oder einer Kundenübergabe bereinigen.
* Vor dem dauerhaften Löschen eine CSV-Kopie exportieren und beim Hoster ein vollständiges Datenbank-Backup erstellen.
* Offene Kommentarstatus bestehender Beiträge schließen, ohne save_post-Hooks auszulösen.

Was „Kommentare deaktivieren“ bewirkt:

Der Schalter „Kommentare deaktivieren“ ändert das Kommentarverhalten der Website. Wenn er aktiv ist, setzt das Plugin die WordPress-Standardwerte für neue Inhalte auf geschlossen, verhindert öffentliche Kommentar- und Ping-Einreichungen, blendet die Kommentar-Ausgabe im Frontend aus und entfernt kommentarbezogene Elemente. Produktbewertungen werden ebenfalls geschlossen. Die REST-Route für Kommentare bleibt für interne Editor-Notizen verfügbar.

Es löscht keine bestehenden Kommentare und führt keine geplante Bereinigung aus. Wenn bestehende Beiträge weiterhin offene Kommentare oder Pings haben, zeigt die Admin-Ansicht den separaten Button „Alle Kommentare jetzt schließen“. Diese bestätigte Aktion ändert die Statuswerte dauerhaft und vermeidet save_post-Hooks pro Beitrag. Das Ausschalten des Schalters öffnet die Beiträge nicht erneut. Bei Websites, die bereits mit einer älteren Plugin-Version deaktiviert wurden, sind die früheren Diskussionseinstellungen nicht gespeichert und können nicht automatisch wiederhergestellt werden.

Vor dem Löschen:

Die Standardauswahl umfasst gewöhnliche Kommentare, Pingbacks und Trackbacks außerhalb von Produktseiten, unabhängig vom Status. Produktbewertungen, Editor-Notizen und andere benutzerdefinierte Typen müssen ausdrücklich ausgewählt werden. Der Spam-Button löscht nur gewöhnlichen öffentlichen Spam. Der CSV-Export enthält alle Kommentartypen und personenbezogene Daten; bewahren Sie ihn geschützt auf. Erstellen Sie für eine Wiederherstellung zuvor ein vollständiges Datenbank-Backup beim Hoster.

Anleitung und Unterstützung bei komplexen Bereinigungen oder Migrationen: https://www.ostheimer.at/leistungen/wordpress-plugins/delete-disable-comments

Übersetzungen:

Die aktuelle Admin-Oberfläche ist vollständig ins Deutsche übersetzt. Weitere Sprachdateien sind enthalten; bei neuen Meldungen erscheint dort bis zur Aktualisierung der Übersetzung gegebenenfalls Englisch. WordPress lädt die passende .mo-Datei anhand der Website-Sprache. Textdomain: delete-disable-comments.

Installation:
1. Laden Sie den Plugin-Ordner nach /wp-content/plugins/delete-disable-comments hoch oder installieren Sie die ZIP-Datei über Plugins → Neues Plugin hinzufügen → Plugin hochladen.
2. Aktivieren Sie das Plugin in WordPress über die Ansicht Plugins.
3. Öffnen Sie Werkzeuge → Kommentare löschen & deaktivieren (Tools → Delete & Disable Comments), um Zahlen zu prüfen, CSV zu exportieren, ausgewählte Kommentare zu löschen oder Kommentare websiteweit zu deaktivieren.

FAQ:

Ist der CSV-Export ein Backup?

Nein. „Alle Kommentare als CSV exportieren“ liefert die Kommentartabelle als geschützten Administrator-Download. Kommentar-Metadaten fehlen; das Plugin kann die Datei nicht wiederherstellen. Erstellen Sie vor dem Löschen ein vollständiges Datenbank-Backup.

Kann das Plugin gelöschte Kommentare aus CSV wiederherstellen?

Nein. CSV dient der Prüfung oder einem manuellen Import. Verwenden Sie für eine Wiederherstellung das Datenbank-Backup.

Warum beginnen manche exportierten Werte mit einem Apostroph?

Kommentarfelder, die wie Tabellenformeln aussehen, erhalten im CSV ein vorangestelltes Apostroph. Dadurch bleibt nicht vertrauenswürdiger Kommentartext sichtbar, ohne dass gängige Tabellenkalkulationen ihn als Formel ausführen. Entfernen Sie dieses Schutzzeichen nur in einem vertrauenswürdigen Import-Workflow.

Löscht das Deaktivieren bestehende Kommentare oder Editor-Notizen?

Nein. Bestehende Kommentardatensätze bleiben erhalten, bis Sie sie ausdrücklich löschen. Editor-Notizen bleiben im Block-Editor nutzbar. Produktbewertungen werden durch den websiteweiten Schalter geschlossen.

Was macht „Alle Kommentare jetzt schließen“?

Wenn der Deaktivierungsmodus aktiv ist, können einige bestehende Beiträge weiterhin comment_status oder ping_status auf open gesetzt haben. Der Button schließt diese Felder dauerhaft mit einem SQL-Update. Das Ausschalten des Schalters öffnet sie nicht erneut.

Ist das Schließen mit WPML, Yoast SEO oder Polylang sicher?

Die Schließaktion ruft nicht für jeden Beitrag wp_update_post() auf und löst keine save_post-Hooks aus. Dadurch eignet sie sich für Websites mit WPML, Yoast SEO, Polylang und anderen Plugins, die auf Beitragsspeicherungen reagieren.

Wer kann die Plugin-Ansicht verwenden?

Nur Benutzer mit der Berechtigung manage_options, üblicherweise Administratoren. AJAX-Aktionen sind mit WordPress-Nonces und Berechtigungsprüfungen geschützt.

Unterstützt das Plugin WordPress Multisite?

Nein. Dieses Plugin ist für WordPress-Einzelseiten ausgelegt.

Führt das Plugin geplante Bereinigungsaufgaben aus?

Nein. Bereinigungsaktionen laufen nur, wenn ein Administrator den entsprechenden Button anklickt.

Wo werden CSV-Exporte gespeichert?

CSV-Exporte werden als geschützter Administrator-Download gestreamt. Das Plugin legt keine CSV-Dateien im öffentlichen Uploads-Verzeichnis ab.

Screenshot-Beschriftungen:
1. Hauptbereich mit Kommentarzahlen, Typauswahl, CSV-Export und Deaktivierungs-Schalter
2. Bestätigungsdialog zum Löschen von Spam mit Ja und Nein
3. Bestätigung zum Löschen ausgewählter Kommentare mit Anzahl
4. Deaktivierungs-Schalter EIN plus gelber Wartungshinweis und Button „Alle Kommentare jetzt schließen“

Upgrade Notices:

1.1.0:
Die Bereinigung schützt Notizen und Bewertungen standardmäßig. Prüfen Sie die ausgewählten Typen vor dem Löschen. CSV-Exporte sind keine wiederherstellbaren Backups.

1.0.7:
Vollständige Kommentarbereinigung und sicherere CSV-Backups. Empfohlen für Websites, die Spam löschen oder Backups in Tabellenkalkulationen öffnen.

1.0.6:
Datenschutz-Härtung für CSV-Backups. Backup-Dateien werden jetzt an Administratoren gestreamt und nicht mehr in öffentlichen Uploads abgelegt.

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
