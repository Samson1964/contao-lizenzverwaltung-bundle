<?php

/**
 * Lizenzverwaltung für den Deutschen Schachbund
 *
 * @copyright  Frank Hoppe 2014 - 2026
 * @author     Frank Hoppe <webmaster@schachbund.de>
 * @license    LGPL-3.0-or-later
 */

use Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper;

/**
 * Palette des Frontend-Moduls
 *
 * Die Felder "align" und "space" aus der früheren Palette gibt es weder in
 * Contao 4.13 noch in Contao 5 — sie stammen aus Contao 3 und sind deshalb
 * entfallen.
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['lizenzverwaltung'] = '{title_legend},name,headline,type;{lizenzverwaltung_legend},lizenzverwaltung_typ,lizenzverwaltung_typview,lizenzverwaltung_endofyear;{protected_legend:hide},protected;{expert_legend:hide},cssID';

// Trainerliste unmittelbar aus dem LiMS; die Tabellen der Lizenzverwaltung bleiben unberührt
$GLOBALS['TL_DCA']['tl_module']['palettes']['lizenzverwaltung_lims'] = '{title_legend},name,headline,type;{lizenzverwaltung_legend},lizenzverwaltung_lims_art,lizenzverwaltung_endofyear;{protected_legend:hide},protected;{expert_legend:hide},cssID';

/**
 * Felder
 */

// Welche Liste aus dem LiMS ausgegeben wird
$GLOBALS['TL_DCA']['tl_module']['fields']['lizenzverwaltung_lims_art'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lizenzverwaltung_lims_art'],
	'inputType'               => 'select',
	'options_callback'        => static fn () => array_map(static fn (array $a): string => $a['titel'], Schachbulle\ContaoLizenzverwaltungBundle\Classes\LimsTrainerliste::ARTEN),
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'mandatory'           => true
	),
	'sql'                     => "varchar(2) NOT NULL default 'A'"
);

// Anzuzeigende Lizenzarten
$GLOBALS['TL_DCA']['tl_module']['fields']['lizenzverwaltung_typ'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lizenzverwaltung_typ'],
	'inputType'               => 'checkboxWizard',
	// Als Rückruf statt als feste Liste: Der DcaLoader lädt keine
	// Sprachdateien, ein options_callback läuft dagegen erst beim Rendern
	'options_callback'        => static fn () => Helper::getLizenzen(),
	'eval'                    => array
	(
		'tl_class'            => 'w50 clr',
		'includeBlankOption'  => true,
		'chosen'              => true,
		'mandatory'           => true,
		'multiple'            => true
	),
	'sql'                     => "blob NULL",
);

// Lizenzart in der Liste anzeigen
$GLOBALS['TL_DCA']['tl_module']['fields']['lizenzverwaltung_typview'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lizenzverwaltung_typview'],
	'inputType'               => 'checkbox',
	'default'                 => false,
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'isBoolean'           => true
	),
	'sql'                     => "char(1) NOT NULL default ''"
);

// Lizenzgültigkeit auf Jahresende in der Anzeige stellen
$GLOBALS['TL_DCA']['tl_module']['fields']['lizenzverwaltung_endofyear'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lizenzverwaltung_endofyear'],
	'inputType'               => 'checkbox',
	'default'                 => false,
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'isBoolean'           => true
	),
	'sql'                     => "char(1) NOT NULL default ''"
);
