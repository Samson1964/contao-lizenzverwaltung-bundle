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
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['editHeader'] = array("Person %s bearbeiten", "Person %s bearbeiten");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['edit'] = array("Lizenzen der Person %s bearbeiten", "Lizenzen der Person %s bearbeiten");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['copy'] = array("Person %s kopieren", "Person %s kopieren");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['delete'] = array("Person %s löschen", "Person %s löschen");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['toggle'] = array("Person %s aktivieren/deaktivieren", "Person %s aktivieren/deaktivieren");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['show'] = array("Details zur Person %s anzeigen", "Details zur Person %s anzeigen");

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['tstamp'] = array('Änderungsdatum', 'Änderungsdatum des Datensatzes');

// Formularfelder
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['name_legend'] = 'Trainer';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['vorname'] = array('Vorname', 'Vorname der Person');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['name']= array('Nachname', 'Nachname der Person');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['titel']= array('Titel', 'Titel, z.B. Dr., Prof., Prof.Dr.');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geburtstag']= array('Geburtstag', 'Geburtstag im Format TT.MM.JJJJ');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['geschlecht']= array('Geschlecht', 'Geschlecht der Person');

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['adresse_legend'] = 'Adresse';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['strasse']= array('Straße und Hausnummer', 'Straße und Hausnummer');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['plz']= array('Postleitzahl', 'Postleitzahl des Wohnortes. Nur 5-stellige Zahlen sind möglich!');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['ort']= array('Wohnort', 'Wohnort');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['email']= array('E-Mail', 'E-Mail-Adresse');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['telefon']= array('Telefon', 'Telefon');

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['hinweise_legend'] = 'Bemerkungen/Hinweise';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['addEnclosure'] = array('Dateien hinzufügen', 'Dateien für interne Verwendung hinzufügen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosure'] = array('Dateien', 'Dateien wählen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['enclosureInfo'] = array('Informationen zu den Dateien', 'Informationen zu den Dateien');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['bemerkung'] = array('Interne Bemerkung', 'Interne Bemerkung');

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['published_legend'] = 'Veröffentlichung';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['published']= array('Aktiv', 'Datensatz aktivieren');

/**
 * Filter
 */

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter'] = 'Spezialfilter';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_extended'] = 'Spezialfilter';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_active_licenses'] = 'Personen mit gültigen Lizenzen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_inactive_licenses'] = 'Personen mit ungültigen Lizenzen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_unsentmails'] = 'Personen mit ungesendeten E-Mails';
