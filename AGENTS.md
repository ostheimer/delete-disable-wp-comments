# Delete & Disable Comments

WordPress-Plugin (Slug `delete-disable-comments`, Funktionspräfix `ddwpc_`): zeigt Kommentarzahlen,
löscht ausgewählte Kommentartypen in fortsetzbaren Schritten, exportiert CSV und deaktiviert
öffentliche Kommentare, ohne Editor-Notizen zu verlieren. Veröffentlicht auf
https://wordpress.org/plugins/delete-disable-comments/ (Autor-Konto `helpstring`).

## Aufbau

- Der Plugin-Quellcode liegt in `wp-content/plugins/delete-disable-comments/` (Haupt-Datei,
  `admin/`, `includes/`, `languages/`, `readme.txt`). Nur dieser Ordner kommt ins Release-ZIP
  (`scripts/build-release.sh` packt `git ls-files` dieses Ordners).
- `wp-content/plugins/classic-editor/` und `wp-content/plugins/plugin-check/` sind mitversionierte
  Fremd-Plugins (rund 2.200 Dateien, mit eigener Lizenz) und nicht Teil des Release-ZIP.
- `wordpress-org/` enthält Banner, Icon, Screenshots (SVN-Ordner `/assets`), die deutsche
  `readme-de_DE.txt` und die SVN-Anleitung (`wordpress-org/README.md`).
- UI-Quellstrings sind englisch, mitgeliefert werden kompilierte `.mo`-Dateien (u. a. `de_AT`,
  `de_DE`). Die Projektdoku ist deutsch. `docker-compose.yml` steht in der `.gitignore`.

## Befehle

- `npm test` führt `php tests/php/test-bug-fix.php` aus (eigenständiges PHP-Skript, keine
  WordPress-Installation).
- `npm run verify:release` prüft, dass Version in `package.json`, Plugin-Header und
  `Stable tag` in `readme.txt` übereinstimmen, baut `dist/delete-disable-comments.zip` zweimal
  und verlangt identische SHA-256-Summen. Die Konstante `DDWPC_VERSION` wird nicht geprüft und muss
  von Hand mitgezogen werden.

## CI und Release

- `ci.yml` läuft bei jedem Pull Request und jedem Push auf `main` ohne Pfadfilter, also auch
  bei reinen Markdown-Änderungen: PHP-Lint, `npm test`, ShellCheck, `verify:release`,
  WordPress Plugin Check (`WordPress/plugin-check-action`), Upload des ZIP als Artefakt.
- `release.yml` startet nur bei einem Tag `v*` (nicht bei Push auf `main`): Tests, Verifikation
  gegen die Tag-Version, dann `gh release create` mit ZIP und SHA-256-Datei. Ein Merge auf
  `main` veröffentlicht nichts.
- Nach wordpress.org gelangt nichts automatisch. Dort wird von Hand per SVN veröffentlicht
  (Ablauf: `wordpress-org/README.md`: `trunk/` synchronisieren und committen, dann `tags/<version>`;
  `Stable tag` in `trunk/readme.txt` muss zum Tag passen). Die SVN-Zugangsdaten liegen nur lokal in
  `.local/wordpress-org-svn.credentials` (gitignored).

## Fallen

- Löschen ist endgültig. Der CSV-Export enthält keine Kommentar-Metadaten und ist kein
  wiederherstellbares Backup; vor einer Löschung braucht es ein Datenbank-Backup über den Hoster.
- Standardmäßig bleiben Editor-Notizen, Produktbewertungen und benutzerdefinierte Kommentartypen
  von der Löschung ausgenommen.
- Das Schließen bestehender Beiträge läuft als ein hook-freies SQL-`UPDATE` (nur über „Alle
  Kommentare jetzt schließen“ oder bei Aktivierung), nie bei jedem Request. Ein `wp_update_post()`
  aus `init` hat zuvor Fatal Errors mit WPML, Yoast und Polylang verursacht (Issue 1).
- Kein Multisite-Support (bewusst).

## Weiterführend

- `ROADMAP.md` (Stand und offene Punkte), `README.md` (Changelog, Build, Verzeichnis),
  `documentation/archive/wordpress-org-review-feedback.md` (Review-Feedback von wordpress.org).
