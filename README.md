# Lizenzverwaltung für den Deutschen Schachbund

Verwaltet die Trainer- und Ausbilderlizenzen des Deutschen Schachbundes in Contao und
überträgt sie an das Lizenzmanagementsystem (LiMS) des Deutschen Olympischen Sportbundes.

**Frank Hoppe**

## Voraussetzungen

| | |
| --- | --- |
| PHP | 8.1 bis 8.4 |
| Contao | 4.13 oder 5.3+ |
| Erweiterungen | `curl`, `json` |

Mitinstalliert werden `codefog/contao-haste`, `menatwork/contao-multicolumnwizard-bundle`,
`schachbulle/contao-helper-bundle` und `phpoffice/phpspreadsheet`.

## Installation

```
composer require schachbulle/contao-lizenzverwaltung-bundle
```

Anschließend die Datenbank aktualisieren (Contao Manager oder
`vendor/bin/contao-console contao:migrate`).

## Einrichtung

Unter **System → Einstellungen**, Bereich *Lizenzverwaltung*:

| Einstellung | Bedeutung |
| --- | --- |
| Ordner für die Lizenzdateien vom DOSB | Hier legt das Bundle die vom DOSB abgerufenen Urkunden als PDF ab. |
| Ordner für die versendeten Dateien | Hier landen die an die Referenten verschickten Excel-Listen. |
| Absender für E-Mail-Versand | In der Form `Name <adresse@example.org>`. |
| Signatur für E-Mail-Versand | Wird als Platzhalter `##lizenz_signatur##` in die Vorlagen eingesetzt. |
| LiMS API-Adresse | Basisadresse der Schnittstelle, mit abschließendem Schrägstrich. |
| LiMS Benutzername / Passwort | Zugangsdaten für die Schnittstelle. |
| LiMS URL zum Lizenzmanagement | Adresse der Weboberfläche, für den Knopf „Ansehen“ am Datensatz. |

Ohne LiMS-Adresse laufen alle Abrufe ins Leere und vermerken das am Datensatz; die
Verwaltung der Lizenzen im Backend funktioniert davon unabhängig.

## Backend-Modul

Das Modul **Inhalte → Lizenzverwaltung** führt Personen, deren Lizenzen, die
Verbandsreferenten, die E-Mail-Vorlagen und die Verbände.

* **Lizenz erstellen/verlängern** überträgt einen Datensatz an das LiMS und schreibt die
  vom DOSB vergebene Lizenznummer zurück.
* **PDF abrufen** holt die Urkunde in den Formaten DIN A4 und Karte und legt sie im
  Lizenzordner ab.
* **Aktive Lizenzen zum DOSB exportieren** überträgt alle Lizenzen, die seit der letzten
  Übertragung geändert wurden oder zuletzt einen Fehler ergaben — nacheinander, mit
  Fortschrittsanzeige.
* **Excel-Export** gibt genau die Datensätze aus, die in der Übersicht gerade zu sehen
  sind: Suche, Filter und Spezialfilter werden übernommen.
* **E-Mail versenden** verschickt die Lizenzurkunde an den Trainer, auf Wunsch mit Kopie
  an den Verbandsreferenten und Blindkopie an die DSB-Geschäftsstelle.

Der Spezialfilter über der Übersicht schränkt auf gültige, ungültige oder markierte
Lizenzen ein, auf ungesendete E-Mails oder auf einen einzelnen Landesverband.

## Frontend-Modul „Lizenzenliste“

Gibt die Personen mit einer gültigen Lizenz als Tabelle aus. Welche Lizenzarten erscheinen,
legt die Moduleinstellung *Lizenzart* fest; *Lizenzspalte anzeigen* blendet die Lizenzart
mit ein, *Lizenz gültig bis Jahresende* rundet das Gültigkeitsdatum in der Anzeige auf den
31. Dezember auf.

Template: `mod_lizenzenliste`.

## Frontend-Modul „Trainerliste aus dem LiMS“

Gibt eine der vier öffentlichen Trainerlisten unmittelbar aus dem Lizenzmanagementsystem
des DOSB aus. Die Tabellen der Lizenzverwaltung spielen dabei keine Rolle.

| Liste | Ausbildungsgänge im LiMS |
| --- | --- |
| A-Trainer | 515 (Leistungssport), 71011 (Breitensport) |
| B-Trainer | 514 (Leistungssport), 71010 (Breitensport) |
| C-Trainer | 513 (Leistungssport), 512 (Breitensport) |
| DOSB-Ausbilder | 49337 (Ausbilder-Zertifikat) |

Ausgegeben werden Nachname, Vorname, Verband und das Gültigkeitsdatum, sortiert nach
Nachname und Vorname, darunter der Stand des Abrufs. *Lizenz gültig bis Jahresende*
rundet das Datum in der Anzeige auf den 31. Dezember auf. Lizenzen, die das LiMS
anonymisiert ausliefert, erscheinen nicht.

Voraussetzung sind die LiMS-Zugangsdaten in den Einstellungen.

**Cache:** Alle Lizenzen werden in einem Zug abgerufen und eine Woche lang im
Contao-Cache gehalten; die vier Listen teilen sich diesen Abruf. Schlägt ein Abruf fehl,
bleibt die letzte erfolgreiche Fassung sichtbar. Wer nicht auf den ersten Besucher warten
will, füllt den Cache per Zeitplan vorab:

```
vendor/bin/contao-console lizenzverwaltung:lims-trainerliste --frisch
```

`--rohdaten` zeigt zusätzlich den Aufbau der Antworten von `/lookup` und
`/lookup_organisations`, mit auf den Anfangsbuchstaben gekürzten Namen.

Template: `mod_lims_trainerliste`.

## Quartalsversand an die Referenten

Referenten, bei denen *Lizenzliste senden* gesetzt ist, bekommen einmal je Quartal die Lizenzliste
ihres Verbands als Excel-Datei, zusammen mit einer Aufstellung der Lizenzen, die in den
nächsten sechs Monaten ablaufen.

Ausgelöst wird der Versand über einen Zeitplan (Cron) auf dem Server:

```
vendor/bin/contao-console lizenzverwaltung:trainerliste
```

| Schalter | Wirkung |
| --- | --- |
| `--dry-run` | Zeigt nur an, was geschehen würde. Es wird nichts verschickt und nichts geschrieben. |
| `--force`, `-f` | Verschickt auch dann, wenn im laufenden Quartal bereits versendet wurde. |

Ein täglicher Aufruf genügt: Der Befehl prüft je Referent selbst, ob im laufenden Quartal
schon versendet wurde. Schlägt der Versand an einen Empfänger fehl, läuft er bei den
übrigen weiter und meldet die Fehlschläge am Ende; das Versanddatum bleibt dann ungesetzt,
sodass der nächste Lauf es erneut versucht.

> **Bis Fassung 4.3.6** wurde dafür die Adresse
> `bundles/contaolizenzverwaltung/Trainerliste.php` aufgerufen. Diese Datei gibt es nicht
> mehr; der Zeitplan muss auf den Konsolenbefehl umgestellt werden.

## E-Mail-Vorlagen

Die Vorlagen liegen als Datensätze in der Lizenzverwaltung und sind vollständige
HTML-Dokumente. Verwertet wird beim Versand der Inhalt des `body`-Elements. Diese
Platzhalter stehen zur Verfügung:

| Platzhalter | Inhalt |
| --- | --- |
| `##css##` | Stilblock, den das Bundle beim Versand einsetzt |
| `##lizenz_title##` | Betreff der E-Mail |
| `##lizenz_vorname##`, `##lizenz_nachname##` | Name des Trainers |
| `##lizenz_geschlecht##` | `m` oder `w` |
| `##lizenz_art##` | Lizenzart, etwa `C-B` |
| `##lizenz_nummer##` | DOSB-Lizenznummer |
| `##lizenz_content##` | Der redaktionelle Text der E-Mail |
| `##lizenz_signatur##` | Signatur aus den Einstellungen |

## Routen

Zwei Routen bedienen den Stapelexport zum DOSB. Sie laufen im Backend-Bereich und
verlangen einen angemeldeten Benutzer mit Zugriff auf das Modul „Lizenzverwaltung“:

* `/_lizenzverwaltung/lims/export/{id}` — überträgt eine Lizenz
* `/_lizenzverwaltung/lims/umzug/{id}` — meldet eine Lizenz in einen Unterverband um

Sie sind nicht dafür gedacht, von Hand aufgerufen zu werden.

## Hinweis zum Update auf Fassung 5.0.0

Wo Contao die Bundle-Dateien kopiert statt verlinkt, bleiben nach dem Update die alten
Skripte unter `public/bundles/contaolizenzverwaltung/` liegen:
`Trainerliste.php`, `ajaxRequest.php`, `ajaxRequestUmzug.php` und
`migration_c3-to-c4.php`. Sie waren ohne Anmeldeprüfung erreichbar und gehören von Hand
gelöscht.

## Lizenz

LGPL-3.0-or-later
