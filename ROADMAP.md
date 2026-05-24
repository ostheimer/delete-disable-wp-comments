# ROADMAP – Delete & Disable Comments

Stand: 2026-05-21 (v1.0.5, i18n-Release)

## Erledigt

| Bereich | Status | Notizen |
|--------|--------|---------|
| Kernfunktionen (Spam löschen, alle löschen, Backup, Toggle) | ✅ | Admin unter **Werkzeuge → Kommentare löschen & deaktivieren** |
| `ddwpc_init()` ohne `wp_update_post()`-Schleife | ✅ | v1.0.2 – behebt Issue #1 (WPML/save_post) |
| Bulk-Close per SQL-UPDATE (hook-frei) | ✅ | Nur noch Wartungs-Button + Aktivierung |
| Toggle-AJAX ohne Bulk-UPDATE | ✅ | v1.0.3 – behebt „Updating…“-Hänger |
| `COUNT(*)` nur bei aktivem Toggle | ✅ | PR #3 |
| PHP-Smoke-Tests | ✅ | `tests/php/test-bug-fix.php`, `npm test` |
| Cypress E2E | ❌ entfernt | v1.0.3 – nur noch PHP-Tests |
| i18n DE/EN | ✅ | v1.0.5 – vollständige Übersetzungen `de_AT` + `de_DE` (52 Strings), `.pot` aktualisiert |
| EN/DE-Strategie dokumentiert | ✅ | `wordpress-org/README.md`, Kurzverweis in `README.md` |
| Plugin Check (PHPCS/WPCS) | ✅ | v1.0.4 – Tested up to 7.0, Sanitize, phpcs:ignore für Bulk-SQL |
| Build-ZIP lokal (`npm run build`) | ✅ | Ausgabe: `dist/delete-disable-comments.zip` (v1.0.5) |
| Deploy jobspot.at (SSH) | ✅ | 2026-05-21 – v1.0.3 → v1.0.4, `Tested up to: 7.0` |
| WordPress.org `readme.txt` überarbeitet | ✅ | Kurzbeschreibung, FAQ, Changelog 1.0.2/1.0.3, Tools-Menüpfad |
| WordPress.org Assets (Banner, Icon, Screenshots) | ✅ | `wordpress-org/assets/`, Generator: `scripts/generate-wordpress-org-assets.py` |
| SVN-/Asset-Doku | ✅ | `wordpress-org/README.md`, Review archiviert unter `documentation/archive/` |
| SVN-Zugangsdaten lokal | ✅ | `.local/wordpress-org-svn.credentials` (gitignored, chmod 600) |

## In Arbeit / Geplant

| Bereich | Priorität | Beschreibung |
|--------|-----------|--------------|
| SVN-Release 1.0.5 auf WordPress.org | Hoch | `trunk` + Tag `1.0.5` + `/assets` hochladen (siehe `wordpress-org/README.md`) |
| Plugin Check jobspot.at erneut prüfen | Hoch | Nach Deploy v1.0.5 im WP-Admin → Werkzeuge → Plugin Check |
| EU-Locale-Übersetzungen (22 Sprachen) | Hoch | Via Codex-Prompt (Inhalt im Chat/Archiv), dann msgfmt + .mo kompilieren |
| GlotPress de_DE readme-Übersetzung | Mittel | Plugin-Beschreibung auf wordpress.org via translate.wordpress.org (Task 4 im Codex-Prompt) |
| PHPUnit / wp-env | Mittel | Smoke-Tests in offizielle WP-Testumgebung portieren |
| Playwright E2E (optional) | Niedrig | Ersetzt Cypress, falls wieder Browser-Tests gewünscht |
| `uninstall.php` | Niedrig | Optionen beim Deinstallieren aufräumen |
| Batch-Close für sehr große DBs | Niedrig | Chunked UPDATEs für Wartungs-Button bei Millionen Posts |
| Live-Screenshots statt Mockups | Niedrig | Optional echte Admin-Screenshots für WordPress.org nach lokalem Test |
| DE-Screenshots für wordpress.org | Niedrig | Optional zusätzliche Screenshots mit deutscher UI |

## Bekannte Einschränkungen

- Kein Multisite-Support (bewusst out of scope).
- **Alle Kommentare jetzt schließen** kann auf sehr großen Installationen weiterhin dauern (ein SQL-`UPDATE`, aber ohne `save_post`-Hooks).
- Plugin-Deaktivierung löscht die Option `ddwpc_disable_comments` (Reaktivierung setzt Standard zurück).

## Testen

```bash
npm test
# oder
php tests/php/test-bug-fix.php
npm run build   # ZIP nach dist/
```
