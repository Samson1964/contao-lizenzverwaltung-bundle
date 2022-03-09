<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2014 Leo Feyer
 *
 */

/**
 * Backend-Modul Übersetzungen
 */

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['lizenzen'] = array('Lizenzen', 'Lizenzen der Person');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['verbaende'] = array('Verband', 'Verbände der Person');

// Zusätzliche Funktionen
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['referenten'] = array('Referenten', 'Ausbildungsreferenten der Landesverbände');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['import'] = array('CSV-Import', 'CSV-Import');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportXLS'] = array('Excel-Export', 'Excel-Export der Lizenzen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['exportDOSB'] = array('DOSB-Export', 'Aktive Lizenzen zum DOSB exportieren');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['deleteMarker'] = array('Markierungen löschen', 'Markierungen in den Lizenzen löschen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['templates'] = array('Mail-Templates', 'E-Mail-Templates verwalten');

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['deleteMarker_confirm'] = 'Wollen Sie die Markierungen in den Lizenzen wirklich löschen?';

// Standardfunktionen
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['new'] = array('Neue Person', 'Neue Person anlegen');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['editHeader'] = array("Person %s bearbeiten", "Person %s bearbeiten");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['edit'] = array("Lizenzen der Person %s bearbeiten", "Lizenzen der Person %s bearbeiten");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['copy'] = array("Person %s kopieren", "Person %s kopieren");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['delete'] = array("Person %s löschen", "Person %s löschen");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['toggle'] = array("Person %s aktivieren/deaktivieren", "Person %s aktivieren/deaktivieren");
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['show'] = array("Details zur Person %s anzeigen", "Details zur Person %s anzeigen");

$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['tstamp'] = array('Änderungsdatum', 'Änderungsdatum des Datensatzes');
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['alias'] = array('Nach- oder Vorname', 'Nach- oder Vorname suchen');

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
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_marked_licenses'] = 'Personen mit markierten Lizenzen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_unsentmails'] = 'Personen mit ungesendeten E-Mails';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_S'] = 'Lizenzen Deutscher Schachbund';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_1'] = 'Lizenzen Baden';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_2'] = 'Lizenzen Bayern';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_3'] = 'Lizenzen Berlin';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_D'] = 'Lizenzen Brandenburg';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_B'] = 'Lizenzen Bremen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_4'] = 'Lizenzen Hamburg';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_5'] = 'Lizenzen Hessen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_E'] = 'Lizenzen Mecklenburg-Vorpommern';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_7'] = 'Lizenzen Niedersachsen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_6'] = 'Lizenzen Nordrhein-Westfalen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_8'] = 'Lizenzen Rheinland-Pfalz';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_9'] = 'Lizenzen Saarland';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_F'] = 'Lizenzen Sachsen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_H'] = 'Lizenzen Sachsen-Anhalt';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_A'] = 'Lizenzen Schleswig-Holstein';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_G'] = 'Lizenzen Thüringen';
$GLOBALS['TL_LANG']['tl_lizenzverwaltung']['filter_verband_C'] = 'Lizenzen Württemberg';
