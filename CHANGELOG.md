# Lizenzverwaltung Changelog

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
