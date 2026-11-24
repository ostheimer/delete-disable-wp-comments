# Delete & Disable Comments: WordPress.org-Vermarktung und Wettbewerbsvergleich

Stand: 24. November 2026. Datenquelle: öffentliche WordPress.org-API (Plugin-Infos, Suche, Sprachpakete, Downloads), aktueller Stand `origin/main` (Version 1.1.0) und die Assets im Repository.

## Kurzfassung

Das Plugin wird auf WordPress.org kaum gefunden: Bei allen relevanten Suchbegriffen steht es erst ab Platz 11. Name, Schlagworte und Kurzbeschreibung enthalten die gesuchten Begriffe nur teilweise, und die Beschreibung erklärt Technik statt Nutzen.

Funktional ist der Marktführer (Disable Comments von WPDeveloper, 1 Mio.+ Installationen) beim **Abschalten** klar breiter aufgestellt und hat seit September 2026 auch eine Vorschau mit CSV-Download vor dem Löschen. Ein reiner Wettbewerb ums Abschalten ist nicht zu gewinnen. Die eigene Stärke liegt beim **sicheren Löschen großer Mengen**: Bereinigung in Etappen mit Fortschritt, geschützte Bewertungen und Notizen, abgesicherter CSV-Export, kostenlos ohne Pro-Version und ohne Datenübermittlung. Diese Positionierung sollte die Plugin-Seite in den Vordergrund stellen.

**Die fünf wirksamsten Maßnahmen, alle ohne Kosten:**

1. **Name, Schlagworte und Kurzbeschreibung auf Löschbegriffe ausrichten** (Vorschläge unten).
2. **Beschreibung auf Nutzen umbauen**: große Mengen sicher löschen, Praxisbeleg aus der Bewertung, „100 % kostenlos“; Warnhinweise und Technik nach unten.
3. **„Live Preview“ mit Testkommentaren** anbieten. Keiner der Konkurrenten hat das.
4. **Deutsche Übersetzung auf translate.wordpress.org freigeben lassen**: derzeit 0 Sprachpakete, Marktführer 44.
5. **Mehr Bewertungen gewinnen**: 1 Bewertung gegenüber 13 bis 282 bei der Konkurrenz.

Größte Funktionslücken: **Abschalten nur für bestimmte Inhaltstypen** (z. B. nur Seiten) und **Löschen nach Status, Inhaltstyp oder Datum**.

## 1. Ausgangslage im Vergleich

| Plugin | Aktive Installationen | Bewertung (Anzahl) | Sprachpakete | Screenshots | Modell |
| --- | ---: | --- | ---: | ---: | --- |
| **Delete & Disable Comments** (unser Plugin) | 70+ | 5,0 (1) | 0 | 4 | kostenlos |
| Disable Comments (WPDeveloper) | 1 Mio.+ | 4,7 (282) | 44 | 3 | kostenlos, Werbung für eigene Produkte |
| Disable Comments RB | 100.000+ | 3,8 (25) | 16 | 6 | Free + Pro |
| WP Bulk Delete (Xylus) | 90.000+ | 4,6 (131) | 6 | 10 | Free + Pro |
| WP Comment Cleaner | 20.000+ | 4,6 (59) | 0 | 5 | Free + Premium |
| Disable Comments & Delete All Comments | 9.000+ | 5,0 (41) | 1 | 2 | kostenlos |
| Bulk Delete Comments | 5.000+ | 3,8 (13) | 0 | 1 | kostenlos |

Entwicklung unseres Plugins: aktive Installationen von 20 (24.09.) auf 70 (24.11.) gestiegen, rund 6 Downloads pro Tag. Die einzige Bewertung (17.11.2026, 5 Sterne) beschreibt das Löschen von 32.509 Kommentaren bei nur 128 MB PHP-Speicher und lobt Fortschrittsanzeige und deutsche Oberfläche. Das ist der stärkste Verkaufsbeleg, den das Plugin derzeit hat, und er bestätigt die empfohlene Positionierung.

### Suchposition auf WordPress.org (API-Suche, kann leicht von der Website abweichen)

| Suchbegriff | Platz | Wer oben steht |
| --- | ---: | --- |
| disable comments | 11 | Disable Comments (WPDeveloper), Disable Comments RB |
| delete comments | 12 | Disable Comments, Bulk Delete Comments, WP Comment Cleaner |
| bulk delete comments | 13 | |
| delete spam comments | 17 | |
| comment cleanup | 20 | |
| delete all comments | 21 | WP Comment Cleaner, Disable Comments & Delete All Comments |
| remove comments | 31 | |
| spam comments | 49 | Anti-Spam-Plugins (anderer Bedarf, nicht lohnend) |

Praktisch heißt das: Wer nach einer Lösung sucht, sieht unser Plugin in der Regel nicht. Konkurrenten wie „Disable Comments & Delete All Comments“ (9.000+) oder „Bulk Delete Comments“ (5.000+) stehen weit vorne, weil sie die Suchbegriffe wörtlich im Namen tragen.

## 2. Verbesserungen auf der Plugin-Seite

Die Plugin-Seite muss auf Englisch bleiben. Die Textvorschläge sind daher englisch.

### 2.1 Name mit Zusatz (hoher Hebel)

WordPress.org gewichtet den Namen in der Suche am stärksten. Die gut platzierten kleineren Konkurrenten tragen die Suchbegriffe im Namen („WP Comment Cleaner – Delete All Comments, Disable Comments, Bulk Delete & Remove Comments“).

Vorschlag (59 Zeichen, die Adresse `delete-disable-comments` bleibt gleich):

```
Delete & Disable Comments – Bulk Delete Spam & All Comments
```

Risiko: Das Plugin-Team von WordPress.org lehnt reine Schlagwort-Ketten im Namen ab. Ein einzelner beschreibender Zusatz wie oben ist üblich und vertretbar; längere Ketten wie beim Konkurrenten würde ich nicht übernehmen. Name im Readme und im Plugin-Kopf gleich halten.

### 2.2 Schlagworte (hoher Hebel)

Heute: `comments, spam, disable comments, delete comments, cleanup`. „comments“ und „spam“ sind allgemein und von Anti-Spam-Plugins mit Millionen Installationen belegt, „cleanup“ wird selten gesucht. Nur die ersten fünf Schlagworte zählen.

Vorschlag:

```
Tags: delete comments, disable comments, delete all comments, remove comments, delete spam comments
```

### 2.3 Kurzbeschreibung (hoher Hebel)

Heute: „Review comment counts, clean up selected types in resumable batches, export CSV, or disable public comments from one admin screen.“ Das beschreibt Bedienung statt Nutzen und enthält „delete all comments“ und „spam“ nicht.

Vorschlag (129 von 150 Zeichen):

```
Bulk delete spam or all comments in safe, resumable batches. Export to CSV first or disable comments site-wide. Free, no upsells.
```

### 2.4 Beschreibung umbauen (mittlerer Hebel)

Heute beginnt die Beschreibung sachlich-technisch, enthält viele Einschränkungen („not a restorable backup“, „permanent“, `save_post`, REST, `manage_options`) und nennt die stärksten Argumente nicht. Vorschlag für die Reihenfolge:

1. **Ein Satz Problem und Lösung** mit den Suchbegriffen: „Delete all comments, bulk delete spam, or disable comments on your whole WordPress site – safely, even with tens of thousands of comments.“
2. **Vorteile als Liste**, jeweils mit Nutzen:
   - Works on large sites and cheap hosting: deletes in small batches with a progress bar, so it does not time out.
   - Protects what you want to keep: WooCommerce product reviews and block editor Notes stay untouched unless you select them.
   - See exact counts before you delete.
   - Free CSV export of all comments before deleting, protected against spreadsheet formula injection.
   - Safe with WPML, Polylang and Yoast SEO: closing comments does not trigger post updates.
   - 100 % free: no Pro version, no ads, no account, no tracking.
3. **Praxisbeleg** ohne Namen: „A user removed 32,509 comments on a host with a 128 MB memory limit without errors.“
4. **Ein einziger Abschnitt „Before you delete“** mit den Warnhinweisen (Löschen ist endgültig, CSV ist kein Backup).
5. **Technische Details** (REST, `save_post`, Berechtigungen) in die FAQ oder einen Abschnitt „For developers“ verschieben.
6. Den Satz „Other included locale files may use English for newer messages“ streichen, sobald die Sprachdateien bereinigt sind. Er wirkt unfertig.

### 2.5 FAQ auf Suchanfragen ausrichten (mittlerer Hebel)

FAQ-Texte werden von WordPress.org und Google gefunden. Fragen so formulieren, wie Nutzer suchen:

- How do I delete all comments in WordPress?
- How do I delete only spam comments?
- How do I disable comments on my whole site?
- Will it delete WooCommerce product reviews?
- Can it handle 10,000+ comments on shared hosting?
- Do I need to keep the plugin active after disabling comments?

### 2.6 Screenshots ergänzen (mittlerer Hebel)

Die vier Screenshots zeigen die echte Oberfläche und sind sauber. Es fehlt genau das Merkmal, das in der Bewertung gelobt wird:

- **Bereinigung läuft, mit Fortschrittsanzeige und Zahlen** (als neuer Screenshot 2).
- **Ergebnis nach dem Löschen** mit Testdaten.
- Optional: deutsche Oberfläche als letzter Screenshot.
- Bildunterschriften mit Suchbegriffen formulieren, z. B. „Bulk delete all comments in batches with live progress“.

Banner und Icon im Retro-Stil sind eigenständig und gut wiedererkennbar. Kein Handlungsbedarf.

### 2.7 Live Preview im Browser (mittlerer Hebel, Alleinstellung)

WordPress.org zeigt einen Button „Live Preview“, wenn im SVN-Ordner `assets/blueprints/blueprint.json` liegt und die Vorschau in den Plugin-Einstellungen aktiviert ist. Besucher starten dann eine Test-WordPress-Seite im Browser (WordPress Playground). Ein Blueprint kann einige hundert Testkommentare inklusive Spam anlegen, sodass man das Löschen gefahrlos ausprobieren kann. Laut API hat **keines** der sieben verglichenen Plugins eine Live Preview. Vor Umsetzung die aktuellen Bedingungen im Plugin-Handbuch prüfen.

### 2.8 Übersetzung freigeben (mittlerer Hebel)

translate.wordpress.org meldet **0 Sprachpakete**. Die deutschen Vorschläge vom 24.09. wurden zurückgezogen (Du-Form, Glossar). Folge: Die deutsche Verzeichnisseite zeigt Englisch, und WordPress liefert keine automatischen Übersetzungs-Updates.

Schritte: deutsche Texte in Du-Form nach Glossar und Style Guide vorschlagen, dann als Plugin-Autor beim deutschen Polyglots-Team die Prüfung anfragen oder die Rolle „Project Translation Editor“ für das eigene Plugin beantragen. Die Plugin-Seite listet danach Deutsch als verfügbare Sprache.

### 2.9 Bewertungen gewinnen (hoher Hebel, langfristig)

- **Dezenter Hinweis im Plugin** nach einer erfolgreichen Bereinigung: einmalig, wegklickbar, mit Link zur Bewertungsseite. WordPress.org erlaubt das, solange es nicht drängt und nicht wiederkehrt. Der Marktführer nutzt ebenfalls einen Bewertungshinweis.
- Eigene Kunden mit Bereinigungsprojekten persönlich um eine Bewertung bitten.
- Support-Fragen schnell beantworten. Die Quote gelöster Fragen ist auf der Seite sichtbar.

### 2.10 Kleinere Punkte

- **Regelmäßige Updates**: letztes Update 23.09.2026. Die Konkurrenz aktualisiert monatlich oder öfter. „Last updated“ und „Tested up to“ wirken auf Besucher und in der Suche.
- **Spendenlink** fehlt. Für die Vermarktung unerheblich.
- **Leistungsseite auf ostheimer.at** ist verlinkt und erreichbar. Ein Blogartikel „How to delete all comments in WordPress“ würde zusätzlich Google-Besucher bringen.

## 3. Funktionsvergleich

Legende: ✅ vorhanden · 💲 nur in Bezahlversion · ◐ teilweise · – in der Beschreibung nicht genannt. Angaben zu Konkurrenten laut deren Beschreibung und Änderungsprotokoll, nicht selbst installiert und getestet.

| Funktion | **Unser Plugin** | Disable Comments (WPDeveloper) | Disable Comments RB | WP Comment Cleaner | Disable & Delete All (comments-plus) | WP Bulk Delete |
| --- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Abschalten** | | | | | | |
| Kommentare auf der ganzen Seite abschalten | ✅ | ✅ | ✅ | ✅ | ✅ | – |
| Abschalten nur für bestimmte Inhaltstypen | – | ✅ | ✅ | ✅ | ✅ | – |
| Abschalten nach Kategorie, Vorlage oder Beitragsalter | – | ✅ | – | – | – | – |
| Kommentar-Menüs, Widgets, Feeds ausblenden | ✅ | ✅ | – | – | ✅ | – |
| Spam über REST/XML-RPC blockieren | ◐ ¹ | ✅ | ✅ | – | – | – |
| Wiedereinschalten stellt alten Zustand vollständig her | ◐ ² | ✅ | – | – | – | – |
| Ausnahmen für Benutzerrollen | – | ✅ | 💲 | 💲 | – | – |
| Multisite | – | ✅ | 💲 | – | – | – |
| **Löschen** | | | | | | |
| Spam löschen | ✅ | – | ✅ | ✅ | ✅ | ✅ |
| Alle Kommentare löschen | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Löschen nach Typ (Bewertungen, Notizen, eigene Typen) | ✅ | ✅ | 💲 | – | – | ◐ |
| Löschen nach Status (unfreigegeben, Papierkorb) | – | – | ✅ | ✅ | ✅ | ✅ |
| Löschen nach Inhaltstyp, Beitrag oder Datum | – | ◐ | ✅ | ✅ | ✅ | ✅ |
| Anzahl bzw. Vorschau vor dem Löschen | ✅ | ✅ | ✅ | – | – | – |
| Löschen in Etappen mit Fortschritt (große Seiten) | ✅ | – | – | – | – | ✅ |
| Bewertungen und Editor-Notizen standardmäßig geschützt | ✅ | ◐ ³ | – | – | – | – |
| CSV-Export vor dem Löschen | ✅ | ✅ | – | 💲 | – | 💲 |
| CSV gegen Formel-Angriffe abgesichert | ✅ | – | – | – | – | – |
| Geplante automatische Spam-Bereinigung | – | – | – | 💲 | – | ◐ |
| **Sonstiges** | | | | | | |
| WP-CLI | – | ✅ | 💲 | – | – | – |
| Einrichtungsassistent | – | ✅ | – | – | – | – |
| KI-Schnittstelle (Abilities API / MCP) | – | ✅ | – | – | – | – |
| Deutsche Oberfläche | ◐ ⁴ | ✅ | ✅ | – | – | – |
| Keine Datenübermittlung an den Hersteller | ✅ | ◐ ⁵ | ✅ | ✅ | – | – |
| Völlig kostenlos ohne Bezahlversion | ✅ | ✅ | – | – | ✅ | – |

¹ Neue Kommentare über REST werden abgewiesen; XML-RPC ist indirekt gesperrt, weil Kommentare geschlossen sind; der X-Pingback-Header bleibt. Nicht ausdrücklich beworben.
² Die Diskussions-Standardwerte werden wiederhergestellt; Beiträge, die per „Close all comments now“ geschlossen wurden, bleiben geschlossen. WPDeveloper ändert die Beiträge gar nicht, sondern filtert nur zur Laufzeit.
³ Editor-Notizen bleiben beim Abschalten erhalten; ein Standardschutz beim Löschen ist nicht beschrieben.
⁴ Im Plugin mitgeliefert, aber nicht über translate.wordpress.org freigegeben.
⁵ Freiwillige Nutzungsstatistik (standardmäßig aus); bei Zustimmung werden u. a. Plugin-Liste und Admin-E-Mail übertragen, verbunden mit einem Rabattgutschein.

### Wo das Plugin vorne liegt

- **Große Mengen sicher löschen**: Etappen mit Fortschritt, Zählung vorher, geschützte Bewertungen und Notizen, abgesicherter CSV-Export. Diese Kombination hat kein Konkurrent. Nur WP Bulk Delete arbeitet ebenfalls in Etappen, ist aber ein allgemeines Löschwerkzeug für Beiträge, Benutzer und Daten.
- **Vertrauen**: völlig kostenlos, keine Pro-Version, keine Werbung, keine Datenübermittlung. Bei den Konkurrenten fehlt jeweils mindestens einer dieser Punkte.
- **Mehrsprachige Seiten**: Schließen ohne Nebenwirkungen bei WPML, Polylang und Yoast (der Marktführer erreicht das auf anderem Weg ebenfalls).

### Wo das Plugin zurückliegt

| Lücke | Wer es hat | Bedeutung für die Vermarktung |
| --- | --- | --- |
| Abschalten nur für bestimmte Inhaltstypen | alle Abschalt-Konkurrenten | **hoch**: häufiger Wunsch („nur auf Seiten aus, im Blog an“), heute ein Ausschlussgrund |
| Löschen nach Status, Inhaltstyp, Datum | RB, Comment Cleaner, comments-plus, WP Bulk Delete | **hoch**: z. B. „nur unfreigegebene löschen“ oder „alles vor 2020“; passt zur Löschpositionierung |
| WP-CLI | WPDeveloper (gratis), RB (Pro) | mittel: wichtig für Agenturen und große Bereinigungen |
| Ausnahmen für Benutzerrollen | WPDeveloper (gratis), RB und Comment Cleaner (Pro) | mittel |
| Multisite | WPDeveloper (gratis), RB (Pro) | mittel; bisher bewusst ausgeschlossen, Geschäftsentscheidung |
| Geplante Spam-Bereinigung | Comment Cleaner (Premium) | niedrig bis mittel |
| Feinere Abschaltregeln, KI-Schnittstelle | WPDeveloper | niedrig; beim Abschalten ist der Marktführer kaum einzuholen |

## 4. Empfohlene Reihenfolge

1. **Sofort, nur Text**: Name-Zusatz, Schlagworte, Kurzbeschreibung, Beschreibung, FAQ, Bildunterschriften. Readme-Änderung über SVN, kein neuer Plugin-Code nötig.
2. **Kurzfristig**: Screenshot mit Fortschrittsanzeige, Live Preview mit Testkommentaren, deutsche Übersetzung über translate.wordpress.org.
3. **Mit dem nächsten Release**: einmaliger Bewertungshinweis nach erfolgreicher Bereinigung.
4. **Produktentscheidung**: Löschfilter nach Status, Inhaltstyp und Datum (stärkt die Löschpositionierung), Abschalten je Inhaltstyp, danach WP-CLI.
5. **Messen**: Suchposition für die acht Begriffe oben, aktive Installationen und Bewertungen 4 und 8 Wochen nach der Textänderung erneut erheben.

## Geprüft und angenommen

- **Geprüft**: Live-Daten aller genannten Plugins über die WordPress.org-API (Version, Installationen, Bewertungen, Schlagworte, Screenshots, Beschreibung, FAQ, Änderungsprotokoll, Live-Preview-Feld), Sprachpakete über die Übersetzungs-API, Download-Statistik der letzten 60 Tage, Suchpositionen für acht Begriffe, aktuelle Banner, Icon und Screenshots aus dem Repository, die Abschaltfunktionen im eigenen Code (`origin/main`).
- **Nicht geprüft**: Die Konkurrenz-Plugins wurden nicht installiert; der Vergleich beruht auf deren Beschreibungen. Die Website-Suche von WordPress.org kann leicht von der API-Suche abweichen. Die Wirkung der Textänderungen ist eine Erwartung, kein Messwert.
- **Hinweis**: Der lokale `main` war 4 Commits hinter GitHub. Die Analyse nutzt den aktuellen GitHub-Stand (1.1.0); die Arbeitskopie wurde nicht verändert, nur dieser Bericht kam als neue Datei hinzu.
