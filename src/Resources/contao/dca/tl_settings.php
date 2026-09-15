<?php

use Contao\StringUtil;
use Contao\Validator;

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{lizenzverwaltung_legend:hide},lizenzverwaltung_lizenzordner,lizenzverwaltung_versandordner,lizenzverwaltung_mailsignatur,lizenzverwaltung_absender,lims_host,lims_link,lims_username,lims_password';

/**
 * fields
 */

// Ordner für die Ablage von Lizenzdateien
$GLOBALS['TL_DCA']['tl_settings']['fields']['lizenzverwaltung_lizenzordner'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lizenzverwaltung_lizenzordner'],
	'inputType'               => 'fileTree',
	'eval'                    => array('multiple'=>false, 'fieldType'=>'radio', 'tl_class'=>'w50'),
	// Der Dateibaum liefert die UUID als 16 Byte Binärwert. Die Einstellungen
	// landen aber in system/config/localconfig.php, also in einer PHP-Datei,
	// die den Binärwert nicht unbeschadet übersteht: Nullbytes und Backslashes
	// gehen dabei verloren, und FilesModel::findByUuid() findet den Ordner
	// später nicht mehr. Deshalb wird hier in die lesbare Schreibweise
	// umgewandelt, die findByUuid() ebenso versteht. Gilt für beide
	// Ordner-Felder dieser Datei.
	'save_callback' => array
	(
		static function ($varValue)
		{
			return Validator::isBinaryUuid($varValue) ? StringUtil::binToUuid($varValue) : $varValue;
		}
	),
);

// Ordner für die Ablage von versendeten Dateien
$GLOBALS['TL_DCA']['tl_settings']['fields']['lizenzverwaltung_versandordner'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lizenzverwaltung_versandordner'],
	'inputType'               => 'fileTree',
	'eval'                    => array('multiple'=>false, 'fieldType'=>'radio', 'tl_class'=>'w50'),
	// Umwandlung der binären UUID, siehe Hinweis beim Lizenzordner
	'save_callback' => array
	(
		static function ($varValue)
		{
			return Validator::isBinaryUuid($varValue) ? StringUtil::binToUuid($varValue) : $varValue;
		}
	),
);

// Absendername und E-Mail die bei Mails verwendet wird
$GLOBALS['TL_DCA']['tl_settings']['fields']['lizenzverwaltung_absender'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lizenzverwaltung_absender'],
	'inputType'               => 'text',
	'eval'                    => array('tl_class'=>'w50','preserveTags'=>true)
);

// Mailtemplate
$GLOBALS['TL_DCA']['tl_settings']['fields']['lizenzverwaltung_mailsignatur'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lizenzverwaltung_mailsignatur'],
	'inputType'               => 'textarea',
	'eval'                    => array('tl_class'=>'long clr', 'rte' => 'tinyMCE', 'cols' => 80,'rows' => 10)
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_host'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lims_host'],
	'inputType'               => 'text',
	'eval'                    => array('tl_class'=>'w50 clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_username'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lims_username'],
	'inputType'               => 'text',
	'eval'                    => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_password'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lims_password'],
	'inputType'               => 'text',
	'eval'                    => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['lims_link'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lims_link'],
	'inputType'               => 'text',
	'eval'                    => array('tl_class'=>'w50')
);
