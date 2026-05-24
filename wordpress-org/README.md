# WordPress.org – Plugin-Verzeichnis

Assets und Anleitung für das [Plugin-Verzeichnis auf WordPress.org](https://wordpress.org/plugins/delete-disable-comments/).

## Ordnerstruktur

```
wordpress-org/
├── README.md          # Diese Datei
└── assets/            # Banner, Icon, Screenshots (SVN /assets, nicht im Plugin-Tag)
    ├── banner-772x250.png
    ├── banner-1544x500.png
    ├── icon-128x128.png
    ├── icon-256x256.png
    ├── screenshot-1.png … screenshot-4.png
```

Der Plugin-Quellcode für SVN liegt in `wp-content/plugins/delete-disable-comments/` (inkl. `readme.txt`).

## Assets neu generieren

```bash
python3 -m venv .venv-assets
.venv-assets/bin/pip install Pillow
.venv-assets/bin/python scripts/generate-wordpress-org-assets.py
```


## Lokale SVN-Zugangsdaten

Für Releases können SVN-Benutzername und -Passwort **nur lokal** in `.local/wordpress-org-svn.credentials` abgelegt werden (Datei ist via `.gitignore` ausgeschlossen, Berechtigung `600`). Passwörter gehören nicht ins Git-Repository.

## SVN-Upload (Plugin-Autor)

1. **Checkout** (einmalig, wenn noch nicht vorhanden):

   ```bash
   svn co https://plugins.svn.wordpress.org/delete-disable-comments/
   cd delete-disable-comments
   ```

2. **Plugin-Release (Tag)** – z. B. Version 1.0.3:

   ```bash
   svn cp trunk tags/1.0.3
   # Dateien aus dem Repo in trunk/ synchronisieren (rsync oder manuell)
   svn add --force trunk/*
   svn commit -m "Release 1.0.3"
   ```

3. **Assets** (Banner, Icon, Screenshots) – Ordner `/assets` auf SVN-Root-Ebene, **nicht** im Plugin-Ordner:

   ```bash
   cp ../wordpress-org/assets/* assets/
   svn add assets/*
   svn commit -m "Update plugin directory assets"
   ```

4. **Stable Tag** in `trunk/readme.txt` muss mit dem veröffentlichten Tag übereinstimmen (`Stable tag: 1.0.3`).

5. Nach dem Commit erscheinen Banner und Screenshots auf der Plugin-Seite nach Cache-Aktualisierung (oft innerhalb weniger Minuten, gelegentlich länger).

## Hinweise

- Screenshots und Banner nutzen englische UI-Texte (breitere Reichweite im Verzeichnis).
- Das Plugin selbst ist mehrsprachig mit breiter EU-Locale-Unterstützung; siehe `languages/` und Abschnitt **Mehrsprachigkeit** unten.
- Review-Feedback von WordPress.org ist im Repo unter `documentation/archive/wordpress-org-review-feedback.md` archiviert.

## Mehrsprachigkeit (EU)

### Plugin-Oberfläche (Admin-UI)

| Aspekt | Strategie |
|--------|-----------|
| Quellsprache im Code | Englisch (`msgid` in PHP/JS) — WordPress-i18n-Standard |
| Mitgelieferte Locales | `de_AT`, `de_DE` sowie weitere EU-Locale-Dateien (`.po` + kompilierte `.mo` im Ordner `languages/`) |
| Automatisches Laden | Seit WordPress 4.6 lädt Core die passende `.mo`-Datei anhand der Site-Locale (`WPLANG` / Benutzersprache) |
| Englische Sites | Keine `.mo` nötig — Quellstrings werden direkt angezeigt |
| EU-Sprachen | Vollständig übersetzte Admin-Oberfläche inkl. AJAX-Meldungen und Bestätigungsdialoge, sofern die passende Locale-Datei vorhanden ist |

**Übersetzungen kompilieren** (nach Änderungen an `.po`-Dateien):

```bash
cd wp-content/plugins/delete-disable-comments/languages
for po in delete-disable-comments-*.po; do
  msgfmt -c -o "${po%.po}.mo" "$po"
done
```

Alternativ: `wp i18n make-mo languages/` (WP-CLI, falls installiert).

**Neue Strings extrahieren** (optional):

```bash
wp i18n make-pot wp-content/plugins/delete-disable-comments \
  wp-content/plugins/delete-disable-comments/languages/delete-disable-comments.pot \
  --domain=delete-disable-comments
```

### WordPress.org-Listing (Plugin-Verzeichnis)

| Bereich | DE/EN möglich? | Hinweis |
|---------|----------------|---------|
| `readme.txt` im SVN-`trunk/` | **Primär Englisch (Pflicht)** | WordPress.org erwartet englische Plugin-Beschreibung, FAQ, Changelog |
| Deutsche Plugin-Seite auf wordpress.org | **Ja, über GlotPress** | Autor und Community können unter [translate.wordpress.org – delete-disable-comments](https://translate.wordpress.org/projects/wp-plugins/delete-disable-comments) die `readme`-Strings ins Deutsche übersetzen; eine lokale GlotPress-Vorlage liegt in `wordpress-org/readme-de_DE.txt` |
| Plugin-Name im Verzeichnis | Englisch (offizieller Name) | Lokalisierter Anzeigename nur über GlotPress, falls vorhanden |
| Banner, Icon, Screenshots | Englisch (aktuell) | Englische UI in Screenshots reicht für globale Reichweite; **optionale DE-Screenshots** können ergänzt werden, sind aber nicht zwingend |
| Plugin-ZIP (Release-Tag) | Enthält `.mo`-Dateien | Endnutzer mit deutscher WordPress-Installation sehen die übersetzte Admin-UI automatisch |

**Kurzantwort:** Die Plugin-UI kann über `.po`/`.mo` in mehreren EU-Sprachen ausgeliefert werden. Die wordpress.org-**Listing**-Beschreibung bleibt primär Englisch; lokalisierte Varianten entstehen über translate.wordpress.org, nicht über eine zweite `readme.txt` im Plugin-Tag.
