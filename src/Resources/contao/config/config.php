<?php

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

use Contao\System;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Mailer;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Marker;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzExport;
use Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzImport;
use Schachbulle\ContaoLizenzverwaltungBundle\Modules\LimsTrainerliste;
use Schachbulle\ContaoLizenzverwaltungBundle\Modules\Lizenzenliste;

/**
 * -------------------------------------------------------------------------
 * Voreinstellungen Contao-BE System -> Einstellungen
 * -------------------------------------------------------------------------
 *
 * Die Werte werden über Config::get() gelesen. Früher lagen sie zusätzlich in
 * Konstanten (LIMS_HOST und Geschwister); die sind entfallen, weil sie beim
 * Speichern der Einstellungen ihren alten Wert behielten und sich unter
 * Contao 5 nicht mehr sauber setzen lassen.
 */
$GLOBALS['TL_CONFIG']['lizenzverwaltung_absender'] ??= '';
$GLOBALS['TL_CONFIG']['lizenzverwaltung_mailsignatur'] ??= '';
$GLOBALS['TL_CONFIG']['lizenzverwaltung_lizenzordner'] ??= '';
$GLOBALS['TL_CONFIG']['lizenzverwaltung_versandordner'] ??= '';
$GLOBALS['TL_CONFIG']['lims_host'] ??= '';
$GLOBALS['TL_CONFIG']['lims_username'] ??= '';
$GLOBALS['TL_CONFIG']['lims_password'] ??= '';
$GLOBALS['TL_CONFIG']['lims_link'] ??= '';

/**
 * Backend-Modul
 */
$GLOBALS['BE_MOD']['content']['lizenzverwaltung'] = array
(
	'tables'            => array('tl_lizenzverwaltung', 'tl_lizenzverwaltung_items', 'tl_lizenzverwaltung_referenten', 'tl_lizenzverwaltung_mails', 'tl_lizenzverwaltung_templates', 'tl_lizenzverwaltung_verbaende'),
	'icon'              => 'bundles/contaolizenzverwaltung/images/icon.png',
	'import'            => array(TrainerlizenzImport::class, 'importTrainer'),
	'exportXLS'         => array(TrainerlizenzExport::class, 'exportTrainer_XLS'),
	'exportDOSB'        => array(DOSBLizenzen::class, 'exportToDOSB'),
	'getLizenz'         => array(DOSBLizenzen::class, 'getLizenz'),
	'getLizenzPDF'      => array(DOSBLizenzen::class, 'getLizenzPDF'),
	'getLizenzPDFCard'  => array(DOSBLizenzen::class, 'getLizenzPDFCard'),
	'deleteMarker'      => array(Marker::class, 'deleteMarker'),
	'send'              => array(Mailer::class, 'send'),
);

/**
 * Frontend-Module
 */
$GLOBALS['FE_MOD']['application']['lizenzverwaltung'] = Lizenzenliste::class;
$GLOBALS['FE_MOD']['application']['lizenzverwaltung_lims'] = LimsTrainerliste::class;

/**
 * Backend-CSS einbinden
 *
 * Die Konstante TL_MODE gibt es unter Contao 5 nicht mehr. Der Scope-Matcher
 * ist in beiden Fassungen ein öffentlicher Dienst; ohne laufenden Request
 * (Kommandozeile) wird nichts eingebunden.
 */
$request = System::getContainer()->get('request_stack')->getCurrentRequest();

if (null !== $request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
{
	$GLOBALS['TL_CSS'][] = 'bundles/contaolizenzverwaltung/css/default.css';
}
