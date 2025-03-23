# Delete & Disable Comments

Ein leistungsstarkes WordPress-Plugin zum Verwalten und Deaktivieren von Kommentaren.

## 🌟 Funktionen

- **Spam-Kommentare löschen**: Entfernen Sie alle als Spam markierten Kommentare mit einem Klick
- **Alle Kommentare löschen**: Löschen Sie alle Kommentare mit Backup-Option
- **Kommentare deaktivieren**: Schalten Sie die Kommentarfunktion für die gesamte Website ein oder aus
- **Mehrsprachig**: Verfügbar in:
  - Deutsch (Sie) - de_DE
  - Deutsch (Österreich) - de_AT
  - Deutsch (Schweiz) - de_CH
  - Deutsch (Standard) - de
  - Englisch (USA) - en_US
  - Englisch (GB) - en_GB
  - Englisch (Standard) - en

## 📋 Voraussetzungen

- WordPress 5.0 oder höher
- PHP 7.4 oder höher
- MySQL 5.6 oder höher

## 💻 Installation

1. Laden Sie die ZIP-Datei des Plugins herunter
2. Gehen Sie in Ihrem WordPress-Dashboard zu "Plugins" → "Installieren"
3. Klicken Sie auf "Plugin hochladen"
4. Wählen Sie die heruntergeladene ZIP-Datei aus
5. Klicken Sie auf "Jetzt installieren"
6. Nach der Installation klicken Sie auf "Aktivieren"

## 🔧 Verwendung

### Spam-Kommentare löschen
1. Navigieren Sie zu "Kommentare" → "Delete & Disable Comments"
2. Klicken Sie auf "Spam-Kommentare löschen"
3. Bestätigen Sie die Aktion im Dialog

### Alle Kommentare löschen
1. Navigieren Sie zu "Kommentare" → "Delete & Disable Comments"
2. Optional: Klicken Sie auf "Backup herunterladen" um eine Sicherung zu erstellen
3. Klicken Sie auf "Alle Kommentare löschen"
4. Bestätigen Sie die Aktion im Dialog

### Kommentare deaktivieren
1. Navigieren Sie zu "Kommentare" → "Delete & Disable Comments"
2. Nutzen Sie den Toggle-Schalter um Kommentare zu aktivieren/deaktivieren
3. Die Änderung wird sofort wirksam

## 🔒 Sicherheit

- Nur Administratoren haben Zugriff auf die Plugin-Funktionen
- Alle Aktionen erfordern eine Bestätigung
- CSRF-Schutz durch WordPress Nonces
- Backup-Option vor dem Löschen aller Kommentare

## 🌐 Erweiterte Kommentar-Deaktivierung

Bei Deaktivierung der Kommentare werden:
- Kommentar-REST-API-Endpunkte deaktiviert
- Kommentar-Links aus Post-Meta entfernt
- Kommentar-Widgets deaktiviert
- Kommentar-Unterstützung für alle Post-Types entfernt
- Kommentarbereich im Frontend ausgeblendet
- Theme-spezifische Kommentar-Styles entfernt
- Kommentar-bezogene Gutenberg-Blöcke entfernt

## 🛠 Fehlerbehebung

### Kommentare erscheinen trotz Deaktivierung
1. Prüfen Sie, ob Ihr Theme die Kommentare hart-codiert anzeigt
2. Leeren Sie den Cache Ihres Browsers und Server-Caches
3. Deaktivieren Sie temporär andere Plugins, die Kommentare beeinflussen könnten

### Übersetzungen werden nicht angezeigt
1. Stellen Sie sicher, dass die korrekte Sprache in WordPress eingestellt ist
2. Überprüfen Sie, ob die Sprachdateien korrekt geladen werden
3. Leeren Sie den WordPress-Cache

## 📝 Changelog

### Version 1.0.0 (2024-03-23)
- Initiale Veröffentlichung
- Grundlegende Funktionen implementiert
- Mehrsprachige Unterstützung hinzugefügt

## 🤝 Mitwirken

Fehler gefunden oder Verbesserungsvorschläge? Erstellen Sie gerne einen Issue oder Pull Request auf GitHub.

## 📄 Lizenz

Dieses Plugin ist unter der GPL v2 oder später lizenziert. Siehe [LICENSE](LICENSE) für Details.

## 👥 Autoren

- Andreas Ostheimer
- [GitHub Repository](https://github.com/ostheimer/delete-disable-wp-comments)

## 🙏 Danksagung

Besonderer Dank an alle Mitwirkenden und die WordPress-Community für ihre Unterstützung und Feedback. 