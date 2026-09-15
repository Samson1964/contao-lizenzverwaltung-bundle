# Lizenzverwaltung Changelog

## Version 5.0.2 (2026-09-15)

* Fix: `lizenzverwaltung:lims-trainerliste` und das Modul „Trainerliste aus dem LiMS“
  blieben scheinbar hängen. `/lookup` ohne `organisation_id` liefert nicht die Lizenzen des
  DSB, sondern die des gesamten DOSB — beim ersten echten Abruf über 520.000, fast alle aus
  anderen Sportarten. Abgefragt wird jetzt je Organisation im Baum unterhalb des DSB
  (Organisation 1093), und nur vollständige Lizenzen (`is_obscured=nein`). Doppelt
  gelieferte Lizenzen werden über die DOSB-Lizenznummer zusammengeführt.
* Change: `--rohdaten` fragt `/lookup` jetzt ebenfalls mit der Organisation des DSB ab.

## Version 5.0.1 (2026-09-15)

* Fix: Unter Contao 4.13 ließ sich 5.0.0 nicht installieren, wenn die Installation
  `codefog/contao-haste` 4 fest verlangt („requires codefog/contao-haste ^5.4 … conflicts
  with your root composer.json require (^4.25)“). Das Bundle nimmt jetzt Haste 4.25 oder
  5.4: Die Platzhalterersetzung der E-Mails nutzt unter Haste 5 den Dienst `StringParser`
  und unter Haste 4 wieder `Haste\Util\StringUtil::recursiveReplaceTokensAndTags()`. Unter
  Contao 5 bleibt es bei Haste 5, weil Haste 4 dort nicht installierbar ist.

## Version 5.0.0 (2026-09-02)

Diese Fassung läuft unter **Contao 4.13 und Contao 5.3+ mit PHP 8.1 bis 8.4**.
Geprüft wurde gegen Contao 4.13.58 und 5.7.7 mit PHP 8.4.24.

**Wichtig beim Aktualisieren:**

1. **Der Cron-Eintrag für die Lizenzlisten muss umgestellt werden.** Bisher rief ein
   Zeitplan die Adresse `bundles/contaolizenzverwaltung/Trainerliste.php` auf. Die Datei
   ist entfallen; an ihre Stelle tritt ein Konsolenbefehl:

   ```
   vendor/bin/contao-console lizenzverwaltung:trainerliste
   ```

   Mit `--dry-run` zeigt er nur an, was geschehen würde, mit `--force` verschickt er auch
   dann, wenn im laufenden Quartal bereits versendet wurde.

2. **Verwaiste Dateien im Webverzeichnis entfernen.** Wo Contao die Bundle-Dateien kopiert
   statt verlinkt, bleiben nach dem Update die alten Skripte
   (`Trainerliste.php`, `ajaxRequest.php`, `ajaxRequestUmzug.php`, `migration_c3-to-c4.php`)
   unter `public/bundles/contaolizenzverwaltung/` liegen. Sie sind ohne Anmeldeprüfung
   erreichbar und gehören von Hand gelöscht.

3. **PHP 8.1 ist Mindestvoraussetzung**, ebenso Haste 5.4 und
   `schachbulle/contao-helper-bundle` 2.0. Letzteres wurde schon immer benutzt, stand aber
   nicht in der `composer.json`.

### Trainerliste aus dem LiMS

* Add: Frontend-Modul „Trainerliste aus dem LiMS“ (`lizenzverwaltung_lims`). Gibt die
  gültigen A-, B- oder C-Trainer beziehungsweise die DOSB-Ausbilder unmittelbar aus dem
  Lizenzmanagementsystem des DOSB aus — mit Nachname, Vorname, Verband und Gültigkeit, so
  wie die bisherigen Trainerlisten auf schachbund.de. Die Tabellen der Lizenzverwaltung
  werden dafür weder gelesen noch beschrieben. Leistungs- und Breitensport stehen jeweils
  in einer gemeinsamen Liste.
* Add: Alle Lizenzen werden in einem Zug abgerufen und eine Woche im Contao-Cache
  gehalten; die vier Listen teilen sich diesen Abruf. Schlägt ein Abruf fehl, bleibt die
  letzte erfolgreiche Fassung sichtbar, statt dass die Seite leer wird.
* Add: Die Verbandsnamen kommen über `/lookup_organisations` aus dem LiMS, Ebene für Ebene
  unterhalb des DSB; fehlt ein Name, greift das Zusatzfeld `custom_1`.
* Add: Konsolenbefehl `lizenzverwaltung:lims-trainerliste` füllt den Cache vorab.
  Mit `--rohdaten` zeigt er den Aufbau der Schnittstellenantwort mit gekürzten Namen —
  die Schnittstellenbeschreibung enthält dafür kein Beispiel.
* Add: Template `mod_lims_trainerliste`.

### Contao 5

* Add: Kompatibilität mit Contao 5.3+ hergestellt. Betroffen war praktisch jede Datei:
  Contao 5 registriert keine globalen Klassenaliasse mehr, `\Backend`, `\Database`,
  `\Input`, `\Controller`, `\Environment`, `\FilesModel`, `\Message`, `\StringUtil`,
  `\Module`, `\BackendTemplate` und die Typhinweise `\DataContainer` wurden deshalb
  durchgängig auf den Namensraum `Contao\` umgestellt.
* Fix: Die Konstanten `TL_ROOT`, `TL_MODE`, `TL_SCRIPT`, `VERSION`, `BUILD`,
  `REQUEST_TOKEN` und `FE_USER_LOGGED_IN` gibt es unter Contao 5 nicht mehr. Ersatz sind
  `Helper::getProjectDir()`, der Scope-Matcher, `Helper::getBackendRoute()` und
  `Helper::getRequestToken()`.
* Fix: Der Kopf `if (!defined('TL_ROOT')) die(...)` in `Marker.php` und
  `TrainerlizenzImport.php` hätte unter Contao 5 den Aufruf kommentarlos beendet.
* Fix: Die Funktionen `log_message()`, `specialchars()`, `ampersand()` und `deserialize()`
  gibt es unter Contao 5 nicht mehr; ersetzt durch `Helper::log()` und die passenden
  `StringUtil`-Methoden.
* Fix: `Session::getInstance()`, `$this->Session` und `$dc->Session` sind unter Contao 5
  wirkungslos beziehungsweise liefern `null`. Die Sitzung kommt jetzt über den
  Request-Stapel, die Filterzustände der Übersicht aus dem Sitzungsspeicher
  `contao_backend`.
* Fix: `$this->import('BackendUser', 'User')` bricht unter Contao 5 ab. Der Aufruf stand in
  vier DCA-Rückrufklassen und im Import, das importierte Objekt wurde nirgends benutzt.
* Fix: `Image::get()` ist unter Contao 5 entfernt; die Vorschaubilder der Dateianhänge
  entstehen jetzt über `contao.image.factory`.
* Fix: `'dataContainer' => 'Table'` auf `DC_Table::class` umgestellt — der Kurzname fehlt
  unter Contao 5.
* Fix: Alle DCA-Rückrufklassen haben einen öffentlichen Konstruktor bekommen. Unter
  Contao 4.13 ist `Backend::__construct()` nur protected, die Klassen ließen sich dort von
  außen nicht erzeugen.

### Alte Einstiegspunkte ersetzt

* Change: `Resources/public/ajaxRequest.php` und `ajaxRequestUmzug.php` sind entfallen. Sie
  holten sich das Framework über `system/initialize.php`, das es unter Contao 5 nicht mehr
  gibt — der Stapelexport zum DOSB wäre dort kommentarlos gescheitert. An ihre Stelle
  treten die Routen `/_lizenzverwaltung/lims/export/{id}` und `.../umzug/{id}`.
* Fix: **Die beiden Skripte liefen ohne jede Anmeldeprüfung.** Wer die Adresse kannte,
  konnte Lizenzdaten an den DOSB übertragen. Die neuen Routen verlangen einen
  Backend-Benutzer mit Zugriff auf das Modul „Lizenzverwaltung"; ohne Anmeldung antworten
  sie mit HTTP 403.
* Change: `Resources/public/Trainerliste.php` ist entfallen und durch den Konsolenbefehl
  `lizenzverwaltung:trainerliste` ersetzt (siehe oben). Auch dieses Skript war ohne
  Anmeldung erreichbar; ein einziger Aufruf löste den Versand an alle Referenten aus.
* Fix: Beim Listenversand wurden die DSB-Referenten aus einer einzigen Ergebnismenge
  gelesen, die schon nach dem ersten Empfänger erschöpft war — ab dem zweiten ging keine
  Blindkopie mehr heraus. Ebenso blieb bei einem Verband ohne Lizenzen die Excel-Datei des
  vorigen Verbands am Anhang hängen.
* Fix: Ein nicht erreichbarer Mailserver bricht den Listenversand nicht mehr ab; die
  übrigen Referenten werden weiter bedient, und das Versanddatum bleibt beim Fehlschlag
  ungesetzt, sodass der nächste Lauf es erneut versucht.
* Change: `Resources/public/migration_c3-to-c4.php` ist entfallen. Das Skript wandelte
  einmalig die Contao-3-Tabelle `tl_trainerlizenzen` um; diese Tabelle gibt es seit
  Fassung 2 nicht mehr.
* Change: Das Template `be_export_lizenzen.html5` ist entfallen. Es war eine nie
  eingebundene Kopie von Contaos Suchindex-Vorlage, verwies auf das seit Fassung 2
  umbenannte Modul `trainerlizenzen` und hätte unter Contao 5 an `REQUEST_TOKEN`
  abgebrochen.

### Fehler, die dabei auffielen

* Fix: `Haste\Util\StringUtil::recursiveReplaceTokensAndTags()` gibt es seit Haste 5 nicht
  mehr — die Klasse ist ersatzlos entfallen. Die E-Mail-Vorschau und der Mailversand wären
  damit **unter beiden Contao-Fassungen** abgebrochen. Ersatz ist der Dienst
  `Codefog\HasteBundle\StringParser`.
* Fix: `Mailer::send()` gab mitten in der Backend-Seite ein vergessenes `print_r($bcc)` aus.
* Fix: Der Import schrieb die CSV-Werte mit `addslashes()` in eine zusammengesetzte
  INSERT-Anweisung. Jetzt läuft je Zeile eine vorbereitete Anweisung.
* Fix: Der Excel-Export setzte Suchbegriff und Filterwerte ungeprüft in die SQL-Abfrage ein.
  Werte gehen jetzt als gebundene Parameter hinein, Feldnamen werden gegen die DCA geprüft.
* Fix: In der E-Mail-Übersicht stand `$result->lizenz` statt `$lizenz->lizenz` — die
  Lizenzart fehlte in jeder Vorschau. Geprüft wurde außerdem ein Feld `sendText`, das es nie
  gab, weshalb auch bereits versendete Mails noch eine Vorschau erzeugten (jetzt
  `sent_text`).
* Fix: Die Palette von `tl_lizenzverwaltung_mails` nannte ein Feld `send` und die von
  `tl_lizenzverwaltung_referenten` ein Feld `untergliederung` — beide gibt es nicht.
* Fix: Die Palette des Frontend-Moduls nannte `align` und `space`; diese Felder stammen aus
  Contao 3 und fehlen in `tl_module` beider unterstützter Fassungen.
* Fix: Zahlreiche Zugriffe auf nicht gesetzte Variablen und Array-Schlüssel beseitigt, die
  unter PHP 8 Warnungen erzeugten — unter anderem `$content` in `exportToDOSB()`,
  `$antwort` in beiden `viewEnclosureInfo()`, `$info` in `viewPDF()`/`viewPDFCard()`,
  `$email_cc`/`$email_bcc` in `Mailer::send()` und `$sql` im Frontend-Modul.
* Fix: Im Zweig „Umzug" des Stapelexports stand `$(itemcss_id)` statt `$(item.css_id)`.
* Change: Der Stapelexport lädt kein jQuery mehr von einem fremden Server (googleapis) und
  ruft die Datensätze nacheinander statt gleichzeitig ab — das LiMS quittierte parallele
  Anfragen mit Zeitüberschreitungen.
* Change: Der Einmal-Token des Mailversands wird mit `random_bytes()` erzeugt statt mit
  `md5(uniqid(mt_rand()))`.

### Innerer Umbau

* Change: Die Kommunikation mit dem LiMS lag dreifach im Bundle (Einzelabruf plus zwei
  Ajax-Skripte) und war auseinandergelaufen — der Stapellauf übermittelte das Geburtsdatum
  ohne die Zeitkorrektur, die der Einzelabruf vornimmt, und kannte die Lizenzarten
  „A-Trainer Breitensport" und „B-Trainer Breitensport" nicht. Alles liegt jetzt in
  `Classes/LimsClient.php`; maßgeblich ist das Verhalten des Einzelabrufs.
* Change: Die Auswahllisten für Verband und Lizenzart entstehen jetzt über
  `options_callback` statt beim Laden der DCA. Die Verbandsabfrage lief bisher bei jedem
  Seitenaufruf mit, auch wenn das Feld gar nicht angezeigt wurde.
* Change: Jede Funktion und Methode hat einen deutschen Kommentarblock bekommen.
* Change: Sämtliche Zeilenenden auf LF vereinheitlicht. Ein Teil der Dateien lag mit CRLF im
  Repository, das erzeugt im Diff dieser Fassung entsprechend Rauschen.
* Change: `composer.json` aufgeräumt — `schachbulle/contao-helper-bundle` ergänzt (wurde
  schon immer benutzt), die nicht mehr gebrauchten Entwicklungsabhängigkeiten
  (`doctrine/doctrine-cache-bundle`, `php-http/guzzle6-adapter`, `php-http/message-factory`)
  entfernt, die Symfony-Komponenten und `ext-curl`/`ext-json` ausdrücklich aufgeführt.

### Nicht behoben

* Der CSV-Import (Modulschlüssel `import`) liest weiterhin die Spaltenfolge der
  Contao-3-Tabelle `tl_trainerlizenzen`, die seit der Aufteilung auf `tl_lizenzverwaltung`
  und `tl_lizenzverwaltung_items` zu keiner Tabelle mehr passt. Im DCA verweist auch keine
  Operation darauf. Der Einstieg wurde nur lauffähig gehalten.

## Version 4.3.6 (2026-08-03)

**Wichtig beim Aktualisieren:** Lizenzordner und Versandordner müssen in den Einstellungen
einmal neu ausgewählt und gespeichert werden. Die bisher gespeicherten Werte sind beschädigt
und werden durch das Update nicht repariert.

* Fix: Lizenzordner und Versandordner (Einstellungen, Bereich Lizenzverwaltung) wurden nie
  gefunden. Der Dateibaum liefert die Kennung des Ordners als 16 Byte langen Binärwert; die
  Einstellungen landen aber in `system/config/localconfig.php`, also in einer PHP-Datei mit
  einfach gequoteten Zeichenketten. Nullbytes und Backslashes überleben das nicht — aus 16
  Byte wurden beim Zurücklesen 19, und `FilesModel::findByUuid()` lieferte nichts. Ein
  `save_callback` legt die Kennung jetzt in der lesbaren Schreibweise ab, die dieselbe
  Methode ebenso versteht. Der Fehler fiel nicht auf, weil im Backend weiterhin ein Ordner
  ausgewählt aussah.
* Change: `src/Resources/contao/dca/tl_settings.php` von ISO-8859-1 auf UTF-8 ohne BOM
  umgestellt; die Umlaute in den Kommentaren waren dort bislang falsch kodiert.

## Version 4.3.5 (2026-07-29)

* Fix: Warning: Undefined array key "deleteConfirm", "deleteMarker_confirm" bei contao:migrate -> Lesezugriffe auf $GLOBALS['TL_LANG'] in den DCA-Dateien mit `?? null` bzw. `?? array()` abgesichert, da der DcaLoader die Sprachdateien noch nicht geladen hat
* Change: Beschreibung, Keywords und Homepage in der composer.json ergänzt, damit Packagist das Paket verständlich darstellt und über die Suche auffindbar macht

## Version 4.3.4 (2025-09-23)

* Fix: Warning: Undefined array key "tl_lizenzverwaltungFilter" in /src/Resources/contao/dca/tl_lizenzverwaltung.php (line 503)
* Fix: Non-static method Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerband() cannot be called statically in /src/Resources/contao/dca/tl_lizenzverwaltung.php (line 713) 
* Fix: Non-static method Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getVerbandMail() cannot be called statically in src/Resources/contao/dca/tl_lizenzverwaltung_items.php (line 585) 
* Fix: Warning: Undefined variable $info in src/Resources/contao/dca/tl_lizenzverwaltung_items.php (line 834) 
* Fix: Warning: Undefined array key "lizenzverwaltung_lizenzordner" in src/Resources/contao/dca/tl_lizenzverwaltung_items.php (line 1026) 
* Fix: Warning: Undefined array key "tl_lizenzverwaltung" in src/Classes/TrainerlizenzExport.php (line 173) 

## Version 4.3.3 (2025-09-10)

* Fix: Warning: Undefined array key "deleteMarker_confirm" in /src/Resources/contao/dca/tl_lizenzverwaltung.php (line 96) -> Sprachvariable ausgelagert in default.php

## Version 4.3.2 (2025-09-10)

* Fix: Warning: Undefined array key "lizenzverwaltung_absender" in src/Resources/contao/config/config.php (line 13) 

## Version 4.3.1 (2025-09-09)

* Fix: Non-static method \Classes\Helper::getLizenzen() cannot be called statically
* Fix: Non-static method \Classes\Helper::getVerbaende() cannot be called statically

## Version 4.3.0 (2024-04-18)

* Change: Haste-Toggler statt des normalen Togglers
* Add: Kompatibilität PHP 8

## Version 4.2.3 (2023-10-10)

* Add: Lizenzen B- und A-Trainer Breitensport

## Version 4.2.2 (2023-10-04)

* Add: tl_lizenzverwaltung_items.verlaengerungen -> Feld für Seminardatum hinzugefügt
 
## Version 4.2.1 (2023-09-12)

* Add: tl_module.lizenzverwaltung_endofyear -> Checkbox um Gültigkeit der Lizenzen bis zum Jahresende im Frontend anzuzeigen

## Version 4.2.0 (2023-08-24)

* Change: Gültigkeit der Lizenzen bis zum Jahresende statt bis zum Quartalsende

## Version 4.1.7 (2023-04-13)

* Fix: Deaktivierte Personen werden in Lizenzliste FE angezeigt
* Fix: Deaktivierte Personen werden mit exportiert

## Version 4.1.6 (2022-07-06)

* Fix: Mailer.php kleine Anpassung
* Fix: Tags werden in E-Mails nicht ersetzt
* Fix: Abfrage ob To, CC und BCC befüllt ist, fehlte in Mailer.php
* Add: Negative Anfragen an den DOSB (Lizenz erstellen/verlängern, PDF-Dateien) werden rot markiert

## Version 4.1.5 (2022-05-19)

* Fix: Spezialfilter bringt Thüringen und Sachsen-Anhalt durcheinander

## Version 4.1.4 (2022-03-16)

* Fix: JQuery-Verlinkung auf https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js in DOSBLizenzen.php geändert

## Version 4.1.3 (2022-03-16)

* Der Fix aus Version 4.1.2 hat funktioniert. getVerbaende wird also bereits bei composer:install aufgerufen!
* Add: Helper::getVerbaende - Abfrage mit SHOW TABLES

## Version 4.1.2 (2022-03-16)

* Fix: Base table or view not found: 1146 Table 'tl_lizenzverwaltung_verbaende' doesn't exist (bei Installation im CM) - SELECT * FROM tl_lizenzverwaltung_verbaende WHERE published = 1
* Change: Helper::getVerbaende alte Funktion reaktivert zum Testen

## Version 4.1.1 (2022-03-16)

* Fix: Base table or view not found: 1146 Table 'tl_lizenzverwaltung_verbaende' doesn't exist (bei Installation im CM) - SELECT * FROM tl_lizenzverwaltung_verbaende WHERE published = 1
* Change: config.php - simpleAjax-Hook auskommentiert (vielleicht am Fehler schuld, weil die Hook-Klasse auf Helper::getVerbaende zugreift

## Version 4.1.0 (2022-03-16)

* Funktionen für untergliederte Verbände eingebaut, aber deaktiviert, da nicht benötigt
* Add: tl_lizenzverwaltung_verbaende
* Add: ajaxRequestUmzug.php
* Add: Helper-Funktion getVerbaende() in Tabelle ausgelagert
* Add: Verbandsname wird an DOSB-API im Feld custom_1 (Zusatzfeld 1) übermittelt
* Add: Template-Variablen lizenz_art und lizenz_nummer

## Version 4.0.0 (2022-03-09)

* Fix: Fehlerhafte Hilfe-Links in den Feldern tl_lizenzverwaltung_items.codex und tl_lizenzverwaltung_items.help verschoben nach codex_date und help_date
* Change: Leitfaden_LiMS_11.07.2016.pdf (Version 1.1) in den Links ausgetauscht gegen Leitfaden_LiMS_09.04.2019.pdf (Version 7.0)
* Add: Hilfe-Tooltip bei tl_lizenzverwaltung_items.gueltigkeit
* Add: Leitfaden-Link tl_lizenzverwaltung_items.leitfaden
* Add: Mailtemplate-Verwaltung
* Add: Abhängigkeit codefog/contao-haste für die Variablenersetzung in den Mailtemplates

## Version 3.1.6 (2021-10-01)

* Fix: Attempted to load class "Helper" from namespace "Samson" (in tl_lizenzverwaltung_items.php)

## Version 3.1.5 (2021-08-20)

* Fix: Bei Versand von E-Mails stören Umlaute in der Adresse und führen zum Absturz (weiße Seite)

## Version 3.1.4 (2021-05-04)

* Fix: Class Schachbulle\ContaoLizenzverwaltung\Classes\Helper not found in public/Trainerliste.php:212 (ContaoLizenzverwaltungBundle ist richtig)

## Version 3.1.3 (2021-04-15)

* Fix: Syntaxfehler bei Mailversand Address in mailbox given [ xxx@gmail.com] does not comply with RFC 2822, 3.6.2. (Leerzeichen zuviel)
* Add: Feld tl_lizenzverwaltung.alias, wo automatisch ein Alias aus Nachname und Vorname gespeichert wird, um darin suchen zu können

## Version 3.1.2 (2020-10-27)

* Fix: count(): Parameter must be an array or an object that implements Countable in Zeile 60 Lizenzenliste.php

## Version 3.1.1 (2020-08-26)

* trainerlizenzen.log auf lizenzverwaltung.log geändert
* Curl error: HTTP/2 stream 0 was not closed cleanly: PROTOCOL_ERROR (err 1) - bei PDF-Card bevorzugt -> Curl auf HTTP 1.1 umgestellt wegen Apache-Fehler

## Version 3.1.0 (2020-07-21)

* Ausgabe der Lizenzen im Frontend fertig programmiert
* Helper-Klasse: Leereintrag bei Lizenzen entfernt

## Version 3.0.3 (2020-06-25)

* Fix: Spezialfilter bei Excel-Import nicht anwendbar - Leerzeichen fehlt vor WHERE

## Version 3.0.2 (2020-06-25)

* Fix: In Mailvorschau und den verschickten Mails fehlt der Name/Vorname

## Version 3.0.1 (2020-06-24)

* Fix: PDF vom DOSB wurden mit HTTP-Header abgespeichert

## Version 3.0.0 (2020-06-23)

* Beschreibung der Erweiterung in composer.json verändert
* Korrektur Icons im Backend-CSS
* Anzeige der Landesverbände (der Lizenzen) in der Personenliste
* Personenliste: PLZ und Wohnort in der Übersicht entfernt
* Spezialfilter für Verbände hinzugefügt
* Spezialfilter (außer Personen mit ungesendeten Mails) werden im Excel-Export berücksichtigt
* Markierungen in Lizenzen löschen

## Version 2.0.1 (2020-06-17)

* Abhängigkeit richardhj/contao-simple-ajax entfernt (Paket wird nicht verwendet)

## Version 2.0.0 (2020-06-17)

Erste Version, die offiziell die alte Trainerlizenzen-Erweiterung ablöst.

* ajaxRequest.php überarbeitet: Lizenzen werden jetzt einzeln übertragen, damit Rückmeldungen verbessert
* Trainerliste.php überarbeitet
* Speicherpfade in System->Einstellungen hinzugefügt
* kleine Fehler korrigiert

## Version 1.2.1 (2020-06-15)

* Abhängigkeit phpoffice/phpexcel ersetzt durch phpoffice/phpspreadsheet
* exportTrainer_XLS erneuert
* exportTrainer_CSV entfernt
* Ausbilder-Zertifikat hinzugefügt
* tl_lizenzverwaltung: Sprachvariablen, Sortierung, Filter, Suche verbessert
* Ausgabe der Lizenzen in Auflistung der Personen
* tl_lizenzverwaltung_items: Auflistung der Lizenzen farblich markiert
* Mailer.php korrigiert
* Add: Signatur für E-Mails, statt Template verwenden
* Speicherpfad für die Lizenzdateien korrigiert
* Markierungsoption in Lizenzen umprogrammiert
* ajaxRequest.php eingebunden und überarbeitet
* Versionsnummer nur auf 1.2.1 statt 1.3.0 weil 1.1.0 versehentlich übersprungen wurde

## Version 1.2.0 (2020-06-11)

* Fix: Sprachdaten tl_settings.php
* Import-Skript Fehler beseitigt und Import Mails und Referenten ergänzt
* tl_lizenzverwaltung: Sprachvariablen, Sortierung, Filter, Suche verbessert
* tl_lizenzverwaltung_items: Sprachvariablen, Sortierung, Filter, Suche verbessert
* Helper-Klasse: neue Funktion getVerbandsmails
* tl_lizenzverwaltung_mails: Sprachvariablen, Sortierung, Filter, Suche verbessert
* tl_lizenzverwaltung_referenten: Sprachvariablen, Sortierung, Filter, Suche verbessert
* Spezialfilter korrigiert
* Neuer Spezialfilter: Personen mit ungültigen Lizenzen
* Ajax funktioniert seit Umstieg auf C4 wohl nicht mehr, deshalb richardhj/contao-simple-ajax hinzugefügt

## Version 1.0.4 (2020-06-08)

* Fix: Klassen-Verweis tl_lizenzverwaltung_items.php repariert

## Version 1.0.3 (2020-06-08)

* Fix: Klassen-Verweis tl_module.php repariert

## Version 1.0.2 (2020-06-08)

* Fix: Klassen-Verweise repariert

## Version 1.0.1 (2019-12-20)

- Fix: Kompatibilität mit Contao 4 alle Versionen hergestellt
- Fix: Abhängigkeit Symfony entfernt

## Version 1.0.0 (2019-08-19)
