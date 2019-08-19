<?php 

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2014 Leo Feyer
 *
 */

/**
 * Backend-Modul Übersetzungen
 */

// Zusätzliche Funktionen
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['referenten'] = array('Referenten', 'Ausbildungsreferenten der Landesverbände');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['import'] = array('CSV-Import', 'CSV-Import');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['export'] = array('CSV-Export', 'CSV-Export der Lizenzen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportXLS'] = array('Excel-Export', 'Excel-Export der Lizenzen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportDOSB'] = array('DOSB-Export', 'Aktive Lizenzen zum DOSB exportieren');

// Standardfunktionen
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['new'] = array('Neue Person', 'Neue Person anlegen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['edit'] = array('Person %s bearbeiten', 'Person %s bearbeiten');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['emailbox'] = array('E-Mails für Person %s verwalten', 'E-Mails für Person %s verwalten');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['copy'] = array('Person %s kopieren', 'Person %s kopieren');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['delete'] = array('Person %s löschen', 'Person %s löschen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['toggle'] = array('Person %s aktivieren/deaktivieren', 'Person %s aktivieren/deaktivieren');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['show'] = array('Details zur Person %s anzeigen', 'Details zur Person %s anzeigen');

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['tstamp'][0] = 'Zeitstempel';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['tstamp'][1] = 'Zeitstempel des Datensatzes';

// Formularfelder
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['name_legend'] = 'Trainer';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['vorname'][0] = 'Vorname';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['vorname'][1] = 'Vorname des Trainers';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['name'][0] = 'Nachname';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['name'][1] = 'Nachname des Trainers';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['titel'][0] = 'Titel';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['titel'][1] = 'Titel, z.B. Dr., Prof., Prof.Dr.';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geburtstag'][0] = 'Geburtstag';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geburtstag'][1] = 'Geburtstag im Format TT.MM.JJJJ';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geschlecht'][0] = 'Geschlecht';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geschlecht'][1] = 'Geschlecht des Trainers';

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['adresse_legend'] = 'Adresse';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['strasse'][0] = 'Straße und Hausnummer';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['strasse'][1] = 'Straße und Hausnummer';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['plz'][0] = 'Postleitzahl';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['plz'][1] = 'Postleitzahl des Wohnortes. Nur 5-stellige Zahlen sind möglich!';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['ort'][0] = 'Wohnort';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['ort'][1] = 'Wohnort';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['email'][0] = 'E-Mail';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['email'][1] = 'E-Mail-Adresse';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['telefon'][0] = 'Telefon';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['telefon'][1] = 'Telefon';

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['hinweise_legend'] = 'Bemerkungen/Hinweise';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['addEnclosure'] = array('Dateien hinzufügen', 'Dateien für interne Verwendung hinzufügen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosure'] = array('Dateien', 'Dateien wählen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosureInfo'] = array('Informationen zu den Dateien', 'Informationen zu den Dateien');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['bemerkung'] = array('Interne Bemerkung', 'Interne Bemerkung');

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['published_legend'] = 'Veröffentlichung';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['published'][0] = 'Aktiv';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['published'][1] = 'Datensatz aktivieren';

/**
 * Filter
 */

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter'] = "Spezialfilter";
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_extended'] = "Spezialfilter";
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_activetrainers'] = "Alle gültigen Lizenzen";
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_unsentmails'] = "Trainer mit ungesendeten E-Mails";
