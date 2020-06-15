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

define('LIZENZVERWALTUNG_ABSENDER', $GLOBALS['TL_CONFIG']['lizenzverwaltung_absender']);
define('LIZENZVERWALTUNG_PFAD', TL_ROOT . '/files/lizenzverwaltung');
define('LIZENZVERWALTUNG_WEBPDF', '/files/lizenzverwaltung');

// Zugang LiMS
define('LIMS_HOST', $GLOBALS['TL_CONFIG']['lims_host']);
define('LIMS_USERNAME', $GLOBALS['TL_CONFIG']['lims_username']);
define('LIMS_PASSWORD', $GLOBALS['TL_CONFIG']['lims_password']);
define('LIMS_LINK', $GLOBALS['TL_CONFIG']['lims_link']);

$GLOBALS['BE_MOD']['content']['lizenzverwaltung'] = array
(
	'tables'            => array('tl_lizenzverwaltung', 'tl_lizenzverwaltung_items', 'tl_lizenzverwaltung_referenten', 'tl_lizenzverwaltung_mails'),
	'icon'              => 'bundles/contaolizenzverwaltung/images/icon.png',
	'exportXLS'         => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzExport', 'exportTrainer_XLS'),
	'import'            => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzImport', 'importTrainer'), 
	'getLizenz'         => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'getLizenz'),
	'getLizenzPDF'      => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'getLizenzPDF'),
	'getLizenzPDFCard'  => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'getLizenzPDFCard'),
	'exportDOSB'        => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'exportDOSB'),
	'send'              => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\Mailer', 'send'), 
);

// SimpleAjax Hook
$GLOBALS['TL_HOOKS']['simpleAjax'][] = array('ajaxRequest', 'compile');

/**
 * Frontend-Module
 */
$GLOBALS['FE_MOD']['application']['lizenzverwaltung'] = '\Schachbulle\ContaoLizenzverwaltungBundle\Classes\Lizenzenliste';

// Backend-CSS einbinden
if(TL_MODE == "BE") $GLOBALS['TL_CSS'][] = 'bundles/contaolizenzverwaltung/css/default.css';

