<?php

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

// Zugang LiMS
define('LIMS_HOST', $GLOBALS['TL_CONFIG']['lims_host']);
define('LIMS_USERNAME', $GLOBALS['TL_CONFIG']['lims_username']);
define('LIMS_PASSWORD', $GLOBALS['TL_CONFIG']['lims_password']);
define('LIMS_LINK', $GLOBALS['TL_CONFIG']['lims_link']);

$GLOBALS['BE_MOD']['content']['lizenzverwaltung'] = array
(
	'tables'            => array('tl_lizenzverwaltung', 'tl_lizenzverwaltung_items', 'tl_lizenzverwaltung_referenten', 'tl_lizenzverwaltung_mails', 'tl_lizenzverwaltung_templates', 'tl_lizenzverwaltung_verbaende'),
	'icon'              => 'bundles/contaolizenzverwaltung/images/icon.png',
	'import'            => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzImport', 'importTrainer'), 
	'exportXLS'         => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\TrainerlizenzExport', 'exportTrainer_XLS'),
	'exportDOSB'        => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'exportToDOSB'),
	'getLizenz'         => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'getLizenz'),
	'getLizenzPDF'      => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'getLizenzPDF'),
	'getLizenzPDFCard'  => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\DOSBLizenzen', 'getLizenzPDFCard'),
	'deleteMarker'      => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\Marker', 'deleteMarker'),
	'send'              => array('Schachbulle\ContaoLizenzverwaltungBundle\Classes\Mailer', 'send'), 
);

// SimpleAjax Hook
//$GLOBALS['TL_HOOKS']['simpleAjax'][] = array('ajaxRequest', 'compile');

/**
 * Frontend-Module
 */
$GLOBALS['FE_MOD']['application']['lizenzverwaltung'] = '\Schachbulle\ContaoLizenzverwaltungBundle\Modules\Lizenzenliste';

// Backend-CSS einbinden
if(TL_MODE == "BE") $GLOBALS['TL_CSS'][] = 'bundles/contaolizenzverwaltung/css/default.css';
