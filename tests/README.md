# Prüfstände

Drei Skripte, mit denen sich das Bundle gegen eine laufende Contao-Installation prüfen
lässt. Sie brauchen kein PHPUnit, sondern fahren das Contao-Framework selbst hoch, legen
ihre Prüfdaten in der Datenbank an und räumen sie hinterher wieder ab.

Gedacht sind sie für den Gegentest über beide unterstützten Contao-Fassungen: Was in
Contao 4.13 läuft, muss auch in Contao 5 laufen und umgekehrt.

## Voraussetzungen

Eine eingerichtete Contao-Installation mit Datenbank, in der dieses Bundle installiert ist
— am einfachsten als Composer-Pfad-Repository, dann wirken Änderungen am Quellcode sofort.

Aufgerufen wird jeweils **aus dem Wurzelverzeichnis der Installation**, nicht aus dem
Bundle-Verzeichnis.

## `pruefstand.php`

Der Hauptprüfstand. Er lädt alle DCA-Dateien, gleicht die Palettenfelder gegen die
Felddefinitionen ab, erzeugt jede Rückrufklasse und ruft jeden Rückruf einmal auf.

```
php <pfad-zum-bundle>/tests/pruefstand.php
```

Der Fehlerbehandler ist bewusst auf Meldungen eingeschränkt, deren Quelldatei im Bundle
liegt: Contao 4.13 löst unter PHP 8.4 eine Reihe eigener Deprecations aus, die hier nichts
zu suchen haben.

Erwartete Ausgabe:

```
=== ERGEBNIS ===
77 Prüfungen bestanden

Keine Fehler, keine Meldungen.
```

**Falle:** Contao 5 legt die zusammengeführten DCA-Dateien unter
`var/cache/prod/contao/dca/` ab. Nach einer Änderung an einer DCA-Datei muss
`var/cache/prod` weg, sonst wird die alte Fassung geladen und der Fehler bleibt wortgleich
stehen.

## `pruefstand-export.php`

Erzeugt die Excel-Datei des Lizenzexports wirklich und prüft dabei, ob Suche, Filter und
Spezialfilter der Übersicht übernommen werden. Zwei Gegenproben stellen sicher, dass ein
nicht passender Suchbegriff die Menge leert und ein erfundener Feldname verworfen wird
statt in die Abfrage zu gelangen.

Die Diagnose geht nach STDERR, allein die Excel-Datei nach STDOUT — `exportTrainer_XLS()`
beendet den Prozess, deshalb hängt das Aufräumen an `register_shutdown_function()`.

```
php <pfad-zum-bundle>/tests/pruefstand-export.php > lizenzen.xls
```

## `pruefstand-routen.php`

Schickt beide LiMS-Routen ohne angemeldeten Backend-Benutzer durch den Kernel. Erwartet
wird zweimal HTTP 403 und ein unveränderter Datensatz.

```
php <pfad-zum-bundle>/tests/pruefstand-routen.php
```

## `pruefstand-lims.php`

Prüft die Trainerliste aus dem LiMS, **ohne** das echte LiMS aufzurufen: Ein
nachgebildeter Client liefert feste Antworten, ein Speicher-Cache ersetzt `cache.app`.
Geprüft werden Blättern über mehrere Seiten, Verbandsnamen über zwei Ebenen, der Rückfall
auf `custom_1`, das Übergehen anonymisierter Lizenzen, die Sortierung mit Umlauten, der
Cache und die Reserve bei einem fehlgeschlagenen Abruf. Zum Schluss wird das Modul samt
Template gerendert.

```
php <pfad-zum-bundle>/tests/pruefstand-lims.php
```

Erwartet: 21 Prüfungen bestanden, keine Meldungen.

Die nachgebildeten Antworten folgen dem Aufbau, den das echte LiMS am 2026-09-15
lieferte: Lizenzen unter `licenses`, Organisationen unter `organisations` mit `name`,
`organisation_id` und `parent_id`, Zahlen als Zeichenketten. Eine Lizenz einer fremden
Sportart ist eingebaut und darf nie abgefragt werden — `/lookup` ohne `organisation_id`
liefert die Lizenzen des gesamten DOSB.

## Konsolenbefehl

Der Quartalsversand lässt sich gefahrlos im Probelauf prüfen:

```
vendor/bin/contao-console lizenzverwaltung:trainerliste --dry-run
```
