<?php
/**
 * Avatar for Contao Open Source CMS
 *
 * Copyright (C) 2013 Kirsten Roschanski
 * Copyright (C) 2013 Tristan Lins <http://bit3.de>
 *
 * @package    DeWIS
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Add palette to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['lizenzverwaltung'] = '{title_legend},name,headline,type;{lizenzverwaltung_legend},lizenzverwaltung_typ,lizenzverwaltung_typview,lizenzverwaltung_endofyear;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';


$GLOBALS['TL_DCA']['tl_module']['fields']['lizenzverwaltung_typ'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lizenzverwaltung_typ'],
	'inputType'               => 'checkboxWizard',
	'options'                 => \Schachbulle\ContaoLizenzverwaltungBundle\Classes\Helper::getLizenzen(),
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
