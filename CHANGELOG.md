# Lizenzverwaltung Changelog

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
