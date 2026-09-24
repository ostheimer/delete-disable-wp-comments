# Delete & Disable Comments 1.1.0: Übersetzung, Echttest und Messbasis

Stand: 24. September 2026. Die Angaben trennen öffentliche Verzeichnisdaten, den tatsächlichen Übersetzungsstatus, die lokale Testinstallation und noch nicht verfügbare Geschäftszahlen.

## 1. Deutsche WordPress.org-Texte

Am 24.09. wurden 44 Readme- und 37 Admin-Textvorschläge über das WordPress.org-Konto `helpstring` eingereicht. Die anschließende Prüfung des [deutschen Polyglots-Handbuchs](https://de.wordpress.org/team/handbook/polyglots-team/hilf-mit-bei-der-uebersetzung/wie-die-deutsche-community-pte-rechte-vergibt/) zeigte, dass **de/default die Anrede „du“ verwendet**. Mehrere neue Readme-Texte verwendeten dagegen „Sie“. Das Handbuch verlangt außerdem die Beachtung von Glossar und Style Guide und rät von maschinellen Übersetzungen ab. Deshalb wurden sämtliche 81 Einreichungen vom 24.09. noch am selben Tag in GlotPress zurückgezogen. Sie wurden nicht veröffentlicht. Die nicht ausreichend geprüften PO-Entwürfe werden nicht als freigabereife Dateien im Repository geführt.

Nach der Rücknahme zeigte [GlotPress Stable Readme](https://translate.wordpress.org/projects/wp-plugins/delete-disable-comments/stable-readme/de/default/) 86 Einträge insgesamt, 1 freigegebenen, 44 unübersetzte und 41 wartende Einträge. [GlotPress Stable](https://translate.wordpress.org/projects/wp-plugins/delete-disable-comments/stable/de/default/) zeigte 70 insgesamt, 8 freigegebene, 37 unübersetzte und 26 wartende Einträge. Die Angaben stammen aus dem GlotPress-UI; Statuszahlen können sich durch weitere Beiträge ändern. Die mitgelieferte lokale `.mo`-Datei bleibt im Plugin enthalten und ist von der WordPress.org-Freigabe unabhängig. Die [deutsche Verzeichnisseite](https://de.wordpress.org/plugins/delete-disable-comments/) zeigt derzeit weiterhin die englische Kurzbeschreibung.

**Nächster redaktioneller Schritt:** Ein deutschsprachiger Mensch prüft die bestehenden wartenden und fehlenden Strings anhand von [Glossar](https://translate.wordpress.org/languages/de/default/glossary/) und [Style Guide](https://de.wordpress.org/team/handbook/polyglots-team/style-guide/), formuliert die fehlenden Texte für `de/default` in der Du-Form und prüft technische Bedeutung, HTML, Platzhalter und Warnungen. Anschließend können geprüfte Texte in GlotPress vorgeschlagen und die deutsche Polyglots-Community um Prüfung gebeten werden. Die Anfrage im Namen des Plugin-Teams ist noch nicht erfolgt.

## 2. Laufender Funktionstest

Isolierte Docker-Installation: WordPress 7.1, PHP 8.4, WooCommerce 11.1.2 und Plugin 1.1.0. Alle Kommentardaten waren synthetisch; es wurden keine Produktionskommentare verändert.

| Prüfung | Beobachtetes Ergebnis |
| --- | --- |
| Ausgangsbestand und Standardauswahl | 1.201 öffentliche Kommentare (darunter 300 Spam), 10 Produktbewertungen, 10 Editor-Notizen, 10 benutzerdefinierte Kommentare. Nur „öffentlich“ war vorausgewählt. |
| CSV-Export | HTTP-Download mit 1.231 Zeilen aller Typen; formelartiger Testwert wurde mit Apostroph entschärft. Die CSV enthält keine Metadaten und ist kein Wiederherstellungs-Backup. |
| Spam bereinigen | 300 Datensätze in 13 AJAX-Anfragen gelöscht; gemeldeter Rest 0. |
| Standardbereinigung | Weitere 901 öffentliche Kommentare in 10 AJAX-Anfragen gelöscht. Alle 10 Bewertungen, 10 Notizen und 10 benutzerdefinierten Kommentare blieben erhalten. |
| Bewusste Typauswahl | Die 10 benutzerdefinierten Kommentare wurden erst nach ausdrücklicher Auswahl von „andere“ gelöscht. |
| Deaktivieren und Schließen | Der Schalter setzte Standardwerte für Kommentare und Pings auf „closed“. Die separate Wartungsaktion schloss 6 offene Beiträge. Nach dem Ausschalten waren die vorherigen Standardwerte wieder „open“; die 6 Beitragsstatus blieben geschlossen. |
| REST bei deaktivierten Kommentaren | Eine Editor-Notiz erhielt HTTP 201 und blieb gespeichert; ein gewöhnlicher Kommentar wurde mit HTTP 403 abgewiesen. WooCommerce-Produktkommentare waren geschlossen. |
| Unangemeldeter AJAX-Zugriff | HTTP 400, Antwort `0`; keine Zähldaten. |

Die laufende WordPress-Installation und ihre HTTP-Endpunkte wurden geprüft. Eine visuelle Abnahme der Admin-Ansicht im Browser fand in diesem Lauf nicht statt.

## 3. 30-Tage-Messung

Vergleichsfenster: 24. September bis 24. Oktober 2026. Die [WordPress.org-API](https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request%5Bslug%5D=delete-disable-comments&request%5Bfields%5D%5Bdownloaded%5D=1) meldete am 24.09. um 14:38 Uhr MESZ Version 1.1.0, 20 aktive Installationen, 769 kumulierte Downloads und 0 Bewertungen. Am 23.09. waren es 20, 745 und 0. Die Differenz von 24 Downloads kann Updates enthalten und belegt keine zusätzlichen Installationen.

| Kennzahl am 24.10. | Quelle und Auswertung |
| --- | --- |
| Aktive Installationen | WordPress.org-API; Anzeige ist grob gerundet. Veränderung als Bereich/Trend beschreiben. |
| Downloads | Kumulierte API-Zahl minus 769; getrennt von Installationen ausweisen. |
| Bewertungen und Support | WordPress.org-Bewertungen und Support-Forum; konkrete Probleme und Wünsche nach Typ ordnen. |
| Besuche der Leistungsseite | Vercel Web Analytics, Pfad `/leistungen/wordpress-plugins/delete-disable-comments`; Besucher und Seitenaufrufe für das gleiche Zeitfenster. Die Website bindet Analytics ein. Ein Dashboardwert war heute wegen erneuter Anmeldung mit 2FA nicht abrufbar und bleibt **unbekannt**. |
| Qualifizierte Anfragen | Postfach `office@ostheimer.at`: E-Mails mit dem eindeutigen Betreff „Anfrage zu Delete & Disable Comments“ sowie manuell erkannte Anfragen, die das Plugin ausdrücklich nennen. Nur echte Bereinigungs-, Migrations- oder Wartungsbedarfe zählen; interne Tests, Spam und allgemeine WordPress-Fragen nicht. Die Suche nach „Delete & Disable Comments“ ab 23.09. ergab heute 0 Treffer. Personenbezogene Inhalte gehören nicht in diesen Bericht. |

Die Leistungsseite erhält einen E-Mail-Link mit eindeutigem Betreff, damit künftige Anfragen zugeordnet werden können. Ein Klick auf einen E-Mail-Link ist noch keine gesendete oder qualifizierte Anfrage; bei Telefonkontakten ohne Plugin-Nennung bleibt die Herkunft unbekannt. Die nächsten Produktänderungen sollen aus beobachteten Problemen und Anfragen abgeleitet werden, nicht aus Downloadzahlen allein.
