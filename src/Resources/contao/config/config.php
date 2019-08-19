<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   bdf
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

define('TRAINERLIZENZEN_ABSENDER', $GLOBALS['TL_CONFIG']['trainerlizenzen_absender']);
define('TRAINERLIZENZEN_PFAD', TL_ROOT . '/files/trainerlizenzen');

// Zugang LiMS
define('LIMS_HOST', $GLOBALS['TL_CONFIG']['lims_host']);
define('LIMS_USERNAME', $GLOBALS['TL_CONFIG']['lims_username']);
define('LIMS_PASSWORD', $GLOBALS['TL_CONFIG']['lims_password']);
define('LIMS_LINK', $GLOBALS['TL_CONFIG']['lims_link']);

$GLOBALS['BE_MOD']['content']['trainerlizenzen'] = array
(
	'tables'            => array('tl_lizenzverwaltung', 'tl_lizenzverwaltung_items', 'tl_lizenzverwaltung_referenten', 'tl_lizenzverwaltung_mails'),
	'icon'              => 'bundles/contaotrainerlizenzen/images/icon.png',
	'export'            => array('trainerlizenzExport', 'exportTrainer'),
	'exportXLS'         => array('trainerlizenzExport', 'exportXLSTrainer'),
	'import'            => array('Samson\Trainerlizenzen\trainerlizenzImport', 'importTrainer'), 
	'getLizenz'         => array('Samson\Trainerlizenzen\DOSBLizenzen', 'getLizenz'),
	'getLizenzPDF'      => array('Samson\Trainerlizenzen\DOSBLizenzen', 'getLizenzPDF'),
	'getLizenzPDFCard'  => array('Samson\Trainerlizenzen\DOSBLizenzen', 'getLizenzPDFCard'),
	'exportDOSB'        => array('Samson\Trainerlizenzen\DOSBLizenzen', 'exportDOSB'),
	'send'              => array('Samson\Trainerlizenzen\Mailer', 'send'), 
);

// SimpleAjax Hook
$GLOBALS['TL_HOOKS']['simpleAjax'][] = array('ajaxRequest', 'compile');

/**
 * Frontend-Module
 */
$GLOBALS['FE_MOD']['application']['trainerlizenzen'] = '\Schachbulle\ContaoTrainerlizenzenBundle\Classes\Trainerliste';
