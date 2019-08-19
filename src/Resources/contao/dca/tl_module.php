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

$GLOBALS['TL_DCA']['tl_module']['palettes']['trainerlizenzen'] = '{title_legend},name,headline,type;{settings_legend},trainerlizenzen_typ,trainerlizenzen_typview;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';


$GLOBALS['TL_DCA']['tl_module']['fields']['trainerlizenzen_typ'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['trainerlizenzen_typ'],
	'inputType'               => 'checkboxWizard',
	'options'                 => \Schachbulle\ContaoTrainerlizenzenBundle\Classes\Helper::getLizenzen(),
	'eval'                    => array
	(
		'tl_class'            => 'w50 clr',
		'chosen'              => true,
		'mandatory'           => true,
		'multiple'            => true
	),
	'sql'                     => "blob NULL", 
);

$GLOBALS['TL_DCA']['tl_module']['fields']['trainerlizenzen_typview'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['trainerlizenzen_typview'],
	'inputType'               => 'checkbox',
	'default'                 => false,
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'isBoolean'           => true
	),
	'sql'                     => "char(1) NOT NULL default ''"
);
