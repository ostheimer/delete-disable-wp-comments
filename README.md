# Delete & Disable Comments

A WordPress plugin that helps site administrators manage comments by deleting spam comments, removing all comments with backup, or disabling comments site-wide.

## Description

This plugin provides a simple way to:
- Delete all spam comments
- Create a backup of existing comments
- Delete all comments
- Disable comments site-wide

## Installation

1. Upload the plugin files to `/wp-content/plugins/delete-disable-comments`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Tools->Delete & Disable Comments screen to manage comments

## Frequently Asked Questions

### Is this action reversible?

Once comments are deleted, they cannot be recovered unless you have created a backup first.

## Screenshots

1. The main plugin interface under Tools->Delete & Disable Comments

## Changelog

### 1.0.5

* Vollständige deutsche Übersetzungen (`de_AT`, `de_DE`) für Admin-Oberfläche, AJAX-Meldungen und Wartungshinweise (52 Strings).
* Übersetzungsvorlage (`.pot`) aktualisiert; hardcodierter JS-String „Error loading status“ durch lokalisierte Zeichenkette ersetzt.

### 1.0.4

* Plugin-Check-Konformität: `Tested up to` 7.0, sanitisierte Toggle-AJAX-Eingabe, dokumentierte beabsichtigte Bulk-SQL-Abfragen.
* Versions-Bump (Patch) für WordPress.org-Einreichung.

### 1.0.3

* **Behebt** hängenden Toggle „Kommentare deaktivieren“: Der AJAX-Handler führt kein synchrones Bulk-`UPDATE` auf `wp_posts` mehr aus (verhindert Timeouts und Tabellen-Locks auf großen Sites).
* Bulk-Schließen bestehender Beiträge erfolgt nur noch über **„Alle Kommentare jetzt schließen“** (oder beim Plugin-Aktivieren).
* Cypress E2E-Tests aus dem Repository entfernt; PHP-Smoke-Tests unter `tests/php/` bleiben die Testbasis.

### 1.0.2 (kritischer Bugfix)

* **Behebt** einen fatalen Fehler / White-Screen-Of-Death, der auftrat, wenn die Option "Disable comments site-wide" aktiv war und ein Plugin wie WPML, Yoast SEO oder Polylang auf den `save_post`-Hook hörte. Siehe [Issue #1](https://github.com/ostheimer/delete-disable-wp-comments/issues/1).
* `ddwpc_init()` ruft `wp_update_post()` nicht mehr auf jedem Request auf. Stattdessen läuft die Bulk-Schließen-Logik nur noch bei Plugin-Aktivierung, beim expliziten Toggle "Disable comments site-wide" und über einen neuen manuellen Button **"Close all comments now"** auf der Einstellungsseite.
* Der Bulk-Close läuft jetzt als einzelne, hook-freie SQL-`UPDATE`-Anweisung. Damit ist der Vorgang kompatibel mit allen Plugins, die `save_post` / `transition_post_status` / `wp_after_insert_post` abonnieren.
* Die Standardeinstellungen `default_comment_status` und `default_ping_status` werden nur noch einmalig geschrieben, nicht bei jedem Aufruf.
* Neu auf der Settings-Seite: Anzeige der Anzahl Posts, die in der DB noch offene Kommentare haben, plus Button zum sofortigen Schließen.
* Neue interne Helper-Funktionen: `ddwpc_is_disable_comments_enabled()`, `ddwpc_apply_disable_comments_defaults()`, `ddwpc_close_all_post_comments_in_db()`, `ddwpc_count_posts_with_open_comments()`, `ddwpc_invalidate_posts_cache()`.

### 1.0.1
* Plugin-Präfixe von `ddc_` auf `ddwpc_` umgestellt (PHP & JS).
* Manuellen `load_plugin_textdomain()`-Aufruf entfernt (wird seit WP 4.6 automatisch geladen).
* Direkte Core-File-Includes (`require_once wp-load.php`) entfernt.
* Backup-Verzeichnis: CSV-Backups liegen jetzt in `wp-content/uploads/delete-disable-comments`.

### 1.0.0
* Initial release

## Requirements

* WordPress 5.0 or higher
* PHP 7.2 or higher

## License

This plugin is licensed under the GPL v2 or later.

* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.

## Tested up to

* WordPress 7.0

## Stable tag

* 1.0.5

## Übersetzungen (DE/EN)

- **Plugin-UI:** Englische Quellstrings im Code (WordPress-Standard); mitgeliefert werden kompilierte `.mo`-Dateien für `de_AT` und `de_DE`. WordPress wählt die Sprache automatisch anhand der Site-Locale.
- **WordPress.org-Listing:** Die primäre `readme.txt` im Plugin-Verzeichnis muss Englisch sein. Deutsche Plugin-Seitenbeschreibung ist über [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/delete-disable-comments) (GlotPress) möglich — siehe [wordpress-org/README.md](wordpress-org/README.md).

## 🌟 Features

See the [Plugin README](wp-content/plugins/delete-disable-comments/README.md) for detailed feature information.

## 📋 Technical Requirements

### WordPress Environment
- WordPress: 5.0 or higher
- PHP: 7.4 or higher
- MySQL: 5.6 or higher
- Apache/Nginx with mod_rewrite

### Development Environment
- Node.js 16 or higher
- npm 8 or higher
- Docker and Docker Compose
- Composer (for PHP dependencies)
- Git 2.25 or higher

### Browser Support
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

## 📁 Project Structure

```
.
├── wp-content/plugins/delete-disable-comments/  # Plugin source code
│   ├── admin/                # Admin interface files
│   │   └── admin-page.php   # Main admin interface implementation
│   ├── css/                 # Stylesheet files
│   │   └── admin-style.css  # Admin interface styling
│   ├── includes/            # Core functionality files
│   │   ├── functions.php    # Core plugin functions
│   │   └── check-languages.php  # Language support functions
│   ├── js/                  # JavaScript files
│   │   └── admin-script.js  # Admin interface interactions
│   ├── languages/           # Translation files
│   │   ├── *.po            # Translation source files
│   │   ├── *.mo            # Compiled translation files
│   │   └── *.pot           # Translation template
│   ├── templates/           # Template files
│   │   └── blank.php       # Empty comments template
│   ├── README.md           # Plugin documentation
│   └── delete-disable-comments.php  # Main plugin file
│
├── tests/                   # PHP smoke tests
│   └── php/
│       └── test-bug-fix.php               # Standalone PHP smoke test (run via npm test)
│
├── documentation/          # Zusätzliche Dokumentation & Archiv
├── wordpress-org/          # Banner, Icon, Screenshots für wordpress.org
│   └── assets/
├── scripts/                # Hilfsskripte (Asset-Generator)
├── dist/                   # Build-Ausgabe (gitignored): delete-disable-comments.zip
├── node_modules/           # npm dependencies (ignored)
│
├── .gitignore             # Git ignore rules
├── README.md              # Repository documentation
├── docker-compose.yml     # Docker environment setup
├── package.json           # Project dependencies and scripts
└── package-lock.json      # Locked dependencies versions
```

### Key Files and Directories

- **Plugin Files** (`wp-content/plugins/delete-disable-comments/`):
  - `delete-disable-comments.php`: Main plugin file with core setup, hooks, and filters
  - `admin/`: WordPress admin interface implementation
    - `admin-page.php`: Implements the plugin's admin panel with all controls
  - `includes/`: Core plugin functionality and helpers
    - `functions.php`: Contains all core functions for comment management
    - `check-languages.php`: Handles language file loading and checks
  - `languages/`: Translation files for multiple languages
    - `.po` files: Source translation files (editable)
    - `.mo` files: Compiled translation files (binary)
    - `.pot` file: Translation template
  - `templates/`: Template files for frontend rendering
    - `blank.php`: Empty template for disabled comments
  - `css/` & `js/`: Asset files for styling and interactions
    - `admin-style.css`: Styles for the admin interface
    - `admin-script.js`: JavaScript for admin panel interactions

- **Development Files**:
  - `tests/php/test-bug-fix.php`: Framework-free PHP smoke test (no `wp_update_post()` from `init`, fast toggle AJAX, idempotent option writes, gated `COUNT(*)`). Run with `npm test` or `php tests/php/test-bug-fix.php` — exit code 0 on success.
  - `documentation/`: Additional development documentation
  - `docker-compose.yml`: Docker environment configuration with WordPress, MySQL, and PHPMyAdmin
  - `package.json`: npm dependencies and development scripts

- **Configuration Files**:
  - `.gitignore`: Specifies which files Git should ignore

## 🔄 Development Workflow

### Branch Strategy
```
main              # Production-ready code
├── develop       # Development branch
│   ├── feature/* # New features
│   ├── bugfix/*  # Bug fixes
│   └── test/*    # Test implementations
└── release/*     # Release preparation
```

## Build (Test-ZIP)

Lokales Installationspaket für Tests:

```bash
npm run build
```

Ergebnis: `dist/delete-disable-comments.zip` (Version **1.0.5**, enthält `readme.txt` und Plugin-Quellcode).

Installation: **Plugins → Installieren → Plugin hochladen** und ZIP auswählen.

## WordPress.org Verzeichnis

- Plugin-Seite: https://wordpress.org/plugins/delete-disable-comments/
- `readme.txt` (WordPress.org-Format): `wp-content/plugins/delete-disable-comments/readme.txt`
- Banner, Icon, Screenshots: `wordpress-org/assets/`
- SVN-Anleitung: [wordpress-org/README.md](wordpress-org/README.md)

Assets neu erzeugen:

```bash
.venv-assets/bin/python scripts/generate-wordpress-org-assets.py
```

Historisches Review-Feedback: [documentation/archive/wordpress-org-review-feedback.md](documentation/archive/wordpress-org-review-feedback.md)

## Roadmap

Siehe [ROADMAP.md](ROADMAP.md) für aktuellen Projektstand.
